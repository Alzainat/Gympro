<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;
use App\Helpers\AdminLogger;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    /**
     * 🧠 After payment created
     */
    protected function afterCreate(): void
    {
        AdminLogger::log(
            action: 'create_payment',
            targetType: 'Payment',
            targetId: $this->record->id,
            newValues: $this->record->toArray(),
        );
    }
}