<?php
namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        return response()->json([
            'profile' => $request->user()->profile,
            'health' => $request->user()->profile->healthProfile ?? null,
            'experience' => $request->user()->profile->experience ?? null
        ]);
    }
}