<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{   
    /**
     * Поля, разрешенные для массового заполнения.
     */
    protected $fillable = [
        'title',
        'short_text',
        'full_text',
        'image_url',
        'type',
        'is_published',
        'published_at',
    ];
}
