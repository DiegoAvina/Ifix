<?php

namespace App\Filament\Extensions;

use App\Services\Order\FreeShippingService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use InvalidArgumentException;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Support\Extending\ViewPageExtension;
use Lunar\Models\Order;

/**
 * Botones "Aplicar envío gratis" y "Quitar envío gratis" en el detalle del
 * pedido (panel). Solo en pedidos sin pagar, para personal que gestiona
 * pedidos.
 */
class ManageOrderExtension extends ViewPageExtension
{
    public function headerActions(array $actions): array
    {
        return [
            Action::make('free_shipping')
                ->label('Aplicar envío gratis')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->visible(fn (Order $record) => $this->staffCanManageOrders() && $this->service()->canApply($record))
                ->requiresConfirmation()
                ->modalHeading('Aplicar envío gratis')
                ->modalDescription(fn (Order $record) => 'Se quitarán $'.number_format($record->shipping_total->decimal(), 2)
                    .' MXN de envío y el total bajará a $'.number_format($record->total->decimal() - $record->shipping_total->decimal(), 2).' MXN. '
                    .'Le enviaremos un correo al cliente con sus productos, el envío gratis y el botón para pagar.')
                ->modalSubmitActionLabel('Sí, aplicar y avisar')
                ->action(fn (Order $record, ManageOrder $livewire) => $this->run(
                    $livewire,
                    $record,
                    fn () => $this->service()->apply($record, Filament::auth()->user()),
                    fn (int $cents) => ['Envío gratis aplicado', 'Se quitaron $'.number_format($cents / 100, 2).' MXN de envío y se avisó al cliente por correo.'],
                )),

            Action::make('remove_free_shipping')
                ->label('Quitar envío gratis')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->visible(fn (Order $record) => $this->staffCanManageOrders() && $this->service()->canRemove($record))
                ->requiresConfirmation()
                ->modalHeading('Quitar envío gratis')
                ->modalDescription('Se regresará el costo de envío original y el total volverá a subir. El cliente no recibe correo de este cambio.')
                ->modalSubmitActionLabel('Sí, quitar')
                ->action(fn (Order $record, ManageOrder $livewire) => $this->run(
                    $livewire,
                    $record,
                    fn () => $this->service()->remove($record, Filament::auth()->user()),
                    fn (int $cents) => ['Envío gratis quitado', 'Se regresaron $'.number_format($cents / 100, 2).' MXN de envío al pedido.'],
                )),

            ...$actions,
        ];
    }

    private function run(ManageOrder $livewire, Order $record, callable $change, callable $message): void
    {
        try {
            $cents = $change();
        } catch (InvalidArgumentException $e) {
            Notification::make()->title('No se pudo cambiar el envío')->body($e->getMessage())->danger()->send();

            return;
        }

        [$title, $body] = $message($cents);
        Notification::make()->title($title)->body($body)->success()->send();

        // Recarga la página para mostrar los totales nuevos.
        $livewire->redirect(ManageOrder::getUrl(['record' => $record]));
    }

    private function service(): FreeShippingService
    {
        return app(FreeShippingService::class);
    }

    private function staffCanManageOrders(): bool
    {
        $staff = Filament::auth()->user();

        return $staff && ($staff->admin || $staff->checkPermissionTo('sales:manage-orders', 'staff'));
    }
}
