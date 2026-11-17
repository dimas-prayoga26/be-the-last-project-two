<?php

namespace App\Filament\Resources\Designers\Pages;

use App\Filament\Resources\Designers\DesignerResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDesigners extends ListRecords
{
    protected static string $resource = DesignerResource::class;

    public function mount(): void
    {
        $user = auth()->user();

        // A designer account has exactly one profile, so skip the list.
        if ($user instanceof User && $user->isDesigner()) {
            $this->redirect(DesignerResource::getUrl('edit', ['record' => $user->designer_id]));

            return;
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
