<?php

namespace App\Filament\Trainer\Resources;

use App\Filament\Trainer\Resources\TrainerAvailabilityResource\Pages;
use App\Models\TrainerAvailability;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class TrainerAvailabilityResource extends Resource
{
    protected static ?string $model = TrainerAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Availability';
    protected static ?string $navigationGroup = 'Schedule';

    /**
     * 🧑‍🏫 المدرب يشوف بس availability تبعته
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('trainer_id', auth()->user()->profile->id);
    }

    /**
     * 📝 Form
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('day_of_week')
                ->options([
                    'Monday'    => 'Monday',
                    'Tuesday'   => 'Tuesday',
                    'Wednesday' => 'Wednesday',
                    'Thursday'  => 'Thursday',
                    'Friday'    => 'Friday',
                    'Saturday'  => 'Saturday',
                    'Sunday'    => 'Sunday',
                ])
                ->required(),

            Forms\Components\TimePicker::make('start_time')
                ->seconds(false)
                ->visible(fn ($get) => $get('is_available')),

            Forms\Components\TimePicker::make('end_time')
                ->seconds(false)
                ->visible(fn ($get) => $get('is_available')),

            Forms\Components\Toggle::make('is_available')
                ->label('Available')
                ->default(true),
        ]);
    }

    /**
     * 📊 Table
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Day')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('From')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('To')
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('is_available')
                    ->label('Status')
                    ->colors([
                        'success' => true,
                        'danger'  => false,
                    ])
                    ->formatStateUsing(fn ($state) =>
                        $state ? 'Available' : 'Day Off'
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('toggleDay')
                    ->label(fn ($record) =>
                        $record->is_available ? 'Disable Day' : 'Enable Day'
                    )
                    ->icon(fn ($record) =>
                        $record->is_available
                            ? 'heroicon-o-x-circle'
                            : 'heroicon-o-check-circle'
                    )
                    ->color(fn ($record) =>
                        $record->is_available ? 'danger' : 'success'
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if ($record->is_available) {
                            // ❌ سكّر اليوم كامل
                            $record->update([
                                'is_available' => false,
                                'start_time'   => null,
                                'end_time'     => null,
                            ]);
                        } else {
                            // ✅ فعّل اليوم (الوقت يحدد لاحقًا)
                            $record->update([
                                'is_available' => true,
                            ]);
                        }
                    }),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /**
     * 📄 Pages
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainerAvailabilities::route('/'),
            'create' => Pages\CreateTrainerAvailability::route('/create'),
            'edit' => Pages\EditTrainerAvailability::route('/{record}/edit'),
        ];
    }
}
