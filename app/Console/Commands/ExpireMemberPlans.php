<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MemberRoutine;
use App\Models\MemberMeal;
use App\Models\MemberProfile;

class ExpireMemberPlans extends Command
{
    protected $signature = 'plans:expire';
    protected $description = 'Expire member subscriptions';

    public function handle()
    {
        $today = now()->toDateString();

        // 🧠 إلغاء التمارين
        MemberRoutine::where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->update([
                'status' => 'archived',
            ]);

        // 🧠 إلغاء الوجبات
        MemberMeal::where('is_active', 1)
            ->whereDate('end_date', '<', $today)
            ->update([
                'is_active' => 0,
            ]);

        // 🧠 تحديث الاشتراك
        MemberProfile::whereNotNull('membership_expires_at')
            ->whereDate('membership_expires_at', '<', $today)
            ->update([
                // 'membership_tier' => null, // اختياري
            ]);

        $this->info('Expired plans processed');
    }
}
