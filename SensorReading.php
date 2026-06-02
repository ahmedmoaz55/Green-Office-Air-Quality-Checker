<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_name',
        'co2_ppm',
        'humidity_percent',
        'is_anomaly'
    ];
}