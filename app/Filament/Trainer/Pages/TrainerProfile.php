<?php

namespace App\Filament\Trainer\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TrainerProfile extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?string $title = 'My Profile';

    protected static string $view = 'filament.trainer.pages.trainer-profile';

    public ?array $data = [];

    public function mount(): void
    {
        $profile = Auth::user()->profile;

        abort_if(! $profile || $profile->role !== 'trainer', 403);

        $trainerProfile = $profile->trainerProfile()->firstOrCreate([
            'profile_id' => $profile->id,
        ]);

        $this->form->fill([
            'full_name' => $profile->full_name,
            'avatar_url' => $profile->avatar_url,
            'bio' => $profile->bio,
            'hourly_rate' => $trainerProfile->hourly_rate,
            'specializations' => $trainerProfile->specializations ?? [],
            'is_available' => $trainerProfile->is_available,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Public Trainer Profile')
                    ->description('These details will appear on the user side.')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar_url')
                            ->label('Profile Image')
                            ->image()
                            ->disk('public')
                            ->directory('trainer-avatars')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048),

                        Forms\Components\TextInput::make('full_name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('bio')
                            ->label('Bio')
                            ->rows(4)
                            ->maxLength(1000),

                        Forms\Components\TextInput::make('hourly_rate')
                            ->label('Hourly Rate')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\TagsInput::make('specializations')
                            ->label('Specializations')
                            ->placeholder('cutting, fat loss, nutrition'),

                        Forms\Components\Toggle::make('is_available')
                            ->label('Available')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $profile = Auth::user()->profile;

        abort_if(! $profile || $profile->role !== 'trainer', 403);

        $data = $this->form->getState();

        $profile->update([
            'full_name' => $data['full_name'],
            'avatar_url' => $data['avatar_url'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);

        $profile->trainerProfile()->updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'hourly_rate' => $data['hourly_rate'] ?? null,
                'specializations' => $data['specializations'] ?? [],
                'is_available' => $data['is_available'] ?? true,
            ]
        );

        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }
}
