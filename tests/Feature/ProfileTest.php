<?php

use App\Models\User;
use App\Modules\Core\Livewire\ProfileEdit;
use Livewire\Livewire;

use Illuminate\Support\Facades\Route;

test('profile route is mapped to ProfileEdit component', function () {
    $route = Route::getRoutes()->getByName('profile');

    expect($route)->not->toBeNull();
    expect($route->getAction('controller'))->toBe(ProfileEdit::class);
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(ProfileEdit::class)
        ->set('name', 'Test User Updated')
        ->set('phone', '123456789')
        ->call('updateProfile')
        ->assertHasNoErrors();

    $user->refresh();

    $this->assertSame('Test User Updated', $user->name);
    $this->assertSame('123456789', $user->phone);
});

test('password can be updated', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user);

    Livewire::test(ProfileEdit::class)
        ->set('current_password', 'password')
        ->set('new_password', 'new-password')
        ->set('new_password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
});

test('current password must be correct to update password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user);

    Livewire::test(ProfileEdit::class)
        ->set('current_password', 'wrong-password')
        ->set('new_password', 'new-password')
        ->set('new_password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);
});
