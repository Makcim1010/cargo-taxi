<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'number' => 'required|string|unique:vehicles,number',
        ];
    }

    /**
     * Проверка, есть ли у водителя необходимая категория прав
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            
            // Проверяем, есть ли профиль водителя
            if (!$user->driverProfile) {
                $validator->errors()->add('type', 'Сначала заполните профиль водителя (категории прав)');
                return;
            }
            
            // Получаем требуемую категорию для типа техники
            $requiredCategory = $this->getCategoryForVehicleType($this->type);
            
            if (!$requiredCategory) {
                return; // Если тип техники не требует категории (на всякий случай)
            }
            
            // Получаем категории прав водителя
            $userCategories = $user->driverProfile->categories->pluck('name')->toArray();
            
            // Проверяем, есть ли нужная категория
            if (!in_array($requiredCategory, $userCategories)) {
                $validator->errors()->add('type', "Для добавления \"{$this->type}\" требуется категория прав \"{$requiredCategory}\"");
            }
        });
    }

    /**
     * Соответствие типа техники и требуемой категории прав
     */
    private function getCategoryForVehicleType($type)
    {
        $map = [
            'Газель' => 'B',
            '5-тонник' => 'C',
            'Самосвал' => 'C',
            'Трал' => 'CE',
            'Трактор' => 'A',
            'Манипулятор' => 'C',
            'Эвакуатор' => 'C',
            'Автовышка' => 'C',
            'Экскаватор' => 'A',
        ];
        
        return $map[$type] ?? null;
    }

    /**
     * Сообщения об ошибках
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Выберите тип техники',
            'brand.required' => 'Введите марку',
            'number.required' => 'Введите гос. номер',
            'number.unique' => 'Транспорт с таким гос. номером уже зарегистрирован',
        ];
    }

    /**
     * Принудительный возврат JSON при ошибке валидации
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Ошибка валидации',
            'errors' => $validator->errors()
        ], 422));
    }
}

