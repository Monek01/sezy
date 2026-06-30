<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Vérifie que l'utilisateur est connecté, possède un rôle back-office
     * et n'est pas bloqué. Le contrôle fin par rôle (matrice de
     * permissions, §5.1) est fait au niveau des policies / contrôleurs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, "Accès réservé à l'administration SEZY.");
        }

        if ($user->is_blocked) {
            abort(403, 'Votre compte a été suspendu.');
        }

        return $next($request);
    }
}
