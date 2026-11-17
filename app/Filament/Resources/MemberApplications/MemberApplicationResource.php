<?php

namespace App\Filament\Resources\MemberApplications;

use App\Enums\ApplicationStatus;
use App\Enums\Discipline;
use App\Filament\Resources\MemberApplications\Pages\ManageMemberApplications;
use App\Models\MemberApplication;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class MemberApplicationResource extends Resource
{
    protected static ?string $model = MemberApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Studio';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'application';

    protected static ?string $navigationLabel = 'Applications';

    public static function getNavigationBadge(): ?string
    {
        $pending = MemberApplication::query()->pending()->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('name'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('email')
                    ->url(fn (MemberApplication $record): string => "mailto:{$record->email}")
                    ->copyable(),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('city'),
                TextEntry::make('discipline'),
                TextEntry::make('portfolio_url')
                    ->label('Portfolio')
                    ->url(fn (MemberApplication $record): string => $record->portfolio_url, shouldOpenInNewTab: true),
                TextEntry::make('instagram')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Received')
                    ->dateTime(),
                TextEntry::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('message')
                    ->label('About their work')
                    ->columnSpanFull()
                    ->prose(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight(fn (MemberApplication $record): ?string => $record->status === ApplicationStatus::Pending ? 'bold' : null),
                TextColumn::make('discipline')
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(ApplicationStatus::class),
                SelectFilter::make('discipline')
                    ->options(Discipline::class),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('accept')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (MemberApplication $record): bool => $record->status !== ApplicationStatus::Accepted)
                    ->action(fn (MemberApplication $record) => $record->review(ApplicationStatus::Accepted)),
                Action::make('decline')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (MemberApplication $record): bool => $record->status !== ApplicationStatus::Declined)
                    ->action(fn (MemberApplication $record) => $record->review(ApplicationStatus::Declined)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMemberApplications::route('/'),
        ];
    }
}
