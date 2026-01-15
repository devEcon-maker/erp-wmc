<?php

namespace App\Modules\Core\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $showModal = false;
    public $showCreateModal = false;
    public $editingUser = null;
    public $selectedRoles = [];

    // Champs pour la creation d'utilisateur
    public $newUserName = '';
    public $newUserEmail = '';
    public $newUserPassword = '';
    public $newUserPasswordConfirmation = '';
    public $newUserRoles = [];

    // Champs pour l'edition d'utilisateur
    public $showEditModal = false;
    public $editUserId = null;
    public $editUserName = '';
    public $editUserPhone = '';
    public $editUserPassword = '';
    public $editUserPasswordConfirmation = '';

    protected $queryString = ['search', 'roleFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function resetCreateForm()
    {
        $this->newUserName = '';
        $this->newUserEmail = '';
        $this->newUserPassword = '';
        $this->newUserPasswordConfirmation = '';
        $this->newUserRoles = [];
        $this->resetValidation();
    }

    public function createUser()
    {
        $this->validate([
            'newUserName' => ['required', 'string', 'max:255'],
            'newUserEmail' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'newUserPassword' => ['required', 'string', 'min:8', 'same:newUserPasswordConfirmation'],
            'newUserRoles' => ['array'],
        ], [
            'newUserName.required' => 'Le nom est obligatoire.',
            'newUserEmail.required' => 'L\'email est obligatoire.',
            'newUserEmail.email' => 'L\'email doit etre valide.',
            'newUserEmail.unique' => 'Cet email est deja utilise.',
            'newUserPassword.required' => 'Le mot de passe est obligatoire.',
            'newUserPassword.min' => 'Le mot de passe doit contenir au moins 8 caracteres.',
            'newUserPassword.same' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::create([
            'name' => $this->newUserName,
            'email' => $this->newUserEmail,
            'password' => Hash::make($this->newUserPassword),
            'is_active' => true,
        ]);

        if (!empty($this->newUserRoles)) {
            $user->syncRoles($this->newUserRoles);
        }

        $this->dispatch('notify', type: 'success', message: "Utilisateur {$user->name} cree avec succes.");
        $this->closeCreateModal();
    }

    public function editUserRoles(User $user)
    {
        $this->editingUser = $user;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->showModal = true;
    }

    public function updateUserRoles()
    {
        if ($this->editingUser) {
            $this->editingUser->syncRoles($this->selectedRoles);

            session()->flash('success', "Rôles mis à jour pour {$this->editingUser->name}");
            $this->showModal = false;
            $this->editingUser = null;
            $this->selectedRoles = [];
        }
    }

    public function toggleUserStatus(User $user)
    {
        // Ne pas désactiver son propre compte
        if ($user->id === auth()->id()) {
            session()->flash('error', "Vous ne pouvez pas désactiver votre propre compte.");
            return;
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activé' : 'désactivé';
        session()->flash('success', "Utilisateur {$user->name} {$status}.");
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingUser = null;
        $this->selectedRoles = [];
    }

    public function openEditModal(User $user)
    {
        $this->editUserId = $user->id;
        $this->editUserName = $user->name;
        $this->editUserPhone = $user->phone ?? '';
        $this->editUserPassword = '';
        $this->editUserPasswordConfirmation = '';
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editUserId = null;
        $this->editUserName = '';
        $this->editUserPhone = '';
        $this->editUserPassword = '';
        $this->editUserPasswordConfirmation = '';
        $this->resetValidation();
    }

    public function updateUser()
    {
        $rules = [
            'editUserName' => ['required', 'string', 'max:255'],
            'editUserPhone' => ['nullable', 'string', 'max:20'],
            'editUserPassword' => ['required', 'string', 'min:8', 'same:editUserPasswordConfirmation'],
            'editUserPasswordConfirmation' => ['required', 'string'],
        ];

        $this->validate($rules, [
            'editUserName.required' => 'Le nom est obligatoire.',
            'editUserPassword.required' => 'Le mot de passe est obligatoire.',
            'editUserPassword.min' => 'Le mot de passe doit contenir au moins 8 caracteres.',
            'editUserPassword.same' => 'Les mots de passe ne correspondent pas.',
            'editUserPasswordConfirmation.required' => 'La confirmation du mot de passe est obligatoire.',
        ]);

        $user = User::find($this->editUserId);
        if (!$user) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Utilisateur introuvable.']);
            return;
        }

        $user->name = $this->editUserName;
        $user->phone = $this->editUserPhone;
        $user->password = Hash::make($this->editUserPassword);

        $user->save();

        $this->dispatch('toast', ['type' => 'success', 'message' => "Utilisateur {$user->name} mis a jour avec succes."]);
        $this->closeEditModal();
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Vous ne pouvez pas supprimer votre propre compte.']);
            return;
        }

        $name = $user->name;
        $user->delete();

        $this->dispatch('toast', ['type' => 'success', 'message' => "Utilisateur {$name} supprime avec succes."]);
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.user-management', [
            'users' => $users,
            'roles' => $roles,
        ])->layout('layouts.app');
    }
}
