<?php

use App\Models\User;
use App\Modules\Core\Livewire\Help;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;

test('help route is mapped to Help component', function () {
    $route = Route::getRoutes()->getByName('help');

    expect($route)->not->toBeNull();
    expect($route->getAction('controller'))->toBe(Help::class);
});

test('help route name is registered', function () {
    expect(Route::has('help'))->toBeTrue();
});
