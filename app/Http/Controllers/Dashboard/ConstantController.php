<?php
namespace App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Constant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;
class ConstantController extends Controller
{
    use ApimtitTrait;
    public function __construct()
    {
        $this->middleware('ishaveaccess:11');
    }
    
    



    public function city()
    {
        if (Perm::get_function_access(83)) {
        $page_title = 'ادخال ثوابت المدن';
        $const = array("page_title");
        return view('dashboard.constant.city.city', compact($const));
        }
    }


    public function tblcity(Request $request)
    {
        if ($request->ajax()) {
            $city = DB::table('city')->get();
            return view('dashboard.constant.city.tbl_city', compact('city'));
        } else {
            return "Request Not Ajax Type";
        }
    }
    function delcity(Request $request)
    {
        $id = $request->id;
        try {
            $delete = DB::delete('delete from city where city_id = ?', [$id]);
            if ($delete) {
                $result['status'] = true;
                $result['message'] = 'تم';
            } else {
                $message = 'لا يمكن الحذف';
                $result['status'] = false;
                $result['message'] = $message;
            }
        } catch (\Exception $exception) {
            $message = 'لا يمكن الحذف لانه يوجد بيانات معتمدة';
            $result['status'] = false;
            $result['message'] = $message;
        }
        echo json_encode($result);
    }


    public function updcity(Request $request)
    {
        $id = $request->id;
        $city = DB::table('city')->where('city_id', $id)->first();
        $sub_add_constant = "1";
        $page_title = 'تعديل بيانات ';
        $const = array("city", "page_title");
        return view('dashboard.constant.city.upd_city', compact($const));
    }


    public function storecity(Request $request)
    {
        $city_name_old = $request->old('city_name');
        $attributeNames = array(
            'city_name' => 'المدن',
        );
        $validator = Validator::make($request->all(), [
            'city_name' => ['required', Rule::unique('city', 'city_name')],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tblcity');

        } else {
            $ERROR_FLAG = 0;
            $result2 = DB::table('city')->insertGetId([
                'city_name' => $request->city_name,
            ]);
            if ($result2 != '') {
                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';
                $result['url'] = route('dashboard.constant.tblcity');
            } else {
                $message = 'لا يمكن الحفظ';
                $result['status'] = false;
                $result['message_out'] = $message;
                $result['url'] = route('dashboard.constant.tblcity');

            }
        }
        return response()->json($result);
    }


    public function updstorecity(Request $request)
    {
        $id = $request->city_id_db;
        $attributeNames = array(
            'city_name_u' => 'المدن',
        );
        $validator = Validator::make($request->all(), [
            'city_name_u' => ['required', Rule::unique('city', 'city_name')->ignore($id, 'city_id')],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tblcity');

        } else {
            $ERROR_FLAG = 0;
            $result2 = DB::table('city')
                ->where('city_id', $id)
                ->update([
                    'city_name' => $request->city_name_u,
                ]);
            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';
            $result['url'] = route('dashboard.constant.tblcity');

        }
        return response()->json($result);
    }




    public function violation()
    {
        if (Perm::get_function_access(81)) {
        $page_title = 'ادخال جهات المخالفة';
        $const = array("page_title");
        return view('dashboard.constant.violation.violation', compact($const));
        }
    }
    public function tblviolation(Request $request)
    {
        if ($request->ajax()) {
            $violation = DB::table('violation_side')->get();
            return view('dashboard.constant.violation.tblviolation', compact('violation'));
        } else {
            return "Request Not Ajax Type";
        }
    }
    function delviolation(Request $request)
    {
        $id = $request->id;
        try {
            $delete = DB::delete('delete from violation_side where violation_side_id = ?', [$id]);
            if ($delete) {
                $result['status'] = true;
                $result['message'] = 'تم';
            } else {
                $message = 'لا يمكن الحذف';
                $result['status'] = false;
                $result['message'] = $message;
            }
        } catch (\Exception $exception) {
            $message = 'لا يمكن الحذف لانه يوجد بيانات معتمدة';
            $result['status'] = false;
            $result['message'] = $message;
        }
        echo json_encode($result);
    }
    public function updviolation(Request $request)
    {
        $id = $request->id;
        $violation = DB::table('violation_side')->where('violation_side_id', $id)->first();
        $sub_add_constant = "1";
        $page_title = 'تعديل بيانات ';
        $const = array("violation", "page_title");
        return view('dashboard.constant.violation.upd_violation', compact($const));
    }
    public function storeviolation(Request $request)
    {
        $violation_side_name_old = $request->old('violation_side_name');
        $attributeNames = array(
            'violation_side_name' => 'جهة المخالفة',
        );
        $validator = Validator::make($request->all(), [
            'violation_side_name' => ['required', Rule::unique('violation_side', 'violation_side_name')],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tblviolation');

        } else {
            $ERROR_FLAG = 0;

            $result2 = DB::table('violation_side')->insertGetId([
                'violation_side_name' => $request->violation_side_name,
            ]);

            if ($result2 != '') {
                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';
                $result['url'] = route('dashboard.constant.tblviolation');
            } else {

                $message = 'لا يمكن الحفظ';
                $result['status'] = false;
                $result['message_out'] = $message;
                $result['url'] = route('dashboard.constant.tblviolation');

            }
        }
        return response()->json($result);
    }
    public function updstoreviolation(Request $request)
    {
        $id = $request->violation_side_id_db;
        $attributeNames = array(
            'violation_side_name_u' => 'جهة المخالفة',
        );
        $validator = Validator::make($request->all(), [
            'violation_side_name_u' => ['required', Rule::unique('violation_side', 'violation_side_name')->ignore($id, 'violation_side_id')],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tblviolation');

        } else {
            $ERROR_FLAG = 0;
            $result2 = DB::table('violation_side')
                ->where('violation_side_id', $id)
                ->update([
                    'violation_side_name' => $request->violation_side_name_u,
                ]);
            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';
            $result['url'] = route('dashboard.constant.tblviolation');

        }
        return response()->json($result);

    }
    public function workplace()
    {
        if (Perm::get_function_access(69)) {
        $page_title = 'ادخال ثوابت اماكن العمل';
        $const = array("page_title");
        return view('dashboard.constant.work_place.workplace', compact($const));
        }
    }

    public function tblworkplace(Request $request)
    {
        if ($request->ajax()) {
            $work_place = DB::table('work_place')->get();
            return view('dashboard.constant.work_place.tbl_workplace', compact('work_place'));
        } else {
            return "Request Not Ajax Type";
        }
    }


    function delworkplace(Request $request)
    {
        $id = $request->id;
        try {
            $delete = DB::delete('delete from work_place where work_place_id = ?', [$id]);
            if ($delete) {
                $result['status'] = true;
                $result['message'] = 'تم';
            } else {
                $message = 'لا يمكن الحذف';
                $result['status'] = false;
                $result['message'] = $message;
            }
        } catch (\Exception $exception) {
            $message = 'لا يمكن الحذف لانه يوجد بيانات معتمدة';
            $result['status'] = false;
            $result['message'] = $message;
        }
        echo json_encode($result);
    }


    public function updworkplace(Request $request)
    {
        $id = $request->id;
        $workplace = DB::table('work_place')->where('work_place_id', $id)->first();
        $sub_add_constant = "1";
        $page_title = 'تعديل بيانات ';
        $const = array("workplace", "page_title");
        return view('dashboard.constant.work_place.upd_workplace', compact($const));
    }


    public function storeworkplace(Request $request)
    {
        $work_place_name_old = $request->old('work_place_name');
        $attributeNames = array(
            'work_place_name' => 'مكان العمل',
        );
        $validator = Validator::make($request->all(), [
            'work_place_name' => ['required', Rule::unique('work_place', 'work_place_name')],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tblworkplace');

        } else {
            $ERROR_FLAG = 0;

            $result2 = DB::table('work_place')->insertGetId([
                'work_place_name' => $request->work_place_name,
            ]);

            if ($result2 != '') {
                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';
                $result['url'] = route('dashboard.constant.tblworkplace');
            } else {
                $message = 'لا يمكن الحفظ';
                $result['status'] = false;
                $result['message_out'] = $message;
                $result['url'] = route('dashboard.constant.tblworkplace');

            }
        }
        return response()->json($result);
    }


    public function updstoreworkplace(Request $request)
    {
        $id = $request->work_place_id_db;
        $attributeNames = array(
            'work_place_name_u' => 'مكان العمل',
        );
        $validator = Validator::make($request->all(), [
            'work_place_name_u' => ['required', Rule::unique('work_place', 'work_place_name')->ignore($id, 'work_place_id')],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tblworkplace');

        } else {
            $ERROR_FLAG = 0;
            $result2 = DB::table('work_place')
                ->where('work_place_id', $id)
                ->update([
                    'work_place_name' => $request->work_place_name_u,
                ]);
            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';
            $result['url'] = route('dashboard.constant.tblworkplace');

        }
        return response()->json($result);

    }


    public function expensecategoty()
    {
        if (Perm::get_function_access(82)) {
        $page_title = 'ادخال ثوابت التصنيف';
        $const = array("page_title");
        return view('dashboard.constant.expense_categoty.expensecategoty', compact($const));
        }
    }


    public function tblexpensecategoty(Request $request)
    {
        if ($request->ajax()) {
            $expense_categoty = DB::table('expense_categoty')->get();
            return view('dashboard.constant.expense_categoty.tbl_expensecategoty', compact('expense_categoty'));
        } else {
            return "Request Not Ajax Type";
        }
    }


    function delexpensecategoty(Request $request)
    {
        $id = $request->id;
        try {
            $delete = DB::delete('delete from  expense_categoty where  expense_categoty_id = ?', [$id]);
            if ($delete) {
                $result['status'] = true;
                $result['message'] = 'تم';
            } else {
                $message = 'لا يمكن الحذف';
                $result['status'] = false;
                $result['message'] = $message;
            }
        } catch (\Exception $exception) {
            $message = 'لا يمكن الحذف لانه يوجد بيانات معتمدة';
            $result['status'] = false;
            $result['message'] = $message;
        }
        echo json_encode($result);
    }


    public function updexpensecategoty(Request $request)
    {
        $id = $request->id;
        $expensecategoty = DB::table('expense_categoty')->where('expense_categoty_id', $id)->first();
        $sub_add_constant = "1";
        $page_title = 'تعديل بيانات ';
        $const = array("expensecategoty", "page_title");
        return view('dashboard.constant.expense_categoty.upd_expensecategoty', compact($const));
    }


    public function storeexpensecategoty(Request $request)
    {
        $expense_categoty_name_old = $request->old('expense_categoty_name');
        $attributeNames = array(
            'expense_categoty_name' => 'التصنيف',
        );
        $validator = Validator::make($request->all(), [
            'expense_categoty_name' => ['required', Rule::unique('expense_categoty', 'expense_categoty_name')],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tblexpensecategoty');

        } else {
            $ERROR_FLAG = 0;

            $result2 = DB::table('expense_categoty')->insertGetId([
                'expense_categoty_name' => $request->expense_categoty_name,
            ]);

            if ($result2 != '') {
                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';
                $result['url'] = route('dashboard.constant.tblexpensecategoty');
            } else {
                $message = 'لا يمكن الحفظ';
                $result['status'] = false;
                $result['message_out'] = $message;
                $result['url'] = route('dashboard.constant.tblexpensecategoty');
            }
        }
        return response()->json($result);
    }


    public function updstoreexpensecategoty(Request $request)
    {
        $id = $request->expense_categoty_id_db;
        $attributeNames = array(
            'expense_categoty_name_u' => 'التصنيف',
        );
        $validator = Validator::make($request->all(), [
            'expense_categoty_name_u' => ['required', Rule::unique('expense_categoty', 'expense_categoty_name')->ignore($id, 'expense_categoty_id')],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tblexpensecategoty');

        } else {
            $ERROR_FLAG = 0;
            $result2 = DB::table('expense_categoty')
                ->where('expense_categoty_id', $id)
                ->update([
                    'expense_categoty_name' => $request->expense_categoty_name_u,
                ]);
            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';
            $result['url'] = route('dashboard.constant.tblexpensecategoty');

        }
        return response()->json($result);

    }


    public function job()
    {
        if (Perm::get_function_access(80)) {
        $page_title = 'ادخال ثوابت المهن';
        $const = array("page_title");
        return view('dashboard.constant.job.job', compact($const));
        }
    }


    public function tbljob(Request $request)
    {
        if ($request->ajax()) {
            $job = DB::table('job')->get();
            return view('dashboard.constant.job.tbl_job', compact('job'));
        } else {
            return "Request Not Ajax Type";
        }
    }
    function deljob(Request $request)
    {
        $id = $request->id;
        try {
            $delete = DB::delete('delete from job where job_id = ?', [$id]);
            if ($delete) {
                $result['status'] = true;
                $result['message'] = 'تم';
            } else {
                $message = 'لا يمكن الحذف';
                $result['status'] = false;
                $result['message'] = $message;
            }
        } catch (\Exception $exception) {
            $message = 'لا يمكن الحذف لانه يوجد بيانات معتمدة';
            $result['status'] = false;
            $result['message'] = $message;
        }
        echo json_encode($result);
    }


    public function updjob(Request $request)
    {
        $id = $request->id;
        $job = DB::table('job')->where('job_id', $id)->first();
        $sub_add_constant = "1";
        $page_title = 'تعديل بيانات ';
        $const = array("job", "page_title");
        return view('dashboard.constant.job.upd_job', compact($const));
    }


    public function storejob(Request $request)
    {
        $job_name_old = $request->old('job_name');
        $attributeNames = array(
            'job_name' => 'المهنة',
        );
        $validator = Validator::make($request->all(), [
            'job_name' => ['required', Rule::unique('job', 'job_name')],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tbljob');

        } else {
            $ERROR_FLAG = 0;
            $result2 = DB::table('job')->insertGetId([
                'job_name' => $request->job_name,
            ]);
            if ($result2 != '') {
                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';
                $result['url'] = route('dashboard.constant.tbljob');
            } else {
                $message = 'لا يمكن الحفظ';
                $result['status'] = false;
                $result['message_out'] = $message;
                $result['url'] = route('dashboard.constant.tbljob');

            }
        }
        return response()->json($result);
    }


    public function updstorejob(Request $request)
    {
        $id = $request->job_id_db;
        $attributeNames = array(
            'job_name_u' => 'المهنة',
        );
        $validator = Validator::make($request->all(), [
            'job_name_u' => ['required', Rule::unique('job', 'job_name')->ignore($id, 'job_id')],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
            $result['url'] = route('dashboard.constant.tbljob');

        } else {
            $ERROR_FLAG = 0;
            $result2 = DB::table('job')
                ->where('job_id', $id)
                ->update([
                    'job_name' => $request->job_name_u,
                ]);
            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';
            $result['url'] = route('dashboard.constant.tbljob');

        }
        return response()->json($result);
    }
}
