{{-- Spec 008 bundle 1 (cashbox) — shared "capture a receipt" modal, shown when
     an unpaid money-document transitions to paid. Reusable: callers pass the
     target URL + extra POST params + a default amount via
     openCashboxReceiptModal({url, params, defaultAmount, onSuccess}). --}}
<div class="modal fade" id="cashbox_receipt_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تسجيل سند قبض</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">المبلغ المستلم <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" id="cashbox_receipt_amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">تاريخ الاستلام <span class="text-danger">*</span></label>
                    <input type="date" id="cashbox_receipt_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">اسم الدافع</label>
                    <input type="text" id="cashbox_receipt_payer" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">ملاحظة</label>
                    <textarea id="cashbox_receipt_note" class="form-control" rows="2"></textarea>
                </div>
                <div id="cashbox_receipt_error" class="text-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" id="cashbox_receipt_confirm" class="btn btn-success fw-bold">تأكيد الاستلام</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var __cashboxReceiptState = null;

function openCashboxReceiptModal(options) {
    __cashboxReceiptState = options;
    $('#cashbox_receipt_amount').val(options.defaultAmount || '');
    $('#cashbox_receipt_date').val(new Date().toISOString().slice(0, 10));
    $('#cashbox_receipt_payer').val('');
    $('#cashbox_receipt_note').val('');
    $('#cashbox_receipt_error').addClass('d-none').text('');
    var modalEl = document.getElementById('cashbox_receipt_modal');
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else {
        $(modalEl).modal('show');
    }
}

$(function () {
    $('#cashbox_receipt_confirm').on('click', function () {
        if (!__cashboxReceiptState) { return; }
        var amount = parseFloat($('#cashbox_receipt_amount').val());
        var date = $('#cashbox_receipt_date').val();
        if (!amount || amount <= 0) {
            $('#cashbox_receipt_error').removeClass('d-none').text('المبلغ مطلوب ويجب أن يكون أكبر من صفر');
            return;
        }
        if (!date) {
            $('#cashbox_receipt_error').removeClass('d-none').text('تاريخ الاستلام مطلوب');
            return;
        }
        var payload = Object.assign({}, __cashboxReceiptState.params, {
            amount: amount,
            receipt_date: date,
            payer_name: $('#cashbox_receipt_payer').val(),
            note: $('#cashbox_receipt_note').val(),
        });
        $.ajax({
            url: __cashboxReceiptState.url,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: payload,
            success: function (res) {
                if (res && res.status) {
                    var modalEl = document.getElementById('cashbox_receipt_modal');
                    if (window.bootstrap && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    } else {
                        $(modalEl).modal('hide');
                    }
                    if (typeof __cashboxReceiptState.onSuccess === 'function') {
                        __cashboxReceiptState.onSuccess(res);
                    }
                } else {
                    $('#cashbox_receipt_error').removeClass('d-none').text((res && res.message_out) || 'تعذّر التسجيل');
                }
            },
            error: function (xhr) {
                $('#cashbox_receipt_error').removeClass('d-none').text((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر التسجيل');
            }
        });
    });
});
</script>
