<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IsHaveAccess


{

    public function handle(Request $request, Closure $next, $parent_id)

    {


        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {

            return $next($request);


        } else {
            $get_controll = DB::table('permission')
                ->join('role_per', 'permission.role_id', '=', 'role_per.role_id')
                ->join('per_function', 'role_per.function_id', '=', 'per_function.id')
                ->join('per_controller', 'per_function.parent_id', '=', 'per_controller.id')
                ->select('per_function.id as per_id')
                ->where('permission.emp_id', $emp_id)
                ->where('per_function.parent_id', $parent_id)
                ->count();


            if (!$get_controll) {
                return redirect()->route('show_not_allow')->send();

            } else {
                return $next($request);

            }

        }


        /* if(session('user_id')=='413346578' ||session('user_id')=='800097818' ||session('user_id')=='800629123' ||session('user_id')=='802725697'  ){



           return $next($request);
            }else{

                        return redirect()->route('show_404');

           }*/


    }

}
