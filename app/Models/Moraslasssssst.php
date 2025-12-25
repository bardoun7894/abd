<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
//use App\Http\Traits\ApimtitTrait;
use Perm;

class Moraslat extends Model

{
    use HasFactory;

    protected $fillabel = ['moraslat_name', 'ssn', 'work_place_id', 'note', 'doe', 'created_at', 'create_user', 'updated_at', 'updated_user'];
    protected $guarded = ['moraslat_id'];
    protected $primaryKey = 'moraslat_id';
    // public $incrementing = false;
//protected $dateFormat = 'U';

public function __construct(array $attributes = [])
{
    parent::__construct($attributes);
    $this->emp_job = Auth()->user()->emp_job;
    $this->user_id=Auth::user()->id;
}


    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }


    public function scopesel_moraslat_list($query, $string, $page)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT moraslat_name as name, moraslat_id as id_no,moraslat_id as id,moraslat_respon
        from  moraslat where  1=1  ";
        if ($string != "") {
            $sql = $sql . " and ( moraslat_name LIKE '%$string%' or ssn LIKE '$string%')    ";
        }
        $sql = $sql . " order by moraslat_id  desc LIMIT {$end}, {$start} ";
        $results = DB::select($sql);
        $count_rs_chk = count(DB::select($sql));
        $users = DB::select($sql);
        $users = json_decode(json_encode($users), true);
        $data = array();
        foreach ($users as $user) {
            $data[] = array(
                "id" => $user['id'],
                "ItemName" => $user['name']
            , "item_code" => ' المسؤول ' . ' ' . $user['moraslat_respon']
            , "total_count" => $count_rs_chk);
        }
        return $data;
    }


    public function scopeserachspendcount($query, $moraslat_id, $moraslat_type_id, $moraslat_categoty_id, $moraslat_dt_from, $moraslat_dt_to, $user_id, $worker_id, $shop_id,$moraslat_status_id)
    {
        $moraslat_type_id = TRIM($moraslat_type_id);
        $moraslat_categoty_id = TRIM($moraslat_categoty_id);
        $moraslat_dt_from = TRIM($moraslat_dt_from);
        $moraslat_dt_to = TRIM($moraslat_dt_to);
        $user_id = TRIM($user_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);
        $moraslat_id = TRIM($moraslat_id);
        $moraslat_status_id = TRIM($moraslat_status_id);

        $rs_stmt1 = " SELECT moraslat_id FROM  moraslat where  1=1  ";
        if(  $this->emp_job!=1){
            $rs_stmt1 = $rs_stmt1 . " and  (user_id = $this->user_id || create_user = $this->user_id ) ";

            }
            if ($moraslat_status_id != "") {
                $rs_stmt1 = $rs_stmt1 . " and  moraslat_type_id = '$moraslat_status_id' ";
            }
            if ($moraslat_status_id == "") {
                $rs_stmt1 = $rs_stmt1 . " and ( moraslat_status_id in (2,3) || moraslat_status_id  is null) ";

            }
        if ($moraslat_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  moraslat_id = '$moraslat_id' ";
        }
        if ($moraslat_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  moraslat_type_id = '$moraslat_type_id' ";
        }
        if ($moraslat_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  moraslat_categoty_id = '$moraslat_categoty_id' ";
        }
        if ($user_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  user_id = '$user_id' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  worker_id = '$worker_id' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  shop_id = '$shop_id' ";
        }
        if ($moraslat_dt_from != "" and $moraslat_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  created_at between '$moraslat_dt_from' and '$moraslat_dt_to'  ";
        }

        if ($moraslat_dt_from != "" and $moraslat_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  created_at >= '$moraslat_dt_from'  ";
        }

        if ($moraslat_dt_from == "" and $moraslat_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  created_at <= '$moraslat_dt_to'  ";
        }
        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachspenddata($query, $moraslat_id, $moraslat_type_id, $moraslat_categoty_id, $moraslat_dt_from, $moraslat_dt_to, $user_id, $worker_id, $shop_id,$moraslat_status_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $moraslat_type_id = TRIM($moraslat_type_id);
        $moraslat_categoty_id = TRIM($moraslat_categoty_id);
        $moraslat_dt_from = TRIM($moraslat_dt_from);
        $moraslat_dt_to = TRIM($moraslat_dt_to);
        $user_id = TRIM($user_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);
        $moraslat_id = TRIM($moraslat_id);
        $moraslat_status_id = TRIM($moraslat_status_id);

        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY m.moraslat_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT m.*,e.name as emp_name,u.name,et.moraslat_type_name,ec.moraslat_categoty_name,s.shop_name,w.worker_name,ms.moraslat_status_name FROM  moraslat m
            left join  users e on m.user_id=e.id
            left join  users u on m.create_user=u.id
            left join   moraslat_type et on m.moraslat_type_id =et.moraslat_type_id
            left join   moraslat_categoty ec on m.moraslat_categoty_id=ec.moraslat_categoty_id
            left join   shop s on m.shop_id=s.shop_id
            left join   workers w on m.worker_id=w.worker_id
            left join   moraslat_status ms on m.moraslat_status_id=ms.moraslat_status_id

            where  1=1 ";

if(  $this->emp_job!=1){
    $rs_stmt1 = $rs_stmt1 . " and  (m.user_id = $this->user_id || m.create_user = $this->user_id ) ";

    }
    if ($moraslat_status_id != "") {
        $rs_stmt1 = $rs_stmt1 . " and  m.moraslat_status_id = '$moraslat_status_id' ";
    }
    if ($moraslat_status_id == "") {
        $rs_stmt1 = $rs_stmt1 . " and ( m.moraslat_status_id in (2,3) || m.moraslat_status_id is null) ";

    }

        if ($moraslat_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.moraslat_type_id = '$moraslat_type_id' ";
        }

        if ($moraslat_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.moraslat_id = '$moraslat_id' ";
        }
        if ($moraslat_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.moraslat_categoty_id = '$moraslat_categoty_id' ";
        }
        if ($user_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.user_id = '$user_id' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.worker_id = '$worker_id' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.shop_id = '$shop_id' ";
        }
        if ($moraslat_dt_from != "" and $moraslat_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.created_at between '$moraslat_dt_from' and '$moraslat_dt_to'  ";
        }

        if ($moraslat_dt_from != "" and $moraslat_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.created_at >= '$moraslat_dt_from'  ";
        }

        if ($moraslat_dt_from == "" and $moraslat_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.created_at <= '$moraslat_dt_to'  ";
        }
        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }















    public function scopeserachspendhome($query)
    {

        $rs_stmt1 = " SELECT m.*,e.name as emp_name,u.name,et.moraslat_type_name,ec.moraslat_categoty_name,s.shop_name,w.worker_name,ms.moraslat_status_name FROM  moraslat m
            left join  users e on m.user_id=e.id
            left join  users u on m.create_user=u.id
            left join   moraslat_type et on m.moraslat_type_id =et.moraslat_type_id
            left join   moraslat_categoty ec on m.moraslat_categoty_id=ec.moraslat_categoty_id
            left join   shop s on m.shop_id=s.shop_id
            left join   workers w on m.worker_id=w.worker_id
            left join   moraslat_status ms on m.moraslat_status_id=ms.moraslat_status_id

            where   (is_read=0  or m.moraslat_status_id !=1)  and 1=1";
   $rs_stmt1 = $rs_stmt1 . " and  (m.user_id = $this->user_id || (m.create_user = $this->user_id and  m.moraslat_status_id =3 )) ";


// if(  $this->emp_job!=1){
//     $rs_stmt1 = $rs_stmt1 . " and  (m.user_id = $this->user_id || m.create_user = $this->user_id ) ";

//     }

        $results = DB::select($rs_stmt1);
        return $results;
    }



    public function scopeserachspendhomecount($query)
    {

        $rs_stmt1 = " SELECT moraslat_id FROM  moraslat where   is_read=0  and  moraslat_status_id !=1  and 1=1 ";
   $rs_stmt1 = $rs_stmt1 . " and  (user_id = $this->user_id || (create_user = $this->user_id and  moraslat_status_id =3 )) ";

//         $rs_stmt1 = " SELECT moraslat_id FROM  moraslat where   (is_read=0  or moraslat_status_id !=1)  and 1=1 ";
//    $rs_stmt1 = $rs_stmt1 . " and  (user_id = $this->user_id || (create_user = $this->user_id and  moraslat_status_id =3 )) ";

       // $rs_stmt1 = $rs_stmt1 . " and user_id = $this->user_id ";
        // if(  $this->emp_job!=1){
        //     $rs_stmt1 = $rs_stmt1 . " and  (user_id = $this->user_id || create_user = $this->user_id ) ";

        //     }
        $results = count(DB::select($rs_stmt1));
        return $results;
    }

    public function scopeserachremarkcount($query, $moraslat_id)
    {
        $moraslat_id = TRIM($moraslat_id);
        $rs_stmt1 = " SELECT moraslat_note_id FROM  moraslat_note where is_deleted=0 and   1=1  ";
        if ($moraslat_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  moraslat_id = '$moraslat_id' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachremarkdata($query, $moraslat_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $moraslat_id = TRIM($moraslat_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY moraslat_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT sn.*,u.name,n.note_type_name FROM   moraslat_note sn
                            left join  users u on sn.create_note_user=u.id
                            left join  note_type n on sn.note_type_id=n.note_type_id

                            where sn.is_deleted=0 and   1=1 ";
        if ($moraslat_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.moraslat_id = '$moraslat_id' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }


}



