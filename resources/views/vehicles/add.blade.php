
@extends('layouts.app')

@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري ')
@section('title', isset($vehicle) ? "تعديل بيانات المركبة" : "إضافة مركبة جديدة")

@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif

    <!-- Print all error messages -->
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- تنسيقات CSS إضافية -->
    <style>
        .section-title {
            color: #083da6;
        }
        .form-group{
            text-align: right;
        }
        .section-fieldset {
            padding: 1.4em;
            margin: 0.5em 0;
            border: 1px solid #ddd;
            border-radius: 0.5em;
        }
        .section-legend {
            width: auto;
            padding: 0 10px;
            border-bottom: none;
            font-size: 1.2em;
        }
        .image-preview {
            width: 100px;
            height: auto;
        }
    </style>

    <div class="container mt-5">
        <form action="{{ isset($vehicle) ? route('update_vehicle', $vehicle->id) : route('store_vehicle') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <h2 class="mb-4 section-title">{{ isset($vehicle) ? "تعديل بيانات المركبة" : "إضافة مركبة جديدة" }}</h2>

                <!-- بيانات أساسية -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">بيانات أساسية</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="owner_name">اسم مالك المركبة:</label>
                            <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{ isset($vehicle) ? $vehicle->owner_name : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="owner_id">رقم الهوية لصاحب المركبة:</label>
                            <input type="text" class="form-control" id="owner_id" name="owner_id" value="{{ isset($vehicle) ? $vehicle->owner_id : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="vehicle_type">نوع المركبة:</label>
                            <input type="text" class="form-control" id="vehicle_type" name="vehicle_type" value="{{ isset($vehicle) ? $vehicle->vehicle_type : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="plate_number">رقم لوحة:</label>
                            <input type="text" class="form-control" id="plate_number" name="plate_number" value="{{ isset($vehicle) ? $vehicle->plate_number : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="serial_number">الرقم التسلسلي:</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" value="{{ isset($vehicle) ? $vehicle->serial_number : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="model">الموديل:</label>
                            <input type="text" class="form-control" id="model" name="model" value="{{ isset($vehicle) ? $vehicle->model : '' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="color">اللون:</label>
                            <input type="text" class="form-control" id="color" name="color" value="{{ isset($vehicle) ? $vehicle->color : '' }}" >
                        </div>
                                        </div>
                                        <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                            <label for="manager_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">قائد المجموعة </label>
                                            <div>
                                                <select class="form-select fw-bold  " data-control="select2" id="manager_id_v"
                                                    name="manager_id" dir="rtl" >
                                                    <option value="">اختر</option>
                                                    @foreach ($managers as $x)
                                                    <option value="{{ $x->manager_id }} "  @if( isset($vehicle) and $vehicle->manager_id == $x->manager_id) selected @endif>{{ $x->manager_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>




                </fieldset>


                <!-- رخصة السير -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">رخصة السير</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="license_id">رقم الهوية برخصة السير:</label>
                            <input type="text" class="form-control" id="license_id" name="license_id" value="{{ isset($vehicle) ? $vehicle->license_id : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="license_serial">رقم التسلسلي لرخصة السير:</label>
                            <input type="text" class="form-control" id="license_serial" name="license_serial" value="{{ isset($vehicle) ? $vehicle->license_serial : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="license_image">صورة لرخصة السير:</label>
                            @if (isset($vehicle->license_image))
                            <img src="{{ asset('storage/' . str_replace("public","",$vehicle->license_image)) }}" class="image-preview" alt="صورة رخصة السير">

                        @endif
                            <input type="file" class="form-control-file" id="license_image" name="license_image" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="license_expiry">تاريخ انتهاء رخصة السير:</label>
                            <input type="date" class="form-control" id="license_expiry" name="license_expiry" value="{{ isset($vehicle) ? $vehicle->license_expiry : '' }}" >
                        </div>                    </div>
                </fieldset>

                <!-- المركبة في عهدة الموظف -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">المركبة في عهدة الموظف</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="custodian_name">الاسم:</label>
                            <input type="text" class="form-control" id="custodian_name" name="custodian_name" value="{{ isset($vehicle) ? $vehicle->custodian_name : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="custodian_name">رقم الهوية :</label>
                            <input type="text" class="form-control" id="custodian_id" name="custodian_id" value="{{ isset($vehicle) ? $vehicle->custodian_id : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="custodian_phone">رقم الجوال:</label>
                            <input type="tel" class="form-control" id="custodian_phone" value="{{ isset($vehicle) ? $vehicle->custodian_phone : '' }}" name="custodian_phone" >
                        </div>                    </div>
                </fieldset>

                <!-- تأمين المركبة -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">تأمين المركبة</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="insurance_company">اسم شركة التأمين:</label>
                            <input type="text" class="form-control" id="insurance_company" name="insurance_company" value="{{ isset($vehicle) ? $vehicle->insurance_company : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="policy_number">رقم بوليصة التأمين:</label>
                            <input type="text" class="form-control" id="policy_number" name="policy_number" value="{{ isset($vehicle) ? $vehicle->policy_number : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="insurance_issue">تاريخ إصدار التأمين:</label>
                            <input type="date" class="form-control" id="insurance_issue" name="insurance_issue" value="{{ isset($vehicle) ? $vehicle->insurance_issue : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="insurance_expiry">تاريخ إنتهاء التأمين:</label>
                            <input type="date" class="form-control" id="insurance_expiry" name="insurance_expiry" value="{{ isset($vehicle) ? $vehicle->insurance_expiry : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="insurance_image"> صورة وثيقة التأمين:</label>
                                                    @if (isset($vehicle->insurance_image))
                            <img src="{{ asset('storage/' . str_replace("public","",$vehicle->insurance_image)) }}" class="image-preview" alt="صورة وثيقة التأمين">

                        @endif
                            <input type="file" class="form-control-file" id="insurance_image" name="insurance_image" >
                        </div>                    </div>
                </fieldset>


                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title"> بطاقة السائق </legend>
                    <div class="form-row">

    <div class="form-group">
        <label for="driver_card_number">رقم بطاقة السائق:</label>
        <input type="text" class="form-control" id="driver_card_number" name="driver_card_number" value="{{ isset($vehicle) ? $vehicle->driver_card_number : '' }}" >
    </div>
    <div class="form-group">
        <label for="driver_name">اسم السائق:</label>
        <input type="text" class="form-control" id="driver_name" name="driver_name" value="{{ isset($vehicle) ? $vehicle->driver_name : '' }}" >
    </div>
    <div class="form-group">
        <label for="driver_id">رقم هوية السائق:</label>
        <input type="text" class="form-control" id="driver_id" name="driver_id" value="{{ isset($vehicle) ? $vehicle->driver_id : '' }}" >
    </div>
    <div class="form-group">
        <label for="driver_license_category">تصنيف بطاقة السائق:</label>
        <select  class="form-control" id="driver_license_category" name="driver_license_category"  >
            @if ( isset($vehicle) and isset($vehicle->driver_license_category)  )
                <option selected value="{{$vehicle->driver_license_category}}">{{$vehicle->driver_license_category}}</option>
            @endif
            <option value="">اختر تصنيف بطاقة السائق</option>
            <option value="بطاقة السائق السنوية">بطاقة السائق السنوية</option>
            <option value="بطاقة السائق الموسمية">بطاقة السائق الموسمية</option>
        <option value="بطاقة السائق المقيّدة">بطاقة السائق المقيّدة</option>
        <option value="بطاقة السائق المؤقتة">بطاقة السائق المؤقتة</option>
    </select>
    </div>
    <div class="form-group">
        <label for="driver_license_image">صورة بطاقة السائق:</label>
        @if (isset($vehicle->driver_license_image))
            <img src="{{ asset('storage/' . str_replace("public", "", $vehicle->driver_license_image)) }}" class="image-preview" alt="صورة بطاقة السائق">
        @endif
        <input type="file" class="form-control-file" id="driver_license_image" name="driver_license_image">
    </div>
    <div class="form-group">
        <label for="driver_license_expiry">تاريخ انتهاء بطاقة السائق:</label>
        <input type="date" class="form-control" id="driver_license_expiry" name="driver_license_expiry" value="{{ isset($vehicle) ? $vehicle->driver_license_expiry : '' }}" >

    </div>
                    </div>
</fieldset>
                <!-- كرت التشغيل -->
                <fieldset class="section-fieldset">
                    <legend class="section-legend section-title">كرت التشغيل</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="operation_card_number">رقم الوثيقة كرت التشغيل:</label>
                            <input type="text" class="form-control" id="operation_card_number" name="operation_card_number" value="{{ isset($vehicle) ? $vehicle->operation_card_number : '' }}" >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="operation_card_issue">تاريخ الإصدار كرت التشغيل:</label>
                            <input type="date" class="form-control" id="operation_card_issue" name="operation_card_issue" value="{{ isset($vehicle) ? $vehicle->operation_card_issue : '' }}"  >
                        </div>



                        <div class="form-group col-md-6">
                            <label for="operation_card_expiry">تاريخ الانتهاء كرت التشغيل:</label>
                            <input type="date" class="form-control" id="operation_card_expiry" name="operation_card_expiry" value="{{ isset($vehicle) ? $vehicle->operation_card_expiry : '' }}"  >
                        </div>
                        <div class="form-group col-md-6">
                            <label for="operation_card_image"> صورة من كرت التشغيل:</label>
                            @if (isset($vehicle->operation_card_image))
                            <img src="{{ asset('storage/' . str_replace("public","",$vehicle->operation_card_image)) }}" width="100%" alt="صورة كرت التشغيل ">

                        @endif
                            <input type="file" class="form-control-file" id="operation_card_image" name="operation_card_image"  >
                        </div>                    </div>
                </fieldset>



                <button type="submit" class="btn btn-primary">{{ isset($vehicle) ? "تحديث" : "إرسال" }}</button>
            </div>
        </form>
    </div>

    <!-- إضافة Bootstrap JS و Popper.js و jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@endsection
