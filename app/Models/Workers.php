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

class Workers extends Model
{
    use HasFactory;
   // use ApimtitTrait;

    protected $fillabel = ['worker_name', 'ssn', 'work_place_id', 'note', 'doe', 'created_at', 'create_user', 'updated_at', 'updated_user' ,"registration_number"];
    protected $guarded = ['worker_id'];
    protected $primaryKey = 'worker_id';
    // public $incrementing = false;
//protected $dateFormat = 'U';







public function __construct(array $attributes = [])
{
    parent::__construct($attributes);
    $this->emp_job = Auth()->user()->emp_job;
    $this->user_id=Auth::user()->id;
}

public function scopeworkercharthome($query)
{
    $rs_stmt1 = "  SELECT  COALESCE(m.manager_name, 'لا يوجد لهم مجموعة')  as SHOP_ID,w.manager_id as STATE,count(w.worker_id)as COUNT_ROW from
    workers w
    left join manager m on w.manager_id=m.manager_id
   ";

   if(  $this->emp_job!=1){
    $rs_stmt1 = $rs_stmt1 . "
        join workers_manager wm on w.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

    }
    $rs_stmt1 = $rs_stmt1 . " where  1=1 ";

    if(  $this->emp_job!=1){
    if (Perm::get_function_access(70)) {
    $rs_stmt1 = $rs_stmt1 . " and  w.create_user = $this->user_id ";
    }
    }
$rs_stmt1 = $rs_stmt1 . "     GROUP BY w.manager_id";
    $results = DB::select($rs_stmt1);
    return $results;
}






    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }


    public function scopesel_worker_list($query, $string, $page)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT worker_name as name, worker_id as id_no,worker_id as id,ssn,ssn
        from  workers    ";


if(  $this->emp_job!=1){
    $sql = $sql . "    join workers_manager wm on workers.manager_id =wm.manager_id and  wm.user_id=$this->user_id ";

   // $sql = $sql . "  join workers_manager wm on (workers.manager_id =wm.manager_id and  wm.user_id=$this->user_id) or (workers.manager_id is null )";

}
$sql = $sql . " where  1=1 ";



        if ($string != "") {
            $sql = $sql . " and ( worker_name LIKE '%$string%' or ssn LIKE '$string%')    ";
        }
        $sql = $sql . " group by worker_id order by worker_id   desc LIMIT {$end}, {$start} ";
        $results = DB::select($sql);
        $count_rs_chk = count(DB::select($sql));
        $users = DB::select($sql);
        $users = json_decode(json_encode($users), true);
        $data = array();
        foreach ($users as $user) {
            $data[] = array(
                "id" => $user['id'],
                "ItemName" => $user['name']
            , "item_code" => 'رقم الاقامة' . ' ' . $user['ssn']

            , "total_count" => $count_rs_chk);
        }
        return $data;
    }


    public function scopeins_tbl()
    {

        /*  DB::table('users')->insert([
              'email' => 'kayla@example.com',
              'votes' => 0
          ]);*/

        /*  DB::table('pruned_users')->insertUsing([
              'id', 'name', 'email', 'email_verified_at'
          ], DB::table('users')->select(
              'id', 'name', 'email', 'email_verified_at'
          )->where('updated_at', '<=', now()->subMonth()));*/


        /*  $id = DB::table('users')->insertGetId(
              ['email' => 'john@example.com', 'votes' => 0]
          );*/


        // $id= DB::query('insert into workers (worker_name, phone, remarks) values ("johndoe", "john@johndoe.com", "password")');
        //$id= DB::insert('insert into workers (worker_name, phone) values (?, ?)', ['john@example.com', '0']);
        DB::insert('insert into workers (worker_name, phone, remarks) values ("Mohanad", "Mohanad@johndoe.com", "password")');
        $id = DB::getPdo()->lastInsertId();
        return $id;
        // $id = Model::insertGetId(["worker_name"=>"Niklesh","phone"=>"myemail@gmail.com"]);
//  return  $id;

        // $id = DB::table('users')->insertGetId(["name"=>"Niklesh","email"=>"myemail@gmail.com"]);
        //  return  $id;
    }

    public function scopeupd_tblarr($query, $xxx)
    {

        /* DB::insert('insert into workers (worker_name, phone) values (?, ?)', [$xxx, $xxx]);
         $id = DB::getPdo()->lastInsertId();
         return  $id;*/
        $id = $xxx['id'];
        $worker_name = $xxx['worker_name'];
        $phone = $xxx['phone'];
        $remarks = $xxx['remarks'];
        //  DB::insert('insert into workers (worker_name, phone , remarks) values (?, ?, ?)', [$worker_name, $phone, $remarks]);
        //  DB::update('insert into workers (worker_name, phone , remarks) values (?, ?, ?)', [$worker_name, $phone, $remarks]);
        // $rs_stmt1 = " update workers   worker_name = $worker_name  where  1=1 and worker_id=$id  ";

        // $sql = "update workers   worker_name = $worker_name  where  1=1 and worker_id=$id;"; // replace food_order_test with the name of your test database
        //DB::statement("update workers  set  worker_name = '$worker_name'  where  1=1 and worker_id=$id;");
        DB::statement("UPDATE workers SET worker_name=?  WHERE  1=1 and worker_id =?", [$worker_name, $id]);
//dd($xxx);
        //$xxx='mmmm';


        /*  DB::table('users')->insert([
              'email' => 'kayla@example.com',
              'votes' => 0
          ]);*/

        /*  DB::table('pruned_users')->insertUsing([
              'id', 'name', 'email', 'email_verified_at'
          ], DB::table('users')->select(
              'id', 'name', 'email', 'email_verified_at'
          )->where('updated_at', '<=', now()->subMonth()));*/


        /*  $id = DB::table('users')->insertGetId(
              ['email' => 'john@example.com', 'votes' => 0]
          );*/


        // $id= DB::query('insert into workers (worker_name, phone, remarks) values ("johndoe", "john@johndoe.com", "password")');
        //$id= DB::insert('insert into workers (worker_name, phone) values (?, ?)', ['john@example.com', '0']);
        //  DB::insert('insert into workers (worker_name, phone, remarks) values ($xxx, "Mohanad@johndoe.com", "password")');


        //DB::insert('insert into workers (worker_name, phone, remarks) values (   ' . "'" . $xxx . "'" .       ",'" .$xxx . "'" .       ",'" .$xxx . "'"    . ')');

        //  DB::insert('insert into workers (worker_name, phone) values (?, ?)', [ $dataset['worker_name'] , $dataset['phone']]);
        /*
          DB::table('workers')
          ->insert([
                'worker_name' => $worker_name,
                'phone' =>$worker_name,
            ]);*/


        // $id = Model::insertGetId(["worker_name"=>"Niklesh","phone"=>"myemail@gmail.com"]);
        //  return  $id;

        // $id = DB::table('users')->insertGetId(["name"=>"Niklesh","email"=>"myemail@gmail.com"]);
        //  return  $id;
    }


    public function scopeins_tblarr($query, $xxx)
    {
//$xxx='mmmm';


        /*  DB::table('users')->insert([
              'email' => 'kayla@example.com',
              'votes' => 0
          ]);*/

        /*  DB::table('pruned_users')->insertUsing([
              'id', 'name', 'email', 'email_verified_at'
          ], DB::table('users')->select(
              'id', 'name', 'email', 'email_verified_at'
          )->where('updated_at', '<=', now()->subMonth()));*/


        /*  $id = DB::table('users')->insertGetId(
              ['email' => 'john@example.com', 'votes' => 0]
          );*/

        // $id= DB::query('insert into workers (worker_name, phone, remarks) values ("johndoe", "john@johndoe.com", "password")');
        //$id= DB::insert('insert into workers (worker_name, phone) values (?, ?)', ['john@example.com', '0']);
        //  DB::insert('insert into workers (worker_name, phone, remarks) values ($xxx, "Mohanad@johndoe.com", "password")');

        DB::insert('insert into workers (worker_name, phone) values (?, ?)', [$xxx, $xxx]);
        //DB::insert('insert into workers (worker_name, phone, remarks) values (   ' . "'" . $xxx . "'" .       ",'" .$xxx . "'" .       ",'" .$xxx . "'"    . ')');

//  DB::insert('insert into workers (worker_name, phone) values (?, ?)', [ $dataset['worker_name'] , $dataset['phone']]);
        /*
          DB::table('workers')
          ->insert([
                'worker_name' => $worker_name,
                'phone' =>$worker_name,
            ]);*/
        $id = DB::getPdo()->lastInsertId();
        return $id;


        // $id = Model::insertGetId(["worker_name"=>"Niklesh","phone"=>"myemail@gmail.com"]);
        //  return  $id;

        // $id = DB::table('users')->insertGetId(["name"=>"Niklesh","email"=>"myemail@gmail.com"]);
        //  return  $id;
    }


    public function scopeworkreport($query, $worker_id, $worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt,$manager_id,$inside,$is_imp,$nation)
    {
        $worker_id = TRIM($worker_id);
        $worker_name = TRIM($worker_name);
        $ssn = TRIM($ssn);
        $work_place_id = TRIM($work_place_id);
        $doe = TRIM($doe);
        $updatedcancal_at = TRIM($updatedcancal_at);
        $job_id = TRIM($job_id);
        $end_dt = TRIM($end_dt);
        $end_p_dt = TRIM($end_p_dt);
        $manager_id = TRIM($manager_id);
        $inside = TRIM($inside);
        $is_imp = TRIM($is_imp);
        $nation = TRIM($nation);


        $rs_stmt1 = " SELECT w.*,n.nation_name_ar,j.job_name,wp.work_place_name,m.manager_name,
            CASE
                WHEN  w.doe BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                WHEN  w.doe  <=CURDATE() THEN '2'
                WHEN  w.doe is null THEN '4'
                ELSE '1'
                END
                as doe_desc,

                CASE
                WHEN  w.dop BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                WHEN  w.dop <=CURDATE() THEN '2'
                WHEN  w.dop is null THEN '4'
                ELSE '1'
                END
                as dop_desc
            FROM  workers w
    left join  nation n on w.nation_id=n.nation_id
    left join  job j on w.job_id=j.job_id
    left join  work_place wp on w.work_place_id=wp.work_place_id
    left join  users u on w.create_user=u.id
    left join  manager m on w.manager_id=m.manager_id

   ";




    if(  $this->emp_job!=1){
        $rs_stmt1 = $rs_stmt1 . "
            join workers_manager wm on w.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 ";

        if(  $this->emp_job!=1){
        if (Perm::get_function_access(70)) {
        $rs_stmt1 = $rs_stmt1 . " and  w.create_user = $this->user_id ";
        }
        }


        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.worker_id = '$worker_id ' ";
        }

        if ($inside != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.inside = '$inside' ";
        }
        if ($is_imp != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.is_imp = '$is_imp' ";
        }
        if ($nation != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.nation_id = '$nation' ";
        }

                if ($end_dt != '') {
                    $rs_stmt1 = $rs_stmt1 . " and
                (
                    CASE
                    WHEN  w.doe BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                    WHEN  w.doe  <=CURDATE() THEN '2'
                    WHEN  w.doe is null THEN '4'
                    ELSE '1'
                    END) IN ($end_dt)
            ";
                }

                if ($end_p_dt != '') {
                    $rs_stmt1 = $rs_stmt1 . " and
                    (
                        CASE
                        WHEN  w.dop BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                        WHEN  w.dop  <=CURDATE() THEN '2'
                        WHEN  w.dop is null THEN '4'
                        ELSE '1'
                        END) IN ($end_p_dt)
                ";
                }


                if ($worker_name != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  w.worker_name like '%$worker_name%' ";
                }

                if ($ssn != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  w.ssn = '$ssn' ";
                }

                if ($work_place_id != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  w.work_place_id = '$work_place_id ' ";
                }
                if ($manager_id != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  w.manager_id = '$manager_id ' ";
                }

                if ($doe != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  w.doe = '$doe ' ";
                }
                if ($updatedcancal_at != "") {
                    if ($updatedcancal_at == "1") {
                        $rs_stmt1 = $rs_stmt1 . " and  w.updatedcancal_at  is  null";
                    } else if ($updatedcancal_at == "0") {
                        $rs_stmt1 = $rs_stmt1 . " and  w.updatedcancal_at  is not null";
                    }
                }
                if ($job_id != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  w.job_id = '$job_id ' ";
                }
                $rs_stmt1 = $rs_stmt1 . "  group by w.worker_id  ";

        $results = DB::select($rs_stmt1);

        return $results;
    }

    public function scopeserachspendcount($query, $worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt,$manager_id,$inside,$is_imp,$nation, $residence_month,$residence_year,$passport_month,$passport_year)
    {

        $worker_name = TRIM($worker_name);
        $ssn = TRIM($ssn);
        $work_place_id = TRIM($work_place_id);
        $doe = TRIM($doe);
        $updatedcancal_at = TRIM($updatedcancal_at);
        $job_id = TRIM($job_id);
        $end_dt = TRIM($end_dt);
        $end_p_dt = TRIM($end_p_dt);
        $manager_id = TRIM($manager_id);
        $inside = TRIM($inside);
        $is_imp = TRIM($is_imp);
        $nation = TRIM($nation);

        $rs_stmt1 = " SELECT worker_id FROM  workers   ";
        if(  $this->emp_job!=1){
           $rs_stmt1 = $rs_stmt1 . "
            join workers_manager wm on workers.manager_id =wm.manager_id and  wm.user_id=$this->user_id";
           // $rs_stmt1 = $rs_stmt1 . "  join workers_manager wm on (workers.manager_id =wm.manager_id and  wm.user_id=$this->user_id) or (workers.manager_id is null )";
        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 ";


if(  $this->emp_job!=1){
if (Perm::get_function_access(70)) {
$rs_stmt1 = $rs_stmt1 . " and  create_user = $this->user_id ";
}
}

if ($inside != "") {
    $rs_stmt1 = $rs_stmt1 . " and  inside = '$inside' ";
}
if ($is_imp != "") {
    $rs_stmt1 = $rs_stmt1 . " and  is_imp = '$is_imp' ";
}
if ($nation != "") {
    $rs_stmt1 = $rs_stmt1 . " and  nation_id = '$nation' ";
}

        if ($worker_name != "") {
            $rs_stmt1 = $rs_stmt1 . " and  worker_name like '%$worker_name%' ";
        }



        if( $residence_month!='')
        {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(doe) = '$residence_month ' ";

        }
        if( $residence_year!='')
        {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(doe) = '$residence_year ' ";

        }

        if( $passport_month!='')
        {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(dop) = '$passport_month ' ";

        }
        if( $passport_year!='')
        {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(dop) = '$passport_year ' ";

        }


        if ($end_dt != '') {
            $rs_stmt1 = $rs_stmt1 . " and
            (
                CASE
                WHEN  doe BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                WHEN  doe  <=CURDATE() THEN '2'
                WHEN  doe is null THEN '4'
                ELSE '1'
                END) IN ($end_dt)
        ";
        }

        if ($end_p_dt != '') {
            $rs_stmt1 = $rs_stmt1 . " and
                (
                    CASE
                    WHEN  doe BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                    WHEN  doe  <=CURDATE() THEN '2'
                    WHEN  doe is null THEN '4'
                    ELSE '1'
                    END) IN ($end_p_dt)
            ";
        }

        if ($ssn != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ssn = '$ssn' ";
        }

        if ($work_place_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  work_place_id = '$work_place_id ' ";
        }

        if ($doe != "") {
            $rs_stmt1 = $rs_stmt1 . " and  doe = '$doe ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  workers.manager_id = '$manager_id ' ";
        }



        if ($updatedcancal_at != "") {
            if ($updatedcancal_at == "1") {
                $rs_stmt1 = $rs_stmt1 . " and  updatedcancal_at  is  null";
            } else if ($updatedcancal_at == "0") {
                $rs_stmt1 = $rs_stmt1 . " and  updatedcancal_at  is not null";
            }
        }
        if ($job_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  job_id = '$job_id ' ";
        }
        $rs_stmt1 = $rs_stmt1 . "  group by workers.worker_id ";


        $results = count(DB::select($rs_stmt1));
        return $results;
    }



































    public function scopeserachspenddata($query, $worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt,$manager_id,$inside,$is_imp,$nation,$order_date, $residence_month,$residence_year,$passport_month,$passport_year)
    {
        $a = $_POST['length'] ?? null;
        $b = $_POST['start'] ?? null;
        $worker_name = TRIM($worker_name);
        $ssn = TRIM($ssn);
        $work_place_id = TRIM($work_place_id);
        $doe = TRIM($doe);
        $updatedcancal_at = TRIM($updatedcancal_at);
        $job_id = TRIM($job_id);
        $end_dt = TRIM($end_dt);
        $end_p_dt = TRIM($end_p_dt);
        $manager_id = TRIM($manager_id);
        $inside = TRIM($inside);
        $is_imp = TRIM($is_imp);
        $nation = TRIM($nation);

        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY worker_id DESC  ";
            }

        } else {
            $ord = "    ";
        }
        if($order_date=="passport_date")
        {
            //جواز السفر
            $ord = " ORDER BY w.doe   ";

        }
        else
        {
            // تاريخ انتهاء الاقامة
            $ord = " ORDER BY w.dop   ";

        }


        $rs_stmt1 = " SELECT w.*,n.nation_name_ar,j.job_name,wp.work_place_name,m.manager_name,count(distinct(wn.worker_note_id)) as count_work_note,
u.name ,u2.name as 'imp_user',


        CASE
            WHEN  w.doe BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
            WHEN  w.doe  <=CURDATE() THEN '2'
            WHEN  w.doe is null THEN '4'
            ELSE '1'
            END
            as doe_desc,

            CASE
            WHEN  w.dop BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
            WHEN  w.dop <=CURDATE() THEN '2'
            WHEN  w.dop is null THEN '4'
            ELSE '1'
            END
            as dop_desc




        FROM  workers w
left join  nation n on w.nation_id=n.nation_id
left join  job j on w.job_id=j.job_id
left join  work_place wp on w.work_place_id=wp.work_place_id
left join  users u on w.create_user=u.id
left join  manager m on w.manager_id=m.manager_id
left join  worker_note wn on w.worker_id=wn.worker_id and  wn.is_deleted=0 and wn.note_type_id !=3
left join  users u2 on w.imp_user=u2.id



          ";



if(  $this->emp_job!=1){
$rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on w.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

}
$rs_stmt1 = $rs_stmt1 . " where  1=1 ";

if(  $this->emp_job!=1){
if (Perm::get_function_access(70)) {
$rs_stmt1 = $rs_stmt1 . " and  w.create_user = $this->user_id ";
}
}



if ($inside != "") {
    $rs_stmt1 = $rs_stmt1 . " and  w.inside = '$inside' ";
}
if ($is_imp != "") {
    $rs_stmt1 = $rs_stmt1 . " and  w.is_imp = '$is_imp' ";
}
if ($nation != "") {
    $rs_stmt1 = $rs_stmt1 . " and  w.nation_id = '$nation' ";
}

        if ($end_dt != '') {
            $rs_stmt1 = $rs_stmt1 . " and
        (
            CASE
            WHEN  w.doe BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
            WHEN  w.doe  <=CURDATE() THEN '2'
            WHEN  w.doe is null THEN '4'
            ELSE '1'
            END) IN ($end_dt)
    ";
        }






        if ($end_p_dt != '') {
            $rs_stmt1 = $rs_stmt1 . " and
            (
                CASE
                WHEN  w.dop BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                WHEN  w.dop  <=CURDATE() THEN '2'
                WHEN  w.dop is null THEN '4'
                ELSE '1'
                END) IN ($end_p_dt)
        ";
        }


        if ($worker_name != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.worker_name like '%$worker_name%' ";
        }

        if ($ssn != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.ssn = '$ssn' ";
        }

        if ($work_place_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.work_place_id = '$work_place_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.manager_id = '$manager_id ' ";
        }

        if ($doe != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.doe = '$doe ' ";
        }


        if( $residence_month!='')
        {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(doe) = '$residence_month ' ";

        }
        if( $residence_year!='')
        {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(doe) = '$residence_year ' ";

        }

        if( $passport_month!='')
        {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(dop) = '$passport_month ' ";

        }
        if( $passport_year!='')
        {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(dop) = '$passport_year  ' ";

        }




        if ($updatedcancal_at != "") {
            if ($updatedcancal_at == "1") {
                $rs_stmt1 = $rs_stmt1 . " and  w.updatedcancal_at  is  null";
            } else if ($updatedcancal_at == "0") {
                $rs_stmt1 = $rs_stmt1 . " and  w.updatedcancal_at  is not null";
            }
        }
        if ($job_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.job_id = '$job_id ' ";
        }
        $rs_stmt1 = $rs_stmt1 . "  group by w.worker_id  ";

        $rs_stmt1 = $rs_stmt1 . $ord;

        if(isset( $_POST['length']))
            $rs_stmt1 = $rs_stmt1 . "   limit $b,$a ";

        $results = DB::select($rs_stmt1);

        return $results;
    }








    public function scopeserachspendhome($query)
    {
        $end_dt=2;
        $end_p_dt=2;

        $rs_stmt1 = " SELECT w.*,


        CASE
            WHEN  w.doe BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
            WHEN  w.doe  <=CURDATE() THEN '2'
            WHEN  w.doe is null THEN '4'
            ELSE '1'
            END
            as doe_desc,

            CASE
            WHEN  w.dop BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
            WHEN  w.dop <=CURDATE() THEN '2'
            WHEN  w.dop is null THEN '4'
            ELSE '1'
            END
            as dop_desc




        FROM  workers w
left join  manager m on w.manager_id=m.manager_id


          ";



if(  $this->emp_job!=1){
$rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on w.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

}
$rs_stmt1 = $rs_stmt1 . " where  1=1 ";

if(  $this->emp_job!=1){
if (Perm::get_function_access(70)) {
$rs_stmt1 = $rs_stmt1 . " and  w.create_user = $this->user_id ";
}
}



            $rs_stmt1 = $rs_stmt1 . " and (
        (
            CASE
            WHEN  w.doe BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
            WHEN  w.doe  <=CURDATE() THEN '2'
            WHEN  w.doe is null THEN '4'
            ELSE '1'
            END) IN ($end_dt)

           or
            (
                CASE
                WHEN  w.dop BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                WHEN  w.dop  <=CURDATE() THEN '2'
                WHEN  w.dop is null THEN '4'
                ELSE '1'
                END) IN ($end_p_dt)
                )
        ";



        $rs_stmt1 = $rs_stmt1 . "  group by w.worker_id, dop_desc,doe_desc ";

        $results = DB::select($rs_stmt1);

        return $results;
    }















    public function scopesumspenddata($query, $worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt,$manager_id,$inside,$is_imp,$nation, $residence_month,$residence_year,$passport_month,$passport_year)
    {
        $worker_name = TRIM($worker_name);
        $ssn = TRIM($ssn);
        $work_place_id = TRIM($work_place_id);
        $doe = TRIM($doe);
        $updatedcancal_at = TRIM($updatedcancal_at);
        $job_id = TRIM($job_id);
        $end_dt = TRIM($end_dt);
        $end_p_dt = TRIM($end_p_dt);
        $manager_id = TRIM($manager_id);

        $inside = TRIM($inside);
        $is_imp = TRIM($is_imp);
        $nation = TRIM($nation);

        $rs_stmt1 = " SELECT

COUNT(CASE WHEN w.is_imp = 1  THEN 1 END) AS `all_imp`,

COUNT(CASE WHEN w.is_imp = 1 AND w.updatecancal_user is  null THEN 1 END) AS `all_imp_not_cancal`,

COUNT(CASE WHEN w.is_imp = 1 AND w.updatecancal_user is not null THEN 1 END) AS `all_imp_cancal`,
COUNT(CASE WHEN  w.updatecancal_user is  null THEN 1 END) AS `all_not_cancal`,
COUNT(CASE WHEN  w.updatecancal_user is  not null THEN 1 END) AS `all_cancal`,
COUNT(CASE WHEN  w.manager_id  is  null AND w.updatecancal_user is  null  THEN 1 END) AS `not_have_manger`,
COUNT(CASE WHEN  w.inside  =0 or w.inside  is null  THEN 1 END) AS `out_ksa`,
COUNT(CASE WHEN  w.inside  =1   THEN 1 END) AS `in_ksa`

            FROM  workers w
    left join  nation n on w.nation_id=n.nation_id
    left join  job j on w.job_id=j.job_id
    left join  work_place wp on w.work_place_id=wp.work_place_id
    left join  users u on w.create_user=u.id
    left join  manager m on w.manager_id=m.manager_id
    left join  users u2 on w.imp_user=u2.id





          ";



if(  $this->emp_job!=1){
$rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on w.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

}
$rs_stmt1 = $rs_stmt1 . " where  1=1 ";

if(  $this->emp_job!=1){
if (Perm::get_function_access(70)) {
$rs_stmt1 = $rs_stmt1 . " and  w.create_user = $this->user_id ";
}
}

if ($inside != "") {
    $rs_stmt1 = $rs_stmt1 . " and  w.inside = '$inside' ";
}
if ($is_imp != "") {
    $rs_stmt1 = $rs_stmt1 . " and  w.is_imp = '$is_imp' ";
}
if ($nation != "") {
    $rs_stmt1 = $rs_stmt1 . " and  w.nation_id = '$nation' ";
}


if( $residence_month!='')
{
    $rs_stmt1 = $rs_stmt1 . " and  MONTH(doe) = '$residence_month ' ";

}
if( $residence_year!='')
{
    $rs_stmt1 = $rs_stmt1 . " and  YEAR(doe) = '$residence_year ' ";

}

if( $passport_month!='')
{
    $rs_stmt1 = $rs_stmt1 . " and  MONTH(dop) = '$passport_month ' ";

}
if( $passport_year!='')
{
    $rs_stmt1 = $rs_stmt1 . " and  YEAR(dop) = '$passport_year ' ";

}




        if ($end_dt != '') {
            $rs_stmt1 = $rs_stmt1 . " and
        (
            CASE
            WHEN  w.doe BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
            WHEN  w.doe  <=CURDATE() THEN '2'
            WHEN  w.doe is null THEN '4'
            ELSE '1'
            END) IN ($end_dt)
    ";
        }

        if ($end_p_dt != '') {
            $rs_stmt1 = $rs_stmt1 . " and
            (
                CASE
                WHEN  w.dop BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                WHEN  w.dop  <=CURDATE() THEN '2'
                WHEN  w.dop is null THEN '4'
                ELSE '1'
                END) IN ($end_p_dt)
        ";
        }


        if ($worker_name != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.worker_name like '%$worker_name%' ";
        }

        if ($ssn != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.ssn = '$ssn' ";
        }

        if ($work_place_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.work_place_id = '$work_place_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.manager_id = '$manager_id ' ";
        }

        if ($doe != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.doe = '$doe ' ";
        }
        if ($updatedcancal_at != "") {
            if ($updatedcancal_at == "1") {
                $rs_stmt1 = $rs_stmt1 . " and  w.updatedcancal_at  is  null";
            } else if ($updatedcancal_at == "0") {
                $rs_stmt1 = $rs_stmt1 . " and  w.updatedcancal_at  is not null";
            }
        }
        if ($job_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  w.job_id = '$job_id ' ";
        }

        $results = DB::select($rs_stmt1);

        return $results;
    }





















    public function scopeserachremarkcount($query, $worker_id)
    {
        $worker_id = TRIM($worker_id);
        $rs_stmt1 = " SELECT worker_note_id FROM  worker_note where is_deleted=0  and   1=1  ";
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  worker_id = '$worker_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachremarkdata($query, $worker_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $worker_id = TRIM($worker_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY worker_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT sn.*,u.name,n.note_type_name FROM
                    worker_note sn
                    left join  users u on sn.create_note_user=u.id
                    left join  note_type n on sn.note_type_id=n.note_type_id

                    where sn.is_deleted=0 and sn.is_deleted=0  and 1=1  ";
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.worker_id = '$worker_id ' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }

    public function scopeserachhistorycount($query, $worker_id)
    {
        $worker_id = TRIM($worker_id);
        $rs_stmt1 = " SELECT nh.worker_note_history_id  FROM  worker_note_history nh
         join worker_note sn on nh.worker_note_id=sn.worker_note_id


         where   1=1  ";
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.worker_id = '$worker_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachhistorydata($query, $worker_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $worker_id = TRIM($worker_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY worker_note_id ,note_type_id  DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT nh.*,u.name,n.note_type_name,n2.note_type_name as note_type_name_old,sn.worker_note_id  FROM

worker_note_history nh
 join worker_note sn on nh.worker_note_id=sn.worker_note_id
left join  users u on nh.change_user =u.id
left join  note_type n on nh.note_type_id=n.note_type_id
left join  note_type n2 on nh.old_note_type_id =n2.note_type_id

                    where    1=1 ";
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.worker_id = '$worker_id ' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }

}





