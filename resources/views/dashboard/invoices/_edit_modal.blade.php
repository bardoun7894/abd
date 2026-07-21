{{-- Bundle A — visible manual edit / entry modal.
     Shared by show.blade.php (results) and review.blade.php (review).
     Markup only: the wiring JS lives in each host view's @section('scripts')
     (where jQuery + Bootstrap are already loaded and the refresh strategy —
     poll() on show, reload() on review — is known). The seven fields mirror
     error.blade.php's manual-entry form and the correct() whitelist. On save
     the host JS diffs changed fields and POSTs each to /{id}/correct, which
     clears needs_review — making a stuck invoice postable. No <form action>. --}}
<div class="modal fade" id="invEditModal" tabindex="-1" aria-labelledby="invEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-4">
                <h3 class="modal-title fs-5" id="invEditModalLabel">تعديل / إدخال يدوي للفاتورة</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <form id="invEditForm" class="row g-3" onsubmit="return false;">
                    <input type="hidden" name="__id">
                    <div class="col-md-6">
                        <label class="form-label fs-7">اسم المورد</label>
                        <input type="text" class="form-control form-control-sm" name="supplier_name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">الرقم الضريبي للمورد</label>
                        <input type="text" class="form-control form-control-sm" name="supplier_tax_number">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">رقم الفاتورة</label>
                        <input type="text" class="form-control form-control-sm" name="invoice_number">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">تاريخ الفاتورة</label>
                        <input type="date" class="form-control form-control-sm" name="invoice_date">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">المبلغ قبل الضريبة</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="amount_before_vat">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">قيمة الضريبة</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="vat_amount">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">الإجمالي شامل الضريبة</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="total_incl_vat">
                    </div>
                    <div class="col-12 d-flex align-items-center gap-3 mt-4">
                        <button type="button" id="invEditSave" class="btn btn-sm btn-primary">حفظ</button>
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <span id="invEditResult" class="fs-7"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
