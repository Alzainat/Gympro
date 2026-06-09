<?php

namespace App\Console\Commands;

use App\Models\SessionBooking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReleaseExpiredTrainerSubscriptions extends Command
{
    protected $signature = 'subscriptions:release-expired';

    protected $description = 'Remove trainer subscription after session ends';

    public function handle()
    {
        $bookings = SessionBooking::query()
            ->with(['session', 'member'])
            ->where('status', 'booked')
            ->get();

        foreach ($bookings as $booking) {
            $session = $booking->session;
            $member = $booking->member;

            if (!$session || !$member) {
                continue;
            }

            $sessionEndTime = Carbon::parse($session->session_date)
                ->addMinutes($session->duration_minutes);

            // إذا الحصة لسا ما خلصت، لا تعمل شيء
            if ($sessionEndTime->gt(now())) {
                continue;
            }

            // تأكد أنه اللاعب لسا مربوط بنفس مدرب هاي الحصة
            if ((int) $member->trainer_id === (int) $session->trainer_id) {
                $member->trainer_id = null;
                $member->save();
            }
        }

        $this->info('Expired trainer subscriptions released successfully.');
    }
}
