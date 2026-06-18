<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DriverProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'license_number' => [
                'nullable',
                'string',
                'regex:/^\d{2} \d{2} \d{6}$/'
            ],
            'license_date_from' => 'nullable|date',
            'license_date_to' => 'nullable|date|after:license_date_from',
            'has_own_loaders' => 'boolean',
            'loaders_count' => 'integer|min:0',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'license_number.regex' => 'Номер водительского удостоверения должен быть в формате: 55 55 555555',
            'license_date_to.after' => 'Дата окончания должна быть позже даты выдачи',
            'loaders_count.min' => 'Количество грузчиков не может быть отрицательным',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Ошибка валидации',
            'errors' => $validator->errors()
        ], 422));
    }
}