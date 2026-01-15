<?php

namespace App\Modules\Core\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileEdit extends Component
{
    use WithFileUploads;

    public $name = '';
    public $phone = '';
    public $avatar;
    public $current_avatar = '';

    // Changement de mot de passe
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->phone = $user->phone ?? '';
        $this->current_avatar = $user->avatar ?? '';
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
        ];

        // Upload avatar si fourni
        if ($this->avatar) {
            // Supprimer l'ancien avatar si existe
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $this->avatar->store('avatars', 'public');
            $data['avatar'] = $path;
            $this->current_avatar = $path;
        }

        $user->update($data);

        $this->reset('avatar');
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Profil mis a jour avec succes.']);
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

        // Verifier le mot de passe actuel
        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Le mot de passe actuel est incorrect.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Mot de passe modifie avec succes.']);
    }

    public function removeAvatar()
    {
        $user = auth()->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);
        $this->current_avatar = '';

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Photo de profil supprimee.']);
    }

    public function render()
    {
        return view('core::livewire.profile-edit', [
            'user' => auth()->user(),
        ])->layout('layouts.app');
    }
}
