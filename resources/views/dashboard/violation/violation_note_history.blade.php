<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }

    .tox-notifications-container {
        display: none !important;
    }
</style>





<form id="upd_note_data"  class="form"    >
    @csrf


    <input name="violation_id" id="violation_id" value="{{ $violation->violation_id }}" im-insert="true"
        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="violation_id"
        aria-describedby="basic-addon1">
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">
            <div class="card">
                <div class="card-body ">










                    <div class="row p-0 mb-0 px-1">
                        <!--begin::Col-->
                        <div class="col">
                            <div
                                class="border border-dashed border-dark text-center min-w-125px rounded pt-4 pb-2 my-3">
                                <span class="fs-6 fw-bold text-info ">رقم المخالفة :</span> <span
                                    class=" fw-bold text-success d-block">{{ $violation->violation_no }}</span>
                            </div>
                        </div>
                        <div class="col">
                            <div
                                class="border border-dashed border-dark text-center min-w-125px  rounded pt-4 pb-2 my-3">
                                <span class="fs-6 fw-bold text-info ">قيمة المخافة:</span> <span
                                    class=" fw-bold text-success d-block">{{ $violation->violation_val }}</span>
                            </div>
                        </div>


                    </div>


                    <div class="mb-0">
                        <div class="row gx-5 mb-5">






                            <div class="separator separator-content border-dark my-10 mb-8"><span
                                    class="w-150px fw-bold text-danger">بيانات الارشيف</span></div>











                                    <div id="result_history_tbl" name="result_history_tbl"></div>













                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@section('styles')
    <style>
        .select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
            padding-bottom: 2px;
        }
    </style>
    <script type="text/javascript" src="{{ asset('assets/module/violation_j.js') }}?t={{ config('global.ver.version_all') }}">
    </script>
    <script>
        view_all_note_history("{{ route('dashboard.violation.tbl_history') }}");
    </script>
