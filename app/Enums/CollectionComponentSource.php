<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Which dataset the page builder's Collection block renders.
 *
 * The block only stores the choice; the frontend fetches the matching endpoint.
 */
enum CollectionComponentSource: string implements HasLabel
{
    case KABUPATEN_MAP = 'kabupaten_map';
    case PILLARS = 'pillars';
    case PARTICIPATION_PATHWAYS = 'participation_pathways';

    public function getLabel(): string
    {
        return match ($this) {
            self::KABUPATEN_MAP => 'Kabupaten Map',
            self::PILLARS => 'Pillars',
            self::PARTICIPATION_PATHWAYS => 'Participation Pathways',
        };
    }

    /**
     * The API endpoint the frontend should read for this source.
     */
    public function getEndpoint(): string
    {
        return match ($this) {
            self::KABUPATEN_MAP => '/api/kabupatens/map',
            self::PILLARS => '/api/pillars',
            self::PARTICIPATION_PATHWAYS => '/api/participation-pathways',
        };
    }
}
