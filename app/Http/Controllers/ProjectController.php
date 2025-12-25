<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;

// use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Workers;
use App\Models\Orcl;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function tbl(Request $request)
    {

        if ($request->ajax()) {
            /*   $dt_to= $request->dt_to;
                $dt_from= $request->dt_from;
                 $sexs = DB::table('sex')->where('sex_active', 1)->get();
                 $states = DB::table('state')->where('state_active', 1)->get();
               return view('operator.workers.tbl', compact('dt_from','dt_to'));*/
            return view('employments.projects.tbl');

        } else {
            return "Request Not Ajax Type";
        }

    }

    public function ajax_search_project(Request $request)
    {
        $dt_to = $request->dt_to;
        $dt_from = $request->dt_from;
        $worker_name = $request->worker_name;
        $sex = $request->sex;
        $phone = $request->phone;
        $email = $request->email;

//echo 'ssssss';
//dd($results);


        $list_total = Workers::serachspendcount($worker_name, $sex, $phone, $email);
        $list = Workers::serachspenddata($worker_name, $sex, $phone, $email);
        $data = array();
        $no = $_POST['start'];
        $i = 0;
        foreach ($list as $x) {
            /* if ($x->ins_id != '') {
                 $emp_name = $this->General_m->get_name_emp($x->ins_id);
             } else {
                 $emp_name = '';
             }
             if ($x->cashing_id != '') {
                 $cashing_name = $this->General_m->get_name_caching($x->cashing_id);
             } else {
                 $cashing_name = '';
             }*/


            /*  +"project_no": "1"
                +"project_name": "ببب"
                +"project_idea": "<p>ؤييييييييييي</p>"
                +"project_description": "<p>ؤؤؤؤؤؤؤؤ</p>"
                +"start_date": "0004-07-20 23:00:00"
                +"end_date": "0018-07-20 23:00:00"
                +"actual_start_date": "0004-07-20 23:00:00"
                +"actual_end_date": "0031-07-20 23:00:00"
                +"target_duration": "22"
                +"financier": "وكالة"
                +"project_budget": "22"
                +"currency": "دينار"
                +"status": "الادارة العاملة والوحدات بالوزارة"
                +"auto_close": null
                +"project_areas": "غزة,الوسطى,خانيونس" */


            /* "PROJECT_ID",
       "PROJECT_NO",
       "PROJECT_NAME",
       "PROJECT_IDEA",
       "PROJECT_DESCRIPTION",
       "START_DATE",
       "END_DATE",
       "ACTUAL_START_DATE",
       "ACTUAL_END_DATE",
       "TARGET_DURATION",
       "FINANCIER",
       "PARTNER",
       "PROJECT_BUDGET",
       "CURRENCY",
       "STATUS",
       "AUTO_CLOSE",
       "PROJECT_AREAS"*/
            $no++;
            $i++;
            $row = array();
            $row[] = $i;
            $row[] = $x->project_no;
            $row[] = $x->project_name;
            $row[] = $x->project_description;
            $row[] = $x->project_idea;
            $row[] = Carbon::parse($x->start_date)->format('d-m-Y') . ' - ' . Carbon::parse($x->end_date)->format('d-m-Y');
            $row[] = Carbon::parse($x->actual_start_date)->format('d-m-Y') . ' - ' . Carbon::parse($x->actual_end_date)->format('d-m-Y');
            $row[] = $x->target_duration;
            $row[] = $x->financier;
            $row[] = $x->project_budget;
            $row[] = $x->currency;
            $row[] = $x->partner;
            $row[] = $x->status_desc;
            $row[] = $x->project_areas;
//$row[] =convertMdyToYmd($x->created_at);
//data-url="{{ route('dashboard.emps.show_job_cat') }}"
//$data-url="{{ route('dashboard.emps.show_job_cat') }}";
            $opt = '<div class="btn-group btn-group-sm " role="group"  >';

            /*if ("1" == "1") {
                $opt.= '<a class="btn btn-sm btn-dark btn-icon btn-icon-sm btn-elevate btn-pill " style="margin-left: .5rem;"  data-url=' . "'" . route('projects.show_project') . "'" . '  onclick="print_spend_bill(' . "'" . $x->project_id . "'" . ",'" . 2 . "'" . ')"> <i class="la  la-print "></i></a>';
            }*/
            if ("1" == "1") {

                $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_project" style="margin-left: .5rem;"    data-url=' . "'" . route('projects.upd_project') . "'" . '            onclick="upd_project(' . "'" . $x->project_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
            }

            if ("1" == "1") {
                $opt .= '<a class="btn btn-sm btn-info btn-icon btn-icon-sm  upd_status" style="margin-left: .5rem;"    data-url=' . "'" . route('projects.upd_status') . "'" . '            onclick="upd_status(' . "'" . $x->project_id . "'" . ')"> <i class="fas fa-exchange-alt fa-fw"></i></a>';
            }


            if ("1" == "1") {
                $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-left: .5rem;" onclick="del_project(' . "'" . $x->project_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
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


    function del_project(Request $request)
    { /* حذف الموظف */
        //  if ( get_function_access(50)  and $this->input->is_ajax_request()) {
        $PROJECT_ID_IN = $request->id;
        //$delete = DB::delete('delete from project where worker_id = ?',[$id]);

        $ERROR_FLAG = 0;
        $procedureName4 = 'employment_pkg.DELETE_PROJECT_PR';
        $bindings4 = [
            "PROJECT_ID_IN" => $PROJECT_ID_IN,
            'codeMsg' => [
                'value' => &$codeMsg,
                'length' => 9,
            ],


        ];
        $result4 = DB::connection('oracle')->executeProcedure($procedureName4, $bindings4);
        // dd($delete);
        // echo   $deleted;

        // echo $codeMsg;
        if ($codeMsg) {
            $result['status'] = true;
            $result['message'] = 'تم';
        } else {
            $message = 'لا يمكن الحذف';
            $result['status'] = false;
            $result['message'] = $message;
        }
        echo json_encode($result);
        // }
    }


    function del_project_bref(Request $request)
    { /* حذف الموظف */
        $ID_IN = $request->id;

        $ERROR_FLAG = 0;
        $procedureName4 = 'employment_pkg.DELETE_EMPLOYMENT_BENEFICIARY_PR';
        $bindings4 = [
            "ID_IN" => $ID_IN,
            'codeMsg' => [
                'value' => &$codeMsg,
                'length' => 9,
            ],


        ];
        $result4 = DB::connection('oracle')->executeProcedure($procedureName4, $bindings4);

        // echo $codeMsg;
        if ($codeMsg) {
            $result['status'] = true;
            $result['message'] = 'تم';
        } else {
            $message = 'لا يمكن الحذف';
            $result['status'] = false;
            $result['message'] = $message;
        }
        echo json_encode($result);
    }


    public function updstore(Request $request)
    {
        // dd(Carbon::createFromFormat('Y-m-d', $request->ACTUAL_END_DATE_IN)->format('Y-m-d H:i:s'));
        $input = $request->all();
        $PROJECT_AREA = $request->PROJECT_AREA;
        $PROJECT_AREA_DEPT = $request->PROJECT_AREA_DEPT;
        $SIDE_ID_IN = $request->SIDE_ID_IN;
        $EMPLOYMENT_CONDITIONS = $request->EMPLOYMENT_CONDITIONS;

        $PROJECT_NAME_IN_OLD = $request->old('PROJECT_NAME_IN');


        $validator = Validator::make($request->all(), [
            'PROJECT_NAME_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_TYPE_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_SUB_TYPE_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_GENDER_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_COUNT_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_SALARY_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_CURRENCY_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_REGION_IN' => 'required',
        ], [
            'PROJECT_NAME_IN.required' => 'اسم المشروع',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_TYPE_IN' => 'المؤهل المستفدين ',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_SUB_TYPE_IN' => 'التخصص',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_GENDER_IN' => ' جنس المستهدفين ',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_COUNT_IN' => ' عد المستفدين من المشروع ',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_SALARY_IN' => 'الراتب',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_CURRENCY_IN' => 'العملة',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_REGION_IN' => ' المحافظة المستهدفة ',
        ]);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
        } else {


            /*    PROCEDURE UPDATE_PROJECT_PR(
        PROJECT_ID_IN EMPLOYMENT_PROJECTS.PROJECT_ID%TYPE,
        PROJECT_CATEGORY_IN EMPLOYMENT_PROJECTS.PROJECT_CATEGORY%TYPE,
        PROJECT_NAME_IN EMPLOYMENT_PROJECTS.PROJECT_NAME%TYPE,
        START_DATE_IN EMPLOYMENT_PROJECTS.START_DATE%TYPE,
        END_DATE_IN EMPLOYMENT_PROJECTS.END_DATE%TYPE,
        FINANCIER_IN EMPLOYMENT_PROJECTS.FINANCIER%TYPE,
        PARTNER_IN EMPLOYMENT_PROJECTS.PARTNER%TYPE,
        PROJECT_DESCRIPTION_IN EMPLOYMENT_PROJECTS.PROJECT_DESCRIPTION%TYPE,
        PROJECT_IDEA_IN EMPLOYMENT_PROJECTS.PROJECT_IDEA%TYPE,
        UPDATED_BY_IN EMPLOYMENT_PROJECTS.CREATED_BY%TYPE,
        ACTUAL_START_DATE_IN EMPLOYMENT_PROJECTS.ACTUAL_START_DATE%TYPE,
        TARGET_DURATION_IN EMPLOYMENT_PROJECTS.TARGET_DURATION%TYPE,
        PROJECT_BUDGET_IN EMPLOYMENT_PROJECTS.PROJECT_BUDGET%TYPE,
        CURRENCY_IN EMPLOYMENT_PROJECTS.CURRENCY%TYPE,
        ACTUAL_END_DATE_IN EMPLOYMENT_PROJECTS.ACTUAL_END_DATE%TYPE,
        STATUS_IN EMPLOYMENT_PROJECTS.STATUS%TYPE,
        AUTO_CLOSE_IN EMPLOYMENT_PROJECTS.AUTO_CLOSE%TYPE,
        ERROR_FLAG OUT NUMBER
      )
              */


            $ERROR_FLAG = 0;
            $procedureName2 = 'employment_pkg.UPDATE_PROJECT_PR';
            $bindings2 = [
                'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                'PROJECT_CATEGORY_IN' => '',
                'PROJECT_NAME_IN' => $request->PROJECT_NAME_IN,
                "START_DATE_IN" => Carbon::createFromFormat('Y-m-d', $request->START_DATE_IN)->format('Y/m/d H:i:s'),
                "END_DATE_IN" => Carbon::createFromFormat('Y-m-d', $request->END_DATE_IN)->format('Y/m/d H:i:s'),
                'FINANCIER_IN' => $request->FINANCIER_IN,
                'PARTNER_IN' => $request->PARTNER_IN,
                //   'PROJECT_DESCRIPTION_IN' => $request->PROJECT_DESCRIPTION_IN,
                'PROJECT_DESCRIPTION_IN' => $request->PROJECT_DESCRIPTION_IN,
                'PROJECT_IDEA_IN' => $request->PROJECT_IDEA_IN,

                //  'PROJECT_IDEA_IN' => $request->PROJECT_IDEA_IN,
                'UPDATED_BY_IN' => session('user_id'),
                "ACTUAL_START_DATE_IN" => Carbon::createFromFormat('Y-m-d', $request->ACTUAL_START_DATE_IN)->format('Y/m/d H:i:s'),
                'TARGET_DURATION_IN' => $request->TARGET_DURATION_IN,
                'PROJECT_BUDGET_IN' => $request->PROJECT_BUDGET_IN,
                'CURRENCY_IN' => $request->CURRENCY_IN,
                "ACTUAL_END_DATE_IN" => Carbon::createFromFormat('Y-m-d', $request->ACTUAL_END_DATE_IN)->format('Y/m/d H:i:s'),
                "STATUS_IN" => $request->status, // h:i A
                'AUTO_CLOSE_IN' => $request->AUTO_CLOSE_IN,
                'codeMsg' => [
                    'value' => &$codeMsg,
                    'length' => 9,
                ],
            ];
            $result2 = DB::connection('oracle')->executeProcedure($procedureName2, $bindings2);


            if (isset($_POST["PROJECT_AREA"])) {


                $procedureName_del = 'employment_pkg.DELETE_PROJECT_AREA_PR';
                $bindings_del = [
                    'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                ];
                $result_del = DB::connection('oracle')->executeProcedure($procedureName_del, $bindings_del);


                foreach ($PROJECT_AREA as $pr) {
                    $procedureName3 = 'employment_pkg.add_project_area_pr';
                    $bindings3 = [
                        'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                        "AREA_ID_IN" => $pr,
                    ];
                    $result3 = DB::connection('oracle')->executeProcedure($procedureName3, $bindings3);
                }
            }


            if (isset($_POST["PROJECT_AREA_DEPT"])) {


                $procedureName_dept_del = 'employment_pkg.DELETE_PROJECT_AREA_DET_PR';
                $bindings_dept_del = [
                    'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                ];
                $result_dept_del = DB::connection('oracle')->executeProcedure($procedureName_dept_del, $bindings_dept_del);


                foreach ($PROJECT_AREA as $pr) {
                    $procedureName3 = 'employment_pkg.add_project_area_det_pr';
                    $bindings3 = [
                        'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                        "AREA_ID_IN" => $pr,
                    ];
                    $result3 = DB::connection('oracle')->executeProcedure($procedureName3, $bindings3);
                }
            }


            if (isset($_POST["SIDE_ID_IN"])) {


                $procedureName_side_del = 'employment_pkg.DELETE_PROJECT_SIDE_PR';
                $bindings_side_del = [
                    'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                ];
                $result_dept_del = DB::connection('oracle')->executeProcedure($procedureName_side_del, $bindings_side_del);


                foreach ($SIDE_ID_IN as $pr) {
                    $procedureName6 = 'employment_pkg.ADD_PROJECT_SIDE_PR';
                    $bindings6 = [
                        'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                        "SIDE_ID_IN" => $pr,
                    ];
                    $result6 = DB::connection('oracle')->executeProcedure($procedureName6, $bindings6);

                }
            }


            if (isset($_POST["EMPLOYMENT_CONDITIONS"])) {
                DB::connection('oracle')->table('employment_projects_conditions')
                    ->where('PROJECT_ID', $request->PROJECT_ID_IN)
                    ->delete();


                foreach ($EMPLOYMENT_CONDITIONS as $pr) {
                    $procedureName9 = 'employment_pkg.ADD_PROJECT_CONDITIONS_PR';
                    $bindings9 = [
                        'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                        "CONDITION_IN" => $pr,
                    ];
                    $result6 = DB::connection('oracle')->executeProcedure($procedureName9, $bindings9);

                }
            }


            foreach ($request->kt_docs_repeater_advanced_edu as $key => $value) {
                $procedureName5 = 'employment_pkg.ADD_EMPLOYMENT_BENEFICIARY_PR';
                $bindings5 = [
                    'ID_IN' => $value['BENEFICIARY_ID'],
                    'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                    'BENEFICIARY_TYPE_IN' => $value['BENEFICIARY_TYPE_IN'],
                    "BENEFICIARY_SUB_TYPE_IN" => $value['BENEFICIARY_SUB_TYPE_IN'],
                    "BENEFICIARY_GENDER_IN" => $value['BENEFICIARY_GENDER_IN'],
                    'BENEFICIARY_COUNT_IN' => $value['BENEFICIARY_COUNT_IN'],
                    'BENEFICIARY_SALARY_IN' => $value['BENEFICIARY_SALARY_IN'],
                    'BENEFICIARY_CURRENCY_IN' => $value['BENEFICIARY_CURRENCY_IN'],
                    'BENEFICIARY_REGION_IN' => $value['BENEFICIARY_REGION_IN']
                ];
                $result5 = DB::connection('oracle')->executeProcedure($procedureName5, $bindings5);
            }


            $result['status'] = $result2;
            $result['message'] = 'تم الحفظ بنجاح';
            $result['url'] = route('projects.tbl');
        }
        return response()->json($result);
    }


    public function upd_status(Request $request)
    {
        $id = $request->id;
        $project = DB::connection('oracle')->table('EMPLOYMENT_PROJECTS')->where('PROJECT_ID', $id)->first();

        //  dd($project);
        $PROJECT_AREA_LIST = DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_AREAS')->select('area_id')->where('PROJECT_ID', $id)->pluck('area_id');
        $PROJECT_AREA_LIST = $PROJECT_AREA_LIST->toArray();


//$EMPLOYMENT_PROJECTS_AREA_DET =  DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_AREA_DET')->get();
//$EMPLOYMENT_PROJECTS_AREA_DET =  DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_AREAS') ->select('area_id')->where('PROJECT_ID',$id)->get();

        $EMPLOYMENT_PROJECTS_AREA_DET = DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_AREA_DET')->select('ad_id')->where('PROJECT_ID', $id)->pluck('ad_id');
        $EMPLOYMENT_PROJECTS_AREA_DET = $EMPLOYMENT_PROJECTS_AREA_DET->toArray();

        $EMPLOYMENT_PROJECTS_SIDES = DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_SIDES')->select('side_id')->where('PROJECT_ID', $id)->pluck('side_id');
        $EMPLOYMENT_PROJECTS_SIDES = $EMPLOYMENT_PROJECTS_SIDES->toArray();

        $EMPLOYMENT_BENEFICIARIES = DB::connection('oracle')->table('EMPLOYMENT_BENEFICIARIES')->where('PROJECT_ID', $id)->get();


        $employment_conditions_LIST = DB::connection('oracle')->table('employment_projects_conditions')->select('condition_id')->where('PROJECT_ID', $id)->pluck('condition_id');
        $employment_conditions_LIST = $employment_conditions_LIST->toArray();

        $status = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15006], $cur = ':cur');

        $page_title = 'عرض تفاصيل المشروع';
        $sel_worker = "1";
        $sub_add_worker = "1";
        $main_title = 'بيانات العمال';
        $title_header = 'العمال';
        $sub_title = 'ادخال بيانات العمال';
        $sub_header = 'ادخال بيانات العمال';
        $sel_worker = array("sel_worker", "sub_add_worker", "main_title", "sub_header", "title_header", "sub_title", "page_title");
        return view('employments.projects.updstatus', compact($sel_worker, 'status', 'project'));
    }


    public function updstatus(Request $request)
    {
        $input = $request->all();
        $PROJECT_ID_IN = $request->PROJECT_ID_IN;
        $status_old = $request->old('STATUS_IN');


        $validator = Validator::make($request->all(), [
            'PROJECT_ID_IN' => 'required',
            'STATUS_IN' => 'required',

        ], [
            'PROJECT_ID_IN.required' => 'رقم المشروع',
            'STATUS_IN.required' => 'حالة المشروع',

        ]);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
        } else {
            $ERROR_FLAG = 0;
            $procedureName2 = 'employment_pkg.UPDATE_PROJECT_STATUS_PR';
            $bindings2 = [
                'PROJECT_ID_IN' => $request->PROJECT_ID_IN,
                "STATUS_IN" => $request->STATUS_IN,
                'codeMsg' => [
                    'value' => &$codeMsg,
                    'length' => 9,
                ],
            ];
            $result2 = DB::connection('oracle')->executeProcedure($procedureName2, $bindings2);
            $result['status'] = $result2;
            $result['message'] = 'تم الحفظ بنجاح';
            $result['url'] = route('projects.tbl');
        }
        return response()->json($result);
    }


    public function index()
    {
        $CONT_TYPE_ID = DB::connection('oracle')->table('CONTACTS')->where('CONT_TYPE_ID', 64)->get();
        $page_title = 'إدارة المشاريع';
        return view('employments.projects.index', compact('page_title', 'CONT_TYPE_ID'));
    }


    public function create()
    {
        // $this->authorize('create',Project::class);
// $totalRecords = DB::connection('oracle')->table('workers_info_vw')->get();

//  dd($CONT_TYPE_ID);
//  dd(Auth::user());
//$totalRecords = DB::connection('oracle')->table('REG_48_CALC_SUM_WORKERS_WORKDAYS_TB')->count();
//dd($totalRecords);
//  $result = DB::connection('oracle')->executeProcedure('REG_48_CONSTANTS_PKG', $bindings);
        $cnx = DB::connection('oracle');
        try {
// $dbconnect = DB::connection()->getPDO();
//$dbconnect = DB::connection('oracle')->getPDO();
            $dbconnect = $cnx->getPDO();
            //dd("Connected successfully to the database");
        } catch (\Exception $e) {
            //dd("Error in connecting to the database") ;
        }
        /*
        $list_total = Orcl::serachspendcount();
        */
        /*$list = Orcl::seserachcrscount(15001);
        $coins = $list["crs"];
        foreach ($list as $x) {
        $STATUS_ID = $x['STATUS_ID'];
        $STATUS_NAME = $x['STATUS_NAME'];
        echo $STATUS_ID.$STATUS_NAME."<br>";
        }*/
        /*$list = Orcl::seserachcrscount(15002);
        $list_job = $list["crs"];
        foreach ($list as $x) {
        $STATUS_ID = $x['STATUS_ID'];
        $STATUS_NAME = $x['STATUS_NAME'];
        echo $STATUS_ID.$STATUS_NAME."<br>";
        }*/
        $CONT_TYPE_ID = DB::connection('oracle')->table('CONTACTS')->where('CONT_TYPE_ID', 64)->get();
        $SEX = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15502], $cur = ':cur');
        $coins = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15001], $cur = ':cur');
        $list_job = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15002], $cur = ':cur');
        $type_in = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15007], $cur = ':cur');
        $sub_type_in = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15008], $cur = ':cur');
        $PROJECT_AREA = DB::connection('oracle')->table('GOVERNORATES')->get();
        $PROJECT_AREA_DEPT = DB::connection('oracle')->table('AREAS')->get();
        $EMPLOYMENT_CONDITIONS = DB::connection('oracle')->table('employment_conditions')->get();

        //$list_data= response()->json($list_data);
//var_dump($list_data);
        $page_title = 'إضافة مشروع جديد';
        $sel_worker = "1";
        $sub_add_worker = "1";
        $main_title = 'بيانات العمال';
        $title_header = 'العمال';
        $sub_title = 'ادخال بيانات العمال';
        $sub_header = 'ادخال بيانات العمال';
        $sel_worker = array("sel_worker", "sub_add_worker", "main_title", "sub_header", "title_header", "sub_title", "page_title");
//$sexs = DB::table('sex')->where('sex_active', 1)->get();
//$states = DB::table('state')->where('state_active', 1)->get();
//return view('dashboard.workers.index',compact('sexs','states', $sel_worker,'coins','list_job'));
        return view('employments.projects.create', compact($sel_worker, 'coins', 'list_job', 'sub_type_in', 'type_in', 'PROJECT_AREA', 'PROJECT_AREA_DEPT', 'CONT_TYPE_ID', 'SEX', 'EMPLOYMENT_CONDITIONS'));


    }


    public function store(Request $request)
    {
// dd(Carbon::parse($request->START_DATE_IN)->format('d-m-Y'));
//   dd($request->all());
        /*
        $START_DATE_IN = Carbon::parse($request->START_DATE_IN)->format('d-M-Y');
        $END_DATE_IN = Carbon::parse($request->END_DATE_IN)->format('d-M-Y');
        $ACTUAL_START_DATE_IN = Carbon::parse($request->ACTUAL_START_DATE_IN)->format('d-M-Y');
        $ACTUAL_END_DATE_IN = Carbon::parse($request->ACTUAL_END_DATE_IN)->format('d-M-Y');
        */
        /*
        $START_DATE_IN ='2023-11-09';
        $END_DATE_IN ='2024-06-09';
        $ACTUAL_START_DATE_IN ='2023-11-09';
        $ACTUAL_END_DATE_IN ='2024-06-09';
        /*$START_DATE_IN ='09-11-2023';
        $END_DATE_IN ='10-06-2024';
        $ACTUAL_START_DATE_IN ='09-11-23';
        $ACTUAL_END_DATE_IN ='10-06-2024';*/
        /*
        if ($START_DATE_IN != '') {
        $START_DATE_IN = date("d/m/Y", strtotime($START_DATE_IN));
        if ($START_DATE_IN == '01/01/1970') {
        $START_DATE_IN = false;
        }
        }
        if ($END_DATE_IN != '') {
        $END_DATE_IN = date("d/m/Y", strtotime($END_DATE_IN));
        if ($END_DATE_IN == '01/01/1970') {
        $END_DATE_IN = false;
        }
        }


        if ($ACTUAL_START_DATE_IN != '') {
        $ACTUAL_START_DATE_IN = date("d/m/Y", strtotime($ACTUAL_START_DATE_IN));
        if ($ACTUAL_START_DATE_IN == '01/01/1970') {
        $ACTUAL_START_DATE_IN = false;
        }
        }

        if ($ACTUAL_END_DATE_IN != '') {
        $ACTUAL_END_DATE_IN = date("d/m/Y", strtotime($ACTUAL_END_DATE_IN));
        if ($ACTUAL_END_DATE_IN == '01/01/1970') {
        $ACTUAL_END_DATE_IN = false;
        }
        }*/

        /*echo $START_DATE_IN ."<br>".$END_DATE_IN;*/
        /*
        $START_DATE_IN = date("d/m/Y", strtotime($request->START_DATE_IN));
        $END_DATE_IN = date("d/m/Y", strtotime($request->END_DATE_IN));
        $ACTUAL_START_DATE_IN = date("d/m/Y", strtotime($request->ACTUAL_START_DATE_IN));
        $ACTUAL_END_DATE_IN= date("d/m/Y", strtotime($request->ACTUAL_END_DATE_IN));*/


        $input = $request->all();
        $PROJECT_AREA = $request->PROJECT_AREA;
        $PROJECT_AREA_DEPT = $request->PROJECT_AREA_DEPT;
        $SIDE_ID_IN = $request->SIDE_ID_IN;
        $EMPLOYMENT_CONDITIONS = $request->EMPLOYMENT_CONDITIONS;


        $PROJECT_NAME_IN_OLD = $request->old('PROJECT_NAME_IN');
        $validator = Validator::make($request->all(), [
// 'NAME_IN' => 'required|max:255',
//  'MOBILE_IN' => 'required',

            'PROJECT_NAME_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_TYPE_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_SUB_TYPE_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_GENDER_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_COUNT_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_SALARY_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_CURRENCY_IN' => 'required',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_REGION_IN' => 'required',
        ], [
            'PROJECT_NAME_IN.required' => 'اسم المشروع',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_TYPE_IN' => 'المؤهل المستفدين ',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_SUB_TYPE_IN' => 'التخصص',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_GENDER_IN' => ' جنس المستهدفين ',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_COUNT_IN' => ' عد المستفدين من المشروع ',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_SALARY_IN' => 'الراتب',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_CURRENCY_IN' => 'العملة',
            'kt_docs_repeater_advanced_edu.*.BENEFICIARY_REGION_IN' => ' المحافظة المستهدفة ',


        ]);


        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
        } else {
            /*
            $START_DATE_IN = date("d-M-y", strtotime($request->START_DATE_IN));
            $END_DATE_IN = date("d-M-y", strtotime($request->END_DATE_IN));
            $ACTUAL_START_DATE_IN = date("d-M-y", strtotime($request->ACTUAL_START_DATE_IN));
            $ACTUAL_END_DATE_IN = date("d-M-y", strtotime($request->ACTUAL_END_DATE_IN));*/

            /*    $START_DATE_IN= Carbon::createFromFormat('Y-m-d', $request->START_DATE_IN)->format('d-M-Y');
                $END_DATE_IN = Carbon::createFromFormat('Y-m-d', $request->END_DATE_IN)->format('d-M-Y');

                $ACTUAL_START_DATE_IN= Carbon::createFromFormat('Y-m-d', $request->ACTUAL_START_DATE_IN)->format('d-M-Y');
                $ACTUAL_END_DATE_IN= Carbon::createFromFormat('Y-m-d', $request->ACTUAL_END_DATE_IN)->format('d-M-Y');*/


            $ERROR_FLAG = 0;
            $procedureName2 = 'employment_pkg.add_new_project_pr';
            $bindings2 = [
                'PROJECT_CATEGORY_IN' => '',
                'PROJECT_NAME_IN' => $request->PROJECT_NAME_IN,
                "START_DATE_IN" => $request->START_DATE_IN,

                "END_DATE_IN" => $request->END_DATE_IN,
                /*  "START_DATE_IN" =>$START_DATE_IN,
                   "END_DATE_IN" =>$END_DATE_IN, */
                'FINANCIER_IN' => $request->FINANCIER_IN,
                'PARTNER_IN' => $request->PARTNER_IN,
                'PROJECT_DESCRIPTION_IN' => $request->PROJECT_DESCRIPTION_IN,
                'PROJECT_IDEA_IN' => $request->PROJECT_IDEA_IN,
                'CREATED_BY_IN' => session('user_id'),
                "ACTUAL_START_DATE_IN" => $request->ACTUAL_START_DATE_IN,// h:i A
                // "ACTUAL_START_DATE_IN" =>$ACTUAL_START_DATE_IN,// h:i A
                'TARGET_DURATION_IN' => $request->TARGET_DURATION_IN,
                'PROJECT_BUDGET_IN' => $request->PROJECT_BUDGET_IN,
                'CURRENCY_IN' => $request->CURRENCY_IN,
                "ACTUAL_END_DATE_IN" => $request->ACTUAL_END_DATE_IN, // h:i A
                //  "ACTUAL_END_DATE_IN" =>$ACTUAL_END_DATE_IN, // h:i A

                'AUTO_CLOSE_IN' => $request->AUTO_CLOSE_IN,
                'codeMsg' => [
                    'value' => &$codeMsg,
                    'length' => 9,
                ],
                'titleMsg' => [
                    'value' => &$titleMsg,
                    'length' => 255,
                ],

            ];
            $result2 = DB::connection('oracle')->executeProcedure($procedureName2, $bindings2);

            if (isset($_POST["PROJECT_AREA"])) {
                foreach ($PROJECT_AREA as $pr) {
                    $procedureName3 = 'employment_pkg.add_project_area_pr';
                    $bindings3 = [
                        'PROJECT_ID_IN' => $codeMsg,
                        "AREA_ID_IN" => $pr,
                    ];
                    $result3 = DB::connection('oracle')->executeProcedure($procedureName3, $bindings3);

                }
            }


            if (isset($_POST["PROJECT_AREA_DEPT"])) {
                foreach ($PROJECT_AREA_DEPT as $pr) {
                    $procedureName4 = 'employment_pkg.add_project_area_det_pr';
                    $bindings4 = [
                        'PROJECT_ID_IN' => $codeMsg,
                        "AD_ID_IN" => $pr,
                    ];
                    $result4 = DB::connection('oracle')->executeProcedure($procedureName4, $bindings4);

                }
            }


            foreach ($request->kt_docs_repeater_advanced_edu as $key => $value) {
//Student::create($value);
// dd($value) ;

// echo        $value['BENEFICIARY_COUNT_IN'];
                $procedureName5 = 'employment_pkg.ADD_EMPLOYMENT_BENEFICIARY_PR';
                $bindings5 = [
                    'ID_IN' => '',
                    'PROJECT_ID_IN' => $codeMsg,
                    'BENEFICIARY_TYPE_IN' => $value['BENEFICIARY_TYPE_IN'],
                    "BENEFICIARY_SUB_TYPE_IN" => $value['BENEFICIARY_SUB_TYPE_IN'],
                    "BENEFICIARY_GENDER_IN" => $value['BENEFICIARY_GENDER_IN'],
                    'BENEFICIARY_COUNT_IN' => $value['BENEFICIARY_COUNT_IN'],
                    'BENEFICIARY_SALARY_IN' => $value['BENEFICIARY_SALARY_IN'],
                    'BENEFICIARY_CURRENCY_IN' => $value['BENEFICIARY_CURRENCY_IN'],
                    'BENEFICIARY_REGION_IN' => $value['BENEFICIARY_REGION_IN']
                ];
                $result5 = DB::connection('oracle')->executeProcedure($procedureName5, $bindings5);
            }


            if (isset($_POST["SIDE_ID_IN"])) {
                foreach ($SIDE_ID_IN as $pr) {
                    $procedureName6 = 'employment_pkg.ADD_PROJECT_SIDE_PR';
                    $bindings6 = [
                        'PROJECT_ID_IN' => $codeMsg,
                        "SIDE_ID_IN" => $pr,
                    ];
                    $result6 = DB::connection('oracle')->executeProcedure($procedureName6, $bindings6);

                }
            }

            if (isset($_POST["EMPLOYMENT_CONDITIONS"])) {
                foreach ($EMPLOYMENT_CONDITIONS as $pr) {
                    $procedureName9 = 'employment_pkg.ADD_PROJECT_CONDITIONS_PR';
                    $bindings9 = [
                        'PROJECT_ID_IN' => $codeMsg,
                        "CONDITION_IN" => $pr,
                    ];
                    $result6 = DB::connection('oracle')->executeProcedure($procedureName9, $bindings9);

                }
            }
            /*
            $procedureName7 = 'employment_pkg.ADD_EMPLOYMENT_BENEFICIARY_PR';
            $bindings7 = [
            'PROJECT_ID_IN' =>$codeMsg,
            "BENEFICIARY_TYPE_IN" => $request->BENEFICIARY_TYPE_IN,
            "BENEFICIARY_SUB_TYPE_IN" => $request->BENEFICIARY_SUB_TYPE_IN,
            'BENEFICIARY_GENDER_IN' => $request->BENEFICIARY_GENDER_IN,
            'BENEFICIARY_COUNT_IN' => $request->BENEFICIARY_COUNT_IN,
            "BENEFICIARY_SALARY_IN" => $request->BENEFICIARY_SALARY_IN,
            'BENEFICIARY_CURRENCY_IN' => $request->BENEFICIARY_CURRENCY_IN,
            "BENEFICIARY_REGION_IN" => $request->BENEFICIARY_REGION_IN,
            ];
            $result7 = DB::connection('oracle')->executeProcedure($procedureName7,$bindings7); */
            /*
            $procedureName7 = 'employment_pkg.ADD_PROJECT_USER_COUNT_PR';
            $bindings7 = [
            'PROJECT_ID_IN' =>$codeMsg,
            'BENEFICIARY_COUNT_IN' => $request->BENEFICIARY_COUNT_IN,
            "BENEFICIARY_TYPE_IN" => $request->BENEFICIARY_TYPE_IN,
            "BENEFICIARY_REGION_IN" => $request->BENEFICIARY_REGION_IN,
            'BENEFICIARY_GENDER_IN' => $request->BENEFICIARY_GENDER_IN,
            ];
            $result7 = DB::connection('oracle')->executeProcedure($procedureName7,$bindings7); */
            /*$request->validate([
                        'kt_docs_repeater_advanced.*.subject' => 'required'
                    ]);*/
            /*foreach ($request->kt_docs_repeater_advanced as $key => $value) {
               $procedureName7 = 'employment_pkg.ADD_PROJECT_USER_COUNT_PR';
            $bindings7 = [
            'PROJECT_ID_IN' =>$codeMsg,
            'BENEFICIARY_COUNT_IN' =>$value['BENEFICIARY_COUNT_IN'],
            "BENEFICIARY_TYPE_IN" =>$value['BENEFICIARY_TYPE_IN'],
            "BENEFICIARY_REGION_IN" =>$value['BENEFICIARY_REGION_IN'],
            'BENEFICIARY_GENDER_IN' =>$value['BENEFICIARY_GENDER_IN'],
            ];
            $result7 = DB::connection('oracle')->executeProcedure($procedureName7,$bindings7);
                    }*/
            //  dd($codeMsg);
            // echo $result2;
            //$result2[0]->ObjectId;
            //dd($result2[0]);
            //  $coins = $result2["codeMsg"];
            // $result2=  response()->json($result2);
            //   $totalRecords = DB::connection('oracle')->table('workers_info_vw')->count();
            //  $user_id = session('user_id');
//echo $user_id;
            //       $totalRecords = DB::connection('oracle')->table('workers_info_vw')->where('ssn',session('user_id'))->get();
//dd($totalRecords);
            // dd(DB::connection('oracle')->table('workers_info_vw') )   ;
            //  $all=DB::connection('oracle')->table('workers_info_vw')   ;
            // dd($all) ;
            $result['status'] = $result2;
            $result['message'] = 'تم الحفظ بنجاح';
            //  $result['url'] = route('operator.workers.tbl');
        }
        return response()->json($result);
    }


    public function show_project($id)
    {
        // $this->authorize('view',Project::class);
        $user = User::findOrFail($id);
        $page_title = 'عرض تفاصيل المشروع';
        return view('employments.projects.show', ['page_title' => $page_title, 'user' => $user]);
    }

    public function upd_project(Request $request)
    {
        $id = $request->id;
        $project = DB::connection('oracle')->table('EMPLOYMENT_PROJECTS')->where('PROJECT_ID', $id)->first();

        //  dd($project);
        $PROJECT_AREA_LIST = DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_AREAS')->select('area_id')->where('PROJECT_ID', $id)->pluck('area_id');
        $PROJECT_AREA_LIST = $PROJECT_AREA_LIST->toArray();


//$EMPLOYMENT_PROJECTS_AREA_DET =  DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_AREA_DET')->get();
//$EMPLOYMENT_PROJECTS_AREA_DET =  DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_AREAS') ->select('area_id')->where('PROJECT_ID',$id)->get();

        $EMPLOYMENT_PROJECTS_AREA_DET = DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_AREA_DET')->select('ad_id')->where('PROJECT_ID', $id)->pluck('ad_id');
        $EMPLOYMENT_PROJECTS_AREA_DET = $EMPLOYMENT_PROJECTS_AREA_DET->toArray();

        $EMPLOYMENT_PROJECTS_SIDES = DB::connection('oracle')->table('EMPLOYMENT_PROJECTS_SIDES')->select('side_id')->where('PROJECT_ID', $id)->pluck('side_id');
        $EMPLOYMENT_PROJECTS_SIDES = $EMPLOYMENT_PROJECTS_SIDES->toArray();

        $EMPLOYMENT_BENEFICIARIES = DB::connection('oracle')->table('EMPLOYMENT_BENEFICIARIES')->where('PROJECT_ID', $id)->get();


        $employment_conditions_LIST = DB::connection('oracle')->table('employment_projects_conditions')->select('condition_id')->where('PROJECT_ID', $id)->pluck('condition_id');
        $employment_conditions_LIST = $employment_conditions_LIST->toArray();

        $CONT_TYPE_ID = DB::connection('oracle')->table('CONTACTS')->where('CONT_TYPE_ID', 64)->get();
        $SEX = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15502], $cur = ':cur');
        $coins = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15001], $cur = ':cur');
        $list_job = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15002], $cur = ':cur');
        $type_in = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15007], $cur = ':cur');
        $sub_type_in = DB::connection('oracle')->executeProcedureWithCursor('EMPLOYMENT_PKG.GET_CONSTANTS', ['c_id' => 15008], $cur = ':cur');
        $PROJECT_AREA = DB::connection('oracle')->table('GOVERNORATES')->get();
        $PROJECT_AREA_DEPT = DB::connection('oracle')->table('AREAS')->get();
        $EMPLOYMENT_CONDITIONS = DB::connection('oracle')->table('employment_conditions')->get();

        $page_title = 'عرض تفاصيل المشروع';
        $sel_worker = "1";
        $sub_add_worker = "1";
        $main_title = 'بيانات العمال';
        $title_header = 'العمال';
        $sub_title = 'ادخال بيانات العمال';
        $sub_header = 'ادخال بيانات العمال';
        $sel_worker = array("sel_worker", "sub_add_worker", "main_title", "sub_header", "title_header", "sub_title", "page_title");
        return view('employments.projects.upd', compact($sel_worker, 'project', 'page_title', 'coins', 'list_job', 'sub_type_in', 'type_in', 'PROJECT_AREA', 'PROJECT_AREA_DEPT', 'CONT_TYPE_ID', 'SEX', 'PROJECT_AREA_LIST', 'EMPLOYMENT_PROJECTS_AREA_DET', 'EMPLOYMENT_PROJECTS_SIDES', 'EMPLOYMENT_BENEFICIARIES', 'EMPLOYMENT_CONDITIONS', 'employment_conditions_LIST'));
    }


    public function edit($id)
    {
        // $this->authorize('update',Project::class);
        $page_title = 'تعديل بيانات المستخدم';
        $user = User::findOrFail($id);
        return view('employments.projects.edit', ['page_title' => $page_title, 'user' => $user]);
    }


    public function update(Request $request, $id)
    {
        // $this->authorize('update',Project::class);
        $user = User::findOrFail($id);
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->address = $request->address;
        $user->dept_id = $request->department_id;
        $user->role_id = $request->role_id;
        if (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->update();
        return redirect()->route('projects.index');
    }


    public function destroy(User $user)
    {
        $user->delete();
        return response()->json();
    }


}
