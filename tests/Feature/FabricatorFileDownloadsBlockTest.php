<?php

use App\Filament\Fabricator\PageBlocks\FileDownloads;
use Illuminate\Support\Facades\Blade;

it('normalizes uploaded and external file downloads', function () {
    $data = FileDownloads::mutateData([
        'downloads' => [
            [
                'title' => 'Risk assessment',
                'description' => 'Health and safety document.',
                'file_source' => 'upload',
                'file_upload_path' => ['page-blocks/downloads/risk-assessment.pdf'],
                'button_text' => '',
            ],
            [
                'title' => 'Annual report',
                'file_source' => 'url',
                'file_url' => 'https://example.com/reports/annual-report.pdf',
                'button_text' => 'Read the report',
            ],
            [
                'title' => '',
                'file_source' => 'url',
                'file_url' => 'https://example.com/missing-title.pdf',
            ],
        ],
    ]);

    expect($data['downloads'])->toHaveCount(2)
        ->and($data['downloads'][0]['url'])->toBe('http://cue.test/storage/page-blocks/downloads/risk-assessment.pdf')
        ->and($data['downloads'][0]['button_text'])->toBe('View / download')
        ->and($data['downloads'][0]['extension'])->toBe('PDF')
        ->and($data['downloads'][1]['url'])->toBe('https://example.com/reports/annual-report.pdf')
        ->and($data['downloads'][1]['button_text'])->toBe('Read the report');
});

it('renders a file downloads block', function () {
    $html = Blade::render(
        '<x-filament-fabricator.page-blocks.file-downloads
            title="Useful documents"
            subtitle="Download reports and risk assessments."
            :downloads="$downloads"
        />',
        [
            'downloads' => [
                [
                    'title' => 'Risk assessment',
                    'description' => 'Health and safety document.',
                    'url' => 'https://example.com/risk-assessment.pdf',
                    'button_text' => 'View / download',
                    'extension' => 'PDF',
                ],
            ],
        ],
    );

    expect($html)->toContain('Useful documents')
        ->and($html)->toContain('Download reports and risk assessments.')
        ->and($html)->toContain('Risk assessment')
        ->and($html)->toContain('Health and safety document.')
        ->and($html)->toContain('PDF')
        ->and($html)->toContain('href="https://example.com/risk-assessment.pdf"')
        ->and($html)->toContain('target="_blank"')
        ->and($html)->toContain('View / download');
});

it('does not render without downloads', function () {
    $html = Blade::render('<x-filament-fabricator.page-blocks.file-downloads />');

    expect(trim($html))->toBe('');
});
