<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Workshop;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\WorkshopResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\WorkshopResource\RelationManagers;

class WorkshopResource extends Resource
{
    protected static ?string $model = Workshop::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([

        Fieldset::make('Details')
            ->schema([
                TextInput::make('name')
                ->required()
                ->maxLength(255),

                Textarea::make('address')
                ->rows (3)
                ->required()
                ->maxLength(255),

                FileUpload::make('thumbnail')
                ->image()
                ->required(),

                FileUpload::make('venue_thumbnail')
                ->image()
                ->required(),

                FileUpload::make('bg_map')
                ->image()
                ->required(),

                Repeater::make('benefits')
                ->relationship('benefits')
                ->schema( [
                    TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                ]),
            ]),

            Fieldset::make('Additional')
            ->schema( [

                Textarea::make('about')
                ->required(),

                TextInput::make('price')
                ->required()
                ->numeric()
                ->prefix('IDR'),

                Select::make('is_open')
                ->options([
                true => 'Open',
                false => 'Not Available',
                ])
                ->required(),

                Select::make('has_started')
                ->options([
                true => 'Started',
                false => 'Not Started Yet',
                ])
                ->required(),

                Select::make('category_id')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required(),

                Select::make('workshop_instructor_id')
                ->relationship('instructor', 'name')
                ->searchable()
                ->preload()
                ->required(),

                DatePicker::make('started_at')
                ->required(),

                TimePicker::make('time_at')
                ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                ImageColumn :: make('thumbnail'),

                TextColumn :: make('name')
                ->searchable(),

                TextColumn :: make('category.name'),

                TextColumn :: make( 'instructor.name'),

                IconColumn :: make('has_started')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger')
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->label('Started'),

                TextColumn :: make('participants_count')
                ->label('Participants')
                ->counts('participants'),

            ])
            ->filters([
                //
                SelectFilter::make('category_id')
                ->label('category')
                ->relationship('category', 'name'),

                SelectFilter::make('workshop_instructor_id')
                ->label('workshop_instructor')
                ->relationship('instructor', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListWorkshops::route('/'),
            'create' => Pages\CreateWorkshop::route('/create'),
            'edit' => Pages\EditWorkshop::route('/{record}/edit'),
        ];
    }
}
