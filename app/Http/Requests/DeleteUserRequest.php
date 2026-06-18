<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteUserRequest extends FormRequest
{
    /**
     * Разрешить выполнение запроса всем авторизованным пользователям.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации.
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string', 
            'password' => 'required|string',
        ];
    }

    /**
     * Кастомные сообщения об ошибках валидации.
     */
    public function messages(): array
    {
        return [
            // Ошибки для телефона
            'phone.required' => 'Поле "Телефон" обязательно для заполнения.',

            // Ошибки для пароля
            'password.required' => 'Поле "Пароль" обязательно для заполнения.',
        ];
    }

    /**
     * Принудительный возврат JSON при ошибке валидации.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Ошибка валидации',
            'errors' => $validator->errors(),
        ], 422));
    }
}
