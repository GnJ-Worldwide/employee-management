<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Roles & Permissions';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 1;

    public const SYSTEM_ROLES = ['superadmin', 'subadmin', 'hr', 'employee'];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        if (in_array($record->name, self::SYSTEM_ROLES, true)) {
            return false;
        }
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    /**
     * ALL permissions are defined centrally here.
     * The Seeder dynamically reads from this to ensure 100% sync.
     */
    public static function permissionGroups(): array
    {
        return [
            'User Management' => [
                'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user', 'restore_user', 'force_delete_user',
            ],
            'Admin Management' => [
                'view_any_admin', 'view_admin', 'create_admin', 'update_admin', 'delete_admin', 'restore_admin', 'force_delete_admin',
            ],
            'Work Orders' => [
                'view_any_work_order', 'view_work_order', 'create_work_order', 'update_work_order', 'delete_work_order',
            ],
            'Attendance' => [
                'view_any_attendance', 'view_attendance', 'create_attendance', 'update_attendance', 'delete_attendance',
            ],
            'Clients' => [ // Added missing
                'view_any_client', 'view_client', 'create_client', 'update_client', 'delete_client',
            ],
            'Billing' => [
                'view_any_billing', 'view_billing', 'create_billing', 'update_billing', 'delete_billing',
            ],
            'HR / Employees' => [
                'view_any_employee', 'view_employee', 'create_employee', 'update_employee', 'delete_employee',
            ],
            'Self-service' => [
                'view_own_profile', 'view_own_documents', 'view_own_attendance',
            ],
            'Contracts' => [
                'view_any_contract', 'view_contract', 'create_contract', 'update_contract', 'delete_contract',
            ],
            'Contract Attachments' => [ // Added missing
                'view_any_contract_attachment', 'view_contract_attachment', 'create_contract_attachment', 'update_contract_attachment', 'delete_contract_attachment', 'verify_contract_attachment',
            ],
            'Invoices' => [
                'view_any_invoice', 'view_invoice', 'create_invoice', 'update_invoice', 'delete_invoice', 'approve_invoice', 'reject_invoice', 'mark_invoice_paid',
            ],
            'Compliance Documents' => [ // Added missing view/update for full resource coverage
                'view_any_compliance_document', 'view_compliance_document', 'create_compliance_document', 'update_compliance_document', 'delete_compliance_document', 'verify_compliance_document',
            ],
            'Compliance Checklists' => [ // Added missing
                'view_any_compliance_checklist', 'view_compliance_checklist', 'create_compliance_checklist', 'update_compliance_checklist', 'delete_compliance_checklist',
            ],
            'Vendors' => [
                'view_any_vendor', 'view_vendor', 'create_vendor', 'update_vendor', 'delete_vendor',
            ],
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Role Details')
                ->icon('heroicon-o-identification')
                ->description('A role is a named bundle of module access levels assignable to users.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Role Name')
                        ->required()
                        ->maxLength(50)
                        ->unique(Role::class, 'name', ignoreRecord: true)
                        ->disabled(fn($record) => $record && in_array($record->name, self::SYSTEM_ROLES, true))
                        ->helperText('Lowercase with underscores (e.g. "compliance_officer"). System role names are immutable.')
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Placeholder::make('_superadmin_notice')
                        ->label('')
                        ->content('⚠️ The Super Admin role always holds every permission. Access cannot be changed here.'),
                ])
                ->visible(fn($record) => $record?->name === 'superadmin'),

            Forms\Components\Section::make('Module Access')
                ->description('Select the modules this role can manage. Granting access to a module automatically assigns all associated permissions behind the scenes.')
                ->icon('heroicon-o-key')
                ->schema([
                    Forms\Components\CheckboxList::make('module_permissions')
                        ->label('')
                        ->options(
                            collect(static::permissionGroups())
                                ->keys()
                                ->mapWithKeys(fn($group) => [$group => $group])
                                ->toArray()
                        )
                        ->columns(3)
                        ->gridDirection('row')
                        ->bulkToggleable()
                        ->disabled(fn($record) => $record?->name === 'superadmin')
                        // Pull granular DB permissions and determine which modules are "checked"
                        ->loadStateFromRelationshipsUsing(function ($component, $record) {
                            if (!$record) {
                                $component->state([]);
                                return;
                            }
                            if ($record->name === 'superadmin') {
                                $component->state(array_keys(static::permissionGroups()));
                                return;
                            }

                            $granted = $record->permissions->pluck('name')->toArray();
                            $selectedModules = [];

                            foreach (static::permissionGroups() as $module => $perms) {
                                // If the role holds AT LEAST ONE permission of this module, display it as checked
                                if (count(array_intersect($perms, $granted)) > 0) {
                                    $selectedModules[] = $module;
                                }
                            }

                            $component->state($selectedModules);
                        })
                        // Take selected modules, fetch all underlying permissions, and sync to DB
                        ->saveRelationshipsUsing(function ($record, $state) {
                            if ($record->name === 'superadmin') return;

                            $permissionsToSync = [];
                            $groups = static::permissionGroups();
                            $selectedModules = is_array($state) ? $state : [];

                            foreach ($selectedModules as $module) {
                                if (isset($groups[$module])) {
                                    $permissionsToSync = array_merge($permissionsToSync, $groups[$module]);
                                }
                            }

                            $record->syncPermissions($permissionsToSync);
                        })
                        ->dehydrated(false) // Required since this field doesn't exist in the DB
                ])
                ->visible(fn($record) => $record?->name !== 'superadmin'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'superadmin' => 'Super Admin',
                        'subadmin' => 'Sub Admin',
                        'hr' => 'HR',
                        'employee' => 'Employee',
                        default => Str::headline($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'subadmin' => 'warning',
                        'hr' => 'info',
                        'employee' => 'gray',
                        default => 'primary',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Total Granular Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn(Role $record) => in_array($record->name, self::SYSTEM_ROLES, true)),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $moduleEntries = [];

        foreach (static::permissionGroups() as $group => $groupPerms) {
            $moduleEntries[] = Infolists\Components\IconEntry::make('module_' . Str::slug($group))
                ->label($group)
                ->getStateUsing(function ($record) use ($groupPerms) {
                    if ($record->name === 'superadmin') return true;
                    $granted = $record->permissions->pluck('name')->toArray();
                    return count(array_intersect($groupPerms, $granted)) > 0;
                })
                ->boolean()
                ->trueColor('success')
                ->falseColor('gray');
        }

        return $infolist->schema([

            Infolists\Components\Section::make('Role Details')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('name')
                        ->label('Role Name')
                        ->badge()
                        ->formatStateUsing(fn(string $state): string => match ($state) {
                            'superadmin' => 'Super Admin',
                            'subadmin' => 'Sub Admin',
                            'hr' => 'HR',
                            'employee' => 'Employee',
                            default => Str::headline($state),
                        })
                        ->color(fn(string $state): string => match ($state) {
                            'superadmin' => 'danger',
                            'subadmin' => 'warning',
                            'hr' => 'info',
                            'employee' => 'gray',
                            default => 'primary',
                        }),

                    Infolists\Components\TextEntry::make('permissions_count')
                        ->label('Total Granular Permissions')
                        ->getStateUsing(
                            fn($record) => $record->name === 'superadmin'
                            ? 'All (' . Permission::count() . ')'
                            : $record->permissions->count()
                        )
                        ->badge()
                        ->color('success'),
                ]),

            Infolists\Components\Section::make('Module Access Matrix')
                ->icon('heroicon-o-key')
                ->columns(3)
                ->schema($moduleEntries),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}