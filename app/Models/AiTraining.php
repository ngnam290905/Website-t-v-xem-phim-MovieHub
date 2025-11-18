<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTraining extends Model
{
    use HasFactory;

    protected $table = 'ai_training';

    protected $fillable = [
        'question',
        'answer',
        'intent',
    ];
}
