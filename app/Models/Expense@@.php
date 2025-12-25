<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class Expense extends Model

{
    use HasFactory;

    protected $fillabel = ['expense_name', 'ssn', 'work_place_id', 'note', 'doe', 'created_at', 'create_user', 'updated_at', 'updated_user'];
    protected $guarded = ['expense_id'];
    protected $primaryKey = 'expense_id';
    // public $incrementing = false;
//protected $dateFormat = 'U';


    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }


    public function scopesel_expense_list($query, $string, $page)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT expense_name as name, expense_id as id_no,expense_id as id,expense_respon
        from  expense where  1=1  ";
        if ($string != "") {
            $sql = $sql . " and ( expense_name LIKE '%$string%' or ssn LIKE '$string%')    ";
        }
        $sql = $sql . " order by expense_id  desc LIMIT {$end}, {$start} ";
        $results = DB::select($sql);
        $count_rs_chk = count(DB::select($sql));
        $users = DB::select($sql);
        $users = json_decode(json_encode($users), true);
        $data = array();
        foreach ($users as $user) {
            $data[] = array(
                "id" => $user['id'],
                "ItemName" => $user['name']
            , "item_code" => ' المسؤول ' . ' ' . $user['expense_respon']
            , "total_count" => $count_rs_chk);
        }
        return $data;
    }


    public function scopeserachspendcount($query, $expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id)
    {
        $expense_type_id = TRIM($expense_type_id);
        $expense_categoty_id = TRIM($expense_categoty_id);
        $expense_dt_from = TRIM($expense_dt_from);
        $expense_dt_to = TRIM($expense_dt_to);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);

        $rs_stmt1 = " SELECT expense_id FROM  expense where  1=1  ";
        if ($expense_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_type_id = '$expense_type_id ' ";
        }
        if ($expense_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_categoty_id = '$expense_categoty_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  manager_id = '$manager_id ' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  worker_id = '$worker_id ' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  shop_id = '$shop_id ' ";
        }
        if ($expense_dt_from != "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_dt between '$expense_dt_from' and '$expense_dt_to'  ";
        }

        if ($expense_dt_from != "" and $expense_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_dt >= '$expense_dt_from'  ";
        }

        if ($expense_dt_from == "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_dt <= '$expense_dt_to'  ";
        }
        $results = count(DB::select($rs_stmt1));
        return $results;
    }






    public function scopeserachspenddatarep($query, $expense_id,$expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id)
    {
        $expense_id = TRIM($expense_id);
        $expense_type_id = TRIM($expense_type_id);
        $expense_categoty_id = TRIM($expense_categoty_id);
        $expense_dt_from = TRIM($expense_dt_from);
        $expense_dt_to = TRIM($expense_dt_to);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);

        $rs_stmt1 = " SELECT ex.*,m.manager_name,u.name,et.expense_type_name,ec.expense_categoty_name,s.shop_name,w.worker_name FROM  expense ex
            left join  manager m on ex.manager_id=m.manager_id
            left join  users u on ex.create_user=u.id
            left join   expense_type et on ex.expense_type_id =et.expense_type_id
            left join   expense_categoty ec on ex.expense_categoty_id=ec.expense_categoty_id
            left join   shop s on ex.shop_id=s.shop_id
            left join   workers w on ex.worker_id=w.worker_id

            where  1=1 ";
                    if ($expense_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  ex.expense_id = '$expense_id ' ";
                    }

        if ($expense_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_type_id = '$expense_type_id ' ";
        }
        if ($expense_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_categoty_id = '$expense_categoty_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.manager_id = '$manager_id ' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.worker_id = '$worker_id ' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.shop_id = '$shop_id ' ";
        }
        if ($expense_dt_from != "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt between '$expense_dt_from' and '$expense_dt_to'  ";
        }

        if ($expense_dt_from != "" and $expense_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt >= '$expense_dt_from'  ";
        }

        if ($expense_dt_from == "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt <= '$expense_dt_to'  ";
        }
        $results = DB::select($rs_stmt1);
        return $results;
    }










    public function scopeserachspenddata($query, $expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $expense_type_id = TRIM($expense_type_id);
        $expense_categoty_id = TRIM($expense_categoty_id);
        $expense_dt_from = TRIM($expense_dt_from);
        $expense_dt_to = TRIM($expense_dt_to);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY ex.expense_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT ex.*,m.manager_name,u.name,et.expense_type_name,ec.expense_categoty_name,s.shop_name,w.worker_name,w.ssn,   sm.municip_no,sm.municip_sdt,sm.municip_edt
         FROM  expense ex
            left join  manager m on ex.manager_id=m.manager_id
            left join  users u on ex.create_user=u.id
            left join   expense_type et on ex.expense_type_id =et.expense_type_id
            left join   expense_categoty ec on ex.expense_categoty_id=ec.expense_categoty_id
            left join   shop s on ex.shop_id=s.shop_id
            left join   workers w on ex.worker_id=w.worker_id
            left join  shop_municip sm on ex.shop_id=sm.shop_id

            where  1=1 ";
        if ($expense_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_type_id = '$expense_type_id ' ";
        }
        if ($expense_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_categoty_id = '$expense_categoty_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.manager_id = '$manager_id ' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.worker_id = '$worker_id ' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.shop_id = '$shop_id ' ";
        }
        if ($expense_dt_from != "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt between '$expense_dt_from' and '$expense_dt_to'  ";
        }

        if ($expense_dt_from != "" and $expense_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt >= '$expense_dt_from'  ";
        }

        if ($expense_dt_from == "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt <= '$expense_dt_to'  ";
        }
        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }


    public function scopeserachremarkcount($query, $expense_id)
    {
        $expense_id = TRIM($expense_id);
        $rs_stmt1 = " SELECT expense_note_id FROM  expense_note where is_deleted=0 and   1=1  ";
        if ($expense_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_id = '$expense_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachremarkdata($query, $expense_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $expense_id = TRIM($expense_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY expense_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT sn.*,u.name,n.note_type_name FROM   expense_note sn
                            left join  users u on sn.create_note_user=u.id
                            left join  note_type n on sn.note_type_id=n.note_type_id

                            where sn.is_deleted=0 and   1=1 ";
        if ($expense_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.expense_id = '$expense_id ' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }


}



