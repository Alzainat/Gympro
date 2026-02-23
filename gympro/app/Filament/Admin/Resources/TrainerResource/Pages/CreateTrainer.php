<?php

namespace App\Filament\Admin\Resources\TrainerResource\Pages;

use App\Filament\Admin\Resources\TrainerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use App\Models\Profile;

class CreateTrainer extends CreateRecord
{
    protected static string $resource = TrainerResource::class;

    /**
     * قبل إنشاء User
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تشفير كلمة المرور
        $data['password'] = Hash::make($data['password']);

        // ⚠️ full_name مش موجود في users
        // نحذفه قبل الحفظ
        unset($data['full_name']);

        return $data;
    }

    /**
     * بعد إنشاء User
     */
    protected function afterCreate(): void
    {
        Profile::create([
            'user_id'   => $this->record->id,
            'full_name' => $this->data['full_name'],
            'role'      => 'trainer',
        ]);
    }
}