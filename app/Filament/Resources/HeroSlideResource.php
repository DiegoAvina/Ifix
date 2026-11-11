<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    // La app corre en UTC; las fechas se capturan y muestran en hora de México.
    private const TIMEZONE = 'America/Mexico_City';

    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Banners del inicio';

    protected static ?string $navigationGroup = 'Tienda en línea';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'banner';

    protected static ?string $pluralModelLabel = 'Banners del inicio';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('eyebrow')
                ->label('Etiqueta')
                ->placeholder('Oferta')
                ->helperText('Texto pequeño sobre el título. Si se deja vacío, dice "Oferta".')
                ->maxLength(60),

            Forms\Components\TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('subtitle')
                ->label('Subtítulo')
                ->maxLength(255),

            // Sin SVG: puede contener JavaScript y se serviría desde nuestro dominio.
            Forms\Components\FileUpload::make('image')
                ->label('Imagen')
                ->helperText('Horizontal, de unos 2400 × 1050 px. JPG, PNG o WebP, hasta 4 MB.')
                ->required()
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(4096)
                ->disk('public')
                ->visibility('public')
                ->directory('hero-slides'),

            Forms\Components\TextInput::make('button_text')
                ->label('Texto del botón')
                ->placeholder('Ver más')
                ->maxLength(60),

            // Solo rutas internas (/productos, #seccion) o enlaces http(s);
            // bloquea javascript:, data:, etc.
            Forms\Components\TextInput::make('button_url')
                ->label('Enlace del botón')
                ->helperText('Por ejemplo, /productos. Si se deja vacío, lleva al catálogo.')
                ->maxLength(255)
                ->regex('~^(/(?!/)|#|https?://)~i'),

            Forms\Components\DateTimePicker::make('starts_at')
                ->label('Inicio')
                ->helperText('Hora de México. Si se deja vacío, empieza de inmediato.')
                ->seconds(false)
                ->timezone(self::TIMEZONE),

            Forms\Components\DateTimePicker::make('ends_at')
                ->label('Fin')
                ->helperText('Hora de México. Si se deja vacío, no termina.')
                ->seconds(false)
                ->timezone(self::TIMEZONE)
                ->rule(
                    fn (Get $get): string => 'after:'.$get('starts_at'),
                    fn (Get $get): bool => filled($get('starts_at')),
                ),

            Forms\Components\TextInput::make('sort_order')
                ->label('Orden')
                ->helperText('Los números más bajos salen primero.')
                ->required()
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('active')
                ->label('Activo')
                ->default(true),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([

                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagen')
                    ->disk('public'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título'),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i', self::TIMEZONE)
                    ->placeholder('Inmediato'),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i', self::TIMEZONE)
                    ->placeholder('Sin fin'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
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
