<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Calculate;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TestingCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testing:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {


        $calculate_month_desc=  Carbon::parse(now())->format('m-Y');
        $calculate_month_desc=  Carbon::parse(now())->format('m-Y');
        $calculate_month_m=  Carbon::parse(now())->format('m');
    $calculate_month_y=  Carbon::parse(now())->format('Y');
         $shop = DB::table('shop')->get();
        foreach ($shop as $x) {
            $shop_id = $x->shop_id;
            $calculate_month_val = $x->calculate_month_val;
            $shop_name = $x->shop_name;
            $count_calculate = DB::table('calculate')->where('calculate_month_desc', '=' ,$calculate_month_desc )->where('shop_id', '=' ,$shop_id )->count();
            if ($count_calculate == 0) {

                $calculate_month_remain = $calculate_month_val - 0;
                $calculate_month_pay =  0;

                $calculate_id = DB::table('calculate')->insertGetId([
                                            'shop_id' => $shop_id,
                                            'calculate_month_desc' => $calculate_month_desc,
                                            'calculate_month_m' => $calculate_month_m,
                                            'calculate_month_y' => $calculate_month_y,
                                            'calculate_month_val' => $calculate_month_val,
                                            'note' =>'تم انشاء الفاتورة اوتوامتيك',

                                        ]);
                     /*    $result_upload = DB::table('calculate_detail')->insertGetId([
                            'calculate_id' => $calculate_id,
                            'calculate_month_val' => $calculate_month_val,
                            'calculate_month_pay' => $calculate_month_pay,
                            'calculate_month_remain' => $calculate_month_remain,
                        ]);*/

            }
        }
        $this->info('Successfully sent daily quote to everyone.');
    }
}
