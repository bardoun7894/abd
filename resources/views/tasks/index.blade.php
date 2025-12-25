@extends('layouts.app')


@section('css')

<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-selection {
        height: 38px !important;
        border: 1px solid #dce7f1 !important;
    }

    .select2-selection__rendered {
        line-height: 36px !important;
        padding-right: 12px !important;
    }

    .select2-selection__arrow {
        height: 36px !important;
    }

    .modal-content {
        padding: 20px;
    }

    .schedule-card {
        margin-bottom: 20px;
        border: 2px solid #e4e6ef;
        border-radius: 8px;
        padding: 20px;
        background-color: #f5f8fa;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    .schedule-card:nth-child(odd) {
        background-color: #eef3f7;
    }

    .schedule-card .card-header {
        background-color: transparent;
        border-bottom: 1px solid #e4e6ef;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }

    .schedule-card .card-body {
        padding: 15px 0;
    }

    .task-list { margin-top: 15px; }
</style>
@endsection

@section('module', 'نظام الحوسبة')
@section('sub', 'الاداري ')
@section('title', ' إدارة المهام ')

@section('content')

<!-- Start Generation Here -->
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<!-- End Generation Here -->

<!-- Start Generation Here -->
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<!-- End Generation Here -->

<script>
$(document).ready(function() {

    // دوال إدارة المهام
    window.addTask = function(scheduleId) {
        // تحديث قيمة schedule_id في النموذج
        $('#addTaskForm').find('input[name="schedule_id"]').val(scheduleId);

        // إعادة تعيين النموذج
        $('#addTaskForm')[0].reset();

        // تحديث عنوان النافذة المنبثقة
        $('#addTaskModal .modal-title').text('إضافة مهمة جديدة');

        // فتح النافذة المنبثقة
        $('#addTaskModal').modal('show');
    }
});
</script>


<div class="page-heading">
    <div class="d-flex justify-content-between align-items-center">
        <h3>الجداول والمهام</h3>
        <div>
            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                <i class="bi bi-plus"></i> إضافة جدول جديد
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="bi bi-plus"></i> إضافة خدمة
            </button>
        </div>
    </div>
</div>

<div class="page-content">
    <br><br>
    <!-- قائمة الجداول -->
    @foreach($schedules as $schedule)
    <div  class="card schedule-card" style="padding:20px;background-color: #65c1ff10; border: 2px solid #65c1ffae; border-radius: 10px; margin-bottom: 20px;">

        <div class="card-header">
            <h4>{{ $schedule->title }}</h4>


            <div class="d-flex justify-content-between align-items-center">
                <br><br>
                <div style="margin-top: 10px;display: flex;gap: 10px;"  class="btn-group">

                    <button class="btn btn-sm btn-warning" onclick="(function(){
                        const form = $('#editScheduleForm');
                        form.attr('action', '/schedules/{{ $schedule->id }}');
                        form.find('input[name=\'title\']').val('{{ $schedule->title }}');
                        form.find('textarea[name=\'description\']').val(`{{ $schedule->description }}`);
                        form.find('input[name=\'start_date\']').val('{{ $schedule->start_date->format('Y-m-d') }}');
                        form.find('input[name=\'end_date\']').val('{{ $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '' }}');
                        $('#editScheduleModal').modal('show');
                    })()">
                        <i class="bi bi-pencil"></i>
                    </button>
                    {{-- <button class="btn btn-sm btn-success" onclick="window.location.href = `/schedules/{{{ $schedule->id }}}/pdf`">
                        <i class="bi bi-file-pdf"></i>
                    </button> --}}
                    <button class="btn btn-sm btn-success" onclick="window.location.href = `/schedules/{{{ $schedule->id }}}/excel`">
                        <i class="bi bi-file-excel"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="window.open(`/schedules/{{{ $schedule->id }}}/print`, '_blank');">
                        <i class="bi bi-printer"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="(function(){ if(confirm('هل أنت متأكد من حذف هذا الجدول وجميع مهامه؟')) { $.ajax({ url: '/schedules/{{ $schedule->id }}', method: 'GET', data: { _token: '{{ csrf_token() }}' }, success: function() { location.reload(); }, error: function() { alert('حدث خطأ أثناء الحذف'); } }); } })()">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <span class="badge bg-{{ $schedule->status == 'نشط' ? 'success' : ($schedule->status == 'مكتمل' ? 'info' : 'warning') }}">
                    {{ $schedule->status }}
                </span>
                <span class="ms-2">من: {{ $schedule->start_date->format('Y-m-d') }}</span>
                @if($schedule->end_date)
                <span class="ms-2">إلى: {{ $schedule->end_date->format('Y-m-d') }}</span>
                @endif
                @if($schedule->status == 'نشط')
                <span class="ms-2">
                    <a href="{{ route('schedules.complete', $schedule->id) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-check"></i> تعيين كمكتمل
                    </a>
                @else
                <span class="ms-2">
                    <a href="{{ route('schedules.incomplete', $schedule->id) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-check"></i> تعيين كغير مكتمل
                    </a>
                </span>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($schedule->description)
            <p>{{ $schedule->description }}</p>
            @endif

            <div class="task-list">
                <div class="mb-3 d-flex justify-content-between">
                    <h5>المهام</h5>
                    <button class="btn btn-sm btn-primary" onclick="$('#addTaskForm').find('input[name=\'schedule_id\']').val({{ $schedule->id }}); $('#addTaskForm')[0].reset(); $('#addTaskModal .modal-title').text('إضافة مهمة جديدة'); $('#addTaskModal').modal('show');">
                        <i class="bi bi-plus"></i> إضافة مهمة
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>العامل</th>
                                <th>رقم الاشتراك</th>
                                <th>المنشأة</th>
                                <th>رقم المنشأة</th>
                                <th>الخدمة</th>
                                <th>الملاحظات</th>
                                <th>يحتاج متابعة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedule->tasks as $task)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $task->worker->worker_name }} </td>
                                <td>{{ $task->worker->registration_number }}</td>
                                <td>{{ $task->shop->shop_name  }}</td>
                                <td>{{ $task->shop->establishment_number }}</td>
                                <td>{{ $task->service->title ??""}}</td>
                                <td>{{ Str::limit($task->note, 50) }}</td>
                                <td>
                                    <span class="badge bg-{{ $task->needs == '1' ? 'success' : 'warning' }}">
                                        {{ $task->needs == '1' ? 'نعم' : 'لا' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">

                                        <button class="btn btn-sm btn-warning" onclick="(function(){
                                            const form = $('#editTaskForm');
                                            form.attr('action', '/tasks/{{ $task->id }}');
                                            form.find('select[name=\'worker_id\']').val('{{ $task->worker->worker_id }}').trigger('change');
                                            form.find('select[name=\'shop_id\']').val('{{ $task->shop->shop_id }}').trigger('change');
                                            form.find('select[name=\'service_id\']').val('{{ $task->service->id ?? '' }}').trigger('change');
                                            form.find('textarea[name=\'note\']').val(`{{ $task->note }}`);
                                            form.find('input[name=\'needs\']').prop('checked', {{ $task->needs ? 'true' : 'false' }});
                                            $('#editTaskModal').modal('show');
                                        })()">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="{{ route('tasks.destroy', $task->id) }}" onclick="return confirm('هل أنت متأكد من حذف هذه المهمة؟')" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <center>
        <small>  تمت الاضافة من قبل  {{$schedule->creator->name}}</small>

        </center>
    </div>
    @endforeach
</div>

<!-- Modal إضافة جدول -->
<div class="modal fade" id="scheduleModal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة جدول جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm" method="POST" action="{{ route('schedules.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">عنوان الجدول</label>
                        <input type="text" class="form-control" name="title" required
                               data-validation-required-message="عنوان الجدول مطلوب">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    {{-- <div class="mb-3">
                        <label class="form-label required">تاريخ البداية</label>
                        <input type="date" class="form-control" name="start_date" required
                               data-validation-required-message="تاريخ البداية مطلوب">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تاريخ النهاية</label>
                        <input type="date" class="form-control" name="end_date">
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal إضافة مهمة -->
<div class="modal fade" id="addTaskModal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة مهمة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTaskForm" method="POST" action="{{ route('tasks.store') }}">
                @csrf
                <input type="hidden" name="schedule_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">العامل</label>
                        <select class="form-select Select2" name="worker_id" required >
                            <option value="">اختر العامل</option>
                            @foreach($workers as $worker)
                            <option value="{{ $worker->worker_id }}">{{ $worker->worker_name }} - {{ $worker->registration_number }} - {{$worker->ssn}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">المنشأة</label>
                        <select class="form-select Select2" name="shop_id"  required >
                            <option value="">اختر المنشأة</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->shop_id }}">{{ $shop->shop_name }} - {{ $shop->establishment_number }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="mb-3">
                        <label class="form-label required">الخدمة</label>
                        <select class="form-select Select2" name="service_id" required >
                            <option value="">اختر الخدمة</option>
                            @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control" name="note" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="needs" value="1" id="needsCheck">
                            <label class="form-check-label" for="needsCheck">
                                يحتاج متابعة
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- نضيف جدول عرض الخدمات في Modal إضافة خدمة -->
<div class="modal fade" id="addServiceModal" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إدارة الخدمات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- نموذج إضافة خدمة -->
                <form id="addServiceForm" class="mb-4" method="POST" action="{{ route('services.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">عنوان الخدمة</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">إضافة</button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- جدول الخدمات -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>عنوان الخدمة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $service)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $service->title }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-warning" onclick="(function(){
                                            const title = prompt('تعديل عنوان الخدمة:', '{{ $service->title }}');
                                            if (title) {
                                                $.ajax({
                                                    url: '{{ route('services.update', $service->id) }}',
                                                    method: 'GET',
                                                    data: {
                                                        _token: '{{ csrf_token() }}',
                                                        title: title
                                                    },
                                                    success: function() {
                                                        location.reload();
                                                    },
                                                    error: function() {
                                                        alert('حدث خطأ أثناء التعديل');
                                                    }
                                                });
                                            }
                                        })()">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="{{ route('services.destroy', $service->id) }}"
                                           onclick="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')"
                                           class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- نضيف هذا النموذج في نهاية الملف قبل إغلاق body -->
<div class="modal fade" id="editTaskModal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل المهمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTaskForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">العامل</label>
                        <select class="form-select Select2" name="worker_id" required >
                            <option value="">اختر العامل</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->worker_id }}">{{ $worker->worker_name }} - {{ $worker->registration_number }} - {{$worker->ssn}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">المنشأة</label>
                        <select class="form-select Select2" name="shop_id" required >
                            <option value="">اختر المنشأة</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->shop_id }}">{{ $shop->shop_name }} - {{ $shop->establishment_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">الخدمة</label>
                        <select class="form-select Select2" name="service_id" required>
                            <option value="">اختر الخدمة</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control" name="note" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="needs" value="1" id="editNeeds">
                            <label class="form-check-label" for="editNeeds">
                                بحاجة لمتابعة
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- نافذة تعديل الجدول -->
<div class="modal fade" id="editScheduleModal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل الجدول</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editScheduleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">عنوان الجدول</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    {{-- <div class="mb-3">
                        <label class="form-label required">تاريخ البداية</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تاريخ النهاية</label>
                        <input type="date" class="form-control" name="end_date">
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// تعريف الدوال في النطاق العام
window.viewTask = function(id) {
    // يمكن إضافة كود لعرض تفاصيل المهمة
};

function editTask(id, workerId, shopId, serviceId, note, needs) {

}

window.deleteTask = function(id) {
    if(confirm('هل أنت متأكد من حذف هذه المهمة؟')) {
        // يمكن إضافة كود لحذف المهمة
    }
};

// باقي الدوال...
window.viewSchedule = function(id) {
    window.location.href = `/schedules/${id}`;
};

window.exportPDF = function(id) {
};

window.exportExcel = function(id) {

};

window.printSchedule = function(id) {
};

window.deleteSchedule = function(id) {
    if(confirm('هل أنت متأكد من حذف هذا الجدول وجميع مهامه؟')) {
        // يمكن إضافة كود لحذف الجدول
    }
};

// تهيئة النموذج عند تحميل الصفحة
$(document).ready(function() {

    $('#editTaskForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#editTaskModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = '';
                for (let error in errors) {
                    errorMessage += errors[error][0] + '\n';
                }
                alert(errorMessage || 'حدث خطأ، يرجى المحاولة مرة أخرى');
            }
        });
    });

    $('#editScheduleForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#editScheduleModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = '';
                for (let error in errors) {
                    errorMessage += errors[error][0] + '\n';
                }
                alert(errorMessage || 'حدث خطأ، يرجى المحاولة مرة أخرى');
            }
        });
    });
});
</script>

<style>
    .modal-content{
      padding: 20px;
      border-radius: 10px;
    }


</style>
@endsection
