<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — the main DB is remote and these tests don't need it, except
// the one dedicated nation-matching test below, which cleans up after itself.
uses(Tests\TestCase::class);

use App\Services\WorkerAiExtractor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

it('extracts and normalizes iqama/passport fields from a Gemini response', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'worker_name' => 'محمد أحمد',
                        'ssn' => '٢٤٥٦٧٨٩٠١٢', // Arabic-Indic digits
                        'passport_no' => 'A1234567',
                        'dob' => '1990-05-01',
                        'doe' => '2027-01-15',
                        'dop' => '2029-03-20',
                        'nationality' => null,
                        'field_confidence' => [
                            'ssn' => 0.95,
                            'passport_no' => 0.8,
                            'dob' => 0.9,
                            'doe' => 0.92,
                            'dop' => 0.85,
                        ],
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'wai').'.jpg';
    file_put_contents($tmp, 'fake-image-bytes');

    $r = app(WorkerAiExtractor::class)->extract($tmp);

    expect($r['worker_name'])->toBe('محمد أحمد');
    expect($r['ssn'])->toBe('2456789012'); // Arabic digits converted to Latin
    expect($r['passport_no'])->toBe('A1234567');
    expect($r['dob'])->toBe('1990-05-01');
    expect($r['doe'])->toBe('2027-01-15');
    expect($r['dop'])->toBe('2029-03-20');
    expect($r['nation_id'])->toBeNull();
    expect($r['field_confidence']['ssn'])->toBe(0.95);

    Http::assertSent(fn ($req) => str_contains($req->url(), ':generateContent')
        && data_get($req->data(), 'generationConfig.responseMimeType') === 'application/json');

    @unlink($tmp);
});

it('does not invent an ID number and clamps confidence to 0..1', function () {
    config()->set('services.gemini.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'worker_name' => 'John Doe',
                        'ssn' => null, // unclear scan — must not be guessed
                        'passport_no' => null,
                        'dob' => null,
                        'doe' => null,
                        'dop' => null,
                        'nationality' => null,
                        'field_confidence' => ['ssn' => 1.5, 'passport_no' => -0.4],
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'wai').'.jpg';
    file_put_contents($tmp, 'fake-image-bytes');

    $r = app(WorkerAiExtractor::class)->extract($tmp);

    expect($r['ssn'])->toBeNull();
    expect($r['passport_no'])->toBeNull();
    expect($r['field_confidence']['ssn'])->toBe(1.0);
    expect($r['field_confidence']['passport_no'])->toBe(0.0);

    @unlink($tmp);
});

it('matches a free-text nationality hint against the real nation table', function () {
    if (! Schema::hasTable('nation')) {
        test()->markTestSkipped('nation table not present in this test DB snapshot');
    }

    config()->set('services.gemini.key', 'test-key');

    $nationId = DB::table('nation')->insertGetId([
        'nation_name_en' => 'Egypt',
        'nation_name_ar' => 'مصر',
        'country_enNationality' => 'Egyptian',
        'country_arNationality' => 'مصري',
    ]);

    try {
        Http::fake([
            '*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            'worker_name' => 'محمد أحمد',
                            'ssn' => '2456789012',
                            'passport_no' => 'A1234567',
                            'dob' => '1990-05-01',
                            'doe' => '2027-01-15',
                            'dop' => '2029-03-20',
                            'nationality' => 'مصر',
                            'field_confidence' => [],
                        ]),
                    ]]],
                ]],
            ], 200),
        ]);

        $tmp = tempnam(sys_get_temp_dir(), 'wai').'.jpg';
        file_put_contents($tmp, 'fake-image-bytes');

        $r = app(WorkerAiExtractor::class)->extract($tmp);

        expect($r['nation_id'])->toBe($nationId);
        expect($r['nationality_name'])->toBe('مصر');

        @unlink($tmp);
    } finally {
        DB::table('nation')->where('nation_id', $nationId)->delete();
    }
});
