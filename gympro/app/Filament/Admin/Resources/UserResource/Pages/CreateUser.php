<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Profile;
use App\Helpers\AdminLogger;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * 🔁 Redirect to users list after create
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * 🧠 After user created
     */
    protected function afterCreate(): void
    {
        // ✅ إنشاء Profile
        Profile::create([
            'user_id'   => $this->record->id,
            'role'      => $this->data['role'],
            'full_name' => $this->record->name, // اختياري
        ]);

        // ✅ تسجيل العملية في admin_logs
        AdminLogger::log(
            action: 'create_user',
            targetType: 'User',
            targetId: $this->record->id,
            newValues: $this->record->toArray(),
        );
    }
}