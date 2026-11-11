<?php

namespace App\Filament\Pages;

use App\Models\Shipping\ShippingSetting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Ajustes generales del cálculo de envío (una sola fila en shipping_settings).
 */
class ShippingSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Ajustes de envío';

    protected static ?string $navigationGroup = 'Tienda en línea';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Ajustes de envío';

    protected static ?string $slug = 'ajustes-de-envio';

    protected static string $view = 'filament.pages.shipping-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(ShippingSetting::query()->firstOrNew()->toArray() + [
            'origin_postcode' => '44720',
            'weight_brackets' => [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Envío gratis')
                    ->description('Monto de compra a partir del cual el envío no se cobra.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('free_shipping_from')
                            ->label('Envío gratis desde')
                            ->helperText('En pesos. Déjalo vacío para no ofrecer envío gratis.')
                            ->numeric()->minValue(0)->prefix('$'),
                        Forms\Components\Toggle::make('free_shipping_covers_extended')
                            ->label('El envío gratis también cubre la zona extendida')
                            ->helperText('Si está apagado, en zona extendida se cobra solo el recargo aunque la compra tenga envío gratis.')
                            ->inline(false),
                    ]),

                Forms\Components\Section::make('Zona extendida')
                    ->description('Recargo para los códigos postales de la lista "Zona extendida".')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('extended_surcharge')
                            ->label('Recargo')
                            ->numeric()->minValue(0)->prefix('$')->required(),
                        Forms\Components\TextInput::make('extended_extra_days')
                            ->label('Días extra de entrega')
                            ->integer()->minValue(0)->required(),
                    ]),

                Forms\Components\Section::make('Peso del pedido')
                    ->description('Recargo según el peso total del pedido. El peso de cada producto se captura en su pestaña "Envío".')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('default_item_weight_kg')
                            ->label('Peso por pieza si el producto no tiene peso')
                            ->numeric()->minValue(0)->suffix('kg')->required(),
                        Forms\Components\TextInput::make('extra_per_kg')
                            ->label('Cargo por kg arriba del último rango')
                            ->numeric()->minValue(0)->prefix('$')->required(),
                        Forms\Components\Repeater::make('weight_brackets')
                            ->label('Rangos de peso')
                            ->helperText('Ej.: hasta 3 kg sin recargo, hasta 5 kg +$40. Se ordenan solos de menor a mayor.')
                            ->schema([
                                Forms\Components\TextInput::make('max_kg')->label('Hasta')->numeric()->minValue(0)->suffix('kg')->required(),
                                Forms\Components\TextInput::make('surcharge')->label('Recargo')->numeric()->minValue(0)->prefix('$')->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Agregar rango')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Origen y preparación')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('origin_postcode')
                            ->label('C.P. desde donde se envía')
                            ->regex('/^\d{5}$/')->required(),
                        Forms\Components\TextInput::make('processing_days')
                            ->label('Días de preparación')
                            ->helperText('Días antes de entregar el paquete a la paquetería.')
                            ->integer()->minValue(0)->required(),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Guardar cambios')->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $data['weight_brackets'] = collect($data['weight_brackets'] ?? [])
            ->sortBy(fn ($bracket) => (float) $bracket['max_kg'])
            ->values()
            ->all();

        ShippingSetting::query()->firstOrNew()->fill($data)->save();

        $this->form->fill($data);

        Notification::make()->title('Ajustes de envío guardados')->success()->send();
    }
}
