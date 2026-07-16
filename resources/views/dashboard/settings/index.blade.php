@extends('layouts.app')
@section('module', 'إعدادات النظام')
@section('sub', 'مفاتيح الـ API')
@section('title', 'إعدادات مفاتيح الـ API')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <div class="post d-flex flex-column-fluid">
        <div class="container-xxl">

            @if (session('success'))
                <div class="alert alert-success d-flex align-items-center">
                    <i class="fa fa-check-circle text-success me-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

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
                                            <input type="text" name="custom_value[]" class="form-control form-control-solid" value="{{ $cv }}" />
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
                                        <input type="text" name="custom_value[]" class="form-control form-control-solid" placeholder="القيمة" />
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
