@extends('layouts.app')
@section('module',"وزارة العمل ")
@section('sub',"المشاريع ")
@section('title',"$page_title")
@section('content')
    @if (session()->has('alert.success'))
        <div class="alert alert-success">
            {{ session('alert.success') }}
        </div>
    @endif
    <div class="flex-lg-row-fluid me-lg-15 order-2 order-lg-1 mb-10 mb-lg-0">
        <!--begin::Card-->
        <div class="card card-flush pt-3 mb-5 mb-xl-10">
            <!--begin::Card header-->
            <!--<div class="card-header">-->
                <!--begin::Card title-->
            <!--    <div class="card-title">-->
            <!--        <h2 class="fw-bolder">تفاصيل المستخدم</h2>-->
            <!--    </div>-->
                <!--begin::Card title-->
                <!--begin::Card toolbar-->
            <!--    <div class="card-toolbar">-->
            <!--        <a href="{{route('users.edit',['user'=>$user->id])}}" class="btn btn-light-primary">-->
            <!--            تعديل بيانات المستخدم-->
            <!--        </a>-->
            <!--    </div>-->
                <!--end::Card toolbar-->
            <!--</div>-->
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-3">
                <!--begin::Section-->
                <div class="mb-10">
{{--                    <!--begin::Title-->--}}
{{--                    <h5 class="mb-4">رقم الطلب:</h5>--}}
{{--                    <!--end::Title-->--}}
                    <!--begin::Details-->
                    <div class="d-flex flex-wrap py-5">
                        <!--begin::Row-->
                        <div class="flex-equal me-5">
                            <!--begin::Details-->
                            <table class="table fs-6 fw-bold gs-0 gy-2 gx-2 m-0">

                                <!--begin::Row-->
                                <tr>
                                    <td class="text-gray-400">اسم المستخدم:</td>
                                    <td class="text-gray-800">{{$user->name}}</td>
                                </tr>
                                <!--end::Row-->

                                <!--begin::Row-->
                                <tr>
                                    <td class="text-gray-400">القسم:</td>
                                    <td class="text-gray-800">{{$user->department->name}}</td>
                                </tr>
                                <!--end::Row-->
                            </table>
                            <!--end::Details-->
                        </div>
                        <!--end::Row-->
                        <!--begin::Row-->
                        <div class="flex-equal">
                            <!--begin::Details-->
                            <table class="table fs-6 fw-bold gs-0 gy-2 gx-2 m-0">
                                <!--begin::Row-->
                                <tr>
                                    <td class="text-gray-400 min-w-175px w-175px">البريد الإلكتروني:</td>
                                    <td class="text-gray-800 min-w-200px">
                                        <a href="mailto:{{$user->email}}"
                                           class="text-gray-800 text-hover-primary">{{$user->email}}</a>
                                    </td>
                                </tr>
                                <!--end::Row-->
                                <!--begin::Row-->
                                <tr>
                                    <td class="text-gray-400 min-w-175px w-175px">رقم الهاتف:</td>
                                    <td class="text-gray-800 min-w-200px">
                                        <a href="tel:{{$user->mobile}}" class="text-gray-800 text-hover-primary">{{$user->mobile}}</a>
                                    </td>
                                </tr>
                                <!--end::Row-->
                            </table>
                            <!--end::Details-->
                        </div>
                        <!--end::Row-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Section-->
                <!--begin::Section-->

                <!--end::Section-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->

    </div>
@endsection







