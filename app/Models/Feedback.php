<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';

    protected $fillable = [
        'user_id',
        'stars',
        'full_name',
        'email',
        'category',
        'message',
    ];

    protected $casts = [
        'stars' => 'integer',
    ];

    // Relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
