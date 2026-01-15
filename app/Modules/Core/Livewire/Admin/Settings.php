<?php

namespace App\Modules\Core\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Modules\Core\Models\SmtpConfiguration;
use App\Modules\Core\Services\UpdateService;
use Illuminate\Support\Facades\Hash;

class Settings extends Component
{
    use WithFileUploads;
    // Onglet actif
    public $activeTab = 'smtp';

    // SMTP Configuration
    public $showSmtpModal = false;
    public $editingSmtp = null;

    // Formulaire SMTP
    public $smtp_name = '';
    public $smtp_host = '';
    public $smtp_port = 587;
    public $smtp_encryption = 'tls';
    public $smtp_username = '';
    public $smtp_password = '';
    public $smtp_from_address = '';
    public $smtp_from_name = '';

    // Test email
    public $showTestModal = false;
    public $testingSmtp = null;
    public $test_email = '';

    // Profil utilisateur
    public $user_name = '';
    public $user_email = '';
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    // Mise à jour
    public $updateInfo = null;
    public $isCheckingUpdate = false;
    public $isApplyingUpdate = false;
    public $showBackupModal = false;
    public $selectedBackup = null;
    public $updateFile = null;
    public $showUploadModal = false;

    protected function rules()
    {
        return [
            'smtp_name' => 'required|string|max:100',
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'smtp_encryption' => 'required|in:tls,ssl,',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => $this->editingSmtp ? 'nullable|string' : 'required|string',
            'smtp_from_address' => 'required|email',
            'smtp_from_name' => 'required|string|max:255',
        ];
    }

    protected $messages = [
        'smtp_name.required' => 'Le nom de la configuration est requis.',
        'smtp_host.required' => 'L\'hote SMTP est requis.',
        'smtp_port.required' => 'Le port est requis.',
        'smtp_username.required' => 'Le nom d\'utilisateur est requis.',
        'smtp_password.required' => 'Le mot de passe est requis.',
        'smtp_from_address.required' => 'L\'adresse d\'expediteur est requise.',
        'smtp_from_address.email' => 'L\'adresse d\'expediteur doit etre un email valide.',
        'smtp_from_name.required' => 'Le nom d\'expediteur est requis.',
    ];

    public function mount()
    {
        $user = auth()->user();
        $this->user_name = $user->name;
        $this->user_email = $user->email;
    }

    public function getSmtpConfigurationsProperty()
    {
        return SmtpConfiguration::with('creator')
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    // SMTP Methods
    public function createSmtp()
    {
        $this->resetSmtpForm();
        $this->showSmtpModal = true;
    }

    public function editSmtp(SmtpConfiguration $smtp)
    {
        $this->editingSmtp = $smtp;
        $this->smtp_name = $smtp->name;
        $this->smtp_host = $smtp->host;
        $this->smtp_port = $smtp->port;
        $this->smtp_encryption = $smtp->encryption;
        $this->smtp_username = $smtp->username;
        $this->smtp_password = '';
        $this->smtp_from_address = $smtp->from_address;
        $this->smtp_from_name = $smtp->from_name;
        $this->showSmtpModal = true;
    }

    public function saveSmtp()
    {
        $this->validate();

        $data = [
            'name' => $this->smtp_name,
            'host' => $this->smtp_host,
            'port' => $this->smtp_port,
            'encryption' => $this->smtp_encryption,
            'username' => $this->smtp_username,
            'from_address' => $this->smtp_from_address,
            'from_name' => $this->smtp_from_name,
        ];

        if ($this->smtp_password) {
            $data['password'] = $this->smtp_password;
        }

        if ($this->editingSmtp) {
            $this->editingSmtp->update($data);
            $message = "Configuration SMTP '{$this->smtp_name}' mise a jour.";
        } else {
            $data['created_by'] = auth()->id();
            $data['is_default'] = SmtpConfiguration::count() === 0;
            SmtpConfiguration::create($data);
            $message = "Configuration SMTP '{$this->smtp_name}' creee.";
        }

        $this->dispatch('notify', type: 'success', message: $message);
        $this->closeSmtpModal();
    }

    public function setDefaultSmtp(SmtpConfiguration $smtp)
    {
        $smtp->setAsDefault();
        $this->dispatch('notify', type: 'success', message: "'{$smtp->name}' est maintenant la configuration par defaut.");
    }

    public function toggleSmtpStatus(SmtpConfiguration $smtp)
    {
        $smtp->update(['is_active' => !$smtp->is_active]);
        $status = $smtp->is_active ? 'activee' : 'desactivee';
        $this->dispatch('notify', type: 'success', message: "Configuration '{$smtp->name}' {$status}.");
    }

    public function deleteSmtp(SmtpConfiguration $smtp)
    {
        if ($smtp->is_default) {
            $this->dispatch('notify', type: 'error', message: "Impossible de supprimer la configuration par defaut.");
            return;
        }

        $name = $smtp->name;
        $smtp->delete();
        $this->dispatch('notify', type: 'success', message: "Configuration '{$name}' supprimee.");
    }

    public function testSmtpConnection(SmtpConfiguration $smtp)
    {
        $result = $smtp->testConnection();

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('notify', type: 'error', message: $result['message']);
        }
    }

    public function openTestModal(SmtpConfiguration $smtp)
    {
        $this->testingSmtp = $smtp;
        $this->test_email = auth()->user()->email;
        $this->showTestModal = true;
    }

    public function sendTestEmail()
    {
        $this->validate([
            'test_email' => 'required|email',
        ]);

        if (!$this->testingSmtp) {
            return;
        }

        $result = $this->testingSmtp->sendTestEmail($this->test_email);

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('notify', type: 'error', message: $result['message']);
        }

        $this->showTestModal = false;
        $this->testingSmtp = null;
    }

    public function closeSmtpModal()
    {
        $this->showSmtpModal = false;
        $this->resetSmtpForm();
    }

    protected function resetSmtpForm()
    {
        $this->editingSmtp = null;
        $this->smtp_name = '';
        $this->smtp_host = '';
        $this->smtp_port = 587;
        $this->smtp_encryption = 'tls';
        $this->smtp_username = '';
        $this->smtp_password = '';
        $this->smtp_from_address = '';
        $this->smtp_from_name = config('app.name', 'ERP WMC');
        $this->resetValidation();
    }

    // Profile Methods
    public function updateProfile()
    {
        $this->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email,' . auth()->id(),
        ]);

        auth()->user()->update([
            'name' => $this->user_name,
            'email' => $this->user_email,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Profil mis a jour avec succes.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Le mot de passe actuel est incorrect.');
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->dispatch('notify', type: 'success', message: 'Mot de passe mis a jour avec succes.');
    }

    // Update Methods
    public function checkForUpdates()
    {
        $this->isCheckingUpdate = true;

        try {
            $updateService = app(UpdateService::class);
            $this->updateInfo = $updateService->checkForUpdates();

            if (isset($this->updateInfo['error'])) {
                $this->dispatch('notify', type: 'warning', message: $this->updateInfo['error']);
            } elseif ($this->updateInfo['update_available']) {
                $this->dispatch('notify', type: 'info', message: "Une nouvelle version {$this->updateInfo['latest_version']} est disponible!");
            } else {
                $this->dispatch('notify', type: 'success', message: 'Vous utilisez la derniere version.');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erreur lors de la verification: ' . $e->getMessage());
        }

        $this->isCheckingUpdate = false;
    }

    public function createBackup()
    {
        try {
            $updateService = app(UpdateService::class);
            $result = $updateService->createBackup();

            if ($result['success']) {
                $this->dispatch('notify', type: 'success', message: $result['message']);
            } else {
                $this->dispatch('notify', type: 'error', message: $result['message']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    public function applyUpdate()
    {
        // Vérifier que l'utilisateur est super admin
        if (!auth()->user()->hasRole('super_admin')) {
            $this->dispatch('notify', type: 'error', message: 'Seul le Super Admin peut effectuer les mises a jour.');
            return;
        }

        if (!$this->updateInfo || !$this->updateInfo['update_available']) {
            $this->dispatch('notify', type: 'warning', message: 'Aucune mise a jour disponible.');
            return;
        }

        $this->isApplyingUpdate = true;

        try {
            $updateService = app(UpdateService::class);
            $downloadUrl = $this->updateInfo['latest_info']['download_url'] ?? null;

            if (!$downloadUrl) {
                throw new \Exception('URL de telechargement non disponible');
            }

            $result = $updateService->downloadAndApplyUpdate($downloadUrl);

            if ($result['success']) {
                $this->dispatch('notify', type: 'success', message: $result['message']);
                // Recharger la page après succès
                $this->dispatch('reload-page');
            } else {
                $this->dispatch('notify', type: 'error', message: $result['message']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erreur: ' . $e->getMessage());
        }

        $this->isApplyingUpdate = false;
    }

    public function restoreBackup(string $backupName)
    {
        if (!auth()->user()->hasRole('super-admin')) {
            $this->dispatch('notify', type: 'error', message: 'Seul le Super Admin peut restaurer les sauvegardes.');
            return;
        }

        try {
            $updateService = app(UpdateService::class);
            $result = $updateService->restoreBackup($backupName);

            if ($result['success']) {
                $this->dispatch('notify', type: 'success', message: $result['message']);
                $this->dispatch('reload-page');
            } else {
                $this->dispatch('notify', type: 'error', message: $result['message']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    public function deleteBackup(string $backupName)
    {
        try {
            $updateService = app(UpdateService::class);
            $result = $updateService->deleteBackup($backupName);

            if ($result['success']) {
                $this->dispatch('notify', type: 'success', message: $result['message']);
            } else {
                $this->dispatch('notify', type: 'error', message: $result['message']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    public function openUploadModal()
    {
        $this->updateFile = null;
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->updateFile = null;
        $this->showUploadModal = false;
    }

    public function applyManualUpdate()
    {
        // Vérifier que l'utilisateur est super admin
        if (!auth()->user()->hasRole('super_admin')) {
            $this->dispatch('notify', type: 'error', message: 'Seul le Super Admin peut effectuer les mises a jour.');
            return;
        }

        $this->validate([
            'updateFile' => 'required|file|mimes:zip|max:102400', // Max 100MB
        ], [
            'updateFile.required' => 'Veuillez selectionner un fichier ZIP.',
            'updateFile.mimes' => 'Le fichier doit etre au format ZIP.',
            'updateFile.max' => 'Le fichier ne doit pas depasser 100 Mo.',
        ]);

        $this->isApplyingUpdate = true;

        try {
            $updateService = app(UpdateService::class);

            // Créer d'abord une sauvegarde
            $backupResult = $updateService->createBackup();
            if (!$backupResult['success']) {
                throw new \Exception('Impossible de creer la sauvegarde: ' . $backupResult['message']);
            }

            // Stocker le fichier temporairement
            $tempPath = $this->updateFile->store('updates', 'local');
            $fullPath = storage_path('app/' . $tempPath);

            // Appliquer la mise à jour depuis le fichier uploadé
            $result = $updateService->applyUpdateFromFile($fullPath);

            // Supprimer le fichier temporaire
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if ($result['success']) {
                $this->dispatch('notify', type: 'success', message: $result['message']);
                $this->showUploadModal = false;
                $this->updateFile = null;
                // Recharger la page après succès
                $this->dispatch('reload-page');
            } else {
                $this->dispatch('notify', type: 'error', message: $result['message']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erreur: ' . $e->getMessage());
        }

        $this->isApplyingUpdate = false;
    }

    public function getVersionInfoProperty()
    {
        $updateService = app(UpdateService::class);
        return $updateService->getCurrentVersion();
    }

    public function getBackupsProperty()
    {
        $updateService = app(UpdateService::class);
        return $updateService->getBackups();
    }

    public function getSystemRequirementsProperty()
    {
        $updateService = app(UpdateService::class);
        return $updateService->checkSystemRequirements();
    }

    public function getUpdateHistoryProperty()
    {
        $updateService = app(UpdateService::class);
        return $updateService->getUpdateHistory();
    }

    public function render()
    {
        return view('core::livewire.admin.settings', [
            'smtpConfigurations' => $this->smtpConfigurations,
            'encryptions' => SmtpConfiguration::ENCRYPTIONS,
            'commonPorts' => SmtpConfiguration::COMMON_PORTS,
            'versionInfo' => $this->versionInfo,
            'backups' => $this->backups,
            'systemRequirements' => $this->systemRequirements,
            'updateHistory' => $this->updateHistory,
        ])->layout('layouts.app');
    }
}
