<?php

namespace App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;
class ExpenseController extends Controller
{
    use ApimtitTrait;
    public function __construct()
    {
        $this->middleware('ishaveaccess:9');
    }
    public function index()
    {
        if (Perm::get_function_access(59)) {
            $page_title = 'ادخال بيانات مصاريف تشغيلية  ';
            $manager = DB::table('manager')->get();
            $expense_type = DB::table('expense_type')->get();
            $const = array("manager", "expense_type", "page_title");
            return view('dashboard.expense.index', compact($const));
        }
    }
    public function load_expense_form(Request $request)
    {
        $expense_type_id = $request->expense_type_id;
        $desc = $request->desc;
        $expense_id = $request->expense_id;
        if ($desc == 1) {
            if ($expense_type_id == 1) {
               // $manager = DB::table('manager')->get();
                $expense_categoty = DB::table('expense_categoty')->get();
                $const = array( "expense_categoty");
                return view('dashboard.expense.expense_shop', compact($const));
            } else if ($expense_type_id == 2) {
                $manager = DB::table('manager')->get();
                $expense_categoty = DB::table('expense_categoty')->get();
                $const = array("manager", "expense_categoty");
                return view('dashboard.expense.expense_workall', compact($const));

            } else if ($expense_type_id == 3) {
              //  $manager = DB::table('manager')->get();
                $expense_categoty = DB::table('expense_categoty')->get();
                $const = array("expense_categoty");
                return view('dashboard.expense.expense_workspec', compact($const));
            }

        } else {
            if ($expense_type_id == 1) {
                $expense_id = $request->expense_id;
                $expense = DB::table('expense')->where('expense_id', $expense_id)->first();
                $shop_id = $expense->shop_id;
                if ($shop_id != '') {
                    $shop = DB::table('shop')->where('shop_id', $shop_id)->first();
                    $corr = 1;
                } else {
                    $shop = '';
                    $corr = 0;
                }
                $expense_categoty = DB::table('expense_categoty')->get();
                $const = array( "expense_categoty", "shop", 'corr');
                return view('dashboard.expense.expense_shop_upd', compact('expense', $const));
            } else if ($expense_type_id == 2) {
                $expense_id = $request->expense_id;
                $expense = DB::table('expense')->where('expense_id', $expense_id)->first();
                $manager = DB::table('manager')->get();
                $expense_categoty = DB::table('expense_categoty')->get();
                $const = array("manager", "expense_categoty");
                return view('dashboard.expense.expense_workall_upd', compact('expense', $const));

            } else if ($expense_type_id == 3) {
                $expense_id = $request->expense_id;
                $expense = DB::table('expense')->where('expense_id', $expense_id)->first();
                $worker_id = $expense->worker_id;
                if ($worker_id != '') {
                    $worker = DB::table('workers')->where('worker_id', $worker_id)->first();
                    $corr = 1;
                } else {
                    $worker = '';
                    $corr = 0;
                }
                $expense_categoty = DB::table('expense_categoty')->get();
                $const = array( "expense_categoty", "worker", 'corr');
                return view('dashboard.expense.expense_workspec_upd', compact('expense', $const));
            }
        }
    }

    public function views()
    {
        if (Perm::get_function_access(60) || Perm::get_function_access(61) || Perm::get_function_access(62)) {
            $manager = DB::table('manager')->get();
            $expense_type = DB::table('expense_type')->get();
            $expense_categoty = DB::table('expense_categoty')->get();
            $page_title = 'عرض بيانات مصاريف تشغيلية  ';
            return view('dashboard.expense.view', compact('manager', 'expense_type', 'expense_categoty', 'page_title'));
        }
    }


    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(60) || Perm::get_function_access(61) || Perm::get_function_access(62))) {
            return view('dashboard.expense.tbl_expense');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function sel_expense_list(Request $request)
    {
        $string = $request->q;
        $page = $request->page;
        //   $job= $request->job;
        $response = Expense::sel_expense_list($string, $page);
        echo json_encode($response);
    }


    public function ajax_search_expense(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(60) || Perm::get_function_access(61) || Perm::get_function_access(62))) {
            $expense_type_id = $request->expense_type_id;
            $expense_categoty_id = $request->expense_categoty_id;
            $expense_dt_from = $request->expense_dt_from;
            $expense_dt_to = $request->expense_dt_to;
            $manager_id = $request->manager_id;
            $worker_id = $request->worker_id;
            $shop_id = $request->shop_id;
            $list_total = Expense::serachspendcount($expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id);
            $list = Expense::serachspenddata($expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();


                if ( $x->municip_no!='') {
                    $municip_no_char = '<br>'.'<span class="ms-2 badge badge-light-danger fw-bold"> ' . $x->municip_no . '</span>';
                }
                else{
                    $municip_no_char='';
                }
                if ( $x->ssn!='') {
                    $ssn_char = '<br>'.'<span class="ms-2 badge badge-light-danger fw-bold"> ' . $x->ssn . '</span>';
                }
                else{
                    $ssn_char='';
                }
                $row[] = $i;
                $row[] = $x->expense_type_name;
                $row[] = $x->expense_categoty_name;
                $row[] = $x->expense_respon;
                $row[] = $x->expense_price;
                $row[] = $x->manager_name;
                $row[] = $x->shop_name.$municip_no_char;
                $row[] = $x->worker_name. $ssn_char;
                $row[] = $x->note;
                $row[] = $x->name;
                $row[] = Carbon::parse($x->created_at)->format('d-m-Y');
                if ( Perm::get_function_access(61) || Perm::get_function_access(62)) {
                    $opt = '<div class="btn-group btn-group-sm " role="group">';
                    if (Perm::get_function_access(61)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_expense" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.expense.upd_expense') . "'" . ' onclick="upd_expense(' . "'" . $x->expense_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(62)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_expense(' . "'" . $x->expense_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
                    }
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


    function del_expense(Request $request)
    {
        if (Perm::get_function_access(62)) {
            $id = $request->id;
            try {
                $delete = DB::delete('delete from expense where expense_id = ?', [$id]);
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


    public function upd_expense(Request $request)
    {
        if (Perm::get_function_access(61)) {

            $id = $request->id;
            $expense = DB::table('expense')->where('expense_id', $id)->first();
            $sub_add_expense = "1";
            $page_title = 'تعديل بيانات العمال';
            $manager = DB::table('manager')->get();
            $expense_type = DB::table('expense_type')->get();
            $expense_categoty = DB::table('expense_categoty')->get();
            $const = array("manager", "expense_type", "expense_categoty", "page_title");
            return view('dashboard.expense.upd_expense', compact('expense', $const));
        }
    }

    public function store(Request $request)
    {
        if (Perm::get_function_access(59)) {

            $expense_type_id=   $request->expense_type_id;
            if ($expense_type_id == 1 ) {
                $manager_db =DB::table('shop')->select('manager_id')->where('shop_id',$request->shop_id)->first();
                if ($manager_db) {
                $manager_id=$manager_db->manager_id;
                }
                else{
                    $manager_id='';
                }
            }
            if ($expense_type_id == 3 ) {
                $manager_db =DB::table('workers')->select('manager_id')->where('worker_id',$request->worker_id)->first();
                if ($manager_db) {
                    $manager_id=$manager_db->manager_id;
                    }
                    else{
                        $manager_id='';
                    }
            }
            if ($expense_type_id == 2 ) {
                $manager_id =$request->manager_id;
            }

            $request->merge([
                'manager_id' =>  $manager_id,
            ]);
            $attributeNames = array(
                'expense_type_id' => 'نوع المصروف',
                'expense_categoty_id' => 'التصنيف',
                'shop_id' => 'اسم المحل',
                'worker_id' => 'اسم العامل',
                'manager_id' => 'ليس له قائد  مجموعة',

            );
            $validator = Validator::make($request->all(), [
                'expense_type_id' => ['required', 'string'],
                'expense_categoty_id' => ['required'],
                'manager_id' => ['required'],
                'shop_id' => [Rule::requiredIf($request->expense_type_id == 1), 'nullable'],
                'worker_id' => [Rule::requiredIf($request->expense_type_id == 3), 'nullable']
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $expensefile_url = '';
                if ($request->hasFile('expensefile')) {
                    $expensefile_name = time() . '.' . $request->expensefile->extension();
                    $request->expensefile->move(public_path('uploads/users/images/'), $expensefile_name);
                    $expensefile_url = 'uploads/users/images/' . $expensefile_name;
                }
                $result2 = DB::table('expense')->insertGetId([
                    'expense_type_id' => $request->expense_type_id,
                    'expense_categoty_id' => $request->expense_categoty_id,
                    'shop_id' => $request->shop_id,
                    'worker_id' => $request->worker_id,
                    'expense_respon' => $request->expense_respon,
                    'expense_price' => $request->expense_price,
                    'expensefile' => $expensefile_url,
                    'manager_id' => $manager_id,
                    'note' => $request->note,
                    'created_at' => Carbon::now(),
                    'create_user' => Auth::user()->id,
                ]);

                if ($result2 != '') {
                    $result['status'] = $result2;
                    $result['message_out'] = 'تم الحفظ بنجاح';
                } else {
                    if (File::exists($expensefile_url)) {
                        File::delete($expensefile_url);
                    }
                    $message = 'لا يمكن الحفظ';
                    $result['status'] = false;
                    $result['message_out'] = $message;
                }
            }
            return response()->json($result);
        }
    }

    public function delete_file(Request $request)
    {
        $expense_id = $request->expense_id;
        $expensefile_url = $request->expensefile_url;
        $type = $request->type;
        if ($type == 'expense_attach') {
            if (File::exists($expensefile_url)) {
                File::delete($expensefile_url);
            }
            $result2 = DB::table('expense_attach')->where('expense_attach_id', $expense_id)->delete();
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;
            echo json_encode($result);
        }


        if ($type == 'expensefile') {
            if (File::exists($expensefile_url)) {
                File::delete($expensefile_url);
            }
            $result2 = DB::table('expense')
                ->where('expense_id', $expense_id)
                ->update([
                    'expensefile' => '',
                ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }

        if ($type == 'passportfile') {
            if (File::exists($expensefile_url)) {
                File::delete($expensefile_url);
            }
            $result2 = DB::table('expense')
                ->where('expense_id', $expense_id)
                ->update([
                    'passportfile' => '',
                ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }


    }















    public function updstore(Request $request)
    {
        if (Perm::get_function_access(61)) {
            $id = $request->expense_id_db;
            $expense_type_id=   $request->expense_type_id;
            if ($expense_type_id == 1 ) {
                $manager_db =DB::table('shop')->select('manager_id')->where('shop_id',$request->shop_id)->first();
                if ($manager_db) {
                $manager_id=$manager_db->manager_id;
                }
                else{
                    $manager_id='';
                }
            }
            if ($expense_type_id == 3 ) {
                $manager_db =DB::table('workers')->select('manager_id')->where('worker_id',$request->worker_id)->first();
                if ($manager_db) {
                    $manager_id=$manager_db->manager_id;
                    }
                    else{
                        $manager_id='';
                    }
            }
            if ($expense_type_id == 2 ) {
                $manager_id =$request->manager_id;
            }
            $request->merge([
                'manager_id' =>  $manager_id,
            ]);

        $attributeNames = array(
            'expense_type_id' => 'نوع المصروف',
            'expense_categoty_id' => 'التصنيف',
            'shop_id' => 'اسم المحل',
            'worker_id' => 'اسم العامل',
            'manager_id' => 'ليس له قائد  مجموعة',
            );
        $validator = Validator::make($request->all(), [
            'expense_type_id' => ['required', 'string'],
            'expense_categoty_id' => ['required'],
            'manager_id' => ['required'],

            'shop_id' => [Rule::requiredIf($request->expense_type_id == 1), 'nullable'],
            'worker_id' => [Rule::requiredIf($request->expense_type_id == 3), 'nullable']
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
        } else {
            $ERROR_FLAG = 0;


            $expensefile_url = '';
            if ($request->hasFile('expensefile')) {
                $expensefile_name = time() . '.' . $request->expensefile->extension();
                $request->expensefile->move(public_path('uploads/users/images/'), $expensefile_name);
                $expensefile_url = 'uploads/users/images/' . $expensefile_name;
                if (File::exists($request->expensefile_db)) {
                    File::delete($request->expensefile_db);
                }
            } else {
                $expensefile_url = $request->expensefile_db;
            }


            $result2 = DB::table('expense')
                ->where('expense_id', $id)
                ->update([
                    'expense_type_id' => $request->expense_type_id,
                    'expense_categoty_id' => $request->expense_categoty_id,
                    'shop_id' => $request->shop_id,
                    'worker_id' => $request->worker_id,
                    'expense_respon' => $request->expense_respon,
                    'expense_price' => $request->expense_price,
                    'expensefile' => $expensefile_url,
                    'manager_id' => $manager_id,
                    'note' => $request->note,
                    'updated_at' => Carbon::now(),
                    'update_user' => Auth::user()->id,
                ]);
            $result['status'] = $result2;
            $result['message_out'] = 'تم الحفظ بنجاح';
        }
        return response()->json($result);
    }
    }


}
