<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Spec 005 — admin screen to view/edit API keys & integration settings
 * (Gemini / SMS / ZATCA), plus arbitrary custom keys. Super-admin only
 * (emp_job == 1) since these are secrets. Secrets are never rendered back to
 * the page; a masked placeholder shows whether a value is set, and a blank
 * submit leaves the stored secret unchanged.
 */
class SettingsController extends Controller
{
    /** Grouped registry of known settings. */
    public static function registry(): array
    {
        return [
            'الذكاء الاصطناعي (Gemini)' => [
                ['key' => 'gemini_api_key', 'label' => 'مفتاح Gemini API', 'secret' => true],
                ['key' => 'gemini_model', 'label' => 'موديل Gemini', 'secret' => false, 'placeholder' => 'gemini-3.5-flash'],
                ['key' => 'gemini_thinking', 'label' => 'مستوى التفكير', 'secret' => false, 'placeholder' => 'minimal | low | medium | high'],
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

    private function guard(): void
    {
        if ((int) (Auth::user()->emp_job ?? 0) !== 1) {
            abort(403, 'هذه الصفحة مخصّصة لمدير النظام فقط');
        }
    }

    public function index()
    {
        $this->guard();

        $registry = self::registry();
        $values = Settings::all();

        // Custom (non-registry) keys the admin added earlier.
        $known = [];
        foreach ($registry as $items) {
            foreach ($items as $it) {
                $known[$it['key']] = true;
            }
        }
        $custom = [];
        foreach ($values as $k => $v) {
            if (! isset($known[$k])) {
                $custom[$k] = $v;
            }
        }

        return view('dashboard.settings.index', compact('registry', 'values', 'custom'));
    }

    public function update(Request $request)
    {
        $this->guard();

        $changed = [];
        foreach (self::registry() as $items) {
            foreach ($items as $it) {
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
        $ckeys = (array) $request->input('custom_key', []);
        $cvals = (array) $request->input('custom_value', []);
        foreach ($ckeys as $i => $ck) {
            $ck = trim((string) $ck);
            if ($ck === '') {
                continue;
            }
            $ck = preg_replace('/[^a-zA-Z0-9_\.]/', '_', $ck);
            Settings::set($ck, trim((string) ($cvals[$i] ?? '')));
            $changed[] = $ck;
        }

        Settings::forgetCache();
        AuditLogger::log('settings', null, AuditLogger::EDIT, [
            'note' => 'تحديث إعدادات مفاتيح الـ API',
            'keys' => implode(', ', $changed),
        ]);

        return redirect()->route('dashboard.settings.index')->with('success', 'تم حفظ الإعدادات بنجاح ✓');
    }
}
