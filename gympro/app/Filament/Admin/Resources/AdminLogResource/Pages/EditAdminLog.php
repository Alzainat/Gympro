<?php

namespace App\Filament\Admin\Resources\AdminLogResource\Pages;

use App\Filament\Admin\Resources\AdminLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdminLog extends EditRecord
{
    protected static string $resource = AdminLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
