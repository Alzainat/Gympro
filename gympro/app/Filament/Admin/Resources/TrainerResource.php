<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TrainerResource\Pages;
use App\Filament\Admin\Resources\TrainerResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainerResource extends Resource
{
    protected static ?string $model = User::class;
    

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * 🔐 Admin only can see this resource
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->profile?->role === 'admin';
    }

    /**
     * 🎯 Get trainers only
     * (profiles.role = trainer)
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('profile', function (Builder $query) {
                $query->where('role', 'trainer');
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
            ->label('Name')
            ->required(),

        Forms\Components\TextInput::make('email')
            ->email()
            ->required()
            ->unique('users', 'email'),

        Forms\Components\TextInput::make('password')
    ->password()
    ->required()
    ->minLength(8)
    ->dehydrateStateUsing(fn ($state) => $state)
    ->dehydrated(fn ($state) => filled($state)),

        Forms\Components\TextInput::make('full_name')
            ->label('Full Name')
            ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // trainer table columns
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTrainers::route('/'),
            'create' => Pages\CreateTrainer::route('/create'),
            'edit'   => Pages\EditTrainer::route('/{record}/edit'),
        ];
    }
}