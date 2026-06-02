<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorReading;

class SensorController extends Controller
{
    // Renders the main dashboard page
    public function index()
    {
        return view('dashboard');
    }

    // Receives data from your JavaScript Simulator
    public function store(Request $request)
    {
        $co2 = $request->co2_ppm;
        // Depth of Analysis: Threshold logic for anomaly detection
        $isAnomaly = $co2 > 1000 ? true : false; 

        $reading = SensorReading::create([
            'zone_name' => $request->zone_name,
            'co2_ppm' => $co2,
            'humidity_percent' => $request->humidity_percent,
            'is_anomaly' => $isAnomaly
        ]);

        return response()->json([
            'status' => 'success', 
            'data' => $reading,
            'anomaly_detected' => $isAnomaly
        ]);
    }
}