<?php

namespace App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vacation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;

class VacationController extends Controller
{
    use ApimtitTrait;
    public function __construct()
    {
        $this->middleware('ishaveaccess:10');
    }
    public function index()
    {
        if (Perm::get_function_access(63)) {
            $page_title = 'اضافة اجازة';
            $sel_vacation = array("page_title");
            $vacation_type = DB::table('vacation_type')->get();
            return view('dashboard.vacation.index', compact('vacation_type', $sel_vacation));
        }
    }


    public function views()
    {
        if (Perm::get_function_access(64) || Perm::get_function_access(65) || Perm::get_function_access(66) || Perm::get_function_access(67)) {
            $page_title = 'كشف الاجازات';
            $vacation_type = DB::table('vacation_type')->get();
            return view('dashboard.vacation.view', compact('vacation_type', 'page_title'));
        }
    }


    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(64) || Perm::get_function_access(65) || Perm::get_function_access(66) || Perm::get_function_access(67))) {
            return view('dashboard.vacation.tbl_vacation');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_vacation(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(64) || Perm::get_function_access(65) || Perm::get_function_access(66) || Perm::get_function_access(67))) {
            $worker_id = $request->worker_id;
            $vacation_type_id = $request->vacation_type_id;
            $vacation_month_desc = $request->vacation_month_desc;
            if ($vacation_month_desc != '') {
                $array_name = explode("-", $vacation_month_desc);
                $vacation_month_m = $array_name[0];
                $vacation_month_y = $array_name[1];
            } else {
                $vacation_month_m = '';
                $vacation_month_y = '';
            }
            $list_total = vacation::serachdet($vacation_month_m, $vacation_month_y, $worker_id, $vacation_type_id);
            $list = vacation::serachspenddet($vacation_month_m, $vacation_month_y, $worker_id, $vacation_type_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                $row[] = $i;
                $row[] = $x->worker_name;
                $row[] = $x->start;
                $row[] = $x->end;
                $row[] = $x->count_day;
                $row[] = $x->vacation_type_name;
                $row[] = $x->job_name;
                $row[] = $x->work_place_name;
                $row[] = $x->note;
                $row[] = $x->name;
                $row[] = Carbon::parse($x->created_at)->format('d-m-Y');
                if (Perm::get_function_access(65) || Perm::get_function_access(66) || Perm::get_function_access(67)) {
                    $opt = '<div class="btn-group btn-group-sm " role="group"  >';
                    if (Perm::get_function_access(65)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_vacation" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"    data-url=' . "'" . route('dashboard.vacation.upd_vacation') . "'" . '            onclick="upd_vacation(' . "'" . $x->vacation_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(66)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_vacation(' . "'" . $x->vacation_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
                    }
                    if (Perm::get_function_access(67)) {
                        $opt .= '<a class="btn btn-sm btn-secondary  btn-icon btn-icon-sm  print_vacation_pdf" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"    data-url=' . "'" . route('dashboard.report.print_vacation_pdf') . "'" . ' onclick="print_vacation_pdf(' . "'" . $x->vacation_id . "'" . ')"> <i class="far fa-file-pdf fa-fw text-danger"></i></a>';
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
        }
    }


    public function views_all()
    {
        if (Perm::get_function_access(68)) {
            $page_title = 'كشف الاجمالي';
            return view('dashboard.vacation.view_all', compact('page_title'));
        }
    }

    public function tbl_All(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(68))) {
            return view('dashboard.vacation.tbl_all');
        } else {
            return "Request Not Ajax Type";
        }
    }

    public function ajax_search_vacation_all(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(68))) {
            $worker_id = $request->worker_id;
            $vacation_month_desc = $request->vacation_month_desc;
            if ($vacation_month_desc != '') {
                $array_name = explode("-", $vacation_month_desc);
                $vacation_month_m = $array_name[0];
                $vacation_month_y = $array_name[1];
            } else {
                $vacation_month_m = '';
                $vacation_month_y = '';
            }
            $list_total = vacation::serachspendcountdesc($vacation_month_m, $vacation_month_y, $worker_id);
            $list = vacation::serachspenddatadesc($vacation_month_m, $vacation_month_y, $worker_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                $row[] = $i;
                $row[] = $x->worker_name;
                $row[] = $x->month . '-' . $x->year;
                $row[] = $x->count_day;
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
        if ($request->ajax()) {
            return view('dashboard.vacation.tbl_vacation_detail');
        } else {
            return "Request Not Ajax Type";
        }
    }


    function del_vacation(Request $request)
    {
        if (Perm::get_function_access(66)) {
            $id = $request->id;
            $delete = DB::table('vacation')
                ->where('vacation_id', $id)
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
    }


    public function upd_vacation(Request $request)
    {
        if (Perm::get_function_access(65)) {
            $id = $request->id;
            $vacation = DB::table('vacation')->where('vacation_id', $id)->first();
            $worker_id = $vacation->worker_id;
            $worker = DB::table('workers')->where('worker_id', $worker_id)->first();
            $vacation_type = DB::table('vacation_type')->get();
            return view('dashboard.vacation.upd_vacation', compact('vacation', 'vacation_type', 'worker'));
        }
    }


    public function store(Request $request)
    {
        if (Perm::get_function_access(63)) {
            $start_month = $request->start;
            $array_name = explode("-", $start_month);
            $start_month_m = $array_name[1];
            $start_month_y = $array_name[0];
            $end_month = $request->end;
            $array_name2 = explode("-", $end_month);
            $end_month_m = $array_name2[1];
            $end_month_y = $array_name2[0];
            $worker_id = $request->worker_id;
            $start = $request->start;
            $end = $request->end;
            $vacation_type_id = $request->vacation_type_id;
            $count_day = $request->count_day;
            $note = $request->note;
            $attributeNames = array(
                'worker_id' => 'اسم الموظف',
                'start' => 'تاريخ الاجازة من',
                'end' => 'تاريخ الاجازة الى',
                'count_day' => 'عدد الايام',
                'vacation_type_id' => 'نوع الاجازة',
                'start_month' => 'تاريخ الاجازة الى',
                'end_month' => 'تاريخ الاجازة الى',
            );
            $validator = Validator::make($request->all(), [
                'worker_id' => ['required', 'integer'],
                'start' => ['required', 'date', 'before_or_equal:end'],
                'end' => ['required', 'date', 'after_or_equal:start'],
                'vacation_type_id' => ['required'],

            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                if (($start_month_m == $end_month_m) and ($start_month_y == $end_month_y)) {
                    $sql = "SELECT vacation_id FROM vacation WHERE
            1=1 and  worker_id= $worker_id  and
            ( (start between '$start' and '$end') OR (end between '$start' and '$end') ); ";
                    $vacation_id = count(DB::select($sql));
                    if ($vacation_id == '0') {
                        $result_upload = DB::table('vacation')->insertGetId([
                            'worker_id' => $worker_id,
                            'start' => $start,
                            'end' => $end,
                            'count_day' => $count_day,
                            'vacation_type_id' => $vacation_type_id,
                            'note' => $request->note,
                            'created_at' => Carbon::now(),
                            'create_user' => Auth::user()->id,
                        ]);
                        $ERROR_FLAG = 0;
                        $result['status'] = $vacation_id;
                        $result['message_out'] = 'تم الحفظ بنجاح';
                    } else {
                        $result['status'] = false;
                        $result['message_out'] = 'يوجد اجازة مدخلة بنفس التاريخ';
                    }
                } else {
                    $result['status'] = false;
                    $result['message_out'] = 'ادخل الاجازة على نفس الشهر و العام';
                }
            }
            return response()->json($result);
        }
    }

    public function updstore(Request $request)
    {
        if (Perm::get_function_access(65)) {
            $id = $request->vacation_id_db;
            $start_month = $request->start;
            $array_name = explode("-", $start_month);
            $start_month_m = $array_name[1];
            $start_month_y = $array_name[0];
            $end_month = $request->end;
            $array_name2 = explode("-", $end_month);
            $end_month_m = $array_name2[1];
            $end_month_y = $array_name2[0];
            $worker_id = $request->worker_id;
            $start = $request->start;
            $end = $request->end;
            $vacation_type_id = $request->vacation_type_id;
            $count_day = $request->count_day;
            $note = $request->note;
            /*  $sql="SELECT vacation_id FROM vacation WHERE
              1=1 and  worker_id= $worker_id  and
              ( (start between '$start' and '$end') OR (end between '$start' and '$end') ); ";*/
            if (($start_month_m == $end_month_m) and ($start_month_y == $end_month_y)) {
                $sql = "SELECT vacation_id FROM vacation WHERE
            1=1 and  worker_id= $worker_id  and  vacation_id!=$id and
            ( (start between '$start' and '$end') OR (end between '$start' and '$end') ); ";
                // $vacation_id = DB::select($sql);
                $vacation_id = count(DB::select($sql));
                if ($vacation_id == '0') {
                    $attributeNames = array(
                        'worker_id' => 'اسم الموظف',
                        'start' => 'تاريخ الاجازة من',
                        'end' => 'تاريخ الاجازة الى',
                        'count_day' => 'عدد الايام',
                        'vacation_type_id' => 'نوع الاجازة',
                        'start_month' => 'تاريخ الاجازة الى',
                        'end_month' => 'تاريخ الاجازة الى',
                    );
                    $validator = Validator::make($request->all(), [
                        'worker_id' => ['required', 'integer'],
                        'start' => ['required', 'date', 'before_or_equal:end'],
                        'end' => ['required', 'date', 'after_or_equal:start'],
                        'vacation_type_id' => ['required'],

                    ]);
                    $validator->setAttributeNames($attributeNames);
                    if ($validator->fails()) {
                        $result['status'] = false;
                        $result['message'] = $validator->errors();
                        $result['message_out'] = '';
                    } else {
                        $result2 = DB::table('vacation')
                            ->where('vacation_id', $id)
                            ->update([
                                'worker_id' => $worker_id,
                                'start' => $start,
                                'end' => $end,
                                'count_day' => $count_day,
                                'vacation_type_id' => $vacation_type_id,
                                'updated_at' => Carbon::now(),
                                'updated_user' => Auth::user()->id,
                            ]);
                        $ERROR_FLAG = 0;
                        $result['status'] = 1;
                        $result['message_out'] = 'تم الحفظ بنجاح';
                    }
                } else {
                    $result['status'] = false;
                    $result['message_out'] = 'يوجد اجازة مدخلة بنفس التاريخ';
                }
            } else {
                $result['status'] = false;
                $result['message_out'] = 'ادخل الاجازة على نفس الشهر و العام';
            }
            return response()->json($result);
        }
    }
}
