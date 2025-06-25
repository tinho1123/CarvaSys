<?php

namespace App\Filament\Resources\FeeResource\Pages;

use App\Filament\Resources\FeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Js;

class EditFee extends EditRecord
{
    protected static string $resource = FeeResource::class;

    protected static ?string $title = "Editar Taxa";

    protected static ?string $breadcrumb = 'Editar Taxa';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir Taxa')
            ->modalHeading('Excluir Taxa')
            ->modalSubheading('Você tem certeza que deseja excluir essa taxa?')
            ->modalButton('Excluir')
            ->modalCancelActionLabel('Cancelar')
            ->successNotificationTitle('Taxa excluída com sucesso!'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Taxa editada com sucesso!';
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('save')
            ->label("Salvar")
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('cancel')
            ->label('Cancelar')
            ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Js::from($this->previousUrl ?? static::getResource()::getUrl()) . ')')
            ->color('gray');
    }
}
