<?php

namespace App\Http\Controllers;

use App\Helpers\Perm;
use App\Models\Manager;
// use Illuminate\Http\Request;
use Request;

use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{

    public function index(Request $request)
    {
        if (!Perm::get_function_access(84)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }
        $vehicles = Vehicle::where("id",">",0);
        //رقم الهوية لصاحب المركبة
        $vehicles = $vehicles->when(Request::input('owner_id'), function ($query) {
            return $query->where('owner_id', Request::input('owner_id'));
        });
        // رقم المركبة
        $vehicles = $vehicles->when(Request::input('plate_number'), function ($query) {
            return $query->where('plate_number', 'like', '%' . Request::input('plate_number') . '%');
        });

        //تاريخ الانتهاء
        $vehicles = $vehicles->when(Request::input('expiry_type'), function ($query) {
            $expiryType = Request::input('expiry_type');
            $month = Request::input('expiry_month');
            $year = Request::input('expiry_year');

            if ( $month ) {
                return $query->whereMonth($expiryType, $month);
            }
            if (  $year) {
                return $query->whereYear($expiryType, $year);
            }
            return $query->whereMonth($expiryType, $month)->whereYear($expiryType, $year);

        });

        //الحالة

        $operationCardStatus = request()->operation_card_status;
$insuranceStatus = request()->insurance_status;
$licenseStatus = request()->license_status;


// فلترة حالة كرت التشغيل
if ($operationCardStatus) {
    if ($operationCardStatus == 'valid') {
        $vehicles = $vehicles->where('operation_card_expiry', '>', Carbon::now()->addDays(10));
    } elseif ($operationCardStatus == 'expired') {
        $vehicles = $vehicles->where('operation_card_expiry', '<=', Carbon::now());
    } elseif ($operationCardStatus == 'expiring') {
        $vehicles = $vehicles->where('operation_card_expiry', '>', Carbon::now())
              ->where('operation_card_expiry', '<=', Carbon::now()->addDays(10));
    }
}

// فلترة حالة تأمين المركبة
if ($insuranceStatus) {
    if ($insuranceStatus == 'valid') {
        $vehicles = $vehicles->where('insurance_expiry', '>', Carbon::now()->addDays(10));
    } elseif ($insuranceStatus == 'expired') {
        $vehicles = $vehicles->where('insurance_expiry', '<=', Carbon::now());
    } elseif ($insuranceStatus == 'expiring') {
        $vehicles = $vehicles->where('insurance_expiry', '>', Carbon::now())
              ->where('insurance_expiry', '<=', Carbon::now()->addDays(10));
    }
}

// فلترة حالة رخصة السير
if ($licenseStatus) {
    if ($licenseStatus == 'valid') {
        $vehicles = $vehicles->where('license_expiry', '>', Carbon::now()->addDays(10));
    } elseif ($licenseStatus == 'expired') {
        $vehicles = $vehicles->where('license_expiry', '<=', Carbon::now());
    } elseif ($licenseStatus == 'expiring') {
        $vehicles = $vehicles->where('license_expiry', '>', Carbon::now())
              ->where('license_expiry', '<=', Carbon::now()->addDays(10));
    }
}
        // جلب جميع بيانات المركبات من قاعدة البيانات
        if (isset(request()->manager_id) and request()->manager_id != '')
            $vehicles = $vehicles->where("manager_id", request()->manager_id);

        $vehicles = $vehicles->get();
        $managers =  Manager::all();

        // إرسال البيانات إلى الواجهة لعرضها
        return view('vehicles.index', compact('vehicles', 'managers'));
    }


    function add($vehicle = null)
    {
        if (isset($vehicle)) {
            if (!Perm::get_function_access(86)) {
                return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
            }
        } else {
            if (!Perm::get_function_access(85)) {
                return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
            }
        }


        $vehicle = Vehicle::find($vehicle);
        $managers =  Manager::all();
        return view('vehicles.add', compact("vehicle", 'managers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HttpRequest $request)
{
    // التحقق من صحة البيانات
    $validatedData = $request->validate([
        'owner_name' => '',
        'owner_id' => '',
        'manager_id' => '',
        'vehicle_type' => '',
        'plate_number' => '',
        'serial_number' => '',
        'model' => '',
        'color' => '',
        'license_id' => '',
        'license_serial' => '',
        'license_expiry' => '',
        'custodian_name' => '',
        'custodian_id' => '',
        'custodian_phone' => '',
        'insurance_company' => '',
        'policy_number' => '',
        'insurance_issue' => '',
        'insurance_expiry' => '',
        'operation_card_number' => '',
        'operation_card_issue' => '',
        'operation_card_expiry' => '',
        // الحقول الجديدة
        'driver_card_number' => '',
        'driver_name' => '',
        'driver_id' => '',
        'driver_license_category' => '',
        'driver_license_image' => '',
        'driver_license_expiry' => '',
    ]);
    // التعامل مع رفع الملفات
    if ($request->hasFile('license_image')) {
        $validatedData['license_image'] = $request->file('license_image')->store('public/licenses');
    }
    if ($request->hasFile('insurance_image')) {
        $validatedData['insurance_image'] = $request->file('insurance_image')->store('public/insurances');
    }
    if ($request->hasFile('operation_card_image')) {
        $validatedData['operation_card_image'] = $request->file('operation_card_image')->store('public/operation_cards');
    }
    // التعامل مع رفع صورة بطاقة السائق
    if ($request->hasFile('driver_license_image')) {
        $validatedData['driver_license_image'] = $request->file('driver_license_image')->store('public/driver_licenses');
    }
    $validatedData['byUser'] = auth()->user()->id;

    // إنشاء السجل الجديد في قاعدة البيانات
    $vehicle = Vehicle::create($validatedData);

    // إرجاع استجابة ناجحة
    return back()->with('alert.success', 'تم إضافة المركبة بنجاح.');
}


    public function update(HttpRequest $request, $id)
    {
        // التحقق من صحة البيانات المرسلة

  $validatedData = $request->validate([
        'owner_name' => '',
        'owner_id' => '',
        'manager_id' => '',
        'vehicle_type' => '',
        'plate_number' => '',
        'serial_number' => '',
        'model' => '',
        'color' => '',
        'license_id' => '',
        'license_serial' => '',
        'license_expiry' => '',
        'custodian_name' => '',
        'custodian_id' => '',
        'custodian_phone' => '',
        'insurance_company' => '',
        'policy_number' => '',
        'insurance_issue' => '',
        'insurance_expiry' => '',
        'operation_card_number' => '',
        'operation_card_issue' => '',
        'operation_card_expiry' => '',
        // الحقول الجديدة
        'driver_card_number' => '',
        'driver_name' => '',
        'driver_id' => '',
        'driver_license_category' => '',
        'driver_license_image' => '',
        'driver_license_expiry' => '',
    ]);

        // البحث عن المركبة بالمعرف وتحديث البيانات
        $vehicle = Vehicle::findOrFail($id);

        // تحديث الصور إذا تم تحميلها
        if ($request->hasFile('license_image')) {
            $validatedData['license_image'] = $request->file('license_image')->store('public/licenses');
        }
        if ($request->hasFile('insurance_image')) {
            $validatedData['insurance_image'] = $request->file('insurance_image')->store('public/insurances');
        }
        if ($request->hasFile('operation_card_image')) {
            $validatedData['operation_card_image'] = $request->file('operation_card_image')->store('public/operation_cards');
        }
        // تحديث صورة بطاقة السائق إذا تم تحميلها
        if ($request->hasFile('driver_license_image')) {
            $validatedData['driver_license_image'] = $request->file('driver_license_image')->store('public/driver_licenses');
        }
        $validatedData['byUser'] = auth()->user()->id;

        $vehicle->fill($validatedData);
        $vehicle->save();

        // إعادة توجيه المستخدم مع رسالة نجاح
        return back()->with('alert.success', 'تم تحديث بيانات المركبة بنجاح.');
    }


    public function destroy($id)
    {
        if (!Perm::get_function_access(87)) {
            return back()->with('alert.success', 'ليس لديك الصلاحيات الكافية ! ');
        }
        // البحث عن المركبة بالمعرف
        $vehicle = Vehicle::findOrFail($id);

        // حذف السجل
        $vehicle->delete();

        // إعادة توجيه المستخدم مع رسالة نجاح
        return back()->with('alert.success', 'تم حذف المركبة بنجاح.');
    }
}
