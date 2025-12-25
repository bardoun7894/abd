<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Calculate;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WrokerCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worker:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cmmanad For Enter New Bill for new month-year';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {

        $financial_month_desc=  Carbon::parse(now())->format('m-Y');
        $financial_month_m=  Carbon::parse(now())->format('m');
        $financial_month_y=  Carbon::parse(now())->format('Y');
$payments_month_tbl = DB::table('payments_month')->where(['payments_month_m' => $financial_month_m, 'payments_month_y' => $financial_month_y])->first();
$payments_month_val=$payments_month_tbl->payments_month_val;
if(!$payments_month_val){$payments_month_val=500;}


        $workers = DB::table('workers')->get();
        foreach ($workers as $x) {
            $worker_id = $x->worker_id;
            $worker_name = $x->worker_name;






            $count_financial = DB::table('financial')->where('financial_month_desc', '=' ,$financial_month_desc )->where('worker_id', '=' ,$worker_id )->count();
            if ($count_financial == 0) {

                $financial_month_remain = $payments_month_val - 0;
                $financial_month_pay =  0;

                $financial_id = DB::table('financial')->insertGetId([
                                            'worker_id' => $worker_id,
                                            'financial_month_desc' => $financial_month_desc,
                                            'financial_month_m' => $financial_month_m,
                                            'financial_month_y' => $financial_month_y,
                                            'financial_month_val' => $payments_month_val,
                                            'note' =>'تم انشاء الفاتورة اوتوامتيك',
                                            'created_at' => Carbon::now(),
                                            'create_user' => Auth::user()->id,

                                        ]);
                   /*        $result_upload = DB::table('financial_detail')->insertGetId([
                            'financial_id' => $financial_id,
                            'financial_month_val' => $payments_month_val,
                            'financial_month_pay' => $financial_month_pay,
                            'financial_month_remain' => $financial_month_remain,
                            'note' => 'تم انشاء الفاتورة اوتوامتيك',
                            'created_at' => Carbon::now(),
                            'create_user' => Auth::user()->id,
                        ]);*/

            }




        }
        }
}
