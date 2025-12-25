<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class Manager extends Model
{
    use HasFactory;

    protected $fillabel = ['manager_name', 'ssn', 'work_place_id', 'note', 'doe', 'created_at', 'create_user', 'updated_at', 'updated_user'];
    protected $guarded = ['manager_id'];
    protected $primaryKey = 'manager_id';
    protected $table = "manager";

    // public $incrementing = false;
//protected $dateFormat = 'U';


    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }


    public function scopesel_manager_list($query, $string, $page)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT manager_name as name, manager_id as id_no,manager_id as id
        from  manager where  1=1  ";
        if ($string != "") {
            $sql = $sql . " and ( manager_name LIKE '%$string%' or ssn LIKE '$string%')    ";
        }
        $sql = $sql . " order by manager_id  desc LIMIT {$end}, {$start} ";
        $results = DB::select($sql);
        $count_rs_chk = count(DB::select($sql));
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


        // $id= DB::query('insert into manager (manager_name, phone, remarks) values ("johndoe", "john@johndoe.com", "password")');
        //$id= DB::insert('insert into manager (manager_name, phone) values (?, ?)', ['john@example.com', '0']);
        DB::insert('insert into manager (manager_name, phone, remarks) values ("Mohanad", "Mohanad@johndoe.com", "password")');
        $id = DB::getPdo()->lastInsertId();
        return $id;
        // $id = Model::insertGetId(["manager_name"=>"Niklesh","phone"=>"myemail@gmail.com"]);
//  return  $id;

        // $id = DB::table('users')->insertGetId(["name"=>"Niklesh","email"=>"myemail@gmail.com"]);
        //  return  $id;
    }

    public function scopeupd_tblarr($query, $xxx)
    {

        /* DB::insert('insert into manager (manager_name, phone) values (?, ?)', [$xxx, $xxx]);
         $id = DB::getPdo()->lastInsertId();
         return  $id;*/
        $id = $xxx['id'];
        $manager_name = $xxx['manager_name'];
        $phone = $xxx['phone'];
        $remarks = $xxx['remarks'];
        //  DB::insert('insert into manager (manager_name, phone , remarks) values (?, ?, ?)', [$manager_name, $phone, $remarks]);
        //  DB::update('insert into manager (manager_name, phone , remarks) values (?, ?, ?)', [$manager_name, $phone, $remarks]);
        // $rs_stmt1 = " update manager   manager_name = $manager_name  where  1=1 and manager_id=$id  ";

        // $sql = "update manager   manager_name = $manager_name  where  1=1 and manager_id=$id;"; // replace food_order_test with the name of your test database
        //DB::statement("update manager  set  manager_name = '$manager_name'  where  1=1 and manager_id=$id;");
        DB::statement("UPDATE manager SET manager_name=?  WHERE  1=1 and manager_id =?", [$manager_name, $id]);
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


        // $id= DB::query('insert into manager (manager_name, phone, remarks) values ("johndoe", "john@johndoe.com", "password")');
        //$id= DB::insert('insert into manager (manager_name, phone) values (?, ?)', ['john@example.com', '0']);
        //  DB::insert('insert into manager (manager_name, phone, remarks) values ($xxx, "Mohanad@johndoe.com", "password")');


        //DB::insert('insert into manager (manager_name, phone, remarks) values (   ' . "'" . $xxx . "'" .       ",'" .$xxx . "'" .       ",'" .$xxx . "'"    . ')');

        //  DB::insert('insert into manager (manager_name, phone) values (?, ?)', [ $dataset['manager_name'] , $dataset['phone']]);
        /*
          DB::table('manager')
          ->insert([
                'manager_name' => $manager_name,
                'phone' =>$manager_name,
            ]);*/


        // $id = Model::insertGetId(["manager_name"=>"Niklesh","phone"=>"myemail@gmail.com"]);
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

        // $id= DB::query('insert into manager (manager_name, phone, remarks) values ("johndoe", "john@johndoe.com", "password")');
        //$id= DB::insert('insert into manager (manager_name, phone) values (?, ?)', ['john@example.com', '0']);
        //  DB::insert('insert into manager (manager_name, phone, remarks) values ($xxx, "Mohanad@johndoe.com", "password")');

        DB::insert('insert into manager (manager_name, phone) values (?, ?)', [$xxx, $xxx]);
        //DB::insert('insert into manager (manager_name, phone, remarks) values (   ' . "'" . $xxx . "'" .       ",'" .$xxx . "'" .       ",'" .$xxx . "'"    . ')');

//  DB::insert('insert into manager (manager_name, phone) values (?, ?)', [ $dataset['manager_name'] , $dataset['phone']]);
        /*
          DB::table('manager')
          ->insert([
                'manager_name' => $manager_name,
                'phone' =>$manager_name,
            ]);*/
        $id = DB::getPdo()->lastInsertId();
        return $id;


        // $id = Model::insertGetId(["manager_name"=>"Niklesh","phone"=>"myemail@gmail.com"]);
        //  return  $id;

        // $id = DB::table('users')->insertGetId(["name"=>"Niklesh","email"=>"myemail@gmail.com"]);
        //  return  $id;
    }


    public function scopeserachspendcount($query, $manager_name, $manager_mobile)
    {
        $manager_name = TRIM($manager_name);
        $manager_mobile = TRIM($manager_mobile);
        $rs_stmt1 = " SELECT manager_id FROM  manager where  1=1  ";
        if ($manager_name != "") {
            $rs_stmt1 = $rs_stmt1 . " and  manager_name like '%$manager_name%' ";
        }
        if ($manager_mobile != "") {
            $rs_stmt1 = $rs_stmt1 . " and  manager_mobile like '%$manager_mobile%' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachspenddata($query, $manager_name, $manager_mobile)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $manager_name = TRIM($manager_name);
        $manager_mobile = TRIM($manager_mobile);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY m.manager_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT m.* FROM  manager m
            left join  users u on m.create_user=u.id
            where  1=1 ";
        if ($manager_name != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.manager_name like '%$manager_name%' ";
        }
        if ($manager_mobile != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.manager_mobile like '%$manager_mobile%' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
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

    /**
     * Get all of the shops for the Manager
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shops()
    {
        return $this->hasMany(Shop::class, 'manager_id', 'manager_id');
    }

}



