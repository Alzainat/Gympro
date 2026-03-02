<?php

namespace App\Filament\Trainer\Resources;

use App\Filament\Trainer\Resources\MemberResource\Pages;
use App\Models\Profile;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class MemberResource extends Resource
{
    protected static ?string $model = Profile::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'My Members';
    protected static ?string $pluralModelLabel = 'Members';
    protected static ?string $navigationGroup = 'Members Management';

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $trainerProfileId = $user?->profile?->id;

        return parent::getEloquentQuery()
            ->with(['user', 'healthProfile'])
            ->where('role', 'member')
            ->when($trainerProfileId, fn (Builder $q) => $q->where('trainer_id', $trainerProfileId))
            ->when(!$trainerProfileId, fn (Builder $q) => $q->whereRaw('1=0'));
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('healthProfile.height')
                    ->label('Height (cm)')
                    ->formatStateUsing(fn ($state) => $state ? (string) $state : '-'),

                Tables\Columns\TextColumn::make('healthProfile.weight')
                    ->label('Weight (kg)')
                    ->formatStateUsing(fn ($state) => $state ? (string) $state : '-'),

                Tables\Columns\TextColumn::make('healthProfile.body_fat_percentage')
                    ->label('Body Fat (%)')
                    ->formatStateUsing(fn ($state) => $state ? (string) $state : '-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined At')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\Action::make('chat')
                    ->label('Chat')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (Profile $record) => url("/trainer/member-chat/{$record->id}")),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
        ];
    }
}
