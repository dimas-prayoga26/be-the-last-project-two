<?php

namespace App\Filament\Resources\PressItems;

use App\Filament\Resources\PressItems\Pages\ManagePressItems;
use App\Filament\Support\Translatable;
use App\Models\PressItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PressItemResource extends Resource
{
    protected static ?string $model = PressItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Studio';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'press item';

    protected static ?string $navigationLabel = 'Press';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Translatable::tabs('title', 'Headline', fn (string $statePath) => TextInput::make($statePath)->maxLength(255), required: true),
                TextInput::make('publication')
                    ->required()
                    ->maxLength(150),
                DatePicker::make('published_on')
                    ->label('Publication date')
                    ->required()
                    ->default(now()),
                TextInput::make('url')
                    ->label('Link to the article')
                    ->url()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Translatable::tabs('excerpt', 'Excerpt', fn (string $statePath) => Textarea::make($statePath)->rows(3)->maxLength(600)),
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('image')
                    ->image()
                    ->maxSize(10240),
                SpatieMediaLibraryFileUpload::make('document')
                    ->label('PDF (optional)')
                    ->collection('document')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20480),
                Toggle::make('is_published')
                    ->label('Show on the website')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('image')
                    ->conversion('thumb')
                    ->imageHeight(48),
                TextColumn::make('title')
                    ->label('Headline')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('publication')
                    ->searchable(),
                TextColumn::make('published_on')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Visible')
                    ->boolean()
                    ->falseColor('gray'),
            ])
            ->defaultSort('published_on', 'desc')
            ->recordActions([
                EditAction::make(),
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
            'index' => ManagePressItems::route('/'),
        ];
    }
}
