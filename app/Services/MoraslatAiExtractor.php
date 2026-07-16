<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Spec 004 C1 — AI for the MORASLAT (correspondence) module. Two capabilities:
 *
 *  (a) analyze(): OCR a scanned letter via the shared GeminiClient::extract(), returning
 *      a short Arabic summary + suggestions for the real moraslat_type / moraslat_categoty /
 *      moraslat_status rows. The caller passes in the live taxonomy (id => name) so this
 *      class never hardcodes ids; suggestions are matched by name similarity, reusing
 *      SupplierMatcher (Spec 002 FR-105) exactly like ExpenseAiExtractor::suggestCategory.
 *      Nothing is saved here — the user confirms in the real moraslat form.
 *
 *  (b) draftReply(): given the letter's summary/subject, drafts a formal Arabic reply.
 *      This call is TEXT-ONLY (no file), so it cannot reuse GeminiClient::extract(), which
 *      requires an inline file part. GeminiClient is shared and frozen (do not modify), so
 *      this class makes its own minimal text-only Gemini HTTP call here, mirroring
 *      GeminiClient's retry/backoff/error handling.
 */
class MoraslatAiExtractor
{
    /** usageMetadata (token counts) from the most recent draftReply() call. */
    public array $lastDraftUsage = [];

    public function __construct(private GeminiClient $gemini) {}

    /**
     * @param  iterable  $types  rows with moraslat_type_id / moraslat_type_name
     * @param  iterable  $categories  rows with moraslat_categoty_id / moraslat_categoty_name
     * @param  iterable  $statuses  rows with moraslat_status_id / moraslat_status_name
     * @return array{summary:?string, suggested_type_id:?int, suggested_type_name:?string,
     *               suggested_category_id:?int, suggested_category_name:?string,
     *               suggested_status_id:?int, suggested_status_name:?string,
     *               extracted_subject:?string, sender:?string, date:?string, key_points:array,
     *               suggested_type_score:float, suggested_category_score:float,
     *               suggested_status_score:float, _in:int, _out:int}
     */
    public function analyze(string $filePath, iterable $types, iterable $categories, iterable $statuses, ?string $model = null): array
    {
        $raw = $this->gemini->extract($this->analyzePrompt($types, $categories, $statuses), $filePath, $this->analyzeSchema(), $model);

        [$typeId, $typeName, $typeScore] = $this->suggestFromList($raw['type_hint'] ?? null, $types, 'moraslat_type_id', 'moraslat_type_name');
        [$catId, $catName, $catScore] = $this->suggestFromList($raw['category_hint'] ?? null, $categories, 'moraslat_categoty_id', 'moraslat_categoty_name');
        [$statusId, $statusName, $statusScore] = $this->suggestFromList($raw['status_hint'] ?? null, $statuses, 'moraslat_status_id', 'moraslat_status_name');

        return [
            'summary' => $this->cleanStr($raw['summary'] ?? null),
            'suggested_type_id' => $typeId,
            'suggested_type_name' => $typeName,
            'suggested_type_score' => $typeScore,
            'suggested_category_id' => $catId,
            'suggested_category_name' => $catName,
            'suggested_category_score' => $catScore,
            'suggested_status_id' => $statusId,
            'suggested_status_name' => $statusName,
            'suggested_status_score' => $statusScore,
            'extracted_subject' => $this->cleanStr($raw['subject'] ?? null),
            'sender' => $this->cleanStr($raw['sender'] ?? null),
            'date' => $this->cleanStr($raw['date'] ?? null),
            'key_points' => $this->cleanList($raw['key_points'] ?? null),
            '_in' => $this->gemini->lastInputTokens(),
            '_out' => $this->gemini->lastOutputTokens(),
        ];
    }

    /**
     * Draft a formal Arabic reply from a text context (letter summary/subject). Text-only —
     * no file involved.
     *
     * @return array{draft:string}
     */
    public function draftReply(string $context, ?string $model = null): array
    {
        $text = $this->callGeminiText($this->draftPrompt($context), $model);

        return [
            'draft' => trim($text),
        ];
    }

    /**
     * Match a free-text hint (from the model) to the closest real taxonomy row by name
     * similarity. Pure w.r.t. its inputs — reuses SupplierMatcher::nameSimilarity.
     *
     * @return array{0:?int,1:?string,2:float} [id, name, score]
     */
    public function suggestFromList(?string $hint, iterable $rows, string $idKey, string $nameKey): array
    {
        $hint = trim((string) $hint);
        if ($hint === '') {
            return [null, null, 0.0];
        }

        $best = null;
        $bestScore = 0.0;
        foreach ($rows as $row) {
            $name = is_array($row) ? ($row[$nameKey] ?? '') : ($row->{$nameKey} ?? '');
            $score = SupplierMatcher::nameSimilarity($hint, (string) $name);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }

        if ($best && $bestScore >= 0.55) {
            $id = is_array($best) ? $best[$idKey] : $best->{$idKey};
            $name = is_array($best) ? $best[$nameKey] : $best->{$nameKey};

            return [(int) $id, $name, $bestScore];
        }

        return [null, null, 0.0];
    }

    private function analyzeSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'summary' => ['type' => 'STRING', 'nullable' => true],
                'subject' => ['type' => 'STRING', 'nullable' => true],
                'sender' => ['type' => 'STRING', 'nullable' => true],
                'date' => ['type' => 'STRING', 'nullable' => true],
                'key_points' => [
                    'type' => 'ARRAY',
                    'nullable' => true,
                    'items' => ['type' => 'STRING'],
                ],
                'type_hint' => ['type' => 'STRING', 'nullable' => true],
                'category_hint' => ['type' => 'STRING', 'nullable' => true],
                'status_hint' => ['type' => 'STRING', 'nullable' => true],
            ],
            'required' => ['summary'],
        ];
    }

    private function analyzePrompt(iterable $types, iterable $categories, iterable $statuses): string
    {
        $typeNames = $this->namesOf($types, 'moraslat_type_name');
        $categoryNames = $this->namesOf($categories, 'moraslat_categoty_name');
        $statusNames = $this->namesOf($statuses, 'moraslat_status_name');

        return "أنت محرّك أرشفة وتلخيص للمراسلات الإدارية (خطابات رسمية عربية/إنجليزية ممسوحة ضوئياً). اقرأ الخطاب وأعد JSON فقط:\n"
            .'- summary: ملخص للخطاب من 2 إلى 3 جمل بالعربية.'."\n"
            .'- subject: موضوع الخطاب.'."\n"
            .'- sender: الجهة أو الشخص المرسل.'."\n"
            .'- date: تاريخ الخطاب بصيغة YYYY-MM-DD إن وجد.'."\n"
            .'- key_points: أهم النقاط في الخطاب كقائمة نصوص قصيرة.'."\n"
            .'- type_hint: أقرب نوع مراسلة من هذه القائمة فقط: ['.implode('، ', $typeNames).'].'."\n"
            .'- category_hint: أقرب درجة أهمية من هذه القائمة فقط: ['.implode('، ', $categoryNames).'].'."\n"
            .'- status_hint: أقرب حالة من هذه القائمة فقط: ['.implode('، ', $statusNames).'] إن أمكن تحديدها من محتوى الخطاب، وإلا اتركها فارغة.'."\n"
            .'حوّل الأرقام العربية إلى لاتينية. لا تخمّن؛ استخدم null لأي حقل غير موجود. أعد JSON فقط.';
    }

    private function draftPrompt(string $context): string
    {
        return "أنت مساعد صياغة مراسلات إدارية رسمية بالعربية الفصحى. بناءً على السياق التالي، اكتب مسودة رد رسمي ومهذب ومختصر، يبدأ بالتحية المناسبة وينتهي بعبارة ختامية رسمية. أعد نص الرد فقط دون أي تعليق إضافي أو تنسيق JSON.\n\n"
            .'السياق: '.$context;
    }

    private function namesOf(iterable $rows, string $nameKey): array
    {
        $names = [];
        foreach ($rows as $row) {
            $name = is_array($row) ? ($row[$nameKey] ?? null) : ($row->{$nameKey} ?? null);
            if ($name !== null && $name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * Minimal text-only Gemini call (no inline file), mirroring GeminiClient::extract()'s
     * retry/backoff/error handling. GeminiClient itself is file-input only, so it can't be
     * reused as-is for a plain-text draft; this keeps the shared client untouched.
     */
    private function callGeminiText(string $prompt, ?string $model = null): string
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }
        $model = $model ?: config('services.gemini.default_model');
        $base = rtrim(config('services.gemini.base_url'), '/');

        $body = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0.3,
                'responseMimeType' => 'text/plain',
            ],
        ];

        $url = "{$base}/models/{$model}:generateContent?key={$key}";
        $maxAttempts = (int) config('services.gemini.retries', 4);
        $attempt = 0;
        while (true) {
            $attempt++;
            $resp = Http::timeout((int) config('services.gemini.timeout', 120))->acceptJson()->post($url, $body);
            if ($resp->successful()) {
                break;
            }
            $status = $resp->status();
            if (in_array($status, [429, 500, 502, 503, 504], true) && $attempt < $maxAttempts) {
                usleep((int) ((2 ** $attempt) * 500_000));

                continue;
            }
            throw new RuntimeException('Gemini HTTP '.$status.': '.$resp->body(), $status);
        }

        $this->lastDraftUsage = (array) data_get($resp->json(), 'usageMetadata', []);
        $text = data_get($resp->json(), 'candidates.0.content.parts.0.text');
        if ($text === null) {
            throw new RuntimeException('Gemini returned no content: '.$resp->body());
        }

        return $text;
    }

    // ---- small pure normalizers ----
    private function cleanStr($v): ?string
    {
        $v = trim((string) ($v ?? ''));

        return $v === '' ? null : $v;
    }

    private function cleanList($v): array
    {
        if (! is_array($v)) {
            return [];
        }

        return array_values(array_filter(array_map(fn ($x) => $this->cleanStr($x), $v)));
    }
}
