<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Perm;
class Calculate extends Model
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


    public function scopesumspendcounthome($query, $calculate_month_m, $calculate_month_y)
    {
        $rs_stmt1 = " SELECT
 p.calculate_month_y,p.calculate_month_m,
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


        if ($calculate_month_y != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_y = '$calculate_month_y' ";
        }

        if ($calculate_month_m != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_m = '$calculate_month_m' ";
        }

if($calculate_month_m!=''){
    $rs_stmt1 = $rs_stmt1 . "    group by p.calculate_month_y,p.calculate_month_m,p.calculate_id ";
}
else{
    $rs_stmt1 = $rs_stmt1 . "    group by p.calculate_month_y ,p.calculate_id";
}


        $results = DB::select($rs_stmt1);

        return $results;
    }












    public function scopesumspendcountdesc($query, $calculate_month_m, $calculate_month_y, $shop_id,$manager_id)
    {
        $rs_stmt1 = " SELECT

    cd.calculate_month_val as c1,m.manager_name,

   COALESCE(count(cd.calculate_detail_id), 0) as count_statement,
   COALESCE(sum(cd.calculate_month_pay), 0) as sum_det_calculate_month_pay,

    (cd.calculate_month_val - COALESCE(sum(cd.calculate_month_pay), 0)) as xx

        FROM   calculate p
        left join  calculate_detail cd on p.calculate_id=cd.calculate_id
        left join  shop s on p.shop_id=s.shop_id
left join  manager m on s.manager_id=m.manager_id


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
if ($calculate_month_y != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_y = '$calculate_month_y ' ";
}

if ($calculate_month_m != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_m = '$calculate_month_m ' ";
}
if ($shop_id != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id' ";
}
if ($manager_id != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.manager_id = '$manager_id' ";
}

        $rs_stmt1 = $rs_stmt1 . "    group by p.calculate_id ";

        $results = DB::select($rs_stmt1);

        return $results;
    }
    public function scopeserachspendcountdesc($query, $calculate_month_m, $calculate_month_y, $shop_id,$manager_id)
    {
        $calculate_month_m = TRIM($calculate_month_m);
        $calculate_month_y = TRIM($calculate_month_y);
        $rs_stmt1 = " SELECT p.calculate_id FROM   calculate p
         left join  shop s on p.shop_id=s.shop_id
left join  manager m on s.manager_id=m.manager_id


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
if ($calculate_month_y != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_y = '$calculate_month_y ' ";
}

if ($calculate_month_m != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_m = '$calculate_month_m ' ";
}
if ($shop_id != "") {
    $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id' ";
}
if ($manager_id != "") {
    $rs_stmt1 = $rs_stmt1 . " and  s.manager_id = '$manager_id' ";
}


        $results = count(DB::select($rs_stmt1));
        return $results;
    }

    public function scopeserachspenddatarep($query,$calculate_id, $calculate_month_m, $calculate_month_y, $shop_id,$manager_id)
    {
        $calculate_month_m = TRIM($calculate_month_m);
        $calculate_month_y = TRIM($calculate_month_y);


        $rs_stmt1 = " SELECT p.*,sh.shop_name,u.name,m.manager_name,
        cd.calculate_detail_id as calculate_detail_id,
        cd.calculate_month_pay as det_calculate_month_pay,
        cd.calculate_month_remain as det_calculate_month_remain,
        cd.note as det_note,
        cd.create_user as det_create_user,
        cd.created_at as det_created_at,
        cd.updated_user as det_updated_user,
        cd.updated_at as det_updated_at,
COALESCE(count(cd.calculate_detail_id), 0) as count_statement,
COALESCE(sum(cd.calculate_month_pay), 0) as sum_det_calculate_month_pay
         FROM   calculate p
         left join  calculate_detail cd on p.calculate_id=cd.calculate_id
         join  shop sh on p.shop_id=sh.shop_id
         join  users u on p.create_user=u.id
         left join  shop s on p.shop_id=s.shop_id
left join  manager m on s.manager_id=m.manager_id

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
            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_id = '$calculate_id ' ";
        }

        if ($calculate_month_y != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_y = '$calculate_month_y ' ";
        }

        if ($calculate_month_m != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_m = '$calculate_month_m ' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  s.manager_id = '$manager_id' ";
        }

        $rs_stmt1 = $rs_stmt1 . "    group by p.calculate_id ";



        $results = DB::select($rs_stmt1);

        return $results;
    }

    public function scopeserachspenddatadesc($query, $calculate_month_m, $calculate_month_y, $shop_id,$manager_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $calculate_month_m = TRIM($calculate_month_m);
        $calculate_month_y = TRIM($calculate_month_y);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY p.calculate_month_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT p.*,sh.shop_name,u.name,m.manager_name,
        cd.calculate_detail_id as calculate_detail_id,
        cd.calculate_month_pay as det_calculate_month_pay,
        cd.calculate_month_remain as det_calculate_month_remain,
        cd.note as det_note,
        cd.create_user as det_create_user,
        cd.created_at as det_created_at,
        cd.updated_user as det_updated_user,
        cd.updated_at as det_updated_at,
COALESCE(count(cd.calculate_detail_id), 0) as count_statement,
COALESCE(sum(cd.calculate_month_pay), 0) as sum_det_calculate_month_pay
         FROM   calculate p
         left join  calculate_detail cd on p.calculate_id=cd.calculate_id
         join  shop sh on p.shop_id=sh.shop_id
         join  users u on p.create_user=u.id
         left join  shop s on p.shop_id=s.shop_id
left join  manager m on s.manager_id=m.manager_id

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
        if ($calculate_month_y != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_y = '$calculate_month_y ' ";
        }

        if ($calculate_month_m != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_m = '$calculate_month_m ' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  s.manager_id = '$manager_id' ";
        }

        $rs_stmt1 = $rs_stmt1 . "    group by p.calculate_id ";

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



