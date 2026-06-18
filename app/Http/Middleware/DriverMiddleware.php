<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DriverMiddleware
{
    /**
     * Обработка входящего запроса.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем: пользователь авторизован (на всякий) и у него есть роль driver
        if ($request->user() && $request->user()->roles()->where('name', 'driver')->exists()) {
            return $next($request); // Пропускаем в контроллер
        }

        // Если не водитель — от ворот поворот ;)
        return response()->json([
            'status'  => 'error',
            'message' => 'Только пользователи с ролью "водитель" могут выполнять это действие.'
        ], 403);
    }
}
