<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Обработка входящего запроса.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем: пользователь авторизован (на всякий) и у него есть роль admin
        if ($request->user() && $request->user()->roles()->where('name', 'admin')->exists()) {
            return $next($request); // Пропускаем в контроллер
        }

        // Если не админ — от ворот поворот ;)
        return response()->json([
            'status'  => 'error',
            'message' => 'Доступ запрещен. Требуются права администратора.'
        ], 403);
    }
}
