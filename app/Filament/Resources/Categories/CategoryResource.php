<?php

namespace App\Filament\Resources\Categories;

use App\Enums\Discipline;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Filament\Support\Translatable;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Menu categories';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('discipline')
                    ->options(Discipline::class)
                    ->required()
                    ->live(),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->alphaDash()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('discipline', $get('discipline')))
                    ->helperText('Used in the URL, e.g. /architecture/residential. Changing it breaks old links.'),
                Translatable::tabs('name', 'Name', fn (string $statePath) => TextInput::make($statePath)
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($statePath): void {
                        if ($statePath === 'name.en' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }), required: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('discipline')
                    ->badge(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->color('gray'),
                TextColumn::make('projects_count')
                    ->counts('projects')
                    ->label('Works'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->defaultGroup('discipline')
            ->filters([
                SelectFilter::make('discipline')->options(Discipline::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
