<?php

namespace App\Filament\Resources\Designers;

use App\Enums\SocialPlatform;
use App\Filament\Resources\Designers\Pages\CreateDesigner;
use App\Filament\Resources\Designers\Pages\EditDesigner;
use App\Filament\Resources\Designers\Pages\ListDesigners;
use App\Filament\Support\Translatable;
use App\Models\Designer;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DesignerResource extends Resource
{
    protected static ?string $model = Designer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return static::currentUser()?->isDesigner() ? 'My profile' : 'Designers';
    }

    /**
     * Designer accounts only ever see their own profile.
     *
     * @return Builder<Designer>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = static::currentUser();

        return $user?->isDesigner() ? $query->whereKey($user->designer_id) : $query;
    }

    public static function form(Schema $schema): Schema
    {
        $isAdmin = fn (): bool => (bool) static::currentUser()?->isAdmin();

        return $schema
            ->components([
                Section::make('Profile')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('photo')
                            ->collection('photo')
                            ->image()
                            ->imageEditor()
                            ->imageAspectRatio('4:5')
                            ->maxSize(10240)
                            ->helperText('Portrait photo, ideally 4:5 and shot in the same style as the other designers.')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('location')
                            ->label('Location base')
                            ->placeholder('Jakarta, Indonesia')
                            ->maxLength(150),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(190),
                        TextInput::make('slug')
                            ->maxLength(150)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the profile URL. Leave empty to create it from the name.')
                            ->visible($isAdmin),
                        Translatable::tabs('title', 'Role', fn (string $statePath) => TextInput::make($statePath)
                            ->placeholder('Interior Designer')
                            ->maxLength(150)),
                        Translatable::tabs('headline', 'Headline', fn (string $statePath) => TextInput::make($statePath)
                            ->placeholder('Quiet houses for a tropical climate')
                            ->helperText('One line under the name at the top of the profile page.')
                            ->maxLength(160)),
                        Translatable::tabs('bio', 'Bio', fn (string $statePath) => Textarea::make($statePath)
                            ->rows(5)
                            ->maxLength(3000)),
                        Translatable::tabs('statement', 'Statement', fn (string $statePath) => Textarea::make($statePath)
                            ->rows(3)
                            ->helperText('A short quote about how you work, set large between the works and the gallery.')
                            ->maxLength(400)),
                    ]),
                Section::make('Social accounts')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('socials')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('platform')
                                    ->options(SocialPlatform::class)
                                    ->required(),
                                TextInput::make('url')
                                    ->label('Link')
                                    ->url()
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderable()
                            ->addActionLabel('Add social account'),
                    ]),
                Section::make('Visibility')
                    ->columnSpanFull()
                    ->visible($isAdmin)
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_published')
                                ->label('Show on the website')
                                ->default(true),
                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->helperText('Lower numbers appear first. You can also drag rows in the list.'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')
                    ->collection('photo')
                    ->conversion('card')
                    ->imageHeight(56),
                TextColumn::make('name')
                    ->searchable()
                    ->description(fn (Designer $record): ?string => $record->title),
                TextColumn::make('location')
                    ->label('Location base')
                    ->toggleable(),
                TextColumn::make('projects_count')
                    ->counts('projects')
                    ->label('Works'),
                IconColumn::make('user_exists')
                    ->exists('user')
                    ->label('Login')
                    ->boolean()
                    ->falseColor('gray'),
                IconColumn::make('is_published')
                    ->label('Visible')
                    ->boolean()
                    ->falseColor('gray'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDesigners::route('/'),
            'create' => CreateDesigner::route('/create'),
            'edit' => EditDesigner::route('/{record}/edit'),
        ];
    }

    private static function currentUser(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }
}
