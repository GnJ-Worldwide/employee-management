<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasLabel, HasColor
{
    case Present            = 'Present';
    case Absent             = 'Absent';
    case PresentWithOT      = 'Present with Overtime';
    case NationalHoliday    = 'National Holiday';
    case HeadOffice         = 'Head Office';
    case Travelling         = 'Travelling';
    case PrivilegeLeave     = 'Privilege Leave';
    case CasualLeave        = 'Casual Leave';
    case SickLeave          = 'Sick Leave';
    case SiteTransfer       = 'Site Transfer';
    case OutOfDuty          = 'Out of Duty';

    // ── Filament label ──────────────────────────────────────────────────────

    public function getLabel(): string
    {
        return $this->value;
    }

    // ── Filament badge colour ───────────────────────────────────────────────

    public function getColor(): string
    {
        return match ($this) {
            self::Present, self::PresentWithOT => 'success',
            self::Absent                        => 'danger',
            self::NationalHoliday               => 'info',
            self::OutOfDuty                     => 'warning',
            default                             => 'gray',
        };
    }

    // ── Short codes for the printed muster sheet ────────────────────────────

    public function shortCode(): string
    {
        return match ($this) {
            self::Present          => 'P',
            self::Absent           => 'A',
            self::PresentWithOT    => 'P+OT',
            self::NationalHoliday  => 'NH',
            self::HeadOffice       => 'HO',
            self::Travelling       => 'TR',
            self::PrivilegeLeave   => 'PL',
            self::CasualLeave      => 'CL',
            self::SickLeave        => 'SL',
            self::SiteTransfer     => 'ST',
            self::OutOfDuty        => 'OD',
        };
    }

    // ── Business helpers ────────────────────────────────────────────────────

    /** Counts as a working/paid day on the muster sheet. */
    public function isPresent(): bool
    {
        return match ($this) {
            self::Absent, self::OutOfDuty => false,
            default                       => true,
        };
    }

    /** Does this status allow overtime entry? */
    public function allowsOvertime(): bool
    {
        return $this === self::PresentWithOT;
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** All statuses as [value => label] for Filament select options. */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->value])
            ->all();
    }
}