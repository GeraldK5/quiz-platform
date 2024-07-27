<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    use HasFactory;
    protected $table = 'questions'; // Specify your table name if different from 'challenges'
    public $timestamps = false;
    protected $fillable = [
        'question_text', 'marks', 'challengeNumber'
    ];
}
