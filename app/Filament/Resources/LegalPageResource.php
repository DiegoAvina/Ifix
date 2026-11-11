<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegalPageResource\Pages;
use App\Models\LegalPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LegalPageResource extends Resource
{
    private const TIMEZONE = 'America/Mexico_City';

    protected static ?string $model = LegalPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Páginas legales';

    protected static ?string $modelLabel = 'página legal';

    protected static ?string $pluralModelLabel = 'Páginas legales';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255),

            // El aviso de privacidad y los términos de servicio tienen dirección
            // propia y los enlazan el registro, el checkout y el footer: su slug no se cambia.
            Forms\Components\TextInput::make('slug')
                ->label('Dirección')
                ->prefix(fn (?LegalPage $record): string => $record?->isFixed() ? url('/').'/' : url('/legal').'/')
                ->helperText('Solo minúsculas, números y guiones. Por ejemplo: terminos-y-condiciones.')
                ->required()
                ->maxLength(100)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true)
                ->disabled(fn (?LegalPage $record): bool => (bool) $record?->isFixed())
                ->dehydrated(fn (?LegalPage $record): bool => ! $record?->isFixed()),

            Forms\Components\RichEditor::make('content')
                ->label('Contenido')
                ->toolbarButtons([
                    'h2', 'h3', 'bold', 'italic', 'underline', 'link',
                    'bulletList', 'orderedList', 'blockquote', 'undo', 'redo',
                ])
                ->columnSpanFull(),

            Forms\Components\Toggle::make('published')
                ->label('Publicada')
                ->helperText('Si se desactiva, la página deja de verse en la tienda.')
                ->default(true),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->label('Título'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Dirección')
                    ->formatStateUsing(fn (LegalPage $record): string => parse_url($record->url(), PHP_URL_PATH) ?: '/'),

                Tables\Columns\IconColumn::make('published')
                    ->label('Publicada')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime('d/m/Y H:i', self::TIMEZONE),

            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver en la tienda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (LegalPage $record): string => $record->url())
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegalPages::route('/'),
            'create' => Pages\CreateLegalPage::route('/create'),
            'edit' => Pages\EditLegalPage::route('/{record}/edit'),
        ];
    }
}
