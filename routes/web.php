<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// Dashboard and Profile routes are managed by App\Modules\Core\routes.php

require __DIR__ . '/auth.php';
