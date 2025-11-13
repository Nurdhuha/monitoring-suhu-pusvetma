<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'name',
        'type',
    ];

    /**
     * Get the device that owns the section.
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the temperature readings for the section.
     */
    public function temperatureReadings()
    {
        return $this->hasMany(TemperatureReading::class);
    }
}
