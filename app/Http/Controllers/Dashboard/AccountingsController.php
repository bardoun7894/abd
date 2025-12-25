<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accountings;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;

class AccountingsController extends Controller
{
    use ApimtitTrait;

    public function __construct()
    {
        $this->middleware('ishaveaccess:4');
    }

    public function payments_month(Request $request)
    {
        $month = $request->month;
        $year = $request->year;
        $list = DB::table('payments_month')->where(['payments_month_m' => $month, 'payments_month_y' => $year])->get();
        if (count($list) > 0) {
            $result['status'] = "true";
            $result['message'] = "يوجد بيانات";
            foreach ($list as $x) {
                $result['payments_month_val'] = $x->payments_month_val;
            }
        } else {
            $result['status'] = 'false';
            $result['code'] = '1';
            $result['message'] = "لا يوجد لهذا الشهر قيمة مدخلة";
        }
        echo json_encode($result);
    }


    public function index()
    {
        if (Perm::get_function_access(26)) {
            $page_title = 'ادخال المدفوعات الشهرية';
            $sel_accountings = array("page_title");
            $work_place = DB::table('work_place')->get();
            return view('dashboard.accountings.index', compact('work_place', $sel_accountings));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function create()
    {
        if (Perm::get_function_access(26)) {
            $page_title = 'ادخال بيانات المدفوعات الشهرية';
            $sel_accountings = array("page_title");
            return view('dashboard.accountings.create', compact($sel_accountings));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function storepmonth(Request $request)
    {
        if (Perm::get_function_access(26)) {
            $payments_month_desc = $request->payments_month_desc;
            $array_name = explode("-", $payments_month_desc);
            $payments_month_m = $array_name[0];
            $payments_month_y = $array_name[1];
            $attributeNames = array(
                'payments_month_desc' => 'شهر الدفع',
                'payments_month_val' => 'المبلغ',
            );
            $validator = Validator::make($request->all(), [
                'payments_month_desc' => ['required', 'unique:payments_month', 'max:255'],
                'payments_month_val' => ['required', 'integer'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $user = DB::table('payments_month')->insertGetId([
                    'payments_month_desc' => $request->payments_month_desc,
                    'payments_month_val' => $request->payments_month_val,
                    'payments_month_y' => $payments_month_y,
                    'payments_month_m' => $payments_month_m,
                    'note' => $request->note,
                ]);
                $ERROR_FLAG = 0;
                $result['status'] = $user;
                $result['message_out'] = 'تم الحفظ بنجاح';
            }
            return response()->json($result);
        }
    }


    public function updpmonthstore(Request $request)
    {
        $payments_month_id = $request->payments_month_id_db;
        $payments_month_desc = $request->payments_month_desc;
        $array_name = explode("-", $payments_month_desc);
        $payments_month_m = $array_name[0];
        $payments_month_y = $array_name[1];
        $attributeNames = array(
            'payments_month_desc' => 'شهر الدفع',
            'payments_month_val' => 'المبلغ',
        );
        $validator = Validator::make($request->all(), [
            'payments_month_val' => ['required', 'integer'],
            'payments_month_desc' => ['required', 'max:255', Rule::unique('payments_month', 'payments_month_desc')->ignore($payments_month_id, 'payments_month_id')],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {
            $ERROR_FLAG = 0;
            $user_photo = '';
            $result2 = DB::table('payments_month')
                ->where('payments_month_id', $payments_month_id)
                ->update([
                    'payments_month_desc' => $request->payments_month_desc,
                    'payments_month_val' => $request->payments_month_val,
                    'payments_month_y' => $payments_month_y,
                    'payments_month_m' => $payments_month_m,
                    'note' => $request->note,]);
            //  $id = DB::getPdo()->lastInsertId();
            /*   $result2=User::where('payments_month_id',$payments_month_id_db)->update([
                  'payments_month_desc' => $request->payments_month_desc,
                  'payments_month_val' => $request->payments_month_val,
                  'payments_month_y' => $payments_month_y,
                  'payments_month_m' => $payments_month_m,
                              ]);*/
            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';

        }
        return response()->json($result);

    }


    public function viewpmonth()
    {
        if (Perm::get_function_access(27) || Perm::get_function_access(28) || Perm::get_function_access(29)) {
            $page_title = 'عرض بيانات المحاسبة';
            return view('dashboard.accountings.view_pmonth', compact('page_title'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function pmonth_tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(27) || Perm::get_function_access(28) || Perm::get_function_access(29))) {
            return view('dashboard.accountings.tbl_pmonth');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_pmonth(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(27) || Perm::get_function_access(28) || Perm::get_function_access(29))) {
            $payments_month_desc = $request->payments_month_desc;
            if ($payments_month_desc != '') {
                $array_name = explode("-", $payments_month_desc);
                $payments_month_m = $array_name[0];
                $payments_month_y = $array_name[1];
            } else {
                $payments_month_m = '';
                $payments_month_y = '';
            }
            $list_total = Accountings::serachspendcount($payments_month_m, $payments_month_y);
            $list = Accountings::serachspenddata($payments_month_m, $payments_month_y);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                $row[] = $i;
                $row[] = $x->payments_month_desc;
                $row[] = $x->payments_month_y;
                $row[] = $x->payments_month_m;
                $row[] = $x->payments_month_val;
                $row[] = $x->note;
                if (Perm::get_function_access(28) || Perm::get_function_access(29)) {
                    $opt = '<div class="btn-group btn-group-sm " role="group"  >';
                    if (Perm::get_function_access(28)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_pmonth" style="margin-left: .5rem;"    data-url=' . "'" . route('dashboard.accountings.upd_pmonth') . "'" . '            onclick="upd_pmonth(' . "'" . $x->payments_month_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(29)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-left: .5rem;" onclick="del_pmonth(' . "'" . $x->payments_month_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
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


    function del_pmonth(Request $request)
    {
        if (Perm::get_function_access(29)) {
            $id = $request->id;
            $delete = DB::delete('delete from payments_month where payments_month_id = ?', [$id]);
            if ($delete) {
                $result['status'] = true;
                $result['message'] = 'تم';
            } else {
                $message = 'لا يمكن الحذف';
                $result['status'] = false;
                $result['message'] = $message;
            }
            echo json_encode($result);
        }
    }


    public function upd_pmonth(Request $request)
    {
        if (Perm::get_function_access(28)) {
            $id = $request->id;
            $payments = DB::table('payments_month')->where('payments_month_id', $id)->first();
            return view('dashboard.accountings.upd_pmonth', compact('payments'));
        }
    }


    public function views()
    {
        $work_place = DB::table('work_place')->get();
        $page_title = 'عرض بيانات المحاسبة';
        return view('dashboard.accountings.view', compact('work_place', 'page_title'));
    }


    public function tbl(Request $request)
    {
        if ($request->ajax()) {
            return view('dashboard.accountings.tbl_accountings');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_accountings(Request $request)
    {
        $worker_id = $request->worker_id;
        $payments_month_desc = $request->payments_month_desc;
        if ($payments_month_desc != '') {
            $array_name = explode("-", $payments_month_desc);
            $payments_month_m = $array_name[0];
            $payments_month_y = $array_name[1];
        } else {
            $payments_month_m = '';
            $payments_month_y = '';
        }
        $list_total = Accountings::serachspendcountdesc($payments_month_m, $payments_month_y, $worker_id);
        $list = Accountings::serachspenddatadesc($payments_month_m, $payments_month_y, $worker_id);
        $data = array();
        $no = $_POST['start'];
        $i = 0;
        foreach ($list as $x) {
            $no++;
            $i++;
            $row = array();
            $row[] = $i;
            $row[] = $x->worker_name;
            $row[] = $x->payments_month_desc;
            $row[] = $x->payments_month_val;
            $row[] = $x->payments_month_pay;
            $row[] = $x->payments_month_remain;
            $row[] = $x->note;
            $row[] = $x->name;
            $row[] = Carbon::parse($x->created_at)->format('d-m-Y');
            $opt = '<div class="btn-group btn-group-sm " role="group"  >';
            if ("1" == "1") {
                $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_accountings" style="margin-left: .5rem;"    data-url=' . "'" . route('dashboard.accountings.upd_accountings') . "'" . '            onclick="upd_accountings(' . "'" . $x->payments_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
            }
            /*if ("1" == "1") {
            $opt.='<a class="btn btn-sm btn-success btn-icon btn-icon-sm  print_accountings" style="margin-left: .5rem;"    data-url=' . "'" . route('dashboard.accountings.print') . "'" . '            onclick="print_accountings(' . "'" . $x->payments_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
            }*/
            if ("1" == "1") {
                $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-left: .5rem;" onclick="del_accountings(' . "'" . $x->payments_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
            }
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


    function del_accountings(Request $request)
    {
        $id = $request->id;
        $delete = DB::table('payments')
            ->where('payments_id', $id)
            ->update([
                'is_deleted' => 1,
                'deleted_at' => Carbon::now(),
                'deleted_user' => Auth::user()->id,

            ]);
        if ($delete) {
            $result['status'] = true;
            $result['message'] = 'تم';
        } else {
            $message = 'لا يمكن الحذف';
            $result['status'] = false;
            $result['message'] = $message;
        }
        echo json_encode($result);
    }


    public function upd_accountings(Request $request)
    {
        $id = $request->id;
        $payments = DB::table('payments')->where('payments_id', $id)->first();
        $worker_id = $payments->worker_id;
        $workers = DB::table('workers')->where('worker_id', $worker_id)->first();
        return view('dashboard.accountings.upd_accountings', compact('payments', 'workers'));
    }


    public function store(Request $request)
    {
        $payments_month_desc = $request->payments_month_desc;
        $array_name = explode("-", $payments_month_desc);
        $payments_month_m = $array_name[0];
        $payments_month_y = $array_name[1];
        $attributeNames = array(
            'worker_id' => 'اسم العامل ',
            'payments_month_desc' => 'شهر الدفع',
            'payments_month_val' => 'المبلغ المطلوب',
            'payments_month_pay' => 'المبلغ المدفوع',
            'payments_month_remain' => 'المبلغ المتبقي',
        );
        $validator = Validator::make($request->all(), [
            'worker_id' => ['required', 'integer',
                Rule::unique("payments")->where(
                    function ($query) use ($request) {
                        return $query->where(
                            [
                                ["worker_id", "=", $request->worker_id],
                                ["payments_month_desc", "=", $request->payments_month_desc]
                            ]
                        );
                    }),

            ],
            'payments_month_desc' => ['required'],
            'payments_month_val' => ['required',],
            'payments_month_pay' => ['required', 'min:1', request()->filled('payments_month_val') ? 'lte:payments_month_val' : ''],
            'payments_month_remain' => ['required', 'min:0', request()->filled('payments_month_val') ? 'lte:payments_month_val' : ''],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {
            $user = DB::table('payments')->insertGetId([
                'worker_id' => $request->worker_id,
                'payments_month_desc' => $request->payments_month_desc,
                'payments_month_m' => $payments_month_m,
                'payments_month_y' => $payments_month_y,
                'payments_month_val' => $request->payments_month_val,
                'payments_month_pay' => $request->payments_month_pay,
                'payments_month_remain' => $request->payments_month_remain,
                'note' => $request->note,
                'created_at' => Carbon::now(),
                'create_user' => Auth::user()->id,

            ]);

            $ERROR_FLAG = 0;
            $result['status'] = $user;
            $result['message_out'] = 'تم الحفظ بنجاح';
        }
        return response()->json($result);
    }


    public function updstore(Request $request)
    {
        $id = $request->payments_id_db;
        $payments_month_desc = $request->payments_month_desc;
        $array_name = explode("-", $payments_month_desc);
        $payments_month_m = $array_name[0];
        $payments_month_y = $array_name[1];
        $attributeNames = array(
            'worker_id' => 'اسم العامل ',
            'payments_month_desc' => 'شهر الدفع',
            'payments_month_val' => 'المبلغ المطلوب',
            'payments_month_pay' => 'المبلغ المدفوع',
            'payments_month_remain' => 'المبلغ المتبقي',
        );
        $validator = Validator::make($request->all(), [
            'worker_id' => ['required', 'integer',
                Rule::unique("payments")->where(
                    function ($query) use ($request) {
                        return $query->where(
                            [
                                ["worker_id", "=", $request->worker_id],
                                ["payments_month_desc", "=", $request->payments_month_desc],
                                ["payments_id", "!=", $request->payments_id_db]
                            ]
                        );
                    }),

            ],
            'payments_month_desc' => ['required'],
            'payments_month_val' => ['required',],
            'payments_month_pay' => ['required', 'min:1', request()->filled('payments_month_val') ? 'lte:payments_month_val' : ''],
            'payments_month_remain' => ['required', 'min:0', request()->filled('payments_month_val') ? 'lte:payments_month_val' : ''],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {
            $result2 = DB::table('payments')
                ->where('payments_id', $id)
                ->update([
                    'worker_id' => $request->worker_id,
                    'payments_month_desc' => $request->payments_month_desc,
                    'payments_month_m' => $payments_month_m,
                    'payments_month_y' => $payments_month_y,
                    'payments_month_val' => $request->payments_month_val,
                    'payments_month_pay' => $request->payments_month_pay,
                    'payments_month_remain' => $request->payments_month_remain,
                    'note' => $request->note,
                    'updated_at' => Carbon::now(),
                    'updated_user' => Auth::user()->id,

                ]);
            $ERROR_FLAG = 0;
            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';
        }

        return response()->json($result);

    }


}
