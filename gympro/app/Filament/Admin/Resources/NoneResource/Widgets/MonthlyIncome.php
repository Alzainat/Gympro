<?php

namespace App\Filament\Admin\Resources\NoneResource\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyIncome extends ChartWidget
{
    protected static ?string $heading = 'Monthly Income';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();

        // جلب الدخل اليومي
        $payments = Payment::query()
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(payment_date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = [];
        $data   = [];

        // تعبئة كل أيام الشهر (حتى الأيام بدون دخل)
        $period = \Carbon\CarbonPeriod::create($startOfMonth, $endOfMonth);

        foreach ($period as $date) {
            $day = $date->format('Y-m-d');

            $labels[] = $date->format('d');

            $paymentForDay = $payments->firstWhere('day', $day);
            $data[] = $paymentForDay ? (float) $paymentForDay->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Income (JOD)',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // line | bar
    }
}