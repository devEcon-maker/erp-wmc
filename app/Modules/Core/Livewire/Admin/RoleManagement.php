<?php

namespace App\Modules\Core\Livewire\Admin;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleManagement extends Component
{
    public $showModal = false;
    public $editingRole = null;
    public $roleName = '';
    public $selectedPermissions = [];
    public $isCreating = false;

    protected $rules = [
        'roleName' => 'required|string|max:255',
    ];

    public function createRole()
    {
        $this->reset(['editingRole', 'roleName', 'selectedPermissions']);
        $this->isCreating = true;
        $this->showModal = true;
    }

    public function editRole(Role $role)
    {
        $this->editingRole = $role;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->isCreating = false;
        $this->showModal = true;
    }

    public function saveRole()
    {
        $this->validate();

        if ($this->isCreating) {
            // Vérifier si le rôle existe déjà
            if (Role::where('name', $this->roleName)->exists()) {
                session()->flash('error', "Le rôle '{$this->roleName}' existe déjà.");
                return;
            }

            $role = Role::create(['name' => $this->roleName, 'guard_name' => 'web']);
            $role->syncPermissions($this->selectedPermissions);

            session()->flash('success', "Rôle '{$this->roleName}' créé avec succès.");
        } else {
            // Ne pas permettre de modifier les rôles système
            if (in_array($this->editingRole->name, ['super_admin'])) {
                session()->flash('error', "Le rôle 'super_admin' ne peut pas être modifié.");
                return;
            }

            $this->editingRole->name = $this->roleName;
            $this->editingRole->save();
            $this->editingRole->syncPermissions($this->selectedPermissions);

            session()->flash('success', "Rôle '{$this->roleName}' mis à jour.");
        }

        $this->closeModal();
    }

    public function deleteRole(Role $role)
    {
        // Ne pas permettre de supprimer les rôles système
        if (in_array($role->name, ['super_admin', 'admin', 'employe'])) {
            session()->flash('error', "Ce rôle système ne peut pas être supprimé.");
            return;
        }

        // Vérifier si des utilisateurs ont ce rôle
        if ($role->users()->count() > 0) {
            session()->flash('error', "Ce rôle est attribué à des utilisateurs. Retirez-le d'abord.");
            return;
        }

        $roleName = $role->name;
        $role->delete();

        session()->flash('success', "Rôle '{$roleName}' supprimé.");
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['editingRole', 'roleName', 'selectedPermissions', 'isCreating']);
    }

    public function getPermissionsByGroup()
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $group = $parts[0] ?? 'other';
            $grouped[$group][] = $permission;
        }

        return $grouped;
    }

    public function render()
    {
        $roles = Role::withCount('permissions', 'users')->orderBy('name')->get();
        $permissionsByGroup = $this->getPermissionsByGroup();

        return view('livewire.admin.role-management', [
            'roles' => $roles,
            'permissionsByGroup' => $permissionsByGroup,
        ])->layout('layouts.app');
    }
}
