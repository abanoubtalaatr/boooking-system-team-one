<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hospital extends Model
{
    use  HasFactory;

    protected $fillable = ["admin_id", "name", "latitude", "longitude"];

    protected $casts = [
        "latitude" => "decimal:7",
        "longitude" => "decimal:7",
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, "admin_id");
    }

    public function doctorProfiles(): BelongsToMany
    {
        return $this->belongsToMany(DoctorProfile::class, "doctor_hospital");
    }
}
