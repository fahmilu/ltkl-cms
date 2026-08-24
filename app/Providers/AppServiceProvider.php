<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Navigation\NavigationGroup;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->sanitiseUploadedFilenames();

        Notifications::alignment(Alignment::Center);
        Notifications::verticalAlignment(VerticalAlignment::End);
        Filament::registerNavigationGroups([
            NavigationGroup::make('Contents')
                ->label('Contents'),
            NavigationGroup::make('Masters')
                ->label('Masters'),
            NavigationGroup::make('Administration')
                ->label('Administration'),
            NavigationGroup::make('Settings')
                ->label('Settings'),
        ]);
    }

    /**
     * Keep uploaded filenames URL safe.
     *
     * Filament reads a stored file back through Str::sanitizeUrl(), which
     * returns an empty string for a URL holding a space or any other character
     * that has to be encoded. The upload itself succeeds, but the field renders
     * an empty, "undefined" file after the record is saved and the form is
     * refilled. Slugging the name at upload time keeps that from happening,
     * while leaving the readable filename that preserveFilenames() is for.
     *
     * Fields that do not preserve filenames keep Filament's own ULID name.
     */
    private function sanitiseUploadedFilenames(): void
    {
        BaseFileUpload::configureUsing(function (BaseFileUpload $component): void {
            $component->getUploadedFileNameForStorageUsing(
                static function (BaseFileUpload $component, TemporaryUploadedFile $file): string {
                    $extension = $file->getClientOriginalExtension();

                    if (! $component->shouldPreserveFilenames()) {
                        return Str::ulid() . '.' . $extension;
                    }

                    $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

                    // A name made only of characters the slug drops leaves
                    // nothing to store under, so fall back to a generated one.
                    if ($name === '') {
                        $name = (string) Str::ulid();
                    }

                    return $extension === '' ? $name : $name . '.' . $extension;
                }
            );
        });
    }
}
