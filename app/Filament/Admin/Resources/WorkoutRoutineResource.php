<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WorkoutRoutineResource\Pages;
use App\Models\WorkoutRoutine;
use App\Models\Exercise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
        return $form->schema([
            Section::make('Routine Information')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label('Routine Name')
                                ->required()
                                ->maxLength(255),

                            Select::make('difficulty_level')
                                ->label('Difficulty Level')
                                ->options([
                                    'beginner'     => 'Beginner',
                                    'intermediate' => 'Intermediate',
                                    'advanced'     => 'Advanced',
                                ])
                                ->required(),
                        ]),

                    Textarea::make('description')
                        ->rows(3),
                ])
                ->collapsible(),

            Section::make('Routine Builder')
                ->schema([
                    ToggleButtons::make('selected_day')
                        ->label('Choose Day')
                        ->options([
                            'Saturday'  => 'Saturday',
                            'Friday'    => 'Friday',
                            'Thursday'  => 'Thursday',
                            'Wednesday' => 'Wednesday',
                            'Tuesday'   => 'Tuesday',
                            'Monday'    => 'Monday',
                            'Sunday'    => 'Sunday',
                        ])
                        ->inline()
                        ->live()
                        ->default('Sunday')
                        ->dehydrated(false)
                        ->grouped()
                        ->columnSpanFull()
                        ->required(),

                    ToggleButtons::make('selected_muscle')
                        ->label('Choose Muscle Group')
                        ->options([
                            'shoulders' => 'Shoulders',
                            'back'      => 'Back',
                            'chest'     => 'Chest',
                            'legs'      => 'Legs',
                            'arms'      => 'Arms',
                            'core'      => 'Core',
                        ])
                        ->inline()
                        ->live()
                        ->default('chest')
                        ->dehydrated(false)
                        ->grouped()
                        ->columnSpanFull()
                        ->required(),

                    Repeater::make('routineExercises')
                        ->label('Exercises')
                        ->columnSpanFull()
                        ->defaultItems(0)
                        ->schema([
                            Hidden::make('day_of_week')
                                ->default(fn (Get $get) => $get('../../selected_day')),

                            Hidden::make('muscle_group')
                                ->default(fn (Get $get) => $get('../../selected_muscle')),

                            TextInput::make('day_label')
                                ->label('Day')
                                ->default(fn (Get $get) => $get('../../selected_day'))
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('muscle_label')
                                ->label('Muscle Group')
                                ->default(fn (Get $get) => $get('../../selected_muscle'))
                                ->disabled()
                                ->dehydrated(false),

                            Select::make('exercise_id')
                                ->label('Exercise')
                                ->options(function (Get $get) {
                                    $selectedMuscle = $get('../../selected_muscle');

                                    return Exercise::query()
                                        ->when($selectedMuscle, fn ($q) => $q->where('target_muscle', $selectedMuscle))
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false)
                                ->createOptionModalHeading('Add New Exercise')
                                ->createOptionForm([
                                    Section::make('Basic Information')
                                        ->schema([
                                            Grid::make(2)
                                                ->schema([
                                                    TextInput::make('name')
                                                        ->label('Exercise Name')
                                                        ->required()
                                                        ->maxLength(255),

                                                    Select::make('target_muscle')
                                                        ->label('Target Muscle')
                                                        ->options([
                                                            'shoulders' => 'Shoulders',
                                                            'back'      => 'Back',
                                                            'chest'     => 'Chest',
                                                            'legs'      => 'Legs',
                                                            'arms'      => 'Arms',
                                                            'core'      => 'Core',
                                                        ])
                                                        ->default(fn (Get $get) => $get('../../../../selected_muscle'))
                                                        ->required(),

                                                    Select::make('difficulty')
                                                        ->label('Difficulty')
                                                        ->options([
                                                            'beginner'     => 'Beginner',
                                                            'intermediate' => 'Intermediate',
                                                            'advanced'     => 'Advanced',
                                                        ])
                                                        ->default('beginner'),

                                                    TextInput::make('equipment')
                                                        ->label('Equipment')
                                                        ->maxLength(255),
                                                ]),

                                            Textarea::make('description')
                                                ->label('Description')
                                                ->rows(4)
                                                ->columnSpanFull(),
                                        ])
                                        ->collapsible(),

                                    Section::make('Media')
                                        ->schema([
                                            FileUpload::make('image_url')
                                                ->label('Exercise Image')
                                                ->image()
                                                ->disk('public')
                                                ->directory('exercises')
                                                ->visibility('public')
                                                ->imageEditor()
                                                ->nullable(),
                                        ])
                                        ->collapsible(),
                                ])
                                ->createOptionUsing(function (array $data) {
                                    return Exercise::create($data)->id;
                                }),

                            Grid::make(3)
                                ->schema([
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
                                ]),

                            TextInput::make('order_index')
                                ->numeric()
                                ->default(1),

                            Textarea::make('notes')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->itemLabel(function (array $state): ?string {
                            $day = $state['day_of_week'] ?? null;
                            $muscle = $state['muscle_group'] ?? null;

                            return $day && $muscle ? "{$day} - {$muscle}" : 'Exercise';
                        })
                        ->addActionLabel('Add Exercise'),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
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
                Tables\Actions\EditAction::make(),
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
