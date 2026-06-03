<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use App\Models\StatecityList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Employees';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'aadhaar_number', 'mobile_number'];
    }

    // 3. Tell Filament to use your Model's Accessor to display the text in the search results!
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->full_name; // Uses your getFullNameAttribute() !
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Access control
    // ──────────────────────────────────────────────────────────────────────────
    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any_employee') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_employee') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_employee') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_employee') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_employee') ?? false;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Shared option lists (used in form + table filters)
    // ──────────────────────────────────────────────────────────────────────────

    public static function designationOptions(): array
    {
        return [
            'Helper' => 'Helper',
            'Legger' => 'Legger',
            'Mason' => 'Mason',
            'Scaffolder' => 'Scaffolder',
            'Fitter' => 'Fitter',
            'Fabricator' => 'Fabricator',
            'Foreman' => 'Foreman',
            'Store Keeper' => 'Store Keeper',
            'Supervisor' => 'Supervisor',
            'Safety Supervisor' => 'Safety Supervisor',
            'Safety Manager' => 'Safety Manager',
            'Quality Engineer' => 'Quality Engineer',
            'Engineer' => 'Engineer',
            'Site In-Charge' => 'Site In-Charge',
            'Project Manager' => 'Project Manager',
            'Project Coordinator' => 'Project Coordinator',
            'Manager' => 'Manager',
            'Director' => 'Director',
        ];
    }

    public static function academicsOptions(): array
    {
        return [
            'Basic' => [
                'Before 10th' => 'Before 10th',
                '10th Pass' => '10th Pass',
                '12th Pass' => '12th Pass',
            ],
            'ITI Courses' => [
                'ITI Electrician' => 'ITI – Electrician',
                'ITI Fitter' => 'ITI – Fitter',
                'ITI Welder' => 'ITI – Welder',
                'ITI Plumber' => 'ITI – Plumber',
                'ITI Mechanic (Motor Vehicle)' => 'ITI – Mechanic (Motor Vehicle)',
                'ITI Turner' => 'ITI – Turner',
                'ITI Machinist' => 'ITI – Machinist',
                'ITI Draughtsman (Mechanical)' => 'ITI – Draughtsman (Mechanical)',
                'ITI Draughtsman (Civil)' => 'ITI – Draughtsman (Civil)',
                'ITI Electronics Mechanic' => 'ITI – Electronics Mechanic',
                'ITI Instrument Mechanic' => 'ITI – Instrument Mechanic',
                'ITI COPA' => 'ITI – COPA',
                'ITI Refrigeration and AC' => 'ITI – Refrigeration & Air Conditioning',
                'ITI Wireman' => 'ITI – Wireman',
                'ITI Painter General' => 'ITI – Painter (General)',
                'ITI Carpenter' => 'ITI – Carpenter',
                'ITI Surveyor' => 'ITI – Surveyor',
            ],
            'Diploma' => [
                'Diploma in Computer Engineering' => 'Diploma – Computer Engineering',
                'Diploma in Information Technology' => 'Diploma – Information Technology',
                'Diploma in Mechanical Engineering' => 'Diploma – Mechanical Engineering',
                'Diploma in Civil Engineering' => 'Diploma – Civil Engineering',
                'Diploma in Electrical Engineering' => 'Diploma – Electrical Engineering',
                'Diploma in Electronics and Communication' => 'Diploma – Electronics & Communication',
                'Diploma in Automobile Engineering' => 'Diploma – Automobile Engineering',
                'Diploma in Chemical Engineering' => 'Diploma – Chemical Engineering',
                'Diploma in Mechatronics' => 'Diploma – Mechatronics',
                'Diploma in Architecture' => 'Diploma – Architecture',
                'Diploma in Hotel Management' => 'Diploma – Hotel Management',
                'Diploma in Pharmacy' => 'Diploma – Pharmacy',
            ],
            "Bachelor's Degrees" => [
                'BA' => 'Bachelor of Arts (BA)',
                'BSc' => 'Bachelor of Science (BSc)',
                'BCom' => 'Bachelor of Commerce (BCom)',
                'BBA' => 'Bachelor of Business Administration (BBA)',
                'BCA' => 'Bachelor of Computer Applications (BCA)',
                'BArch' => 'Bachelor of Architecture (B.Arch)',
                'BEd' => 'Bachelor of Education (BEd)',
                'BPharm' => 'Bachelor of Pharmacy (BPharm)',
                'LLB' => 'Bachelor of Laws (LLB)',
                'MBBS' => 'MBBS',
            ],
            'BE / BTech' => [
                'BE CSE' => 'BE – Computer Science',
                'BE Mechanical' => 'BE – Mechanical',
                'BE Civil' => 'BE – Civil',
                'BE Electrical' => 'BE – Electrical',
                'BE ECE' => 'BE – Electronics & Communication',
                'BE Automobile' => 'BE – Automobile',
                'BE Chemical' => 'BE – Chemical',
                'BE Aerospace' => 'BE – Aerospace',
                'BE Mechatronics' => 'BE – Mechatronics',
                'BE Marine' => 'BE – Marine',
                'BE Mining' => 'BE – Mining',
            ],
            'ME / MTech' => [
                'ME CSE' => 'ME – Computer Science',
                'ME Mechanical' => 'ME – Mechanical',
                'ME Civil' => 'ME – Civil',
                'ME Electrical' => 'ME – Electrical',
                'ME ECE' => 'ME – Electronics & Communication',
                'ME Chemical' => 'ME – Chemical',
                'ME Structural' => 'ME – Structural Engineering',
            ],
            'Postgraduate' => [
                'MBA' => 'Master of Business Administration (MBA)',
                'MCA' => 'Master of Computer Applications (MCA)',
                'MTech' => 'Master of Technology (MTech)',
                'MSc' => 'Master of Science (MSc)',
                'MA' => 'Master of Arts (MA)',
                'MCom' => 'Master of Commerce (MCom)',
                'MPharm' => 'Master of Pharmacy (MPharm)',
                'LLM' => 'Master of Laws (LLM)',
                'MD' => 'Doctor of Medicine (MD)',
            ],
            'Other' => [
                'Other' => 'Other (please specify)',
            ],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  FORM
    // ──────────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ─────────────────────────────────────────────────────────────
                // SECTION 1 — Employment Details
                // ─────────────────────────────────────────────────────────────
                Forms\Components\Section::make('Employment Details')
                    ->description('Core employment record: joining dates, designation, and status.')
                    ->icon('heroicon-o-briefcase')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('aadhaar_number')
                            ->label('Aadhaar Number')
                            ->required()
                            ->unique(Employee::class, 'aadhaar_number', ignoreRecord: true)
                            ->length(12)
                            ->numeric()
                            ->placeholder('12-digit Aadhaar number')
                            ->helperText('Unique 12-digit government-issued identifier.')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_permanent')
                            ->label('Permanent Employee')
                            ->helperText('Toggle on if this is a permanent appointment.')
                            ->onColor('success')
                            ->offColor('warning')
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('joined_at')
                            ->label('Date of Joining')
                            ->required()
                            ->maxDate(now())
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        Forms\Components\Select::make('designation')
                            ->label('Designation')
                            ->required()
                            ->options(self::designationOptions())
                            ->searchable()
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('exited_at')
                            ->label('Date of Exit')
                            ->displayFormat('d/m/Y')
                            ->helperText('Leave blank if the employee has not exited.')
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('rejoined_at')
                            ->label('Date of Rejoining')
                            ->displayFormat('d/m/Y')
                            ->helperText('Leave blank if not applicable.')
                            ->columnSpan(1),

                        Forms\Components\Select::make('trade')
                            ->label('Trade')
                            ->required()
                            ->options([
                                'Un Skilled' => 'Un-Skilled',
                                'Semi-Skilled' => 'Semi-Skilled',
                                'Skilled' => 'Skilled',
                                'Highly Skilled' => 'Highly Skilled',
                            ])
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('skill_level')
                            ->label('Specific Skill')
                            ->placeholder('e.g. Plumbing, Shuttering, Wiring…')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\Select::make('emp_status')
                            ->label('Employment Status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'on_leave' => 'On Leave',
                                'terminated' => 'Terminated',
                                'resigned' => 'Resigned',
                            ])
                            ->default('active')
                            ->native(false)
                            ->columnSpan(1),
                    ]),

                // ─────────────────────────────────────────────────────────────
                // SECTION 2 — Personal Information
                // ─────────────────────────────────────────────────────────────
                Forms\Components\Section::make('Personal Information')
                    ->description('Legal identity and contact details of the employee.')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->required()
                            ->maxDate(now()->subYears(18))
                            ->displayFormat('d/m/Y')
                            ->helperText('Employee must be at least 18 years old.')
                            ->columnSpan(1),

                        Forms\Components\Select::make('gender')
                            ->label('Gender')
                            ->required()
                            ->options([
                                'Male' => 'Male',
                                'Female' => 'Female',
                            ])
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('guardian_name')
                            ->label('Father / Husband Name')
                            ->required()
                            ->maxLength(150)
                            ->helperText("Father\'s name (unmarried) or husband's name (married female).")
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('mobile_number')
                            ->label('Mobile Number')
                            ->required()
                            ->tel()
                            ->length(10)
                            ->numeric()
                            ->placeholder('10-digit mobile number')
                            ->columnSpan(1),

                        Forms\Components\Select::make('marital_status')
                            ->label('Marital Status')
                            ->required()
                            ->options([
                                'Married' => 'Married',
                                'UnMarried' => 'Unmarried',
                            ])
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\Select::make('blood_group')
                            ->label('Blood Group')
                            ->required()
                            ->options([
                                'A+' => 'A+',
                                'A-' => 'A-',
                                'B+' => 'B+',
                                'B-' => 'B-',
                                'AB+' => 'AB+',
                                'AB-' => 'AB-',
                                'O+' => 'O+',
                                'O-' => 'O-',
                                'Not Known' => 'Not Known',
                            ])
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nationality')
                            ->label('Nationality')
                            ->required()
                            ->default('Indian')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('identification_mark')
                            ->label('Mark of Identification')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Mole on left cheek')
                            ->columnSpan(1),
                    ]),

                // ─────────────────────────────────────────────────────────────
                // SECTION 3 — Address Details
                // ─────────────────────────────────────────────────────────────
                Forms\Components\Section::make('Address Details')
                    ->description('Permanent and present address of the employee.')
                    ->icon('heroicon-o-map-pin')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make('permanent_address')
                            ->label('Permanent Address')
                            ->required()
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('present_address')
                            ->label('Present Address')
                            ->required()
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpan(1),

                        Forms\Components\Select::make('country')
                            ->label('Country')
                            ->required()
                            ->options(['India' => 'India'])
                            ->default('India')
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('location_manual_entry')
                            ->label('Enter state, district, taluka & village/city manually')
                            ->helperText('Off: choose from list. On: type any value.')
                            ->default(false)
                            ->live()
                            ->columnSpan(1),

                        Forms\Components\Group::make()
                            ->hidden(fn (Get $get) => $get('location_manual_entry'))
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('state_dropdown')
                                        ->label('State')
                                        ->options(fn () => StatecityList::stateOptions())
                                        ->searchable()
                                        ->native(false)
                                        ->live()
                                        ->required(fn (Get $get) => ! $get('location_manual_entry'))
                                        ->afterStateUpdated(function (Set $set) {
                                            $set('district_dropdown', null);
                                            $set('taluka_dropdown', null);
                                            $set('village_dropdown', null);
                                        }),

                                    Forms\Components\Select::make('district_dropdown')
                                        ->label('District')
                                        ->options(fn (Get $get) => StatecityList::districtOptions($get('state_dropdown')))
                                        ->searchable()
                                        ->native(false)
                                        ->live()
                                        ->required(fn (Get $get) => ! $get('location_manual_entry'))
                                        ->disabled(fn (Get $get) => blank($get('state_dropdown')))
                                        ->afterStateUpdated(function (Set $set) {
                                            $set('taluka_dropdown', null);
                                            $set('village_dropdown', null);
                                        }),

                                    Forms\Components\Select::make('taluka_dropdown')
                                        ->label('Taluka')
                                        ->options(fn (Get $get) => StatecityList::talukaOptions(
                                            $get('state_dropdown'),
                                            $get('district_dropdown'),
                                        ))
                                        ->searchable()
                                        ->native(false)
                                        ->live()
                                        ->required(fn (Get $get) => ! $get('location_manual_entry'))
                                        ->disabled(fn (Get $get) => blank($get('district_dropdown')))
                                        ->afterStateUpdated(fn (Set $set) => $set('village_dropdown', null)),

                                    Forms\Components\Select::make('village_dropdown')
                                        ->label('Village / City')
                                        ->options(fn (Get $get) => StatecityList::villageOptions(
                                            $get('state_dropdown'),
                                            $get('district_dropdown'),
                                            $get('taluka_dropdown'),
                                        ))
                                        ->searchable()
                                        ->native(false)
                                        ->required(fn (Get $get) => ! $get('location_manual_entry'))
                                        ->disabled(fn (Get $get) => blank($get('taluka_dropdown'))),
                                ]),
                            ]),

                        Forms\Components\Group::make()
                            ->hidden(fn (Get $get) => ! $get('location_manual_entry'))
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('state_manual')
                                        ->label('State')
                                        ->maxLength(100)
                                        ->required(fn (Get $get) => (bool) $get('location_manual_entry')),

                                    Forms\Components\TextInput::make('district_manual')
                                        ->label('District')
                                        ->maxLength(100)
                                        ->required(fn (Get $get) => (bool) $get('location_manual_entry')),

                                    Forms\Components\TextInput::make('taluka_manual')
                                        ->label('Taluka')
                                        ->maxLength(100)
                                        ->required(fn (Get $get) => (bool) $get('location_manual_entry')),

                                    Forms\Components\TextInput::make('village_manual')
                                        ->label('Village / City')
                                        ->maxLength(150)
                                        ->required(fn (Get $get) => (bool) $get('location_manual_entry')),
                                ]),
                            ]),

                        Forms\Components\TextInput::make('pin_code')
                            ->label('Pin Code')
                            ->required()
                            ->length(6)
                            ->numeric()
                            ->placeholder('6-digit PIN code')
                            ->columnSpan(1),
                    ]),

                // ─────────────────────────────────────────────────────────────
                // SECTION 4 — Education & Qualifications
                // ─────────────────────────────────────────────────────────────
                Forms\Components\Section::make('Education & Qualifications')
                    ->description('Academic background and professional credentials.')
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('highest_education')
                            ->label('Highest Education')
                            ->maxLength(100)
                            ->placeholder('e.g. B.Tech, ITI, 12th Pass')
                            ->helperText('Optional — use Academics below for structured selection.')
                            ->columnSpan(2),

                        Forms\Components\Select::make('academics')
                            ->label('Academics / Qualification')
                            ->required()
                            ->options(self::academicsOptions())
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(
                                fn($state, Forms\Set $set) =>
                                $set('academics_other', $state !== 'Other' ? null : '')
                            )
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('academics_other')
                            ->label('Please specify your qualification')
                            ->placeholder('Enter your qualification here')
                            ->maxLength(255)
                            ->required(fn(Get $get) => $get('academics') === 'Other')
                            ->hidden(fn(Get $get) => $get('academics') !== 'Other')
                            ->columnSpan(2),
                    ]),

                // ─────────────────────────────────────────────────────────────
                // SECTION 5 — Statutory Identifiers
                // ─────────────────────────────────────────────────────────────
                Forms\Components\Section::make('Statutory Identifiers')
                    ->description('Government-issued tax and social-security numbers.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('pan_number')
                            ->label('PAN Number')
                            ->maxLength(10)
                            ->minLength(10)
                            ->rules(['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'])
                            ->placeholder('ABCDE1234F')
                            ->helperText('10-character alphanumeric PAN.')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('uan_number')
                            ->label('UAN Number')
                            ->maxLength(12)
                            ->numeric()
                            ->placeholder('12-digit UAN')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('esic_number')
                            ->label('ESIC Number')
                            ->maxLength(17)
                            ->placeholder('17-digit ESIC number')
                            ->columnSpan(1),
                    ]),

                // ─────────────────────────────────────────────────────────────
                // SECTION 6 — Bank Details
                // ─────────────────────────────────────────────────────────────
                Forms\Components\Section::make('Bank Details')
                    ->description('Salary credit bank account information.')
                    ->icon('heroicon-o-banknotes')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->required()
                            ->maxLength(150)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->required()
                            ->maxLength(20)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('bank_ifsc_code')
                            ->label('IFSC Code')
                            ->required()
                            ->maxLength(11)
                            ->minLength(11)
                            ->rules(['regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'])
                            ->placeholder('e.g. SBIN0001234')
                            ->helperText('11-character IFSC code.')
                            ->columnSpan(1),
                    ]),

                // ─────────────────────────────────────────────────────────────
                // SECTION 7 — Nominee Details
                // ─────────────────────────────────────────────────────────────
                Forms\Components\Section::make('Nominee Details')
                    ->description('Next-of-kin or insurance nominee information.')
                    ->icon('heroicon-o-user-group')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('nominee_name')
                            ->label('Nominee Name')
                            ->required()
                            ->maxLength(150)
                            ->columnSpan(1),

                        Forms\Components\Select::make('nominee_relationship')
                            ->label('Relationship')
                            ->required()
                            ->options([
                                'Mother' => 'Mother',
                                'Father' => 'Father',
                                'Wife' => 'Wife',
                                'Husband' => 'Husband',
                                'Brother' => 'Brother',
                                'Sister' => 'Sister',
                                'Child' => 'Child',
                                'Other' => 'Other',
                            ])
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('nominee_date_of_birth')
                            ->label('Nominee Date of Birth')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nominee_mobile_number')
                            ->label('Nominee Mobile Number')
                            ->required()
                            ->tel()
                            ->length(10)
                            ->numeric()
                            ->placeholder('10-digit mobile number')
                            ->columnSpan(1),
                    ]),

                // ─────────────────────────────────────────────────────────────
                // SECTION 8 — Document Uploads
                // ─────────────────────────────────────────────────────────────
                Forms\Components\Section::make('Document Uploads')
                    ->description('Provide documents via file upload or plain text. Photo must be an image; file uploads must be PDF. Max 5 MB each.')
                    ->icon('heroicon-o-paper-clip')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\FileUpload::make('doc_photo')
                            ->label('Employee Photo')
                            ->disk('public')
                            ->directory('employee-docs/photos')
                            ->image()
                            ->acceptedFileTypes(['image/png', 'image/jpeg'])
                            ->maxSize(2048)
                            ->imagePreviewHeight('120')
                            ->helperText('PNG or JPG, max 2 MB.')
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),

                        // Aadhaar Card
                        Forms\Components\Group::make()->schema([
                            Forms\Components\ToggleButtons::make('doc_aadhaar_mode')
                                ->label('Aadhaar Card Input')
                                ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                                ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                                ->colors(['file' => 'primary', 'text' => 'info'])
                                ->default('file')
                                ->inline()
                                ->live(),

                            Forms\Components\FileUpload::make('doc_aadhaar')
                                ->label('Aadhaar Card (PDF)')
                                ->disk('public')
                                ->directory('employee-docs/aadhaar')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->downloadable()
                                ->openable()
                                ->visible(fn(Get $get) => $get('doc_aadhaar_mode') === 'file'),

                            Forms\Components\Textarea::make('doc_aadhaar_text')
                                ->label('Aadhaar Content')
                                ->placeholder("Type or paste Aadhaar details here.")
                                ->rows(3)
                                ->visible(fn(Get $get) => $get('doc_aadhaar_mode') === 'text'),
                        ])->columnSpan(1),

                        // PAN Card
                        Forms\Components\Group::make()->schema([
                            Forms\Components\ToggleButtons::make('doc_pan_mode')
                                ->label('PAN Card Input')
                                ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                                ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                                ->colors(['file' => 'primary', 'text' => 'info'])
                                ->default('file')
                                ->inline()
                                ->live(),

                            Forms\Components\FileUpload::make('doc_pan')
                                ->label('PAN Card (PDF)')
                                ->disk('public')
                                ->directory('employee-docs/pan')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->downloadable()
                                ->openable()
                                ->visible(fn(Get $get) => $get('doc_pan_mode') === 'file'),

                            Forms\Components\Textarea::make('doc_pan_text')
                                ->label('PAN Content')
                                ->placeholder("Type or paste PAN details here.")
                                ->rows(3)
                                ->visible(fn(Get $get) => $get('doc_pan_mode') === 'text'),
                        ])->columnSpan(1),

                        // Bank Passbook
                        Forms\Components\Group::make()->schema([
                            Forms\Components\ToggleButtons::make('doc_bank_passbook_mode')
                                ->label('Bank Passbook Input')
                                ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                                ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                                ->colors(['file' => 'primary', 'text' => 'info'])
                                ->default('file')
                                ->inline()
                                ->live(),

                            Forms\Components\FileUpload::make('doc_bank_passbook')
                                ->label('Bank Passbook / Cheque (PDF)')
                                ->disk('public')
                                ->directory('employee-docs/bank')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->downloadable()
                                ->openable()
                                ->visible(fn(Get $get) => $get('doc_bank_passbook_mode') === 'file'),

                            Forms\Components\Textarea::make('doc_bank_passbook_text')
                                ->label('Bank Account Content')
                                ->placeholder("Type or paste Bank Details here.")
                                ->rows(3)
                                ->visible(fn(Get $get) => $get('doc_bank_passbook_mode') === 'text'),
                        ])->columnSpan(1),

                        // Education Certificate
                        Forms\Components\Group::make()->schema([
                            Forms\Components\ToggleButtons::make('doc_education_certificate_mode')
                                ->label('Education Certificate Input')
                                ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                                ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                                ->colors(['file' => 'primary', 'text' => 'info'])
                                ->default('file')
                                ->inline()
                                ->live(),

                            Forms\Components\FileUpload::make('doc_education_certificate')
                                ->label('Education Certificate (PDF)')
                                ->disk('public')
                                ->directory('employee-docs/education')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->downloadable()
                                ->openable()
                                ->visible(fn(Get $get) => $get('doc_education_certificate_mode') === 'file'),

                            Forms\Components\Textarea::make('doc_education_certificate_text')
                                ->label('Education Details Content')
                                ->placeholder("Type or paste Education credentials here.")
                                ->rows(3)
                                ->visible(fn(Get $get) => $get('doc_education_certificate_mode') === 'text'),
                        ])->columnSpan(1),
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
                // Photo thumbnail
                Tables\Columns\ImageColumn::make('doc_photo')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->width(40)
                    ->height(40),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Employee Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name'])
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('aadhaar_number')
                    ->label('Aadhaar')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Aadhaar copied')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('designation')
                    ->label('Designation')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('trade')
                    ->label('Trade')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Highly Skilled' => 'success',
                        'Skilled' => 'info',
                        'Semi-Skilled' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('mobile_number')
                    ->label('Mobile')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Mobile copied'),

                Tables\Columns\TextColumn::make('emp_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'on_leave' => 'warning',
                        'terminated' => 'danger',
                        'resigned' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_permanent')
                    ->label('Permanent')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Joined')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('state')
                    ->label('State')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('district')
                    ->label('District')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pin_code')
                    ->label('PIN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered On')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('emp_status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'on_leave' => 'On Leave',
                        'terminated' => 'Terminated',
                        'resigned' => 'Resigned',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('designation')
                    ->label('Designation')
                    ->options(self::designationOptions())
                    ->searchable()
                    ->native(false),

                Tables\Filters\SelectFilter::make('trade')
                    ->label('Trade')
                    ->options([
                        'Un Skilled' => 'Un-Skilled',
                        'Semi-Skilled' => 'Semi-Skilled',
                        'Skilled' => 'Skilled',
                        'Highly Skilled' => 'Highly Skilled',
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_permanent')
                    ->label('Permanent Employee')
                    ->trueLabel('Permanent only')
                    ->falseLabel('Contract only')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('terminate')
                    ->label('Terminate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Terminate Employee')
                    ->modalDescription('This will mark the employee as terminated. You can reverse this by editing the record.')
                    ->visible(fn(Employee $r) => $r->emp_status === 'active')
                    ->action(fn(Employee $r) => $r->update(['emp_status' => 'terminated'])),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_active')
                        ->label('Mark as Active')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn($records) => $records->each->update(['emp_status' => 'active'])),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withTrashed());
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  INFOLIST  (View page)
    // ──────────────────────────────────────────────────────────────────────────
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ── Header strip: photo + name + status badges ────────────────
            Infolists\Components\Section::make()
                ->schema([
                    Infolists\Components\ImageEntry::make('doc_photo')
                        ->label('')
                        ->disk('public')
                        ->circular()
                        ->height(80)
                        ->columnSpan(1),

                    Infolists\Components\Group::make([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Employee')
                            ->getStateUsing(fn(Employee $r) => "{$r->first_name} {$r->last_name}")
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('emp_status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn(string $state) => ucfirst(str_replace('_', ' ', $state)))
                            ->color(fn(string $state) => match ($state) {
                                'active' => 'success',
                                'on_leave' => 'warning',
                                'terminated' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\IconEntry::make('is_permanent')
                            ->label('Permanent')
                            ->boolean()
                            ->trueColor('success'),
                    ])->columnSpan(3),
                ])
                ->columns(4),

            // ── Employment Details ────────────────────────────────────────
            Infolists\Components\Section::make('Employment Details')
                ->icon('heroicon-o-briefcase')
                ->columns(3)
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('aadhaar_number')->label('Aadhaar Number')->copyable(),
                    Infolists\Components\TextEntry::make('designation')->label('Designation')->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('trade')->label('Trade'),
                    Infolists\Components\TextEntry::make('skill_level')->label('Skill')->placeholder('—'),
                    Infolists\Components\TextEntry::make('joined_at')->label('Date of Joining')->date('d M Y'),
                    Infolists\Components\TextEntry::make('exited_at')->label('Date of Exit')->date('d M Y')->placeholder('—'),
                    Infolists\Components\TextEntry::make('rejoined_at')->label('Rejoining Date')->date('d M Y')->placeholder('—'),
                ]),

            // ── Personal Information ──────────────────────────────────────
            Infolists\Components\Section::make('Personal Information')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('date_of_birth')->label('Date of Birth')->date('d M Y'),
                    Infolists\Components\TextEntry::make('gender')->label('Gender'),
                    Infolists\Components\TextEntry::make('blood_group')->label('Blood Group')->badge()->color('danger'),
                    Infolists\Components\TextEntry::make('guardian_name')->label('Father / Husband'),
                    Infolists\Components\TextEntry::make('mobile_number')->label('Mobile')->copyable(),
                    Infolists\Components\TextEntry::make('marital_status')->label('Marital Status'),
                    Infolists\Components\TextEntry::make('nationality')->label('Nationality'),
                    Infolists\Components\TextEntry::make('identification_mark')->label('Identification Mark'),
                ]),

            // ── Address Details ───────────────────────────────────────────
            Infolists\Components\Section::make('Address Details')
                ->icon('heroicon-o-map-pin')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('permanent_address')->label('Permanent Address'),
                    Infolists\Components\TextEntry::make('present_address')->label('Present Address'),
                    Infolists\Components\TextEntry::make('country')->label('Country'),
                    Infolists\Components\TextEntry::make('state')->label('State'),
                    Infolists\Components\TextEntry::make('district')->label('District'),
                    Infolists\Components\TextEntry::make('taluka')->label('Taluka'),
                    Infolists\Components\TextEntry::make('village_city')->label('Village / City'),
                    Infolists\Components\TextEntry::make('pin_code')->label('Pin Code'),
                ]),

            // ── Education ────────────────────────────────────────────────
            Infolists\Components\Section::make('Education & Qualifications')
                ->icon('heroicon-o-academic-cap')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('highest_education')->label('Highest Education')->placeholder('—'),
                    Infolists\Components\TextEntry::make('academics')->label('Qualification'),
                ]),

            // ── Statutory ────────────────────────────────────────────────
            Infolists\Components\Section::make('Statutory Identifiers')
                ->icon('heroicon-o-shield-check')
                ->columns(3)
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('pan_number')->label('PAN Number')->copyable()->placeholder('—'),
                    Infolists\Components\TextEntry::make('uan_number')->label('UAN Number')->copyable()->placeholder('—'),
                    Infolists\Components\TextEntry::make('esic_number')->label('ESIC Number')->copyable()->placeholder('—'),
                ]),

            // ── Bank Details ──────────────────────────────────────────────
            Infolists\Components\Section::make('Bank Details')
                ->icon('heroicon-o-banknotes')
                ->columns(3)
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('bank_name')->label('Bank Name'),
                    Infolists\Components\TextEntry::make('bank_account_number')->label('Account Number')->copyable(),
                    Infolists\Components\TextEntry::make('bank_ifsc_code')->label('IFSC Code')->copyable(),
                ]),

            // ── Nominee ───────────────────────────────────────────────────
            Infolists\Components\Section::make('Nominee Details')
                ->icon('heroicon-o-user-group')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('nominee_name')->label('Nominee Name'),
                    Infolists\Components\TextEntry::make('nominee_relationship')->label('Relationship'),
                    Infolists\Components\TextEntry::make('nominee_date_of_birth')->label('Nominee DOB')->date('d M Y'),
                    Infolists\Components\TextEntry::make('nominee_mobile_number')->label('Nominee Mobile')->copyable(),
                ]),

            // ── Documents ─────────────────────────────────────────────────
            Infolists\Components\Section::make('Uploaded Documents')
            ->icon('heroicon-o-paper-clip')
            ->columns(2)
            ->collapsible()
            ->schema([
                // Aadhaar
                Infolists\Components\Group::make()->schema([
                    Infolists\Components\TextEntry::make('doc_aadhaar_mode')
                        ->label('Aadhaar Format')
                        ->badge()
                        ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                        ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                    Infolists\Components\TextEntry::make('doc_aadhaar')
                        ->label('Aadhaar PDF')
                        ->placeholder('Not uploaded')
                        ->visible(fn(Employee $record) => $record->doc_aadhaar_mode === 'file'),
                    Infolists\Components\TextEntry::make('doc_aadhaar_text')
                        ->label('Aadhaar Text Content')
                        ->placeholder('Not provided')
                        ->visible(fn(Employee $record) => $record->doc_aadhaar_mode === 'text'),
                ])->columnSpan(1),

                // PAN
                Infolists\Components\Group::make()->schema([
                    Infolists\Components\TextEntry::make('doc_pan_mode')
                        ->label('PAN Format')
                        ->badge()
                        ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                        ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                    Infolists\Components\TextEntry::make('doc_pan')
                        ->label('PAN PDF')
                        ->placeholder('Not uploaded')
                        ->visible(fn(Employee $record) => $record->doc_pan_mode === 'file'),
                    Infolists\Components\TextEntry::make('doc_pan_text')
                        ->label('PAN Text Content')
                        ->placeholder('Not provided')
                        ->visible(fn(Employee $record) => $record->doc_pan_mode === 'text'),
                ])->columnSpan(1),

                // Bank Passbook
                Infolists\Components\Group::make()->schema([
                    Infolists\Components\TextEntry::make('doc_bank_passbook_mode')
                        ->label('Bank Passbook Format')
                        ->badge()
                        ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                        ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                    Infolists\Components\TextEntry::make('doc_bank_passbook')
                        ->label('Bank Passbook PDF')
                        ->placeholder('Not uploaded')
                        ->visible(fn(Employee $record) => $record->doc_bank_passbook_mode === 'file'),
                    Infolists\Components\TextEntry::make('doc_bank_passbook_text')
                        ->label('Bank Passbook Text Content')
                        ->placeholder('Not provided')
                        ->visible(fn(Employee $record) => $record->doc_bank_passbook_mode === 'text'),
                ])->columnSpan(1),

                // Education Certificate
                Infolists\Components\Group::make()->schema([
                    Infolists\Components\TextEntry::make('doc_education_certificate_mode')
                        ->label('Education Cert Format')
                        ->badge()
                        ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                        ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                    Infolists\Components\TextEntry::make('doc_education_certificate')
                        ->label('Education Cert PDF')
                        ->placeholder('Not uploaded')
                        ->visible(fn(Employee $record) => $record->doc_education_certificate_mode === 'file'),
                    Infolists\Components\TextEntry::make('doc_education_certificate_text')
                        ->label('Education Cert Text Content')
                        ->placeholder('Not provided')
                        ->visible(fn(Employee $record) => $record->doc_education_certificate_mode === 'text'),
                ])->columnSpan(1),
            ]),

        ]);
    }
    // ──────────────────────────────────────────────────────────────────────────
    //  Ledger
    // ──────────────────────────────────────────────────────────────────────────
    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\EmployeeResource\RelationManagers\LedgerTransactionsRelationManager::class,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  PAGES
    // ──────────────────────────────────────────────────────────────────────────
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            // 'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('emp_status', 'active')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'success';
    }

    public static function getNavigationBadgeTooltip(): string
    {
        return 'Active employees';
    }

    /**
     * Map persisted location columns into form virtual fields (dropdown vs manual).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function hydrateLocationVirtualFields(array $data): array
    {
        $manual = (bool) ($data['location_manual_entry'] ?? false);

        if ($manual) {
            $data['state_manual'] = $data['state'] ?? null;
            $data['district_manual'] = $data['district'] ?? null;
            $data['taluka_manual'] = $data['taluka'] ?? null;
            $data['village_manual'] = $data['village_city'] ?? null;
            $data['state_dropdown'] = null;
            $data['district_dropdown'] = null;
            $data['taluka_dropdown'] = null;
            $data['village_dropdown'] = null;
        } else {
            $data['state_dropdown'] = $data['state'] ?? null;
            $data['district_dropdown'] = $data['district'] ?? null;
            $data['taluka_dropdown'] = $data['taluka'] ?? null;
            $data['village_dropdown'] = $data['village_city'] ?? null;
            $data['state_manual'] = null;
            $data['district_manual'] = null;
            $data['taluka_manual'] = null;
            $data['village_manual'] = null;
        }

        return $data;
    }

    /**
     * Merge virtual location fields into persisted columns and remove virtual keys.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function collapseLocationVirtualFields(array $data): array
    {
        $manual = (bool) ($data['location_manual_entry'] ?? false);

        $data['state'] = $manual ? ($data['state_manual'] ?? null) : ($data['state_dropdown'] ?? null);
        $data['district'] = $manual ? ($data['district_manual'] ?? null) : ($data['district_dropdown'] ?? null);
        $data['taluka'] = $manual ? ($data['taluka_manual'] ?? null) : ($data['taluka_dropdown'] ?? null);
        $data['village_city'] = $manual ? ($data['village_manual'] ?? null) : ($data['village_dropdown'] ?? null);

        unset(
            $data['state_dropdown'],
            $data['state_manual'],
            $data['district_dropdown'],
            $data['district_manual'],
            $data['taluka_dropdown'],
            $data['taluka_manual'],
            $data['village_dropdown'],
            $data['village_manual'],
        );

        return $data;
    }
}