<?php

namespace App\Filament\Resources;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\AttendanceRecord;
use App\Models\Contract;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = AttendanceRecord::class;

    protected static ?string $navigationIcon    = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel   = 'Attendance Records';
    protected static ?string $navigationGroup   = 'Attendance';
    protected static ?int    $navigationSort    = 3;
    protected static ?string $recordTitleAttribute = 'id';

    // ── Permissions ───────────────────────────────────────────────────────────
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_attendance') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_attendance') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_attendance') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_attendance') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_attendance') ?? false;
    }

    // ── Form ────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Attendance Details')->columns(2)->schema([

                Forms\Components\Select::make('employee_id')
                    ->label('Employee')
                    ->options(
                        Employee::query()
                            ->active()
                            ->orderBy('first_name')
                            ->get()
                            ->mapWithKeys(fn (Employee $employee) => [
                                $employee->id => "{$employee->full_name} ({$employee->aadhaar_number})",
                            ])
                    )
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('contract_id')
                    ->label('Contract / Job')
                    ->options(
                        Contract::where('overall_status', 'Active')
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => "{$c->work_order_code} – {$c->contract_title}",
                            ])
                    )
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $contract = Contract::find($state);
                        if ($contract) {
                            $set('vendor_id', $contract->vendor_id);
                        }
                    }),

                Forms\Components\Hidden::make('vendor_id'),

                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->maxDate(now()),

                Forms\Components\Select::make('status')
                    ->options(AttendanceStatus::options())
                    ->enum(AttendanceStatus::class)
                    ->required()
                    ->reactive(),

                Forms\Components\TextInput::make('overtime_hours')
                    ->label('Overtime Hours')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(24)
                    ->step(0.5)
                    ->default(0)
                    ->visible(fn (Forms\Get $get) =>
                        $get('status') === AttendanceStatus::PresentWithOT->value),

                Forms\Components\Textarea::make('remarks')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    // ── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.designation')
                    ->label('Designation')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('contract.work_order_code')
                    ->label('Job Code')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => $state instanceof AttendanceStatus
                        ? $state->getLabel() : $state)
                    ->color(fn ($state) => $state instanceof AttendanceStatus
                        ? $state->getColor() : 'gray'),

                Tables\Columns\TextColumn::make('overtime_hours')
                    ->label('OT Hrs')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} h" : '—')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('markedBy.name')
                    ->label('Marked By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contract_id')
                    ->label('Contract / Job')
                    ->options(
                        Contract::all()->mapWithKeys(fn ($c) => [
                            $c->id => "{$c->work_order_code} – {$c->contract_title}",
                        ])
                    ),

                Tables\Filters\SelectFilter::make('status')
                    ->options(AttendanceStatus::options()),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From Date'),
                        Forms\Components\DatePicker::make('until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['from'],  fn ($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([]) 
            ->defaultSort('date', 'desc');
    }

    // WE ADDED THIS BELOW CONDTITION INTENTIONALLY AS NO CREATION IS ALLOWED HERE 
    public static function canCreate(): bool
    {
        return false;
    }

    // ── Pages ────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttendanceRecords::route('/'),
            // 'create' => Pages\CreateAttendanceRecord::route('/create'),
            'edit'   => Pages\EditAttendanceRecord::route('/{record}/edit'),
        ];
    }
}