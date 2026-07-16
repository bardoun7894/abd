<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\InvoiceExtractionService;
use App\Services\InvoicePipeline;
use Illuminate\Console\Command;

/**
 * Local trial harness — runs the shared extraction pipeline on a PDF without the
 * web UI / auth / main DB. Prints the table + grand total.
 *
 * Default pipeline (cheapest + accurate): free page count → split → ONE AI read
 * per page → group by invoice number → grand total.
 *
 *   php artisan invoices:extract storage/saqrian_33.pdf
 *   php artisan invoices:extract file.pdf --model=gemini-2.5-flash-lite
 *   php artisan invoices:extract file.pdf --whole   # one AI call for the whole PDF
 *   php artisan invoices:extract file.pdf --split   # one row per page, no grouping
 */
class ExtractInvoices extends Command
{
    protected $signature = 'invoices:extract
                            {pdf : Path to the invoice PDF (1 or many invoices)}
                            {--model= : Override the Gemini model}
                            {--per-page : Per-page mode + group by invoice number (needs clean per-page render)}
                            {--split : Per-page mode, one row per page, no grouping}
                            {--whole : Whole-document mode (one AI call for the whole PDF)}';

    protected $description = 'Extract invoices from a PDF into the isolated invoices DB (local trial)';

    public function handle(InvoicePipeline $pipeline, InvoiceExtractionService $service): int
    {
        $path = $this->argument('pdf');
        if (! is_file($path)) {
            $this->error("PDF not found: {$path}");

            return self::FAILURE;
        }

        $model = $this->option('model') ?: config('services.gemini.default_model');
        $mode = $this->option('whole') ? 'whole'
            : ($this->option('split') ? 'split'
            : ($this->option('per-page') ? 'grouped'
            : config('services.gemini.default_mode', 'split')));
        $this->info("Model: {$model}  |  mode: {$mode}");

        $free = $service->countInvoicesFree($path);
        $this->info("Free count (no AI): ~{$free['count']} — {$free['method']}");

        $batch = InvoiceBatch::create([
            'original_filename' => basename($path),
            'pdf_path' => $path,
            'status' => 'processing',
            'model_used' => $model,
        ]);

        $bar = null;
        $made = $pipeline->run($batch, $path, $model, function ($done, $total) use (&$bar) {
            if (! $bar) {
                $bar = $this->output->createProgressBar($total);
            }
            $bar->setProgress($done);
        }, $mode);
        if ($bar) {
            $bar->finish();
            $this->newLine();
        }

        $this->renderTable($batch);
        $this->newLine();
        $fresh = $batch->fresh();
        $sar = round((float) $fresh->est_cost_usd * (float) config('services.gemini.usd_to_sar', 3.75), 4);
        $this->info('Batch #'.$batch->id.' — invoices: '.$made.'  |  الإجمالي العام (grand total): '.number_format((float) $fresh->grand_total, 2));
        $this->line('Tokens: '.$fresh->input_tokens.' in / '.$fresh->output_tokens.' out  |  cost ≈ $'.number_format((float) $fresh->est_cost_usd, 5).'  ('.number_format($sar, 4).' SAR)');
        $flagged = $batch->invoices()->where('needs_review', true)->count();
        if ($flagged) {
            $this->warn($flagged.' invoice(s) flagged needs_review — check the data.');
        }

        return self::SUCCESS;
    }

    private function renderTable(InvoiceBatch $batch): void
    {
        $rows = $batch->invoices()->orderBy('page_number')->get()->map(fn (Invoice $i) => [
            $i->page_number,
            mb_substr((string) $i->supplier_name, 0, 22),
            $i->supplier_tax_number,
            $i->invoice_number,
            $i->invoice_date?->format('Y-m-d'),
            $i->amount_before_vat,
            $i->vat_amount,
            $i->total_incl_vat,
            $i->needs_review ? '⚠' : '✓',
        ])->toArray();

        $this->table(
            ['#', 'المورد', 'الرقم الضريبي', 'رقم الفاتورة', 'التاريخ', 'قبل الضريبة', 'الضريبة', 'الإجمالي', 'حالة'],
            $rows
        );
    }
}
