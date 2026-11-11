<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingExtendedPostcodeResource\Pages;
use App\Models\Shipping\ShippingExtendedPostcode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Códigos postales de zona extendida. La lista la publica cada paquetería;
 * se importa con "Importar lista" (CSV, TXT o Excel guardado como CSV).
 */
class ShippingExtendedPostcodeResource extends Resource
{
    protected static ?string $model = ShippingExtendedPostcode::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Zona extendida';

    protected static ?string $navigationGroup = 'Tienda en línea';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'código postal';

    protected static ?string $pluralModelLabel = 'Códigos postales de zona extendida';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('postcode')
                ->label('Código postal')
                ->required()
                ->regex('/^\d{5}$/')
                ->validationMessages(['regex' => 'Escribe un código postal de 5 dígitos.'])
                ->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('postcode')
            ->columns([
                Tables\Columns\TextColumn::make('postcode')->label('Código postal')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Agregado')->date('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Sin códigos postales de zona extendida')
            ->emptyStateDescription('Importa la lista de tu paquetería con "Importar lista". Mientras esté vacía, no se cobra recargo de zona extendida.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingExtendedPostcodes::route('/'),
        ];
    }
}
