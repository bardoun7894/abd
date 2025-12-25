<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Perm;

//use App\Models\Student;
trait ApimtitTrait
{
    public function jawdaapi()
    {
        $user_id = Auth::user()->id;
        $response = Http::withHeaders([
            'TOKEN' => 'XyYRLm5g6bDctPk2jtV8xiNnBWaU2KLeEmXDzJOY2SCSqyrPsQ'])->get('https://e.services.gov.ps/api/ministry/getAppsByServiceIDAndIdNo/' . $user_id);
        $jsonData = $response->json();
        return $jsonData;
//dd($jsonData);
//echo "<pre> status:";
        /*print_r($response->status());

                echo "<br/> ok:";

                print_r($response->ok());

                echo "<br/> successful:";

                print_r($response->successful());

                echo "<br/> serverError:";

                print_r($response->serverError());

                echo "<br/> clientError:";

                print_r($response->clientError());

                echo "<br/> headers:";

                print_r($response->headers());*/
    }


    public function get_manager()
    {
        if (Auth()->user()->emp_job == 1) {
            $manager = DB::table('manager')->get();
        } else {
            $manager = DB::table('manager')
                ->Join('workers_manager', 'manager.manager_id', '=', 'workers_manager.manager_id')
                ->where('workers_manager.user_id', Auth::user()->id)->get();
        }
        return $manager;
    }


    public function ishavegroupworker($worker_id)
    {
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {
            return 1;
        } else {
            $workers_chk = DB::table('workers')
                ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                ->where('workers_manager.user_id', Auth::user()->id)
                ->where('worker_id', $worker_id)->count();
            if (!$workers_chk) {
                //return redirect()->route('show_not_allow')->send();
                return 0;
            } else {
                return 1;
            }
        }
    }


    public function issamecreateworker($worker_id)
    {
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {
            return 1;
        } else {
            if (Perm::get_function_access(70)) {
                $workers_chk = DB::table('workers')
                    ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('workers.create_user', Auth::user()->id)
                    ->where('worker_id', $worker_id)->count();
            } else {
                $workers_chk = DB::table('workers')
                    ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('worker_id', $worker_id)->count();
            }
            if (!$workers_chk) {
                return 0;
            } else {
                return 1;
            }
        }
    }

    public function issamecreateshop($shop_id)
    {
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {
            return 1;
        } else {
            if (Perm::get_function_access(74)) {
                $shop_chk = DB::table('shop')
                    ->Join('workers_manager', 'shop.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('shop.create_user', Auth::user()->id)
                    ->where('shop_id', $shop_id)->count();
            } else {
                $shop_chk = DB::table('shop')
                    ->Join('workers_manager', 'shop.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('shop_id', $shop_id)->count();
            }
            if (!$shop_chk) {
                return 0;
            } else {
                return 1;
            }
        }
    }
    public function issamefinancialins($worker_id)
    {
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {
            return 1;
        } else {

            $workers_chk = DB::table('workers')
                ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                ->where('workers_manager.user_id', Auth::user()->id)
                ->where('worker_id', $worker_id)->count();

            if (!$workers_chk) {
                return 0;
            } else {
                return 1;
            }
        }
    }
    public function issamecreatefinancial($worker_id)
    {
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {
            return 1;
        } else {
            if (Perm::get_function_access(73)) {
                $workers_chk = DB::table('financial')
                    ->Join('workers', 'financial.worker_id', '=', 'workers.worker_id')
                    ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('financial.create_user', Auth::user()->id)
                    ->where('workers.worker_id', $worker_id)->count();
            } else {
                $workers_chk = DB::table('financial')
                    ->Join('workers', 'financial.worker_id', '=', 'workers.worker_id')
                    ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('workers.worker_id', $worker_id)->count();
            }
            if (!$workers_chk) {
                return 0;
            } else {
                return 1;
            }
        }
    }


    //دفعات مالية للنظم التشغيلية

    public function issamecreateexpense($worker_id)
    {
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {
            return 1;
        } else {
            if (Perm::get_function_access(73)) {
                $workers_chk = DB::table('expense')
                    ->Join('workers', 'expense.worker_id', '=', 'workers.worker_id')
                    ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('expense.create_user', Auth::user()->id)
                    ->where('workers.worker_id', $worker_id)->count();
            } else {
                $workers_chk = DB::table('expense')
                    ->Join('workers', 'expense.worker_id', '=', 'workers.worker_id')
                    ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('workers.worker_id', $worker_id)->count();
            }
            if (!$workers_chk) {
                return 0;
            } else {
                return 1;
            }
        }
    }


    public function issamecreatecalculate($shop_id)
    {
        
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {
            return 1;
        } else {
            if (Perm::get_function_access(75)) {
                dd(1);
                $shop_chk = DB::table('calculate')
                    ->Join('shop', 'calculate.shop_id', '=', 'shop.shop_id')
                    ->Join('workers_manager', 'shop.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('calculate.create_user', Auth::user()->id)
                    ->where('shop.shop_id', $shop_id)->count();
            } else {
                dd(2);
                $shop_chk = DB::table('calculate')
                    
                    ->Join('shop', 'calculate.shop_id', '=', 'shop.shop_id')
                    ->Join('workers_manager', 'shop.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('shop.shop _id', $shop_id)->count();
            }
            if (!$shop_chk) {
                return 0;
            } else {
                return 1;
            }
        }
    }




    public function issamecalculatesins($shop_id)
    {
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {
            return 1;
        } else {
            $shop_chk = DB::table('shop')
                ->Join('workers_manager', 'shop.manager_id', '=', 'workers_manager.manager_id')
                ->where('workers_manager.user_id', Auth::user()->id)
                ->where('shop_id', $shop_id)->count();

            if (!$shop_chk) {
                return 0;
            } else {
                return 1;
            }
        }

    }










    public function checkPermission()
    {
        $user_id = Auth::user()->id;
        dd($user_id);
    }


}
