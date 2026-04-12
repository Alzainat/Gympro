<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AdminLogResource\Pages;
use App\Models\AdminLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdminLogResource extends Resource
{
    protected static ?string $model = AdminLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Admin Logs';

    public static function canViewAny(): bool
    {
        return auth()->user()?->profile?->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('admin.full_name')
                    ->label('Admin')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_type')
                    ->badge()
                    ->label('Target'),

                Tables\Columns\TextColumn::make('target_id')
                    ->label('ID'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Date')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'create_user'    => 'Create User',
                        'update_user'    => 'Update User',
                        'delete_user'    => 'Delete User',
                        'create_payment' => 'Create Payment',
                        'update_payment' => 'Update Payment',
                        'delete_payment' => 'Delete Payment',
                        'create_meal'    => 'Create Meal',
                        'update_meal'    => 'Update Meal',
                        'delete_meal'    => 'Delete Meal',
                    ]),

                Tables\Filters\SelectFilter::make('target_type')
                    ->options([
                        'User'    => 'User',
                        'Trainer' => 'Trainer',
                        'Admin'   => 'Admin',
                        'Payment' => 'Payment',
                        'Meal'    => 'Meal',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminLogs::route('/'),
        ];
    }
}
