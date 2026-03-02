<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\DietPlan;
use App\Models\MemberMeal;
use Illuminate\Http\Request;

class DietController extends Controller
{
    public function myPlans(Request $request)
    {
        return DietPlan::where('member_id', $request->user()->profile->id)
            ->where('is_active', 1)
            ->get();
    }

    /**
     * ✅ جدول الوجبات للعضو
     * GET /member/meals
     *
     * Output:
     * {
     *   "breakfast": [...],
     *   "lunch": [...],
     *   "dinner": [...],
     *   "snack": [...]
     * }
     */
    public function myMeals(Request $request)
{
    $profileId = $request->user()->profile->id;

    $items = MemberMeal::query()
        ->where('member_id', $profileId)
        ->where('is_active', 1)
        ->with(['meal:id,name,description,category,calories,protein,carbs,fats,image_url'])
        ->orderByDesc('start_date')
        ->get()
        ->map(function (MemberMeal $mm) {
            $m = $mm->meal;

            return [
                'assignment_id' => $mm->id,
                'meal_id'       => $m?->id,
                'meal_time'     => $mm->meal_time,
                'day_of_week'   => $mm->day_of_week, // ✅ مهم
                'start_date'    => $mm->start_date?->toDateString(),

                'name'        => $m?->name,
                'description' => $m?->description,
                'category'    => $m?->category,

                'calories' => $m?->calories,
                'protein'  => $m?->protein,
                'carbs'    => $m?->carbs,
                'fats'     => $m?->fats,

                'image_url' => $m?->image_url,
            ];
        });

    $days  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    $times = ['breakfast', 'lunch', 'dinner', 'snack'];

    // ✅ group by day then time
    $result = [];
    foreach ($days as $day) {
        $dayItems = $items->where('day_of_week', $day);

        $grouped = $dayItems->groupBy('meal_time');

        $result[$day] = [];
        foreach ($times as $t) {
            $result[$day][$t] = $grouped->get($t, collect())->values();
        }

        // null/other meal_time
        $others = $grouped->get(null, collect())->merge($grouped->get('other', collect()))->values();
        $result[$day]['other'] = $others;
    }

    return response()->json($result);
}
}
