<?php

namespace App\Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Core\Services\UpdateService;

class CheckUpdateCommand extends Command
{
    protected $signature = 'erp:check-update {--apply : Appliquer la mise à jour si disponible}';
    protected $description = 'Vérifier et appliquer les mises à jour de l\'ERP WMC';

    protected UpdateService $updateService;

    public function __construct(UpdateService $updateService)
    {
        parent::__construct();
        $this->updateService = $updateService;
    }

    public function handle(): int
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║       ERP WMC - Système de mise à jour ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        // Afficher la version actuelle
        $current = $this->updateService->getCurrentVersion();
        $this->line("Version actuelle: <fg=cyan>v{$current['version']}</>");
        $this->line("Build: <fg=gray>{$current['build']}</> - {$current['codename']}");
        $this->newLine();

        // Vérifier les mises à jour
        $this->info('Vérification des mises à jour...');
        $updateInfo = $this->updateService->checkForUpdates();

        if (isset($updateInfo['error'])) {
            $this->warn("Erreur: {$updateInfo['error']}");
            return self::FAILURE;
        }

        if (!$updateInfo['update_available']) {
            $this->info('✓ Votre ERP est à jour.');
            return self::SUCCESS;
        }

        // Mise à jour disponible
        $this->newLine();
        $this->warn("⚡ Nouvelle version disponible: v{$updateInfo['latest_version']}");

        if (!empty($updateInfo['latest_info']['changelog'])) {
            $this->newLine();
            $this->line('<fg=yellow>Changelog:</>');
            $this->line($updateInfo['latest_info']['changelog']);
        }

        // Appliquer si demandé
        if ($this->option('apply')) {
            $this->newLine();

            if (!$this->confirm('Voulez-vous appliquer cette mise à jour ?')) {
                $this->info('Mise à jour annulée.');
                return self::SUCCESS;
            }

            // Créer une sauvegarde
            $this->info('Création d\'une sauvegarde...');
            $backup = $this->updateService->createBackup();

            if (!$backup['success']) {
                $this->error("Échec de la sauvegarde: {$backup['message']}");
                return self::FAILURE;
            }

            $this->info("✓ Sauvegarde créée: {$backup['path']}");
            $this->newLine();

            // Appliquer la mise à jour
            $downloadUrl = $updateInfo['latest_info']['download_url'] ?? null;

            if (!$downloadUrl) {
                $this->error('URL de téléchargement non disponible.');
                return self::FAILURE;
            }

            $this->info('Application de la mise à jour...');
            $result = $this->updateService->downloadAndApplyUpdate($downloadUrl);

            if ($result['success']) {
                $this->newLine();
                $this->info('╔════════════════════════════════════════╗');
                $this->info('║  ✓ Mise à jour appliquée avec succès!  ║');
                $this->info('╚════════════════════════════════════════╝');
                $this->line("Nouvelle version: <fg=green>v{$result['new_version']}</>");
                return self::SUCCESS;
            } else {
                $this->error("Échec: {$result['message']}");
                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->line('Utilisez <fg=cyan>php artisan erp:check-update --apply</> pour appliquer la mise à jour.');

        return self::SUCCESS;
    }
}
