@extends('layouts.dashboard')


@section('content')
<script type="text/javascript" src="<?php echo asset('assets/emp_j.js') ?>"></script>

     <div class="kt-subheader  kt-grid__item" id="kt_subheader">
        <div class="kt-container  kt-container--fluid ">
            <div class="kt-subheader__main">
                <h3 class="kt-subheader__title">
                    <?php echo "main_title"?> </h3>
                <span class="kt-subheader__separator kt-hidden"></span>
                <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class=" flaticon2-back "></i></a>
                    <span class="kt-subheader__breadcrumbs"></span>
                    <a href="" class="kt-subheader__breadcrumbs-link">
                        <?php echo "sub_title"?> </a></i>
                    <span class="kt-subheader__breadcrumbs"></span>
                </div>
            </div>
        </div>
    </div>



    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="row">
            <div class="kt-portlet">
                <div class="kt-portlet__head">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title">
                            <?php echo "header"?> </h3>
                    </div>
                </div>
                <form autocomplete='off' class="kt-form kt-form--label-right" action="{{ route('dashboard.categories.store')}}" method="post"
                    id="save_emp" name="save_emp" enctype="multipart/form-data" accept-charset="utf-8">
@csrf

<!--<input type="hidden" name="_token" value="{{ csrf_token() }}" />-->


                    <div class="alert alert-outline-danger fade show" role="alert" id="errorBox_worker"
                        style="display: none">
                        <div class="alert-icon"><i class="flaticon-questions-circular-button"></i></div>
                        <div class="alert-text" id="displayErrors_worker"></div>
                        <div class="alert-close">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"><i class="la la-close"></i></span>
                            </button>
                        </div>
                    </div>




                    <div class="kt-portlet__body">
                        <div class="form-group row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>emp_name</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fa  fa-user kt-font-dark kt-font-bolder"></i></span></div>
                                        <input name="emp_name" type="text" class="form-control kt-font-dark kt-font-bolder"
                                            placeholder="emp_name"
                                            aria-describedby="basic-addon1">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label>job_num</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="la  la-star kt-font-dark kt-font-bolder"></i></span></div>
                                        <input name="job_num" id="job_num" type="text"
                                            class="form-control kt-font-dark kt-font-bolder rtlchange"
                                            placeholder="الرقم الوظيفي" aria-describedby="basic-addon1"
                                            data-inputmask="'alias' : 'integer'" maxlength="20" minlenght="20">
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-3">
                                <div class="form-group " onclick='show_job_cat(1)'>
                                    <label>job_title</label>
                                    <div class="input-group">
                                        <input type="hidden" id="job" name="job" class="form-control"
                                            placeholder="job_title" data-maxzpsw="0">

                                        <input type="text"  readonly id="job_desc" name="job_desc"
                                            class="form-control kt-font-dark kt-font-bolder"
                                            placeholder="job_title">

                                        <div class="input-group-append">
                                            <button  class="btn btn-primary"
                                                type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>username</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fa fa-user-cog kt-font-dark kt-font-bolder"></i></span></div>
                                        <input name="username" id="username" type="text"
                                            class="form-control kt-font-dark kt-font-bolder"
                                            placeholder="username"
                                            aria-describedby="basic-addon1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>password</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fa fa-lock kt-font-dark kt-font-bolder"></i></span></div>
                                        <input name="password" id="password" type="text"
                                            class="form-control kt-font-dark kt-font-bolder"
                                            placeholder="password"
                                            aria-describedby="basic-addon1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>mobile</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="la  la-phone kt-font-dark kt-font-bolder"></i></span></div>
                                        <input name="phone" id="phone" type="text"
                                            class="form-control kt-font-dark kt-font-bolder rtlchange"
                                            placeholder="mobile"
                                            aria-describedby="basic-addon1" data-inputmask="'alias' : 'integer'"
                                            maxlength="9" minlenght="9">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="form-group">
                                    <label>email</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span
                                                class="input-group-text kt-font-info kt-font-bold">@</span></div>
                                        <input name="email" id="email" type="text"
                                            class="form-control kt-font-dark kt-font-bolder"
                                            placeholder="email"
                                            aria-describedby="basic-addon1">
                                    </div>
                                </div>
                            </div>







                            <div class="col-lg-12 row" id='change_job' name='change_job'>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>remarks</label>
                                    <textarea class="form-control kt-font-dark kt-font-bolder"
                                        placeholder="remarks" id="remarks"
                                        name="remarks" rows="1"></textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="kt-portlet__foot">
                        <div class="kt-form__actions">
                            <div class="row">
                                <div class="col-lg-9 ml-lg-auto">
                                    <button type="submit" class="btn btn-success"><i
                                            class="fa fa-check"></i>save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>







@endsection


@push('styles')
@endpush
