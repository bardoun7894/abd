@php
    use Carbon\Carbon;

@endphp
@extends('layouts.app')

@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري ')
@section('title', 'عرض بيانات المركبات')

@section('content')
    <div class="py-5">
        <h2 class="mb-4 section-title">قائمة المركبات</h2>
        <div class="col">
            <label for="manager_id_v" class="form-label   fs-6 fw-bold text-dark mb-3">قائد المجموعة </label>
            <div>
                <select class="form-select fw-bold  " data-control="select2" id="manager_id_v"
                    onchange="window.location.replace('{{ route('vehicles.index') }}'+'?manager_id='+this.value)"
                    name="manager_id" dir="rtl">
                    <option value="">الكل</option>
                    @foreach ($managers as $x)
                        <option value="{{ $x->manager_id }} " @if (isset($_GET['manager_id']) and $_GET['manager_id'] == $x->manager_id) selected @endif>
                            {{ $x->manager_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- إضافة حقل البحث برقم الهوية --}}
        <div class="col">
            <label for="owner_id" class="form-label fs-6 fw-bold text-dark mb-3">رقم الهوية صاحب المركبة</label>
            <div>
                <input type="text" class="form-control" id="owner_id" name="owner_id" value="{{ request()->owner_id }}"
                    onchange="window.location.replace('{{ route('vehicles.index') }}'+'?owner_id='+this.value)">
            </div>
        </div>

        {{-- إضافة حقل البحث برقم المركبة --}}
        <div class="col">
            <label for="plate_number" class="form-label fs-6 fw-bold text-dark mb-3">رقم المركبة</label>
            <div>
                <input type="text" class="form-control" id="plate_number" name="plate_number"
                    value="{{ request()->plate_number }}"
                    onchange="window.location.replace('{{ route('vehicles.index') }}'+'?plate_number='+this.value)">
            </div>
        </div>

        {{-- إضافة الفلاتر لحالات كرت التشغيل والتأمين ورخصة السير --}}
        <div class="row">
            <div class="col">
                <label for="operation_card_status" class="form-label fs-6 fw-bold text-dark mb-3">حالة كرت التشغيل</label>
                <div>
                    <select class="form-select" id="operation_card_status" name="operation_card_status"
                        onchange="updateFilters2()">
                        <option value="">الكل</option>
                        <option value="valid" @if (request()->operation_card_status == 'valid') selected @endif>ساري</option>
                        <option value="expiring" @if (request()->operation_card_status == 'expiring') selected @endif>ستنتهي</option>
                        <option value="expired" @if (request()->operation_card_status == 'expired') selected @endif>منتهي</option>
                    </select>
                </div>
            </div>
            <div class="col">
                <label for="insurance_status" class="form-label fs-6 fw-bold text-dark mb-3">حالة تأمين المركبة</label>
                <div>
                    <select class="form-select" id="insurance_status" name="insurance_status" onchange="updateFilters2()">
                        <option value="">الكل</option>
                        <option value="valid" @if (request()->insurance_status == 'valid') selected @endif>ساري</option>
                        <option value="expiring" @if (request()->insurance_status == 'expiring') selected @endif>ستنتهي</option>
                        <option value="expired" @if (request()->insurance_status == 'expired') selected @endif>منتهي</option>
                    </select>
                </div>
            </div>
            <div class="col">
                <label for="license_status" class="form-label fs-6 fw-bold text-dark mb-3">حالة رخصة السير</label>
                <div>
                    <select class="form-select" id="license_status" name="license_status" onchange="updateFilters2()">
                        <option value="">الكل</option>
                        <option value="valid" @if (request()->license_status == 'valid') selected @endif>ساري</option>
                        <option value="expiring" @if (request()->license_status == 'expiring') selected @endif>ستنتهي</option>
                        <option value="expired" @if (request()->license_status == 'expired') selected @endif>منتهي</option>
                    </select>
                </div>
            </div>
        </div>


        {{-- إضافة قائمة منسدلة لاختيار نوع تاريخ الانتهاء --}}
        <div class="col">
            <label for="expiry_type" class="form-label fs-6 fw-bold text-dark mb-3">نوع تاريخ الانتهاء</label>
            <div>
                <select class="form-select" id="expiry_type" name="expiry_type" onchange="updateFilters()">
                    <option value="license_expiry" @if (request()->expiry_type == 'license_expiry') selected @endif>تاريخ انتهاء رخصة
                        السير</option>
                    <option value="insurance_expiry" @if (request()->expiry_type == 'insurance_expiry') selected @endif>تاريخ انتهاء
                        التأمين</option>
                    <option value="operation_card_expiry" @if (request()->expiry_type == 'operation_card_expiry') selected @endif>تاريخ كرت
                        التشغيل</option>
                </select>
            </div>
        </div>

        {{-- إضافة حقلي البحث بالشهر والسنة --}}
        <div class="row">
            <div class="col">
                <label for="expiry_month" class="form-label fs-6 fw-bold text-dark mb-3">الشهر</label>
                <div>
                    <select class="form-select" id="expiry_month" name="expiry_month" onchange="updateFilters()">
                        <option value="">الكل</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                @if (request()->expiry_month == str_pad($i, 2, '0', STR_PAD_LEFT)) selected @endif>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col">
                <label for="expiry_year" class="form-label fs-6 fw-bold text-dark mb-3">السنة</label>
                <div>
                    <select class="form-select" id="expiry_year" name="expiry_year" onchange="updateFilters()">
                        <option value="">الكل</option>
                        @for ($i = date('Y'); $i <= date('Y') + 5; $i++)
                            <option value="{{ $i }}" @if (request()->expiry_year == $i) selected @endif>
                                {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        <script>
            function updateFilters() {
                var expiryType = document.getElementById('expiry_type').value;
                var month = document.getElementById('expiry_month').value;
                var year = document.getElementById('expiry_year').value;
                var queryParams = '?';

                if (expiryType) queryParams += 'expiry_type=' + expiryType + '&';
                if (month) queryParams += 'expiry_month=' + month + '&';
                if (year) queryParams += 'expiry_year=' + year;

                window.location.replace('{{ route('vehicles.index') }}' + queryParams);
            }
        </script>

        <script>
            function updateFilters2() {
                var operationCardStatus = document.getElementById('operation_card_status').value;
                var insuranceStatus = document.getElementById('insurance_status').value;
                var licenseStatus = document.getElementById('license_status').value;
                var queryParams = '?';

                if (operationCardStatus) queryParams += 'operation_card_status=' + operationCardStatus + '&';
                if (insuranceStatus) queryParams += 'insurance_status=' + insuranceStatus + '&';
                if (licenseStatus) queryParams += 'license_status=' + licenseStatus;

                window.location.replace('{{ route('vehicles.index') }}' + queryParams);
            }
        </script>

        <br><br><br>

        {{-- <h3><a class="btn btn-primary" href="{{route('insert_vehicle')}}">إضافة مركبة </a></h3> --}}
        <table class="table" id="vehiclesTable" class="table table-row-bordered gy-5">
            <thead>
                <tr class="fw-semibold fs-6 text-muted">
                    <th scope="col">اسم مالك المركبة</th>
                    <th scope="col">تم الإضافة من   </th>
                    <th scope="col">قائد المجموعة</th>
                    <th scope="col">رقم الهوية لصاحب المركبة</th>
                    <th scope="col">نوع المركبة</th>
                    <th scope="col">رقم لوحة</th>
                    <th scope="col">الرقم التسلسلي</th>
                    <th scope="col">الموديل</th>
                    <th scope="col">اللون</th>
                    <th scope="col">صورة رخصة السير</th>
                    <th scope="col">تاريخ انتهاء رخصة السير</th>
                    <th scope="col">اسم الموظف</th>
                    <th scope="col">رقم الجوال للموظف</th>
                    <th scope="col">اسم شركة التأمين</th>
                    <th scope="col">رقم بوليصة التأمين</th>
                    <th scope="col">تاريخ إصدار التأمين</th>
                    <th scope="col">تاريخ انتهاء التأمين</th>
                    <th scope="col">صورة وثيقة التأمين</th>
                    <th scope="col">رقم الوثيقة كرت التشغيل</th>
                    <th scope="col">تاريخ الإصدار كرت التشغيل</th>
                    <th scope="col">تاريخ الانتهاء كرت التشغيل</th>
                    <th scope="col">صورة كرت التشغيل</th>
                    <th scope="col">حالة رخصة السير</th>
                    <th scope="col">حالة تأمين المركبة</th>
                    <th scope="col">حالة كرت التشغيل</th>
                    <th scope="col">حالة بطاقة السائق</th>
                    <!-- الحقول الجديدة للسائق -->
                    <th scope="col">رقم بطاقة السائق</th>
                    <th scope="col">اسم السائق</th>
                    <th scope="col">رقم هوية السائق</th>
                    <th scope="col">تصنيف بطاقة السائق</th>
                    <th scope="col">صورة بطاقة السائق</th>
                    <th scope="col">تاريخ انتهاء بطاقة السائق</th>
                    <th scope="col">الإجراءات</th>
                </tr>

            </thead>
            <tbody>
                @if (count($vehicles))
                    @foreach ($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->owner_name }}</td>
                            <td>{{ $vehicle->user->name ?? '' }}</td>
                            <td>{{ $vehicle->manager->manager_name ?? ''}}</td>
                            <td>{{ $vehicle->owner_id }}</td>
                            <td>{{ $vehicle->vehicle_type }}</td>
                            <td>{{ $vehicle->plate_number }}</td>
                            <td>{{ $vehicle->serial_number }}</td>
                            <td>{{ $vehicle->model }}</td>
                            <td>{{ $vehicle->color }}</td>
                            <td><a href="{{ asset('storage/' . str_replace('public', '', $vehicle->license_image)) }}"
                                    target="_blank">رخصة السير</a></td>
                            <td>{{ $vehicle->license_expiry }}</td>
                            <td>{{ $vehicle->custodian_name }}</td>
                            <td>{{ $vehicle->custodian_phone }}</td>
                            <td>{{ $vehicle->insurance_company }}</td>
                            <td>{{ $vehicle->policy_number }}</td>
                            <td>{{ $vehicle->insurance_issue }}</td>
                            <td>{{ $vehicle->insurance_expiry }}</td>
                            <td><a href="{{ asset('storage/' . str_replace('public', '', $vehicle->insurance_image)) }}"
                                    target="_blank">وثيقة التأمين</a></td>
                            <td>{{ $vehicle->operation_card_number }}</td>
                            <td>{{ $vehicle->operation_card_issue }}</td>
                            <td>{{ $vehicle->operation_card_expiry }}</td>
                            <td><a href="{{ asset('storage/' . str_replace('public', '', $vehicle->operation_card_image)) }}"
                                    target="_blank">كرت التشغيل</a></td>
                            <td>{!! (Carbon::parse($vehicle->license_expiry)->diffInDays(\Carbon\Carbon::now()) <= 10 and
                            Carbon::parse($vehicle->license_expiry) > \Carbon\Carbon::now())
                                ? '<span class="btn btn-warning">ستنتهي</span>'
                                : (Carbon::parse($vehicle->license_expiry) < \Carbon\Carbon::now()
                                    ? '<span class="btn btn-danger">منتهية</span>'
                                    : '<span class="btn btn-success">سارية</span>') !!}</td>
                            <td>{!! (Carbon::parse($vehicle->insurance_expiry)->diffInDays(\Carbon\Carbon::now()) <= 10 and
                            Carbon::parse($vehicle->insurance_expiry) > \Carbon\Carbon::now())
                                ? '<span class="btn btn-warning">ستنتهي</span>'
                                : (Carbon::parse($vehicle->insurance_expiry) < \Carbon\Carbon::now()
                                    ? '<span class="btn btn-danger">منتهية</span>'
                                    : '<span class="btn btn-success">سارية</span>') !!}</td>
                            <td>{!! (Carbon::parse($vehicle->operation_card_expiry)->diffInDays(\Carbon\Carbon::now()) <= 10 and
                            Carbon::parse($vehicle->operation_card_expiry) > \Carbon\Carbon::now())
                                ? '<span class="btn btn-warning">ستنتهي</span>'
                                : (Carbon::parse($vehicle->operation_card_expiry) < \Carbon\Carbon::now()
                                    ? '<span class="btn btn-danger">منتهية</span>'
                                    : '<span class="btn btn-success">سارية</span>') !!}</td>

                            <td>{!! (Carbon::parse($vehicle->driver_license_expiry)->diffInDays(\Carbon\Carbon::now()) <= 10 and
                            Carbon::parse($vehicle->driver_license_expiry) > \Carbon\Carbon::now())
                                ? '<span class="btn btn-warning">ستنتهي</span>'
                                : (Carbon::parse($vehicle->driver_license_expiry) < \Carbon\Carbon::now()
                                    ? '<span class="btn btn-danger">منتهية</span>'
                                    : '<span class="btn btn-success">سارية</span>') !!}</td>




                            <td>



                            <td>{{ $vehicle->driver_card_number }}</td>
                            <td>{{ $vehicle->driver_name }}</td>
                            <td>{{ $vehicle->driver_id }}</td>
                            <td>{{ $vehicle->driver_license_category }}</td>
                            <td>
                                @if (isset($vehicle->driver_license_image))
                                    <a href="{{ asset('storage/' . str_replace('public', '', $vehicle->driver_license_image)) }}"
                                        target="_blank">بطاقة السائق</a>
                                @endif
                            </td>
                            <td>{{ $vehicle->driver_license_expiry }}</td>
                            <td>{!! (Carbon::parse($vehicle->driver_license_expiry)->diffInDays(\Carbon\Carbon::now()) <= 10 and
                            Carbon::parse($vehicle->driver_license_expiry) > \Carbon\Carbon::now())
                                ? '<span class="btn btn-warning">ستنتهي</span>'
                                : (Carbon::parse($vehicle->driver_license_expiry) < \Carbon\Carbon::now()
                                    ? '<span class="btn btn-danger">منتهية</span>'
                                    : '<span class="btn btn-success">سارية</span>') !!}</td>




                            <td>
                                @if (auth()->user()->isAdmin || $vehicle->byUser == auth()->user()->id  || $vehicle->id <21)
                                @if (Perm::get_function_access(86))
                                    <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if (Perm::get_function_access(87))
                                    <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="get"
                                        style="display: inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('هل أنت متأكد من أنك تريد حذف هذه المركبة؟')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="24">
                            <center>
                                <h2>لا يوجد بيانات لعرضها !</h2>

                            </center>

                        </td>

                    </tr>


                @endif
            </tbody>
        </table>
    </div>

    <!-- إضافة Bootstrap JS و Popper.js و jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- إضافة مكتبة DataTables -->
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
 --}}
@endsection
