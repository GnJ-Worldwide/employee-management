<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 0;  // appears before Admin module

    // ──────────────────────────────────────────────────────────────────────────
    //  Access control — sidebar link is auto-hidden when this returns false
    // ──────────────────────────────────────────────────────────────────────────
    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any_user') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_user') ?? false;
    }

    public static function canEdit($record): bool
    {
        // Prevent editing your own role/status to avoid accidental lockout
        if ($record->id === auth()->id() && !auth()->user()->isSuperAdmin()) {
            return false;
        }
        return auth()->user()?->can('update_user') ?? false;
    }

    public static function canDelete($record): bool
    {
        // Nobody can delete themselves
        if ($record->id === auth()->id()) {
            return false;
        }
        return auth()->user()?->can('delete_user') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_user') ?? false;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  FORM
    // ──────────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── SECTION 1: Account Details ────────────────────────────────
            Forms\Components\Section::make('Account Details')
                ->description('Basic login credentials for the user.')
                ->icon('heroicon-o-user-circle')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->minLength(8)
                        ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->helperText(fn (string $operation) => $operation === 'edit'
                            ? 'Leave blank to keep the current password.'
                            : 'Minimum 8 characters.')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Confirm Password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->same('password')
                        ->dehydrated(false)
                        ->helperText('Must match the password above.')
                        ->columnSpan(1),
                ]),

            // ── SECTION 2: Role & Status ──────────────────────────────────
            Forms\Components\Section::make('Role & Status')
                ->description('Assign a role and set the account status.')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('roles')
                        ->label('Role')
                        ->relationship('roles', 'name')
                        ->options(function () {
                            $roles = Role::query();

                            // Non-superadmins cannot assign the superadmin role
                            if (!auth()->user()?->isSuperAdmin()) {
                                $roles->where('name', '!=', 'superadmin');
                            }

                            return $roles->pluck('name', 'id')->map(fn ($name) => match ($name) {
                                'superadmin' => 'Super Admin',
                                'subadmin'   => 'Sub Admin',
                                'hr'         => 'HR',
                                'employee'   => 'Employee',
                                default      => ucfirst($name),
                            });
                        })
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->helperText('Select one role for this user.')
                        ->columnSpan(1),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active Account')
                        ->helperText('Inactive users cannot log in to any panel.')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger')
                        ->columnSpan(1),
                ]),

        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  TABLE
    // ──────────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'superadmin' => 'Super Admin',
                        'subadmin'   => 'Sub Admin',
                        'hr'         => 'HR',
                        'employee'   => 'Employee',
                        default      => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'subadmin'   => 'warning',
                        'hr'         => 'info',
                        'employee'   => 'gray',
                        default      => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->options([
                        'superadmin' => 'Super Admin',
                        'subadmin'   => 'Sub Admin',
                        'hr'         => 'HR',
                        'employee'   => 'Employee',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->is_active && $record->id !== auth()->id())
                    ->action(fn (User $record) => $record->update(['is_active' => false])),
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => !$record->is_active)
                    ->action(fn (User $record) => $record->update(['is_active' => true])),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate_selected')
                        ->label('Activate selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate_selected')
                        ->label('Deactivate selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records
                            ->filter(fn (User $user) => $user->id !== auth()->id())
                            ->each->update(['is_active' => false])
                        ),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('roles'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  INFOLIST  (View page)
    // ──────────────────────────────────────────────────────────────────────────
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Infolists\Components\Section::make('Account Details')
                ->icon('heroicon-o-user-circle')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('name')->label('Full Name'),
                    Infolists\Components\TextEntry::make('email')->label('Email Address'),
                    Infolists\Components\TextEntry::make('created_at')->label('Joined')->dateTime('d M Y, h:i A'),
                    Infolists\Components\TextEntry::make('updated_at')->label('Last Updated')->dateTime('d M Y, h:i A'),
                ]),

            Infolists\Components\Section::make('Role & Status')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('roles.name')
                        ->label('Role')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'superadmin' => 'Super Admin',
                            'subadmin'   => 'Sub Admin',
                            'hr'         => 'HR',
                            'employee'   => 'Employee',
                            default      => ucfirst($state),
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'superadmin' => 'danger',
                            'subadmin'   => 'warning',
                            'hr'         => 'info',
                            'employee'   => 'gray',
                            default      => 'gray',
                        }),

                    Infolists\Components\IconEntry::make('is_active')
                        ->label('Account Active')
                        ->boolean()
                        ->trueColor('success')
                        ->falseColor('danger'),
                ]),

        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  PAGES
    // ──────────────────────────────────────────────────────────────────────────
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'info';
    }
}