<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CollectionType: string implements HasLabel
{
    // case CLIENT = 'client';
    // case SERVICE = 'service';
    case TOPIC = 'topic';
    case TAG = 'tag';
    // Participation pathways moved to their own resource under Masters.
    // case PARTICIPATION_PATHWAY = 'participation_pathway';
    // case TEAM = 'team';

    public function getLabel(): string
    {
        return match ($this) {
            // self::CLIENT => 'Clients',
            // self::SERVICE => 'Services',
            self::TOPIC => 'Categories',
            self::TAG => 'Tags',
            // self::TEAM => 'Teams',
        };
    }
}

