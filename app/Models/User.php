<?php /*
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'emp_name',
        'firstname',
        'lastname',
        'phone',
        'email',
        'password',
        'username',
        'note',
        'emp_job',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // protected function emp_job(): Attribute

    // {

    //     return new Attribute(


    //         get: fn ($value, $attributes) => $attributes['emp_job'],
    //         set: fn ($value) => ['emp_job' => $value], // <<< CHANGE HERE

    //     );

    // }


    public function scopeserachrolecount($query)
    {
        $rs_stmt1 = " SELECT id FROM  role where  1=1  ";

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachroleddata($query)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT * FROM  role where  1=1    ";


        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);

        return $results;
    }


    public function scopeserachspendcount($query, $name, $email)
    {
        $name = TRIM($name);
        $email = TRIM($email);
        $rs_stmt1 = " SELECT id FROM  users where  1=1  ";
        if ($name != "") {
            $rs_stmt1 = $rs_stmt1 . " and  name like '%$name%' ";
        }

        if ($email != "") {
            $rs_stmt1 = $rs_stmt1 . " and  email = '$email ' ";
        }
        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachspenddata($query, $name, $email)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $name = TRIM($name);
        $email = TRIM($email);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT users.*,job_cat.j_c_name_ar FROM  users
                left join  job_cat  on  users.emp_job =job_cat.j_c_id

                where  1=1  ";
        if ($name != "") {
            $rs_stmt1 = $rs_stmt1 . " and  users.name like '%$name%' ";
        }

        if ($email != "") {
            $rs_stmt1 = $rs_stmt1 . " and  users.email = '$email ' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);

        return $results;
    }
}
