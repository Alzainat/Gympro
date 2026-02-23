<?php

namespace App\Filament\Trainer\Resources;

use App\Filament\Trainer\Resources\WorkoutRoutineResource\Pages;
use App\Models\WorkoutRoutine;
use App\Models\Profile;
use App\Models\MemberRoutine;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;

class WorkoutRoutineResource extends Resource
{
    protected static ?string $model = WorkoutRoutine::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Workout Routines';
    protected static ?string $navigationGroup = 'Training';

    /**
     * 🧑‍🏫 المدرب يشوف بس الروتينات تبعته
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('creator_id', auth()->user()->profile->id);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            TextInput::make('name')
                ->label('Routine Name')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->rows(3),

            Select::make('difficulty_level')
                ->options([
                    'beginner' => 'Beginner',
                    'intermediate' => 'Intermediate',
                    'advanced' => 'Advanced',
                ])
                ->required(),

            Toggle::make('is_public')
                ->label('Public Routine')
                ->default(true),

            /**
             * 🔥 Exercises inside routine (Hybrid Advanced)
             */
            Repeater::make('routineExercises')
                ->label('Exercises')
                ->relationship()
                ->schema([
                    Select::make('exercise_id')
                        ->label('Exercise')
                        ->relationship('exercise', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->suffixAction(
                            \Filament\Forms\Components\Actions\Action::make('addExercise')
                                ->icon('heroicon-o-plus-circle')
                                ->tooltip('Add new exercise')
                                ->modalHeading('Add New Exercise')
                                ->modalSubmitActionLabel('Create')
                                ->form([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),

                                    Textarea::make('description'),

                                    TextInput::make('target_muscle')
                                        ->label('Target Muscle'),

                                    Select::make('difficulty')
                                        ->options([
                                            'beginner' => 'Beginner',
                                            'intermediate' => 'Intermediate',
                                            'advanced' => 'Advanced',
                                        ]),

                                    TextInput::make('video_url')
                                        ->label('Video URL')
                                        ->url(),
                                ])
                                ->action(function (array $data, callable $set) {
                                    $exercise = \App\Models\Exercise::create($data);

                                    // 👈 اختيار التمرين الجديد مباشرة
                                    $set('exercise_id', $exercise->id);
                                })
                        ),

                    TextInput::make('sets')
                        ->numeric()
                        ->default(3)
                        ->required(),

                    TextInput::make('reps')
                        ->numeric()
                        ->default(12)
                        ->required(),

                    TextInput::make('rest_seconds')
                        ->numeric()
                        ->default(60),

                    TextInput::make('order_index')
                        ->numeric()
                        ->label('Order'),

                    Textarea::make('notes')
                        ->rows(2),
                ])
                ->orderable('order_index')
                ->collapsed()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('difficulty_level')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                /**
                 * 🔗 Assign Workout Routine to Member
                 */
                Action::make('assign')
                    ->label('Assign to Member')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Assign Workout Routine')
                    ->modalSubmitActionLabel('Assign')
                    ->form([
                        Select::make('member_id')
                            ->label('Member')
                            ->options(function () {
                                return Profile::query()
                                    ->where('trainer_id', auth()->user()->profile->id)
                                    ->where('role', 'member')
                                    ->pluck('full_name', 'id');
                            })
                            ->searchable()
                            ->required(),

                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data, WorkoutRoutine $record) {
                        MemberRoutine::updateOrCreate(
                            [
                                'member_id'  => $data['member_id'],
                                'routine_id' => $record->id,
                            ],
                            [
                                'assigned_by' => auth()->user()->profile->id,
                                'start_date'  => $data['start_date'],
                                'status'      => 'active',
                            ]
                        );
                    })
                    ->successNotificationTitle('Routine assigned successfully'),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkoutRoutines::route('/'),
            'create' => Pages\CreateWorkoutRoutine::route('/create'),
            'edit'   => Pages\EditWorkoutRoutine::route('/{record}/edit'),
        ];
    }
}