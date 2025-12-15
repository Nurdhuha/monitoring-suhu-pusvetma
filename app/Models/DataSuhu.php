<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSuhu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'data_suhu';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'section',
        'temperature',
        'user_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the device that owns the temperature reading.
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the user who recorded the temperature reading.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
