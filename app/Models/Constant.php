<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class Constant extends Model

{
    use HasFactory;

    protected $fillabel = ['constant_name', 'ssn', 'work_place_id', 'note', 'doe', 'created_at', 'create_user', 'updated_at', 'updated_user'];
    protected $guarded = ['constant_id'];
    protected $primaryKey = 'constant_id';
    // public $incrementing = false;
//protected $dateFormat = 'U';


    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }


    public function scopesel_constant_list($query, $string, $page)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT constant_name as name, constant_id as id_no,constant_id as id,constant_respon
        from  constant where  1=1  ";
        if ($string != "") {
            $sql = $sql . " and ( constant_name LIKE '%$string%' or ssn LIKE '$string%')    ";
        }
        $sql = $sql . " order by constant_id  desc LIMIT {$end}, {$start} ";
        $results = DB::select($sql);
        $count_rs_chk = count(DB::select($sql));
        $users = DB::select($sql);
        $users = json_decode(json_encode($users), true);
        $data = array();
        foreach ($users as $user) {
            $data[] = array(
                "id" => $user['id'],
                "ItemName" => $user['name']
            , "item_code" => ' المسؤول ' . ' ' . $user['constant_respon']
            , "total_count" => $count_rs_chk);
        }
        return $data;
    }


    public function scopeserachspendcount($query, $constant_type_id, $constant_categoty_id, $constant_dt_from, $constant_dt_to, $manager_id, $worker_id, $shop_id)
    {
        $constant_type_id = TRIM($constant_type_id);
        $constant_categoty_id = TRIM($constant_categoty_id);
        $constant_dt_from = TRIM($constant_dt_from);
        $constant_dt_to = TRIM($constant_dt_to);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);

        $rs_stmt1 = " SELECT constant_id FROM  constant where  1=1  ";
        if ($constant_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_type_id = '$exconstant_type_idpense_no ' ";
        }
        if ($constant_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_categoty_id = '$constant_categoty_id ' ";
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
        if ($constant_dt_from != "" and $constant_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_dt between '$constant_dt_from' and '$constant_dt_to'  ";
        }

        if ($constant_dt_from != "" and $constant_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_dt >= '$constant_dt_from'  ";
        }

        if ($constant_dt_from == "" and $constant_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_dt <= '$constant_dt_to'  ";
        }
        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachspenddata($query, $constant_type_id, $constant_categoty_id, $constant_dt_from, $constant_dt_to, $manager_id, $worker_id, $shop_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $constant_type_id = TRIM($constant_type_id);
        $constant_categoty_id = TRIM($constant_categoty_id);
        $constant_dt_from = TRIM($constant_dt_from);
        $constant_dt_to = TRIM($constant_dt_to);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY ex.constant_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT ex.*,m.manager_name,u.name,et.constant_type_name,ec.constant_categoty_name,s.shop_name,w.worker_name FROM  constant ex
            left join  manager m on ex.manager_id=m.manager_id
            left join  users u on ex.create_user=u.id
            left join   constant_type et on ex.constant_type_id =et.constant_type_id
            left join   constant_categoty ec on ex.constant_categoty_id=ec.constant_categoty_id
            left join   shop s on ex.shop_id=s.shop_id
            left join   workers w on ex.worker_id=w.worker_id

            where  1=1 ";
        if ($constant_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_type_id = '$exconstant_type_idpense_no ' ";
        }
        if ($constant_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_categoty_id = '$constant_categoty_id ' ";
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
        if ($constant_dt_from != "" and $constant_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_dt between '$constant_dt_from' and '$constant_dt_to'  ";
        }

        if ($constant_dt_from != "" and $constant_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_dt >= '$constant_dt_from'  ";
        }

        if ($constant_dt_from == "" and $constant_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_dt <= '$constant_dt_to'  ";
        }
        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }


    public function scopeserachremarkcount($query, $constant_id)
    {
        $constant_id = TRIM($constant_id);
        $rs_stmt1 = " SELECT constant_note_id FROM  constant_note where is_deleted=0 and   1=1  ";
        if ($constant_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  constant_id = '$constant_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachremarkdata($query, $constant_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $constant_id = TRIM($constant_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY constant_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT sn.*,u.name,n.note_type_name FROM   constant_note sn
                            left join  users u on sn.create_note_user=u.id
                            left join  note_type n on sn.note_type_id=n.note_type_id

                            where sn.is_deleted=0 and   1=1 ";
        if ($constant_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.constant_id = '$constant_id ' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }


}



