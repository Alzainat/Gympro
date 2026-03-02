<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use App\Helpers\AdminLogger;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected array $oldValues = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // ✅ خزّن القيم القديمة للّوغ
        $this->oldValues = $this->record->toArray();

        // ✅ نعبّي role من profile
        $data['role'] = $this->record->profile?->role ?? 'user';

        return $data;
    }

    protected function afterSave(): void
    {
        // ✅ تحديث/إنشاء Profile
        $this->record->profile()->updateOrCreate(
            ['user_id' => $this->record->id],
            [
                'role'      => $this->data['role'],
                'full_name' => $this->record->name,
            ]
        );

        // ✅ تسجيل تعديل user في logs
        AdminLogger::log(
            action: 'update_user',
            targetType: 'User',
            targetId: $this->record->id,
            oldValues: $this->oldValues,
            newValues: $this->record->fresh()->toArray(),
        );
    }

    /**
     * ✅ بعد الحفظ يرجع على صفحة list users
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
