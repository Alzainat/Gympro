<?php

namespace App\Filament\Trainer\Resources;

use App\Filament\Trainer\Resources\HealthConditionResource\Pages;
use App\Models\HealthCondition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HealthConditionResource extends Resource
{
    protected static ?string $model = HealthCondition::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'Member Injuries';
    protected static ?string $modelLabel = 'Member Injury';
    protected static ?string $pluralModelLabel = 'Member Injuries';
    protected static ?string $navigationGroup = 'Members';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        $trainerProfileId = auth()->user()?->profile?->id;

        return parent::getEloquentQuery()
            ->with('user')
            ->whereHas('user', function (Builder $query) use ($trainerProfileId) {
                $query->where('trainer_id', $trainerProfileId);
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user.full_name')
                ->label('Member')
                ->disabled()
                ->dehydrated(false),

            Forms\Components\Select::make('type')
                ->options([
                    'injury' => 'Injury',
                    'condition' => 'Condition',
                    'allergy' => 'Allergy',
                ])
                ->required(),

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('severity')
                ->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                ])
                ->required(),

            Forms\Components\Textarea::make('notes')
                ->rows(4)
                ->columnSpanFull(),

            Forms\Components\DateTimePicker::make('detected_at')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'danger' => 'injury',
                        'warning' => 'condition',
                        'success' => 'allergy',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->limit(40)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('detected_at')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'injury' => 'Injury',
                        'condition' => 'Condition',
                        'allergy' => 'Allergy',
                    ]),

                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHealthConditions::route('/'),
            'view' => Pages\ViewHealthCondition::route('/{record}'),
            'edit' => Pages\EditHealthCondition::route('/{record}/edit'),
        ];
    }
}
