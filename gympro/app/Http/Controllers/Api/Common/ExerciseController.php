<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        return Exercise::when($request->difficulty, fn($q) =>
            $q->where('difficulty', $request->difficulty)
        )->get();
    }

    public function show($id)
    {
        return Exercise::findOrFail($id);
    }
}