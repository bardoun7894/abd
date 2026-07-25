@extends('layouts.app')

@section('title', 'الخدمات')
@section('module', 'المحوسب')
@section('sub', 'إدارة الخدمات')

@section('content')
<div class="container-xxl">

    @if (session('success'))
        <div class="mb-5 alert alert-success d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3 fs-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 alert alert-danger d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-2"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="row g-5">

        {{-- add form --}}
        <div class="col-12 col-lg-4">
            <div class="card card-flush">
                <div class="card-header">
                    <h3 class="card-title">إضافة خدمة</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('services.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="title" class="form-label required">اسم الخدمة</label>
                            <input type="text" id="title" name="title" class="form-control"
                                   value="{{ old('title') }}" required maxlength="255"
                                   placeholder="مثال: صيانة دورية">
                            @error('title')
                                <div class="mt-1 text-danger fs-7">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i> إضافة
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- list --}}
        <div class="col-12 col-lg-8">
            <div class="card card-flush">
                <div class="card-header">
                    <h3 class="card-title">
                        الخدمات
                        <span class="ms-2 badge badge-light-primary">{{ count($services) }}</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed gy-4">
                            <thead>
                                <tr class="text-gray-600 fw-bold fs-7 text-uppercase">
                                    <th style="width:60px">#</th>
                                    <th>اسم الخدمة</th>
                                    <th style="width:130px">عدد المهام</th>
                                    <th style="width:150px">تاريخ الإضافة</th>
                                    <th style="width:90px"></th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-700">
                                @forelse ($services as $i => $service)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-bold text-gray-900">{{ $service->title }}</td>
                                        <td>
                                            {{-- tasks() is a hasMany on the model; count lazily so this
                                                 view works whether or not the caller eager-loaded it. --}}
                                            <span class="badge badge-light-primary">{{ $service->tasks()->count() }}</span>
                                        </td>
                                        <td class="text-muted fs-7">
                                            {{ $service->created_at ? $service->created_at->format('Y-m-d') : '—' }}
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('services.destroy', $service->id) }}"
                                               class="btn btn-sm btn-icon btn-light-danger"
                                               title="حذف"
                                               onclick="return confirm('حذف الخدمة «{{ $service->title }}»؟');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-10 text-center text-muted">
                                            لا توجد خدمات مضافة بعد
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
