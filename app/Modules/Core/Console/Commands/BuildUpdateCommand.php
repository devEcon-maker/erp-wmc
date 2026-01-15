<?php

namespace App\Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BuildUpdateCommand extends Command
{
    protected $signature = 'erp:build-update
                            {--version= : Nouvelle version (ex: 1.1.0)}
                            {--build= : Numéro de build (optionnel)}
                            {--codename= : Nom de code (optionnel)}';

    protected $description = 'Créer un package ZIP de mise à jour pour déploiement';

    protected array $excludedPaths = [
        '.git',
        '.idea',
        '.vscode',
        'node_modules',
        'vendor',
        'storage/logs',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/app/backups',
        'storage/app/updates',
        'tests',
        '.env',
        '.env.example',
        '.env.testing',
        '.gitignore',
        '.gitattributes',
        'phpunit.xml',
        'package-lock.json',
        '*.log',
    ];

    protected array $excludedExtensions = [
        'log',
        'cache',
    ];

    public function handle(): int
    {
        $this->info('╔════════════════════════════════════════════╗');
        $this->info('║   ERP WMC - Générateur de mise à jour      ║');
        $this->info('╚════════════════════════════════════════════╝');
        $this->newLine();

        // Lire la version actuelle
        $versionFile = base_path('version.json');
        $currentVersion = json_decode(File::get($versionFile), true);

        $this->line("Version actuelle: <fg=cyan>v{$currentVersion['version']}</>");
        $this->newLine();

        // Demander la nouvelle version
        $newVersion = $this->option('version')
            ?? $this->ask('Nouvelle version', $this->incrementVersion($currentVersion['version']));

        $newBuild = $this->option('build')
            ?? now()->format('YmdHi');

        $newCodename = $this->option('codename')
            ?? $currentVersion['codename'] ?? 'Release';

        // Mettre à jour version.json
        $updatedVersion = [
            'version' => $newVersion,
            'build' => $newBuild,
            'release_date' => now()->format('Y-m-d'),
            'codename' => $newCodename,
            'minimum_php' => $currentVersion['minimum_php'] ?? '8.2',
            'minimum_laravel' => $currentVersion['minimum_laravel'] ?? '12.0',
        ];

        File::put($versionFile, json_encode($updatedVersion, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info("✓ version.json mis à jour: v{$newVersion}");

        // Créer le package ZIP
        $outputDir = storage_path('app/releases');
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $zipName = "erp-wmc-v{$newVersion}-build{$newBuild}.zip";
        $zipPath = "{$outputDir}/{$zipName}";

        $this->info('Création du package ZIP...');
        $this->newLine();

        $result = $this->createZipPackage($zipPath);

        if ($result['success']) {
            $this->newLine();
            $this->info('╔════════════════════════════════════════════╗');
            $this->info('║     ✓ Package créé avec succès!            ║');
            $this->info('╚════════════════════════════════════════════╝');
            $this->newLine();
            $this->line("Fichier: <fg=green>{$zipPath}</>");
            $this->line("Taille: <fg=cyan>{$result['size']}</>");
            $this->line("Fichiers: <fg=cyan>{$result['file_count']}</>");
            $this->newLine();
            $this->line('<fg=yellow>Instructions:</>');
            $this->line('1. Uploadez ce fichier sur votre serveur de production');
            $this->line('2. Dans l\'admin ERP > Paramètres > Mises à jour');
            $this->line('3. Cliquez sur "Upload manuel" et sélectionnez le ZIP');

            return self::SUCCESS;
        }

        $this->error("Échec de la création: {$result['message']}");
        return self::FAILURE;
    }

    protected function createZipPackage(string $zipPath): array
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return [
                'success' => false,
                'message' => 'Impossible de créer le fichier ZIP',
            ];
        }

        $basePath = base_path();
        $fileCount = 0;

        // Parcourir tous les fichiers
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $progressBar = $this->output->createProgressBar();
        $progressBar->start();

        foreach ($iterator as $file) {
            $filePath = $file->getPathname();
            $relativePath = str_replace($basePath . '/', '', $filePath);

            // Vérifier si le fichier/dossier doit être exclu
            if ($this->shouldExclude($relativePath, $file->isDir())) {
                continue;
            }

            if ($file->isFile()) {
                $zip->addFile($filePath, $relativePath);
                $fileCount++;
                $progressBar->advance();
            } elseif ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            }
        }

        $progressBar->finish();
        $zip->close();

        // Calculer la taille
        $size = filesize($zipPath);
        $sizeFormatted = $this->formatSize($size);

        return [
            'success' => true,
            'path' => $zipPath,
            'size' => $sizeFormatted,
            'file_count' => $fileCount,
        ];
    }

    protected function shouldExclude(string $path, bool $isDir): bool
    {
        foreach ($this->excludedPaths as $excluded) {
            // Gestion des wildcards
            if (str_contains($excluded, '*')) {
                $pattern = str_replace('*', '.*', preg_quote($excluded, '/'));
                if (preg_match("/^{$pattern}$/", $path)) {
                    return true;
                }
            } elseif (str_starts_with($path, $excluded) || $path === $excluded) {
                return true;
            }
        }

        // Vérifier les extensions pour les fichiers
        if (!$isDir) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($extension, $this->excludedExtensions)) {
                return true;
            }
        }

        return false;
    }

    protected function incrementVersion(string $version): string
    {
        $parts = explode('.', $version);

        if (count($parts) >= 3) {
            $parts[2] = (int)$parts[2] + 1;
        } elseif (count($parts) == 2) {
            $parts[] = '1';
        } else {
            return '1.0.1';
        }

        return implode('.', $parts);
    }

    protected function formatSize(int $size): string
    {
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        }

        return $size . ' bytes';
    }
}
