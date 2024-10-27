<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkshopInstructorResource\Pages;
use App\Filament\Resources\WorkshopInstructorResource\RelationManagers;
use App\Models\WorkshopInstructor;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkshopInstructorResource extends Resource
{
    protected static ?string $model = WorkshopInstructor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->required()
                ->maxLength(225),

                TextInput::make('occupation')
                ->required()
                ->maxLength(225),
                
                FileUpload::make('avatar')
                ->image()
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->searchable(),
                
                TextColumn::make('occupation'),

                ImageColumn::make('avatar')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListWorkshopInstructors::route('/'),
            'create' => Pages\CreateWorkshopInstructor::route('/create'),
            'edit' => Pages\EditWorkshopInstructor::route('/{record}/edit'),
        ];
    }
}
