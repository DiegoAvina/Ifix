<?php

namespace App\Filament\Resources\ShippingExtendedPostcodeResource\Pages;

use App\Filament\Resources\ShippingExtendedPostcodeResource;
use App\Services\Shipping\ExtendedPostcodeImporter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListShippingExtendedPostcodes extends ListRecords
{
    protected static string $resource = ShippingExtendedPostcodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Importar lista')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Importar códigos postales de zona extendida')
                ->modalDescription('Sube el archivo de tu paquetería (CSV o TXT). Se toman todos los números de 5 dígitos que contenga, sin importar las columnas.')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Archivo')
                        ->disk('local')
                        ->directory('imports/extended-postcodes')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv'])
                        ->maxSize(10240)
                        ->required(),
                    Forms\Components\Toggle::make('replace')
                        ->label('Reemplazar la lista actual')
                        ->helperText('Actívalo cuando la paquetería publique una lista nueva completa. Si no, solo se agregan los que falten.'),
                ])
                ->action(function (array $data, ExtendedPostcodeImporter $importer): void {
                    $path = Storage::disk('local')->path($data['file']);

                    try {
                        $result = $importer->import(file_get_contents($path), (bool) ($data['replace'] ?? false));
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }

                    Notification::make()
                        ->title("Lista importada: {$result['found']} códigos postales en el archivo, {$result['added']} nuevos")
                        ->body("Total en la lista: {$result['total']}.")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()->label('Agregar código postal'),
        ];
    }
}
