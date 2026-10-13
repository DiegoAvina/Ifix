<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Filament\Resources\HeroSlideResource\RelationManagers;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form->schema([

        Forms\Components\TextInput::make('title')
            ->required(),

        Forms\Components\TextInput::make('subtitle'),

        Forms\Components\FileUpload::make('image')
            ->image()
            ->directory('hero-slides'),

        Forms\Components\TextInput::make('button_text'),

        Forms\Components\TextInput::make('button_url'),

        Forms\Components\Toggle::make('active')
            ->default(true),

        Forms\Components\TextInput::make('sort_order')
            ->numeric()
            ->default(0),

    ]);
}

   public static function table(Table $table): Table
{
    return $table->columns([

        Tables\Columns\ImageColumn::make('image'),

        Tables\Columns\TextColumn::make('title'),

        Tables\Columns\TextColumn::make('sort_order'),

        Tables\Columns\IconColumn::make('active')
            ->boolean(),

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
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
