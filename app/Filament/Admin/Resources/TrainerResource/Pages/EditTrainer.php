<?php

namespace App\Filament\Admin\Resources\TrainerResource\Pages;

use App\Filament\Admin\Resources\TrainerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainer extends EditRecord
{
    protected static string $resource = TrainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
