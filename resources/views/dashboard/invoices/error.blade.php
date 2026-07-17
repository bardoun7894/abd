@extends('layouts.app')
@section('module', 'استخراج الفواتير')
@section('sub', 'الفواتير التي تعذّر استخراجها')
@section('title', "$page_title")
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="fs-6 text-muted">
            @if (!empty($batchId))
                نتائج دفعة #{{ $batchId }} — <a href="{{ route('dashboard.invoices.error') }}">عرض كل الدفعات الفاشلة</a>
            @else
                جميع الفواتير والدفعات التي فشل استخراجها
            @endif
        </div>
        <a href="{{ route('dashboard.invoices.index') }}" class="btn btn-sm btn-light-primary">→ سجل الدفعات</a>
    </div>

    @if ($batches->isNotEmpty())
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">دفعات فشلت بالكامل</h3></div>
            <div class="table-responsive">
                <table class="table table-striped gy-3 align-middle">
                    <thead>
                        <tr class="fw-bold fs-7 text-gray-800 border-bottom-2 border-gray-800" style="background-color:#ffb822 !important;">
                            <th>#</th><th>الملف</th><th>سبب الفشل</th><th>التاريخ</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($batches as $b)
                            <tr>
                                <td>{{ $b->id }}</td>
                                <td>{{ $b->original_filename }}</td>
                                <td class="text-danger">{{ $b->error_message ?: 'غير معروف' }}</td>
                                <td>{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ asset($b->pdf_path) }}" target="_blank" class="btn btn-sm btn-light">الملف الأصلي</a>
                                    <button class="btn btn-sm btn-light-warning act-reprocess-batch" data-batch="{{ $b->id }}">↻ إعادة معالجة</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($invoices->isEmpty() && $batches->isEmpty())
        <div class="card"><div class="card-body text-center text-muted py-10">لا توجد فواتير فاشلة حاليًا 🎉</div></div>
    @endif

    @foreach ($invoices as $invoice)
        <div class="card mb-5" id="err-{{ $invoice->id }}">
            <div class="card-header flex-wrap">
                <h3 class="card-title">دفعة #{{ $invoice->batch_id }} — صفحة {{ $invoice->page_number }}</h3>
                <div class="card-toolbar flex-wrap">
                    <a href="{{ asset($invoice->batch->pdf_path ?? '') }}" target="_blank" class="btn btn-sm btn-light">الملف الأصلي</a>
                    <button class="btn btn-sm btn-light-warning act-reprocess" data-id="{{ $invoice->id }}">↻ إعادة معالجة</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-lg-4">
                        @if ($invoice->image_url)
                            <img src="{{ $invoice->image_url }}" loading="lazy" class="inv-thumb w-100 rounded border" data-full="{{ $invoice->image_url }}" style="cursor:zoom-in" title="اضغط للتكبير">
                        @else
                            <div class="text-muted text-center border rounded py-10">لا توجد صورة للصفحة</div>
                        @endif
                        <div class="alert alert-danger mt-3 py-2 px-3 fs-7 mb-0">{{ $invoice->error_message ?: 'فشل الاستخراج لسبب غير معروف' }}</div>
                    </div>
                    <div class="col-lg-8">
                        <form class="row g-3 manual-entry-form" data-id="{{ $invoice->id }}">
                            <div class="col-md-6">
                                <label class="form-label fs-7">اسم المورد</label>
                                <input type="text" class="form-control form-control-sm" name="supplier_name" value="{{ $invoice->supplier_name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-7">الرقم الضريبي للمورد</label>
                                <input type="text" class="form-control form-control-sm" name="supplier_tax_number" value="{{ $invoice->supplier_tax_number }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-7">رقم الفاتورة</label>
                                <input type="text" class="form-control form-control-sm" name="invoice_number" value="{{ $invoice->invoice_number }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-7">تاريخ الفاتورة</label>
                                <input type="date" class="form-control form-control-sm" name="invoice_date" value="{{ $invoice->invoice_date?->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7">المبلغ قبل الضريبة</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="amount_before_vat" value="{{ $invoice->amount_before_vat }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7">قيمة الضريبة</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="vat_amount" value="{{ $invoice->vat_amount }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7">الإجمالي شامل الضريبة</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="total_incl_vat" value="{{ $invoice->total_incl_vat }}">
                            </div>
                            <div class="col-12 d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-sm btn-primary">حفظ البيانات يدويًا</button>
                                <span class="manual-entry-result fs-7"></span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div id="invLb" style="position:fixed;inset:0;z-index:1090;display:none;place-items:center;background:rgba(0,0,0,.85);padding:30px" onclick="this.style.display='none'">
        <img id="invLbImg" src="" style="max-width:92vw;max-height:92vh;border-radius:8px;box-shadow:0 30px 80px -20px #000">
    </div>
@endsection
@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });
        var base = "{{ url('dashboard/invoices') }}";

        $(document).on('click', '.act-reprocess', function () {
            var id = $(this).data('id'), $btn = $(this).prop('disabled', true);
            $.post(base + '/' + id + '/reprocess', {})
                .done(function () { $('#err-' + id).fadeTo(300, 0.5); })
                .always(function () { $btn.prop('disabled', false); });
        });

        $(document).on('click', '.act-reprocess-batch', function () {
            // No dedicated batch-level endpoint here: reprocess acts per invoice,
            // so trigger it for the batch's own review/results page instead.
            window.location = "{{ url('dashboard/invoices') }}/" + $(this).data('batch');
        });

        $(document).on('submit', '.manual-entry-form', function (e) {
            e.preventDefault();
            var $form = $(this), id = $form.data('id'), $result = $form.find('.manual-entry-result');
            $result.text('جارٍ الحفظ…').removeClass('text-success text-danger');
            $.post(base + '/' + id + '/manual-entry', $form.serialize())
                .done(function (r) {
                    $result.text(r.message_out || 'تم الحفظ').addClass('text-success');
                    $('#err-' + id).fadeTo(300, 0.6);
                })
                .fail(function (xhr) {
                    var m = (xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الحفظ';
                    $result.text(m).addClass('text-danger');
                });
        });

        $(document).on('click', '.inv-thumb', function () {
            document.getElementById('invLbImg').src = this.dataset.full;
            document.getElementById('invLb').style.display = 'grid';
        });
    </script>
@endsection
