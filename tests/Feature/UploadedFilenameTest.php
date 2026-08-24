<?php

use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Build the temporary file Livewire hands to Filament for a given upload name.
 */
function temporaryUpload(string $originalName): TemporaryUploadedFile
{
    Storage::fake(FileUploadConfiguration::disk());

    $file = UploadedFile::fake()->create($originalName, 10);
    $name = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);

    $file->storeAs(FileUploadConfiguration::path(), $name, [
        'disk' => FileUploadConfiguration::disk(),
    ]);

    return new TemporaryUploadedFile($name, FileUploadConfiguration::disk());
}

it('slugs a preserved filename so the stored URL survives sanitising', function () {
    $upload = FileUpload::make('image')->preserveFilenames();

    $name = $upload->getUploadedFileNameForStorage(temporaryUpload('Clip path frame.png'));

    expect($name)->toBe('clip-path-frame.png')
        ->and(Str::sanitizeUrl('https://example.test/storage/posts/' . $name))->not->toBe('');
});

it('keeps a generated name when filenames are not preserved', function () {
    $upload = FileUpload::make('image');

    $name = $upload->getUploadedFileNameForStorage(temporaryUpload('Clip path frame.png'));

    expect($name)->not->toBe('clip-path-frame.png')
        ->and($name)->toEndWith('.png')
        ->and(Str::isUlid(pathinfo($name, PATHINFO_FILENAME)))->toBeTrue();
});

it('falls back to a generated name when nothing survives the slug', function () {
    $upload = FileUpload::make('image')->preserveFilenames();

    $name = $upload->getUploadedFileNameForStorage(temporaryUpload('中文.png'));

    expect($name)->toEndWith('.png')
        ->and(pathinfo($name, PATHINFO_FILENAME))->not->toBe('');
});
