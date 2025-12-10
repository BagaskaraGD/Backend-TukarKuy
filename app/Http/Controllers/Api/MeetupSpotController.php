<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meetup_Spot;

class MeetupSpotController extends Controller
{
    public function index()
    {
        $MeetupSpot = Meetup_Spot::latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'List Data Meet up spot',
            'data'    => $MeetupSpot
        ], 200);
    }
}
