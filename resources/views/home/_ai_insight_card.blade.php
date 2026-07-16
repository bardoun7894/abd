{{-- Spec 005 T-B3 — Home AI insight card. $insight is
     array{summary:?string, deltas:array, fallback:bool} from HomeInsightService::insight().
     Gemini is never called on every page load (cached per month) and this card must never
     break the home page — on fallback it renders the raw numbers instead of the narrative. --}}
@php
    $insight = (isset($insight) && is_array($insight)) ? $insight : ['summary' => null, 'deltas' => [], 'fallback' => true];
    $deltas = $insight['deltas'] ?? [];
    $labels = ['income' => 'الدخل', 'expense' => 'المصروفات', 'purchase' => 'المشتريات'];
@endphp
@once
<style>
.ai-insight-card{position:relative;border:0;border-radius:1rem;overflow:hidden;box-shadow:0 .5rem 1.5rem rgba(0,158,247,.15);}
.ai-insight-card__header{position:relative;padding:1.5rem 1.75rem;background:linear-gradient(135deg,#009ef7 0%,#7239ea 100%);color:#fff;overflow:hidden;}
.ai-insight-card__header::after{content:"";position:absolute;inset-inline-end:-2rem;top:-2rem;width:8rem;height:8rem;border-radius:50%;background:rgba(255,255,255,.12);}
.ai-insight-card__header-row{position:relative;display:flex;align-items:center;gap:.85rem;}
.ai-icon-badge--light{display:inline-flex;align-items:center;justify-content:center;width:2.75rem;height:2.75rem;border-radius:.75rem;background:rgba(255,255,255,.18);color:#fff;font-size:1.2rem;flex:0 0 auto;backdrop-filter:blur(2px);}
.ai-pill--light{display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;padding:.3rem .55rem;border-radius:50rem;background:rgba(255,255,255,.22);color:#fff;letter-spacing:.02em;margin-bottom:.3rem;}
.ai-insight-card__month{font-size:1.05rem;font-weight:700;}
.ai-insight-card__summary{font-size:1rem;font-weight:600;line-height:1.9;color:#181c32;}
.ai-insight-card__row{display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:1px dashed #e4e6ef;}
.ai-insight-card__row:last-child{border-bottom:0;}
.ai-insight-card__empty{display:flex;align-items:center;gap:.6rem;font-size:.85rem;color:#7e8299;background:#f8f9fc;border-radius:.6rem;padding:.85rem 1rem;margin-top:.5rem;}
</style>
@endonce
<div class="col-xl-4">
    <div class="card ai-insight-card mb-5 mb-xl-8">
        <div class="ai-insight-card__header">
            <div class="ai-insight-card__header-row">
                <span class="ai-icon-badge--light"><i class="fa fa-robot"></i></span>
                <div>
                    <span class="ai-pill--light d-inline-flex"><i class="fa fa-magic"></i> ذكاء اصطناعي</span>
                    <div class="ai-insight-card__month">ملخص الشهر الحالي</div>
                </div>
            </div>
        </div>
        <div class="card-body pt-5">
            @if (!empty($insight['summary']))
                <div class="ai-insight-card__summary mb-4">{{ $insight['summary'] }}</div>
                <div class="separator separator-dashed border-2 mb-4"></div>
            @endif

            @foreach ($deltas as $key => $row)
                <div class="ai-insight-card__row fs-6">
                    <div class="fw-bold">{{ $labels[$key] ?? $key }}</div>
                    <div class="d-flex fw-bolder">
                        {{ number_format($row['current'] ?? 0, 2) }}
                        <span class="ms-2 {{ ($row['delta_pct'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            ({{ ($row['delta_pct'] ?? 0) >= 0 ? '+' : '' }}{{ $row['delta_pct'] ?? 0 }}%)
                        </span>
                    </div>
                </div>
            @endforeach

            @if (empty($insight['summary']))
                <div class="ai-insight-card__empty">
                    <i class="fa fa-info-circle"></i>
                    {{ !empty($deltas) ? 'تعذّر توليد ملاحظة تحليلية الآن — الأرقام أعلاه محسوبة مباشرة.' : 'لا توجد بيانات كافية بعد.' }}
                </div>
            @endif
        </div>
    </div>
</div>
