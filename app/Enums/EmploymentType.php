<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The engagement a job opportunity is offered under.
 *
 * The stored value is language neutral; the frontend renders its own wording
 * per language, the way it does for every other typed field.
 */
enum EmploymentType: string implements HasLabel
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case CONTRACT = 'contract';
    case CONSULTANT = 'consultant';
    case INTERNSHIP = 'internship';
    case VOLUNTEER = 'volunteer';

    public function getLabel(): string
    {
        return match ($this) {
            self::FULL_TIME => 'Full Time',
            self::PART_TIME => 'Part Time',
            self::CONTRACT => 'Contract',
            self::CONSULTANT => 'Consultant',
            self::INTERNSHIP => 'Internship',
            self::VOLUNTEER => 'Volunteer',
        };
    }
}
