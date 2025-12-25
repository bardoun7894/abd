<?php
namespace App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Financial;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;
use Illuminate\Support\Str;
class FinancialController extends Controller
{
    use ApimtitTrait;
    public function __construct()
    {
        $this->middleware('ishaveaccess:3');
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
        if (Perm::get_function_access(20)) {
            $page_title = 'إضافة مصروف عامل';
            $sel_financial = array("page_title");
            $work_place = DB::table('work_place')->get();
            return view('dashboard.financial.index', compact('work_place', $sel_financial));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function create()
    {
        $sel_financial = "1";
        $sub_add_financial = "1";
        $page_title = 'ادخال بيانات المدفوعات الشهرية';
        $sel_financial = array("sel_financial", "page_title");
        return view('dashboard.financial.create', compact($sel_financial));
    }


    public function storepmonth(Request $request)
    {
        $financial_month_desc = $request->financial_month_desc;
        $array_name = explode("-", $financial_month_desc);
        $financial_month_m = $array_name[0];
        $financial_month_y = $array_name[1];
        $attributeNames = array(
            'financial_month_desc' => 'شهر الدفع',
            'financial_month_val' => 'المبلغ',

        );
        $validator = Validator::make($request->all(), [
            'financial_month_desc' => ['required', 'unique:financial_month', 'max:255'],
            'financial_month_val' => ['required', 'integer'],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {

            $user = DB::table('financial_month')->insertGetId([
                'financial_month_desc' => $request->financial_month_desc,
                'financial_month_val' => $request->financial_month_val,
                'financial_month_y' => $financial_month_y,
                'financial_month_m' => $financial_month_m,
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
        $financial_month_id = $request->financial_month_id_db;

        $financial_month_desc = $request->financial_month_desc;
        $array_name = explode("-", $financial_month_desc);
        $financial_month_m = $array_name[0];
        $financial_month_y = $array_name[1];
        $attributeNames = array(
            'financial_month_desc' => 'شهر الدفع',
            'financial_month_val' => 'المبلغ',

        );
        $validator = Validator::make($request->all(), [

            'financial_month_val' => ['required', 'integer'],
            'financial_month_desc' => ['required', 'max:255', Rule::unique('financial_month', 'financial_month_desc')->ignore($financial_month_id, 'financial_month_id')],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {
            $ERROR_FLAG = 0;
            $user_photo = '';
            $result2 = DB::table('financial_month')
                ->where('financial_month_id', $financial_month_id)
                ->update([
                    'financial_month_desc' => $request->financial_month_desc,
                    'financial_month_val' => $request->financial_month_val,
                    'financial_month_y' => $financial_month_y,
                    'financial_month_m' => $financial_month_m,
                    'note' => $request->note,]);
            //  $id = DB::getPdo()->lastInsertId();


            /*   $result2=User::where('financial_month_id',$financial_month_id_db)->update([
                  'financial_month_desc' => $request->financial_month_desc,
                  'financial_month_val' => $request->financial_month_val,
                  'financial_month_y' => $financial_month_y,
                  'financial_month_m' => $financial_month_m,
                              ]);*/
            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';

        }
        return response()->json($result);

    }


    public function viewpmonth()
    {
        $page_title = 'إدارة مصاريف العمال';
        return view('dashboard.financial.view_pmonth', compact('page_title'));
    }


    public function pmonth_tbl(Request $request)
    {
        if ($request->ajax()) {
            return view('dashboard.financial.tbl_pmonth');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_pmonth(Request $request)
    {
        $financial_month_desc = $request->financial_month_desc;
        if ($financial_month_desc != '') {
            $array_name = explode("-", $financial_month_desc);
            $financial_month_m = $array_name[0];
            $financial_month_y = $array_name[1];
        } else {
            $financial_month_m = '';
            $financial_month_y = '';
        }

        $list_total = Financial::serachspendcount($financial_month_m, $financial_month_y);
        $list = Financial::serachspenddata($financial_month_m, $financial_month_y);
        $data = array();
        $no = $_POST['start'];
        $i = 0;
        foreach ($list as $x) {
            $no++;
            $i++;
            $row = array();
            $row[] = $i;

            $row[] = $x->financial_month_desc;
            $row[] = $x->financial_month_y;
            $row[] = $x->financial_month_m;
            $row[] = $x->financial_month_val;
            $row[] = $x->note;
            $opt = '<div class="btn-group btn-group-sm " role="group"  >';
            if ("1" == "1") {
                $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_pmonth" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"    data-url=' . "'" . route('dashboard.financial.upd_pmonth') . "'" . '            onclick="upd_pmonth(' . "'" . $x->financial_month_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
            }
            if ("1" == "1") {
                $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_pmonth(' . "'" . $x->financial_month_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
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
        $delete = DB::delete('delete from financial_month where financial_month_id = ?', [$id]);
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
        $financial = DB::table('financial_month')->where('financial_month_id', $id)->first();
        return view('dashboard.financial.upd_pmonth', compact('financial'));
    }


    public function views()
    {
        if (Perm::get_function_access(21)||Perm::get_function_access(22)||Perm::get_function_access(23)||Perm::get_function_access(24)||Perm::get_function_access(25)) {
        $work_place = DB::table('work_place')->get();
        //  $manager = DB::table('manager')->get();
        $manager = $this->get_manager();
        $page_title = 'إدارة مصاريف العمال';
        return view('dashboard.financial.view', compact('work_place', 'manager', 'page_title'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }



    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(21) || Perm::get_function_access(22) || Perm::get_function_access(23) || Perm::get_function_access(24) || Perm::get_function_access(25))) {
            return view('dashboard.financial.tbl_financial');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_financial(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(21)||Perm::get_function_access(22)||Perm::get_function_access(23)||Perm::get_function_access(24)||Perm::get_function_access(25))) {
            $worker_id = $request->worker_id;
        $manager_id = $request->manager_id;
        $financial_month_desc = $request->financial_month_desc;
        if ($financial_month_desc != '') {
            $array_name = explode("-", $financial_month_desc);
            $financial_month_m = $array_name[0];
            $financial_month_y = $array_name[1];
        } else {
            $financial_month_m = '';
            $financial_month_y = '';
        }
        $list_totl = Financial::sumspendcountdesc($financial_month_m, $financial_month_y, $worker_id, $manager_id);
        $sum_c1 = 0;
        $sum_count_statement = 0;
        $sum_sum_det_financial_month_pay_All = 0;
        $sum_xx = 0;
        foreach ($list_totl as $x_sum) {
            $c1 = $x_sum->c1;
            $count_statement = $x_sum->count_statement;
            $sum_det_financial_month_pay_all = $x_sum->sum_det_financial_month_pay;
            $xx = $x_sum->xx;
            $sum_c1 += $c1;
            $sum_count_statement += $count_statement;
            $sum_sum_det_financial_month_pay_All += $sum_det_financial_month_pay_all;
            $sum_xx += $xx;

        }
        $list_total = Financial::serachspendcountdesc($financial_month_m, $financial_month_y, $worker_id, $manager_id);
        $list = Financial::serachspenddatadesc($financial_month_m, $financial_month_y, $worker_id, $manager_id);


        $data = array();
        $no = $_POST['start'];
        $i = 0;
        $sum_financial_month_val = 0;
        $sum_sum_det_financial_month_pay = 0;
        $sum_sum_det_financial_month_remain = 0;

        foreach ($list as $x) {
            $no++;
            $i++;


            $row = array();
            $row[] = $i;
            $financial_month_val = $x->financial_month_val;
            $sum_det_financial_month_pay = $x->sum_det_financial_month_pay;
            $sum_det_financial_month_remain = $financial_month_val - $sum_det_financial_month_pay;

            if ($sum_det_financial_month_remain == '0') {
                $financial_desc = '<span class="ms-2 badge badge-light-success fw-bold">مكتمل الدفع</span>';
            } else {
                $financial_desc = '<span class="ms-2 badge badge-light-danger fw-bold">متبقي</span>';
            }

            if ($x->ssn != '') {
                $ssn_desc = '<br><span class="ms-2 text-danger fw-bold">' . $x->ssn . '</span>';
            } else {
                $ssn_desc = '';
            }

            $sum_financial_month_val += $financial_month_val;
            $sum_sum_det_financial_month_pay += $sum_det_financial_month_pay;
            $sum_sum_det_financial_month_remain += $sum_det_financial_month_remain;


            $row[] = $x->worker_name.$ssn_desc;
            $row[] = $x->manager_name;

            $row[] = $x->financial_month_desc;
            $row[] = $financial_desc;
            $row[] = $financial_month_val;
            $row[] = $sum_det_financial_month_pay;
            $row[] = $sum_det_financial_month_remain;
            $row[] = $x->count_statement;
            $row[] = $x->note;
            $row[] = $x->name;
            $row[] = Carbon::parse($x->created_at)->format('d-m-Y');

            if (Perm::get_function_access(22)||Perm::get_function_access(23)||Perm::get_function_access(24)||Perm::get_function_access(25)) {
            $opt = '<div class="btn-group btn-group-sm " role="group"  >';
                if (Perm::get_function_access(22)) {
                $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_financial" style="margin-left: .5rem"    data-url=' . "'" . route('dashboard.financial.upd_financial') . "'" . ' onclick="upd_financial(' . "'" . $x->financial_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
            }
                if (Perm::get_function_access(23)|| Perm::get_function_access(24)) {
                $opt .= '<a class="btn btn-sm btn-info btn-icon btn-icon-sm  upd_statement" style="margin-left: .5rem"    data-url=' . "'" . route('dashboard.financial.upd_statement') . "'" . ' onclick="upd_statement(' . "'" . $x->financial_id . "'" . ')"> <i class="fab fa-cc-amazon-pay fa-fw"></i></a>';
            }
                if (Perm::get_function_access(25)) {
                $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-left: .5rem" onclick="del_financial(' . "'" . $x->financial_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
            }
            $opt .= '<a class="btn btn-sm btn-dark btn-icon btn-icon-sm  financial_note_history" style="margin-left: .5rem;"    data-url=' . "'" . route('dashboard.financial.financial_note_history') . "'" . ' onclick="financial_note_history(' . "'" . $x->financial_id . "'" . ')"> <i class="fas fa-history fa-fw "></i></a>';

            $opt .= '</div>';
            $row[] = $opt;
        }
            $data[] = $row;
        }
        $output = array(
            "sum_c1" => $sum_c1,
            "sum_count_statement" => $sum_count_statement,
            "sum_sum_det_financial_month_pay_All" => $sum_sum_det_financial_month_pay_All,
            "sum_xx" => $sum_xx,

            "draw" => $_POST['draw'],
            "recordsTotal" => $list_total,
            "recordsFiltered" => $list_total,
            "data" => $data);
        echo json_encode($output);
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }











    public function financial_note_history(Request $request)
    {
        if (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79)) {
            $id = $request->id;
                $financial = DB::table('financial')->where('financial_id', $id)->first();
                $sub_add_worker = "1";
                $page_title = 'إنشاء ملف ';
                $note_type = DB::table('note_type')->get();
                $const = array("note_type", "page_title");
                return view('dashboard.financial.financial_note_history', compact('financial', $const));

        }
    }

    public function tbl_history(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79))) {
            return view('dashboard.financial.tbl_history');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_history(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79))) {
            $financial_id = $request->financial_id;
            $list_total = financial::serachhistorycount($financial_id);
            $list = financial::serachhistorydata($financial_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;


                $financial_month_val=$x->financial_month_val;
                if($financial_month_val!=''){
                    $financial_month_val=$x->financial_month_val;
                }


                $old_financial_month_val=$x->old_financial_month_val;
                if($old_financial_month_val==''){
                    $old_financial_month_val=$financial_month_val;
                }

                $financial_month_pay=$x->financial_month_pay;
                if($financial_month_pay!=''){
                    $financial_month_pay=$x->financial_month_pay;
                    $old_financial_month_pay=$financial_month_pay;
                }
                else{
                        $financial_month_pay='';
                        $old_financial_month_pay=$financial_month_pay;
                }

                if($financial_month_pay!=''){
                    $financial_month_pay=$x->financial_month_pay;
                    $old_financial_month_pay=$financial_month_pay;
                }
                else{
                        $financial_month_pay='';
                        $old_financial_month_pay=$financial_month_pay;
                }


              $old_financial_month_remain=  $x->old_financial_month_remain;
                if($old_financial_month_remain==''){
                    $remain_pay=$old_financial_month_val-(int)$financial_month_pay;
                }
                else{
                    $remain_pay= $x->financial_month_remain;

                }


                if(Str::contains($x->note, 'تم تعديل بيانات مصاريف العمال ')) {
                    $xxxxxx='1';
                }
                else{
                    $xxxxxx='0';

                }
                if($xxxxxx==1){
                    $remain_pay_new= $financial_month_val-$financial_month_pay;

                }
                else{
                    $remain_pay_new= $remain_pay;

                }




                $row = array();
                $row[] = $i;
                $row[] = $financial_month_val;
                $row[] = $old_financial_month_val;
                $row[] = $financial_month_pay;
                $row[] = $old_financial_month_pay;
          //  $row[] = $remain_pay;
        //    $row[] = $remain_pay_new;

               // $row[] = $x->old_financial_month_remain;
                $row[] =$x->note;
                $row[] = $x->old_note;
                $row[] = $x->name;
                $row[] = Carbon::parse($x->change_at)->format('d-m-Y');
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

















    public function tbl_detail(Request $request)
    {
        if (Perm::get_function_access(23)|| Perm::get_function_access(24)) {
            if ($request->ajax()) {
                return view('dashboard.financial.tbl_financial_detail');
            } else {
                return "Request Not Ajax Type";
            }
        }
    }


    public function ajax_search_financial_detail(Request $request)
    {
        if (Perm::get_function_access(23) || Perm::get_function_access(24)) {

            $financial_id = $request->financial_id;
            $financial = DB::table('financial')->where('financial_id', $financial_id)->first();
            $worker_id = $financial->worker_id;
//        $ishavegroupworker = $this->ishavegroupworker($worker_id);
//        if ($ishavegroupworker) {
            $issamecreatefinancial = $this->issamecreatefinancial($worker_id);
            if ($issamecreatefinancial) {

                $list_total = Financial::serachspendcountdet($financial_id);
                $list = Financial::serachspenddet($financial_id);


                $data = array();
                $no = $_POST['start'];
                $i = 0;
                foreach ($list as $x) {
                    $no++;
                    $i++;
                    $row = array();
                    $row[] = $i;
                    $financial_month_val = $x->financial_month_val;


                    $row[] = $x->worker_name;
                    $row[] = $x->financial_month_desc;
//$row[] =$financial_month_val;
                    $row[] = $x->det_financial_month_val;

                    $row[] = $x->det_financial_month_pay;
                    $row[] = $x->det_financial_month_remain;
                    $row[] = $x->det_note;
                    $row[] = $x->det_create_user_name;
                    $row[] = Carbon::parse($x->det_created_at)->format('d-m-Y');
                    if ( Perm::get_function_access(24)) {
                        $opt = '<div class="btn-group btn-group-sm " role="group"  >';
                            $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_financial_det(' . "'" . $x->financial_detail_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
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
        }
    }


    function del_financial(Request $request)
    {
        if (Perm::get_function_access(25)) {
        $id = $request->id;
        $financial = DB::table('financial')->where('financial_id', $id)->first();
        $worker_id = $financial->worker_id;
            $issamecreatefinancial = $this->issamecreatefinancial($worker_id);
        if ($issamecreatefinancial) {

            $delete = DB::table('financial')
                ->where('financial_id', $id)
                ->update([
                    'is_deleted' => 1,
                    'deleted_at' => Carbon::now(),
                    'deleted_user' => Auth::user()->id,

                ]);
            if ($delete) {
                $result22 = DB::table('financial_detail_history')->insertGetId([
                    'financial_id' =>$financial->financial_id,
                    'financial_month_desc'=>$financial->financial_month_desc,
                    'financial_month_val'=>$financial->financial_month_val,
                    'old_financial_month_val'=>$financial->financial_month_val,
                    'note' => 'تم حذف جميع الاقساط'.'<br><br>'. $request->note,
                    'old_note' => $financial->note,
                    'create_user' => $financial->create_user,
                    'created_at' => $financial->created_at,
                    'updated_at' => $financial->updated_at,
                    'updated_user' => $financial->updated_user,
                    'change_user' => Auth::user()->id,
                    'change_at' => Carbon::now(),
                ]);

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

    function del_financial_det(Request $request)
    {
        if (Perm::get_function_access(24)) {

            $id = $request->id;
            $financial_det = DB::table('financial_detail')->where('financial_detail_id', $id)->first();
            $financial_id = $financial_det->financial_id;
            $financial_month_pay = $financial_det->financial_month_pay;


            $financial = DB::table('financial')->where('financial_id', $financial_id)->first();
            $worker_id = $financial->worker_id;
//        $ishavegroupworker = $this->ishavegroupworker($worker_id);
//        if ($ishavegroupworker) {
            $issamecreatefinancial = $this->issamecreatefinancial($worker_id);
            if ($issamecreatefinancial) {

                $last_financial = DB::table('financial_detail')->where('financial_id', $financial_id)->latest("financial_detail_id")->first();
                $financial_detail_id = $last_financial->financial_detail_id;
                $financial_month_remain = $last_financial->financial_month_remain;

                $result1 = DB::table('financial_detail')
                    ->where('financial_detail_id', $financial_detail_id)
                    ->update(['financial_month_remain' => $financial_month_pay + $financial_month_remain]);

                    $old_financial_id= $financial->financial_id ;
                    $financial_month_desc = $financial->financial_month_desc;
                    $old_financial_month_val = $financial_det->financial_month_val;
                    $old_note = $financial_det->note;
                    $old_create_user  = $financial_det->create_user;
                    $old_created_at = $financial_det->created_at;
                    $old_updated_at = $financial_det->updated_at;
                    $old_updated_user = $financial_det->updated_user;


                $delete = DB::table('financial_detail')->where('financial_detail_id', $financial_detail_id)->delete();
                if ($delete) {
                    $result22 = DB::table('financial_detail_history')->insertGetId([
                        'financial_id' => $old_financial_id,
                        'financial_month_desc' => $financial_month_desc,
                        'financial_month_val' => $old_financial_month_val,
                        'old_financial_month_val' => $old_financial_month_val,
                        'note' => 'تم حذف القسط'.'<br><br>'. $request->note,
                        'old_note' => $old_note,
                        'create_user' => $old_create_user,
                        'created_at' => $old_created_at,
                        'updated_at' => $old_updated_at,
                        'updated_user' => $old_updated_user,
                        'change_user' => Auth::user()->id,
                        'change_at' => Carbon::now(),

                    ]);

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
    }


    public function upd_financial(Request $request)
    {
        if (Perm::get_function_access(22)) {
            $id = $request->id;
            $financial = DB::table('financial')->where('financial_id', $id)->first();
            $worker_id = $financial->worker_id;
            $issamecreatefinancial = $this->issamecreatefinancial($worker_id);
            if ($issamecreatefinancial) {
                $worker = DB::table('workers')->where('worker_id', $worker_id)->first();
                return view('dashboard.financial.upd_financial', compact('financial', 'worker'));
            } else {
                return redirect()->route('show_not_allow')->send();
            }
        }
    }

    public function upd_statement(Request $request)
    {
        if (Perm::get_function_access(23)|| Perm::get_function_access(24)) {
        $id = $request->id;
        $financial = DB::table('financial')->where('financial_id', $id)->first();
        $worker_id = $financial->worker_id;
        $financial_month_val = $financial->financial_month_val;
//        $ishavegroupworker = $this->ishavegroupworker($worker_id);
//        if ($ishavegroupworker) {
            $issamecreatefinancial = $this->issamecreatefinancial($worker_id);
        if ($issamecreatefinancial) {
            $worker = DB::table('workers')->where('worker_id', $worker_id)->first();
            $last_financial = DB::table('financial_detail')->where('financial_id', $id)->latest("financial_detail_id")->first();
            if (isset($last_financial)) {
                $last_financial_month_remain = $last_financial->financial_month_remain;
                $last_financial_month_pay = $last_financial->financial_month_pay;
            } else {
                $last_financial_month_remain = $financial_month_val;
                $last_financial_month_pay = 0;
            }

            return view('dashboard.financial.upd_statement', compact('financial', 'worker', 'last_financial_month_remain', 'last_financial_month_pay'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
        }
    }


    public function upd_financial_det(Request $request)
    {
        if (Perm::get_function_access(23) ) {

            $id = $request->id;
            $financial = DB::table('financial_detail')->where('financial_detail_id', $id)->first();
            return view('dashboard.financial.upd_financial_det', compact('financial'));
        }
    }

    public function updstatement(Request $request)
    {

        $financial_month_desc = $request->financial_month_desc;
        $array_name = explode("-", $financial_month_desc);
        $financial_month_m = $array_name[0];
        $financial_month_y = $array_name[1];


        $financial_id = $request->financial_id_db;
        $financial_month_m = $request->financial_month_desc;
        $financial_month_y = $request->financial_month_y;


        $attributeNames = array(
            'worker_id' => 'اسم العامل',
            'financial_month_desc' => 'شهر الدفع',
            'financial_month_m' => 'سنة الدفع',
            'financial_month_y' => 'شهر الدفع',
            'financial_month_val' => 'المبلغ المطلوب',
            'financial_month_pay' => 'المبلغ المدفوع',
            'financial_month_remain' => 'المبلغ المتبقي',
        );
        $validator = Validator::make($request->all(), [
            'worker_id' => ['required', 'integer',

            ],
            'financial_month_desc' => ['required'],
            'financial_month_val' => ['required', 'min:1', 'not_in:0', 'gt:0'],
            'financial_month_pay' => ['required', 'min:1', request()->filled('financial_month_val') ? 'lte:financial_month_val' : ''],
            'financial_month_remain' => ['required', 'min:0', request()->filled('financial_month_val') ? 'lte:financial_month_val' : ''],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {

            if ($financial_id != '') {

                $financial_month_val = $request->financial_month_val;
                $financial_month_pay = $request->financial_month_pay;
                $financial_month_remain = $financial_month_val - $financial_month_pay;


                $result_upload = DB::table('financial_detail')->insertGetId([
                    'financial_id' => $financial_id,
                    'financial_month_val' => $financial_month_val,
                    'financial_month_pay' => $financial_month_pay,
                    'financial_month_remain' => $financial_month_remain,
                    'note' => $request->note,
                    'created_at' => Carbon::now(),
                    'create_user' => Auth::user()->id,
                ]);

	 $result22 = DB::table('financial_detail_history')->insertGetId([
        'financial_id' => $financial_id,
        'financial_month_val' => $financial_month_val,
        'financial_month_pay' => $financial_month_pay,
        'financial_month_remain' => $financial_month_remain,
        'note' => 'تم التعديل على بيانات القسط'.'<br><br>'. $request->note,
        'created_at' => Carbon::now(),
        'create_user' => Auth::user()->id,
        'change_user' => Auth::user()->id,
        'change_at' => Carbon::now(),

    ]);

                $ERROR_FLAG = 0;
                $result['status'] = $financial_id;
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
        if (Perm::get_function_access(20)) {
            $worker_id = $request->worker_id;
//            $ishavegroupworker = $this->ishavegroupworker($worker_id);
//            if ($ishavegroupworker) {
            $issamefinancialins = $this->issamefinancialins($worker_id);
            if ($issamefinancialins) {
                $financial_month_desc = $request->financial_month_desc;
                $array_name = explode("-", $financial_month_desc);
                $financial_month_m = $array_name[0];
                $financial_month_y = $array_name[1];
                $attributeNames = array(
                    'worker_id' => 'اسم العامل',
                    'financial_month_desc' => 'شهر الدفع',
                    'financial_month_val' => 'المبلغ المطلوب',
                    'financial_month_pay' => 'المبلغ المدفوع',
                    'financial_month_remain' => 'المبلغ المتبقي',
                );
                $validator = Validator::make($request->all(), [
                    'worker_id' => ['required', 'integer',
                        Rule::unique("financial")->where(
                            function ($query) use ($request) {
                                return $query->where(
                                    [
                                        ["worker_id", "=", $request->worker_id],
                                        ["is_deleted", "=", '0'],
                                        ["financial_month_desc", "=", $request->financial_month_desc]
                                    ]
                                );
                            }),
                    ],
                    'financial_month_desc' => ['required'],
                    'financial_month_val' => ['required',],
                    'financial_month_pay' => ['required', 'min:1', request()->filled('financial_month_val') ? 'lte:financial_month_val' : ''],
                    'financial_month_remain' => ['required', 'min:0', request()->filled('financial_month_val') ? 'lte:financial_month_val' : ''],
                ]);
                $validator->setAttributeNames($attributeNames);
                if ($validator->fails()) {
                    $result['status'] = false;
                    $result['message'] = $validator->errors();
                    $result['message_out'] = '';

                } else {
                    $financial_month_val = $request->financial_month_val;
                    $financial_month_pay = $request->financial_month_pay;
                    $financial_month_remain = $financial_month_val - $financial_month_pay;
                    $financial_id = DB::table('financial')->insertGetId([
                        'worker_id' => $worker_id,
                        'financial_month_desc' => $request->financial_month_desc,
                        'financial_month_m' => $financial_month_m,
                        'financial_month_y' => $financial_month_y,
                        'financial_month_val' => $financial_month_val,
                        'note' => $request->note,
                        'created_at' => Carbon::now(),
                        'create_user' => Auth::user()->id,

                    ]);
                    if ($financial_id != '') {

                        $result_upload = DB::table('financial_detail')->insertGetId([
                            'financial_id' => $financial_id,
                            'financial_month_val' => $financial_month_val,
                            'financial_month_pay' => $financial_month_pay,
                            'financial_month_remain' => $financial_month_remain,
                            'note' => $request->note,
                            'created_at' => Carbon::now(),
                            'create_user' => Auth::user()->id,

                        ]);


                        // $result22 = DB::table('financial_detail_history')->insertGetId([
                        //     'financial_id' => $financial_id,
                        //     'financial_month_desc' => $request->financial_month_desc,
                        //     'financial_month_val' => $financial_month_val,
                        //     'financial_month_pay' => $financial_month_pay,
                        //     'note' => $request->note,
                        //     'note' => 'تم اضافة بيانات مصاريف العمال جديدة'.'<br><br>'. $request->note,
                        //     'created_at' => Carbon::now(),
                        //     'create_user' => Auth::user()->id,
                        //     'change_user' => Auth::user()->id,
                        //     'change_at' => Carbon::now(),
                        // ]);



                        $ERROR_FLAG = 0;
                        $result['status'] = $financial_id;
                        $result['message_out'] = 'تم الحفظ بنجاح';
                    } else {
                        $result['status'] = false;
                        $result['message_out'] = 'لم يتم الحفظ';

                    }


                }

            } else {
                //return redirect()->route('show_not_allow')->send();
                $result['status'] = false;
                $result['message_out'] = 'ام يتم الحفظ - العامل ليس من ضمن مجموعتك';

            }
            return response()->json($result);

        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function updstore(Request $request)
    {
        if (Perm::get_function_access(22)) {
            $worker_id = $request->worker_id;
            $issamecreatefinancial = $this->issamecreatefinancial($worker_id);
            if ($issamecreatefinancial) {
                $id = $request->financial_id_db;
                $financial = DB::table('financial')->where('financial_id', $id)->first();





                $last_financial = DB::table('financial_detail')->where('financial_id', $id)->latest("financial_detail_id")->first();
                if (isset($last_financial)) {
                    $last_financial_month_remain = $last_financial->financial_month_remain;
                    $last_financial_month_pay = $last_financial->financial_month_pay;
                } else {
                    $last_financial_month_remain = $financial_month_val;
                    $last_financial_month_pay = 0;
                }








                $financial_month_desc = $request->financial_month_desc;
                $array_name = explode("-", $financial_month_desc);
                $financial_month_m = $array_name[0];
                $financial_month_y = $array_name[1];
                $financial_month_val = $request->financial_month_val;
                $total_payment = DB::table('financial_detail')->where("financial_id", "=", $id)->sum('financial_month_pay');
                if ($financial_month_val < $total_payment) {
                    $result['status'] = false;
                    $result['message'] = '';
                    $result['message_out'] = ' لا يمكن تعديل يجب ان يكون مبلغ الجديد اكبر من الجمالي الدفع' . $total_payment;
                } else {
                    $attributeNames = array(
                        'worker_id' => 'اسم العامل',
                        'financial_month_desc' => 'شهر الدفع',
                        'financial_month_val' => 'المبلغ المطلوب',
                    );
                    $validator = Validator::make($request->all(), [
                        'worker_id' => ['required', 'integer',
                            Rule::unique("financial")->where(
                                function ($query) use ($request) {
                                    return $query->where(
                                        [
                                            ["worker_id", "=", $request->worker_id],
                                            ["is_deleted", "=", '0'],

                                            ["financial_month_desc", "=", $request->financial_month_desc],
                                            ["financial_id", "!=", $request->financial_id_db]

                                        ]
                                    );
                                }),

                        ],
                        'financial_month_desc' => ['required'],
                        'financial_month_val' => ['required',
                        ],
                    ]);
                    $validator->setAttributeNames($attributeNames);
                    if ($validator->fails()) {
                        $result['status'] = false;
                        $result['message'] = $validator->errors();
                        $result['message_out'] = '';

                    } else {
                        /*
                        $old_financial_month_val = DB::table('financial')->select('financial_month_val')->where( "financial_id", "=", $id)->value('financial_month_val');
                        $total_payment = DB::table('financial_detail')->where( "financial_id", "=", $id)->sum('financial_month_pay');
                        */
                        /*
                        $last_financial =DB::table('financial_detail')->where('financial_id',$financial_id)->latest("financial_detail_id")->first();
                        $financial_detail_id=$last_financial->financial_detail_id;
                        $financial_month_remain=$last_financial->financial_month_remain;*/
                        $old_financial_month_val = DB::table('financial')->select('financial_month_val')->where("financial_id", "=", $id)->value('financial_month_val');
                        (float)$incre = $request->financial_month_val - $old_financial_month_val;
                        $rs_stmt1 = " update financial_detail   set
financial_month_val = financial_month_val + $incre,
financial_month_remain = financial_month_val-financial_month_pay

where  1=1 and financial_id=$id  ";
                        DB::statement($rs_stmt1);
                        /*
                        DB::statement('UPDATE financial_detail SET
                         financial_month_val = financial_month_val +$incre,
                         financial_month_remain = (financial_month_val +$incre)-financial_month_pay
                          WHERE financial_id = $id');*/
                        /*
                        $Rpodetail =DB::table('financial_detail')
                                     ->where('financial_id',$id)
                                     ->update([

                                         'financial_month_val' => DB::raw('financial_month_val + ' . $incre->number),
                                         'financial_month_remain' =>  DB::raw ("financial_month_val +$incre") - DB::raw("financial_month_pay")    ,

                                         'note' => $request->note,
                                         'updated_at' =>  Carbon::now(),
                                         'updated_user' =>  Auth::user()->id,

                                     ]);*/

                        $result2 = DB::table('financial')
                            ->where('financial_id', $id)
                            ->update([
                                'worker_id' => $request->worker_id,
                                'financial_month_desc' => $request->financial_month_desc,
                                'financial_month_m' => $financial_month_m,
                                'financial_month_y' => $financial_month_y,
                                'financial_month_val' => $request->financial_month_val,
                                'note' => $request->note,
                                'updated_at' => Carbon::now(),
                                'updated_user' => Auth::user()->id,

                            ]);



                            $last_financial_month_remain = $last_financial->financial_month_remain;
                            $last_financial_month_pay = $last_financial->financial_month_pay;







                        /*
                        $last_financial =DB::table('financial_deatil')->where('financial_id',$financial_id)->latest("financial_detail_id")->first();
                        $financial_detail_id=$last_financial->financial_detail_id;
                        $financial_month_remain=$last_financial->financial_month_val ;

                        $result1= DB::table('financial_detail')
                        ->where('financial_detail_id',$financial_detail_id)
                        ->update(['financial_month_remain' => $financial_month_pay+$financial_month_remain]);
                        */
                        $ERROR_FLAG = 0;
                        $result['status'] = 1;
                        $result['message_out'] = 'تم الحفظ بنجاح';
                    }

                }
            } else {
                //return redirect()->route('show_not_allow')->send();
                $result['status'] = false;
                $result['message_out'] = 'ام يتم الحفظ - العامل ليس من ضمن مجموعتك';
            }
            return response()->json($result);

        }
    }

}
