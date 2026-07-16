{{-- Spec 005 T-B3 — Home AI insight card. $insight is
     array{summary:?string, deltas:array, fallback:bool} from HomeInsightService::insight().
     Gemini is never called on every page load (cached per month) and this card must never
     break the home page — on fallback it renders the raw numbers instead of the narrative. --}}
@php
    $insight = (isset($insight) && is_array($insight)) ? $insight : ['summary' => null, 'deltas' => [], 'fallback' => true];
    $deltas = $insight['deltas'] ?? [];
    $labels = ['income' => 'الدخل', 'expense' => 'المصروفات', 'purchase' => 'المشتريات'];
@endphp
<div class="col-xl-4">
    <div class="card card-xl-stretch mb-5 mb-xl-8 bg-light-primary border border-primary border-dashed">
        <div class="card-body pt-5">
            <div class="fs-4 fw-bold text-primary mb-4"><i class="fa fa-robot me-1"></i> ملخص الذكاء الاصطناعي — هذا الشهر</div>

            @if (!empty($insight['summary']))
                <div class="fs-6 fw-bold mb-4">{{ $insight['summary'] }}</div>
                <div class="separator separator-dashed border-2 mb-4"></div>
            @endif

            @foreach ($deltas as $key => $row)
                <div class="fs-6 d-flex justify-content-between mb-3">
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
                <div class="fs-8 text-muted mt-2">
                    {{ !empty($deltas) ? 'تعذّر توليد ملاحظة تحليلية الآن — الأرقام أعلاه محسوبة مباشرة.' : 'لا توجد بيانات كافية بعد.' }}
                </div>
            @endif
        </div>
    </div>
</div>
