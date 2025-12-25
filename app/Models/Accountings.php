<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class Accountings extends Model
{
    use HasFactory;

    //  protected $fillabel = ['worker_name','ssn','work_place_id','note','doe','created_at','create_user','updated_at','updated_user'];
    // protected $guarded = ['worker_id'];
    // protected $primaryKey = 'worker_id';
    // public $incrementing = false;
//protected $dateFormat = 'U';


    public function scopeserachspendcountdesc($query, $payments_month_m, $payments_month_y, $worker_id)
    {
        $payments_month_m = TRIM($payments_month_m);
        $payments_month_y = TRIM($payments_month_y);
        $rs_stmt1 = " SELECT payments_id FROM   payments where  1=1 and is_deleted=0   ";

        if ($payments_month_y != "") {
            $rs_stmt1 = $rs_stmt1 . " and  payments_month_y = '$payments_month_y' ";
        }

        if ($payments_month_m != "") {
            $rs_stmt1 = $rs_stmt1 . " and  payments_month_m = '$payments_month_m' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  worker_id = '$worker_id' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachspenddatadesc($query, $payments_month_m, $payments_month_y, $worker_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $payments_month_m = TRIM($payments_month_m);
        $payments_month_y = TRIM($payments_month_y);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY payments_month_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT p.*,w.worker_name,u.name  FROM   payments p

         join  workers w on p.worker_id=w.worker_id
         join  users u on p.create_user=u.id

         where  1=1 and is_deleted=0  ";

        if ($payments_month_y != "") {
            $rs_stmt1 = $rs_stmt1 . " and  payments_month_y = '$payments_month_y ' ";
        }

        if ($payments_month_m != "") {
            $rs_stmt1 = $rs_stmt1 . " and  payments_month_m = '$payments_month_m ' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  worker_id = '$worker_id' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);

        return $results;
    }

    public function scopeserachspendcount($query, $payments_month_m, $payments_month_y)
    {
        $payments_month_m = TRIM($payments_month_m);
        $payments_month_y = TRIM($payments_month_y);
        $rs_stmt1 = " SELECT payments_month_id FROM   payments_month where  1=1  ";
        if ($payments_month_y != "") {
            $rs_stmt1 = $rs_stmt1 . " and  payments_month_y = '$payments_month_y ' ";
        }

        if ($payments_month_m != "") {
            $rs_stmt1 = $rs_stmt1 . " and  payments_month_m = '$payments_month_m ' ";
        }
        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachspenddata($query, $payments_month_m, $payments_month_y)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $payments_month_m = TRIM($payments_month_m);
        $payments_month_y = TRIM($payments_month_y);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY payments_month_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT * FROM   payments_month where  1=1  ";

        if ($payments_month_y != "") {
            $rs_stmt1 = $rs_stmt1 . " and  payments_month_y = '$payments_month_y ' ";
        }

        if ($payments_month_m != "") {
            $rs_stmt1 = $rs_stmt1 . " and  payments_month_m = '$payments_month_m ' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);

        return $results;
    }


}



