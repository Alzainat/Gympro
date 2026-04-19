<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\DietPlan;
use App\Models\MemberMeal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        $selectedDate = $request->query('date')
            ? Carbon::parse($request->query('date'))->toDateString()
            : now()->toDateString();

        $items = MemberMeal::query()
            ->where('member_id', $profileId)
            ->where('is_active', 1)
            ->whereDate('start_date', '<=', $selectedDate)
            ->where(function ($q) use ($selectedDate) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $selectedDate);
            })
            ->with(['meal:id,name,description,category,calories,protein,carbs,fats,image_url'])
            ->orderBy('day_of_week')
            ->orderBy('meal_time')
            ->orderByDesc('start_date')
            ->get();

        $planStartDate = $items->sortBy('start_date')->first()?->start_date?->toDateString();
        $planEndDate = $items
            ->filter(fn ($item) => !empty($item->end_date))
            ->sortByDesc('end_date')
            ->first()?->end_date?->toDateString();

        $mappedItems = $items->map(function (MemberMeal $mm) {
            $m = $mm->meal;

            return [
                'assignment_id' => $mm->id,
                'meal_id'       => $m?->id,
                'meal_time'     => $mm->meal_time,
                'day_of_week'   => $mm->day_of_week,
                'start_date'    => $mm->start_date?->toDateString(),
                'end_date'      => $mm->end_date?->toDateString(),
                'source'        => $mm->source,
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
            $dayItems = $mappedItems->where('day_of_week', $day);
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

        return response()->json([
            'meta' => [
                'selected_date' => $selectedDate,
                'plan_timer' => $mappedItems->isEmpty() ? null : [
                    'id' => 'website-meals-plan',
                    'title' => 'Website Meal Plan',
                    'source' => 'payment',
                    'start_date' => $planStartDate,
                    'end_date' => $planEndDate,
                ],
            ],
            'days' => $result,
        ]);
    }
}
