<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Perm;
class Violation extends Model
{
    use HasFactory;

    //  protected $fillabel = ['shop_name','ssn','work_place_id','note','doe','created_at','create_user','updated_at','updated_user'];
    // protected $guarded = ['shop_id'];
    // protected $primaryKey = 'shop_id';
    // public $incrementing = false;
//protected $dateFormat = 'U';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->emp_job = Auth()->user()->emp_job;
        $this->user_id=Auth::user()->id;
    }


    public function scopeserachhistorycount($query, $violation_id)
    {
        $violation_id = TRIM($violation_id);
        $rs_stmt1 = " SELECT vh.violation_history_id   FROM  violation_history vh


         where   1=1  ";
        if ($violation_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  vh.violation_id = '$violation_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachhistorydata($query, $violation_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $violation_id = TRIM($violation_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY violation_history_id   ASC  ";
            }

        } else {
            $ord = "  ORDER BY violation_history_id   desc  ";
        }

        $rs_stmt1 = " SELECT vh.*,u.name FROM violation_history vh left join  users u on vh.change_user =u.id

                    where    1=1 ";
        if ($violation_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  vh.violation_id = '$violation_id ' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }

    public function scopesumspendcounthome($query, $violation_month_m, $violation_month_y)
    {
        $rs_stmt1 = " SELECT
 p.violation_month_y,p.violation_month_m,
 cd.calculate_month_val as c1,
   COALESCE(count(cd.calculate_detail_id), 0) as count_statement,
   COALESCE(sum(cd.calculate_month_pay), 0) as sum_det_calculate_month_pay,
    ( cd.calculate_month_val - COALESCE(sum(cd.calculate_month_pay), 0)) as xx
        FROM   calculate p
        left join  calculate_detail cd on p.calculate_id=cd.calculate_id
        left join  shop s on p.shop_id=s.shop_id


       ";
        if(  $this->emp_job!=1){
            $rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on s.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 and p.is_deleted=0 ";

        if(  $this->emp_job!=1){
            if (Perm::get_function_access(75)) {
                $rs_stmt1 = $rs_stmt1 . " and  p.create_user = $this->user_id ";
            }
        }


        if ($violation_month_y != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.violation_month_y = '$violation_month_y' ";
        }

        if ($violation_month_m != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.violation_month_m = '$violation_month_m' ";
        }

if($violation_month_m!=''){
    $rs_stmt1 = $rs_stmt1 . "    group by p.violation_month_y,p.violation_month_m,p.calculate_id ";
}
else{
    $rs_stmt1 = $rs_stmt1 . "    group by p.violation_month_y ,p.calculate_id";
}


        $results = DB::select($rs_stmt1);

        return $results;
    }







    public function scopesumspendcountdesc($query,$violation_id, $violation_month_m, $violation_month_y, $shop_id,$manager_id,$violation_no,$violation_ispay,
    $comme_no,$municip_no,$shop_respon)
    {
        $violation_month_m = TRIM($violation_month_m);
        $violation_month_y = TRIM($violation_month_y);
        $shop_id = TRIM($shop_id);
        $manager_id = TRIM($manager_id);
        $violation_no = TRIM($violation_no);
        $violation_ispay = TRIM($violation_ispay);
        $comme_no = TRIM($comme_no);
        $municip_no = TRIM($municip_no);
        $shop_respon = TRIM($shop_respon);

        $rs_stmt1 = " SELECT sum(p.violation_val) as violation_val_all_pay,
SUM(CASE WHEN p.violation_ispay = 1  THEN p.violation_val END) AS `violation_val_pay`,
SUM(CASE WHEN p.violation_ispay = 0  THEN p.violation_val END) AS `violation_val_not_pay`
        FROM    violation p
        left join  shop s on p.shop_id=s.shop_id
left join  manager m on s.manager_id=m.manager_id
       ";
        if(  $this->emp_job!=1){
            $rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on s.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 and p.is_deleted=0 ";

        if ($violation_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.violation_id = '$violation_id' ";
        }
if ($violation_month_y != "") {
    $rs_stmt1 = $rs_stmt1 . " and  year(p.violation_dt) = '$violation_month_y ' ";
}

if ($violation_month_m != "") {
    $rs_stmt1 = $rs_stmt1 . " and  month(p.violation_dt) = '$violation_month_m ' ";
}
if ($violation_no != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.violation_no = '$violation_no' ";
}
if ($violation_ispay != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.violation_ispay = '$violation_ispay' ";
}
if ($shop_id != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id' ";
}
if ($manager_id != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.manager_id = '$manager_id' ";
}
if ($comme_no != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.comme_no = '$comme_no' ";
}
if ($municip_no != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.municip_no = '$municip_no' ";
}
if ($shop_respon != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.shop_respon = '$shop_respon' ";
}
        $results = DB::select($rs_stmt1);

        return $results;
    }



    public function scopeserachspendcountdesc($query,$violation_month_m, $violation_month_y, $shop_id,$manager_id,$violation_no,$violation_ispay,
    $comme_no,$municip_no,$shop_respon)
    {
        $violation_month_m = TRIM($violation_month_m);
        $violation_month_y = TRIM($violation_month_y);
        $shop_id = TRIM($shop_id);
        $manager_id = TRIM($manager_id);
        $violation_no = TRIM($violation_no);
        $violation_ispay = TRIM($violation_ispay);
        $comme_no = TRIM($comme_no);
        $municip_no = TRIM($municip_no);
        $shop_respon = TRIM($shop_respon);


        $rs_stmt1 = " SELECT p.violation_id FROM   violation p left join  shop s on p.shop_id=s.shop_id
left join  manager m on s.manager_id=m.manager_id
           ";

if(  $this->emp_job!=1){
    $rs_stmt1 = $rs_stmt1 . "
join workers_manager wm on s.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

}
$rs_stmt1 = $rs_stmt1 . " where  1=1 and p.is_deleted=0 ";


if ($violation_month_y != "") {
$rs_stmt1 = $rs_stmt1 . " and  year(p.violation_dt) = '$violation_month_y ' ";
}

if ($violation_month_m != "") {
$rs_stmt1 = $rs_stmt1 . " and  month(p.violation_dt) = '$violation_month_m ' ";
}
if ($violation_no != "") {
$rs_stmt1 = $rs_stmt1 . " and  p.violation_no = '$violation_no' ";
}
if ($violation_ispay != "") {
$rs_stmt1 = $rs_stmt1 . " and  p.violation_ispay = '$violation_ispay' ";
}
if ($shop_id != "") {
$rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id' ";
}
if ($manager_id != "") {
$rs_stmt1 = $rs_stmt1 . " and  s.manager_id = '$manager_id' ";
}
if ($comme_no != "") {
$rs_stmt1 = $rs_stmt1 . " and  s.comme_no = '$comme_no' ";
}
if ($municip_no != "") {
$rs_stmt1 = $rs_stmt1 . " and  s.municip_no = '$municip_no' ";
}
if ($shop_respon != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.shop_respon like '%$shop_respon%' ";
}
$rs_stmt1 = $rs_stmt1 . "    group by p.violation_id ";

        $results = count(DB::select($rs_stmt1));
        return $results;
    }
    public function scopeserachspenddatarep($query,$violation_id,$violation_month_m, $violation_month_y, $shop_id,$manager_id,$violation_no,$violation_ispay,
    $comme_no,$municip_no,$shop_respon)
    {
        $violation_month_m = TRIM($violation_month_m);
        $violation_month_y = TRIM($violation_month_y);
        $shop_id = TRIM($shop_id);
        $manager_id = TRIM($manager_id);
        $violation_no = TRIM($violation_no);
        $violation_ispay = TRIM($violation_ispay);
        $comme_no = TRIM($comme_no);
        $municip_no = TRIM($municip_no);
        $shop_respon = TRIM($shop_respon);
        $rs_stmt1 = " SELECT p.*,s.shop_name,u.name,m.manager_name,v.violation_side_name,s.shop_mobile,sc.comme_no,sm.municip_no,s.shop_respon,s.shop_location
         FROM   violation p
         left join  shop s on p.shop_id=s.shop_id
         join  users u on p.create_user=u.id
left join  manager m on s.manager_id=m.manager_id
left join  violation_side v on p.violation_side_id=v.violation_side_id
left join  shop_municip sm on s.shop_id=sm.shop_id
left join  shop_comme sc on s.shop_id=sc.shop_id
         ";
if(  $this->emp_job!=1){
    $rs_stmt1 = $rs_stmt1 . "
join workers_manager wm on s.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

}
$rs_stmt1 = $rs_stmt1 . " where  1=1 and p.is_deleted=0 ";
if ($violation_month_y != "") {
$rs_stmt1 = $rs_stmt1 . " and  year(p.violation_dt) = '$violation_month_y ' ";
}

if ($violation_month_m != "") {
$rs_stmt1 = $rs_stmt1 . " and  month(p.violation_dt) = '$violation_month_m ' ";
}
if ($violation_no != "") {
$rs_stmt1 = $rs_stmt1 . " and  p.violation_no = '$violation_no' ";
}
if ($violation_ispay != "") {
$rs_stmt1 = $rs_stmt1 . " and  p.violation_ispay = '$violation_ispay' ";
}
if ($shop_id != "") {
$rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id' ";
}
if ($manager_id != "") {
$rs_stmt1 = $rs_stmt1 . " and  s.manager_id = '$manager_id' ";
}
if ($comme_no != "") {
$rs_stmt1 = $rs_stmt1 . " and  s.comme_no = '$comme_no' ";
}
if ($municip_no != "") {
$rs_stmt1 = $rs_stmt1 . " and  s.municip_no = '$municip_no' ";
}
if ($shop_respon != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.shop_respon like '%$shop_respon%' ";
}
$rs_stmt1 = $rs_stmt1 . "    group by p.violation_id ";



        $results = DB::select($rs_stmt1);

        return $results;
    }

    public function scopeserachspenddatadesc($query,$violation_month_m, $violation_month_y, $shop_id,$manager_id,$violation_no,$violation_ispay,
    $comme_no,$municip_no,$shop_respon)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $violation_month_m = TRIM($violation_month_m);
        $violation_month_y = TRIM($violation_month_y);
        $shop_id = TRIM($shop_id);
        $manager_id = TRIM($manager_id);
        $violation_no = TRIM($violation_no);
        $violation_ispay = TRIM($violation_ispay);
        $comme_no = TRIM($comme_no);
        $municip_no = TRIM($municip_no);
        $shop_respon = TRIM($shop_respon);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY p.violation_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT p.*,s.shop_name,u.name,m.manager_name,v.violation_side_name,s.shop_mobile,sc.comme_no,sm.municip_no,s.shop_respon,s.shop_location
        FROM   violation p
        left join  shop s on p.shop_id=s.shop_id
        join  users u on p.create_user=u.id
left join  manager m on s.manager_id=m.manager_id
left join  violation_side v on p.violation_side_id=v.violation_side_id
left join  shop_municip sm on s.shop_id=sm.shop_id
left join  shop_comme sc on s.shop_id=sc.shop_id

          ";
     if(  $this->emp_job!=1){
        $rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on s.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

    }
    $rs_stmt1 = $rs_stmt1 . " where  1=1 and p.is_deleted=0 ";


    if ($violation_month_y != "") {
    $rs_stmt1 = $rs_stmt1 . " and  year(p.violation_dt) = '$violation_month_y ' ";
    }

    if ($violation_month_m != "") {
    $rs_stmt1 = $rs_stmt1 . " and  month(p.violation_dt) = '$violation_month_m ' ";
    }
    if ($violation_no != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.violation_no = '$violation_no' ";
    }
    if ($violation_ispay != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.violation_ispay = '$violation_ispay' ";
    }
    if ($shop_id != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id' ";
    }
    if ($manager_id != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.manager_id = '$manager_id' ";
    }
    if ($comme_no != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.comme_no = '$comme_no' ";
    }
    if ($municip_no != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.municip_no = '$municip_no' ";
    }
    if ($shop_respon != "") {
        $rs_stmt1 = $rs_stmt1 . " and  s.shop_respon like '%$shop_respon%' ";
    }

        $rs_stmt1 = $rs_stmt1 . "    group by p.violation_id ";

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "    limit $b,$a ";


        $results = DB::select($rs_stmt1);

        return $results;
    }

    public function scopeserachspendcountdet($query, $calculate_id)
    {
        $calculate_id = TRIM($calculate_id);
        $rs_stmt1 = " SELECT p.calculate_id
 FROM   calculate p
          join  shop s on p.shop_id=s.shop_id
";
        if(  $this->emp_job!=1){
            $rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on s.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 and p.is_deleted=0 ";

        if(  $this->emp_job!=1){
            if (Perm::get_function_access(75)) {
                $rs_stmt1 = $rs_stmt1 . " and  p.create_user = $this->user_id ";
            }
        }
        if ($calculate_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  calculate_id = '$calculate_id' ";
        }


        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachspenddet($query, $calculate_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $calculate_id = TRIM($calculate_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY cd.calculate_detail_id desc  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT p.*,sh.shop_name,u.name,

        cd.calculate_detail_id as calculate_detail_id,
        cd.calculate_month_pay as det_calculate_month_pay,
        cd.calculate_month_remain as det_calculate_month_remain,
                cd.calculate_month_val as det_calculate_month_val,

        cd.note as det_note,
        u2.name as det_create_user_name,
        cd.create_user as det_create_user,
        cd.created_at as det_created_at,
        cd.updated_user as det_updated_user,
        cd.updated_at as det_updated_at

          FROM
          calculate p
          join  calculate_detail cd on p.calculate_id=cd.calculate_id
         join  shop sh on p.shop_id=sh.shop_id
         join  users u on p.create_user=u.id
         join  users u2 on cd.create_user=u2.id

 ";
        if(  $this->emp_job!=1){
            $rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on sh.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 and p.is_deleted=0 ";

        if(  $this->emp_job!=1){
            if (Perm::get_function_access(75)) {
                $rs_stmt1 = $rs_stmt1 . " and  p.create_user = $this->user_id ";
            }
        }
        if ($calculate_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_id = '$calculate_id' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . " ORDER BY cd.calculate_detail_id desc limit $b,$a ";
        $results = DB::select($rs_stmt1);

        return $results;
    }


}



