<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Helpers\AdminLogger;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function canViewAny(): bool
    {
        return auth()->user()?->profile?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->dehydrated(true),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->unique('users', 'email'),

            Forms\Components\TextInput::make('password')
                ->password()
                ->required()
                ->minLength(8)
                ->dehydrateStateUsing(fn ($state) => bcrypt($state)),

            Forms\Components\Select::make('role')
                ->label('Role')
                ->options([
                    'admin'   => 'Admin',
                    'trainer' => 'Trainer',
                    'user'    => 'User',
                ])
                ->required()
                ->default('user')
                ->dehydrated(false), // ⚠️ لأنه مش موجود بجدول users
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
            ])

            // ✅ ما عاد السطر يفتح edit عند الضغط عليه
            ->recordUrl(null)

            // ✅ Actions مثل الصورة: View | Edit | Delete
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete user')
                    ->modalDescription('Are you sure you want to delete this user? This action cannot be undone.')
                    ->before(function ($record) {
                        // ✅ log قبل الحذف
                        AdminLogger::log(
                            action: 'delete_user',
                            targetType: 'User',
                            targetId: $record->id,
                            oldValues: $record->toArray(),
                            newValues: null,
                        );

                        // ✅ لتجنب مشاكل FK / orphan profile
                        $record->profile()?->delete();
                    })
                    ->successNotificationTitle('User deleted'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
