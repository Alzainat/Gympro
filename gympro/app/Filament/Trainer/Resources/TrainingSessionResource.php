<?php

namespace App\Filament\Trainer\Resources;

use App\Filament\Trainer\Resources\TrainingSessionResource\Pages;
use App\Models\TrainingSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingSessionResource extends Resource
{
    protected static ?string $model = TrainingSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'My Sessions';
    protected static ?string $pluralModelLabel = 'Sessions';
    protected static ?string $navigationGroup = 'Schedule';

    public static function getEloquentQuery(): Builder
    {
        $trainerProfileId = auth()->user()?->profile?->id;

        return parent::getEloquentQuery()
            ->when($trainerProfileId, fn (Builder $q) => $q->where('trainer_id', $trainerProfileId))
            ->when(!$trainerProfileId, fn (Builder $q) => $q->whereRaw('1=0'));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\DateTimePicker::make('session_date')
                ->label('Session Date & Time')
                ->required(),

            Forms\Components\TextInput::make('duration_minutes')
                ->numeric()
                ->required()
                ->default(60)
                ->minValue(10),

            Forms\Components\TextInput::make('max_participants')
                ->numeric()
                ->required()
                ->default(10)
                ->minValue(1),

            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('session_date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')->label('Min')->sortable(),
                Tables\Columns\TextColumn::make('max_participants')->label('Cap')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->since(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle')
                    ->label(fn (TrainingSession $record) => $record->is_active ? 'Disable' : 'Enable')
                    ->requiresConfirmation()
                    ->action(function (TrainingSession $record) {
                        $record->is_active = !$record->is_active;
                        $record->save();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // ✅ اجبار trainer_id يكون للمدرب الحالي
        $data['trainer_id'] = auth()->user()?->profile?->id;
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingSessions::route('/'),
            'create' => Pages\CreateTrainingSession::route('/create'),
            'edit' => Pages\EditTrainingSession::route('/{record}/edit'),
        ];
    }
}
