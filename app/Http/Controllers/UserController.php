<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\DriverLicense;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class UserController extends Controller
{   

    public function getCurrentUser(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /*==========Регистрация нового пользователя==========*/
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Создаём пользователя
        $user = User::create([
            'surname' => $validated['surname'],
            'name' => $validated['name'],
            'patronymic' => $validated['patronymic'] ?? null,
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Если водитель — создаём профиль водителя
        if ($validated['role'] === 'driver') {
            DriverLicense::create([
                'user_id' => $user->id,
                'license_number' => $validated['license_number'],
                'license_date' => $validated['license_date'],
                'license_categories' => json_encode($validated['license_categories']),
                'has_loaders' => $validated['has_loaders'],
                'loaders_count' => $validated['has_loaders'] ? $validated['loaders_count'] : 0,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Регистрация прошла успешно',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }



    /*==========Аутентификация пользователя==========*/
    public function login(LoginUserRequest $request): JsonResponse
    {
        // Валидация JSON-данных от фронтенда
        $validated = $request->validated();

        // Ищем пользователя по номеру телефона
        $user = User::where('phone', $validated['phone'])->first();

        // Проверяем, существует ли пользователь и совпадает ли пароль
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Неверный номер телефона или пароль.'
            ], 401);
        }

        // Удаляем все старые токены этого пользователя (старше 7 дней)
        $user->tokens()->where('created_at', '<', now()->subDays(7))->delete();

        // Генерируем токен доступа для аутентифицированного пользователя
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'       => 'success',
            'message'      => 'Вы успешно вошли в систему!',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user->load('driverLicense'), // Только driverLicense, без roles
        ]);
    }



    /*==========Выход из системы==========*/
    public function logout(Request $request): JsonResponse
    {
        // Удаляем текущий токен пользователя
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Вы успешно вышли из системы'
        ], 200);
    }



    /*==========Удаление аккаунта пользователя==========*/
    public function destroy(DeleteUserRequest $request)
    {   
        // Валидация JSON-данных от фронтенда
        $validated = $request->validated();

        // Ищем пользователя по номеру телефона
        $user = User::where('phone', $validated['phone'])->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Пользователь не найден'  // ← исправил сообщение
            ], 404);  // ← 404 Not Found, не 401
        }

        // Проверяем, существует ли пользователь и совпадает ли пароль
        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка удаления',
                'errors' => [
                    'password' => ['Неверный пароль для подтверждения удаления.']
                ]
            ], 422);
        }

        // Удаляем пользователя
        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }


    
    /*==========Редактироваение профиля пользователя==========*/
    public function update(UpdateUserRequest $request, $id)
    {
        if (!request()->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Не авторизован'
            ], 401);
        }
        
        $validated = $request->validated();
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Пользователь не найден'
            ], 404);
        }
        
        if (request()->user()->id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Вы можете редактировать только свой профиль'
            ], 403);
        }
        
        if (isset($validated['password'])) {
            
            if (!isset($validated['current_password'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Для смены пароля введите текущий пароль'
                ], 422);
            }
            
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Текущий пароль указан неверно'
                ], 422);
            }
            
            $validated['password'] = Hash::make($validated['password']);
            
            // Удаляем current_password внутри блока
            unset($validated['current_password']);
        }
        
        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Профиль успешно обновлён',
            'user' => $user
        ], 200);
    }



    /*==========Смена роли==========*/
    public function changeRole(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $request->validate([
            'role' => 'required|in:customer,driver,loader'
        ]);
        
        $user->update(['role' => $request->role]);
        
        return response()->json([
            'status' => 'success',
            'role' => $user->role,
            'message' => 'Роль изменена'
        ]);
    }


    /*==========Получение баланса и кошелька==========*/
    public function getBalance(): JsonResponse
    {
        return response()->json([
            'balance' => auth()->user()->balance
        ]);
    }


    /*==========Тестовое пополнение баланса и вывод==========*/
    public function testDeposit(Request $request): JsonResponse
    {
        $user = auth()->user();
        $amount = $request->input('amount', 0);
        
        if ($amount <= 0) {
            return response()->json(['message' => 'Сумма должна быть больше 0'], 400);
        }
        
        $user->balance = ($user->balance ?? 0) + $amount;
        $user->save();
        
        return response()->json([
            'success' => true,
            'balance' => $user->balance,
            'message' => "Счёт пополнен на {$amount} ₽"
        ]);
    }

    public function testWithdraw(Request $request): JsonResponse
    {
        $user = auth()->user();
        $amount = $request->input('amount', 0);
        
        if ($amount <= 0) {
            return response()->json(['message' => 'Сумма должна быть больше 0'], 400);
        }
        
        if (($user->balance ?? 0) < $amount) {
            return response()->json(['message' => 'Недостаточно средств'], 400);
        }
        
        $user->balance = ($user->balance ?? 0) - $amount;
        $user->save();
        
        return response()->json([
            'success' => true,
            'balance' => $user->balance,
            'message' => "Выведено {$amount} ₽"
        ]);
    }
}
