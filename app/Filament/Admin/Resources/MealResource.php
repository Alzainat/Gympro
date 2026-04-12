<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MealResource\Pages;
use App\Models\Meal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Profile;

class MealResource extends Resource
{
    protected static ?string $model = Meal::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';
    protected static ?string $navigationGroup = 'Nutrition';
    protected static ?string $navigationLabel = 'Meals';

    public static function canViewAny(): bool
    {
        return auth()->user()?->profile?->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->profile?->role === 'admin';
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->profile?->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->profile?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Meal Information')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Meal Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('category')
                        ->label('Category')
                        ->options([
                            'bulking'     => 'Bulking',
                            'weight_gain' => 'Weight Gain',
                            'healthy'     => 'Healthy',
                            'cutting'     => 'Cutting',
                        ])
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Nutrition Values')
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Forms\Components\TextInput::make('calories')
                        ->label('Calories')
                        ->numeric()
                        ->required(),

                    Forms\Components\TextInput::make('protein')
                        ->label('Protein (g)')
                        ->numeric()
                        ->step('0.01')
                        ->required(),

                    Forms\Components\TextInput::make('carbs')
                        ->label('Carbs (g)')
                        ->numeric()
                        ->step('0.01')
                        ->required(),

                    Forms\Components\TextInput::make('fats')
                        ->label('Fats (g)')
                        ->numeric()
                        ->step('0.01')
                        ->required(),
                ])
                ->columns(4),

            Forms\Components\Section::make('Ingredients')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Forms\Components\Repeater::make('ingredients')
                        ->label('Ingredients List')
                        ->schema([
                            Forms\Components\TextInput::make('item')
                                ->label('Ingredient')
                                ->required(),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel('Add Ingredient')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Assignment & Image')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\Select::make('trainer_id')
                        ->label('Trainer')
                        ->options(
                            Profile::query()
                                ->where('role', 'trainer')
                                ->pluck('full_name', 'id')
                                ->toArray()
                        )
                        ->searchable()
                        ->nullable()
                        ->placeholder('All Trainers'),

                    Forms\Components\FileUpload::make('image_url')
                        ->label('Meal Image')
                        ->image()
                        ->directory('meals')
                        ->disk('public')
                        ->imageEditor()
                        ->nullable(),

                    Forms\Components\Hidden::make('created_by')
                        ->default(fn () => auth()->user()?->profile?->id),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->getStateUsing(fn ($record) => $record->image_url ? asset('storage/' . $record->image_url) : null)
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Meal Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->sortable(),

                Tables\Columns\TextColumn::make('trainer.full_name')
                    ->label('Trainer')
                    ->formatStateUsing(fn ($state) => $state ?: 'All Trainers')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('calories')
                    ->label('Calories')
                    ->sortable(),

                Tables\Columns\TextColumn::make('protein')
                    ->label('Protein')
                    ->suffix(' g'),

                Tables\Columns\TextColumn::make('carbs')
                    ->label('Carbs')
                    ->suffix(' g'),

                Tables\Columns\TextColumn::make('fats')
                    ->label('Fats')
                    ->suffix(' g'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'bulking'     => 'Bulking',
                        'weight_gain' => 'Weight Gain',
                        'healthy'     => 'Healthy',
                        'cutting'     => 'Cutting',
                    ]),

                Tables\Filters\SelectFilter::make('trainer_id')
                    ->label('Trainer')
                    ->options(
                        Profile::query()
                            ->where('role', 'trainer')
                            ->pluck('full_name', 'id')
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        \App\Helpers\AdminLogger::log(
                            action: 'delete_meal',
                            targetType: 'Meal',
                            targetId: $record->id,
                            oldValues: $record->toArray(),
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->after(function ($records) {
                        foreach ($records as $record) {
                            \App\Helpers\AdminLogger::log(
                                action: 'delete_meal',
                                targetType: 'Meal',
                                targetId: $record->id,
                                oldValues: $record->toArray(),
                            );
                        }
                    }),
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
