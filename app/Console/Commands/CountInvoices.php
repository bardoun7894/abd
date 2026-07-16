<?php

namespace App\Console\Commands;

use App\Services\InvoiceExtractionService;
use Illuminate\Console\Command;

/**
 * PHASE 1 only — detect & count the invoices inside a PDF (no field extraction).
 *
 *   php artisan invoices:count storage/saqrian_33.pdf
 */
class CountInvoices extends Command
{
    protected $signature = 'invoices:count {pdf} {--ai : Use the AI counter (costs money) instead of the free page/text counter} {--model=}';

    protected $description = 'Detect how many invoices are inside a PDF (free page/text counter by default; --ai for AI segmentation)';

    public function handle(InvoiceExtractionService $service): int
    {
        $path = $this->argument('pdf');
        if (! is_file($path)) {
            $this->error("PDF not found: {$path}");

            return self::FAILURE;
        }

        if ($this->option('ai')) {
            $model = $this->option('model') ?: config('services.gemini.default_model');
            $this->info("AI counting (costs money) in: {$path}  (model: {$model})");
            $result = $service->countInvoices($path, $model);
            $rows = [];
            foreach ($result['segments'] as $i => $seg) {
                $pages = $seg['start_page'] === $seg['end_page']
                    ? (string) $seg['start_page']
                    : $seg['start_page'].'–'.$seg['end_page'];
                $rows[] = [$i + 1, $seg['invoice_number'] ?? '(?)', $pages];
            }
            $this->table(['#', 'رقم الفاتورة', 'الصفحات'], $rows);
            $this->info('Detected invoices (AI): '.$result['count']);

            return self::SUCCESS;
        }

        $this->info("FREE counting (no AI) in: {$path}");
        $r = $service->countInvoicesFree($path);
        $this->line('  pages    : '.$r['pages']);
        $this->line('  has text : '.($r['has_text'] ? 'yes (digital PDF)' : 'no (image scan)'));
        $this->line('  method   : '.$r['method']);
        $this->info('Detected invoices (free): '.$r['count']);

        return self::SUCCESS;
    }
}
