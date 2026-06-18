<?php

namespace App\Http\Controllers;
use App\Http\Requests\DriverProfileRequest;
use Illuminate\Http\Request;
use App\Models\DriverProfile;
use App\Models\Category;

class DriverProfileController extends Controller
{
    // Получить профиль водителя
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'license_number' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
        ]);
        
        // Обновляем или создаём профиль
        $profile = DriverProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['license_number' => $validated['license_number'] ?? null]
        );
        
        // Сохраняем категории с датами
        if (isset($validated['categories'])) {
            $categoriesData = [];
            foreach ($validated['categories'] as $cat) {
                $category = Category::where('name', $cat['name'])->first();
                if ($category) {
                    $categoriesData[$category->id] = [
                        'issued_at' => $cat['dateFrom'] ?? null,
                        'expires_at' => $cat['dateTo'] ?? null
                    ];
                }
            }
            $profile->categories()->sync($categoriesData);
        }
        
        return response()->json($profile->load('categories'));
    }

    public function show()
    {
        $profile = auth()->user()->driverProfile()->with('categories')->first();
        return response()->json($profile);
    }
}