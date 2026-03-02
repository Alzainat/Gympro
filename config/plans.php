<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cutting Plans (تنحيف)
    |--------------------------------------------------------------------------
    */

    'cutting' => [

        'bronze' => [
            'price' => 20,
            'routines' => [1], // تمارين حرق دهون خفيفة
            'meals' => [
                ['meal_id' => 1, 'meal_time' => 'breakfast'],
                ['meal_id' => 2, 'meal_time' => 'lunch'],
            ],
        ],

        'silver' => [
            'price' => 40,
            'routines' => [2, 3], // تمارين كارديو + مقاومة
            'meals' => [
                ['meal_id' => 3, 'meal_time' => 'breakfast'],
                ['meal_id' => 4, 'meal_time' => 'lunch'],
                ['meal_id' => 5, 'meal_time' => 'dinner'],
            ],
        ],

        'gold' => [
            'price' => 70,
            'routines' => [4, 5, 6], // برنامج حرق مكثف
            'meals' => [
                ['meal_id' => 6, 'meal_time' => 'breakfast'],
                ['meal_id' => 7, 'meal_time' => 'snack'],
                ['meal_id' => 8, 'meal_time' => 'lunch'],
                ['meal_id' => 9, 'meal_time' => 'dinner'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bulking Plans (تضخيم)
    |--------------------------------------------------------------------------
    */

    'bulking' => [

        'bronze' => [
            'price' => 25,
            'routines' => [7], // تمارين مقاومة أساسية
            'meals' => [
                ['meal_id' => 10, 'meal_time' => 'breakfast'],
                ['meal_id' => 11, 'meal_time' => 'lunch'],
            ],
        ],

        'silver' => [
            'price' => 50,
            'routines' => [8, 9], // تضخيم متوسط
            'meals' => [
                ['meal_id' => 12, 'meal_time' => 'breakfast'],
                ['meal_id' => 13, 'meal_time' => 'snack'],
                ['meal_id' => 14, 'meal_time' => 'lunch'],
                ['meal_id' => 15, 'meal_time' => 'dinner'],
            ],
        ],

        'gold' => [
            'price' => 80,
            'routines' => [10, 11, 12], // برنامج تضخيم احترافي
            'meals' => [
                ['meal_id' => 16, 'meal_time' => 'breakfast'],
                ['meal_id' => 17, 'meal_time' => 'snack'],
                ['meal_id' => 18, 'meal_time' => 'lunch'],
                ['meal_id' => 19, 'meal_time' => 'dinner'],
                ['meal_id' => 20, 'meal_time' => 'snack'],
            ],
        ],
    ],

];
