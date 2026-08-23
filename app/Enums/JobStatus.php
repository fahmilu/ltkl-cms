<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Whether a job opportunity still takes applications.
 *
 * The status is set by hand: a vacancy stays open until it is closed, even once
 * its deadline has passed, so a late applicant is never silently turned away by
 * the clock alone.
 */
enum JobStatus: string implements HasColor, HasLabel
{
    case OPEN = 'open';
    case CLOSED = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::CLOSED => 'Closed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OPEN => 'success',
            self::CLOSED => 'danger',
        };
    }

    /**
     * The status of a vacancy, defaulting to OPEN.
     */
    public static function fromState($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return is_string($value)
            ? (self::tryFrom($value) ?? self::OPEN)
            : self::OPEN;
    }
}
