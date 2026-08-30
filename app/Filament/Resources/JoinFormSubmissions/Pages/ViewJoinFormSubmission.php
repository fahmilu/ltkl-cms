<?php

namespace App\Filament\Resources\JoinFormSubmissions\Pages;

use App\Filament\Resources\JoinFormSubmissions\JoinFormSubmissionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewJoinFormSubmission extends ViewRecord
{
    protected static string $resource = JoinFormSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
