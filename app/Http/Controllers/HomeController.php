<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Workers;
use App\Models\Moraslat;
use App\Models\Financial;
use App\Models\Calculate;
use App\Models\TheTask;
use App\Helpers\Perm;

//use App\Http\Requests\StoreEmpsRequest;
//use App\Http\Requests\UpdateEmpsRequest;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }


    public function     notify_num ()    {
      //  $count_list= count($listmoraslat);
     //   return view('load_alerts', compact('listmoraslat','count_list'));

     $count_notify = Moraslat::serachspendhomecount();
        // Spec 001 FR-007 — include unread AI alerts (lease due/expiry etc.) in the bell count.
        $count_notify += \App\Models\AppNotification::where('user_id', \Auth::id())->where('is_read', false)->count();
        $result['count_notify'] =$count_notify;
        echo json_encode($result);



    }




    public function load_alerts()
    {
        $listmoraslat = Moraslat::serachspendhome();
      //  $count_list= count($listmoraslat);
        $count_list = Moraslat::serachspendhomecount();

        return view('load_alerts', compact('listmoraslat','count_list'));

    }








    public function index(Request $request)
    {
//        dd(Hash::make('232046'));
        $page_title = 'شركة صباح النور';

        // Home worker-widget filters (iqama + passport expiry). Sanitised here so only clean
        // values reach the raw-SQL scope: dates must be Y-m-d, statuses a single 1-4. Anything
        // else becomes '' (no constraint), so by default the widget shows ALL workers.
        $cleanDate = function ($v) {
            $v = is_string($v) ? trim($v) : '';
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';
        };
        $cleanStatus = function ($v) {
            return in_array($v, ['1', '2', '3', '4'], true) ? $v : '';
        };
        $doe_from   = $cleanDate($request->input('doe_from'));
        $doe_to     = $cleanDate($request->input('doe_to'));
        $dop_from   = $cleanDate($request->input('dop_from'));
        $dop_to     = $cleanDate($request->input('dop_to'));
        $doe_status = $cleanStatus($request->input('doe_status'));
        $dop_status = $cleanStatus($request->input('dop_status'));
        $filters = compact('doe_from', 'doe_to', 'dop_from', 'dop_to', 'doe_status', 'dop_status');

        $listworker = Workers::serachspendhome($doe_from, $doe_to, $dop_from, $dop_to, $doe_status, $dop_status);
        $listmoraslat = Moraslat::serachspendhome();



        $year=Carbon::now()->format('Y');
       $month=Carbon::now()->format('m');

        $listworkersum_all = Financial::sumspendcounthome('',$year);
        $sum_c1f = 0;
        $sum_count_statement = 0;
        $sum_sum_det_financial_month_pay_Allf = 0;
        $sum_xxf = 0;

        foreach ($listworkersum_all as $x_sum) {
            $c1 = $x_sum->c1;
            $count_statement = $x_sum->count_statement;
            $sum_det_financial_month_pay_all = $x_sum->sum_det_financial_month_pay;
            $xx = $x_sum->xx;
            $sum_c1f += $c1;
            $sum_count_statement += $count_statement;
            $sum_sum_det_financial_month_pay_Allf += $sum_det_financial_month_pay_all;
            $sum_xxf += $xx;
        }
        $const = array("sum_c1f", "sum_sum_det_financial_month_pay_Allf", "sum_xxf");
      //  $listworkersum_spec = Financial::sumspendcounthome($month,$year);


        $listworkersum_all_s = Calculate::sumspendcounthome('',$year);
        $sum_c1 = 0;
        $sum_count_statement = 0;
        $sum_sum_det_calculate_month_pay_All = 0;
        $sum_xx = 0;

        foreach ($listworkersum_all_s as $x_sum) {
            $c1 = $x_sum->c1;
            $count_statement = $x_sum->count_statement;
            $sum_det_calculate_month_pay_all = $x_sum->sum_det_calculate_month_pay;
            $xx = $x_sum->xx;
            $sum_c1 += $c1;
            $sum_count_statement += $count_statement;
            $sum_sum_det_calculate_month_pay_All += $sum_det_calculate_month_pay_all;
            $sum_xx += $xx;
    }
        $const2 = array( "sum_c1", "sum_sum_det_calculate_month_pay_All", "sum_xx");




        $result = Workers::workercharthome();
        $result= $result;
     //   $ch_data_bar = json_encode($result);


        $z = count($result);
        if ($z == 0) {
           // $data_ar[] = array('label' => 'لا يوجد بيانات', 'value' => 'لا يوجد بيانات');
           $data_ar[] = array( 'لا يوجد بيانات' );
           $data_ar2[] = array('لا يوجد بيانات' );


        } else {
            foreach ($result as $s) {
                $data_ar[] = array(round($s->COUNT_ROW) );
                $data_ar2[] = array( $s->SHOP_ID );

            }
        }
      //  $ch_data_donut= json_encode($data_ar);
        $ch_data_bar = json_encode($data_ar);
        $ch_data_bar2 = json_encode($data_ar2);


        $const3 = array( "ch_data_bar","ch_data_bar2");

        // Spec 005 T-B3 — home AI insight card. Never throws: HomeInsightService::insight()
        // catches its own Gemini failures and returns fallback=>true with raw numbers.
        try {
            $insight = app(\App\Services\HomeInsightService::class)->insight();
        } catch (\Throwable $e) {
            $insight = ['summary' => null, 'deltas' => [], 'fallback' => true];
        }

        // Tasks widget (perm 88 = tasks-module view, same gate as TaskController@index).
        // Never throws: mirrors the HomeInsightService defensive pattern so a schema
        // surprise can't take the whole homepage down.
        $listtasks = collect();
        $overdue_tasks_count = 0;
        try {
            if (Perm::get_function_access(88)) {
                $activeSchedule = function ($q) { $q->where('status', '!=', 'مكتمل'); };
                $listtasks = TheTask::with(['worker', 'service', 'schedule'])
                    ->whereHas('schedule', $activeSchedule)
                    ->orderByRaw('due_date IS NULL, due_date ASC')
                    ->limit(15)
                    ->get();
                $overdue_tasks_count = TheTask::whereHas('schedule', $activeSchedule)
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', Carbon::today())
                    ->count();
            }
        } catch (\Throwable $e) {
            $listtasks = collect();
            $overdue_tasks_count = 0;
        }

        // Vacations widget (perms 63-67, same gate set as VacationController). The
        // vacation table has no approval-status column, so status is derived at
        // render time: ongoing / upcoming / ended. Never throws.
        $listvacations = collect();
        try {
            if (Perm::get_function_access(63) || Perm::get_function_access(64) || Perm::get_function_access(65)
                || Perm::get_function_access(66) || Perm::get_function_access(67)) {
                $listvacations = DB::table('vacation as v')
                    ->join('workers as w', 'v.worker_id', '=', 'w.worker_id')
                    ->leftJoin('vacation_type as vt', 'v.vacation_type_id', '=', 'vt.vacation_type_id')
                    ->where('v.is_deleted', 0)
                    ->orderBy('v.vacation_id', 'desc')
                    ->limit(15)
                    ->get(['v.vacation_id', 'v.start', 'v.end', 'w.worker_name', 'vt.vacation_type_name']);
            }
        } catch (\Throwable $e) {
            $listvacations = collect();
        }

        return view('home', compact('page_title', 'listworker', 'listmoraslat', 'filters', 'insight', 'listtasks', 'overdue_tasks_count', 'listvacations', $const,  $const2,  $const3));

    }


    public function show_404()
    {
        $page_title = 'رسالة نظام';
        return view('show_404', compact('page_title'));

    }

    public function show_not_allow()
    {
        $page_title = 'رسالة نظام';
        return view('show_not_allow', compact('page_title'));

    }


    public function show_enter_data()
    {

        $list = Order::serachspenddata(session('user_id'));
        $page_title = 'رسالة نظام';
        return view('show_enter_data', compact('page_title', 'list'));


    }


    public function edit_profile()
    {
        $emps = DB::table('users')
            ->leftJoin('job_cat', 'users.emp_job', '=', 'job_cat.j_c_id')
            ->select('users.*', 'job_cat.j_c_name_ar')
            ->where('users.id', Auth::user()->id)->first();
        $page_title = 'تعديل الملف الشخصي';
        $sel_emps = array("page_title");
        return view('edit_profile', compact($sel_emps, 'emps'));
    }


    public function updateProfile(Request $request)
    {
        $id = $request->id_val;
        $role_per = $request->role_per;

        $attributeNames = array(
            'email' => 'الايميل',
            'password' => 'كلمة المرور',
            'password_confirmation' => 'تأكيد كلمة المرور',

            'phone' => 'رقم الجوال',

        );
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'max:255', 'string', 'email', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['required', 'numeric', Rule::unique('users', 'phone')->ignore($id)],
            'password' => ['required','confirmed','min:6'],
            'password_confirmation' => ['required'],



        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';

        } else {
            $ERROR_FLAG = 0;
            $user_photo = '';
            $result2=   DB::table('users')  ->where('id',$id)->update([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'emp_upd_user' => Auth::user()->id,
                'emp_upd_dt' => Carbon::now(),

            ]);




            $result['status'] = 1;
            $result['message_out'] = 'تم الحفظ بنجاح';

        }
        return response()->json($result);

    }

}
