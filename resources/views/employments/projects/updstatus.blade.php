<style type="text/css">
    .select2-selection__arrow b {
        display: none !important;
    }
    .tox-notifications-container{
    display:none !important;
}
    </style>
<script>
document.addEventListener('focusin', (e) => {
  if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
    e.stopImmediatePropagation();
  }
});
</script>

<form id="upd_status_data" name="upd_status_data" class="form" action="{{route('projects.updstatus')}}"
    method="post" enctype="multipart/form-data" autocomplete="off">
                        @csrf


                        <input name="PROJECT_ID_IN" id="PROJECT_ID_IN" value="{{$project->project_id}}" im-insert="true"
                        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="PROJECT_ID_IN"
                        aria-describedby="basic-addon1">
            <input name="status_db" id="status_db" value="{{$project->status}}" im-insert="true"
                        data-inputmask="'alias' : 'integer' " type="text" style="display:none"
                        class="form-control kt-font-dark kt-font-bolder " readonly placeholder="status_db"
                        aria-describedby="basic-addon1">

              <!--begin::Layout-->
    <div class="d-flex flex-column flex-lg-row">

        <!--begin::Content-->

        <div class="flex-lg-row-fluid mb-10 mb-lg-0 ">


            <div class="card">
                <div class="card-body ">
       <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10"
                        id="errorBox_project" style="display: none !important">
                        <i class="ki-duotone ki-search-list fs-2hx text-success me-4 mb-5 mb-sm-0"><span
                                class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10" id="displayErrors_project">
                            <h5 class="mb-1">This is an alert</h5>
                            <span>The alert component can be used to highlight certain parts of your page for higher
                                content visibility.</span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span
                                    class="path2"></span></i>
                        </button>
                    </div>


                    <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10"
                        id="errorBox_project" style="display: none !important">
                        <i class="ki-duotone ki-search-list fs-2hx text-success me-4 mb-5 mb-sm-0"><span
                                class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10" id="displayErrors_project">
                            <h5 class="mb-1">This is an alert</h5>
                            <span></span>
                        </div>
                        <button type="button"
                            class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                            data-bs-dismiss="alert">
                            <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span
                                    class="path2"></span></i>
                        </button>
                    </div>
                    <div class="mb-0">
                        <div class="row gx-5 mb-5">

                            <div class="col-12 col-lg-12 col-md-12 col-sm-12 mb-5">
                                <label class=" form-label fs-6 fw-bold text-dark mb-3">الحالة</label>
                                <div>
                                    <select class="form-select form-select_u fw-bold" data-control="select2" id="STATUS_IN"
                                        name="STATUS_IN" dir="rtl" >
                                        <option value="">اختر ..</option>
                                        @foreach ($status as $x)
                                        <option @selected($project->status==$x->status_id) value="{{ $x->status_id }} ">{{ $x->status_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

















   


























               




                        </div>
                        <!--end::Row-->



























                        <!--begin::Actions-->
                        <div class="text-center mb-0  ">


                            <button type="submit" id="kt_docs_submitsss"
                                class="btn btn-primary font-weight-bold mr-2" name="submitButton">حفظ البيانات</button>
                        </div>
                        <!--end::Actions-->

                    </div>
                    <!--end::Wrapper-->
                    <!--end::Form-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>



    </div>
                </form>

{{-- Styles Section --}}
@section('styles')
<style>
.tox-tinymce {
    border-radius: 0.475rem !important;
    height: 200px !important;
}

.tox:not([dir="rtl"]) {
    direction: rtl !important;
    text-align: right !important;
}

.separator.separator-content {
    display: flex;
    align-items: center;
    border-bottom: 0;
    border-bottom-color: currentcolor;
    text-align: center;
}

.separator {
    display: block;
    height: 0;
    border-bottom: 1px solid var(--bs-border-color);
}

.my-15 {
    margin-top: 3.75rem !important;
    margin-bottom: 3.75rem !important;
}

.border-dark {
    --bs-border-opacity: 1;
    border-color: rgba(var(--bs-dark-rgb), var(--bs-border-opacity)) !important;
}

.separator.separator-content.border-dark::before,
.separator.separator-content.border-dark::after {
    border-color: #071437 !important;
}

.separator.separator-content::before {
    margin-right: 1.25rem;
}

.separator.separator-content::before,
.separator.separator-content::after {
    content: " ";
    width: 50%;
    border-bottom: 1px solid var(--bs-border-color);
}

.separator.separator-content.border-dark::before,
.separator.separator-content.border-dark::after {
    border-color: #071437 !important;
}

.separator.separator-content::before,
.separator.separator-content::after {
    content: " ";
    width: 50%;
    border-bottom: 1px solid #071437;
}
</style>
   <script type="text/javascript" src="{{ asset('assets/module/woker_j.js') }}?t={{ config('global.ver.version_all') }}"></script>

<script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/documentation/documentation.js') }}"></script>
<script src="{{ asset('assets/js/custom/documentation/search.js') }}"></script>




<script>
$(document).ready(function() {
$(".form-select_u").select2({
dropdownParent: $('#view_prim_const_sm .modal-content')
    });
});

$('.input_date_').flatpickr({
format : 'dd-mm-yyyy',
"locale": "ar",
});
</script>









