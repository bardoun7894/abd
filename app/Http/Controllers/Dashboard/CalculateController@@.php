<?php
namespace App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Calculate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;
class CalculateController extends Controller
{
    use ApimtitTrait;

    public function __construct()
    {
        $this->middleware('ishaveaccess:6');
    }

    public function index()
    {
        if (Perm::get_function_access(39)) {
            $page_title = 'إضافة مصروف محل';
            $sel_calculate = array("page_title");
            $work_place = DB::table('work_place')->get();
            return view('dashboard.calculate.index', compact('work_place', $sel_calculate));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function create()
    {
        $sel_calculate = "1";
        $sub_add_calculate = "1";
        $page_title = 'ادخال بيانات المدفوعات الشهرية';
        $sel_calculate = array("sel_calculate", "page_title");
        return view('dashboard.calculate.create', compact($sel_calculate));
    }


    public function storepmonth(Request $request)
    {
        $calculate_month_desc = $request->calculate_month_desc;
        $array_name = explode("-", $calculate_month_desc);
        $calculate_month_m = $array_name[0];
        $calculate_month_y = $array_name[1];
        $attributeNames = array(
            'calculate_month_desc' => 'شهر الدفع',
            'calculate_month_val' => 'المبلغ',

        );
        $validator = Validator::make($request->all(), [
            'calculate_month_desc' => ['required', 'unique:calculate_month', 'max:255'],
            'calculate_month_val' => ['required', 'integer'],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {

            $user = DB::table('calculate_month')->insertGetId([
                'calculate_month_desc' => $request->calculate_month_desc,
                'calculate_month_val' => $request->calculate_month_val,
                'calculate_month_y' => $calculate_month_y,
                'calculate_month_m' => $calculate_month_m,
                'note' => $request->note,
            ]);

            $ERROR_FLAG = 0;
            $result['status'] = $user;
            $result['message_out'] = 'تم الحفظ بنجاح';
        }
        return response()->json($result);
    }


    public function updpmonthstore(Request $request)
    {
        $calculate_month_id = $request->calculate_month_id_db;

        $calculate_month_desc = $request->calculate_month_desc;
        $array_name = explode("-", $calculate_month_desc);
        $calculate_month_m = $array_name[0];
        $calculate_month_y = $array_name[1];
        $attributeNames = array(
            'calculate_month_desc' => 'شهر الدفع',
            'calculate_month_val' => 'المبلغ',

        );
        $validator = Validator::make($request->all(), [

            'calculate_month_val' => ['required', 'integer'],
            'calculate_month_desc' => ['required', 'max:255', Rule::unique('calculate_month', 'calculate_month_desc')->ignore($calculate_month_id, 'calculate_month_id')],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {
            $ERROR_FLAG = 0;
            $user_photo = '';
            $result2 = DB::table('calculate_month')
                ->where('calculate_month_id', $calculate_month_id)
                ->update([
                    'calculate_month_desc' => $request->calculate_month_desc,
                    'calculate_month_val' => $request->calculate_month_val,
                    'calculate_month_y' => $calculate_month_y,
                    'calculate_month_m' => $calculate_month_m,
                    'note' => $request->note,]);
            //  $id = DB::getPdo()->lastInsertId();


            /*   $result2=User::where('calculate_month_id',$calculate_month_id_db)->update([
                  'calculate_month_desc' => $request->calculate_month_desc,
                  'calculate_month_val' => $request->calculate_month_val,
                  'calculate_month_y' => $calculate_month_y,
                  'calculate_month_m' => $calculate_month_m,
                              ]);*/
            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';

        }
        return response()->json($result);

    }


    public function viewpmonth()
    {
        $page_title = 'عرض بيانات المحاسبة';
        return view('dashboard.calculate.view_pmonth', compact('page_title'));
    }


    public function pmonth_tbl(Request $request)
    {
        if ($request->ajax()) {
            return view('dashboard.calculate.tbl_pmonth');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_pmonth(Request $request)
    {
        $calculate_month_desc = $request->calculate_month_desc;
        if ($calculate_month_desc != '') {
            $array_name = explode("-", $calculate_month_desc);
            $calculate_month_m = $array_name[0];
            $calculate_month_y = $array_name[1];
        } else {
            $calculate_month_m = '';
            $calculate_month_y = '';
        }

        $list_total = Calculate::serachspendcount($calculate_month_m, $calculate_month_y);
        $list = Calculate::serachspenddata($calculate_month_m, $calculate_month_y);
        $data = array();
        $no = $_POST['start'];
        $i = 0;
        foreach ($list as $x) {
            $no++;
            $i++;
            $row = array();
            $row[] = $i;

            $row[] = $x->calculate_month_desc;
            $row[] = $x->calculate_month_y;
            $row[] = $x->calculate_month_m;
            $row[] = $x->calculate_month_val;
            $row[] = $x->note;
            $opt = '<div class="btn-group btn-group-sm " role="group"  >';
            if ("1" == "1") {
                $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_pmonth" style="margin-left: .5rem;"    data-url=' . "'" . route('dashboard.calculate.upd_pmonth') . "'" . '            onclick="upd_pmonth(' . "'" . $x->calculate_month_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
            }
            if ("1" == "1") {
                $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-left: .5rem;" onclick="del_pmonth(' . "'" . $x->calculate_month_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
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


    function del_pmonth(Request $request)
    {
        //  if ( get_function_access(50)  and $this->input->is_ajax_request()) {
        $id = $request->id;
        $delete = DB::delete('delete from calculate_month where calculate_month_id = ?', [$id]);
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


    public function upd_pmonth(Request $request)
    {
        $id = $request->id;
        $calculate = DB::table('calculate_month')->where('calculate_month_id', $id)->first();
        return view('dashboard.calculate.upd_pmonth', compact('calculate'));
    }


    public function views()
    {
        if (Perm::get_function_access(40) || Perm::get_function_access(41) || Perm::get_function_access(42)
            || Perm::get_function_access(43) || Perm::get_function_access(44)) {
            $work_place = DB::table('work_place')->get();
            $manager = $this->get_manager();
            $page_title = 'إدارة مصاريف المحلات';
            return view('dashboard.calculate.view', compact('work_place', 'manager', 'page_title'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(40) || Perm::get_function_access(41) || Perm::get_function_access(42)
                || Perm::get_function_access(43) || Perm::get_function_access(44))) {
            return view('dashboard.calculate.tbl_calculate');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_calculate(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(40) || Perm::get_function_access(41) || Perm::get_function_access(42)
                || Perm::get_function_access(43) || Perm::get_function_access(44))) {
            $shop_id = $request->shop_id;
            $manager_id = $request->manager_id;
            $calculate_month_desc = $request->calculate_month_desc;
            if ($calculate_month_desc != '') {
                $array_name = explode("-", $calculate_month_desc);
                $calculate_month_m = $array_name[0];
                $calculate_month_y = $array_name[1];
            } else {
                $calculate_month_m = '';
                $calculate_month_y = '';
            }
            $list_totl = Calculate::sumspendcountdesc($calculate_month_m, $calculate_month_y, $shop_id, $manager_id);
            $sum_c1 = 0;
            $sum_count_statement = 0;
            $sum_sum_det_calculate_month_pay_All = 0;
            $sum_xx = 0;
            foreach ($list_totl as $x_sum) {
                $c1 = $x_sum->c1;
                $count_statement = $x_sum->count_statement;
                $sum_det_calculate_month_pay_all = $x_sum->sum_det_calculate_month_pay;
                $xx = $x_sum->xx;
                $sum_c1 += $c1;
                $sum_count_statement += $count_statement;
                $sum_sum_det_calculate_month_pay_All += $sum_det_calculate_month_pay_all;
                $sum_xx += $xx;
            }
            $list_total = Calculate::serachspendcountdesc($calculate_month_m, $calculate_month_y, $shop_id, $manager_id);
            $list = Calculate::serachspenddatadesc($calculate_month_m, $calculate_month_y, $shop_id, $manager_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            $sum_calculate_month_val = 0;
            $sum_sum_det_calculate_month_pay = 0;
            $sum_sum_det_calculate_month_remain = 0;

            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                $row[] = $i;
                $calculate_month_val = $x->calculate_month_val;
                $sum_det_calculate_month_pay = $x->sum_det_calculate_month_pay;
                $sum_det_calculate_month_remain = $calculate_month_val - $sum_det_calculate_month_pay;
                if ($sum_det_calculate_month_remain == '0') {
                    $calculate_desc = '<span class="ms-2 badge badge-light-success fw-bold">مكتمل الدفع</span>';
                } else {
                    $calculate_desc = '<span class="ms-2 badge badge-light-danger fw-bold">متبقي</span>';
                }
                $sum_calculate_month_val += $calculate_month_val;
                $sum_sum_det_calculate_month_pay += $sum_det_calculate_month_pay;
                $sum_sum_det_calculate_month_remain += $sum_det_calculate_month_remain;
                $row[] = $x->shop_name;
                $row[] = $x->manager_name;
                $row[] = $x->calculate_month_desc;
                $row[] = $calculate_desc;
                $row[] = $calculate_month_val;
                $row[] = $sum_det_calculate_month_pay;
                $row[] = $sum_det_calculate_month_remain;
                $row[] = $x->count_statement;
                $row[] = $x->note;
                $row[] = $x->name;
                $row[] = Carbon::parse($x->created_at)->format('d-m-Y');
                if (Perm::get_function_access(41) || Perm::get_function_access(42)
                    || Perm::get_function_access(43) || Perm::get_function_access(44)) {

                    $opt = '<div class="btn-group btn-group-sm " role="group"  >';
                    if (Perm::get_function_access(41)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_calculate" style="margin-left: .5rem;"    data-url=' . "'" . route('dashboard.calculate.upd_calculate') . "'" . ' onclick="upd_calculate(' . "'" . $x->calculate_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(42) || Perm::get_function_access(43)) {
                        $opt .= '<a class="btn btn-sm btn-info btn-icon btn-icon-sm  upd_statement" style="margin-left: .5rem;"    data-url=' . "'" . route('dashboard.calculate.upd_statement') . "'" . ' onclick="upd_statement(' . "'" . $x->calculate_id . "'" . ')"> <i class="fab fa-cc-amazon-pay fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(44)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-left: .5rem;" onclick="del_calculate(' . "'" . $x->calculate_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
                    }
                    $opt .= '</div>';
                    $row[] = $opt;
                }
                $data[] = $row;
            }
            $output = array(
                "sum_c1" => $sum_c1,
                "sum_count_statement" => $sum_count_statement,
                "sum_sum_det_calculate_month_pay_All" => $sum_sum_det_calculate_month_pay_All,
                "sum_xx" => $sum_xx,

                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data);
            echo json_encode($output);
        }
    }


    public function tbl_detail(Request $request)
    {
        if (Perm::get_function_access(42) || Perm::get_function_access(43)) {
            return view('dashboard.calculate.tbl_calculate_detail');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_calculate_detail(Request $request)
    {
        $calculate_id = $request->calculate_id;
        $list_total = Calculate::serachspendcountdet($calculate_id);
        $list = Calculate::serachspenddet($calculate_id);
        $data = array();
        $no = $_POST['start'];
        $i = 0;
        foreach ($list as $x) {
            $no++;
            $i++;
            $row = array();
            $row[] = $i;
            $calculate_month_val = $x->calculate_month_val;
            $row[] = $x->shop_name;
            $row[] = $x->calculate_month_desc;
//$row[] =$calculate_month_val;
            $row[] = $x->det_calculate_month_val;
            $row[] = $x->det_calculate_month_pay;
            $row[] = $x->det_calculate_month_remain;
            $row[] = $x->det_note;
            $row[] = $x->det_create_user_name;
            $row[] = Carbon::parse($x->det_created_at)->format('d-m-Y');
            if (Perm::get_function_access(43)) {
                $opt = '<div class="btn-group btn-group-sm " role="group"  >';
                $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-left: .5rem;" onclick="del_calculate_det(' . "'" . $x->calculate_detail_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';

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
    }


    function del_calculate(Request $request)
    {
        if (Perm::get_function_access(44)) {
            $id = $request->id;
            $calculate = DB::table('calculate')->where('calculate_id', $id)->first();
            $shop_id = $calculate->shop_id;
            $issamecreatecalculate = $this->issamecreatecalculate($shop_id);
            if ($issamecreatecalculate) {
                $delete = DB::table('calculate')
                    ->where('calculate_id', $id)
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
            } else {
                $message = 'لا يمكن الحذف';
                $result['status'] = false;
                $result['message'] = $message;
                echo json_encode($result);
            }
        }
    }

    function del_calculate_det(Request $request)
    {
        if (Perm::get_function_access(43)) {

            $id = $request->id;
            $calculate = DB::table('calculate_detail')->where('calculate_detail_id', $id)->first();
            $calculate_id = $calculate->calculate_id;
            $calculate_month_pay = $calculate->calculate_month_pay;
            $last_calculate = DB::table('calculate_detail')->where('calculate_id', $calculate_id)->latest("calculate_detail_id")->first();
            $calculate_detail_id = $last_calculate->calculate_detail_id;
            $calculate_month_remain = $last_calculate->calculate_month_remain;
            $result1 = DB::table('calculate_detail')
                ->where('calculate_detail_id', $calculate_detail_id)
                ->update(['calculate_month_remain' => $calculate_month_pay + $calculate_month_remain]);
            /*
            $result1= DB::table('calculate')
            ->where('calculate_id',$calculate_id)
            ->update(['calculate_month_remain' => $calculate_month_pay+$calculate_month_remain,
            'calculate_month_pay' => $calculate_month_pay,
            ]);*/

            $delete = DB::table('calculate_detail')->where('calculate_detail_id', $calculate_detail_id)->delete();
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


    public function upd_calculate(Request $request)
    {
        if (Perm::get_function_access(41)) {
            $id = $request->id;
            $calculate = DB::table('calculate')->where('calculate_id', $id)->first();
            $shop_id = $calculate->shop_id;
            $issamecreatecalculate = $this->issamecreatecalculate($shop_id);
            if ($issamecreatecalculate) {
                $shop = DB::table('shop')->where('shop_id', $shop_id)->first();
                return view('dashboard.calculate.upd_calculate', compact('calculate', 'shop'));
            } else {
                return redirect()->route('show_not_allow')->send();
            }
        }

    }

    public function upd_statement(Request $request)
    {
        if (Perm::get_function_access(42) || Perm::get_function_access(43)) {
            $id = $request->id;
            $calculate = DB::table('calculate')->where('calculate_id', $id)->first();
            $shop_id = $calculate->shop_id;
            $issamecreatecalculate = $this->issamecreatecalculate($shop_id);
            if ($issamecreatecalculate) {
            $calculate = DB::table('calculate')->where('calculate_id', $id)->first();
            $shop_id = $calculate->shop_id;
            $calculate_month_val = $calculate->calculate_month_val;
            $shop = DB::table('shop')->where('shop_id', $shop_id)->first();
            $last_calculate = DB::table('calculate_detail')->where('calculate_id', $id)->latest("calculate_detail_id")->first();
            if (isset($last_calculate)) {
                $last_calculate_month_remain = $last_calculate->calculate_month_remain;
                $last_calculate_month_pay = $last_calculate->calculate_month_pay;
            } else {
                $last_calculate_month_remain = $calculate_month_val;
                $last_calculate_month_pay = 0;
            }
            return view('dashboard.calculate.upd_statement', compact('calculate', 'shop', 'last_calculate_month_remain', 'last_calculate_month_pay'));
            } else {
                return redirect()->route('show_not_allow')->send();
            }
        }
    }


    public function upd_calculate_det(Request $request)
    {
        if (Perm::get_function_access(42)) {
            $id = $request->id;
            $calculate = DB::table('calculate_detail')->where('calculate_detail_id', $id)->first();
            return view('dashboard.calculate.upd_calculate_det', compact('calculate'));
        }
    }

    public function updstatement(Request $request)
    {
        $calculate_month_desc = $request->calculate_month_desc;
        $array_name = explode("-", $calculate_month_desc);
        $calculate_month_m = $array_name[0];
        $calculate_month_y = $array_name[1];
        $calculate_id = $request->calculate_id_db;
        $calculate_month_m = $request->calculate_month_desc;
        $calculate_month_y = $request->calculate_month_y;
        $attributeNames = array(
            'shop_id' => 'اسم المحل',
            'calculate_month_desc' => 'شهر الدفع',
            'calculate_month_m' => 'سنة الدفع',
            'calculate_month_y' => 'شهر الدفع',
            'calculate_month_val' => 'المبلغ المطلوب',
            'calculate_month_pay' => 'المبلغ المدفوع',
            'calculate_month_remain' => 'المبلغ المتبقي',
        );
        $validator = Validator::make($request->all(), [
            'shop_id' => ['required', 'integer',
                /*  Rule::unique("calculate")->where(
                      function ($query) use ($request) {
                          return $query->where(
                              [
                                  ["shop_id", "=", $request->shop_id],
                                  ["calculate_month_desc", "=", $request->calculate_month_desc]
                              ]
                          );
                      }),*/
            ],
            'calculate_month_desc' => ['required'],
            // 'calculate_month_val' => ['required','integer','min:10000','min:10000','not_in:0'],
            // 'note' => ['required','not_in:0'],
            'calculate_month_val' => ['required', 'min:1', 'not_in:0', 'gt:0'],
            'calculate_month_pay' => ['required', 'min:1', request()->filled('calculate_month_val') ? 'lte:calculate_month_val' : ''],
            'calculate_month_remain' => ['required', 'min:0', request()->filled('calculate_month_val') ? 'lte:calculate_month_val' : ''],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {
            /* $calculate_month_val=$request->calculate_month_val;
             $calculate_month_pay=$request->calculate_month_pay;
             $calculate_month_remain=$calculate_month_val-$calculate_month_pay;
             $calculate_id= DB::table('calculate')->insertGetId([
                 'shop_id' => $request->shop_id,
                 'calculate_month_desc' => $request->calculate_month_desc,
                 'calculate_month_m' => $calculate_month_m,
                 'calculate_month_y' => $calculate_month_y,
                 'calculate_month_val' => $calculate_month_val,
                 'calculate_month_pay' => $calculate_month_pay,
                 'calculate_month_remain' => $calculate_month_remain,
                 'note' => $request->note,
                 'created_at' =>  Carbon::now(),
                 'create_user' =>  Auth::user()->id,

             ]);*/
            if ($calculate_id != '') {
                $calculate_month_val = $request->calculate_month_val;
                $calculate_month_pay = $request->calculate_month_pay;
                $calculate_month_remain = $calculate_month_val - $calculate_month_pay;
                $result_upload = DB::table('calculate_detail')->insertGetId([
                    'calculate_id' => $calculate_id,
                    'calculate_month_val' => $calculate_month_val,
                    'calculate_month_pay' => $calculate_month_pay,
                    'calculate_month_remain' => $calculate_month_remain,
                    'note' => $request->note,
                    'created_at' => Carbon::now(),
                    'create_user' => Auth::user()->id,
                ]);

                /*          $result1= DB::table('calculate')
  ->where('calculate_id',$calculate_id)
  ->update([
  'calculate_month_remain' => $calculate_month_remain,
  'calculate_month_pay' => $calculate_month_pay,
  'calculate_month_val' => $calculate_month_val,
  ]);*/
                $ERROR_FLAG = 0;
                $result['status'] = $calculate_id;
                $result['message_out'] = 'تم الحفظ بنجاح';
            } else {
                $result['status'] = false;
                $result['message_out'] = 'لم يتم الحفظ';

            }

        }
        // }
        return response()->json($result);
    }


    public function store(Request $request)
    {
        if (Perm::get_function_access(39)) {
            $shop_id = $request->shop_id;
            $issamecalculatesins = $this->issamecalculatesins($shop_id);
            if ($issamecalculatesins) {
                $calculate_month_desc = $request->calculate_month_desc;
                $array_name = explode("-", $calculate_month_desc);
                $calculate_month_m = $array_name[0];
                $calculate_month_y = $array_name[1];
                $attributeNames = array(
                    'shop_id' => 'اسم المحل',
                    'calculate_month_desc' => 'شهر الدفع',
                    'calculate_month_val' => 'المبلغ المطلوب',
                    'calculate_month_pay' => 'المبلغ المدفوع',
                    'calculate_month_remain' => 'المبلغ المتبقي',
                );
                $validator = Validator::make($request->all(), [
                    'shop_id' => ['required', 'integer',
                        Rule::unique("calculate")->where(
                            function ($query) use ($request) {
                                return $query->where(
                                    [
                                        ["shop_id", "=", $request->shop_id],
                                        ["is_deleted", "=", '0'],

                                        ["calculate_month_desc", "=", $request->calculate_month_desc]
                                    ]
                                );
                            }),
                    ],
                    'calculate_month_desc' => ['required'],
                    'calculate_month_val' => ['required',],
                    'calculate_month_pay' => ['required', 'min:1', request()->filled('calculate_month_val') ? 'lte:calculate_month_val' : ''],
                    'calculate_month_remain' => ['required', 'min:0', request()->filled('calculate_month_val') ? 'lte:calculate_month_val' : ''],
                ]);
                $validator->setAttributeNames($attributeNames);
                if ($validator->fails()) {
                    $result['status'] = false;
                    $result['message'] = $validator->errors();
                    $result['message_out'] = '';

                } else {
                    $calculate_month_val = $request->calculate_month_val;
                    $calculate_month_pay = $request->calculate_month_pay;
                    $calculate_month_remain = $calculate_month_val - $calculate_month_pay;
                    $calculate_id = DB::table('calculate')->insertGetId([
                        'shop_id' => $request->shop_id,
                        'calculate_month_desc' => $request->calculate_month_desc,
                        'calculate_month_m' => $calculate_month_m,
                        'calculate_month_y' => $calculate_month_y,
                        'calculate_month_val' => $calculate_month_val,
                        'note' => $request->note,
                        'created_at' => Carbon::now(),
                        'create_user' => Auth::user()->id,

                    ]);
                    if ($calculate_id != '') {
                        $result_upload = DB::table('calculate_detail')->insertGetId([
                            'calculate_id' => $calculate_id,
                            'calculate_month_val' => $calculate_month_val,
                            'calculate_month_pay' => $calculate_month_pay,
                            'calculate_month_remain' => $calculate_month_remain,
                            'note' => $request->note,
                            'created_at' => Carbon::now(),
                            'create_user' => Auth::user()->id,
                        ]);
                        $ERROR_FLAG = 0;
                        $result['status'] = $calculate_id;
                        $result['message_out'] = 'تم الحفظ بنجاح';
                    } else {
                        $result['status'] = false;
                        $result['message_out'] = 'لم يتم الحفظ';

                    }
                }
            } else {
                $result['status'] = false;
                $result['message_out'] = 'لم يتم الحفظ - المحل ليس من ضمن مجموعتك';
            }
            return response()->json($result);

        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function updstore(Request $request)
    {
        if (Perm::get_function_access(41)) {
            $shop_id = $request->shop_id;
            $issamecreatecalculate = $this->issamecreatecalculate($shop_id);
            if ($issamecreatecalculate) {
                $id = $request->calculate_id_db;
                $calculate_month_desc = $request->calculate_month_desc;
                $array_name = explode("-", $calculate_month_desc);
                $calculate_month_m = $array_name[0];
                $calculate_month_y = $array_name[1];
                $calculate_month_val = $request->calculate_month_val;
                $total_payment = DB::table('calculate_detail')->where("calculate_id", "=", $id)->sum('calculate_month_pay');
                if ($calculate_month_val < $total_payment) {
                    $result['status'] = false;
                    $result['message'] = '';
                    $result['message_out'] = ' لا يمكن تعديل يجب ان يكون مبلغ الجديد اكبر من الجمالي الدفع' . $total_payment;

                } else {
                    $attributeNames = array(
                        'shop_id' => 'اسم المحل',
                        'calculate_month_desc' => 'شهر الدفع',
                        'calculate_month_val' => 'المبلغ المطلوب',
                    );
                    $validator = Validator::make($request->all(), [
                        'shop_id' => ['required', 'integer',
                            Rule::unique("calculate")->where(
                                function ($query) use ($request) {
                                    return $query->where(
                                        [
                                            ["is_deleted", "=", 0],
                                            ["shop_id", "=", $request->shop_id],
                                            ["calculate_month_desc", "=", $request->calculate_month_desc],
                                            ["calculate_id", "!=", $request->calculate_id_db]
                                        ]
                                    );
                                }),

                        ],
                        'calculate_month_desc' => ['required'],
                        'calculate_month_val' => ['required',
                        ],

                    ]);
                    $validator->setAttributeNames($attributeNames);
                    if ($validator->fails()) {
                        $result['status'] = false;
                        $result['message'] = $validator->errors();
                        $result['message_out'] = '';

                    } else {
                        /*
                        $old_calculate_month_val = DB::table('calculate')->select('calculate_month_val')->where( "calculate_id", "=", $id)->value('calculate_month_val');
                        $total_payment = DB::table('calculate_detail')->where( "calculate_id", "=", $id)->sum('calculate_month_pay');
                        */
                        /*
                        $last_calculate =DB::table('calculate_detail')->where('calculate_id',$calculate_id)->latest("calculate_detail_id")->first();
                        $calculate_detail_id=$last_calculate->calculate_detail_id;
                        $calculate_month_remain=$last_calculate->calculate_month_remain;*/
                        $old_calculate_month_val = DB::table('calculate')->select('calculate_month_val')->where("calculate_id", "=", $id)->value('calculate_month_val');
                        (float)$incre = $request->calculate_month_val - $old_calculate_month_val;
                        $rs_stmt1 = " update calculate_detail   set
calculate_month_val = calculate_month_val + $incre,
calculate_month_remain = calculate_month_val-calculate_month_pay

where  1=1 and calculate_id=$id  ";
                        DB::statement($rs_stmt1);
                        /*
                        DB::statement('UPDATE calculate_detail SET
                         calculate_month_val = calculate_month_val +$incre,
                         calculate_month_remain = (calculate_month_val +$incre)-calculate_month_pay
                          WHERE calculate_id = $id');*/
                        /*
                        $Rpodetail =DB::table('calculate_detail')
                                     ->where('calculate_id',$id)
                                     ->update([
                                         'calculate_month_val' => DB::raw('calculate_month_val + ' . $incre->number),
                                         'calculate_month_remain' =>  DB::raw ("calculate_month_val +$incre") - DB::raw("calculate_month_pay")    ,
                                         'note' => $request->note,
                                         'updated_at' =>  Carbon::now(),
                                         'updated_user' =>  Auth::user()->id,
                                     ]);*/
                        $result2 = DB::table('calculate')
                            ->where('calculate_id', $id)
                            ->update([
                                'shop_id' => $request->shop_id,
                                'calculate_month_desc' => $request->calculate_month_desc,
                                'calculate_month_m' => $calculate_month_m,
                                'calculate_month_y' => $calculate_month_y,
                                'calculate_month_val' => $request->calculate_month_val,
                                'note' => $request->note,
                                'updated_at' => Carbon::now(),
                                'updated_user' => Auth::user()->id,

                            ]);
                        /*
                        $last_calculate =DB::table('calculate_deatil')->where('calculate_id',$calculate_id)->latest("calculate_detail_id")->first();
                        $calculate_detail_id=$last_calculate->calculate_detail_id;
                        $calculate_month_remain=$last_calculate->calculate_month_val ;
                        $result1= DB::table('calculate_detail')
                        ->where('calculate_detail_id',$calculate_detail_id)
                        ->update(['calculate_month_remain' => $calculate_month_pay+$calculate_month_remain]);
                        */
                        $ERROR_FLAG = 0;
                        $result['status'] = 1;
                        $result['message_out'] = 'تم الحفظ بنجاح';
                    }

                }
                return response()->json($result);
            }
        }
    }
}
