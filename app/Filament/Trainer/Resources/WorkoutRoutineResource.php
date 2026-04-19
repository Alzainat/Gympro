<?php

namespace App\Filament\Trainer\Resources;

use App\Filament\Trainer\Resources\WorkoutRoutineResource\Pages;
use App\Models\WorkoutRoutine;
use App\Models\Profile;
use App\Models\MemberRoutine;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\DatePicker;

class WorkoutRoutineResource extends Resource
{
    protected static ?string $model = WorkoutRoutine::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Workout Routines';
    protected static ?string $navigationGroup = 'Training';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('difficulty_level')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->date(),
            ])
            ->actions([
                Action::make('assign')
                    ->label('Assign to Member')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Assign Workout Routine')
                    ->modalSubmitActionLabel('Assign')
                    ->modalWidth('4xl')
                    ->form([
                        Radio::make('member_id')
                            ->label('Choose Member')
                            ->options(function () {
                                return Profile::query()
                                    ->where('trainer_id', auth()->user()->profile->id)
                                    ->where('role', 'member')
                                    ->pluck('full_name', 'id')
                                    ->toArray();
                            })
                            ->descriptions(function () {
                                return Profile::query()
                                    ->where('trainer_id', auth()->user()->profile->id)
                                    ->where('role', 'member')
                                    ->get()
                                    ->mapWithKeys(function ($member) {
                                        return [
                                            $member->id => 'Member ID: ' . $member->id,
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->columns(2)
                            ->required(),

                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->default(now())
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->minDate(fn (callable $get) => $get('start_date') ?: now())
                            ->rule('after_or_equal:start_date'),
                    ])
                    ->action(function (array $data, WorkoutRoutine $record) {
                        MemberRoutine::updateOrCreate(
                            [
                                'member_id'  => $data['member_id'],
                                'routine_id' => $record->id,
                                'source'     => 'trainer',
                            ],
                            [
                                'assigned_by' => auth()->user()->profile->id,
                                'start_date'  => $data['start_date'],
                                'end_date'    => $data['end_date'],
                                'source'      => 'trainer',
                                'status'      => 'active',
                            ]
                        );
                    })
                    ->successNotificationTitle('Routine assigned successfully'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkoutRoutines::route('/'),
        ];
    }
}
