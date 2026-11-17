<?php

namespace App\Filament\Resources\Users;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $modelLabel = 'account';

    protected static ?string $navigationLabel = 'Accounts';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(150),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(190),
                Select::make('role')
                    ->options(UserRole::class)
                    ->default(UserRole::Designer)
                    ->required()
                    ->live(),
                Select::make('designer_id')
                    ->label('Designer profile')
                    ->relationship('designer', 'name')
                    ->searchable()
                    ->preload()
                    ->unique(ignoreRecord: true)
                    ->required(fn (Get $get): bool => self::isDesignerRole($get('role')))
                    ->visible(fn (Get $get): bool => self::isDesignerRole($get('role')))
                    ->helperText('The account can edit only this profile and its works.'),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8)
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Leave empty to keep the current password.' : null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge(),
                TextColumn::make('designer.name')
                    ->label('Designer profile')
                    ->placeholder('-'),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => self::withoutDesignerForAdmins($data)),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }

    /**
     * Admin accounts are not tied to a designer profile.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function withoutDesignerForAdmins(array $data): array
    {
        if (! self::isDesignerRole($data['role'] ?? null)) {
            $data['designer_id'] = null;
        }

        return $data;
    }

    private static function isDesignerRole(mixed $role): bool
    {
        return ($role instanceof UserRole ? $role : UserRole::tryFrom((string) $role)) === UserRole::Designer;
    }
}
