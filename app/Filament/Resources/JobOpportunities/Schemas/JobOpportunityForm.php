<?php

namespace App\Filament\Resources\JobOpportunities\Schemas;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Filament\Helpers\FormHelper;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class JobOpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([

            Grid::make()
                ->schema([
                    Tabs::make('Tabs')
                        ->tabs([
                            self::getTabs('id'),
                            self::getTabs('en'),
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpan([ // left section
                    'md' => 12,
                    'lg' => 8,
                ]),

            Grid::make()
                ->schema([
                    Section::make('Publishing')
                        ->schema([
                            Toggle::make('is_active')
                                ->label('Set as published')
                                ->onColor('primary')
                                ->offColor(null)
                                ->onIcon(Heroicon::Check)
                                ->offIcon(Heroicon::XMark)
                                ->default(true)
                                ->columnSpanFull(),
                            // Closing a vacancy keeps it published: the page stays
                            // readable, it just no longer takes applications.
                            Select::make('status')
                                ->label('Status')
                                ->options(JobStatus::class)
                                ->default(JobStatus::OPEN->value)
                                ->formatStateUsing(fn($state): string => JobStatus::fromState($state)->value)
                                ->selectablePlaceholder(false)
                                ->native(false)
                                ->required()
                                ->columnSpanFull(),
                        ])->columnSpanFull(),

                    Section::make('Details')
                        ->schema([
                            Select::make('employment_type')
                                ->label('Employment Type')
                                ->options(EmploymentType::class)
                                ->native(false)
                                ->nullable()
                                ->columnSpanFull(),
                            DatePicker::make('posted_at')
                                ->label('Posted date')
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->default(now())
                                ->columnSpanFull(),
                            DatePicker::make('deadline_at')
                                ->label('Application deadline')
                                ->helperText('Shown on the vacancy. It does not close the vacancy on its own.')
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->nullable()
                                ->columnSpanFull(),
                        ])->columnSpanFull(),

                    Section::make('Applying')
                        ->schema([
                            TextInput::make('contact_email')
                                ->label('Contact email')
                                ->placeholder('recruitment@kabupatenlestari.org')
                                ->email()
                                ->nullable()
                                ->columnSpanFull(),
                            TextInput::make('apply_url')
                                ->label('Apply URL')
                                ->placeholder('Input apply url...')
                                ->url()
                                ->suffixIcon(Heroicon::GlobeAlt)
                                ->nullable()
                                ->columnSpanFull(),
                            FileUpload::make('attachment')
                                ->label('Job description file')
                                ->helperText('Terms of reference, ideally max size are 50 MB.')
                                ->openable()->downloadable()
                                ->maxSize(52428800)
                                ->directory('job_opportunities')
                                ->disk('public')
                                ->visibility('public')
                                ->preserveFilenames()
                                ->acceptedFileTypes(config('filesystems.file_mimes'))
                                ->nullable()
                                ->columnSpanFull(),
                        ])->columnSpanFull(),
                ])
                ->columnSpan([ // right section
                    'md' => 12,
                    'lg' => 4,
                ]),
        ])->columns(12);
    }

    private static function getTabs($lang_code)
    {
        $suffix = $lang_code === 'en' ? '' : '_id';
        $isEnglish = $lang_code === 'en';

        return Tabs\Tab::make($isEnglish ? 'English' : 'Indonesian')
            ->icon(Heroicon::OutlinedDocumentText)
            ->schema([
                TextInput::make('title' . $suffix)
                    ->label('Title')
                    ->placeholder($isEnglish ? 'Consultant - Sustainable Investment Development' : 'Konsultan - Sustainable Investment Development')
                    ->required()->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) use ($suffix) {
                        $set('slug' . $suffix, Str::slug($state));
                    })
                    ->columnSpan(1),

                TextInput::make('slug' . $suffix)
                    ->label('Slug')
                    ->placeholder('Input slug...')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->columnSpan(1),

                TextInput::make('location' . $suffix)
                    ->label('Location')
                    ->placeholder('Jakarta, Indonesia')
                    ->nullable()
                    ->columnSpanFull(),

                FormHelper::makeRichEditor('description' . $suffix, 'Description'),

                FormHelper::makeRichEditor('how_to_apply' . $suffix, 'How to Apply'),
            ])
            ->columns(2)
            ->columnSpanFull();
    }
}
