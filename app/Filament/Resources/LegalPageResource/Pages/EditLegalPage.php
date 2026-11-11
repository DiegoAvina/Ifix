<?php

namespace App\Filament\Resources\LegalPageResource\Pages;

use App\Filament\Resources\LegalPageResource;
use App\Models\LegalPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLegalPage extends EditRecord
{
    protected static string $resource = LegalPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ver')
                ->label('Ver en la tienda')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => $this->record->url())
                ->openUrlInNewTab(),

            // El registro, el checkout y los correos enlazan al aviso de privacidad:
            // se puede ocultar (Publicada), pero no borrar.
            Actions\DeleteAction::make()
                ->hidden(fn (): bool => $this->record->slug === LegalPage::PRIVACY),
        ];
    }
}
