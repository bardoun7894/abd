<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IsHaveGroupWorker


{

    public function handle(Request $request, Closure $next, $worker_id)

    {

        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {

            return $next($request);


        } else {
                    $workers_chk = DB::table('workers')
                    ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('worker_id',$worker_id)->count();



            if (!$workers_chk) {
                return redirect()->route('show_not_allow')->send();

            } else {
                return $next($request);

            }

        }




    }

}
