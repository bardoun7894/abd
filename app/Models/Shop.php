<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Perm;

class Shop extends Model
{
    use HasFactory;
    protected $fillabel = ['shop_name', 'establishment_number','ssn', 'work_place_id', 'note', 'doe', 'created_at', 'create_user', 'updated_at', 'updated_user'];
    protected $guarded = ['shop_id'];
    protected $primaryKey = 'shop_id';
    protected $table = "shop";

    // public $incrementing = false;
    //protected $dateFormat = 'U';


    public function municip()
    {
        return $this->hasOne(Municip::class, 'shop_id');
    }

    /**
     * Get the manager that owns the Shop
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manager()
    {
        return $this->belongsTo(Manager::class, 'manager_id');
    }


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->emp_job = Auth()->user()->emp_job;
        $this->user_id = Auth::user()->id;
    }


    public function expens()
    {
        return $this->hasMany(Expence::class, 'shop_id');
    }

    public function calculates()
    {
        return $this->hasMany(Calculate::class, 'shop_id');
    }


    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }




    public function scopesel_shop_list($query, $string, $page)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT shop.shop_name as name, shop.shop_id as id_no,shop.shop_id as id,shop.shop_respon,shop_municip.municip_no as municip_no
        from  shop

        left join shop_municip  on shop.shop_id=shop_municip.shop_id
 ";
        if ($this->emp_job != 1) {
            $sql = $sql . "    join workers_manager wm on shop.manager_id =wm.manager_id and  wm.user_id=$this->user_id ";
        }
        $sql = $sql . " where  1=1 ";
        if ($string != "") {
            $sql = $sql . " and ( shop.shop_name LIKE '%$string%' or  shop_municip.municip_no LIKE '%$string%')    ";
        }
        $sql = $sql . "group by shop.shop_id  order by shop.shop_id  desc LIMIT {$end}, {$start} ";
        $results = DB::select($sql);
        $count_rs_chk = count(DB::select($sql));
        $users = DB::select($sql);
        $users = json_decode(json_encode($users), true);
        $data = array();
        foreach ($users as $user) {
            if ($user['municip_no'] != '') {
                $muni_no = ' رخصة البلدية ' . ' ' . $user['municip_no'];
            } else {
                $muni_no = '';
            }
            $data[] = array(
                "id" => $user['id'],
                "ItemName" => $user['name'], "item_code" => $muni_no, "total_count" => $count_rs_chk
            );
        }
        return $data;
    }












    public function scopeserachhistorycount($query, $shop_id)
    {
        $shop_id = TRIM($shop_id);
        $rs_stmt1 = " SELECT nh.shop_note_history_id  FROM  shop_note_history nh
         join shop_note sn on nh.shop_note_id=sn.shop_note_id
         where   1=1  ";
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.shop_id = '$shop_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachhistorydata($query, $shop_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $shop_id = TRIM($shop_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY shop_note_id ,note_type_id  DESC  ";
            }
        } else {
            $ord = "    ";
        }
        $rs_stmt1 = " SELECT nh.*,u.name,n.note_type_name,n2.note_type_name as note_type_name_old,sn.shop_note_id  FROM
shop_note_history nh
 join shop_note sn on nh.shop_note_id=sn.shop_note_id
left join  users u on nh.change_user =u.id
left join  note_type n on nh.note_type_id=n.note_type_id
left join  note_type n2 on nh.old_note_type_id =n2.note_type_id
                    where    1=1 ";
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.shop_id = '$shop_id ' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        if (isset($b) and isset($a) and $b !="" and $a!="")
            $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }





    public function scopeshopreport($query, $shop_id, $shop_name, $shop_mobile, $manager_id, $city_id, $comme_no, $municip_no, $rentpay_price)
    {
        $shop_name = TRIM($shop_name);
        $shop_mobile = TRIM($shop_mobile);
        $manager_id = TRIM($manager_id);
        $city_id = TRIM($city_id);
        $comme_no = TRIM($comme_no);
        $municip_no = TRIM($municip_no);
        $shop_id = TRIM($shop_id);
        $rentpay_price = TRIM($rentpay_price);

        $rs_stmt1 = " SELECT sh.*,m.manager_name,c.city_name,u.name,sherp.rentpay_dt,sherp.rentpay_price,
            sm.municip_no,sm.municip_sdt,sm.municip_edt,
            sd.defence_no,sd.defence_sdt,sd.defence_edt,
            shel.health_no,shel.health_edt,

            CASE
    WHEN  sm.municip_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
    WHEN  sm.municip_edt  <=CURDATE() THEN '2'
    WHEN  sm.municip_edt is null THEN '4'
    ELSE '1'
    END
    as sm_desc,
    CASE
    WHEN  shel.health_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
    WHEN  shel.health_edt  <=CURDATE() THEN '2'
    WHEN  shel.health_edt is null THEN '4'
    ELSE '1'
    END
    as shel_desc,
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
            left join  shop_health shel on sh.shop_id=shel.shop_id
            left join   shop_rentpay sherp on sh.shop_id=sherp.shop_id and (sherp.rentpay_dt BETWEEN now() and DATE_ADD(CURDATE(), INTERVAL 30 DAY)  or date(now())<=date(rentpay_dt) )


       ";
        if ($this->emp_job != 1) {
            $rs_stmt1 = $rs_stmt1 . "
            join workers_manager wm on sh.manager_id =wm.manager_id and  wm.user_id=$this->user_id";
        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 ";


        if ($this->emp_job != 1) {
            if (Perm::get_function_access(74)) {
                $rs_stmt1 = $rs_stmt1 . " and  sh.create_user = $this->user_id ";
            }
        }

        if ($rentpay_price  != "") {
            if ($rentpay_price  == "0") {
                $rs_stmt1 = $rs_stmt1 . " and  sherp.rentpay_price is null ";
            } else  if ($rentpay_price  == "1") {

                $rs_stmt1 = $rs_stmt1 . " and  sherp.rentpay_price is not null ";
            }
        }

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












    public function scopeserachspendcount($query, $shop_name, $shop_mobile, $manager_id, $city_id, $comme_no, $municip_no, $rentpay_price, $order_date, $comme_month, $comme_year, $municip_month, $municip_year, $rentpay_month, $rentpay_year)
    {
        $shop_name = TRIM($shop_name);
        $shop_mobile = TRIM($shop_mobile);
        $manager_id = TRIM($manager_id);
        $city_id = TRIM($city_id);
        $comme_no = TRIM($comme_no);
        $municip_no = TRIM($municip_no);
        $rentpay_price = TRIM($rentpay_price);

        $rs_stmt1 = " SELECT sh.shop_id FROM  shop sh
        left join  shop_municip sm on sh.shop_id=sm.shop_id
            left join  shop_comme sc on sh.shop_id=sc.shop_id
            left join   shop_rentpay sherp on sh.shop_id=sherp.shop_id and (sherp.rentpay_dt BETWEEN now() and DATE_ADD(CURDATE(), INTERVAL 30 DAY)  or date(now())<=date(rentpay_dt) )

          ";

        if ($this->emp_job != 1) {
            $rs_stmt1 = $rs_stmt1 . "
            join workers_manager wm on sh.manager_id =wm.manager_id and  wm.user_id=$this->user_id";
        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 ";


        if ($this->emp_job != 1) {
            if (Perm::get_function_access(74)) {
                $rs_stmt1 = $rs_stmt1 . " and  sh.create_user = $this->user_id ";
            }
        }
        if ($rentpay_price  != "") {
            if ($rentpay_price  == "0") {
                $rs_stmt1 = $rs_stmt1 . " and  sherp.rentpay_price is null ";
            } else  if ($rentpay_price  == "1") {
                $rs_stmt1 = $rs_stmt1 . " and  sherp.rentpay_price is not null ";
            }
        }


        if ($comme_month != '') {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(sc.comme_edt) = '$comme_month ' ";
        }
        if ($comme_year != '') {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(sc.comme_edt) = '$comme_year  ' ";
        }

        if ($municip_month != '') {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(sc.comme_edt) = '$municip_month ' ";
        }
        if ($municip_year != '') {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(sc.comme_edt) = '$municip_year  ' ";
        }

        if ($rentpay_month != '') {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(sherp.rentpay_dt) = '$rentpay_month ' ";
        }
        if ($rentpay_year != '') {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(sherp.rentpay_dt) = '$rentpay_year  ' ";
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


    public function scopeserachspenddata($query, $shop_name, $shop_mobile, $manager_id, $city_id, $comme_no, $municip_no, $rentpay_price, $order_date, $comme_month, $comme_year, $municip_month, $municip_year, $rentpay_month, $rentpay_year)
    {
        $a = $_POST['length'] ?? "";
        $b = $_POST['start'] ?? "";
        $shop_name = TRIM($shop_name);
        $shop_mobile = TRIM($shop_mobile);
        $manager_id = TRIM($manager_id);
        $city_id = TRIM($city_id);
        $comme_no = TRIM($comme_no);
        $municip_no = TRIM($municip_no);
        $rentpay_price = TRIM($rentpay_price);

        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder  = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord =  " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord =  " ORDER BY shop_id DESC  ";
            }
        } else {
            $ord =  "    ";
        }

        $rs_stmt1 = " SELECT sh.*,m.manager_name,c.city_name,u.name,sherp.rentpay_dt,sherp.rentpay_price,
            sm.municip_no,sm.municip_sdt,sm.municip_edt,
            sd.defence_no,sd.defence_sdt,sd.defence_edt,
            shel.health_no,shel.health_edt,

CASE
    WHEN  sm.municip_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
    WHEN  sm.municip_edt  <=CURDATE() THEN '2'
    WHEN  sm.municip_edt is null THEN '4'
    ELSE '1'
    END
    as sm_desc,
    CASE
    WHEN  shel.health_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
    WHEN  shel.health_edt  <=CURDATE() THEN '2'
    WHEN  shel.health_edt is null THEN '4'
    ELSE '1'
    END
    as shel_desc,
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
            left join  shop_health shel on sh.shop_id=shel.shop_id
            left join   shop_rentpay sherp on sh.shop_id=sherp.shop_id and (sherp.rentpay_dt BETWEEN now() and DATE_ADD(CURDATE(), INTERVAL 30 DAY)  or date(now())<=date(rentpay_dt) )

            ";
        if ($this->emp_job != 1) {
            $rs_stmt1 = $rs_stmt1 . "
            join workers_manager wm on sh.manager_id =wm.manager_id and  wm.user_id=$this->user_id";
        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 ";



        if ($order_date == "comme_date") {
            // السجل التجاري
            $ord = " ORDER BY sc.comme_edt   ";
        } elseif ($order_date == "rent_edt") {
            // تاريخ انتهاء عقد الايجار
            $ord = " ORDER BY sherp.rentpay_dt   ";
        } elseif ($order_date == "municip_date") {
            //تاريخ انتهاء رخصة البلدية
            $ord = " ORDER BY sm.municip_edt   ";
        }
        if ($comme_month != '') {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(sc.comme_edt) = '$comme_month ' ";
        }
        if ($comme_year != '') {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(sc.comme_edt) = '$comme_year  ' ";
        }

        if ($municip_month != '') {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(sc.comme_edt) = '$municip_month ' ";
        }
        if ($municip_year != '') {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(sc.comme_edt) = '$municip_year  ' ";
        }

        if ($rentpay_month != '') {
            $rs_stmt1 = $rs_stmt1 . " and  MONTH(sherp.rentpay_dt) = '$rentpay_month ' ";
        }
        if ($rentpay_year != '') {
            $rs_stmt1 = $rs_stmt1 . " and  YEAR(sherp.rentpay_dt) = '$rentpay_year  ' ";
        }


        if ($this->emp_job != 1) {
            if (Perm::get_function_access(74)) {
                $rs_stmt1 = $rs_stmt1 . " and  sh.create_user = $this->user_id ";
            }
        }
        if ($rentpay_price  != "") {
            if ($rentpay_price  == "0") {
                $rs_stmt1 = $rs_stmt1 . " and  sherp.rentpay_price is null ";
            } else  if ($rentpay_price  == "1") {

                $rs_stmt1 = $rs_stmt1 . " and  sherp.rentpay_price is not null ";
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

        $rs_stmt1 = $rs_stmt1  . $ord;
        if (isset($b) and isset($a) and $b !="" and $a!="")
            $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return  $results;
    }




    public function scopeserachspenddata_________________($query, $shop_name, $shop_mobile, $manager_id, $city_id, $comme_no, $municip_no)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $shop_name = TRIM($shop_name);
        $shop_mobile = TRIM($shop_mobile);
        $manager_id = TRIM($manager_id);
        $city_id = TRIM($city_id);
        $comme_no = TRIM($comme_no);
        $municip_no = TRIM($municip_no);

        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder  = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord =  " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord =  " ORDER BY shop_id DESC  ";
            }
        } else {
            $ord =  "    ";
        }

        $rs_stmt1 = " SELECT sh.*,m.manager_name,c.city_name,u.name,MIN(rentpay_dt) as rentpay_dt ,
                        sm.municip_no,sm.municip_sdt,sm.municip_edt,
                        sd.defence_no,sd.defence_sdt,sd.defence_edt,
                        shel.health_no,shel.health_edt,

            CASE
                WHEN  sm.municip_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                WHEN  sm.municip_edt  <=CURDATE() THEN '2'
                WHEN  sm.municip_edt is null THEN '4'
                ELSE '1'
                END
                as sm_desc,
                CASE
                WHEN  shel.health_edt BETWEEN CURDATE() and DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN '3'
                WHEN  shel.health_edt  <=CURDATE() THEN '2'
                WHEN  shel.health_edt is null THEN '4'
                ELSE '1'
                END
                as shel_desc,

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
                        left join  shop_health shel on sh.shop_id=shel.shop_id
                        left join   shop_rentpay sherp on sh.shop_id=sherp.shop_id and date(now())<date(rentpay_dt)


                        ";
        if ($this->emp_job != 1) {
            $rs_stmt1 = $rs_stmt1 . "
                        join workers_manager wm on sh.manager_id =wm.manager_id and  wm.user_id=$this->user_id";
        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 ";


        if ($this->emp_job != 1) {
            if (Perm::get_function_access(74)) {
                $rs_stmt1 = $rs_stmt1 . " and  sh.create_user = $this->user_id ";
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


        //$rs_stmt1 = $rs_stmt1 . "  HAVING  DATE(sherp.rentpay_dt) between DATE(NOW()) and DATE(MIN(sherp.rentpay_dt)) ";


        $rs_stmt1 = $rs_stmt1  . $ord;
        if (isset($b) and isset($a) and $b !="" and $a!="")
            $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return  $results;
    }






    public function scopeserachremarkcount($query, $shop_id)
    {
        $shop_id = TRIM($shop_id);
        $rs_stmt1 = " SELECT shop_note_id FROM  shop_note where is_deleted=0  and   1=1  ";
        if ($shop_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  shop_id = '$shop_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return  $results;
    }


    public function scopeserachremarkdata($query, $shop_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $shop_id = TRIM($shop_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder  = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord =  " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord =  " ORDER BY shop_id DESC  ";
            }
        } else {
            $ord =  "    ";
        }

        $rs_stmt1 = " SELECT sn.*,u.name,n.note_type_name FROM   shop_note sn
                            left join  users u on sn.create_note_user=u.id
                            left join  note_type n on sn.note_type_id=n.note_type_id

                            where sn.is_deleted=0  and   1=1 ";
        if ($shop_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.shop_id = '$shop_id ' ";
        }

        $rs_stmt1 = $rs_stmt1  . $ord;
        if (isset($b) and isset($a) and $b !="" and $a!="")
            $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return  $results;
    }



    public function scopeserachrentpaycount($query, $shop_id)
    {
        $shop_id = TRIM($shop_id);
        $rs_stmt1 = " SELECT rentpay_id  FROM  shop_rentpay where   1=1  ";
        if ($shop_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  shop_id = '$shop_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return  $results;
    }


    public function scopeserachrentpaydata($query, $shop_id)
    {
        $a = $_POST['length'] ?? "";
        $b = $_POST['start'] ?? "";
        $shop_id = TRIM($shop_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder  = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord =  " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord =  " ORDER BY shop_id DESC  ";
            }
        } else {
            $ord =  "    ";
        }

        $rs_stmt1 = " SELECT sn.* FROM   shop_rentpay sn

                                            where   1=1 ";
        if ($shop_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.shop_id = '$shop_id ' ";
        }

        $rs_stmt1 = $rs_stmt1  . $ord;
        if (isset($b) and isset($a) and $b !="" and $a!="")
            $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return  $results;
    }
}
