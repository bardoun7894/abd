<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use Illuminate\Support\Facades\DB;

//use Illuminate\validation\Rule;
//use Illuminate\Database\Query\Builder;

class Categories extends Model
{

    // public $table = "posts";
    use HasFactory;


    public function scopeisdel(Builder $builder)
    {
        $builder->where('isdelete', '=', '0');
    }


    public function scopeToday($query)

    {

        return $query->whereDate('created_at', \Carbon\Carbon::today());

    }

    public function scopeStatus($query, $type)

    {

        return $query->where('isdelete', $type);

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


        //DB::insert('insert into users (email, votes) values (?, ?)', ['john@example.com', '0']);

        //DB::query('insert into users (username, email, password) values ("johndoe", "john@johndoe.com", "password")');


        /*
         $point = DB::select("SELECT POINT($data['lat'], $data['lng']) as point FROM `any_table_name_avilable_in_your_db` LIMIT 1");

         $data=array(
             "lat_field_name_table"=>$data['lat'],
             "lng_field_name_table"=>$data['lng'],
             "location"=>$point[0]->point
         );

         $checkinsert=DB::table('mytablename')->insert($data);*/


        /*

         DB::connection('network')->table('Maps')
          ->insert([
                'Name' => $maps['name'],
                'Gametype' => $maps['game'],
                ...
            ]);*/
    }


    public function scopeActive($query)
    {
        return $query->where('status', 1)->get();
    }


    public function scopeNewest($query)
    {

        return $query->where('created_at', '>', now()->subDays(30));


    }
}
