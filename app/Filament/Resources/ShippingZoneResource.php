<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingZoneResource\Pages;
use App\Models\Shipping\ShippingZone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Zonas de envío por código postal (primeros 2 dígitos = estado).
 */
class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZone::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Zonas de envío';

    protected static ?string $navigationGroup = 'Tienda en línea';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'zona de envío';

    protected static ?string $pluralModelLabel = 'Zonas de envío';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('price')
                ->label('Precio de envío')
                ->helperText('En pesos, con IVA. Es el precio base, sin recargos por peso o zona extendida.')
                ->numeric()
                ->minValue(0)
                ->prefix('$')
                ->required(),

            Forms\Components\TagsInput::make('postcode_prefixes')
                ->label('Inicio de código postal')
                ->helperText('Primeros 2 dígitos del C.P. de la zona (ej. 44, 45). Escribe uno y pulsa Enter. Déjalo vacío en la zona general.')
                ->placeholder('Ej. 44')
                ->nestedRecursiveRules(['regex:/^\d{2}$/'])
                ->columnSpanFull(),

            Forms\Components\TextInput::make('min_days')
                ->label('Días de entrega (mínimo)')
                ->helperText('Días hábiles en tránsito, sin contar la preparación.')
                ->integer()
                ->minValue(0)
                ->required(),

            Forms\Components\TextInput::make('max_days')
                ->label('Días de entrega (máximo)')
                ->integer()
                ->minValue(0)
                ->gte('min_days')
                ->required(),

            Forms\Components\Toggle::make('is_default')
                ->label('Zona general')
                ->helperText('Se usa para cualquier C.P. que no esté en otra zona y cuando el cliente aún no da su C.P. Debe haber una.'),

            Forms\Components\TextInput::make('position')
                ->label('Orden')
                ->integer()
                ->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Zona'),
                Tables\Columns\TextColumn::make('postcode_prefixes')
                    ->label('C.P. que inician con')
                    ->formatStateUsing(fn ($state, ShippingZone $record) => $record->is_default
                        ? 'Todos los demás'
                        : implode(', ', (array) $record->postcode_prefixes))
                    ->wrap(),
                Tables\Columns\TextColumn::make('price')->label('Precio')->money('MXN'),
                Tables\Columns\TextColumn::make('min_days')
                    ->label('Entrega')
                    ->formatStateUsing(fn (ShippingZone $record) => "{$record->min_days} a {$record->max_days} días"),
                Tables\Columns\IconColumn::make('is_default')->label('General')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (ShippingZone $record) => $record->is_default),
            ]);
    }

    /**
     * Solo una zona puede ser la general: al marcar una, se desmarcan las demás.
     */
    public static function keepSingleDefault(ShippingZone $zone): void
    {
        if ($zone->is_default) {
            ShippingZone::query()->whereKeyNot($zone->getKey())->update(['is_default' => false]);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingZones::route('/'),
            'create' => Pages\CreateShippingZone::route('/create'),
            'edit' => Pages\EditShippingZone::route('/{record}/edit'),
        ];
    }
}
