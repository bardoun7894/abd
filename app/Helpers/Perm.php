<?php

namespace App\Helpers;
use Illuminate\Support\Facades\DB;
use App\Models\Workers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Str;

class Perm {

    public static function helperfunction1(){
        return "helper function 1 response";
    }

    public static function get_controll_access($parent_id=0){









      //  $record = Workers::find($id);
        $emp_id= Auth::id();
        $emp_job=   Auth()->user()->emp_job ;
        $emp_name= Auth()->user()->emp_name;
        if($emp_job==1){
            return true;
        }
        else{
        /*    $users = permission::select('IFNULL(count(per_function.id), 0) as per_id')
                ->join('role_per', 'permission.role_id', '=', 'role_per.role_id')
                ->join('per_function', 'role_per.function_id=per_function.id')
                ->join('per_controller', 'per_function.parent_id=per_controller.id')
                ->where('permission.emp_id', $emp_id)
                ->where('per_function.parent_id', $parent_id)
                ->count();*/

         /*   $users = permission::select('IFNULL(count(per_function.id), 0) as per_id')
                ->join('role_per', 'permission.role_id', '=', 'role_per.role_id')
                ->join('per_function', 'role_per.function_id=per_function.id')
                ->join('per_controller', 'per_function.parent_id=per_controller.id')
                ->where('permission.emp_id', $emp_id)
                ->where('per_function.parent_id', $parent_id)
                ->get();




                $moraslat = DB::table('permission')
                ->join('users', 'moraslat.user_id', '=', 'users.id')
                ->join('moraslat_type', 'moraslat.moraslat_type_id', '=', 'moraslat_type.moraslat_type_id')
                ->join('moraslat_categoty', 'moraslat.moraslat_categoty_id', '=', 'moraslat_categoty.moraslat_categoty_id')
                ->join('shop', 'moraslat.shop_id', '=', 'shop.shop_id')
                ->join('workers', 'moraslat.worker_id', '=', 'workers.worker_id')
                ->select('moraslat.*', 'users.name as emp_name','moraslat_type.moraslat_type_name','moraslat_categoty.moraslat_categoty_name','shop.shop_name','workers.worker_name')
                ->where('moraslat.moraslat_id',$id) ->first();*/


                $get_controll = DB::table('permission')
                ->join('role_per', 'permission.role_id', '=', 'role_per.role_id')
                ->join('per_function', 'role_per.function_id', '=', 'per_function.id')
                ->join('per_controller', 'per_function.parent_id', '=', 'per_controller.id')
                ->select('per_function.id as per_id')


                ->where('permission.emp_id', $emp_id)
                ->where('per_function.parent_id', $parent_id)
                ->count();



                if(!$get_controll){
                 //   return redirect()->route('show_not_allow')->send();
                 return 0;

                }
                else{
                    return 1;

                }
            //    return $users;
       //     return redirect()->route('show_not_allow');
   //    return to_route('show_not_allow');
  // return redirect()->route('show_not_allow')->send();
  // return redirect()->route('https://www.google.com')->send();

  // return to_route('show_not_allow');
   //    $string = "Welcome to Online Web Tutor";
//echo Str::snake($string); // welcome_to_online_web_tutor

              //  return response()->view('order.job.not_nominated');



          /*  foreach ($users as $row) {
                if ($row->per_id > 0) {
                    return true;
                } else {

                    return false;
                }
            }*/


        }

       // return $emp_id;
    }

















    public static function get_function_access($function_id) {
        $emp_id= Auth::id();
        $emp_job=   Auth()->user()->emp_job ;
        $emp_name= Auth()->user()->emp_name;
        if($emp_job==1){
            return true;
        }
    else{
        $get_function = DB::table('permission')
        ->select('permission.id')
        ->where('permission.emp_id', $emp_id)
        ->where('permission.function_id', $function_id)
        ->count();
        if(!$get_function){
            return 0;
        }
        else{
            return 1;

        }
    }
    }





    public static function get_function_access________($function_id,$desc) {
        $emp_id= Auth::id();
        $emp_job=   Auth()->user()->emp_job ;
        $emp_name= Auth()->user()->emp_name;
        if($emp_job==1){
            return true;
        }
    else{
        $get_function = DB::table('permission')
        ->select('permission.id')
        ->where('permission.emp_id', $emp_id)
        ->where('permission.function_id', $function_id)
        ->count();
        if(!$get_function){
           // return redirect()->route('show_not_allow')->send();
           if($desc==1){
            return redirect()->route('show_not_allow')->send();
           }
           if($desc==2){
            return 0;

           }
        }
        else{
            return 1;

        }
    }
    }


    public static function getWorkers($id=0){
        $record = Workers::find($id);
      // $record = Workers::find('122');

//dd($record);
        return $record->phone;
    }


  /*  function get_controll_access($parent_id) {

        $CI = & get_instance();

        $emp_id = $CI->session->userdata('user_id');
        $emp_job = $CI->session->userdata('emp_job');
        if($emp_job==1){
            return true;
        }
        else{



            $CI->db->select('IFNULL(count(per_function.id), 0) as per_id');
            $CI->db->join('role_per', 'permission.role_id  =role_per.role_id');
            $CI->db->join('per_function', 'role_per.function_id=per_function.id');
            $CI->db->join('per_controller', 'per_function.parent_id=per_controller.id');
            $CI->db->where('permission.emp_id', $emp_id);
            $CI->db->where('per_function.parent_id', $parent_id);
            $query = $CI->db->get('permission');
            $row = $query->row();
             if ($row->per_id > 0) {
                return true;
            } else {

                return false;
            }
        }
    }



    function get_function_access($function_id) {
        $CI = & get_instance();
        $emp_id = $CI->session->userdata('user_id');
        $emp_job = $CI->session->userdata('emp_job');
        if($emp_job==1){
            return true;
        }
        else{
            $CI->db->select('permission.id');
            $CI->db->join('role_per', 'role_per.role_id=permission.role_id  ');
            $CI->db->where('role_per.function_id', $function_id);
            $CI->db->where('permission.emp_id', $emp_id);
            $query = $CI->db->get('permission');
           if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }*/

}
