<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Emps;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

//use App\Http\Requests\StoreEmpsRequest;
//use App\Http\Requests\UpdateEmpsRequest;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;
use Debugbar;

class EmpsController extends Controller
{
    use ApimtitTrait;
    public function __construct()
    {
        $this->middleware('ishaveaccess:1');
    }
    public function show_job_cat(Request $request)
    {
        $desc = $request->desc;
        $get_all_job_dept = DB::select('SELECT job_dept_id, job_dept_name_ar as  name from job_dept where   1=1 ');
        return view('dashboard.emps.show_job_cat', compact('get_all_job_dept'));
    }
    public function load_emp_div(Request $request)
    {
        $job = $request->job;
        $desc = $request->desc;
        $serach_role_data_all = DB::select('SELECT id ,role_name_en,role_name_ar as name FROM role ');
        return view('dashboard.emps.load_emp_div', compact('serach_role_data_all', 'job', 'desc'));
    }
    public function sel_emp_supervisor(Request $request)
    {
        $string = $request->q;
        $page = $request->page;
        $response = emps::sel_emp_supervisor($string, $page);
        echo json_encode($response);
    }
    public function add_role()
    {
        if (Perm::get_function_access(6)) {
            $page_title = 'ادخال بيانات المجموعة';
            $get_all_per_controller = DB::table('per_controller')->select('*')->where('is_delete', 0)->where('is_active', 1)->get();
            $const = array("get_all_per_controller", "page_title");
            return view('dashboard.emps.add_role', compact($const));
        }
    }
    public function save_role(Request $request)
    {
        if (Perm::get_function_access(6)) {
            $role_per = $request->role_per;
            $attributeNames = array(
                'role_name' => 'اسم المجموعة',
                'role_per' => 'الصلاحيات',
            );
            $validator = Validator::make($request->all(), [
                'role_name' => ['required', 'string'],
                'role_per' => ['required'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $result2 = DB::table('role')->insertGetId([
                    'role_name' => $request->role_name,
                    'role_ins_dt' => Carbon::now(),
                    'role_ins_id' => Auth::user()->id,
                ]);
                if ($result2 != '') {
                    if ($role_per != "") {
                        $role_per = explode(",", $role_per);
                        foreach ($role_per as $tem) {
                            if (is_numeric($tem)) {
                                $result_role_per = DB::table('role_per')->insertGetId([
                                    'role_id' => $result2,
                                    'function_id' => $tem
                                ]);
                            }
                        }
                    }
                    $result['status'] = $result2;
                    $result['message_out'] = 'تم الحفظ بنجاح';
                } else {
                    $result['status'] = false;
                    $result['message_out'] = 'لم يتم الحفظ';
                }
            }
            return response()->json($result);
        }
    }


    public function view_role()
    {
        if (Perm::get_function_access(7) || Perm::get_function_access(8) || Perm::get_function_access(9)) {
            $page_title = 'ادارة مجموعة الصلاحيات';
            return view('dashboard.emps.view_role', compact('page_title'));
        }
    }


    public function tbl_role(Request $request)
    {
        if (Perm::get_function_access(7) || Perm::get_function_access(8) || Perm::get_function_access(9)) {
            return view('dashboard.emps.tbl_role');
        }
    }


    public function ajax_search_role(Request $request)
    {
        if (Perm::get_function_access(7) || Perm::get_function_access(8) || Perm::get_function_access(9)) {
            $worker_name = $request->worker_name;
            $ssn = $request->ssn;
            $work_place_id = $request->work_place_id;
            $doe = $request->doe;
            $updatedcancal_at = $request->updatedcancal_at;
            $job_id = $request->job_id;
            $end_dt = $request->end_dt;
            $end_p_dt = $request->end_p_dt;
            $list_total = user::serachrolecount();
            $list = user::serachroleddata();
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                $row[] = $i;
                $row[] = $x->role_name;
                $opt = '<div class="btn-group btn-group-sm" role="group"  >';
                    $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm upd_role" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"    data-url=' . "'" . route('dashboard.emps.upd_role') . "'" . '            onclick="upd_role(' . "'" . $x->id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_role(' . "'" . $x->id . "'" . ')"> <i class="fas fa-trash-alt fa-fw"></i>  </a>';
                $opt .= '</div>';
                $opt .= '</div>';
                $row[] = $opt;
                $data[] = $row;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data);
            echo json_encode($output);
        }
    }


    function del_role(Request $request)
    {
        if (Perm::get_function_access(7) || Perm::get_function_access(8) || Perm::get_function_access(9)) {

            $id = $request->id;
            try {
                $delete = DB::delete('delete from role where id = ?', [$id]);
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
    }

    public function upd_role(Request $request)
    {
        if (Perm::get_function_access(7) || Perm::get_function_access(8) || Perm::get_function_access(9)) {
            $role_id_upd = $request->role_id;
            $page_title = 'تعديل بيانات ';
            $get_sp_role = DB::table('role')->select('*')->where('id', $role_id_upd)->first();
            $get_all_per_controller = DB::table('per_controller')->select('*')->where('is_delete', 0)->where('is_active', 1)->get();
            $const = array("get_all_per_controller", "page_title", 'role_id_upd', 'get_sp_role');
            return view('dashboard.emps.upd_role', compact($const));
        }

    }


    public function updrole(Request $request)
    {
        if (Perm::get_function_access(7) || Perm::get_function_access(8) || Perm::get_function_access(9)) {
            $id = $request->role_id_val;
            $role_per = $request->role_per;
            $attributeNames = array(
                'role_name' => 'اسم المجموعة',
                'role_per' => 'الصلاحيات',
            );
            $validator = Validator::make($request->all(), [
                'role_name' => ['required', 'string'],
                'role_per' => ['required'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;

                $result2 = DB::table('role')
                    ->where('id', $id)
                    ->update([
                        'role_name' => $request->role_name,
                        'role_ins_dt' => Carbon::now(),
                        'role_ins_id' => Auth::user()->id,
                    ]);
                if ($role_per != "") {
                    DB::table('role_per')->where('role_id', $id)->delete();
                    $role_per = explode(",", $role_per);
                    foreach ($role_per as $tem) {
                        if (is_numeric($tem)) {
                            $result_role_per = DB::table('role_per')->insertGetId([
                                'role_id' => $id,
                                'function_id' => $tem
                            ]);
                        }
                    }
                    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! مشكلة الصلاحيات
                    $list = DB::table('permission')->where("role_id",$id )->distinct('emp_id')->groupBy(['emp_id'])->get();
                    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! مشكلة الصلاحيات

                    foreach ($list as $x) {
                        $emp_id = $x->emp_id;
                        DB::table('permission')->where('emp_id', $emp_id)->delete();
                        $get_role_per = DB::table('role_per')->select('*')->where('role_id', $id)->get();

                        foreach ($get_role_per as $x) {
                            $function_id = $x->function_id;
                            DB::table('permission')->insertGetId([
                                'emp_id' => $emp_id,
                                'role_id' => $id,
                                'function_id' => $function_id,
                                'is_role' => 1,
                            ]);
                        }
                    }
                }

                $result['status'] = 1;
                $result['message_out'] = 'تم الحفظ بنجاح';
            }
            return response()->json($result);
        }
    }


    public function index()
    {
        if (Perm::get_function_access(1)) {
         //   Debugbar::info('dd');
            $page_title = 'ادخال بيانات الموظفين';
            $serach_role_data_all = DB::table('role')->get();
            $manager = DB::table('manager')->get();
            $sel_emps = array("page_title");
            return view('dashboard.emps.index', compact($sel_emps, 'serach_role_data_all', 'manager'));
        } else {
        }
    }


    public function ajax_search_emps(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(2) || Perm::get_function_access(3) || Perm::get_function_access(4) ||
                Perm::get_function_access(5))) {
            $dt_to = $request->dt_to;
            $dt_from = $request->dt_from;
            $emps_name = $request->emps_name;
            $sex = $request->sex;
            $phone = $request->phone;
            $email = $request->email;
            $list_total = user::serachspendcount($emps_name, $sex, $phone, $email);
            $list = user::serachspenddata($emps_name, $sex, $phone, $email);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {

                if ($x->active == '1') {
                    $active_desc = '<span class="ms-2 badge badge-light-success fw-bold">فعال</span>';
                } else {
                    $active_desc = '<span class="ms-2 badge badge-light-danger fw-bold">غير فعال</span>';
                }
                $no++;
                $i++;
                $row = array();
                $row[] = $i;
                $row[] = $x->name;
                $row[] = $x->email;
                $row[] = $x->phone;
                $row[] = $x->j_c_name_ar;
                $row[] = $active_desc;
                $row[] = $x->note;
                if (Perm::get_function_access(3) || Perm::get_function_access(4) ||Perm::get_function_access(5)) {
                    $opt = '<div class="btn-group btn-group-sm" role="group"  >';
                    if (Perm::get_function_access(3)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm upd_emps" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"    data-url=' . "'" . route('dashboard.emps.upd_emps') . "'" . '            onclick="upd_emps(' . "'" . $x->id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(4)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_emps(' . "'" . $x->id . "'" . ')"> <i class="fas fa-trash-alt fa-fw"></i>  </a>';
                    }
                    if (Perm::get_function_access(5)) {
                        if ($x->active == '1') {
                            $opt .= '<a class="btn btn-sm btn-dark btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="inactive_emp(' . "'" . $x->id . "'" . ",'" . $x->active . "'" . ')"> <i class="fas fa-stop-circle fa-fw"></i>  </a>';
                        } else if ($x->active == '0') {
                            $opt .= '<a class="btn btn-sm btn-info btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="inactive_emp(' . "'" . $x->id . "'" . ",'" . $x->active . "'" . ')"> <i class="fas fa-play-circle fa-fw"></i>  </a>';
                        }
                    }
                    $opt .= '</div>';
                    $row[] = $opt;
                }
                $data[] = $row;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data);
            echo json_encode($output);
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    function inactive_emp(Request $request)
    {
        if (Perm::get_function_access(5)) {
            $id = $request->id;
            $active = $request->active;
            if ($active == 0) {
                $active = 1;
            } else if ($active == 1) {
                $active = 0;
            }
            try {
                $delete = DB::table('users')->where('id', $id)->update(['active' => $active]);
                if ($delete) {
                    $result['status'] = true;
                    $result['message'] = 'تمت العملية بنجاح';
                } else {
                    $message = 'لم تتم العملية بنجاح';
                    $result['status'] = false;
                    $result['message'] = $message;
                }
            } catch (\Exception $exception) {
                $message = 'لا يمكن اتمام العملية';
                $result['status'] = false;
                $result['message'] = $message;
            }
            echo json_encode($result);
        }
    }

    function del_emps(Request $request)
    {
        if (Perm::get_function_access(4)) {
            $id = $request->id;
            try {
                $delete = DB::delete('delete from users where id = ?', [$id]);
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
    }


    public function upd_emps(Request $request)
    {
        if (Perm::get_function_access(3)) {
            $id = $request->id;
            $emps = DB::table('users')
                ->leftJoin('job_cat', 'users.emp_job', '=', 'job_cat.j_c_id')
                ->select('users.*', 'job_cat.j_c_name_ar')
                ->where('users.id', $id)->first();
            $get_role_emp = DB::table('permission')->select('role_id')->where('emp_id', $emps->id)->first();
            //$workers_manager = DB::table('workers_manager')->select('*')->where('user_id', $emps->id)->get();
            $manager = DB::table('manager')->get();
            $workers_manager = DB::table('workers_manager')->select('manager_id')->where('user_id', $emps->id)->pluck('manager_id');
            $workers_manager = $workers_manager->toArray();

            if (!$get_role_emp) {
                $get_role_emp = '';
            } else {
                $get_role_emp = $get_role_emp->role_id;
            }
            $serach_role_data_all = DB::table('role')->get();
            return view('dashboard.emps.upd_emps', compact('emps', 'serach_role_data_all', 'get_role_emp', 'manager', 'workers_manager'));
        }
    }

    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(2) || Perm::get_function_access(3) || Perm::get_function_access(4) || Perm::get_function_access(5))) {
            return view('dashboard.emps.tbl_emps');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function views()
    {
        if (Perm::get_function_access(2) || Perm::get_function_access(3) || Perm::get_function_access(4) || Perm::get_function_access(5)) {
            $page_title = 'عرض بيانات الموظفين';
            return view('dashboard.emps.view', compact('page_title'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function updstore(Request $request)
    {
        if (Perm::get_function_access(3)) {
            $id = $request->id_val;
            $role_per = $request->role_per;
            $manager = $request->manager;
            $attributeNames = array(
                'name' => 'اسم العامل ',
                'email' => 'الايميل',
                'password' => 'كلمة المرور',
                'phone' => 'رقم الجوال',
                'job' => 'المسمى الوظيفي',
                'role_per' => 'مجموعة الصلاحية',
                // 'manager' => 'المجموعة للعامل',
            );
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:255', 'string', Rule::unique('users', 'name')->ignore($id)],
                'email' => ['required', 'max:255', 'string', 'email', Rule::unique('users', 'email')->ignore($id)],
                'phone' => ['required', 'numeric', Rule::unique('users', 'phone')->ignore($id)],
                'job' => ['required'],
                'role_per' => [Rule::requiredIf($request->job != 1)],
                // 'manager' => [Rule::requiredIf($request->job != 1)],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';

            } else {
                $ERROR_FLAG = 0;
                $user_photo = '';
                $result2 = User::where('id', $id)->update([
                    'name' => $request->name,
                    'email' => Str::lower($request->email),
                    'note' => $request->note,
                    'phone' => $request->phone,
                    'emp_job' => $request->job,
                    'emp_upd_user' => Auth::user()->id,
                    'emp_upd_dt' => Carbon::now(),
                ]);
                if ($request->job == 1) {
                    DB::table('permission')->where('emp_id', $id)->delete();
                    DB::table('workers_manager')->where('user_id', $id)->delete();

                } else {
                    if ($role_per != "") {
                        $get_role_per = DB::table('role_per')->select('function_id')->where('role_id', $role_per)->get();
                        DB::table('permission')->where('emp_id', $id)->where('is_role', 1)->delete();
                        foreach ($get_role_per as $x) {
                            $function_id = $x->function_id;
                            DB::table('permission')->insertGetId([
                                'emp_id' => $id,
                                'role_id' => $role_per,
                                'function_id' => $function_id,
                                'is_role' => 1,
                            ]);
                        }
                    }
                    if (isset($_POST["manager"])) {
                        DB::table('workers_manager')->where('user_id', $id)->delete();
                        foreach ($manager as $pr) {
                            $result_per = DB::table('workers_manager')->insertGetId([
                                'user_id' => $id,
                                'manager_id' => $pr,
                            ]);
                        }
                    }
                }
                $result['status'] = 1;
                $result['message_out'] = 'تم الحفظ بنجاح';
            }
            return response()->json($result);
        }
    }


    public function store(Request $request)
    {
        if (Perm::get_function_access(1)) {
            $role_per = $request->role_per;
            $manager = $request->manager;
            $attributeNames = array(
                'name' => 'اسم العامل ',
                'email' => 'الايميل',
                'password' => 'كلمة المرور',
                'phone' => 'رقم الجوال',
                'job' => 'المسمى الوظيفي',
                'role_per' => 'مجموعة الصلاحية',
                // 'manager' => 'المجموعة للعامل',
            );
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'unique:users', 'max:255', 'string'],
                'email' => ['required', 'unique:users', 'max:255', 'email'],
                'password' => ['required', 'unique:users'],
                'phone' => ['required', 'unique:users', 'numeric'],
                'job' => ['required'],
                'role_per' => [Rule::requiredIf($request->job != 1)],
                // 'manager' => [Rule::requiredIf($request->job != 1)],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $result2 = DB::table('users')->insertGetId([
                    'name' => $request->name,
                    'email' => Str::lower($request->email),
                    'password' => Hash::make($request->password),
                    'note' => $request->note,
                    'phone' => $request->phone,
                    'emp_job' => $request->job,
                    'emp_ins_user' => Auth::user()->id,
                    'emp_ins_dt' => Carbon::now(),
                ]);
                if ($result2 != '') {
                    if ($role_per != "") {
                        $get_role_per = DB::table('role_per')->select('function_id')->where('role_id', $role_per)->get();
                        foreach ($get_role_per as $x) {
                            $function_id = $x->function_id;

                            $result_per = DB::table('permission')->insertGetId([
                                'emp_id' => $result2,
                                'role_id' => $role_per,
                                'function_id' => $function_id,
                                'is_role' => 1,
                            ]);
                        }
                    }
                    if (isset($_POST["manager"])) {
                        foreach ($manager as $pr) {
                            $result_per = DB::table('workers_manager')->insertGetId([
                                'user_id' => $result2,
                                'manager_id' => $pr,
                            ]);
                        }
                    }
                    $ERROR_FLAG = 0;
                    $result['status'] = $result2;
                    $result['message_out'] = 'تم الحفظ بنجاح';

                } else {
                    $result['status'] = false;
                    $result['message_out'] = 'لم يتم الحفظ بنجاح';
                }
            }
            return response()->json($result);
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }
}
