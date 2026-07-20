<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AiSubscription;
use App\Services\AuditLogger;
use App\Services\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Perm;

/**
 * Spec 005 — admin screen to view/edit API keys & integration settings
 * (Gemini / SMS / ZATCA), plus arbitrary custom keys. Secrets are never
 * rendered back to the page; a masked placeholder shows whether a value is
 * set, and a blank submit leaves the stored secret unchanged.
 *
 * Spec 008 bundle 2 (ai-permissions) — full admin (emp_job==1) still sees/edits
 * every group. A delegated user holding function 213 (or master 210) may also
 * reach this page, but ONLY for the Gemini group: index() filters the registry
 * to that group before rendering, and update() enforces a server-side
 * gemini_*-only allowlist for non-admins — the UI filter alone is not
 * sufficient, since a crafted POST could otherwise still overwrite SMS/ZATCA
 * secrets.
 */
class SettingsController extends Controller
{
    private const GEMINI_GROUP = 'الذكاء الاصطناعي (Gemini)';

    /** Grouped registry of known settings. */
    public static function registry(): array
    {
        return [
            'الذكاء الاصطناعي (Gemini)' => [
                ['key' => 'gemini_api_key', 'label' => 'مفتاح Gemini API', 'secret' => true],
                ['key' => 'gemini_model', 'label' => 'موديل Gemini', 'secret' => false, 'placeholder' => 'gemini-flash-lite-latest'],
                ['key' => 'gemini_rescan_model', 'label' => 'موديل إعادة الفحص', 'secret' => false, 'placeholder' => 'gemini-3-flash-preview'],
                ['key' => 'gemini_thinking', 'label' => 'مستوى التفكير', 'secret' => false, 'placeholder' => 'minimal | low | medium | high'],
                ['key' => 'gemini_thinking_hard', 'label' => 'التفكير العميق', 'secret' => false, 'placeholder' => 'low | medium | high'],
                ['key' => 'gemini_timeout', 'label' => 'مهلة استدعاءات النص فقط (ثانية)', 'secret' => false, 'placeholder' => '120'],
                ['key' => 'gemini_page_timeout', 'label' => 'مهلة استخراج الملفات لكل صفحة (ثانية)', 'secret' => false, 'placeholder' => '120'],
                ['key' => 'gemini_retries', 'label' => 'عدد محاولات إعادة الاتصال', 'secret' => false, 'placeholder' => '4'],
            ],
            'الرسائل النصية (SMS)' => [
                ['key' => 'sms_provider', 'label' => 'مزوّد الرسائل', 'secret' => false, 'placeholder' => 'taqnyat'],
                ['key' => 'sms_api_key', 'label' => 'مفتاح SMS API', 'secret' => true],
                ['key' => 'sms_sender', 'label' => 'اسم المُرسِل', 'secret' => false],
                ['key' => 'sms_base_url', 'label' => 'رابط API (اختياري)', 'secret' => false],
            ],
            'الفوترة الإلكترونية (ZATCA)' => [
                ['key' => 'zatca_seller_name', 'label' => 'اسم البائع', 'secret' => false, 'placeholder' => 'شركة صباح النور'],
                ['key' => 'zatca_vat_number', 'label' => 'الرقم الضريبي (VAT)', 'secret' => false, 'placeholder' => '15 رقم'],
            ],
        ];
    }

    /** Full admin, or a delegated user holding the AI-settings function (213/master 210). */
    private function guard(): void
    {
        if ((int) (Auth::user()->emp_job ?? 0) === 1) {
            return;
        }
        if (Perm::ai_access(Perm::AI_SETTINGS)) {
            return;
        }
        abort(403, 'هذه الصفحة مخصّصة لمدير النظام أو من لديه صلاحية إعدادات الذكاء الاصطناعي');
    }

    /** True for a non-admin delegate — scopes them to the Gemini group only, everywhere. */
    private function isGeminiOnlyDelegate(): bool
    {
        return (int) (Auth::user()->emp_job ?? 0) !== 1;
    }

    public function index()
    {
        $this->guard();

        $registry = self::registry();
        if ($this->isGeminiOnlyDelegate()) {
            // Load-bearing: a 213-only user must never see SMS/ZATCA secret fields,
            // not even masked placeholders — filter before the view ever renders.
            $registry = array_intersect_key($registry, [self::GEMINI_GROUP => true]);
        }
        $values = Settings::all();

        // Custom (non-registry) keys the admin added earlier. Computed from the FULL
        // (unfiltered) registry so SMS/ZATCA keys are never misclassified as "custom"
        // and leaked to a Gemini-only delegate through the back door.
        $known = [];
        foreach (self::registry() as $items) {
            foreach ($items as $it) {
                $known[$it['key']] = true;
            }
        }
        $custom = [];
        if (! $this->isGeminiOnlyDelegate()) {
            // Custom keys are arbitrary admin-added values of unknown sensitivity —
            // never shown to a Gemini-only delegate at all.
            foreach ($values as $k => $v) {
                if (! isset($known[$k])) {
                    $custom[$k] = $v;
                }
            }
        }

        // Spec 007 — AI subscription status/config on the settings screen.
        $subscription = AiSubscription::current();

        return view('dashboard.settings.index', compact('registry', 'values', 'custom', 'subscription'));
    }

    public function update(Request $request)
    {
        $this->guard();

        // Spec 008 bundle 2 (ai-permissions) — load-bearing server-side allowlist.
        // UI filtering in index() alone is NOT enough: a Gemini-only delegate could
        // still POST a crafted body with sms_api_key/zatca_* fields directly. Reject
        // any registry key outside the Gemini group for a non-admin, regardless of
        // what the form actually rendered.
        $delegate = $this->isGeminiOnlyDelegate();
        $geminiKeys = [];
        foreach (self::registry()[self::GEMINI_GROUP] ?? [] as $it) {
            $geminiKeys[$it['key']] = true;
        }

        $changed = [];
        foreach (self::registry() as $group => $items) {
            if ($delegate && $group !== self::GEMINI_GROUP) {
                continue; // non-gemini group entirely off-limits to a delegate
            }
            foreach ($items as $it) {
                if ($delegate && ! isset($geminiKeys[$it['key']])) {
                    continue; // defense in depth, matches the group skip above
                }
                $field = 'setting_'.$it['key'];
                if (! $request->has($field)) {
                    continue;
                }
                $val = trim((string) $request->input($field));
                // For secrets: blank submit means "keep current value".
                if (! empty($it['secret']) && $val === '') {
                    continue;
                }
                Settings::set($it['key'], $val === '' ? null : $val);
                $changed[] = $it['key'];
            }
        }

        // Custom key/value rows (repeatable): custom_key[] + custom_value[].
        // Values render as empty password inputs — blank means "keep the stored
        // value" (same contract as registry secrets above), never wipe to ''.
        // A Gemini-only delegate cannot touch custom keys at all — their sensitivity
        // is unknown and index() never shows them to a delegate in the first place.
        $ckeys = $delegate ? [] : (array) $request->input('custom_key', []);
        $cvals = (array) $request->input('custom_value', []);
        foreach ($ckeys as $i => $ck) {
            $ck = trim((string) $ck);
            if ($ck === '') {
                continue;
            }
            $ck = preg_replace('/[^a-zA-Z0-9_\.]/', '_', $ck);
            $cv = trim((string) ($cvals[$i] ?? ''));
            if ($cv === '') {
                continue; // leave stored value untouched
            }
            Settings::set($ck, $cv);
            $changed[] = $ck;
        }

        Settings::forgetCache();
        AuditLogger::log('settings', null, AuditLogger::EDIT, [
            'note' => 'تحديث إعدادات مفاتيح الـ API',
            'keys' => implode(', ', $changed),
        ]);

        return redirect()->route('dashboard.settings.index')->with('success', 'تم حفظ الإعدادات بنجاح ✓');
    }

    /**
     * Spec 007 — edit the AI subscription's active flag, expiry date, and
     * page quota (does NOT touch used_pages or renewed_at — that's renew()).
     */
    public function updateSubscription(Request $request)
    {
        $this->guard();

        $sub = AiSubscription::current();
        $sub->active = $request->boolean('sub_active');
        $sub->expires_at = $request->filled('sub_expires_at') ? $request->input('sub_expires_at') : null;
        $sub->quota_pages = $request->filled('sub_quota_pages') ? max(0, (int) $request->input('sub_quota_pages')) : null;
        $sub->save();

        AuditLogger::log('ai_subscription', $sub->id, AuditLogger::EDIT, [
            'note' => 'تحديث إعدادات اشتراك الذكاء الاصطناعي',
        ]);

        return redirect()->route('dashboard.settings.index')->with('success', 'تم حفظ إعدادات الاشتراك ✓');
    }

    /**
     * Spec 007 — "تجديد الاشتراك": reactivates, sets a new expires_at,
     * resets used_pages=0, and stamps renewed_at.
     */
    public function renewSubscription(Request $request)
    {
        $this->guard();

        $request->validate([
            'renew_expires_at' => 'nullable|date',
        ]);

        $sub = AiSubscription::current();
        $sub->active = true;
        $sub->expires_at = $request->filled('renew_expires_at')
            ? $request->input('renew_expires_at')
            : now()->addYear()->toDateString();
        $sub->used_pages = 0;
        $sub->renewed_at = now();
        $sub->save();

        AuditLogger::log('ai_subscription', $sub->id, AuditLogger::EDIT, [
            'note' => 'تم تجديد اشتراك الذكاء الاصطناعي حتى '.$sub->expires_at->toDateString(),
        ]);

        return redirect()->route('dashboard.settings.index')->with('success', 'تم تجديد الاشتراك بنجاح ✓');
    }

    /**
     * AI usage & cost dashboard (Phase 4): reads the ai_usage_log ledger + ai_extractions
     * cache to show spend, tokens, and cache-hit rate per module and per day. Fail-open —
     * if the tables don't exist yet (migration not run) it shows an empty state.
     */
    public function aiUsage(Request $request)
    {
        $this->guard();

        $page_title = 'استهلاك وتكلفة الذكاء الاصطناعي';
        $days = (int) $request->query('days', 30);
        $days = $days > 0 ? min($days, 365) : 30;
        $since = now()->subDays($days);

        $empty = [
            'total_calls' => 0, 'hits' => 0, 'misses' => 0, 'hit_rate' => 0.0,
            'input_tokens' => 0, 'output_tokens' => 0, 'cost_usd' => 0.0, 'cost_sar' => 0.0,
            'cache_rows' => 0,
        ];
        $byModule = collect();
        $byDay = collect();
        $stats = $empty;

        try {
            $base = DB::table('ai_usage_log')->where('created_at', '>=', $since);

            $total = (clone $base)->count();
            $hits = (clone $base)->where('cache_hit', true)->count();
            $inTok = (int) (clone $base)->sum('input_tokens');
            $outTok = (int) (clone $base)->sum('output_tokens');
            $cost = (float) (clone $base)->where('cache_hit', false)->sum('est_cost_usd');
            $sar = (float) config('services.gemini.usd_to_sar', 3.75);

            $stats = [
                'total_calls' => $total,
                'hits' => $hits,
                'misses' => $total - $hits,
                'hit_rate' => $total > 0 ? round($hits / $total * 100, 1) : 0.0,
                'input_tokens' => $inTok,
                'output_tokens' => $outTok,
                'cost_usd' => round($cost, 4),
                'cost_sar' => round($cost * $sar, 3),
                'cache_rows' => (int) DB::table('ai_extractions')->count(),
            ];

            $byModule = DB::table('ai_usage_log')
                ->where('created_at', '>=', $since)
                ->select('module',
                    DB::raw('COUNT(*) as calls'),
                    DB::raw('SUM(CASE WHEN cache_hit = 1 THEN 1 ELSE 0 END) as hits'),
                    DB::raw('SUM(input_tokens) as in_tok'),
                    DB::raw('SUM(output_tokens) as out_tok'),
                    DB::raw('SUM(CASE WHEN cache_hit = 0 THEN est_cost_usd ELSE 0 END) as cost'))
                ->groupBy('module')->orderByDesc('cost')->get();

            $byDay = DB::table('ai_usage_log')
                ->where('created_at', '>=', $since)
                ->select(DB::raw('DATE(created_at) as d'),
                    DB::raw('COUNT(*) as calls'),
                    DB::raw('SUM(CASE WHEN cache_hit = 1 THEN 1 ELSE 0 END) as hits'),
                    DB::raw('SUM(CASE WHEN cache_hit = 0 THEN est_cost_usd ELSE 0 END) as cost'))
                ->groupBy(DB::raw('DATE(created_at)'))->orderByDesc('d')->limit(31)->get();
        } catch (\Throwable $e) {
            // tables not migrated yet → empty state
        }

        return view('dashboard.settings.ai_usage', compact('page_title', 'stats', 'byModule', 'byDay', 'days'));
    }
}
