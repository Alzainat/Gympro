<?php

namespace App\Filament\Trainer\Resources;

use App\Filament\Trainer\Resources\MealResource\Pages;
use App\Models\Meal;
use App\Models\Profile;
use App\Models\MemberMeal;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;


class MealResource extends Resource
{
    protected static ?string $model = Meal::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';
    protected static ?string $navigationGroup = 'Nutrition';
    protected static ?string $navigationLabel = 'Meals';

    public static function getEloquentQuery(): Builder
    {
        $trainerProfileId = auth()->user()->profile->id;

        return parent::getEloquentQuery()
            ->where(function ($query) use ($trainerProfileId) {
                $query->where('trainer_id', $trainerProfileId)
                    ->orWhereNull('trainer_id');
            });
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

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->getStateUsing(fn ($record) => $record->image_url ? asset('storage/' . $record->image_url) : null)
                    ->square()
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge(),

                Tables\Columns\TextColumn::make('calories'),
            ])
            ->actions([
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

                        ToggleButtons::make('day_of_week')
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
    ->inline() // يخليهم جنب بعض
    ->required(),

                        ToggleButtons::make('meal_time')
    ->label('Meal Time')
    ->options([
        'breakfast' => 'Breakfast',
        'lunch' => 'Lunch',
        'dinner' => 'Dinner',
        'snack' => 'Snack',
    ])
    ->colors([
        'breakfast' => 'warning', // صباح
        'lunch' => 'primary',     // وسط
        'dinner' => 'success',    // مساء
        'snack' => 'gray',        // خفيف
    ])
    ->inline()
    ->required(),

                        TextInput::make('grams')
                            ->label('Grams')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->suffix('g'),
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
                                'grams'       => $data['grams'],
                                'start_date'  => now()->toDateString(),
                                'is_active'   => true,
                            ]
                        );
                    })
                    ->successNotificationTitle('Meal assigned successfully'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeals::route('/'),
        ];
    }
}
