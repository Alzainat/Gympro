<?php

namespace App\Filament\Trainer\Resources;

use App\Filament\Trainer\Resources\MealResource\Pages;
use App\Models\Meal;
use App\Models\Profile;
use App\Models\MemberMeal;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;

class MealResource extends Resource
{
    protected static ?string $model = Meal::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';
    protected static ?string $navigationGroup = 'Nutrition';
    protected static ?string $navigationLabel = 'Meals';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('trainer_id', auth()->user()->profile->id);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            Textarea::make('description'),

            Select::make('category')
                ->options([
                    'bulking' => 'Bulking',
                    'weight_gain' => 'Weight Gain',
                    'healthy' => 'Healthy',
                    'cutting' => 'Cutting',
                ])
                ->required(),

            TextInput::make('calories')->numeric(),
            TextInput::make('protein')->numeric(),
            TextInput::make('carbs')->numeric(),
            TextInput::make('fats')->numeric(),

            FileUpload::make('image_url')
                ->label('Meal Image')
                ->image()
                ->disk('public')
                ->directory('meals')
                ->visibility('public')
                ->imageEditor()
                ->nullable(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                

                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('calories'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('assign')
                    ->label('Assign to Member')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Select::make('member_id')
                            ->label('Member')
                            ->options(fn () =>
                                Profile::where('trainer_id', auth()->user()->profile->id)
                                    ->where('role', 'member')
                                    ->pluck('full_name', 'id')
                            )
                            ->searchable()
                            ->required(),

                        Select::make('day_of_week')
                            ->label('Day')
                            ->options([
                                'Monday' => 'Monday',
                                'Tuesday' => 'Tuesday',
                                'Wednesday' => 'Wednesday',
                                'Thursday' => 'Thursday',
                                'Friday' => 'Friday',
                                'Saturday' => 'Saturday',
                                'Sunday' => 'Sunday',
                            ])
                            ->required(),

                        Select::make('meal_time')
                            ->label('Meal Time')
                            ->options([
                                'breakfast' => 'Breakfast',
                                'lunch' => 'Lunch',
                                'dinner' => 'Dinner',
                                'snack' => 'Snack',
                            ])
                            ->required(),

                        DatePicker::make('start_date')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data, Meal $record) {
                        MemberMeal::updateOrCreate(
                            [
                                'member_id'   => $data['member_id'],
                                'meal_id'     => $record->id,
                                'day_of_week' => $data['day_of_week'],
                                'meal_time'   => $data['meal_time'],
                            ],
                            [
                                'assigned_by' => auth()->user()->profile->id,
                                'start_date'  => $data['start_date'],
                                'is_active'   => true,
                            ]
                        );
                    })
                    ->successNotificationTitle('Meal assigned successfully'),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMeals::route('/'),
            'create' => Pages\CreateMeal::route('/create'),
            'edit'   => Pages\EditMeal::route('/{record}/edit'),
        ];
    }
}
