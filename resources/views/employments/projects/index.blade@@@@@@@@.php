
@extends('layouts.app')
@section('module'," التشغيل ")
@section('sub',"المشاريع ")
@section('title',"$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif
    <div class="card card-custom">
        <!--<div class="card-header flex-wrap border-0 pt-6 pb-0">-->
        <!--    <div class="card-title">-->
        <!--        <h3 class="card-label">-->
        <!--            {{$page_title}}-->
        <!--            {{--<span class="d-block text-muted pt-2 font-size-sm">Set column width individually</span>--}}-->
        <!--        </h3>-->
        <!--    </div>-->
        <!--    <div class="card-toolbar">-->
                <!--begin::Dropdown-->

            <!--end::Dropdown-->

                <!--begin::Button-->

            <!--end::Button-->
        <!--    </div>-->
        <!--</div>-->


        <div class="card-body" style="padding-top: 5px!important;">
            <div class="d-flex flex-stack mb-5">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                </div>
                <!--end::Search-->
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                    <div class="accordion" id="kt_accordion_1">
                        <div class="accordion-item border-0">
                    <!--begin::Filter-->
                    <button type="button" id="kt_accordion_1_header_1" class="btn btn-light-primary me-3" data-bs-toggle="collapse" data-bs-target="#kt_accordion_1_body_1" aria-expanded="true" aria-controls="kt_accordion_1_body_1" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <!--begin::Svg Icon | path: icons/duotune/general/gen031.svg-->
                        <span class="svg-icon svg-icon-2">
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
															<path d="M19.0759 3H4.72777C3.95892 3 3.47768 3.83148 3.86067 4.49814L8.56967 12.6949C9.17923 13.7559 9.5 14.9582 9.5 16.1819V19.5072C9.5 20.2189 10.2223 20.7028 10.8805 20.432L13.8805 19.1977C14.2553 19.0435 14.5 18.6783 14.5 18.273V13.8372C14.5 12.8089 14.8171 11.8056 15.408 10.964L19.8943 4.57465C20.3596 3.912 19.8856 3 19.0759 3Z" fill="black" />
														</svg>
													</span>
                        <!--end::Svg Icon-->بحث متقدم</button>
                    <!--begin::Menu 1-->
                        </div>
                        </div>
                    <!--end::Menu 1-->
                    <!--end::Filter-->
                    <!--begin::Add customer-->

                    <!--@can('create',\App\Models\Project::class)-->
                    <a href="{{route('projects.create')}}" class="btn btn-primary er fs-6 px-8 py-4">  <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                        <span class="svg-icon svg-icon-2">
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
															<rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1" transform="rotate(-90 11.364 20.364)" fill="black" />
															<rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black" />
														</svg>
													</span>
                        <!--end::Svg Icon-->إضافة مشروع جديد</a>
                    <!--@endcan-->

                    <!--end::Add customer-->

                </div>
                <!--end::Toolbar-->
                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
                    <div class="fw-bolder me-5">
                        <span class="me-2" data-kt-docs-table-select="selected_count"></span>Selected</div>
                    <button type="button" class="btn btn-danger" data-kt-docs-table-select="delete_selected">Selection Action</button>
                </div>
                <!--end::Group actions-->
            </div>
            <div id="kt_accordion_1_body_1" class="accordion-collapse collapse show" aria-labelledby="kt_accordion_1_header_1" data-bs-parent="#kt_accordion_1">
                <div class="accordion-body">
                    <div class="card-body pt-0">
                        <form class="kt-form kt-form--fit mb-0">


                            <div class="row mb-0 search_input">
                                <div class="col-lg-2 mb-6">
                                    <label>رقم الهوية :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-id-card"></i></span></div>

                                        <input type="number" class="form-control datatable-input" name="filter_4" id="filter_4" placeholder="رقم الهوية" data-col-index="5"/>
                                    </div>
                                </div>
                                <div class="col-lg-2 mb-6">
                                    <label>الرقم الوظيفي :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-id-card"></i></span></div>

                                        <input type="number" class="form-control datatable-input" name="filter_1" id="filter_1" placeholder="رقم المستخدم" data-col-index="5"/>
                                    </div>
                                </div>
                                <div class="col-lg-3 mb-6">
                                    <label>اسم المستخدم:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">  <i class="fa fa-user-edit"></i></span></div>

                                        <input type="text" class="form-control datatable-input" name="filter_2" id="filter_2" placeholder="اكتب جزء من اسم المستخدم" data-col-index="5"/>
                                    </div>
                                </div>
                                <div class="col-lg-2 mb-6">
                                    <label>رقم الموبايل :</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-phone"></i></span></div>

                                        <input type="number" class="form-control datatable-input" name="filter_5" id="filter_5" placeholder="رقم الموبايل" data-col-index="5"/>
                                    </div>
                                </div>
                                <div class="col-lg-3 mb-6">
                                    <label>القسم:</label>
                                    <select class="form-select form-select-solid" data-control="select2" name="filter_3" id="filter_3" dir="rtl" data-placeholder="اختر القسم" data-allow-clear="true" multiple="multiple">
                                        @foreach(\App\Models\Department::all() as $item)
                                            <option value="{{$item->id}}">{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                </div>


                               <div class="col-lg-4 mb-6">
                                    <label>تاريخ إضافة المستخدم :</label>
                                    <div class="input-daterange input-group">
                                        <input class="form-control input_date_" placeholder="من" name="from_date" id="from_date"/>
                                        {{--                                        <input type="text" class="form-control datatable-input" name="from_date" id="from_date" placeholder="من" data-col-index="5"/>--}}
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="la la-ellipsis-h"></i></span>
                                        </div>
                                        <input class="form-control input_date_" placeholder="إلى" name="to_date" id="to_date"/>
                                        {{--                                        <input type="text" class="form-control datatable-input" name="to_date" id="to_date" placeholder="إلى" data-col-index="5"/>--}}
                                    </div>
                                </div>



                                <div class="col-lg-3 mt-auto mb-auto">
                                    <button type="button" class="btn btn-primary btn-primary--icon" name="filter" id="filter" {{--id="kt_search"--}}>
						<span>
							<i class="la la-search"></i>
							<span>تصفية</span>
						</span>
                                    </button>
                                    &nbsp;
                                    <button type="button" class="btn btn-secondary btn-secondary--icon" name="refresh" id="refresh" {{--id="kt_reset"--}}>
						<span>
							<i class="la la-close"></i>
							<span>إعادة تعيين</span>
						</span>
                                    </button>
                                </div>
                            </div>


                        </form>
                    </div>
                </div>
            </div>

            <!--begin::Accordion-->

            <!--end::Accordion-->

            <!--begin: Search Form-->
            <!--begin::Search Form-->
            {{--table table-bordered table-hover table-checkable--}}
            {{--table table-separate table-head-custom table-checkable--}}
            <table class="table table-striped table-row-bordered gy-5 gs-7" id="kt_datatable" style="margin-top: 13px !important">
                <thead>
                <tr class="fw-bold fs-6 text-gray-800">
                    <th>رقم الهوية </th>
                    <th>الرقم الموظيفي </th>
                    <th>اسم المستخدم</th>
                    <th>البريد الإلكتروني</th>
                    <th>رقم الموبايل</th>
                    <th>القسم</th>
                    <th>تاريخ الإضافة</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>


            </table>
            <!--end: Datatable-->
        </div>
    </div>

@endsection

{{-- Styles Section --}}
@section('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/plugins/custom/datatables/buttons.dataTables.min.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        .dt-button-collection .dropdown-menu{
            position: relative;
        }
        /*.dir_ltr{*/
        /*    direction: ltr !important;*/
        /*}*/
        div.dt-button-collection{
            width: 172px !important;
        }
        tr{
            cursor: pointer;
        }
    </style>

@endsection
@section('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            }
        });
        var csrf = "{{csrf_token()}}";
        var DATA_URL = "{{ route('projects.data') }}";
        var SITEURL = '{{URL::to('')}}';
        var from_date = -1;
        var to_date = -1;
        var filter_1 = -1;
        var filter_2 = -1;
        var filter_3 = -1;
        var filter_4 = -1;
        var filter_5 = -1;

        //var HOST_URL = "https://preview.keenthemes.com/metronic/theme/html/tools/preview";
    </script>

    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
{{--    <script src="{{ asset('assets/js/pages/features/miscellaneous/sweetalert2.js') }}" type="text/javascript"></script>--}}
    <script src="{{ asset('assets/js/custom/pages/datatable/projects/data-json.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/custom/documentation/forms/select2.js') }}"></script>
  
@endsection




