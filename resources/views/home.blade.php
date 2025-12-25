@extends('layouts.app')
@section('module', 'نظام إدارة الشركة ')
@section('sub', 'الرئيسية')
@section('title', "$page_title")
@section('content')
    <style>
        .mol_home_ a {
            font-size: 16px !important;
        }
    </style>


    @inject('Carbon', 'Carbon\Carbon')










    <div class="row gy-5 g-xl-8">

        <?php
        if (
        Perm::get_function_access(12) || Perm::get_function_access(13) || Perm::get_function_access(15) || Perm::get_function_access(14) ||
        Perm::get_function_access(16) || Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {?>

        <div class="col-xl-4 ">
            <div class="card card-xl-stretch mb-xl-8">
                <div class="card-header border-0">
                    <h3 class="card-title fw-bolder text-info">اشعارات العمال</h3>
                </div>
                <div class="card-body pt-2 card-scroll h-300px">
                    <?php
                    foreach ($listworker as $x) {
                        if ($x->doe_desc == '3') {
                            $doe_desc_char = 'شارف على الانتهاء';
                        } else if ($x->doe_desc == '2') {
                            $doe_desc_char = 'منتهي الاقامة بتاريخ ';
                        } else if ($x->doe_desc == '1') {
                            $doe_desc_char = 'سارية';
                        } else {
                            $doe_desc_char = 'غير مدخل';
                        }
                        if ($x->dop_desc == '3') {
                            $dop_desc_char = 'شارف على الانتهاء';
                        } else if ($x->dop_desc == '2') {
                            $dop_desc_char = 'منتهي الجواز بتاريخ';
                        } else if ($x->dop_desc == '1') {
                            $dop_desc_char = 'سارية';
                        } else {
                            $dop_desc_char = '>غير مدخل';
                        }
                        if ($x->inside == 1) {
                            $inside_desc = '<span class="ms-2 badge badge-light-success fw-bold">داخل المملكة</span>';
                        } else {
                            $inside_desc = '<span class="ms-2 badge badge-light-danger fw-bold">خارج المملكة</span>';
                        }
                        if ($x->avatar != '') {
                            $avatar = $x->avatar;
                        } else {
                            $avatar = 'assets/media/avatars/blank.png';
                        }
                        if ($x->doe != '') {
                            $udoe_desc =  $Carbon::parse($x->doe)->format('d-m-Y') ;
                        } else {
                            $udoe_desc = '';
                        }
                        if ($x->dop != '') {
                            $udop_desc =  $Carbon::parse($x->dop)->format('d-m-Y') ;
                        } else {
                            $udop_desc = '';
                        }

?>

                    <div class="d-flex align-items-center mb-7  ">
                        <div class="symbol symbol-50px me-5">
                            <img src="{{ $avatar }}" class="" alt="">
                        </div>
                        <div class="flex-grow-1">
                            <a href="#"
                                class=" fw-bolder text-hover-primary fs-6 text-primary">{{ $x->worker_name }}</a>
                                <?php if ($x->doe_desc == '2') { ?>
                            <span class=" d-block fw-bold text-danger ">{{ $doe_desc_char }} - {{ $udoe_desc }}</span>
                            <?php } ?>
                            <?php if ($x->dop_desc == '2') { ?>
                            <span class=" d-block fw-bold text-danger ">{{ $dop_desc_char }} - {{ $udop_desc }}</span>
                            <?php } ?>

                            <span class=" d-block fw-bold text-dark">{{ $x->ssn }}</span>

                        </div>
                    </div>
                    <?php        } ?>
                </div>
            </div>
        </div>
        <?php        } ?>










        <?php if(Perm::get_function_access(50) || Perm::get_function_access(51) || Perm::get_function_access(52) || Perm::get_function_access(53) || Perm::get_function_access(54)){?>

        <div class="col-xl-4">
            <div class="card card-xl-stretch mb-xl-8">
                <div class="card-header border-0">
                    <h3 class="card-title fw-bolder text-info">معاملات</h3>
                </div>
                <div class="card-body pt-0 card-scroll h-300px">
                    <?php
                    foreach ($listmoraslat as $x) {
                        if ($x->is_read == '0') {
                    $is_read_desc = 'غير مقروء';
                } else if ($x->is_read == '1') {
                    $is_read_desc = 'مقروء';
                } else {
                    $is_read_desc = 'غير مدخل';
                }
                $moraslat_status_name =  $x->moraslat_status_name;
                if ($x->create_user == Auth::user()->id) {
                    $type_desc = 'صادر';
                } else if ($x->user_id == Auth::user()->id) {
                    $type_desc = 'وارد';
                } else {
                    $type_desc = '';
                }
                if($moraslat_status_name==''){
                    $moraslat_status_name='جديدة';
                }
                        ?>
                    <div class="d-flex align-items-center @if ($x->create_user == Auth::user()->id) bg-light-success rounded p-5 mb-7 @else bg-light-danger rounded p-5 mb-7  @endif ">
                        <span class="svg-icon @if ($x->create_user == Auth::user()->id) svg-icon-success me-5 @else svg-icon-danger me-5  @endif ">
                            <span class="svg-icon svg-icon-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <path opacity="0.3"
                                        d="M21.25 18.525L13.05 21.825C12.35 22.125 11.65 22.125 10.95 21.825L2.75 18.525C1.75 18.125 1.75 16.725 2.75 16.325L4.04999 15.825L10.25 18.325C10.85 18.525 11.45 18.625 12.05 18.625C12.65 18.625 13.25 18.525 13.85 18.325L20.05 15.825L21.35 16.325C22.35 16.725 22.35 18.125 21.25 18.525ZM13.05 16.425L21.25 13.125C22.25 12.725 22.25 11.325 21.25 10.925L13.05 7.62502C12.35 7.32502 11.65 7.32502 10.95 7.62502L2.75 10.925C1.75 11.325 1.75 12.725 2.75 13.125L10.95 16.425C11.65 16.725 12.45 16.725 13.05 16.425Z"
                                        fill="black"></path>
                                    <path
                                        d="M11.05 11.025L2.84998 7.725C1.84998 7.325 1.84998 5.925 2.84998 5.525L11.05 2.225C11.75 1.925 12.45 1.925 13.15 2.225L21.35 5.525C22.35 5.925 22.35 7.325 21.35 7.725L13.05 11.025C12.45 11.325 11.65 11.325 11.05 11.025Z"
                                        fill="black"></path>
                                </svg>
                            </span>
                        </span>
                        <div class="flex-grow-1 me-2">
                            <a href="#" class="fw-bolder text-gray-800 text-info fs-6">{{ $x->moraslat_type_name }} - {{ $x->moraslat_categoty_name }}</a>
                            <span class=" fw-bold d-block text-dark">{{ $x->moraslat_id }}-{{ $type_desc }}</span>
                        </div>
                        <div class="flex">
                        <span class="fw-bolder text-danger py-1">{{ $is_read_desc }}</span>
                        <span class="text-dark fw-bold d-block">{{ $moraslat_status_name }}</span>
                    </div>
                    </div>

<?php } ?>

                </div>
            </div>
        </div>
        <?php } ?>


        <?php  if( Auth()->user()->emp_job==1){ ?>

        <div class="col-xl-4">
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <div class="card-body pt-5 card-scroll h-300px">
                    <div class="fs-4 fw-bold text-info mb-7">احصائية مصاريف العمال -{{$Carbon::now()->format('Y')}}</div>
                    <div class="fs-6 d-flex justify-content-between mb-4">
                        <div class="fw-bold">اجمالي المبلغ المطلوب</div>
                        <div class="d-flex fw-bolder">
                        <span class="svg-icon svg-icon-3 me-1 svg-icon-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M13.4 10L5.3 18.1C4.9 18.5 4.9 19.1 5.3 19.5C5.7 19.9 6.29999 19.9 6.69999 19.5L14.8 11.4L13.4 10Z" fill="black"></path>
                                <path opacity="0.3" d="M19.8 16.3L8.5 5H18.8C19.4 5 19.8 5.4 19.8 6V16.3Z" fill="black"></path>
                            </svg>
                        </span>
                        {{ $sum_c1f }}</div>
                    </div>
                    <div class="separator separator-dashed border-2 "></div>
                    <div class="fs-6 d-flex justify-content-between my-4">
                        <div class="fw-bold">اجمالي المدفوع</div>
                        <div class="d-flex fw-bolder">
                        <span class="svg-icon svg-icon-3 me-1 svg-icon-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M13.4 14.8L5.3 6.69999C4.9 6.29999 4.9 5.7 5.3 5.3C5.7 4.9 6.29999 4.9 6.69999 5.3L14.8 13.4L13.4 14.8Z" fill="black"></path>
                                <path opacity="0.3" d="M19.8 8.5L8.5 19.8H18.8C19.4 19.8 19.8 19.4 19.8 18.8V8.5Z" fill="black"></path>
                            </svg>
                        </span>
                        {{ $sum_sum_det_financial_month_pay_Allf }}</div>
                    </div>
                    <div class="separator separator-dashed border-2 "></div>
                    <div class="fs-6 d-flex justify-content-between mt-4">
                        <div class="fw-bold">اجمالي المتبقي</div>
                        <div class="d-flex fw-bolder">
                        <span class="svg-icon svg-icon-3 me-1 svg-icon-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M13.4 10L5.3 18.1C4.9 18.5 4.9 19.1 5.3 19.5C5.7 19.9 6.29999 19.9 6.69999 19.5L14.8 11.4L13.4 10Z" fill="black"></path>
                                <path opacity="0.3" d="M19.8 16.3L8.5 5H18.8C19.4 5 19.8 5.4 19.8 6V16.3Z" fill="black"></path>
                            </svg>
                        </span>
                       {{ $sum_xxf }}</div>
                    </div>
                    <div class="separator separator-dotted  pt-5 border-danger my-2 "></div>

                    <div class="fs-4 fw-bold text-info mb-7">احصائية مصاريف المحلات -{{$Carbon::now()->format('Y')}}</div>
                    <div class="fs-6 d-flex justify-content-between mb-4">
                        <div class="fw-bold">اجمالي المبلغ المطلوب</div>
                        <div class="d-flex fw-bolder">
                        <span class="svg-icon svg-icon-3 me-1 svg-icon-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M13.4 10L5.3 18.1C4.9 18.5 4.9 19.1 5.3 19.5C5.7 19.9 6.29999 19.9 6.69999 19.5L14.8 11.4L13.4 10Z" fill="black"></path>
                                <path opacity="0.3" d="M19.8 16.3L8.5 5H18.8C19.4 5 19.8 5.4 19.8 6V16.3Z" fill="black"></path>
                            </svg>
                        </span>
                        {{ $sum_c1 }}</div>
                    </div>
                    <div class="separator separator-dashed border-2 "></div>
                    <div class="fs-6 d-flex justify-content-between my-4">
                        <div class="fw-bold">اجمالي المدفوع</div>
                        <div class="d-flex fw-bolder">
                        <span class="svg-icon svg-icon-3 me-1 svg-icon-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M13.4 14.8L5.3 6.69999C4.9 6.29999 4.9 5.7 5.3 5.3C5.7 4.9 6.29999 4.9 6.69999 5.3L14.8 13.4L13.4 14.8Z" fill="black"></path>
                                <path opacity="0.3" d="M19.8 8.5L8.5 19.8H18.8C19.4 19.8 19.8 19.4 19.8 18.8V8.5Z" fill="black"></path>
                            </svg>
                        </span>
                        {{ $sum_sum_det_calculate_month_pay_All }}</div>
                    </div>
                    <div class="separator separator-dashed border-2 "></div>
                    <div class="fs-6 d-flex justify-content-between mt-4">
                        <div class="fw-bold">اجمالي المتبقي</div>
                        <div class="d-flex fw-bolder">
                        <span class="svg-icon svg-icon-3 me-1 svg-icon-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M13.4 10L5.3 18.1C4.9 18.5 4.9 19.1 5.3 19.5C5.7 19.9 6.29999 19.9 6.69999 19.5L14.8 11.4L13.4 10Z" fill="black"></path>
                                <path opacity="0.3" d="M19.8 16.3L8.5 5H18.8C19.4 5 19.8 5.4 19.8 6V16.3Z" fill="black"></path>
                            </svg>
                        </span>
                       {{ $sum_xx }}</div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>


        <?php
        if (
        Perm::get_function_access(12) || Perm::get_function_access(13) || Perm::get_function_access(15) || Perm::get_function_access(14) ||
        Perm::get_function_access(16) || Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {?>

        <div class="col-xl-4">
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder text-info">العمال حسب المجموعات</span>
                    </h3>
                </div>
                <div class="card-body pt-5 card-scroll h-400px">
                    <canvas id="kt_chartjs_tt" class="mh-400px"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder text-info">العمال حسب المجموعات</span>
                    </h3>
                </div>
                <div class="card-body pt-5 card-scroll h-400px">
                    <canvas id="kt_chartjs_bb" class="mh-400px"></canvas>
                </div>
            </div>
        </div>
        <?php } ?>



    </div>
@endsection
@section('styles')
    <style>
       body {
            background-color: #fff;
        }
        #kt_content {
            background-color: #f5f8fa !important;
        }
    </style>
@endsection
@section('scripts')
<script>
var ctx = document.getElementById('kt_chartjs_tt');
var primaryColor = KTUtil.getCssVariableValue('--bs-primary');
var dangerColor = KTUtil.getCssVariableValue('--bs-danger');
var successColor = KTUtil.getCssVariableValue('--bs-success');
var warningColor = KTUtil.getCssVariableValue('--bs-warning');
var infoColor = KTUtil.getCssVariableValue('--bs-info');
var fontFamily = KTUtil.getCssVariableValue('--bs-font-sans-serif');
const labels = <?php echo $ch_data_bar2; ?>;
const data = {
    labels: labels,
    datasets: [{
        data: <?php echo $ch_data_bar; ?>,
    backgroundColor: [
        'rgb(255, 205, 86)',
      'rgb(255, 99, 132)',
      'rgb(54, 162, 235)',
      'rgb(255, 205, 86)',
      'rgba(255, 99, 132, 0.2)',
      'rgba(255, 159, 64, 0.2)',
      'rgba(255, 205, 86, 0.2)',
      'rgba(75, 192, 192, 0.2)',
      'rgba(54, 162, 235, 0.2)',
      'rgba(153, 102, 255, 0.2)',
      'rgba(201, 203, 207, 0.2)',
      primaryColor,
      successColor,
      warningColor,
      infoColor,
    ],
    borderColor: [
      'rgb(255, 99, 132)',
      'rgb(255, 159, 64)',
      'rgb(255, 205, 86)',
      'rgb(75, 192, 192)',
      'rgb(54, 162, 235)',
      'rgb(153, 102, 255)',
      'rgb(201, 203, 207)'
    ],
    }]
};
const config = {
    type: 'pie',
    data: data,
    options: {
        plugins: {
            title: {
                display: false,
            }
        },
        responsive: true,
    },
    defaults:{
        global: {
            defaultFont: fontFamily
        }
    }
};
var myChart = new Chart(ctx, config);
var ctx2 = document.getElementById('kt_chartjs_bb');
var primaryColor = KTUtil.getCssVariableValue('--bs-primary');
var dangerColor = KTUtil.getCssVariableValue('--bs-danger');
var successColor = KTUtil.getCssVariableValue('--bs-success');
var warningColor = KTUtil.getCssVariableValue('--bs-warning');
var infoColor = KTUtil.getCssVariableValue('--bs-info');
var fontFamily = KTUtil.getCssVariableValue('--bs-font-sans-serif');
const labels2 = <?php echo $ch_data_bar2; ?>;
const data2 = {
    labels: labels2,
    datasets: [{
        data: <?php echo $ch_data_bar; ?>,
        label: 'الرسم البياني',
    backgroundColor: [
        'rgb(255, 205, 86)',
      'rgb(255, 99, 132)',
      'rgb(54, 162, 235)',
      'rgb(255, 205, 86)',
      'rgba(255, 99, 132, 0.2)',
      'rgba(255, 159, 64, 0.2)',
      'rgba(255, 205, 86, 0.2)',
      'rgba(75, 192, 192, 0.2)',
      'rgba(54, 162, 235, 0.2)',
      'rgba(153, 102, 255, 0.2)',
      'rgba(201, 203, 207, 0.2)',
      primaryColor,
      successColor,
      warningColor,
      infoColor,
    ],
    borderColor: [
      'rgb(255, 99, 132)',
      'rgb(255, 159, 64)',
      'rgb(255, 205, 86)',
      'rgb(75, 192, 192)',
      'rgb(54, 162, 235)',
      'rgb(153, 102, 255)',
      'rgb(201, 203, 207)'
    ],
    }]
};
const config2 = {
    type: 'bar',
    data: data2,
    options: {
        plugins: {
            title: {
                display: false,
            }
        },
        responsive: true,
        interaction: {
            intersect: false,
        },
        scales: {
            x: {
                stacked: true,
            },
            y: {
                stacked: true
            }
        }
    },
    defaults:{
        global: {
            defaultFont: fontFamily
        }
    }
};
var myChart2 = new Chart(ctx2, config2);
</script>
@endsection
