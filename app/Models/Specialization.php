<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
    ];

    // protected function image(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn () => $this->getFirstMediaUrl('specializations')
    //     );
    // }

    public function doctors(): HasMany
    {
        return $this->hasMany(DoctorProfile::class);
    }
}
