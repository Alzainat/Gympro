<?php

namespace App\Filament\Admin\Resources\AdminLogResource\Pages;

use App\Filament\Admin\Resources\AdminLogResource;
use App\Models\AdminLog;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListAdminLogs extends ListRecords
{
    protected static string $resource = AdminLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    protected function exportCsv(): StreamedResponse
    {
        $fileName = 'admin-logs-' . now()->format('Y-m-d') . '.csv';

        $logs = AdminLog::query()
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // CSV Header
            fputcsv($handle, [
                'Admin',
                'Action',
                'Target Type',
                'Target ID',
                'IP Address',
                'User Agent',
                'Date',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->admin?->full_name,
                    $log->action,
                    $log->target_type,
                    $log->target_id,
                    $log->ip_address,
                    $log->user_agent,
                    $log->created_at,
                ]);
            }

            fclose($handle);
        }, $fileName);
    }
}