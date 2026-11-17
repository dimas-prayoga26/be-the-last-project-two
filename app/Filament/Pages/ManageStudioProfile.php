<?php

namespace App\Filament\Pages;

use App\Enums\SocialPlatform;
use App\Filament\Support\Translatable;
use App\Models\StudioProfile;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Edits the single studio profile behind the Studio > About / Contact / Follow pages.
 *
 * @property-read Schema $form
 */
class ManageStudioProfile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Studio';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Studio profile';

    protected static ?string $title = 'Studio profile';

    protected static ?string $slug = 'studio-profile';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdmin();
    }

    public function mount(): void
    {
        $this->form->fill($this->getRecord()->attributesToArray());
    }

    public function getRecord(): StudioProfile
    {
        return StudioProfile::current();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model($this->getRecord())
            ->statePath('data')
            ->components([
                Section::make('About')
                    ->description('Studio > About, and the introduction on the home page.')
                    ->schema([
                        Translatable::tabs('intro', 'Home page introduction', fn (string $statePath) => Textarea::make($statePath)->rows(2)->maxLength(300)),
                        Translatable::tabs('about_heading', 'About heading', fn (string $statePath) => Textarea::make($statePath)->rows(2)->maxLength(300)),
                        Translatable::tabs('about_body', 'About text', fn (string $statePath) => Textarea::make($statePath)->rows(10)->maxLength(10000)),
                        SpatieMediaLibraryFileUpload::make('about_image')
                            ->collection('about_image')
                            ->image()
                            ->maxSize(15360),
                    ]),
                Section::make('Contact')
                    ->description('Studio > Contact and the site footer.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')->email()->maxLength(190),
                        TextInput::make('phone')->tel()->maxLength(40),
                        TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(40),
                        TextInput::make('map_url')->label('Map link')->url()->maxLength(255),
                        Textarea::make('address')->rows(3)->maxLength(500),
                        Translatable::tabs('office_hours', 'Office hours', fn (string $statePath) => TextInput::make($statePath)->maxLength(150)),
                    ]),
                Section::make('Follow')
                    ->description('Studio > Follow and the footer icons.')
                    ->schema([
                        Repeater::make('socials')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('platform')->options(SocialPlatform::class)->required(),
                                TextInput::make('url')->label('Link')->url()->required()->maxLength(255),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderable()
                            ->addActionLabel('Add social account'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $record = $this->getRecord();

        $record->update($this->form->getState());
        $this->form->model($record)->saveRelationships();

        Notification::make()->success()->title('Studio profile saved')->send();
    }
}
