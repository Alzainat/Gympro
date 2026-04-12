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
        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        return response()->json(
            DietPlan::query()
                ->where('member_id', $profile->id)
                ->where('is_active', 1)
                ->get()
        );
    }

    /**
     * جدول الوجبات للعضو
     * GET /member/meals
     */
    public function myMeals(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        $profileId = $profile->id;

        $items = MemberMeal::query()
            ->where('member_id', $profileId)
            ->where('is_active', 1)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->with(['meal:id,name,description,category,calories,protein,carbs,fats,image_url'])
            ->orderBy('day_of_week')
            ->orderBy('meal_time')
            ->orderByDesc('start_date')
            ->get()
            ->map(function (MemberMeal $mm) {
                $m = $mm->meal;

                return [
                    'assignment_id' => $mm->id,
                    'meal_id'       => $m?->id,
                    'meal_time'     => $mm->meal_time,
                    'day_of_week'   => $mm->day_of_week,
                    'start_date'    => $mm->start_date?->toDateString(),
                    'end_date'      => $mm->end_date?->toDateString(),
                    'grams'         => $mm->grams,

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

        $days  = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $times = ['breakfast', 'lunch', 'dinner', 'snack'];

        $result = [];

        foreach ($days as $day) {
            $dayItems = $items->where('day_of_week', $day);
            $grouped = $dayItems->groupBy('meal_time');

            $result[$day] = [];

            foreach ($times as $t) {
                $result[$day][$t] = $grouped->get($t, collect())->values();
            }

            $others = $grouped->get(null, collect())
                ->merge($grouped->get('other', collect()))
                ->values();

            $result[$day]['other'] = $others;
        }

        return response()->json($result);
    }
}
