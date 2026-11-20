<?php

namespace App\Filament\Resources\Tournaments\Pages;

use App\Filament\Resources\Tournaments\TournamentResource;
use App\Support\TournamentDraw;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTournament extends EditRecord
{
    protected static string $resource = TournamentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('draw')
                ->label('إجراء القرعة')
                ->icon('heroicon-o-arrows-right-left')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('إجراء قرعة البطولة')
                ->modalDescription('سيتم خلط المشاركين وتوليد هيكلية المنافسة. أي قرعة سابقة سيتم استبدالها.')
                ->visible(fn () => $this->record->participants()->where('is_bye', false)->count() >= 2)
                ->action(function () {
                    app(TournamentDraw::class)->draw($this->record);
                    $this->record->refresh();

                    Notification::make()
                        ->title('تمت القرعة بنجاح')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
