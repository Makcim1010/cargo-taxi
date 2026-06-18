<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterUserRequest extends FormRequest
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
        $role = $this->input('role');

        $rules = [
            'role' => 'required|in:customer,driver,loader',
            'surname' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone|regex:/^\+7\d{10}$/',
            'password' => 'required|string|min:6|confirmed',
        ];

        // Водитель — добавляем дополнительные поля
        if ($role === 'driver') {
            $rules['patronymic'] = 'nullable|string|max:255';
            $rules['license_number'] = 'required|string|regex:/^\d{2} \d{2} \d{6}$/';
            $rules['license_date'] = 'required|date';
            $rules['license_categories'] = 'required|array';
            $rules['license_categories.*'] = 'string|in:A,B,C,D,BE,CE,DE';
            $rules['has_loaders'] = 'required|boolean';
            $rules['loaders_count'] = 'required_if:has_loaders,true|integer|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        $role = $this->input('role');

        $messages = [
            // Общие для всех
            'role.required' => 'Выберите тип регистрации',
            'role.in' => 'Тип регистрации может быть customer или driver',
            
            'surname.required' => 'Поле "Фамилия" обязательно для заполнения',
            'surname.string' => 'Фамилия должна быть текстовой строкой',
            'surname.max' => 'Максимальная длина фамилии — 255 символов',
            
            'name.required' => 'Поле "Имя" обязательно для заполнения',
            'name.string' => 'Имя должно быть текстовой строкой',
            'name.max' => 'Максимальная длина имени — 255 символов',
            
            'phone.required' => 'Поле "Телефон" обязательно для заполнения',
            'phone.unique' => 'Пользователь с таким номером телефона уже зарегистрирован',
            'phone.regex' => 'Номер телефона должен соответствовать формату: +79999999999',
            
            'password.required' => 'Поле "Пароль" обязательно для заполнения',
            'password.min' => 'Пароль должен быть не менее 6 символов',
            'password.confirmed' => 'Подтверждение пароля не совпадает',
        ];

        // Сообщения для водителя
        if ($role === 'driver') {
            $messages['patronymic.max'] = 'Максимальная длина отчества — 255 символов';
            
            $messages['license_number.required'] = 'Поле "Номер водительских прав" обязательно для заполнения';
            $messages['license_number.regex'] = 'Номер прав должен соответствовать формату: 55 55 555555';
            
            $messages['license_date.required'] = 'Поле "Дата выдачи прав" обязательно для заполнения';
            $messages['license_date.date'] = 'Укажите корректную дату выдачи прав';
            
            $messages['license_categories.required'] = 'Выберите хотя бы одну категорию прав';
            $messages['license_categories.array'] = 'Категории прав должны быть переданы массивом';
            $messages['license_categories.*.in'] = 'Выбрана недопустимая категория прав';
            
            $messages['has_loaders.required'] = 'Укажите, есть ли у вас грузчики';
            $messages['has_loaders.boolean'] = 'Поле "Есть грузчики" должно быть true или false';
            
            $messages['loaders_count.required_if'] = 'Укажите количество грузчиков';
            $messages['loaders_count.integer'] = 'Количество грузчиков должно быть целым числом';
            $messages['loaders_count.min'] = 'Количество грузчиков не может быть отрицательным';
        }

        return $messages;
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
