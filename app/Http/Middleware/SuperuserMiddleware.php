<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperuserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSuperuser()) {
            return response()->json(['message' => 'Forbidden. Superuser access required.'], 403);
        }
        return $next($request);
    }
}
