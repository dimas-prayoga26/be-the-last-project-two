<?php

namespace App\Filament\Resources\Projects;

use App\Enums\Discipline;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Support\Translatable;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'work';

    protected static ?string $navigationLabel = 'Works';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Designer accounts only see the works they are credited on.
     *
     * @return Builder<Project>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = static::currentUser();

        if ($user?->isDesigner()) {
            $query->whereHas('designers', fn (Builder $designers) => $designers->whereKey($user->designer_id));
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        $isAdmin = fn (): bool => (bool) static::currentUser()?->isAdmin();

        return $schema
            ->columns(3)
            ->components([
                Section::make('Work')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Translatable::tabs('title', 'Title', fn (string $statePath) => TextInput::make($statePath)->maxLength(200), required: true),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'slug', fn (Builder $query) => $query->ordered())
                            ->getOptionLabelFromRecordUsing(fn (Category $record): string => "{$record->discipline->getLabel()} › {$record->name}")
                            ->preload()
                            ->required(),
                        Select::make('designers')
                            ->relationship('designers', 'name', fn (Builder $query) => $query->ordered())
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->helperText('Credit every designer who worked on it. Your own name is always kept.'),
                        Translatable::tabs('summary', 'Short intro', fn (string $statePath) => Textarea::make($statePath)
                            ->rows(2)
                            ->maxLength(300)),
                        Translatable::tabs('description', 'Description', fn (string $statePath) => Textarea::make($statePath)
                            ->rows(8)
                            ->maxLength(10000)),
                    ]),
                Section::make('Details')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('location')->placeholder('Canggu, Bali')->maxLength(150),
                        TextInput::make('year')->numeric()->minValue(1900)->maxValue(2100),
                        TextInput::make('area')->placeholder('420 m²')->maxLength(50),
                        TextInput::make('client')->maxLength(150),
                        TextInput::make('photographer')->label('Photography')->maxLength(150),
                        DateTimePicker::make('published_at')
                            ->label('Publish date')
                            ->default(now())
                            ->helperText('Leave empty to keep the work as a draft.'),
                        Toggle::make('is_featured')
                            ->label('Feature on the home page')
                            ->visible($isAdmin),
                        TextInput::make('slug')
                            ->maxLength(200)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the URL. Leave empty to create it from the English title.')
                            ->visible($isAdmin),
                    ]),
                Section::make('Images')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            SpatieMediaLibraryFileUpload::make('cover')
                                ->collection('cover')
                                ->image()
                                ->imageEditor()
                                ->automaticallyResizeImagesMode('contain')
                                ->automaticallyResizeImagesToWidth('2000')
                                ->automaticallyResizeImagesToHeight('2000')
                                ->automaticallyUpscaleImagesWhenResizing(false)
                                ->maxSize(15360)
                                ->required()
                                ->helperText('Shown on cards (cropped to 4:5) and at the top of the page.')
                                ->columnSpan(1),
                            SpatieMediaLibraryFileUpload::make('gallery')
                                ->collection('gallery')
                                ->image()
                                ->multiple()
                                ->reorderable()
                                ->appendFiles()
                                ->panelLayout('grid')
                                ->automaticallyResizeImagesMode('contain')
                                ->automaticallyResizeImagesToWidth('2000')
                                ->automaticallyResizeImagesToHeight('2000')
                                ->automaticallyUpscaleImagesWhenResizing(false)
                                ->maxFiles(40)
                                ->maxSize(15360)
                                ->helperText('Drag to reorder. Landscape photos span the full width; portrait photos are paired.')
                                ->columnSpan(2),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->collection('cover')
                    ->conversion('card')
                    ->imageHeight(56),
                TextColumn::make('title')
                    ->searchable()
                    ->description(fn (Project $record): string => $record->designers->pluck('name')->join(', ')),
                TextColumn::make('category.discipline')
                    ->label('Discipline')
                    ->badge(),
                TextColumn::make('category.name')
                    ->label('Category'),
                TextColumn::make('year')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->falseColor('gray'),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->date()
                    ->placeholder('Draft')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'designers']))
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('discipline')
                    ->options(Discipline::class)
                    ->query(fn (Builder $query, array $data) => filled($data['value'])
                        ? $query->inDiscipline(Discipline::from($data['value']))
                        : $query),
                SelectFilter::make('designers')
                    ->relationship('designers', 'name')
                    ->multiple()
                    ->visible(fn (): bool => (bool) static::currentUser()?->isAdmin()),
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('published_at')
                    ->label('Published')
                    ->nullable(),
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }

    /**
     * Keep the logged-in designer credited on works they create or edit.
     */
    public static function creditCurrentDesigner(Project $project): void
    {
        $user = static::currentUser();

        if ($user?->isDesigner()) {
            $project->designers()->syncWithoutDetaching([$user->designer_id]);
        }
    }

    private static function currentUser(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }
}
