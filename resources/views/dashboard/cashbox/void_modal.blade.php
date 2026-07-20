{{-- Spec 008 bundle 1 (cashbox) — shared "void a receipt" modal. Reason is
     mandatory client-side (submit disabled while empty) AND enforced again
     server-side by CashboxController::voidReceipt() / CashboxService::voidReceipt().
     Reusable: callers pass the target URL + extra POST params via
     openCashboxVoidModal({url, params, onSuccess}). --}}
<div class="modal fade" id="cashbox_void_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إلغاء السند</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    هذا الإجراء لا يحذف السند — سيتم تعليمه كملغى وتسجيل حركة عكسية في الصندوق. سبب الإلغاء إلزامي.
                </div>
                <label class="form-label fw-semibold">سبب الإلغاء <span class="text-danger">*</span></label>
                <textarea id="cashbox_void_reason" class="form-control" rows="3" required></textarea>
                <div id="cashbox_void_error" class="text-danger mt-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" id="cashbox_void_confirm" class="btn btn-danger fw-bold">تأكيد الإلغاء</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var __cashboxVoidState = null;

function openCashboxVoidModal(idOrOptions, onSuccessMaybe) {
    // Backward/forward compatible: accepts either (id, onSuccess) for the
    // generic cashbox ledger page, or ({url, params, onSuccess}) for a
    // module-specific void endpoint (e.g. shop rentpay).
    if (typeof idOrOptions === 'object') {
        __cashboxVoidState = idOrOptions;
    } else {
        __cashboxVoidState = {
            url: "{{ route('dashboard.cashbox.receipt.void') }}",
            params: { receipt_id: idOrOptions },
            onSuccess: onSuccessMaybe,
        };
    }
    $('#cashbox_void_reason').val('');
    $('#cashbox_void_error').addClass('d-none').text('');
    var modalEl = document.getElementById('cashbox_void_modal');
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else {
        $(modalEl).modal('show');
    }
}

$(function () {
    $('#cashbox_void_confirm').on('click', function () {
        if (!__cashboxVoidState) { return; }
        var reason = $('#cashbox_void_reason').val();
        if (!reason || !reason.trim()) {
            $('#cashbox_void_error').removeClass('d-none').text('سبب الإلغاء مطلوب');
            return;
        }
        var payload = Object.assign({}, __cashboxVoidState.params, { reason: reason });
        $.ajax({
            url: __cashboxVoidState.url,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: payload,
            success: function (res) {
                if (res && res.status) {
                    var modalEl = document.getElementById('cashbox_void_modal');
                    if (window.bootstrap && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    } else {
                        $(modalEl).modal('hide');
                    }
                    if (typeof __cashboxVoidState.onSuccess === 'function') {
                        __cashboxVoidState.onSuccess(res);
                    }
                } else {
                    $('#cashbox_void_error').removeClass('d-none').text((res && res.message_out) || 'تعذّر الإلغاء');
                }
            },
            error: function (xhr) {
                $('#cashbox_void_error').removeClass('d-none').text((xhr.responseJSON && xhr.responseJSON.message_out) || 'تعذّر الإلغاء');
            }
        });
    });
});
</script>
