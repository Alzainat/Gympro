<?php

namespace App\Filament\Trainer\Resources;

use App\Filament\Trainer\Resources\TrainingSessionResource\Pages;
use App\Models\TrainingSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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

    /**
     * Check overlap for a trainer.
     * Overlap rule:
     * existing_start < new_end AND existing_end > new_start
     *
     * Note: This SQL uses DATE_ADD (MySQL/MariaDB).
     */
    protected static function hasOverlapForTrainer(
        int $trainerId,
        Carbon $newStart,
        int $durationMinutes,
        ?int $ignoreRecordId = null
    ): bool {
        $newEnd = (clone $newStart)->addMinutes($durationMinutes);

        $query = TrainingSession::query()
            ->where('trainer_id', $trainerId)
            ->when($ignoreRecordId, fn ($q) => $q->whereKeyNot($ignoreRecordId))
            ->where('session_date', '<', $newEnd) // existing_start < new_end
            ->whereRaw(
                'DATE_ADD(session_date, INTERVAL duration_minutes MINUTE) > ?',
                [$newStart->toDateTimeString()] // existing_end > new_start
            );

        return $query->exists();
    }

    protected static function computeEndTime(TrainingSession $record): Carbon
    {
        return Carbon::parse($record->session_date)->addMinutes((int) $record->duration_minutes);
    }

    protected static function isExpired(TrainingSession $record): bool
    {
        return now()->greaterThanOrEqualTo(self::computeEndTime($record));
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
                ->required()
                ->seconds(false)
                ->rule(function (Get $get, ?TrainingSession $record) {
                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                        $trainerProfileId = auth()->user()?->profile?->id;
                        if (!$trainerProfileId) {
                            $fail('Trainer profile not found.');
                            return;
                        }

                        if (!$value) {
                            return;
                        }

                        $duration = (int) ($get('duration_minutes') ?? 60);
                        $start = Carbon::parse($value);

                        $ignoreId = $record?->getKey();

                        if (self::hasOverlapForTrainer($trainerProfileId, $start, $duration, $ignoreId)) {
                            $fail('This session time overlaps with another session you already have.');
                        }
                    };
                }),

            Forms\Components\TextInput::make('duration_minutes')
                ->numeric()
                ->required()
                ->default(60)
                ->minValue(10)
                ->rule(function (Get $get, ?TrainingSession $record) {
                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                        $trainerProfileId = auth()->user()?->profile?->id;
                        if (!$trainerProfileId) {
                            $fail('Trainer profile not found.');
                            return;
                        }

                        $sessionDate = $get('session_date');
                        if (!$sessionDate || !$value) {
                            return;
                        }

                        $duration = (int) $value;
                        $start = Carbon::parse($sessionDate);

                        $ignoreId = $record?->getKey();

                        if (self::hasOverlapForTrainer($trainerProfileId, $start, $duration, $ignoreId)) {
                            $fail('Duration causes overlap with another existing session.');
                        }
                    };
                }),

            Forms\Components\TextInput::make('max_participants')
                ->numeric()
                ->required()
                ->default(10)
                ->minValue(1),

            Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->helperText('If expired, it will still show as Expired automatically.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),

                Tables\Columns\TextColumn::make('session_date')
                    ->label('Start')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End')
                    ->state(function (TrainingSession $record) {
                        return self::computeEndTime($record)->toDateTimeString();
                    })
                    ->dateTime(),

                Tables\Columns\TextColumn::make('duration_minutes')->label('Min')->sortable(),
                Tables\Columns\TextColumn::make('max_participants')->label('Cap')->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->state(function (TrainingSession $record) {
                        if (self::isExpired($record)) {
                            return 'Expired';
                        }
                        return $record->is_active ? 'Active' : 'Disabled';
                    })
                    ->color(function (string $state) {
                        return match ($state) {
                            'Expired' => 'gray',
                            'Active' => 'success',
                            'Disabled' => 'warning',
                            default => 'secondary',
                        };
                    }),

                Tables\Columns\TextColumn::make('created_at')->dateTime()->since(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('toggle')
                    ->label(fn (TrainingSession $record) => $record->is_active ? 'Disable' : 'Enable')
                    ->requiresConfirmation()
                    ->disabled(fn (TrainingSession $record) => self::isExpired($record)) // ما بسمح تبدّل بعد ما تصير Expired
                    ->action(function (TrainingSession $record) {
                        $record->is_active = !$record->is_active;
                        $record->save();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // اجبار trainer_id يكون للمدرب الحالي (profile id)
        $data['trainer_id'] = auth()->user()?->profile?->id;

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        // حماية: لا تسمح بتغيير trainer_id من الفورم
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
