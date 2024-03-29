<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'url',
        'like_count',
        'type'
    ];

    public function likes() {
        return $this->hasMany(Like::class);
    }
}
