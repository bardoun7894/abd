<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $primaryKey = 'purchase_id';
    // public $incrementing = false;
    //protected $dateFormat = 'U';


    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }




    public function scopesel_purchase_list($query, $string, $page)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT purchase_name as name, purchase_id as id_no,purchase_id as id,purchase_respon
        from  purchase where  1=1  ";
        if ($string != "") {
            $sql = $sql . " and ( purchase_name LIKE '%$string%' or ssn LIKE '$string%')    ";
        }
        $sql = $sql . " order by purchase_id  desc LIMIT {$end}, {$start} ";
        $results = DB::select($sql);
        $count_rs_chk = count(DB::select($sql));
        $users = DB::select($sql);
        $users = json_decode(json_encode($users), true);
        $data = array();
        foreach ($users as $user) {
            $data[] = array(
                "id" => $user['id'],
                "ItemName" => $user['name'], "item_code" => ' المسؤول ' . ' ' . $user['purchase_respon'], "total_count" => $count_rs_chk
            );
        }
        return $data;
    }



    public function scopeserachspendcount($query, $purchase_no, $purchase_dt_from, $purchase_dt_to, $purchase_respon, $manager_id, $shop_id, $shops , $create_users)
    {
        $purchase_no = TRIM($purchase_no);
        $purchase_dt_from = TRIM($purchase_dt_from);
        $purchase_dt_to = TRIM($purchase_dt_to);
        $purchase_respon = TRIM($purchase_respon);
        $manager_id = TRIM($manager_id);
        $shop_id = TRIM($shop_id);
        $create_users = TRIM($create_users);


        $rs_stmt1 = " SELECT purchase_id FROM  purchase where  1=1  ";
        if ($purchase_no  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  purchase_no = '$purchase_no ' ";
        }

        if ($purchase_respon  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  purchase_respon like '%$purchase_respon%' ";
        }

        if ($create_users  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  create_user = '$create_users' ";
        }


        if ($purchase_dt_from  != "" and $purchase_dt_to  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  purchase_dt between '$purchase_dt_from' and '$purchase_dt_to'  ";
        }

        if ($purchase_dt_from  != "" and $purchase_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  purchase_dt >= '$purchase_dt_from'  ";
        }

        if ($purchase_dt_from  == "" and $purchase_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  purchase_dt <= '$purchase_dt_to'  ";
        }
        if ($shops == "on") {
            $rs_stmt1 = $rs_stmt1 . " and  manager_id  is NULL ";
            $rs_stmt1 = $rs_stmt1 . " and  shop_id is not NULL";

            if ($manager_id  != "") {
                $shops_list = "(";

                foreach( Manager::find($manager_id)->shops as $shop){
                    $shops_list .= $shop->shop_id ."," ; 
                }
                $shops_list .=  "0)";

                $rs_stmt1 = $rs_stmt1 . " and  shop_id in   ".$shops_list  ;
            }

        } else {

            $rs_stmt1 = $rs_stmt1 . " and  manager_id  is not NULL ";
            $rs_stmt1 = $rs_stmt1 . " and  shop_id is  NULL";
        }

        if ($manager_id  != "" and $shops != "on") {
            $rs_stmt1 = $rs_stmt1 . " and  manager_id = '$manager_id ' ";
        }
        if ($shop_id  != "") {
            $rs_stmt1 = $rs_stmt1 .  " and  shop_id = '$shop_id' ";
        }
        $results = count(DB::select($rs_stmt1));
        return  $results;
    }

    public function scopeserachspenddatarep($query, $purchase_id, $purchase_no, $purchase_dt_from, $purchase_dt_to, $purchase_respon, $manager_id,$shop_id,$shops)
    {
        $purchase_no = TRIM($purchase_no);
        $purchase_id = TRIM($purchase_id);
        $purchase_dt_from = TRIM($purchase_dt_from);
        $purchase_dt_to = TRIM($purchase_dt_to);
        $purchase_respon = TRIM($purchase_respon);
        $manager_id = TRIM($manager_id);


        $rs_stmt1 = " SELECT p.*,m.manager_name,u.name FROM  purchase p
            left join  manager m on p.manager_id=m.manager_id
            left join  users u on p.create_user=u.id
            where  1=1 ";
        if ($purchase_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_id = '$purchase_id ' ";
        }
        if ($purchase_no  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_no = '$purchase_no ' ";
        }

        if ($purchase_respon  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_respon like '%$purchase_respon%' ";
        }

        if ($shops == "on") {
            $rs_stmt1 = $rs_stmt1 . " and  p.manager_id  is NULL ";
            $rs_stmt1 = $rs_stmt1 . " and  p.shop_id is not NULL";

            if ($manager_id  != "") {
                $shops_list = "(";

                foreach( Manager::find($manager_id)->shops as $shop){
                    $shops_list .= $shop->shop_id ."," ; 
                }
                $shops_list .=  "0)";

                $rs_stmt1 = $rs_stmt1 . " and  p.shop_id in   ".$shops_list  ;
            }

        } else {

            $rs_stmt1 = $rs_stmt1 . " and  p.manager_id  is not NULL ";
            $rs_stmt1 = $rs_stmt1 . " and  p.shop_id is  NULL";
        }
        if ($purchase_dt_from  != "" and $purchase_dt_to  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_dt between '$purchase_dt_from' and '$purchase_dt_to'  ";
        }

        if ($purchase_dt_from  != "" and $purchase_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_dt >= '$purchase_dt_from'  ";
        }

        if ($purchase_dt_from  == "" and $purchase_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_dt <= '$purchase_dt_to'  ";
        }
        if ($manager_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.manager_id = '$manager_id ' ";
        }
        if ($shop_id  != "") {
            $rs_stmt1 = $rs_stmt1 .  " and  p.shop_id = '$shop_id' ";
        }
        $results = DB::select($rs_stmt1);
        return  $results;
    }


    public function scopeserachspenddata($query, $purchase_no, $purchase_dt_from, $purchase_dt_to, $purchase_respon, $manager_id, $shop_id, $shops , $create_users)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $purchase_no = TRIM($purchase_no);
        $purchase_dt_from = TRIM($purchase_dt_from);
        $purchase_dt_to = TRIM($purchase_dt_to);
        $purchase_respon = TRIM($purchase_respon);
        $manager_id = TRIM($manager_id);
        $create_users = TRIM($create_users);
        $shop_id = TRIM($shop_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder  = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord =  " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord =  " ORDER BY p.purchase_id DESC  ";
            }
        } else {
            $ord =  "    ";
        }

        $rs_stmt1 = " SELECT p.*,m.manager_name,u.name FROM  purchase p
            left join  manager m on p.manager_id=m.manager_id
            left join  users u on p.create_user=u.id
            where  1=1 ";
        if ($purchase_no  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_no = '$purchase_no ' ";
        }

        if ($purchase_respon  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_respon like '%$purchase_respon%' ";
        }

        if ($create_users  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.create_user = '$create_users' ";
        }

        if ($purchase_dt_from  != "" and $purchase_dt_to  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_dt between '$purchase_dt_from' and '$purchase_dt_to'  ";
        }

        if ($purchase_dt_from  != "" and $purchase_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_dt >= '$purchase_dt_from'  ";
        }

        if ($purchase_dt_from  == "" and $purchase_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.purchase_dt <= '$purchase_dt_to'  ";
        }
        if ($shops == "on") {
            $rs_stmt1 = $rs_stmt1 . " and  p.manager_id  is NULL ";
            $rs_stmt1 = $rs_stmt1 . " and  p.shop_id is not NULL";

            if ($manager_id  != "") {
                $shops_list = "(";

                foreach( Manager::find($manager_id)->shops as $shop){
                    $shops_list .= $shop->shop_id ."," ; 
                }
                $shops_list .=  "0)";

                $rs_stmt1 = $rs_stmt1 . " and  p.shop_id in   ".$shops_list  ;
            }
        } else {

            $rs_stmt1 = $rs_stmt1 . " and  p.manager_id  is not NULL ";
            $rs_stmt1 = $rs_stmt1 . " and  p.shop_id is  NULL";
        }
        if ($manager_id  != "" and $shops != "on") {
            $rs_stmt1 = $rs_stmt1 . " and  p.manager_id = '$manager_id ' ";
        }
        if ($shop_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id ' ";
        }


        $rs_stmt1 = $rs_stmt1  . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return  $results;
    }








    public function scopeserachremarkcount($query, $purchase_id)
    {
        $purchase_id = TRIM($purchase_id);
        $rs_stmt1 = " SELECT purchase_note_id FROM  purchase_note where is_deleted=0 and   1=1  ";
        if ($purchase_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  purchase_id = '$purchase_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return  $results;
    }


    public function scopeserachremarkdata($query, $purchase_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $purchase_id = TRIM($purchase_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder  = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord =  " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord =  " ORDER BY purchase_id DESC  ";
            }
        } else {
            $ord =  "    ";
        }

        $rs_stmt1 = " SELECT sn.*,u.name,n.note_type_name FROM   purchase_note sn
                            left join  users u on sn.create_note_user=u.id
                            left join  note_type n on sn.note_type_id=n.note_type_id

                            where sn.is_deleted=0 and   1=1 ";
        if ($purchase_id  != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.purchase_id = '$purchase_id ' ";
        }

        $rs_stmt1 = $rs_stmt1  . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return  $results;
    }
}
