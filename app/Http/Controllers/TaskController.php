<?php

namespace App\Http\Controllers;

use App\Helpers\Perm;
use App\Models\Shop;
use App\Models\Worker;
use App\Models\TheTask;
use App\Models\Service;
use App\Models\Subtask;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TaskController extends Controller
{
    public function index()
    {
        if (!Perm::get_function_access(88)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }


        $workers = Worker::where("nation_id", 191)->get();
        $shops = Shop::all();
        $services = Service::all();
        $schedules = Schedule::with(['tasks', 'creator'])->latest()->get();

        return view('tasks.index', compact('workers', 'shops', 'services', 'schedules'));
    }

    public function completeSchedule($id)
    {
        if (!Perm::get_function_access(90)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }

        $schedule = Schedule::findOrFail($id);
        if($schedule->created_by != Auth::id()) {
            return back()->with('error', 'لا يمكنك تعيين هذا الجدول كمكتمل');
        }

        $schedule->status = 'مكتمل';
        $schedule->end_date = now();

        $schedule->save();

        return back()->with('success', 'تم تعيين الجدول كمكتمل');

    }


    public function storeSchedule(Request $request)
    {
        if (!Perm::get_function_access(89)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',

            ]);

            $schedule = Schedule::create([
                'title' => $validated['title'],
                'description' => $validated['description'],

                'created_by' => Auth::id()
            ]);

            return back()->with('success', 'تم إنشاء الجدول بنجاح');

        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء الجدول: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إنشاء الجدول');
        }
    }

    public function store(Request $request)
    {
        if (!Perm::get_function_access(89)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }

        $schedule = Schedule::findOrFail($request->schedule_id);
        if($schedule->created_by != Auth::id()) {
            return back()->with('error', 'لا يمكنك إنشاء مهمة بهذا الجدول');
        }



        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'worker_id' => 'required|exists:workers,worker_id',
            'shop_id' => 'required',

            'service_id' => 'required|exists:services,id',
            'note' => 'nullable|string',
        ]);
        $request->merge(['needs' => $request->needs == '1' ? 1 : 0]);

        $task = TheTask::create($request->all());

        return back()->with('success', 'تم إنشاء المهمة بنجاح');
    }





    public function exportExcel($id)
    {
        if(!Perm::get_controll_access(14))
        abort(403);

        $schedule = Schedule::with(['tasks.worker', 'tasks.shop', 'tasks.service'])->findOrFail($id);

        // إنشاء ملف Excel جديد — نفس التنسيق الاحترافي (الزمردي) المستخدم في تصدير الفواتير
        $spreadsheet = \App\Services\ExcelReportStyler::newBook('العنوان: ' . $schedule->title);
        $sheet = $spreadsheet->getActiveSheet();

        \App\Services\ExcelReportStyler::titleRow($sheet, 'العنوان: ' . $schedule->title, 'F');
        \App\Services\ExcelReportStyler::headerRow($sheet, [
            '#', 'العامل', 'المتجر', 'الخدمة', 'الملاحظات', 'يحتاج متابعة',
        ]);

        // إضافة البيانات
        $row = 3;
        foreach ($schedule->tasks as $task) {
            $sheet->setCellValue('A' . $row, $row - 2);
            $sheet->setCellValue('B' . $row, $task->worker->worker_name);
            $sheet->setCellValue('C' . $row, $task->shop->shop_name);
            $sheet->setCellValue('D' . $row, $task->service->title);
            $sheet->setCellValue('E' . $row, $task->note);
            $sheet->setCellValue('F' . $row, $task->needs == '1' ? 'نعم' : 'لا');
            $row++;
        }

        \App\Services\ExcelReportStyler::finalize($sheet, 'F', 3, $row - 1, []);

        // إنشاء الملف وتحميله
        $writer = new Xlsx($spreadsheet);
        $fileName = 'schedule-' . $id . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function printSchedule($id)
    {
        if(!Perm::get_controll_access(14))
        abort(403);

        $schedule = Schedule::with(['tasks.worker', 'tasks.shop', 'tasks.service'])->findOrFail($id);
        return view('tasks.print', compact('schedule'));
    }

    public function destroySchedule($id)
    {
        if(!Perm::get_controll_access(14))
        abort(403);

        $schedule = Schedule::findOrFail($id);
        if($schedule->created_by != Auth::id()) {
            return back()->with('error', 'لا يمكنك حذف هذا الجدول');
        }

        $schedule->delete();

        return back()->with('success', 'تم حذف الجدول بنجاح');
    }

    public function destroyTask(TheTask $task)
    {
        if (!Perm::get_function_access(91)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }

        if($task->schedule->created_by != Auth::id()) {
            return back()->with('error', 'لا يمكنك حذف هذه المهمة');
        }

        $task->delete();

        return back()->with('success', 'تم حذف المهمة بنجاح');
    }

    public function update(Request $request, TheTask $task)
    {
        if (!Perm::get_function_access(90)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }

        $schedule = Schedule::findOrFail($task->schedule_id);
        if($schedule->created_by != Auth::id()) {
            return back()->with('error', 'لا يمكنك تحديث هذه المهمة');
        }

        $request->validate([
            'worker_id' => 'required|exists:workers,worker_id',
            'shop_id' => 'required',
            'service_id' => 'required|exists:services,id',
            'note' => 'nullable|string',
        ]);

        $request->merge(['needs' => $request->needs == '1' ? 1 : 0]);

        $task->update($request->all());

        return back()->with('success', 'تم تحديث المهمة بنجاح');
    }

    public function updateSchedule(Request $request, $id)
    {
        if (!Perm::get_function_access(90)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }

        $schedule = Schedule::findOrFail($id);
        if($schedule->created_by != Auth::id()) {
            return back()->with('error', 'لا يمكنك تحديث هذا الجدول');
        }


        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',

            ]);

            $schedule->update($validated);

            return back()->with('success', 'تم تحديث الجدول بنجاح');

        } catch (\Exception $e) {
            Log::error('خطأ في تحديث الجدول: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء تحديث الجدول'
            ], 500);
        }
    }

    public function incompleteSchedule($id)
    {
        if (!Perm::get_function_access(90)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }

        $schedule = Schedule::findOrFail($id);
        if($schedule->created_by != Auth::id()) {
            return back()->with('error', 'لا يمكنك تعيين هذا الجدول كغير مكتمل');
        }

        $schedule->status = 'نشط';
        $schedule->end_date = null;
        $schedule->save();

        return back()->with('success', 'تم تعيين الجدول كغير مكتمل');
    }
}
