<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255|unique:services,title'
            ]);

            $service = Service::create([
                'title' => $validated['title']

            ]);

            return back()->with('success', 'تم إضافة الخدمة بنجاح');

        } catch (\Exception $e) {
            Log::error('خطأ في إضافة الخدمة: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إضافة الخدمة');
        }
        }
    

    public function index()
    {
        $services = Service::all();
        return view('services.index', compact('services'));
    }

    public function show(Service $service)
    {
        return response()->json($service);
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'title' => 'required|string|max:255'
        ]);

        $service->update($request->all());
        return back()->with('success', 'تم تحديث الخدمة بنجاح');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('success', 'تم حذف الخدمة بنجاح');
    }
}
