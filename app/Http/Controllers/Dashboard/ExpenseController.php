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
use App\Models\Shop;
use App\Models\Worker;

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
            //$manager = DB::table('manager')->get();
            $manager = $this->get_manager();
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
                //             //$manager = DB::table('manager')->get();
                $manager = $this->get_manager();
                $expense_categoty = DB::table('expense_categoty')->get();
                $const = array("expense_categoty");
                return view('dashboard.expense.expense_shop', compact($const));
            } else if ($expense_type_id == 2) {
                //$manager = DB::table('manager')->get();
                $manager = $this->get_manager();
                $expense_categoty = DB::table('expense_categoty')->get();
                $const = array("manager", "expense_categoty");
                return view('dashboard.expense.expense_workall', compact($const));
            } else if ($expense_type_id == 3) {
                //              //$manager = DB::table('manager')->get();
                $manager = $this->get_manager();
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
                $const = array("expense_categoty", "shop", 'corr');
                return view('dashboard.expense.expense_shop_upd', compact('expense', $const));
            } else if ($expense_type_id == 2) {
                $expense_id = $request->expense_id;
                $expense = DB::table('expense')->where('expense_id', $expense_id)->first();
                //$manager = DB::table('manager')->get();
                $manager = $this->get_manager();
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
                $const = array("expense_categoty", "worker", 'corr');
                return view('dashboard.expense.expense_workspec_upd', compact('expense', $const));
            }
        }
    }

    public function views()
    {
        if (Perm::get_function_access(60) || Perm::get_function_access(61) || Perm::get_function_access(62)) {
            //$manager = DB::table('manager')->get();
            $manager = $this->get_manager();
            $expense_type = DB::table('expense_type')->get();
            $expense_categoty = DB::table('expense_categoty')->get();
            $nowmonth =  Carbon::parse(now())->format('m-Y');
            $page_title = 'عرض بيانات مصاريف تشغيلية  ';
            return view('dashboard.expense.view', compact('manager', 'expense_type', 'nowmonth', 'expense_categoty', 'page_title'));
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
            $expense_month_desc = "";
            $type = $request->type;
            $det_expense_month_remain = $request->det_expense_month_remain;


            $list_total = Expense::serachspendcountall($expense_type_id, $expense_categoty_id, $expense_month_desc, $manager_id, $worker_id, $shop_id, $type, $det_expense_month_remain);
            // $list = Expense::serachspenddataall($expense_type_id, $expense_categoty_id,$expense_month_desc, $manager_id, $worker_id, $shop_id,$type,$det_expense_month_remain);
            $i = 0;

            //مصاريف عمال
            if ($expense_type_id != 1 or isset($worker_id)) {
                $list = Worker::where("worker_id", ">", 0);
                if (isset($worker_id)) {
                    $list = $list->where("worker_id", $worker_id);
                }

                $list = $list->get();


                $data = array();
                $no = $_POST['start'];
                $work_num = 0;




                foreach ($list as $worker) {
                    $no++;
                    $i++;
                    $row = array();

                    $expenses = Expense::where("worker_id", $worker->worker_id);

                    if (isset($request->expense_dt_from) and isset($request->expense_dt_to)) {
                        $expenses = $expenses->whereDate('created_at', '>=' , date('Y-m-d',strtotime($request->expense_dt_from)));
                        $expenses = $expenses->whereDate('created_at', '<=' , date('Y-m-d',strtotime($request->expense_dt_to)));                }

                        if(isset($request->manager_id) and $request->manager_id !='' )                        {
                            $expenses = $expenses->where("manager_id",$request->manager_id);
                        }

                        if(isset($request->expense_categoty_id) and $request->expense_categoty_id !='' )                        {
                            $expenses = $expenses->where("expense_categoty_id",$request->expense_categoty_id);
                        }



                        $expenses = $expenses->get();

                    //بحال لم يكن هنالك مصاريف تشغيلية لا تعرض باقي المصاريف
                    if (!count($expenses))
                        continue;

                    $work_num++;

                    foreach ($expenses as $expense) {
                        # code...



                        $pay_expense = DB::table('expense_detail')->where("expense_id", $expense->expense_id)->get()->sum("expense_month_pay");
                        $remain_db = $expense->expense_price  - $pay_expense;

                        if (isset($request->det_calculate_month_remain) ) {
                            if ($request->det_calculate_month_remain =='1') {
                                if (!$remain_db)
                                    continue;
                            } else if ($request->det_calculate_month_remain =='0') {
                                if ($remain_db)
                                    continue;
                            }
                        }

                        //    $det_expense_month_remain=$x->expense_price -$pay_expense;


                        if ($remain_db == '0') {
                            $remain_desc = '<span class="ms-2 badge badge-light-success fw-bold">مكتمل الدفع</span>';
                        } else {
                            $remain_desc = '<span class="ms-2 badge badge-light-danger fw-bold">متبقي</span>';
                        }

                        // if ( $x->municip_no!='') {
                        //     $municip_no_char = '<br>'.'<span class="ms-2 badge badge-light-danger fw-bold"> ' . $x->municip_no . '</span>';
                        // }
                        // else{
                        //     $municip_no_char='';
                        // }
                        if (isset($worker->ssn)) {
                            $ssn_char = '<br>' . '<span class="ms-2 badge badge-light-danger fw-bold"> ' . $worker->ssn . '</span>';
                        } else {
                            $ssn_char = '';
                        }
                        // if ( $x->shop_name!='') {
                        //     $shop_name = '<span class="ms-2 text-info fw-bold"> ' . $x->shop_name.'-'.$x->shop_id . '</span>';
                        // }
                        // else{
                        //     $shop_name='';
                        // }
                        $row = array();

                        $row[] = $work_num;
                        $row[] = "مصاريف تشغيلية"; //$x->type_desc;
                        $row[] = ""; //$x->expense_type_name;
                        $row[] = $expense->expense_categoty->expense_categoty_name; //$x->expense_categoty_name;

                        // $row[] = $shop_name.$municip_no_char;
                        $row[] = "";

                        $avatar_path = '/../../'.$worker->avatar;
                        $avatar_html = file_exists(public_path($worker->avatar)) ?
                            '<img src="'.$avatar_path.'" width="100" height="100" class="mx-auto mb-2 rounded-circle d-block">' :
                            '<div class="mx-auto mb-2 bg-light rounded-circle" style="width:100px;height:100px"></div>';
                        $row[] = '<div class="text-center">'.$avatar_html.'<div>' . $worker->worker_name . $ssn_char . '</div></div>';




                        $row[] =$expense->manager->manager_name; //$x->manager_name;
                        $row[] = ""; //$x->expense_month_desc;
                        $row[] = $expense->expense_price; //8
                        $row[] = number_format( $expense->expense_price - $expense->expense_price / 1.15 , 2 );
                        $row[] = number_format( $expense->expense_price / 1.15  , 2);
                        //  $row[] = $det_expense_month_remain.'-'.$remain_db;
                        $row[] = $pay_expense;
                        $row[] = $remain_db;
                        $row[] = $remain_desc;
                        $row[] = $expense->note; //$x->note;
                        $row[] = ""; //$x->name;
                        $row[] = Carbon::parse($expense->created_at)->format('d-m-Y');
                        if (Perm::get_function_access(61) || Perm::get_function_access(62)) {
                            $opt = '<div class="btn-group btn-group-sm" role="group">';
                            if (Perm::get_function_access(61)) {
                                $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm upd_expense" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.expense.upd_expense') . "'" . ' onclick="upd_expense(' . "'" . $expense->expense_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                            }
                            if (Perm::get_function_access(62)) {
                                $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_expense(' . "'" . $expense->expense_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw"></i>  </a>';
                            }
                            if (Perm::get_function_access(23) || Perm::get_function_access(24)) {
                                $opt .= '<a class="btn btn-sm btn-info btn-icon btn-icon-sm upd_statement" style="margin-left: .5rem"    data-url=' . "'" . route('dashboard.expense.upd_statement') . "'" . ' onclick="upd_statement(' . "'" . $expense->expense_id . "'" . ')"> <i class="fab fa-cc-amazon-pay fa-fw"></i></a>';
                            }
                            $row[] = $opt;
                        }
                        $data[] = $row;
                    }
                    //سطر المصاريف عمال
                    $worker_row = $row;
                    if (!isset( $worker_row[0]))
                    continue;
                    $worker_row[1] = "<div style='    background: #ffeb3b52;
                    padding: 10px;'>  مصاريف عمال </div>";

                    $financial_detail = DB::table('financial_detail')
                        ->select('*')
                        ->join('financial', 'financial.financial_id', '=', 'financial_detail.financial_id')
                        ->where('financial.worker_id', $worker->worker_id)
                        ->get();


                    $pay_worker    = $financial_detail->sum("financial_month_pay");
                    $remain_worker = $financial_detail->sum("financial_month_remain");
                    $worker_price = $financial_detail->sum("financial_month_val");

                    //    $det_expense_month_remain=$x->expense_price -$pay_expense;


                    if ($remain_worker == '0') {
                        $remain_desc = '<span class="ms-2 badge badge-light-success fw-bold">مكتمل الدفع</span>';
                    } else {
                        $remain_desc = '<span class="ms-2 badge badge-light-danger fw-bold">متبقي</span>';
                    }
                    $worker_row[8] = $worker_price;
                    $worker_row[9] = number_format( $worker_price - $worker_price / 1.15 , 2 );
                    $worker_row[10] = number_format( $worker_price / 1.15  , 2);
                    $worker_row[11] = $pay_worker;

                    $worker_row[12] = $remain_worker;
                    $worker_row[13] = $remain_desc;
                    $worker_row[16] = "";
                    $worker_row[3] = "";
                    $worker_row[17] = "";

                    $data[] = $worker_row;
                }
            }

            //مصاريف محال

            else {
                $list = Shop::where("shop_id", ">", 0);
                if (isset($shop_id)) {
                    $list = $list->where("shop_id", $shop_id);
                }

                $list = $list->get();


                $data = array();
                $no = $_POST['start'];
                $work_num = 0;




                foreach ($list as $shop) {
                    $no++;
                    $i++;
                    $row = array();

                    $expenses = Expense::where("shop_id", $shop->shop_id);

                    if (isset($request->expense_dt_from) and isset($request->expense_dt_to)) {
                        $expenses = $expenses->whereDate('created_at', '>=' , date('Y-m-d',strtotime($request->expense_dt_from)));
                        $expenses = $expenses->whereDate('created_at', '<=' , date('Y-m-d',strtotime($request->expense_dt_to)));                }

                        if(isset($request->expense_categoty_id) and $request->expense_categoty_id !='' )                        {
                            $expenses = $expenses->where("expense_categoty_id",$request->expense_categoty_id);
                        }


                        if(isset($request->manager_id) and $request->manager_id !='' )
                        {
                            $expenses = $expenses->where("manager_id",$request->manager_id);
                        }
                        $expenses = $expenses->get();

                    //بحال لم يكن هنالك مصاريف تشغيلية لا تعرض باقي المصاريف
                    if (!count($expenses))
                        continue;

                    $work_num++;

                    foreach ($expenses as $expense) {
                        # code...



                        $pay_expense = DB::table('expense_detail')->where("expense_id", $expense->expense_id)->get()->sum("expense_month_pay");
                        $remain_db = $expense->expense_price  - $pay_expense;

                        //    $det_expense_month_remain=$x->expense_price -$pay_expense;

                        if (isset($request->det_calculate_month_remain) ) {
                            if ($request->det_calculate_month_remain =='1') {
                                if (!$remain_db)
                                    continue;
                            } else if ($request->det_calculate_month_remain =='0') {
                                if ($remain_db)
                                    continue;
                            }
                        }


                        if ($remain_db == '0') {
                            $remain_desc = '<span class="ms-2 badge badge-light-success fw-bold">مكتمل الدفع</span>';
                        } else {
                            $remain_desc = '<span class="ms-2 badge badge-light-danger fw-bold">متبقي</span>';
                        }

                        // if ( $x->municip_no!='') {
                        //     $municip_no_char = '<br>'.'<span class="ms-2 badge badge-light-danger fw-bold"> ' . $x->municip_no . '</span>';
                        // }
                        // else{
                        //     $municip_no_char='';
                        // }
                        if (isset($shop->shop_mobile)) {
                            $ssn_char = '<br>' . '<span class="ms-2 badge badge-light-danger fw-bold"> ' . $shop->municip->municip_no . '</span>';
                        } else {
                            $ssn_char = '';
                        }
                        // if ( $x->shop_name!='') {
                        //     $shop_name = '<span class="ms-2 text-info fw-bold"> ' . $x->shop_name.'-'.$x->shop_id . '</span>';
                        // }
                        // else{
                        //     $shop_name='';
                        // }

                        $row = array();

                        $row[] = $work_num;
                        $row[] = "مصاريف تشغيلية"; //$x->type_desc;
                        $row[] = ""; //$x->expense_type_name;
                        $row[] = $expense->expense_categoty->expense_categoty_name; //$x->expense_categoty_name;

                        // $row[] = $shop_name.$municip_no_char;

                        $row[] = $shop->shop_name .  $ssn_char; //$x->shop_name. $ssn_char;
                        $row[] = "";



                        $row[] =$expense->manager->manager_name; //$x->manager_name;
                        $row[] = ""; //$x->expense_month_desc;
                        $row[] = $expense->expense_price; //8

                        $row[] = number_format(  $expense->expense_price - $expense->expense_price / 1.15 , 2 );
                        $row[] = number_format( $expense->expense_price / 1.15  , 2);
                        $row[] = $pay_expense;

                        //  $row[] = $det_expense_month_remain.'-'.$remain_db;
                        $row[] = $remain_db;
                        $row[] = $remain_desc;
                        $row[] = $expense->note; //$x->note;
                        $row[] = ""; //$x->name;
                        $row[] = Carbon::parse($expense->created_at)->format('d-m-Y');
                        if (Perm::get_function_access(61) || Perm::get_function_access(62)) {
                            $opt = '<div class="btn-group btn-group-sm" role="group">';
                            if (Perm::get_function_access(61)) {
                                $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm upd_expense" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.expense.upd_expense') . "'" . ' onclick="upd_expense(' . "'" . $expense->expense_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                            }
                            if (Perm::get_function_access(62)) {
                                $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_expense(' . "'" . $expense->expense_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw"></i>  </a>';
                            }
                            if (Perm::get_function_access(23) || Perm::get_function_access(24)) {
                                $opt .= '<a class="btn btn-sm btn-info btn-icon btn-icon-sm upd_statement" style="margin-left: .5rem"    data-url=' . "'" . route('dashboard.expense.upd_statement') . "'" . ' onclick="upd_statement(' . "'" . $expense->expense_id . "'" . ')"> <i class="fab fa-cc-amazon-pay fa-fw"></i></a>';
                            }
                            $row[] = $opt;
                        }
                        $data[] = $row;
                    }
                    //سطر المصاريف محلات
                    $shop_row = $row;
                    if (!isset( $shop_row[0]))
                    continue;
                    $shop_row[1] = "<div style='    background: #2196f336;
                    padding: 10px;'>  مصاريف محلات </div>";

                    $calculate_detail = DB::table('calculate_detail')
                        ->select('*')
                        ->join('calculate', 'calculate.calculate_id', '=', 'calculate_detail.calculate_id')
                        ->where('calculate.shop_id', $shop->shop_id)
                        ->get();


                    $pay_shop    = $calculate_detail->sum("calculate_month_pay");
                    $remain_shop = $calculate_detail->sum("calculate_month_remain");
                    $shop_price = $calculate_detail->sum("calculate_month_val");

                    //$det_expense_month_remain=$x->expense_price -$pay_expense;


                    if ($remain_shop == '0') {
                        $remain_desc = '<span class="ms-2 badge badge-light-success fw-bold">مكتمل الدفع</span>';
                    } else {
                        $remain_desc = '<span class="ms-2 badge badge-light-danger fw-bold">متبقي</span>';
                    }
                    $shop_row[8] = $shop_price;

                    $shop_row[9] = number_format( $shop_price - $shop_price / 1.15 , 2 );
                    $shop_row[10] = number_format( $shop_price / 1.15  , 2);
                    $shop_row[11] = $pay_shop;

                    $shop_row[12] = $remain_shop;
                    $shop_row[13] = $remain_desc;
                    $shop_row[16] = "";
                    $shop_row[3] = "";
                    $shop_row[4] = $shop->shop_name .  $ssn_char;
                    $shop_row[17] = "";

                    $data[] = $shop_row;
                }
            }

            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $work_num,
                "recordsFiltered" => $work_num,

                "data" => $data
            );
            echo json_encode($output);
        }
    }














    public function ajax_search_expense____________(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(60) || Perm::get_function_access(61) || Perm::get_function_access(62))) {
            $expense_type_id = $request->expense_type_id;
            $expense_categoty_id = $request->expense_categoty_id;
            $expense_dt_from = $request->expense_dt_from;
            $expense_dt_to = $request->expense_dt_to;
            $manager_id = $request->manager_id;
            $worker_id = $request->worker_id;
            $shop_id = $request->shop_id;
            $list_total = Expense::serachspendcountall($expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id);
            $list = Expense::serachspenddataall($expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();


                if ($x->municip_no != '') {
                    $municip_no_char = '<br>' . '<span class="ms-2 badge badge-light-danger fw-bold"> ' . $x->municip_no . '</span>';
                } else {
                    $municip_no_char = '';
                }
                if ($x->ssn != '') {
                    $ssn_char = '<br>' . '<span class="ms-2 badge badge-light-danger fw-bold"> ' . $x->ssn . '</span>';
                } else {
                    $ssn_char = '';
                }

                //  if($x->shop)
                $row[] = $i;
                $row[] = $x->expense_type_name;
                $row[] = $x->expense_categoty_name;
                $row[] = $x->expense_respon;
                $row[] = $x->expense_price;
                $row[] = $x->manager_name;
                $row[] = $x->shop_name . $municip_no_char;
                $row[] = $x->worker_name . $ssn_char;
                $row[] = $x->note;
                $row[] = $x->name;
                $row[] = Carbon::parse($x->created_at)->format('d-m-Y');
                if (Perm::get_function_access(61) || Perm::get_function_access(62)) {
                    $opt = '<div class="btn-group btn-group-sm" role="group">';
                    if (Perm::get_function_access(61)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm upd_expense" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.expense.upd_expense') . "'" . ' onclick="upd_expense(' . "'" . $x->expense_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(62)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_expense(' . "'" . $x->expense_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw"></i>  </a>';
                    }
                    $row[] = $opt;
                }
                $data[] = $row;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data
            );
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
            //$manager = DB::table('manager')->get();
            $manager = $this->get_manager();
            $expense_type = DB::table('expense_type')->get();
            $expense_categoty = DB::table('expense_categoty')->get();
            $const = array("manager", "expense_type", "expense_categoty", "page_title");
            return view('dashboard.expense.upd_expense', compact('expense', $const));
        }
    }

    public function store(Request $request)
    {
        if (Perm::get_function_access(59)) {

            $expense_type_id =   $request->expense_type_id;

            if ($expense_type_id == 1) {
                $manager_db = DB::table('shop')->select('manager_id')->where('shop_id', $request->shop_id)->first();
                if ($manager_db) {
                    $manager_id = $manager_db->manager_id;
                } else {
                    $manager_id = '';
                }
            }
            if ($expense_type_id == 3) {
                $manager_db = DB::table('workers')->select('manager_id')->where('worker_id', $request->worker_id)->first();
                if ($manager_db) {
                    $manager_id = $manager_db->manager_id;
                } else {
                    $manager_id = '';
                }
            }
            if ($expense_type_id == 2) {
                $manager_id = $request->manager_id;
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
                'expense_month_desc' => 'شهر الدفع',


            );
            $validator = Validator::make($request->all(), [
                'expense_month_desc' => ['required'],
                'expense_type_id' => ['required', 'string'],
                'expense_categoty_id' => ['required'],
                'manager_id' => ['required'],
                'shop_id' => [Rule::requiredIf($request->expense_type_id == 1), 'nullable'],
                'worker_id' => [Rule::requiredIf($request->expense_type_id == 3), 'nullable'],
                // 'expense_month_desc' => [Rule::requiredIf($request->expense_type_id == 3 || $request->expense_type_id == 1), 'nullable']

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

                $expense_month_desc = $request->expense_month_desc;
                $array_name = explode("-", $expense_month_desc);
                $expense_month_m = $array_name[0];
                $expense_month_y = $array_name[1];

                $result2 = DB::table('expense')->insertGetId([
                    'expense_type_id' => $request->expense_type_id,
                    'expense_categoty_id' => $request->expense_categoty_id,
                    'expense_month_desc' => $request->expense_month_desc,
                    'expense_month_m' => $expense_month_m,
                    'expense_month_y' => $expense_month_y,

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

                // If initial payment is provided, create expense_detail record
                $expense_month_pay = $request->expense_month_pay;
                if ($result2 != '' && $expense_month_pay != '' && $expense_month_pay > 0) {
                    $expense_price = $request->expense_price ?? 0;
                    $expense_month_remain = $expense_price - $expense_month_pay;

                    DB::table('expense_detail')->insert([
                        'expense_id' => $result2,
                        'expense_month_val' => $expense_price,
                        'expense_month_pay' => $expense_month_pay,
                        'expense_month_remain' => $expense_month_remain,
                        'note' => 'دفعة أولية',
                        'created_at' => Carbon::now(),
                        'create_user' => Auth::user()->id,
                    ]);
                }

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
        $result = ['status' => false, 'message' => '', 'message_out' => ''];
        
        try {
            if (!Perm::get_function_access(61)) {
                $result['message_out'] = 'لا تملك صلاحية';
                return response()->json($result);
            }
            
            $id = $request->expense_id_db;
            $expense_type_id = trim($request->expense_type_id);
            $manager_id = '';
            
            if ($expense_type_id == 1) {
                $manager_db = DB::table('shop')->select('manager_id')->where('shop_id', $request->shop_id)->first();
                if ($manager_db) {
                    $manager_id = $manager_db->manager_id;
                }
            }
            if ($expense_type_id == 3) {
                $manager_db = DB::table('workers')->select('manager_id')->where('worker_id', $request->worker_id)->first();
                if ($manager_db) {
                    $manager_id = $manager_db->manager_id;
                }
            }
            if ($expense_type_id == 2) {
                $manager_id = $request->manager_id;
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

                'shop_id' => [Rule::requiredIf(trim($request->expense_type_id) == 1), 'nullable'],
                'worker_id' => [Rule::requiredIf(trim($request->expense_type_id) == 3), 'nullable']
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
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
                        'expense_type_id' => trim($request->expense_type_id),
                        'expense_categoty_id' => trim($request->expense_categoty_id),
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
                $result['status'] = true;
                $result['message_out'] = 'تم الحفظ بنجاح';
            }
        } catch (\Exception $e) {
            \Log::error('Expense updstore error: ' . $e->getMessage() . ' at line ' . $e->getLine());
            $result['status'] = false;
            $result['message_out'] = 'حدث خطأ: ' . $e->getMessage();
        }
        
        return response()->json($result);
    }



    //دفعات
    public function ajax_search_expense_detail(Request $request)
    {
        if (Perm::get_function_access(23) || Perm::get_function_access(24)) {

            $expense_id = $request->expense_id;
            $expense = DB::table('expense')->where('expense_id', $expense_id)->first();
            $worker_id = $expense->worker_id;
            //        $ishavegroupworker = $this->ishavegroupworker($worker_id);
            //        if ($ishavegroupworker) {
            $issamecreateexpense = $this->issamecreateexpense($worker_id);
            if ($issamecreateexpense) {

                $list_total = Expense::serachspendcountdet($expense_id);
                $list = Expense::serachspenddet($expense_id);


                $data = array();
                $no = $_POST['start'];
                $i = 0;
                foreach ($list as $x) {
                    $no++;
                    $i++;
                    $row = array();
                    $row[] = $i;
                    $expense_month_val = $x->expense_price;


                    $row[] = $x->worker_name;
                    $row[] = $x->expense_month_desc;
                    //$row[] =$expense_month_val;
                    $row[] = $x->det_expense_month_val;

                    $row[] = $x->det_expense_month_pay;
                    $row[] = $x->det_expense_month_remain;
                    $row[] = $x->det_note;
                    $row[] = $x->det_create_user_name;
                    $row[] = Carbon::parse($x->det_created_at)->format('d-m-Y');
                    if (Perm::get_function_access(24)) {
                        $opt = '<div class="btn-group btn-group-sm" role="group"  >';
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_expense_det(' . "'" . $x->expense_detail_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw"></i>  </a>';
                        $opt .= '</div>';
                        $row[] = $opt;
                    }
                    $data[] = $row;
                }
                $output = array(
                    "draw" => $_POST['draw'],
                    "recordsTotal" => $list_total,
                    "recordsFiltered" => $list_total,
                    "data" => $data
                );
                echo json_encode($output);
            }
        }
    }


    // الدفعات

    public function upd_statement(Request $request)
    {
        $id = $request->id;
        $expense = DB::table('expense')->where('expense_id', $id)->first();
        $worker_id = $expense->worker_id ?? null;
        $shop_id = $expense->shop_id ?? null;
        $expense_month_val = $expense->expense_price ?? 0;

        if (Perm::get_function_access(23) || Perm::get_function_access(24)) {

            //بحال كانت المصاريف التشغليلة لعامل فقط
            if (isset($expense->worker_id)) {


                //        $ishavegroupworker = $this->ishavegroupworker($worker_id);
                //        if ($ishavegroupworker) {
                $issamecreateexpense = $this->issamecreateexpense($worker_id);
                if ($issamecreateexpense or 1) {
                    $worker = DB::table('workers')->where('worker_id', $worker_id)->first();
                    $last_expense = DB::table('expense_detail')->where('expense_id', $id)->latest("expense_detail_id")->first();
                    if (isset($last_expense)) {
                        $last_expense_month_remain = $last_expense->expense_month_remain;
                        $last_expense_month_pay = $last_expense->expense_month_pay;
                    } else {
                        $last_expense_month_remain = $expense_month_val;
                        $last_expense_month_pay = 0;
                    }

                    if (isset($expense->worker_id))

                        return view('dashboard.expense.upd_statement_worker', compact('expense', 'worker', 'last_expense_month_remain', 'last_expense_month_pay'));
                } else {
                    return redirect()->route('show_not_allow')->send();
                }
            }



            // بحال كانت المصاريف التشغيلية لمحل فقط
            else {
                $issamecreateexpense = $this->issamecreateexpense($shop_id);
                if ($issamecreateexpense or 1)  {

                    $shop = DB::table('shop')->where('shop_id', $shop_id)->first();
                    $last_expense = DB::table('expense_detail')->where('expense_id', $id)->latest("expense_detail_id")->first();
                    if (isset($last_expense)) {
                        $last_expense_month_remain = $last_expense->expense_month_remain;
                        $last_expense_month_pay = $last_expense->expense_month_pay;
                    } else {
                        $last_expense_month_remain = $expense_month_val;
                        $last_expense_month_pay = 0;
                    }
                    return view('dashboard.expense.upd_statement_shop', compact('expense', 'shop', 'last_expense_month_remain', 'last_expense_month_pay'));
                } else {
                    return redirect()->route('show_not_allow')->send();
                }
            }
        }
    }

    public function tbl_detail(Request $request)
    {
        if (Perm::get_function_access(23) || Perm::get_function_access(24)) {
            if ($request->ajax()) {
                return view('dashboard.expense.tbl_expense_detail');
            } else {
                return "Request Not Ajax Type";
            }
        }
    }


    function del_expense_det(Request $request)
    {
        if (Perm::get_function_access(24)) {

            $id = $request->id;
            $expense_det = DB::table('expense_detail')->where('expense_detail_id', $id)->first();
            $expense_id = $expense_det->expense_id;
            $expense_month_pay = $expense_det->expense_month_pay;


            $expense = DB::table('expense')->where('expense_id', $expense_id)->first();
            $worker_id = $expense->worker_id;
            //        $ishavegroupworker = $this->ishavegroupworker($worker_id);
            //        if ($ishavegroupworker) {
            $issamecreateexpense = $this->issamecreateexpense($worker_id);
            if ($issamecreateexpense) {

                $last_expense = DB::table('expense_detail')->where('expense_id', $expense_id)->latest("expense_detail_id")->first();
                $expense_detail_id = $last_expense->expense_detail_id;
                $expense_month_remain = $last_expense->expense_month_remain;

                $result1 = DB::table('expense_detail')
                    ->where('expense_detail_id', $expense_detail_id)
                    ->update(['expense_month_remain' => $expense_month_pay + $expense_month_remain]);

                $old_expense_id = $expense->expense_id;
                $expense_month_desc = $expense->expense_month_desc;
                $old_expense_month_val = $expense_det->expense_month_val;
                $old_note = $expense_det->note;
                $old_create_user  = $expense_det->create_user;
                $old_created_at = $expense_det->created_at;
                $old_updated_at = $expense_det->updated_at;
                $old_updated_user = $expense_det->updated_user;


                $delete = DB::table('expense_detail')->where('expense_detail_id', $expense_detail_id)->delete();
                if ($delete) {
                    $result22 = DB::table('expense_detail_history')->insertGetId([
                        'expense_id' => $old_expense_id,
                        'expense_month_desc' => $expense_month_desc,
                        'expense_month_val' => $old_expense_month_val,
                        'old_expense_month_val' => $old_expense_month_val,
                        'note' => 'تم حذف القسط' . '<br><br>' . $request->note,
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



    public function updstatement(Request $request)
    {

        $expense_month_desc = $request->expense_month_desc;
        $array_name = explode("-", $expense_month_desc);
        $expense_month_m = $array_name[0];
        $expense_month_y = $array_name[1];


        $expense_id = $request->expense_id_db;
        $expense_month_m = $request->expense_month_desc;
        $expense_month_y = $request->expense_month_y;


        $attributeNames = array(
            'worker_id' => 'اسم العامل',
            'expense_month_desc' => 'شهر الدفع',
            'expense_month_m' => 'سنة الدفع',
            'expense_month_y' => 'شهر الدفع',
            'expense_month_val' => 'المبلغ المطلوب',
            'expense_month_pay' => 'المبلغ المدفوع',
            'expense_month_remain' => 'المبلغ المتبقي',
        );
        $validator = Validator::make($request->all(), [
            // 'worker_id' => ['required', 'integer',

            // ],
            'expense_month_desc' => ['required'],
            'expense_month_val' => ['required', 'min:1', 'not_in:0', 'gt:0'],
            'expense_month_pay' => ['required', 'min:1', request()->filled('expense_month_val') ? 'lte:expense_month_val' : ''],
            'expense_month_remain' => ['required', 'min:0', request()->filled('expense_month_val') ? 'lte:expense_month_val' : ''],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
        } else {

            if ($expense_id != '') {

                $expense_month_val = $request->expense_month_val;
                $expense_month_pay = $request->expense_month_pay;
                $expense_month_remain = $expense_month_val - $expense_month_pay;


                $result_upload = DB::table('expense_detail')->insertGetId([
                    'expense_id' => $expense_id,
                    'expense_month_val' => $expense_month_val,
                    'expense_month_pay' => $expense_month_pay,
                    'expense_month_remain' => $expense_month_remain,
                    'note' => $request->note,
                    'created_at' => Carbon::now(),
                    'create_user' => Auth::user()->id,
                ]);

                $result22 = DB::table('expense_detail_history')->insertGetId([
                    'expense_id' => $expense_id,
                    'expense_month_val' => $expense_month_val,
                    'expense_month_pay' => $expense_month_pay,
                    'expense_month_remain' => $expense_month_remain,
                    'note' => 'تم التعديل على بيانات القسط' . '<br><br>' . $request->note,
                    'created_at' => Carbon::now(),
                    'create_user' => Auth::user()->id,
                    'change_user' => Auth::user()->id,
                    'change_at' => Carbon::now(),

                ]);

                $ERROR_FLAG = 0;
                $result['status'] = $expense_id;
                $result['message_out'] = 'تم الحفظ بنجاح';
            } else {
                $result['status'] = false;
                $result['message_out'] = 'لم يتم الحفظ';
            }
        }


        // }
        return response()->json($result);
    }

    /**
     * Spec 004 B1 — AI prefill for the expense form. Accepts a receipt (image/PDF),
     * runs OCR, and returns the fields for the screen to fill (amount, vendor, date,
     * description, suggested category). Nothing is saved here — the user confirms in
     * the normal expense form, which writes to the real `expense` table.
     */
    public function aiExtract(Request $request)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:20480',
        ]);

        $file = $request->file('receipt');
        $ds = app(\App\Services\DocumentStorage::class);
        $tmp = $ds->tempWorkingCopy($file);
        try {
            $data = app(\App\Services\ExpenseAiExtractor::class)->extract($tmp);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message_out' => 'تعذّر استخراج البيانات: '.$e->getMessage()], 422);
        } finally {
            @unlink($tmp);
        }
        $stored = $ds->store($file, 'expense');
        $fileUrl = route('dashboard.documents.serve', ['module' => 'expense', 'filename' => $stored['filename']]);

        \App\Services\AuditLogger::log('expense', null, \App\Services\AuditLogger::EXTRACT, [
            'note' => 'استخراج إيصال مصروف بالذكاء الاصطناعي',
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'expense_price' => $data['expense_price'],
                'expense_respon' => $data['vendor'],
                'date' => $data['date'],
                'note' => $data['description'],
                'expense_categoty_id' => $data['expense_categoty_id'],
                'category_name' => $data['category_name'],
                'confidence' => $data['field_confidence'],
                'receipt_url' => $fileUrl,
            ],
        ]);
    }
}
