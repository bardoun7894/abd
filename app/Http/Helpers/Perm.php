<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Workers;

class Perm
{

    public static function helperfunction1()
    {
        return "helper function 1 response";
    }

    public static function get_controll_access($parent_id = 0)
    {
        //  $record = Workers::find($id);
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        if ($emp_job == 1) {
            return true;
        } else {
            /*    $users = permission::select('IFNULL(count(per_function.id), 0) as per_id')
                    ->join('role_per', 'permission.role_id', '=', 'role_per.role_id')
                    ->join('per_function', 'role_per.function_id=per_function.id')
                    ->join('per_controller', 'per_function.parent_id=per_controller.id')
                    ->where('permission.emp_id', $emp_id)
                    ->where('per_function.parent_id', $parent_id)
                    ->count();*/

            $users = permission::select('IFNULL(count(per_function.id), 0) as per_id')
                ->join('role_per', 'permission.role_id', '=', 'role_per.role_id')
                ->join('per_function', 'role_per.function_id=per_function.id')
                ->join('per_controller', 'per_function.parent_id=per_controller.id')
                ->where('permission.emp_id', $emp_id)
                ->where('per_function.parent_id', $parent_id)
                ->get();

            foreach ($users as $row) {
                //  echo  $row->per_id;
                if ($row->per_id > 0) {
                    return true;
                } else {

                    return false;
                }
            }


        }

        // return $emp_id;
    }

    public static function get_function_access($function_id = 0)
    {
        $emp_id = Auth::id();
        $emp_job = Auth()->user()->emp_job;
        $emp_name = Auth()->user()->emp_name;
        return $emp_id;
    }

    public static function getWorkers($id = 0)
    {
        $record = Workers::find($id);

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
