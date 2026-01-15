<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Spatie Permission Middleware
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Ajouter le middleware pour verifier que l'utilisateur est actif sur toutes les routes web authentifiees
        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gerer l'expiration du token CSRF (erreur 419) de maniere elegante
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            // Pour les requetes Livewire/AJAX, retourner une reponse JSON
            if ($request->expectsJson() || $request->header('X-Livewire')) {
                return response()->json([
                    'message' => 'Votre session a expiré. Veuillez rafraîchir la page.'
                ], 419);
            }

            // Pour les requetes normales, rediriger vers la page precedente avec un message
            return redirect()
                ->back()
                ->with('error', 'Votre session a expiré. Veuillez réessayer.')
                ->withInput();
        });
    })->create();
