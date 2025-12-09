<?php

declare (strict_types= 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'technical_test_id',
        'title',
        'description',
        'order',
        'is_completed',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_completed' => 'boolean',
    ];

    public function technicalTest()
    {
        return $this->belongsTo(TechnicalTest::class);
    }
}
