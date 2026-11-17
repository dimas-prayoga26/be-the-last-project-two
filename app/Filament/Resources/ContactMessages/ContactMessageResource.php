<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|UnitEnum|null $navigationGroup = 'Studio';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'message';

    protected static ?string $navigationLabel = 'Messages';

    public static function getNavigationBadge(): ?string
    {
        $unread = ContactMessage::query()->unread()->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->url(fn (ContactMessage $record): string => "mailto:{$record->email}")
                    ->copyable(),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Received')
                    ->dateTime(),
                TextEntry::make('subject')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('message')
                    ->columnSpanFull()
                    ->prose(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('read_at')
                    ->label('')
                    ->icon(fn (ContactMessage $record): Heroicon => $record->read_at ? Heroicon::OutlinedEnvelopeOpen : Heroicon::Envelope)
                    ->color(fn (ContactMessage $record): string => $record->read_at ? 'gray' : 'primary'),
                TextColumn::make('name')
                    ->searchable()
                    ->weight(fn (ContactMessage $record): ?string => $record->read_at ? null : 'bold'),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('subject')
                    ->limit(50)
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('read_at')
                    ->label('Read')
                    ->nullable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->after(fn (ContactMessage $record) => $record->read_at ?? $record->forceFill(['read_at' => now()])->save()),
                Action::make('markAsRead')
                    ->label('Mark as read')
                    ->icon(Heroicon::OutlinedCheck)
                    ->visible(fn (ContactMessage $record): bool => $record->read_at === null)
                    ->action(fn (ContactMessage $record) => $record->forceFill(['read_at' => now()])->save()),
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
            'index' => ManageContactMessages::route('/'),
        ];
    }
}
