<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Payments';

    public static function canViewAny(): bool
    {
        return auth()->user()?->profile?->role === 'admin';
    }

    /* =========================
     | Form (Create / Edit)
     ========================= */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Payment Details')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Member')
                        ->relationship('user', 'full_name')
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->prefix('JOD')
                        ->required(),

                    Forms\Components\Select::make('payment_type')
                        ->options([
                            'membership'       => 'Membership Package',
                            'training_session' => 'Training Session',
                            'class_booking'    => 'Class Booking',
                            'product'          => 'Product',
                            'service'          => 'Service',
                            'other'            => 'Other',
                        ])
                        ->required(),

                    Forms\Components\Select::make('payment_method')
                        ->options([
                            'cash'           => 'Cash',
                            'credit_card'    => 'Credit Card',
                            'debit_card'     => 'Debit Card',
                            'bank_transfer'  => 'Bank Transfer',
                            'digital_wallet' => 'Digital Wallet',
                        ])
                        ->default('cash')
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'   => 'Pending',
                            'completed' => 'Completed',
                            'failed'    => 'Failed',
                            'refunded'  => 'Refunded',
                        ])
                        ->default('completed')
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Transaction Info')
                ->icon('heroicon-o-receipt-percent')
                ->schema([
                    Forms\Components\TextInput::make('transaction_id')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('reference_id')
                        ->numeric(),

                    Forms\Components\DateTimePicker::make('payment_date')
                        ->default(now()),
                ])
                ->columns(2),

            Forms\Components\Section::make('Notes')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->rows(3),
                ]),
        ]);
    }

    /* =========================
     | Table (List)
     ========================= */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Member')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_type')
                    ->badge()
                    ->colors([
                        'primary' => 'membership',
                        'success' => 'training_session',
                        'warning' => 'class_booking',
                        'info'    => 'product',
                        'gray'    => 'service',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('amount')
                    ->money('JOD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger'  => 'failed',
                        'gray'    => 'refunded',
                    ]),

                Tables\Columns\TextColumn::make('payment_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options([
                        'membership'       => 'Membership',
                        'training_session' => 'Training Session',
                        'class_booking'    => 'Class Booking',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'pending'   => 'Pending',
                        'failed'    => 'Failed',
                        'refunded'  => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}