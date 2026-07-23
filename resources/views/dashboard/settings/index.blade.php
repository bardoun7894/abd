@extends('layouts.app')
@section('module', 'إعدادات النظام')
@section('sub', 'مفاتيح الـ API')
@section('title', 'إعدادات مفاتيح الـ API')

@section('content')
<div class="ai-page d-flex flex-column flex-column-fluid">
    <div class="post d-flex flex-column-fluid">
        <div class="container-xxl">

            @if (session('success'))
                <div class="alert alert-success d-flex align-items-center">
                    <i class="fa fa-check-circle text-success me-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Spec 007 — AI subscription status + config --}}
            @php
                $subBlocked = $subscription->isBlocked();
                $subRemainingPages = $subscription->remainingPages();
                $subRemainingDays = $subscription->remainingDays();
            @endphp
            <div class="card mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa fa-robot text-primary me-2"></i>
                        <h2 class="fw-bold">اشتراك الذكاء الاصطناعي</h2>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('dashboard.settings.ai_usage') }}" class="btn btn-sm btn-light-primary fw-bold">
                            <i class="fa fa-chart-line me-1"></i> الاستهلاك والتكلفة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert {{ $subBlocked ? 'alert-danger' : 'alert-light-success' }} d-flex align-items-center mb-6">
                        <i class="fa {{ $subBlocked ? 'fa-ban' : 'fa-check-circle' }} me-2"></i>
                        <div>
                            <div class="fw-bold">
                                {{ $subBlocked ? 'الاشتراك متوقف — كل عمليات الاستخراج بالذكاء الاصطناعي معطّلة حتى التجديد' : 'الاشتراك فعّال' }}
                            </div>
                            <div class="fs-7 mt-1">
                                الصفحات المتبقية: {{ $subRemainingPages === null ? 'غير محدودة' : number_format($subRemainingPages) }}
                                (المستخدم {{ number_format($subscription->used_pages) }}{{ $subscription->quota_pages !== null ? ' / '.number_format($subscription->quota_pages) : '' }})
                                — الأيام المتبقية: {{ $subRemainingDays === null ? 'بلا انتهاء' : number_format($subRemainingDays) }}
                                @if ($subscription->renewed_at)
                                    — آخر تجديد: {{ $subscription->renewed_at->format('Y-m-d') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('dashboard.settings.subscription.update') }}" method="POST" class="row g-5 align-items-end mb-6">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">حالة الاشتراك</label>
                            <select name="sub_active" class="form-select form-select-solid">
                                <option value="1" @selected($subscription->active)>مفعّل</option>
                                <option value="0" @selected(! $subscription->active)>موقوف</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">تاريخ الانتهاء</label>
                            <input type="date" name="sub_expires_at" class="form-control form-control-solid"
                                value="{{ $subscription->expires_at?->format('Y-m-d') }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">حصة الصفحات (اتركه فارغاً لغير محدود)</label>
                            <input type="number" min="0" name="sub_quota_pages" class="form-control form-control-solid"
                                value="{{ $subscription->quota_pages }}" placeholder="غير محدود" />
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-save me-1"></i> حفظ إعدادات الاشتراك
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('dashboard.settings.subscription.renew') }}" method="POST" class="row g-5 align-items-end border-top pt-5">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">تجديد الاشتراك حتى تاريخ</label>
                            <input type="date" name="renew_expires_at" class="form-control form-control-solid" />
                            <div class="form-text text-muted">اتركه فارغاً للتجديد سنة كاملة من اليوم.</div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa fa-rotate-right me-1"></i> تجديد الاشتراك
                            </button>
                            <div class="form-text text-muted">يُصفّر الصفحات المستخدَمة ويُفعّل الاشتراك.</div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa fa-key text-primary me-2"></i>
                        <h2 class="fw-bold">مفاتيح الـ API وإعدادات التكامل</h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-light-primary border border-primary border-dashed">
                        هذه الإعدادات تُخزَّن في قاعدة البيانات وتتجاوز قيم <code>.env</code>. اترك حقل المفتاح
                        السري فارغاً للإبقاء على القيمة الحالية. مخصّص لمدير النظام فقط.
                    </div>

                    <form action="{{ route('dashboard.settings.update') }}" method="POST" autocomplete="off">
                        @csrf

                        @foreach ($registry as $group => $items)
                            <div class="mb-8">
                                <h3 class="fw-bold text-gray-800 mb-4 border-bottom pb-2">{{ $group }}</h3>
                                <div class="row g-5">
                                    @foreach ($items as $it)
                                        @php
                                            $val = $values[$it['key']] ?? null;
                                            $isSecret = ! empty($it['secret']);
                                            $hasVal = $val !== null && $val !== '';
                                        @endphp
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                {{ $it['label'] }}
                                                @if ($isSecret)
                                                    <span class="badge badge-light-warning ms-1">سري</span>
                                                    @if ($hasVal)
                                                        <span class="badge badge-light-success ms-1">محفوظ ✓</span>
                                                    @else
                                                        <span class="badge badge-light-danger ms-1">غير مضبوط</span>
                                                    @endif
                                                @endif
                                            </label>
                                            <input
                                                type="{{ $isSecret ? 'password' : 'text' }}"
                                                name="setting_{{ $it['key'] }}"
                                                class="form-control form-control-solid"
                                                autocomplete="new-password"
                                                @if ($isSecret)
                                                    placeholder="{{ $hasVal ? '•••••••••• (اتركه فارغاً للإبقاء)' : 'أدخل المفتاح' }}"
                                                @else
                                                    value="{{ $val }}"
                                                    placeholder="{{ $it['placeholder'] ?? '' }}"
                                                @endif
                                            />
                                            <div class="form-text text-muted">{{ $it['key'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        {{-- Custom / additional keys --}}
                        <div class="mb-8">
                            <h3 class="fw-bold text-gray-800 mb-4 border-bottom pb-2">مفاتيح إضافية مخصّصة</h3>
                            <div class="text-muted mb-3">أضف أي مفتاح/قيمة جديدة (مثلاً مزوّد آخر).</div>
                            <div id="custom_rows">
                                @forelse ($custom as $ck => $cv)
                                    <div class="row g-3 mb-3 align-items-end custom-row">
                                        <div class="col-md-5">
                                            <label class="form-label">اسم المفتاح</label>
                                            <input type="text" name="custom_key[]" class="form-control form-control-solid" value="{{ $ck }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">القيمة</label>
                                            <input type="password" name="custom_value[]" class="form-control form-control-solid"
                                                placeholder="{{ $cv !== null && $cv !== '' ? '•••••••••• (اتركه فارغاً للإبقاء)' : 'القيمة' }}" />
                                        </div>
                                    </div>
                                @empty
                                @endforelse
                                {{-- one empty row to add a new key --}}
                                <div class="row g-3 mb-3 align-items-end custom-row">
                                    <div class="col-md-5">
                                        <label class="form-label">اسم المفتاح</label>
                                        <input type="text" name="custom_key[]" class="form-control form-control-solid" placeholder="مثال: openai_api_key" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">القيمة</label>
                                        <input type="password" name="custom_value[]" class="form-control form-control-solid" placeholder="القيمة" />
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add_custom_row" class="btn btn-sm btn-light-primary">
                                <i class="fa fa-plus me-1"></i> إضافة مفتاح آخر
                            </button>
                        </div>

                        <div class="d-flex justify-content-end pt-4 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('styles')
    @include('dashboard.partials.ai-page-styles')
    <style>
        /* ---- settings-page polish: emerald focus rings + card headers ---- */
        .ai-page .form-control:focus,
        .ai-page .form-select:focus {
            border-color: var(--sn-emerald);
            box-shadow: 0 0 0 0.25rem var(--sn-emerald-tint);
        }
        .ai-page .card-header {
            border-bottom: 1px solid var(--sn-emerald-tint);
        }
        .ai-page .card-title i.text-primary {
            color: var(--sn-emerald) !important;
        }
    </style>
@endsection

@section('scripts')
<script>
    document.getElementById('add_custom_row')?.addEventListener('click', function () {
        var rows = document.getElementById('custom_rows');
        var tpl = rows.querySelector('.custom-row');
        var clone = tpl.cloneNode(true);
        clone.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        rows.appendChild(clone);
    });
</script>
@endsection
