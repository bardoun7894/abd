<?php

namespace App\Models;

use App\Helpers\Perm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class Expense extends Model

{
    use HasFactory;

    protected $fillabel = ['expense_name', 'ssn', 'work_place_id', 'note', 'doe', 'created_at', 'create_user', 'updated_at', 'updated_user', 'update_user'];
    protected $guarded = ['expense_id'];
    protected $primaryKey = 'expense_id';
    protected $table = "expense";

    // public $incrementing = false;
//protected $dateFormat = 'U';


public function __construct(array $attributes = [])
{
    parent::__construct($attributes);
    $this->emp_job = Auth()->user()->emp_job;
    $this->user_id=Auth::user()->id;
}
/**
 * Get the manger associated with the Expense
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasOne
 */
public function manager()
{
    return $this->belongsTo(Manager::class, 'manager_id');
}

    function expense_categoty() {
        // expense_categoty_id
        return $this->belongsTo(Expense_categoty::class, 'expense_categoty_id');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }


    public function scopesel_expense_list($query, $string, $page)
    {
        $resultCount = 50;
        $end = ($page - 1) * $resultCount;
        $start = $end + $resultCount;
        $sql = "SELECT expense_name as name, expense_id as id_no,expense_id as id,expense_respon
        from  expense where  1=1  ";
        if ($string != "") {
            $sql = $sql . " and ( expense_name LIKE '%$string%' or ssn LIKE '$string%')    ";
        }
        $sql = $sql . " order by expense_id  desc LIMIT {$end}, {$start} ";
        $results = DB::select($sql);
        $count_rs_chk = count(DB::select($sql));
        $users = DB::select($sql);
        $users = json_decode(json_encode($users), true);
        $data = array();
        foreach ($users as $user) {
            $data[] = array(
                "id" => $user['id'],
                "ItemName" => $user['name']
            , "item_code" => ' المسؤول ' . ' ' . $user['expense_respon']
            , "total_count" => $count_rs_chk);
        }
        return $data;
    }
    public function scopeserachspendcountall($query, $expense_type_id, $expense_categoty_id, $expense_month_desc, $manager_id, $worker_id, $shop_id,$type,$det_calculate_month_remain)
    {
        $expense_type_id = TRIM($expense_type_id);
        $expense_categoty_id = TRIM($expense_categoty_id);
        $expense_month_desc = TRIM($expense_month_desc);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);

        $rs_stmt1 = "

        select type from
        (";

        $rs_stmt1 = $rs_stmt1 . "

        SELECT 1 as type,
        ex.expense_id,ex.shop_id,ex.worker_id,ex.expense_month_desc,ex.expense_month_y,ex.expense_month_m,ex.expense_price,ex.manager_id,ex.expensefile,ex.note,ex.create_user,ex.created_at,ex.update_user,ex.updated_at,ex.expense_type_id,ex.expense_categoty_id,
        s.shop_name,u.name,m.manager_name,
        '' as calculate_detail_id,
        ex.expense_price as det_calculate_month_pay,
        0 as det_calculate_month_remain,
        1 as count_statement,
        ex.expense_price as sum_det_calculate_month_pay,
       0 as remain_db

                 FROM  expense ex
                    left join  manager m on ex.manager_id=m.manager_id
                    left join  users u on ex.create_user=u.id
                    left join   expense_type et on ex.expense_type_id =et.expense_type_id
                    left join   expense_categoty ec on ex.expense_categoty_id=ec.expense_categoty_id
                    left join   shop s on ex.shop_id=s.shop_id
                    left join   workers w on ex.worker_id=w.worker_id
                    left join  shop_municip sm on ex.shop_id=sm.shop_id


                    where  1=1 ";



        if ($expense_type_id != "") {
             $rs_stmt1 = $rs_stmt1 . " and  ex.expense_type_id = '$expense_type_id ' ";
        }
        if ($expense_month_desc != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_month_desc = '$expense_month_desc ' ";
        }
        if ($expense_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_categoty_id = '$expense_categoty_id ' ";
        }

         if ($worker_id != "") {
             $rs_stmt1 = $rs_stmt1 . " and  ex.worker_id = '$worker_id ' ";
         }

         if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.manager_id = '$manager_id ' ";
        }






                    if ($expense_type_id == "1" || $expense_type_id == "") {

                    $rs_stmt1 = $rs_stmt1 . "
                    union

        SELECT 2 as type,
        p.calculate_id,p.shop_id,'' as worker_id,p.calculate_month_desc,p.calculate_month_y,p.calculate_month_m,p.calculate_month_val,'' as manager_id,'' as expensefile,p.note,p.create_user,p.created_at,p.updated_user,p.updated_at,'' as expense_type_id,'' as expense_categoty_id
        ,sh.shop_name,u.name,m.manager_name,
                cd.calculate_detail_id as calculate_detail_id,
                cd.calculate_month_pay as det_calculate_month_pay,
                cd.calculate_month_remain as det_calculate_month_remain,
        COALESCE(count(cd.calculate_detail_id), 0) as count_statement,
        COALESCE(sum(cd.calculate_month_pay), 0) as sum_det_calculate_month_pay,
        p.calculate_month_val-  COALESCE(sum(cd.calculate_month_pay), 0)  as remain_db

                 FROM   calculate p
                 left join  calculate_detail cd on p.calculate_id=cd.calculate_id
                 join  shop sh on p.shop_id=sh.shop_id
                 left join  users u on p.create_user=u.id
                 left join  shop s on p.shop_id=s.shop_id
        left join  manager m on s.manager_id=m.manager_id
        left join  shop_municip sm on sh.shop_id=sm.shop_id
        where  1=1 and p.is_deleted=0";



        if ($shop_id != "") {
           $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id ' ";
           }
           if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.manager_id = '$manager_id ' ";
        }
        if ($expense_month_desc != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_desc = '$expense_month_desc ' ";
        }
      $rs_stmt1 = $rs_stmt1 . "   group by p.calculate_id         ";



   }



                    if ($expense_type_id == "3" || $expense_type_id == "") {


                     $rs_stmt1 = $rs_stmt1 . "
                     union

                    SELECT 3 as type,

        p.expense_id,'' as shop_id,p.worker_id,p.expense_month_desc,p.expense_month_y,p.expense_month_m,p.expense_price,'' as manager_id,'' as expensefile,p.note,p.create_user,p.created_at,p.updated_user,p.updated_at,'' as expense_type_id,'' as expense_categoty_id,
        sh.worker_name,u.name,m.manager_name,
                cd.expense_detail_id as expense_detail_id,
                cd.expense_month_pay as det_expense_month_pay,
                cd.expense_month_remain as det_expense_month_remain,
        COALESCE(count(cd.expense_detail_id), 0) as count_statement,
        COALESCE(sum(cd.expense_month_pay), 0) as sum_det_expense_month_pay,
        p.expense_price-  COALESCE(sum(cd.expense_month_pay), 0)  as remain_db

                 FROM   expense p
                 left join  expense_detail cd on p.expense_id=cd.expense_id
                 join  workers sh on p.worker_id=sh.worker_id
                 left join  users u on p.create_user=u.id
                 left join  workers w on p.worker_id=w.worker_id
        left join  manager m on w.manager_id=m.manager_id
        where  1=1 and p.is_deleted=0";


        if ($worker_id != "") {
           $rs_stmt1 = $rs_stmt1 . " and  p.worker_id = '$worker_id ' ";
       }
       if ($manager_id != "") {
        $rs_stmt1 = $rs_stmt1 . " and  m.manager_id = '$manager_id ' ";
    }
    if ($expense_month_desc != "") {
        $rs_stmt1 = $rs_stmt1 . " and  p.expense_month_desc = '$expense_month_desc ' ";
    }
       $rs_stmt1 = $rs_stmt1 . "  group by p.expense_id

              ";
           }



         $rs_stmt1 = $rs_stmt1 . "
         ) c where 1=1  ";


         if ($type != "") {
            $rs_stmt1 = $rs_stmt1 . " and  c.type = '$type' ";
        }

        if ($det_calculate_month_remain != "") {
            if($det_calculate_month_remain==0){
                $rs_stmt1 = $rs_stmt1 . " and c.remain_db=0 ";
            }
            else{
                $rs_stmt1 = $rs_stmt1 . " and c.remain_db!=0 ";
            }
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }

    public function scopeserachspenddataall($query, $expense_type_id, $expense_categoty_id, $expense_month_desc, $manager_id, $worker_id, $shop_id,$type,$det_calculate_month_remain)
    {
        $a = $_POST['length'] ??"";
        $b = $_POST['start'] ??"";
        $expense_type_id = TRIM($expense_type_id);
        $expense_categoty_id = TRIM($expense_categoty_id);
        $expense_month_desc = TRIM($expense_month_desc);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY shop_id d DESC  ";
            }

        } else {
            $ord = "    ";
        }
        $rs_stmt1 = "

        select * from
        (";

            $rs_stmt1 = $rs_stmt1 . "
        SELECT 1 as type,'مصاريف تشغيلية ' as type_desc,
        ex.expense_id,ex.shop_id,ex.worker_id,ex.expense_month_desc,ex.expense_month_y,ex.expense_month_m,ex.expense_price,ex.manager_id,ex.expensefile,ex.note,ex.create_user,ex.created_at,ex.update_user,ex.updated_at,ex.expense_type_id,ex.expense_categoty_id,
        s.shop_name,w.worker_name,u.name,m.manager_name,
        '' as calculate_detail_id,
        ex.expense_price as det_calculate_month_pay,
        0 as det_calculate_month_remain,
        1 as count_statement,
        ex.expense_price as sum_det_calculate_month_pay,
0 as remain_db

        ,w.ssn,sm.municip_no,sm.municip_sdt,sm.municip_edt,et.expense_type_name,ec.expense_categoty_name
                 FROM  expense ex
                    left join  manager m on ex.manager_id=m.manager_id
                    left join  users u on ex.create_user=u.id
                    left join   expense_type et on ex.expense_type_id =et.expense_type_id
                    left join   expense_categoty ec on ex.expense_categoty_id=ec.expense_categoty_id
                    left join   shop s on ex.shop_id=s.shop_id
                    left join   workers w on ex.worker_id=w.worker_id
                    left join  shop_municip sm on ex.shop_id=sm.shop_id


                    where  1=1 ";



                    if ($expense_type_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  ex.expense_type_id = '$expense_type_id ' ";
                   }

                   if ($expense_categoty_id != "") {
                       $rs_stmt1 = $rs_stmt1 . " and  ex.expense_categoty_id = '$expense_categoty_id ' ";
                   }
                   if ($expense_month_desc != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  ex.expense_month_desc = '$expense_month_desc ' ";
                }
                    if ($worker_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  ex.worker_id = '$worker_id ' ";
                    }
                    if ($shop_id != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  ex.shop_id = '$shop_id ' ";
                    }
                    if ($manager_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  ex.manager_id = '$manager_id ' ";
                    }


                if ($expense_type_id == "1" || $expense_type_id == "") {

  $rs_stmt1 = $rs_stmt1 . "
                    union

        SELECT 2 as type,'مصاريف محل' as type_desc,
        p.calculate_id,p.shop_id,'' as worker_id,p.calculate_month_desc,p.calculate_month_y,p.calculate_month_m,p.calculate_month_val,m.manager_id ,'' as expensefile,p.note,p.create_user,p.created_at,p.updated_user,p.updated_at,'' as expense_type_id,'' as expense_categoty_id
        ,sh.shop_name,'' as worker_name,u.name,m.manager_name,
                cd.calculate_detail_id as calculate_detail_id,
                cd.calculate_month_pay as det_calculate_month_pay,
                cd.calculate_month_remain as det_calculate_month_remain,
        COALESCE(count(cd.calculate_detail_id), 0) as count_statement,
        COALESCE(sum(cd.calculate_month_pay), 0) as sum_det_calculate_month_pay,

        p.calculate_month_val-  COALESCE(sum(cd.calculate_month_pay), 0)  as remain_db

        ,'' as ssn,sm.municip_no,sm.municip_sdt,sm.municip_edt,'' as expense_type_name,'' as expense_categoty_name
                 FROM   calculate p
                 left join  calculate_detail cd on p.calculate_id=cd.calculate_id
                 join  shop sh on p.shop_id=sh.shop_id
                 left join  users u on p.create_user=u.id
                 left join  shop s on p.shop_id=s.shop_id
        left join  manager m on s.manager_id=m.manager_id
        left join  shop_municip sm on sh.shop_id=sm.shop_id
                     where  1=1 and p.is_deleted=0";



                     if ($shop_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id ' ";
                        }
                        if ($manager_id != "") {
                            $rs_stmt1 = $rs_stmt1 . " and  m.manager_id = '$manager_id ' ";
                        }
                        if ($expense_month_desc != "") {
                            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_desc = '$expense_month_desc ' ";
                        }
                   $rs_stmt1 = $rs_stmt1 . "   group by p.calculate_id

                     ";


                }

                if ($expense_type_id == "3" || $expense_type_id == "") {

  $rs_stmt1 = $rs_stmt1 . " union

                    SELECT 3 as type, 'مصاريف عمال' as type_desc,

        p.expense_id,'' as shop_id,p.worker_id,p.expense_month_desc,p.expense_month_y,p.expense_month_m,p.expense_price,m.manager_id,'' as expensefile,p.note,p.create_user,p.created_at,p.updated_user,p.updated_at,'' as expense_type_id,'' as expense_categoty_id,
        '' as shop_name,w.worker_name,u.name,m.manager_name,
                cd.expense_detail_id as expense_detail_id,
                cd.expense_month_pay as det_expense_month_pay,
                cd.expense_month_remain as det_expense_month_remain,
        COALESCE(count(cd.expense_detail_id), 0) as count_statement,
        COALESCE(sum(cd.expense_month_pay), 0) as sum_det_calculate_month_pay,
        p.expense_price-  COALESCE(sum(cd.expense_month_pay), 0)  as remain_db

        ,w.ssn,'' as municip_no,'' as municip_sdt,'' as municip_edt,'' as expense_type_name,'' as expense_categoty_name
                 FROM   expense p
                 left join  expense_detail cd on p.expense_id=cd.expense_id
                 join  workers sh on p.worker_id=sh.worker_id
                 left join  users u on p.create_user=u.id
                 left join  workers w on p.worker_id=w.worker_id
        left join  manager m on w.manager_id=m.manager_id
         where  1=1 and p.is_deleted=0";

         if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.worker_id = '$worker_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.manager_id = '$manager_id ' ";
        }
        if ($expense_month_desc != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.expense_month_desc = '$expense_month_desc ' ";
        }
        $rs_stmt1 = $rs_stmt1 . "  group by p.expense_id

               ";
            }



                $rs_stmt1 = $rs_stmt1 . "        ) c
where 1=1

                    ";


                    if ($type != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  c.type = '$type' ";
                    }






          if ($expense_type_id == "1" || $expense_type_id == "") {
        $ord = " ORDER BY shop_id  DESC,type asc  ";
                    }

                    if ($expense_type_id == "3" || $expense_type_id == "") {
                        $ord = " ORDER BY type desc,worker_id  DESC  ";
                                    }
        $rs_stmt1 = $rs_stmt1 . $ord;

       // $rs_stmt1 = $rs_stmt1 . "  group by shop_id";

        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        try {
            $results = DB::select($rs_stmt1);

        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
        return $results;
    }



    public function scopeserachspenddataarepll($query, $expense_type_id, $expense_categoty_id, $expense_month_desc, $manager_id, $worker_id, $shop_id,$type,$det_calculate_month_remain)
    {
       // echo "ddddddd". $expense_type_id;

        $expense_type_id = TRIM($expense_type_id);
        $expense_categoty_id = TRIM($expense_categoty_id);
        $expense_month_desc = TRIM($expense_month_desc);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY shop_id d DESC  ";
            }

        } else {
            $ord = "    ";
        }
        $rs_stmt1 = "

        select * from
        (";

            $rs_stmt1 = $rs_stmt1 . "
        SELECT 1 as type,'مصاريف تشغيلية ' as type_desc,
        ex.expense_id,ex.shop_id,ex.worker_id,ex.expense_month_desc,ex.expense_month_y,ex.expense_month_m,ex.expense_price,ex.manager_id,ex.expensefile,ex.note,ex.create_user,ex.created_at,ex.update_user,ex.updated_at,ex.expense_type_id,ex.expense_categoty_id,
        s.shop_name,w.worker_name,u.name,m.manager_name,
        '' as calculate_detail_id,
        ex.expense_price as det_calculate_month_pay,
        0 as det_calculate_month_remain,
        1 as count_statement,
        ex.expense_price as sum_det_calculate_month_pay,
0 as remain_db

        ,w.ssn,sm.municip_no,sm.municip_sdt,sm.municip_edt,et.expense_type_name,ec.expense_categoty_name
                 FROM  expense ex
                    left join  manager m on ex.manager_id=m.manager_id
                    left join  users u on ex.create_user=u.id
                    left join   expense_type et on ex.expense_type_id =et.expense_type_id
                    left join   expense_categoty ec on ex.expense_categoty_id=ec.expense_categoty_id
                    left join   shop s on ex.shop_id=s.shop_id
                    left join   workers w on ex.worker_id=w.worker_id
                    left join  shop_municip sm on ex.shop_id=sm.shop_id


                    where  1=1 ";



                    if ($expense_type_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  ex.expense_type_id = '$expense_type_id ' ";
                   }

                   if ($expense_categoty_id != "") {
                       $rs_stmt1 = $rs_stmt1 . " and  ex.expense_categoty_id = '$expense_categoty_id ' ";
                   }
                   if ($expense_month_desc != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  ex.expense_month_desc = '$expense_month_desc ' ";
                }
                    if ($worker_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  ex.worker_id = '$worker_id ' ";
                    }
                    if ($shop_id != "") {
                    $rs_stmt1 = $rs_stmt1 . " and  ex.shop_id = '$shop_id ' ";
                    }
                    if ($manager_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  ex.manager_id = '$manager_id ' ";
                    }


                if ($expense_type_id == "1" || $expense_type_id == "") {

  $rs_stmt1 = $rs_stmt1 . "
                    union

        SELECT 2 as type,'مصاريف محل' as type_desc,
        p.calculate_id,p.shop_id,'' as worker_id,p.calculate_month_desc,p.calculate_month_y,p.calculate_month_m,p.calculate_month_val,m.manager_id ,'' as expensefile,p.note,p.create_user,p.created_at,p.updated_user,p.updated_at,'' as expense_type_id,'' as expense_categoty_id
        ,sh.shop_name,'' as worker_name,u.name,m.manager_name,
                cd.calculate_detail_id as calculate_detail_id,
                cd.calculate_month_pay as det_calculate_month_pay,
                cd.calculate_month_remain as det_calculate_month_remain,
        COALESCE(count(cd.calculate_detail_id), 0) as count_statement,
        COALESCE(sum(cd.calculate_month_pay), 0) as sum_det_calculate_month_pay,

        p.calculate_month_val-  COALESCE(sum(cd.calculate_month_pay), 0)  as remain_db

        ,'' as ssn,sm.municip_no,sm.municip_sdt,sm.municip_edt,'' as expense_type_name,'' as expense_categoty_name
                 FROM   calculate p
                 left join  calculate_detail cd on p.calculate_id=cd.calculate_id
                 join  shop sh on p.shop_id=sh.shop_id
                 left join  users u on p.create_user=u.id
                 left join  shop s on p.shop_id=s.shop_id
        left join  manager m on s.manager_id=m.manager_id
        left join  shop_municip sm on sh.shop_id=sm.shop_id
                     where  1=1 and p.is_deleted=0";



                     if ($shop_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  p.shop_id = '$shop_id ' ";
                        }
                        if ($manager_id != "") {
                            $rs_stmt1 = $rs_stmt1 . " and  m.manager_id = '$manager_id ' ";
                        }
                        if ($expense_month_desc != "") {
                            $rs_stmt1 = $rs_stmt1 . " and  p.calculate_month_desc = '$expense_month_desc ' ";
                        }
                   $rs_stmt1 = $rs_stmt1 . "   group by p.calculate_id

                     ";


                }

                if ($expense_type_id == "3" || $expense_type_id == "") {

  $rs_stmt1 = $rs_stmt1 . " union

                    SELECT 3 as type, 'مصاريف عمال' as type_desc,

        p.expense_id,'' as shop_id,p.worker_id,p.expense_month_desc,p.expense_month_y,p.expense_month_m,p.expense_price,m.manager_id,'' as expensefile,p.note,p.create_user,p.created_at,p.updated_user,p.updated_at,'' as expense_type_id,'' as expense_categoty_id,
        '' as shop_name,w.worker_name,u.name,m.manager_name,
                cd.expense_detail_id as expense_detail_id,
                cd.expense_month_pay as det_expense_month_pay,
                cd.expense_month_remain as det_expense_month_remain,
        COALESCE(count(cd.expense_detail_id), 0) as count_statement,
        COALESCE(sum(cd.expense_month_pay), 0) as sum_det_calculate_month_pay,
        p.expense_price-  COALESCE(sum(cd.expense_month_pay), 0)  as remain_db

        ,w.ssn,'' as municip_no,'' as municip_sdt,'' as municip_edt,'' as expense_type_name,'' as expense_categoty_name
                 FROM   expense p
                 left join  expense_detail cd on p.expense_id=cd.expense_id
                 join  workers sh on p.worker_id=sh.worker_id
                 left join  users u on p.create_user=u.id
                 left join  workers w on p.worker_id=w.worker_id
        left join  manager m on w.manager_id=m.manager_id
         where  1=1 and p.is_deleted=0";

         if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.worker_id = '$worker_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  m.manager_id = '$manager_id ' ";
        }
        if ($expense_month_desc != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.expense_month_desc = '$expense_month_desc ' ";
        }
        $rs_stmt1 = $rs_stmt1 . "  group by p.expense_id

               ";
            }



                $rs_stmt1 = $rs_stmt1 . "        ) c
where 1=1

                    ";


                    if ($type != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  c.type = '$type' ";
                    }






          if ($expense_type_id == "1" || $expense_type_id == "") {
        $ord = " ORDER BY shop_id  DESC,type asc  ";
                    }

                    if ($expense_type_id == "3" || $expense_type_id == "") {
                        $ord = " ORDER BY type desc,worker_id  DESC  ";
                                    }
        $rs_stmt1 = $rs_stmt1 . $ord;


        $results = DB::select($rs_stmt1);
        return $results;
    }



    public function scopeserachspendcount__________($query, $expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id)
    {
        $expense_type_id = TRIM($expense_type_id);
        $expense_categoty_id = TRIM($expense_categoty_id);
        $expense_dt_from = TRIM($expense_dt_from);
        $expense_dt_to = TRIM($expense_dt_to);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);

        $rs_stmt1 = " SELECT expense_id FROM  expense where  1=1  ";
        if ($expense_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_type_id = '$expense_type_id ' ";
        }
        if ($expense_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_categoty_id = '$expense_categoty_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  manager_id = '$manager_id ' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  worker_id = '$worker_id ' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  shop_id = '$shop_id ' ";
        }
        if ($expense_dt_from != "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_dt between '$expense_dt_from' and '$expense_dt_to'  ";
        }

        if ($expense_dt_from != "" and $expense_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_dt >= '$expense_dt_from'  ";
        }

        if ($expense_dt_from == "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_dt <= '$expense_dt_to'  ";
        }
        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachspenddatarep($query, $expense_id,$expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id)
    {
        $expense_id = TRIM($expense_id);
        $expense_type_id = TRIM($expense_type_id);
        $expense_categoty_id = TRIM($expense_categoty_id);
        $expense_dt_from = TRIM($expense_dt_from);
        $expense_dt_to = TRIM($expense_dt_to);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);

        $rs_stmt1 = " SELECT ex.*,m.manager_name,u.name,et.expense_type_name,ec.expense_categoty_name,s.shop_name,w.worker_name FROM  expense ex
            left join  manager m on ex.manager_id=m.manager_id
            left join  users u on ex.create_user=u.id
            left join   expense_type et on ex.expense_type_id =et.expense_type_id
            left join   expense_categoty ec on ex.expense_categoty_id=ec.expense_categoty_id
            left join   shop s on ex.shop_id=s.shop_id
            left join   workers w on ex.worker_id=w.worker_id

            where  1=1 ";
                    if ($expense_id != "") {
                        $rs_stmt1 = $rs_stmt1 . " and  ex.expense_id = '$expense_id ' ";
                    }

        if ($expense_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_type_id = '$expense_type_id ' ";
        }
        if ($expense_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_categoty_id = '$expense_categoty_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.manager_id = '$manager_id ' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.worker_id = '$worker_id ' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.shop_id = '$shop_id ' ";
        }
        if ($expense_dt_from != "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt between '$expense_dt_from' and '$expense_dt_to'  ";
        }

        if ($expense_dt_from != "" and $expense_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt >= '$expense_dt_from'  ";
        }

        if ($expense_dt_from == "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt <= '$expense_dt_to'  ";
        }
        $results = DB::select($rs_stmt1);
        return $results;
    }










    public function scopeserachspenddata($query, $expense_type_id, $expense_categoty_id, $expense_dt_from, $expense_dt_to, $manager_id, $worker_id, $shop_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $expense_type_id = TRIM($expense_type_id);
        $expense_categoty_id = TRIM($expense_categoty_id);
        $expense_dt_from = TRIM($expense_dt_from);
        $expense_dt_to = TRIM($expense_dt_to);
        $manager_id = TRIM($manager_id);
        $worker_id = TRIM($worker_id);
        $shop_id = TRIM($shop_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY ex.expense_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT ex.*,m.manager_name,u.name,et.expense_type_name,ec.expense_categoty_name,s.shop_name,w.worker_name,w.ssn,sm.municip_no,sm.municip_sdt,sm.municip_edt
         FROM  expense ex
            left join  manager m on ex.manager_id=m.manager_id
            left join  users u on ex.create_user=u.id
            left join   expense_type et on ex.expense_type_id =et.expense_type_id
            left join   expense_categoty ec on ex.expense_categoty_id=ec.expense_categoty_id
            left join   shop s on ex.shop_id=s.shop_id
            left join   workers w on ex.worker_id=w.worker_id
            left join  shop_municip sm on ex.shop_id=sm.shop_id

            where  1=1 ";
        if ($expense_type_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_type_id = '$expense_type_id ' ";
        }
        if ($expense_categoty_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_categoty_id = '$expense_categoty_id ' ";
        }
        if ($manager_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.manager_id = '$manager_id ' ";
        }
        if ($worker_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.worker_id = '$worker_id ' ";
        }
        if ($shop_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.shop_id = '$shop_id ' ";
        }
        if ($expense_dt_from != "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt between '$expense_dt_from' and '$expense_dt_to'  ";
        }

        if ($expense_dt_from != "" and $expense_dt_to = "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt >= '$expense_dt_from'  ";
        }

        if ($expense_dt_from == "" and $expense_dt_to != "") {
            $rs_stmt1 = $rs_stmt1 . " and  ex.expense_dt <= '$expense_dt_to'  ";
        }
        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }


    public function scopeserachremarkcount($query, $expense_id)
    {
        $expense_id = TRIM($expense_id);
        $rs_stmt1 = " SELECT expense_note_id FROM  expense_note where is_deleted=0 and   1=1  ";
        if ($expense_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_id = '$expense_id ' ";
        }

        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    public function scopeserachremarkdata($query, $expense_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $expense_id = TRIM($expense_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY expense_id DESC  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT sn.*,u.name,n.note_type_name FROM   expense_note sn
                            left join  users u on sn.create_note_user=u.id
                            left join  note_type n on sn.note_type_id=n.note_type_id

                            where sn.is_deleted=0 and   1=1 ";
        if ($expense_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  sn.expense_id = '$expense_id ' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . "  limit $b,$a ";
        $results = DB::select($rs_stmt1);
        return $results;
    }


    public function scopeserachspendcountdet($query, $expense_id)
    {
        $expense_id = TRIM($expense_id);
        $rs_stmt1 = " SELECT p.expense_id

 FROM   expense p
          join  workers w on p.worker_id=w.worker_id

  ";
        if(  $this->emp_job!=1){
            $rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on w.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

        }
        // $rs_stmt1 = $rs_stmt1 . " where  1=1 and p.is_deleted=0 ";
        $rs_stmt1 = $rs_stmt1 . " where  1=1  ";

        if(  $this->emp_job!=1){
            if (Perm::get_function_access(73)) {
                $rs_stmt1 = $rs_stmt1 . " and  p.create_user = $this->user_id ";
            }
        }
        if ($expense_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  expense_id = '$expense_id' ";
        }


        $results = count(DB::select($rs_stmt1));
        return $results;
    }


    
    public function scopeserachspenddet($query, $expense_id)
    {
        $a = $_POST['length'];
        $b = $_POST['start'];
        $expense_id = TRIM($expense_id);
        if (isset($_POST['order'])) {
            $columnName = $_POST['order']['0']['column'];
            $columnSortOrder = $_POST['order']['0']['dir'];
            if ($columnName != 0) {
                $ord = " order by  " . $columnName . " " . $columnSortOrder;
            } else {
                $ord = " ORDER BY cd.expense_detail_id desc  ";
            }

        } else {
            $ord = "    ";
        }

        $rs_stmt1 = " SELECT p.*,w.worker_name,u.name,

        cd.expense_detail_id as expense_detail_id,
        cd.expense_month_pay as det_expense_month_pay,
        cd.expense_month_remain as det_expense_month_remain,
                cd.expense_month_val as det_expense_month_val,

        cd.note as det_note,
        u2.name as det_create_user_name,
        cd.create_user as det_create_user,
        cd.created_at as det_created_at,
        cd.updated_user as det_updated_user,
        cd.updated_at as det_updated_at

          FROM
          expense p
          join  expense_detail cd on p.expense_id=cd.expense_id
         join  workers w on p.worker_id=w.worker_id
         join  users u on p.create_user=u.id
         join  users u2 on cd.create_user=u2.id

        ";
        if(  $this->emp_job!=1){
            $rs_stmt1 = $rs_stmt1 . "
    join workers_manager wm on w.manager_id=wm.manager_id and  wm.user_id=$this->user_id";

        }
        $rs_stmt1 = $rs_stmt1 . " where  1=1 and p.is_deleted=0 ";

        if(  $this->emp_job!=1){
            if (Perm::get_function_access(73)) {
                $rs_stmt1 = $rs_stmt1 . " and  p.create_user = $this->user_id ";
            }
        }
        if ($expense_id != "") {
            $rs_stmt1 = $rs_stmt1 . " and  p.expense_id = '$expense_id' ";
        }

        $rs_stmt1 = $rs_stmt1 . $ord;
        $rs_stmt1 = $rs_stmt1 . " ORDER BY cd.expense_detail_id desc limit $b,$a ";
        $results = DB::select($rs_stmt1);

        return $results;
    }

}



