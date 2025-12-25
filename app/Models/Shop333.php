<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Perm;

class Shop333 extends Model
{
    use HasFactory;
    protected $fillabel = ['shop_name','ssn','work_place_id','note','doe','created_at','create_user','updated_at','updated_user'];
    protected $guarded = ['shop_id'];
    protected $primaryKey = 'shop_id';
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




public function scopesel_shop_list($query,$string,$page){
    $resultCount = 50;
    $end = ($page - 1) * $resultCount;
    $start = $end + $resultCount;
    $sql="SELECT shop_name as name, shop_id as id_no,shop_id as id,shop_respon
        from  shop where  1=1  ";
    if ($string != "") {
        $sql = $sql . " and ( shop_name LIKE '%$string%' or ssn LIKE '$string%')    ";
    }
    $sql = $sql . " order by shop_id  desc LIMIT {$end}, {$start} ";
    $results = DB::select($sql);
   $count_rs_chk = count(DB::select($sql));
   $users = DB::select($sql);
  $users = json_decode(json_encode($users), true);
    $data = array();
    foreach($users as $user){
        $data[] = array(
            "id"=>$user['id'],
            "ItemName"=>$user['name']
        , "item_code"=> ' المسؤول ' .' '.$user['shop_respon']
        , "total_count"=>$count_rs_chk);
    }
    return $data;
}


















public function scopeshopreport($query,$shop_id,$shop_name,$shop_mobile,$manager_id,$city_id,$comme_no,$municip_no){
$shop_name = TRIM($shop_name);
$shop_mobile = TRIM($shop_mobile);
$manager_id = TRIM($manager_id);
$city_id = TRIM($city_id);
$comme_no = TRIM($comme_no);
$municip_no = TRIM($municip_no);
$shop_id = TRIM($shop_id);

        $rs_stmt1 = " SELECT sh.*,m.manager_name,c.city_name,u.name,
        sm.municip_no,sm.municip_sdt,sm.municip_edt,
        sd.defence_no,sd.defence_sdt,sd.defence_edt,

CASE
WHEN  sm.municip_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
WHEN  sm.municip_edt  <=CURDATE() THEN '2'
WHEN  sm.municip_edt is null THEN '4'
ELSE '1'
END
as sm_desc,

CASE
WHEN  sc.comme_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
WHEN  sc.comme_edt  <=CURDATE() THEN '2'
WHEN  sc.comme_edt is null THEN '4'
ELSE '1'
END
as sc_desc,


CASE
WHEN  sr.rent_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
WHEN  sr.rent_edt  <=CURDATE() THEN '2'
WHEN  sr.rent_edt is null THEN '4'
ELSE '1'
END
as sr_desc,

CASE
WHEN  sd.defence_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
WHEN  sd.defence_edt  <=CURDATE() THEN '2'
WHEN  sd.defence_edt is null THEN '4'
ELSE '1'
END
as sd_desc,


        sc.comme_sso,sc.comme_no,sc.comme_sdt,sc.comme_edt,
        sr.rent_no,sr.rent_sdt,sr.rent_edt

         FROM  shop sh
        left join  manager m on sh.manager_id=m.manager_id
        left join  city c on sh.city_id=c.city_id
        left join  users u on sh.create_user=u.id
        left join  shop_municip sm on sh.shop_id=sm.shop_id
        left join  shop_comme sc on sh.shop_id=sc.shop_id
        left join  shop_rent sr on sr.shop_id=sr.shop_id
        left join  shop_defence  sd on sh.shop_id=sd.shop_id


        where  1=1 ";
          if ($shop_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sh.shop_id = '$shop_id ' ";
            }
                if ($shop_name  != "") {
                $rs_stmt1 = $rs_stmt1 . " and  sh.shop_name like '%$shop_name%' ";
                }
                if ($shop_mobile  != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  sh.shop_mobile like '%$shop_mobile%' ";
                }
                if ($manager_id  != "") {
                $rs_stmt1 = $rs_stmt1 . " and  sh.manager_id = '$manager_id ' ";
                }
                if ($city_id  != "") {
                $rs_stmt1 = $rs_stmt1 . " and  sh.city_id = '$city_id ' ";
                }
                if ($comme_no  != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  sc.comme_no = '$comme_no ' ";
                    }
                     if ($municip_no  != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  sm.municip_no = '$municip_no ' ";
                        }
                $rs_stmt1 = $rs_stmt1 . "  group by sh.shop_id ";

                $results = DB::select($rs_stmt1);
                return  $results;
            }














  public function scopeserachspendcount($query,$shop_name,$shop_mobile,$manager_id,$city_id,$comme_no,$municip_no){
    $shop_name = TRIM($shop_name);
    $shop_mobile = TRIM($shop_mobile);
    $manager_id = TRIM($manager_id);
    $city_id = TRIM($city_id);
    $comme_no = TRIM($comme_no);
    $municip_no = TRIM($municip_no);

        $rs_stmt1 = " SELECT sh.shop_id FROM  shop sh

        left join  shop_municip sm on sh.shop_id=sm.shop_id
            left join  shop_comme sc on sh.shop_id=sc.shop_id


         where  1=1   ";

      if(  $this->emp_job!=1){
          $rs_stmt1 = $rs_stmt1 . "
            join workers_manager wm on workers.manager_id =wm.manager_id and  wm.user_id=$this->user_id";
      }
      $rs_stmt1 = $rs_stmt1 . " where  1=1 ";


      if(  $this->emp_job!=1){
          if (Perm::get_function_access(70)) {
              $rs_stmt1 = $rs_stmt1 . " and  create_user = $this->user_id ";
          }
      }


        if ($shop_name  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sh.shop_name like '%$shop_name%' ";
            }
            if ($shop_mobile  != "") {
                $rs_stmt1 = $rs_stmt1 . " and  sh.shop_mobile like '%$shop_mobile%' ";
            }
            if ($manager_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sh.manager_id = '$manager_id ' ";
            }
            if ($city_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sh.city_id = '$city_id ' ";
            }
            if ($comme_no  != "") {
                $rs_stmt1 = $rs_stmt1 . " and  sc.comme_no = '$comme_no ' ";
                }
                 if ($municip_no  != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  sm.municip_no = '$municip_no ' ";
                    }
                    $rs_stmt1 = $rs_stmt1 . "  group by sh.shop_id ";

      $results = count(DB::select($rs_stmt1));
    return  $results;
    }


    public function scopeserachspenddata($query,$shop_name,$shop_mobile,$manager_id,$city_id,$comme_no,$municip_no){
        $a = $_POST['length'];
$b = $_POST['start'];
$shop_name = TRIM($shop_name);
$shop_mobile = TRIM($shop_mobile);
$manager_id = TRIM($manager_id);
$city_id = TRIM($city_id);
$comme_no = TRIM($comme_no);
$municip_no = TRIM($municip_no);

   if(isset($_POST['order']))
            {
            $columnName=$_POST['order']['0']['column'];
            $columnSortOrder  = $_POST['order']['0']['dir'];
            if($columnName!=0){
            $ord =  " order by  ".$columnName. " ". $columnSortOrder ;
            }
            else{
            $ord =  " ORDER BY shop_id DESC  " ;
            }

            }
            else{
            $ord =  "    " ;
            }

            $rs_stmt1 = " SELECT sh.*,m.manager_name,c.city_name,u.name,
            sm.municip_no,sm.municip_sdt,sm.municip_edt,
            sd.defence_no,sd.defence_sdt,sd.defence_edt,

CASE
    WHEN  sm.municip_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
    WHEN  sm.municip_edt  <=CURDATE() THEN '2'
    WHEN  sm.municip_edt is null THEN '4'
    ELSE '1'
    END
    as sm_desc,

    CASE
    WHEN  sc.comme_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
    WHEN  sc.comme_edt  <=CURDATE() THEN '2'
    WHEN  sc.comme_edt is null THEN '4'
    ELSE '1'
    END
    as sc_desc,


    CASE
    WHEN  sr.rent_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
    WHEN  sr.rent_edt  <=CURDATE() THEN '2'
    WHEN  sr.rent_edt is null THEN '4'
    ELSE '1'
    END
    as sr_desc,

    CASE
    WHEN  sd.defence_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
    WHEN  sd.defence_edt  <=CURDATE() THEN '2'
    WHEN  sd.defence_edt is null THEN '4'
    ELSE '1'
    END
    as sd_desc,


            sc.comme_sso,sc.comme_no,sc.comme_sdt,sc.comme_edt,
            sr.rent_no,sr.rent_sdt,sr.rent_edt

             FROM  shop sh
            left join  manager m on sh.manager_id=m.manager_id
            left join  city c on sh.city_id=c.city_id
            left join  users u on sh.create_user=u.id
            left join  shop_municip sm on sh.shop_id=sm.shop_id
            left join  shop_comme sc on sh.shop_id=sc.shop_id
            left join  shop_rent sr on sr.shop_id=sr.shop_id
            left join  shop_defence  sd on sh.shop_id=sd.shop_id


            where  1=1 ";
                    if ($shop_name  != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  sh.shop_name like '%$shop_name%' ";
                    }
                    if ($shop_mobile  != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  sh.shop_mobile like '%$shop_mobile%' ";
                    }
                    if ($manager_id  != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  sh.manager_id = '$manager_id ' ";
                    }
                    if ($city_id  != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  sh.city_id = '$city_id ' ";
                    }
                    if ($comme_no  != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  sc.comme_no = '$comme_no ' ";
                        }
                         if ($municip_no  != "") {
                            $rs_stmt1 = $rs_stmt1 . " and  sm.municip_no = '$municip_no ' ";
                            }
                    $rs_stmt1 = $rs_stmt1 . "  group by sh.shop_id ";

                    $rs_stmt1 = $rs_stmt1  .$ord;
                    $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
                    $results = DB::select($rs_stmt1);
                    return  $results;
                }








                public function scopeserachremarkcount($query,$shop_id){
                    $shop_id = TRIM($shop_id);
                        $rs_stmt1 = " SELECT shop_note_id FROM  shop_note where is_deleted=0 and   1=1  ";
                        if ($shop_id  != "") {
                            $rs_stmt1 = $rs_stmt1 . " and  shop_id = '$shop_id ' ";
                            }

                      $results = count(DB::select($rs_stmt1));
                    return  $results;
                    }


                    public function scopeserachremarkdata($query,$shop_id){
                        $a = $_POST['length'];
                $b = $_POST['start'];
                $shop_id = TRIM($shop_id);
                   if(isset($_POST['order']))
                            {
                            $columnName=$_POST['order']['0']['column'];
                            $columnSortOrder  = $_POST['order']['0']['dir'];
                            if($columnName!=0){
                            $ord =  " order by  ".$columnName. " ". $columnSortOrder ;
                            }
                            else{
                            $ord =  " ORDER BY shop_id DESC  " ;
                            }

                            }
                            else{
                            $ord =  "    " ;
                            }

                            $rs_stmt1 = " SELECT sn.*,u.name,n.note_type_name FROM   shop_note sn
                            left join  users u on sn.create_note_user=u.id
                            left join  note_type n on sn.note_type_id=n.note_type_id

                            where sn.is_deleted=0 and   1=1 ";
                                                          if ($shop_id  != "") {
                                                            $rs_stmt1 = $rs_stmt1 . " and  sn.shop_id = '$shop_id ' ";
                                                            }

                                    $rs_stmt1 = $rs_stmt1  .$ord;
                                    $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
                                    $results = DB::select($rs_stmt1);
                                    return  $results;
                                }





}



