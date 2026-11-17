<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Designer;
use App\Models\MemberApplication;
use App\Models\Project;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudioOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user instanceof User && $user->isDesigner()) {
            $works = Project::query()->whereHas('designers', fn ($query) => $query->whereKey($user->designer_id));

            return [
                Stat::make('Your published works', (clone $works)->published()->count()),
                Stat::make('Your drafts', (clone $works)->whereNull('published_at')->count()),
            ];
        }

        return [
            Stat::make('Published works', Project::query()->published()->count()),
            Stat::make('Designers', Designer::query()->published()->count()),
            Stat::make('Unread messages', ContactMessage::query()->unread()->count()),
            Stat::make('Pending applications', MemberApplication::query()->pending()->count()),
        ];
    }
}
