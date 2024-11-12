<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'year_published',
        'quantity',
        'category_id',
        'created_by',
        'image_path',
        'description'
    ];

    public function category()
    {
        return $this->belongsTo(BookCategory::class);
    }
}
