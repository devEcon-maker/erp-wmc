<?php

namespace App\Modules\Core\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class UpdateService
{
    protected string $versionFile;
    protected string $updateServer;
    protected string $backupPath;

    public function __construct()
    {
        $this->versionFile = base_path('version.json');
        $this->updateServer = config('app.update_server', 'https://updates.erp-wmc.com');
        $this->backupPath = storage_path('app/backups');
    }

    /**
     * Obtenir la version actuelle de l'application
     */
    public function getCurrentVersion(): array
    {
        if (!File::exists($this->versionFile)) {
            return [
                'version' => '1.0.0',
                'build' => '0',
                'release_date' => now()->format('Y-m-d'),
                'codename' => 'Unknown',
            ];
        }

        return json_decode(File::get($this->versionFile), true);
    }

    /**
     * Vérifier les mises à jour disponibles
     */
    public function checkForUpdates(): array
    {
        try {
            $current = $this->getCurrentVersion();
            $githubRepo = config('app.github_repo', '');
            $customServer = config('app.update_server', '');

            // Priorité 1: GitHub si configuré
            if (!empty($githubRepo)) {
                return $this->checkGitHubReleases($current);
            }

            // Priorité 2: Serveur personnalisé si configuré et différent du défaut
            if (!empty($customServer) && $customServer !== 'https://updates.erp-wmc.com') {
                $response = Http::timeout(30)->get("{$customServer}/api/v1/latest");

                if ($response->successful()) {
                    $latest = $response->json();

                    return [
                        'current_version' => $current['version'],
                        'latest_version' => $latest['version'] ?? $current['version'],
                        'update_available' => version_compare($latest['version'] ?? '0', $current['version'], '>'),
                        'latest_info' => $latest,
                        'changelog' => $latest['changelog'] ?? [],
                    ];
                }
            }

            // Aucune source configurée
            return [
                'current_version' => $current['version'],
                'latest_version' => $current['version'],
                'update_available' => false,
                'message' => 'Configurez GITHUB_REPO dans .env pour activer les mises à jour automatiques',
            ];
        } catch (\Exception $e) {
            Log::warning('Update check failed: ' . $e->getMessage());

            return [
                'current_version' => $this->getCurrentVersion()['version'],
                'latest_version' => $this->getCurrentVersion()['version'],
                'update_available' => false,
                'error' => 'Impossible de vérifier les mises à jour: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier les releases GitHub comme fallback
     */
    protected function checkGitHubReleases(array $current): array
    {
        $githubRepo = config('app.github_repo', '');

        if (empty($githubRepo)) {
            return [
                'current_version' => $current['version'],
                'latest_version' => $current['version'],
                'update_available' => false,
                'error' => 'Serveur de mise à jour non configuré',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
            ])->get("https://api.github.com/repos/{$githubRepo}/releases/latest");

            if (!$response->successful()) {
                throw new \Exception('GitHub API error');
            }

            $release = $response->json();
            $latestVersion = ltrim($release['tag_name'] ?? 'v0.0.0', 'v');

            return [
                'current_version' => $current['version'],
                'latest_version' => $latestVersion,
                'update_available' => version_compare($latestVersion, $current['version'], '>'),
                'latest_info' => [
                    'version' => $latestVersion,
                    'release_date' => $release['published_at'] ?? null,
                    'download_url' => $release['zipball_url'] ?? null,
                    'changelog' => $release['body'] ?? '',
                ],
                'source' => 'github',
            ];
        } catch (\Exception $e) {
            return [
                'current_version' => $current['version'],
                'latest_version' => $current['version'],
                'update_available' => false,
                'error' => 'Vérification GitHub échouée',
            ];
        }
    }

    /**
     * Créer une sauvegarde avant mise à jour
     */
    public function createBackup(): array
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupDir = "{$this->backupPath}/{$timestamp}";

            if (!File::isDirectory($this->backupPath)) {
                File::makeDirectory($this->backupPath, 0755, true);
            }

            File::makeDirectory($backupDir, 0755, true);

            // Sauvegarder les fichiers critiques
            $filesToBackup = [
                '.env',
                'version.json',
                'composer.json',
                'composer.lock',
            ];

            foreach ($filesToBackup as $file) {
                $source = base_path($file);
                if (File::exists($source)) {
                    File::copy($source, "{$backupDir}/{$file}");
                }
            }

            // Sauvegarder la base de données
            $this->backupDatabase($backupDir);

            // Enregistrer les infos de backup
            File::put("{$backupDir}/backup_info.json", json_encode([
                'created_at' => now()->toIso8601String(),
                'version' => $this->getCurrentVersion()['version'],
                'files' => $filesToBackup,
            ], JSON_PRETTY_PRINT));

            return [
                'success' => true,
                'path' => $backupDir,
                'message' => 'Sauvegarde créée avec succès',
            ];
        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Échec de la sauvegarde: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sauvegarder la base de données
     */
    protected function backupDatabase(string $backupDir): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection === 'mysql') {
            $host = config("database.connections.{$connection}.host");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");

            $dumpFile = "{$backupDir}/database.sql";

            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($dumpFile)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                Log::warning('Database backup may have failed', ['output' => $output]);
            }
        }
    }

    /**
     * Télécharger et appliquer une mise à jour
     */
    public function downloadAndApplyUpdate(string $downloadUrl): array
    {
        try {
            // 1. Créer une sauvegarde d'abord
            $backup = $this->createBackup();
            if (!$backup['success']) {
                return $backup;
            }

            // 2. Mettre l'application en mode maintenance
            Artisan::call('down', ['--retry' => 60]);

            // 3. Télécharger le fichier de mise à jour
            $tempFile = storage_path('app/update_' . time() . '.zip');
            $response = Http::timeout(300)->sink($tempFile)->get($downloadUrl);

            if (!$response->successful()) {
                Artisan::call('up');
                return [
                    'success' => false,
                    'message' => 'Échec du téléchargement de la mise à jour',
                ];
            }

            // 4. Extraire les fichiers
            $extractPath = storage_path('app/update_temp_' . time());
            $zip = new ZipArchive();

            if ($zip->open($tempFile) !== true) {
                Artisan::call('up');
                return [
                    'success' => false,
                    'message' => 'Impossible d\'ouvrir le fichier de mise à jour',
                ];
            }

            $zip->extractTo($extractPath);
            $zip->close();

            // 5. Copier les fichiers (en excluant certains fichiers sensibles)
            $this->copyUpdateFiles($extractPath);

            // 6. Exécuter les migrations
            Artisan::call('migrate', ['--force' => true]);

            // 7. Vider les caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            // 8. Réinstaller les dépendances composer
            if (File::exists(base_path('composer.json'))) {
                Log::info('Réinstallation des dépendances composer...');
                $composerOutput = [];
                $composerReturn = 0;
                exec('cd ' . escapeshellarg(base_path()) . ' && composer install --no-dev --optimize-autoloader 2>&1', $composerOutput, $composerReturn);
                Log::info('Composer install terminé avec code: ' . $composerReturn);
            }

            // 9. Nettoyer les fichiers temporaires
            File::delete($tempFile);
            File::deleteDirectory($extractPath);

            // 10. Sortir du mode maintenance
            Artisan::call('up');

            // 11. Enregistrer la mise à jour
            $this->logUpdate();

            return [
                'success' => true,
                'message' => 'Mise à jour appliquée avec succès',
                'new_version' => $this->getCurrentVersion()['version'],
            ];
        } catch (\Exception $e) {
            Artisan::call('up');
            Log::error('Update failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Échec de la mise à jour: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Copier les fichiers de mise à jour en excluant les fichiers sensibles
     */
    protected function copyUpdateFiles(string $sourcePath): void
    {
        // Dossiers à exclure complètement
        $excludeDirs = [
            'storage',
            '.git',
            'node_modules',
            'vendor',
        ];

        // Fichiers spécifiques à exclure
        $excludeFiles = [
            '.env',
            '.env.local',
            'composer.lock',
        ];

        // Trouver le répertoire racine dans le ZIP (GitHub ajoute un préfixe comme "repo-main/")
        $directories = File::directories($sourcePath);
        $sourceRoot = count($directories) === 1 ? $directories[0] : $sourcePath;

        $files = File::allFiles($sourceRoot);
        $copiedCount = 0;

        foreach ($files as $file) {
            $relativePath = str_replace($sourceRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath); // Normaliser les séparateurs

            // Vérifier si le fichier est dans un dossier exclu
            $shouldExclude = false;
            foreach ($excludeDirs as $excludeDir) {
                if (str_starts_with($relativePath, $excludeDir . '/') || $relativePath === $excludeDir) {
                    $shouldExclude = true;
                    break;
                }
            }

            // Vérifier si c'est un fichier spécifique à exclure
            if (!$shouldExclude) {
                foreach ($excludeFiles as $excludeFile) {
                    if ($relativePath === $excludeFile) {
                        $shouldExclude = true;
                        break;
                    }
                }
            }

            if (!$shouldExclude) {
                $destination = base_path($relativePath);
                $destinationDir = dirname($destination);

                if (!File::isDirectory($destinationDir)) {
                    File::makeDirectory($destinationDir, 0755, true);
                }

                File::copy($file->getPathname(), $destination);
                $copiedCount++;
            }
        }

        Log::info("Update: {$copiedCount} fichiers copiés");
    }

    /**
     * Enregistrer la mise à jour dans l'historique
     */
    protected function logUpdate(string $notes = null, bool $success = true, string $errorMessage = null): void
    {
        $current = $this->getCurrentVersion();

        DB::table('system_updates')->insert([
            'version' => $current['version'],
            'build' => $current['build'] ?? null,
            'applied_at' => now(),
            'applied_by' => auth()->id(),
            'notes' => $notes,
            'success' => $success,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Obtenir l'historique des mises à jour
     */
    public function getUpdateHistory(): array
    {
        try {
            return DB::table('system_updates')
                ->orderBy('applied_at', 'desc')
                ->limit(20)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtenir la liste des sauvegardes
     */
    public function getBackups(): array
    {
        if (!File::isDirectory($this->backupPath)) {
            return [];
        }

        $backups = [];
        $directories = File::directories($this->backupPath);

        foreach ($directories as $dir) {
            $infoFile = "{$dir}/backup_info.json";
            if (File::exists($infoFile)) {
                $info = json_decode(File::get($infoFile), true);
                $info['path'] = $dir;
                $info['name'] = basename($dir);
                $info['size'] = $this->getDirectorySize($dir);
                $backups[] = $info;
            }
        }

        // Trier par date décroissante
        usort($backups, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return $backups;
    }

    /**
     * Restaurer une sauvegarde
     */
    public function restoreBackup(string $backupName): array
    {
        try {
            $backupDir = "{$this->backupPath}/{$backupName}";

            if (!File::isDirectory($backupDir)) {
                return [
                    'success' => false,
                    'message' => 'Sauvegarde introuvable',
                ];
            }

            Artisan::call('down', ['--retry' => 60]);

            // Restaurer les fichiers
            $files = File::files($backupDir);
            foreach ($files as $file) {
                if ($file->getFilename() !== 'backup_info.json' && $file->getFilename() !== 'database.sql') {
                    File::copy($file->getPathname(), base_path($file->getFilename()));
                }
            }

            // Restaurer la base de données si présente
            $dumpFile = "{$backupDir}/database.sql";
            if (File::exists($dumpFile)) {
                $this->restoreDatabase($dumpFile);
            }

            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('up');

            return [
                'success' => true,
                'message' => 'Sauvegarde restaurée avec succès',
            ];
        } catch (\Exception $e) {
            Artisan::call('up');

            return [
                'success' => false,
                'message' => 'Échec de la restauration: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Restaurer la base de données
     */
    protected function restoreDatabase(string $dumpFile): void
    {
        $connection = config('database.default');

        if ($connection === 'mysql') {
            $host = config("database.connections.{$connection}.host");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");
            $database = config("database.connections.{$connection}.database");

            $command = sprintf(
                'mysql -h %s -u %s -p%s %s < %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($dumpFile)
            );

            exec($command);
        }
    }

    /**
     * Appliquer une mise à jour depuis un fichier ZIP uploadé
     */
    public function applyUpdateFromFile(string $filePath): array
    {
        try {
            // Vérifier que le fichier existe
            if (!File::exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'Fichier de mise à jour introuvable',
                ];
            }

            Log::info('Début de la mise à jour manuelle depuis: ' . $filePath);

            // 1. Mettre l'application en mode maintenance
            Artisan::call('down', ['--retry' => 60]);

            // 2. Extraire les fichiers
            $extractPath = storage_path('app/update_temp_' . time());
            $zip = new ZipArchive();

            if ($zip->open($filePath) !== true) {
                Artisan::call('up');
                return [
                    'success' => false,
                    'message' => 'Impossible d\'ouvrir le fichier ZIP',
                ];
            }

            $zip->extractTo($extractPath);
            $zip->close();

            Log::info('Fichiers extraits vers: ' . $extractPath);

            // 3. Copier les fichiers de mise à jour
            $this->copyUpdateFiles($extractPath);

            // 4. Vérifier si composer.json a été mis à jour et réinstaller les dépendances
            if (File::exists(base_path('composer.json'))) {
                Log::info('Réinstallation des dépendances composer...');
                $composerOutput = [];
                $composerReturn = 0;
                exec('cd ' . escapeshellarg(base_path()) . ' && composer install --no-dev --optimize-autoloader 2>&1', $composerOutput, $composerReturn);
                Log::info('Composer install terminé avec code: ' . $composerReturn);
                if ($composerReturn !== 0) {
                    Log::warning('Composer install output: ' . implode("\n", $composerOutput));
                }
            }

            // 5. Exécuter les migrations
            Artisan::call('migrate', ['--force' => true]);
            Log::info('Migrations exécutées');

            // 6. Vider les caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            // 7. Optimiser si en production
            if (app()->environment('production')) {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
            }

            // 8. Nettoyer les fichiers temporaires
            File::deleteDirectory($extractPath);

            // 9. Sortir du mode maintenance
            Artisan::call('up');

            // 10. Enregistrer la mise à jour
            $this->logUpdate('Mise à jour manuelle via upload');

            $newVersion = $this->getCurrentVersion();
            Log::info('Mise à jour terminée vers: v' . $newVersion['version']);

            return [
                'success' => true,
                'message' => "Mise à jour vers v{$newVersion['version']} appliquée avec succès!",
                'new_version' => $newVersion['version'],
            ];
        } catch (\Exception $e) {
            Artisan::call('up');
            Log::error('Manual update failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Échec de la mise à jour: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Supprimer une sauvegarde
     */
    public function deleteBackup(string $backupName): array
    {
        try {
            $backupDir = "{$this->backupPath}/{$backupName}";

            if (!File::isDirectory($backupDir)) {
                return [
                    'success' => false,
                    'message' => 'Sauvegarde introuvable',
                ];
            }

            File::deleteDirectory($backupDir);

            return [
                'success' => true,
                'message' => 'Sauvegarde supprimée',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Échec de la suppression: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculer la taille d'un répertoire
     */
    protected function getDirectorySize(string $path): string
    {
        $size = 0;

        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }

        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        }

        return $size . ' bytes';
    }

    /**
     * Vérifier les prérequis système
     */
    public function checkSystemRequirements(): array
    {
        $current = $this->getCurrentVersion();

        return [
            'php' => [
                'required' => $current['minimum_php'] ?? '8.2',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, $current['minimum_php'] ?? '8.2', '>='),
            ],
            'extensions' => [
                'zip' => [
                    'required' => true,
                    'installed' => extension_loaded('zip'),
                ],
                'pdo' => [
                    'required' => true,
                    'installed' => extension_loaded('pdo'),
                ],
                'mbstring' => [
                    'required' => true,
                    'installed' => extension_loaded('mbstring'),
                ],
                'openssl' => [
                    'required' => true,
                    'installed' => extension_loaded('openssl'),
                ],
            ],
            'permissions' => [
                'storage' => is_writable(storage_path()),
                'base' => is_writable(base_path()),
            ],
            'disk_space' => [
                'free' => disk_free_space(base_path()),
                'required' => 100 * 1024 * 1024, // 100 MB minimum
                'passed' => disk_free_space(base_path()) > 100 * 1024 * 1024,
            ],
        ];
    }
}
