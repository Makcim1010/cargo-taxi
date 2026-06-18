<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        // Получаем ID из маршрута (например, /user/update/{id})
        $userId = $this->route('id');
        
        return [
            'surname' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'phone' => [
                'sometimes',
                'string',
                'unique:users,phone,' . $userId,
                'regex:/^\+7\d{10}$/'
            ],
            'password' => 'sometimes|string|min:6|confirmed',
            'current_password' => 'required_with:password|string',
        ];
    }

    public function messages(): array
    {
        return [
            // Фамилия
            'surname.string' => 'Фамилия должна быть текстовой строкой.',
            'surname.max' => 'Максимальная длина фамилии — 255 символов.',

            // Имя
            'name.string' => 'Имя должно быть текстовой строкой.',
            'name.max' => 'Максимальная длина имени — 255 символов.',

            // Email
            'email.email' => 'Введите корректный email адрес.',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован.',

            // Телефон
            'phone.string' => 'Телефон должен быть текстовой строкой.',
            'phone.unique' => 'Пользователь с таким номером телефона уже зарегистрирован.',
            'phone.regex' => 'Номер телефона должен соответствовать формату: +79999999999.',

            // Пароль
            'password.min' => 'Пароль должен быть не менее 6 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',

            // Текущий пароль
            'current_password.required_with' => 'Для смены пароля введите текущий пароль.',
        ];
    }
}
