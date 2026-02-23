<?php

namespace App\Filament\Admin\Resources\AdminLogResource\Pages;

use App\Filament\Admin\Resources\AdminLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminLog extends CreateRecord
{
    protected static string $resource = AdminLogResource::class;
}
