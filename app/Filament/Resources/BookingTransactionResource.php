<?php

namespace App\Filament\Resources;

use PDO;
use Filament\Forms;
use Filament\Tables;
use App\Models\Workshop;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Queue\Worker;
use Filament\Resources\Resource;
use App\Models\BookingTransaction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Actions\BulkActionGroup;
use SebastianBergmann\CodeUnit\FunctionUnit;
use Filament\Tables\Actions\DeleteBulkAction;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;
use App\Filament\Resources\BookingTransactionResource\Pages\EditBookingTransaction;
use App\Filament\Resources\BookingTransactionResource\Pages\ListBookingTransactions;
use App\Filament\Resources\BookingTransactionResource\Pages\CreateBookingTransaction;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Product and Price')
                    ->schema([
                        Select::make('workshop_id') 
                        ->relationship('workshop', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $workshop = Workshop::find($state);
                            $set('price', $workshop ? $workshop->price : 0);
                        })
                        ->afterStateHydrated(function ($state, callable $get, callable $set) {
                            $workshop = Workshop::find($state);
                            $set('price', $workshop ? $workshop->price : 0);
                        }),

                        TextInput::make('quantity')
                        ->required()
                        ->numeric()
                        ->prefix("Qty People")
                        ->live()
                        ->afterStateUpdated(Function ($state, callable $get, callable $set) {
                            $price = $get('price');
                            $subTotal = $price * $state;
                            $totalPPN = $subTotal * 0.11;
                            $totalAmount = $subTotal + $totalPPN;
                            
                            $set('total_amount', $totalAmount);
                            $participants = $get('participants') ?? [];
                            $currentCount = count($participants);

                            if ($state > $currentCount) {
                                for($i = $currentCount; $i < $state; $i++) {
                                    $participants[] = ['name' => '', 'occupation' => '', 'email' => ''];
                                }
                            } else {
                                $participants = array_slice($participants, 0, $state);
                            }
                            $set('participants', $participants);
                        })
                        ->afterStateHydrated(function($state, callable $get, callable $set) {
                            $price = $get('price');
                            $subTotal = $price * $state;
                            $totalPPN = $subTotal * 0.11;
                            $totalAmount = $subTotal + $totalPPN;
                            
                            $set('total_amount', $totalAmount);
                        }),

                        TextInput::make('total_amount')
                        ->required()
                        ->numeric()
                        ->prefix('IDR')
                        ->readOnly()
                        ->helperText('Harga sudah include PPN 11%'),
                        Repeater::make('participants')
                        ->schema([
                            Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                ->label('Participants Name')
                                ->required(),
                                
                                TextInput::make('occupation')
                                ->label('Occupation')
                                ->required(),
                                
                                TextInput::make('email')
                                ->label('Email')
                                ->required(),
                            ]),
                        ])
                        ->columns(1)
                        ->label('Participant Detail')
                    ]),
                    Step::make('Customer Information')
                    ->schema([
                        TextInput::make('name')
                        ->required()
                        ->maxLength(225),
                        
                        TextInput::make('email')
                        ->required()
                        ->maxLength(225),
                        
                        TextInput::make('phone')
                        ->required()
                        ->maxLength(225),

                        TextInput::make('customer_bank_name')
                        ->required()
                        ->maxLength(225),
                        
                        TextInput::make('customer_bank_account')
                        ->required()
                        ->maxLength(225),
                        
                        TextInput::make('customer_bank_number')
                        ->required()
                        ->maxLength(225),

                        TextInput::make('booking_trx_id')
                        ->required()
                        ->maxLength(225),
                    ]),
                    Step::make('Payment Information')
                    ->schema([
                        ToggleButtons::make('is_paid')
                        ->label('Apakah sudah membayar?')
                        ->boolean()
                        ->icons([
                            true => 'heroicon-o-pencil',
                            false => 'heroicon-o-clock'
                        ])
                        ->required(),

                        FileUpload::make('proof')
                        ->label('Upload Bukti Pembayaran')
                        ->image()
                        ->required()
                    ])
                ])
                ->columnSpan('full')
                ->columns(1)
                ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('workshop.thumbnail'),

                TextColumn::make('name'),
                
                TextColumn::make('booking_trx_id'),

                IconColumn::make('is_paid')
                ->boolean()
                ->trueColor('succes')
                ->falseColor('danger')
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->label('Terverifikasi')
            ])
            ->filters([
                SelectFilter::make('workshop_id')
                ->label('workshop')
                ->relationship('workshop', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookingTransactions::route('/'),
            'create' => Pages\CreateBookingTransaction::route('/create'),
            'edit' => Pages\EditBookingTransaction::route('/{record}/edit'),
        ];
    }
}
