<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Spec 005 T-B2 — "اسأل بياناتك" (ask your data) + NL summary for the Reports module.
 *
 * SAFETY MODEL: the LLM never sees raw rows and NEVER generates SQL. All numbers are
 * computed here in PHP from a FIXED WHITELIST of aggregate queries (self::ALLOWED_METRICS).
 * Gemini only receives the already-computed metric totals (as JSON text) and is asked to
 * phrase an Arabic narrative/answer around them — it cannot invent, join, or filter data.
 *
 * Text-only Gemini calls are delegated to GeminiClient::generateText() so retry/backoff
 * logic lives in one place.
 */
class ReportsNlService
{
    /** metric key => Arabic label. The ONLY metrics ever exposed to the model. */
    public const ALLOWED_METRICS = [
        'income_total' => 'إجمالي الدخل (تحصيلات العمال)',
        'expense_total' => 'إجمالي المصروفات التشغيلية',
        'purchase_total' => 'إجمالي المشتريات',
        'net_total' => 'صافي الفرق (الدخل - المصروفات - المشتريات)',
    ];

    /**
     * Compute the fixed whitelist of period aggregates via DB::table (no raw SQL, no
     * user-controlled table/column names). $from/$to are 'Y-m-d' strings or null (unbounded).
     */
    public function periodAggregates(?string $from, ?string $to): array
    {
        $income = (float) DB::table('financial')
            ->where('is_deleted', 0)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('financial_month_val');

        $expense = (float) DB::table('expense')
            ->where('is_deleted', 0)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('expense_price');

        $purchase = (float) DB::table('purchase')
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('purchase_price');

        return $this->buildMetrics($income, $expense, $purchase);
    }

    /** PURE — no DB/network. Rounds inputs and derives net_total. */
    public function buildMetrics(float $income, float $expense, float $purchase): array
    {
        return [
            'income_total' => round($income, 2),
            'expense_total' => round($expense, 2),
            'purchase_total' => round($purchase, 2),
            'net_total' => round($income - $expense - $purchase, 2),
        ];
    }

    /**
     * PURE — the whitelist gate. Drops any key not in ALLOWED_METRICS so an unexpected or
     * attacker-controlled key can never reach the prompt.
     */
    public function filterAllowed(array $metrics): array
    {
        return array_intersect_key($metrics, self::ALLOWED_METRICS);
    }

    /**
     * @param  array  $aggregates  output of periodAggregates()/buildMetrics()
     * @return array{summary:string}
     */
    public function narrate(array $aggregates, ?string $model = null): array
    {
        $safe = $this->filterAllowed($aggregates);
        $text = app(GeminiClient::class)->generateText($this->narratePrompt($safe), $model);

        return ['summary' => trim($text)];
    }

    /**
     * SAFE ask-your-data. The model NEVER generates SQL and NEVER receives raw rows — it
     * only phrases an Arabic answer around the pre-computed whitelist numbers.
     *
     * @return array{answer:string, metrics_used:array}
     */
    public function answer(string $question, array $allowedMetrics, ?string $model = null): array
    {
        $safe = $this->filterAllowed($allowedMetrics);
        $text = app(GeminiClient::class)->generateText($this->askPrompt($question, $safe), $model);

        return ['answer' => trim($text), 'metrics_used' => $safe];
    }

    /** PURE — prompt shape for the NL period summary. */
    public function narratePrompt(array $metrics): string
    {
        // Re-apply filterAllowed() as defense-in-depth so a raw/unfiltered array can
        // never leak a disallowed key into the prompt, regardless of what the caller passed.
        $safe = $this->filterAllowed($metrics);

        return "أنت محلل مالي. لديك الأرقام الإجمالية التالية فقط بصيغة JSON، ولا يوجد لديك أي بيانات أخرى (لا تخترع أرقاماً ولا تفترض بيانات غير موجودة هنا):\n"
            .json_encode($safe, JSON_UNESCAPED_UNICODE)."\n"
            .'اكتب ملخصاً سردياً بالعربية من جملتين إلى ثلاث جمل يوضح هذه الأرقام بأسلوب واضح لصاحب العمل. أجب بنص عادي فقط دون أي تنسيق JSON.';
    }

    /** PURE — prompt shape for ask-your-data. Explicitly forbids the model from writing SQL. */
    public function askPrompt(string $question, array $metrics): string
    {
        // Re-apply filterAllowed() as defense-in-depth (see narratePrompt() above).
        $safe = $this->filterAllowed($metrics);

        return "أنت مساعد تقارير. لا تكتب أي استعلام SQL ولا تخترع أرقاماً؛ استخدم فقط الأرقام التالية بصيغة JSON للإجابة على سؤال المستخدم:\n"
            .json_encode($safe, JSON_UNESCAPED_UNICODE)."\n"
            .'سؤال المستخدم: '.$question."\n"
            .'إن كان السؤال يطلب رقماً غير موجود في القائمة أعلاه فوضّح أن هذا الرقم غير متاح حالياً بدلاً من تخمينه. أجب بالعربية نصاً عادياً فقط دون JSON.';
    }
}
