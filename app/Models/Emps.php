<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class emps extends Model
{
    use HasFactory;

    protected $fillabel = ['worker_name', 'phone', 'email', 'remarks', 'updated_at', 'created_at'];
    protected $guarded = ['worker_id'];
    protected $primaryKey = 'worker_id';
    // public $incrementing = false;
//protected $dateFormat = 'U';


    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }


    public function scopesel_emp_supervisor($query, $string, $page)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT name as name, id as id_no,id as id
        from users where  1=1  ";
        if ($string != "") {
            $sql = $sql . " and ( name LIKE '%$string%' or id LIKE '$string%')    ";
        }
        //  echo $sql;
        $sql = $sql . " order by id  desc LIMIT {$end}, {$start} ";
        //   $results = DB::select( DB::raw($sql) );
        $results = DB::select($sql);


        //  dd($results);

        //$count_rs_chk = count(DB::select( DB::raw( $sql)) );

        $count_rs_chk = count(DB::select($sql));


        //$users=DB::select( DB::raw( $sql));
        $users = DB::select($sql);

        $users = json_decode(json_encode($users), true);


        $data = array();
        foreach ($users as $user) {
            $data[] = array(
                "id" => $user['id'],
                "ItemName" => $user['name']
            , "item_code" => 'رقم الوظيفي' . ' ' . $user['id']
            , "total_count" => $count_rs_chk);
        }
        return $data;
    }


    public function scopesel_emp_supervisor___________($query, $string, $page, $job)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT emp_name as name, job_num as id_no,id as id
            from users where  1=1 and emp_job not in ($job,1)  ";
        if ($string != "") {
            $sql = $sql . " and ( emp_name LIKE '%$string%' or job_num LIKE '$string%')    ";
        }
        $sql = $sql . " order by id  desc LIMIT {$end}, {$start} ";
        // $result = $this->db->query($sql);
        // $count_rs_chk= $result->num_rows();
        $result = DB::select(DB::raw($sql));
        // dd($result);
        // $count_rs_chk= $result->num_rows();
        $count_rs_chk = count(DB::select(DB::raw($sql)));

        // return  $results;

        //   $users=DB::select( DB::raw( $sql))->result_array();


        $users = DB::select(DB::raw($sql));
        $users = json_decode(json_encode($users), true);

        //  $users = DB::select( DB::raw( $sql))->get();
        // $users = $users->toArray();

        // dd($users);
        $data = array();
        foreach ($users as $user) {
            //      echo $user['id'];
            $data[] = array(
                "id" => $user['id'],
                "ItemName" => $user['name']
            , "item_code" => 'رقم الوظيفي' . ' ' . $user['id_no']
            , "total_count" => $count_rs_chk);
        }
        return $data;
    }


    public function scopeins_tbl($query, $xxx)
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


        // $id= DB::query('insert into emps (worker_name, phone, remarks) values ("johndoe", "john@johndoe.com", "password")');
        //$id= DB::insert('insert into emps (worker_name, phone) values (?, ?)', ['john@example.com', '0']);
        DB::insert('insert into emps (worker_name, phone, remarks) values ("Mohanad", "Mohanad@johndoe.com", "password")');
        $id = DB::getPdo()->lastInsertId();
        return $id;
        // $id = Model::insertGetId(["worker_name"=>"Niklesh","phone"=>"myemail@gmail.com"]);
//  return  $id;

        // $id = DB::table('users')->insertGetId(["name"=>"Niklesh","email"=>"myemail@gmail.com"]);
        //  return  $id;
    }

    public function scopeupd_tblarr($query, $xxx)
    {

        /* DB::insert('insert into emps (worker_name, phone) values (?, ?)', [$xxx, $xxx]);
         $id = DB::getPdo()->lastInsertId();
         return  $id;*/
        $id = $xxx['id'];
        $worker_name = $xxx['worker_name'];
        $phone = $xxx['phone'];
        $remarks = $xxx['remarks'];
        //  DB::insert('insert into emps (worker_name, phone , remarks) values (?, ?, ?)', [$worker_name, $phone, $remarks]);
        //  DB::update('insert into emps (worker_name, phone , remarks) values (?, ?, ?)', [$worker_name, $phone, $remarks]);
        // $rs_stmt1 = " update emps   worker_name = $worker_name  where  1=1 and worker_id=$id  ";

        // $sql = "update emps   worker_name = $worker_name  where  1=1 and worker_id=$id;"; // replace food_order_test with the name of your test database
        //DB::statement("update emps  set  worker_name = '$worker_name'  where  1=1 and worker_id=$id;");
        DB::statement("UPDATE emps SET worker_name=?  WHERE  1=1 and worker_id =?", [$worker_name, $id]);
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


        // $id= DB::query('insert into emps (worker_name, phone, remarks) values ("johndoe", "john@johndoe.com", "password")');
        //$id= DB::insert('insert into emps (worker_name, phone) values (?, ?)', ['john@example.com', '0']);
        //  DB::insert('insert into emps (worker_name, phone, remarks) values ($xxx, "Mohanad@johndoe.com", "password")');


        //DB::insert('insert into emps (worker_name, phone, remarks) values (   ' . "'" . $xxx . "'" .       ",'" .$xxx . "'" .       ",'" .$xxx . "'"    . ')');

        //  DB::insert('insert into emps (worker_name, phone) values (?, ?)', [ $dataset['worker_name'] , $dataset['phone']]);
        /*
          DB::table('emps')
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
        DB::insert('insert into emps (worker_name, phone) values (?, ?)', [$xxx, $xxx]);
        $id = DB::getPdo()->lastInsertId();
        return $id;
    }


    public function scopeserachspendcount($query, $worker_name, $sex, $phone, $email)
    {
        $rs_stmt1 = " SELECT worker_id FROM  emps where  1=1  ";
        if ($worker_name != "") {
            $rs_stmt1 = $rs_stmt1 . " and  worker_name like '%$worker_name%' ";
        }

        if ($sex != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sex = '$sex ' ";
        }

        if ($phone != "") {
            $rs_stmt1 = $rs_stmt1 . " and  phone = '$phone ' ";
        }

        if ($email != "") {
            $rs_stmt1 = $rs_stmt1 . " and  email = '$email ' ";
        }

        /*
        if ($dt_from != "1970/01/01" and $dt_to != "1970/01/01") {
        $rs_stmt1 = $rs_stmt1 . " and dt_exchange  between'$dt_from' and '$dt_to'  ";
        }
        if ($dt_from == "1970/01/01" and $dt_to != "1970/01/01") {
        $rs_stmt1 = $rs_stmt1 . " and dt_exchange <='$dt_to'";
        }
        if ($dt_from != "1970/01/01" and $dt_to == "1970/01/01") {
        $rs_stmt1 = $rs_stmt1 . " and dt_exchange  >='$dt_from'";
        }
        $rs_stmt1 = $rs_stmt1 . " order by exchange_id desc   ";
        $rs1 = $this->db->query($rs_stmt1);*/
// $results = DB::query( $rs_stmt1 );
        $results = count(DB::select(DB::raw($rs_stmt1)));

        return $results;

        // return $rs1->num_rows();
    }


    public function scopeserachspenddata($query, $worker_name, $sex, $phone, $email)
    {
        $rs_stmt1 = " SELECT users.*,job_cat.j_c_name_ar FROM  users
        left join  job_cat  on  users.emp_job =job_cat.j_c_id

        where  1=1  ";
        if ($worker_name != "") {
            $rs_stmt1 = $rs_stmt1 . " and  users.worker_name like '%$worker_name%' ";
        }

        if ($sex != "") {
            $rs_stmt1 = $rs_stmt1 . " and  users.sex = '$sex ' ";
        }

        if ($phone != "") {
            $rs_stmt1 = $rs_stmt1 . " and  users.phone = '$phone ' ";
        }

        if ($email != "") {
            $rs_stmt1 = $rs_stmt1 . " and  users.email = '$email ' ";
        }
        $results = DB::select(DB::raw($rs_stmt1));
        return $results;
    }

    public function scopexxx()
    {
        $results = DB::select(DB::raw("SELECT * FROM categories WHERE 1=1"));
        return $results;
        //dd($results);
        // DB::table("categories")->get();

        /* $someVariable = Input::get("some_variable");
        $results = DB::select( DB::raw("SELECT * FROM some_table WHERE some_col = :somevariable"), array(
           'somevariable' => $someVariable,
         ));*/
        //$someVariable = Input::get("some_variable");
        //$results = DB::select( DB::raw("SELECT * FROM some_table WHERE some_col = '$someVariable'") );


        //DB::statement( 'ALTER TABLE HS_Request AUTO_INCREMENT=9999' );

    }

}



