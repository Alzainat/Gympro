<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Helpers\AdminLogger;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected array $oldData = [];

    protected function beforeSave(): void
    {
        $this->oldData = $this->record->toArray();
    }

    protected function afterSave(): void
    {
        AdminLogger::log(
            action: 'update_payment',
            targetType: 'Payment',
            targetId: $this->record->id,
            oldValues: $this->oldData,
            newValues: $this->record->fresh()->toArray(),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    AdminLogger::log(
                        action: 'delete_payment',
                        targetType: 'Payment',
                        targetId: $this->record->id,
                        oldValues: $this->record->toArray(),
                    );
                }),
        ];
    }
}
