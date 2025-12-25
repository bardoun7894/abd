<?php
namespace App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;
use App\Models\Shop_rent;
use Jenssegers\Agent\Agent;

use function PHPUnit\Framework\isNull;

class ShopController extends Controller
{
    use ApimtitTrait;

    public function __construct()
    {
        $this->middleware('ishaveaccess:5');
    }

    public function index()
    {
        if (Perm::get_function_access(30)) {
// $agent = new Agent();
// dd($agent);

// $agent->is('Windows');
// $agent->is('Firefox');
// $agent->is('iPhone');
// $agent->is('OS X');

// $agent->isAndroidOS();
// $agent->isNexus();
// $agent->isSafari();

// $agent->isMobile();
// $agent->isTablet();

// $languages = $agent->languages();

// $device = $agent->device();

// $platform = $agent->platform();

// $browser = $agent->browser();

// $agent->isDesktop();

// $agent->isPhone();
// $agent->isRobot();
// $robot = $agent->robot();

// $browser = $agent->browser();
// $version = $agent->version($browser);

// $platform = $agent->platform();
// $version = $agent->version($platform);


            $page_title = 'ادخال بيانات المحل';
            $manager = $this->get_manager();
            $city = DB::table('city')->get();
            $const = array("manager", "city", "page_title");
            return view('dashboard.shop.index', compact($const));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function views()
    {
        if (Perm::get_function_access(31) || Perm::get_function_access(32) || Perm::get_function_access(33)
            || Perm::get_function_access(34) || Perm::get_function_access(35)
            || Perm::get_function_access(36) || Perm::get_function_access(37)
            || Perm::get_function_access(38)) {
            $manager = $this->get_manager();
            $city = DB::table('city')->get();
            $page_title = 'عرض بيانات العمال';
            return view('dashboard.shop.view', compact('manager', 'city', 'page_title'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(31) || Perm::get_function_access(32) || Perm::get_function_access(33)
                || Perm::get_function_access(34) || Perm::get_function_access(35)
                || Perm::get_function_access(36) || Perm::get_function_access(37)
                || Perm::get_function_access(38)
            )) {
            return view('dashboard.shop.tbl_shop');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function sel_shop_list(Request $request)
    {
        $string = $request->q;
        $page = $request->page;
        $response = Shop::sel_shop_list($string, $page);
        echo json_encode($response);
    }


    public function ajax_search_shop(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(31) || Perm::get_function_access(32) || Perm::get_function_access(33)
                || Perm::get_function_access(34) || Perm::get_function_access(35)
                || Perm::get_function_access(36) || Perm::get_function_access(37)
                || Perm::get_function_access(38)
            )) {
            $shop_name = $request->shop_name;
            $shop_mobile = $request->shop_mobile;
            $manager_id = $request->manager_id;
            $city_id = $request->city_id;
            $comme_no = $request->comme_no;
            $municip_no = $request->municip_no;
            $rentpay_price = $request->rentpay_price;

            $order_date = $request->order_date;
            $comme_month = $request->comme_month;
            $comme_year = $request->comme_year;
            $municip_month = $request->municip_month;
            $municip_year = $request->municip_year;
            $rentpay_month = $request->rentpay_month;
            $rentpay_year = $request->rentpay_year;

            $list_total = Shop::serachspendcount($shop_name, $shop_mobile, $manager_id, $city_id, $comme_no, $municip_no,$rentpay_price,$order_date,$comme_month,$comme_year,$municip_month,$municip_year,$rentpay_month,$rentpay_year);
            $list = Shop::serachspenddata($shop_name, $shop_mobile, $manager_id, $city_id, $comme_no, $municip_no,$rentpay_price,$order_date,$comme_month,$comme_year,$municip_month,$municip_year,$rentpay_month,$rentpay_year);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {



                $shop = Shop_rent::where("shop_id",$x->shop_id)->get()->first();
                //حالة عقد الاجار
                if(isset($shop->rent_edt))
                {
                    $today =   Carbon::parse(now())->format('d-m-Y');
                    $newDate = Carbon::now()->addDays(15);
                    $newDate = Carbon::parse($newDate)->format('d-m-Y');
                    $rentp_dt =   Carbon::parse($shop->rent_edt)->format('d-m-Y');


                $today = date("Y-m-d", strtotime($today));
                $newDate = date("Y-m-d", strtotime($newDate));
                $rentp_dt = date("Y-m-d", strtotime($rentp_dt));
                }

                $sr_desc_char ="";

                if(!isset($shop->rent_edt) ) {
                    $sr_desc_char = '<span class="ms-2 badge badge-light-info fw-bold"> تاريخ العقد غير مدخل </span>';
                }
                else if($rentp_dt<$newDate and $rentp_dt>$today)
                                {
                    $sr_desc_char = '<span class="ms-2 badge badge-light-warning fw-bold">شارف على الانتهاء</span>';
                } else if ($today>=$rentp_dt) {
                    $sr_desc_char = '<span class="ms-2 badge badge-light-danger fw-bold">منتهي</span>';
                } else  {
                    $sr_desc_char = '<span class="ms-2 badge badge-light-success fw-bold">سارية</span>';
                }

                $sr_desc_txt = '<div class="d-flex flex-column justify-content-center">';
                if (isset($shop->rent_no)) {
                    $sr_desc_txt .= '<div class="fw-bold text-dark">' . $shop->rent_no . '</div>';
                }
                    if(isset($shop->rent_edt))
                        $sr_desc_txt .= '<div class="fw-bold text-info">' . $shop->rent_edt . '</div><div class="fw-bold text-info">' . $sr_desc_char . '</div>';
                    else
                        $sr_desc_txt .= '<div class="fw-bold text-info">' . '</div><div class="fw-bold text-info">' . $sr_desc_char . '</div>';






                $no++;
                $i++;
                if ($x->name != '') {
                    $insert_desc = '<br><span class="ms-2 text-dark fw-bold">' . $x->name . '</span>';
                    $insert_desc .= '<br><span class="ms-2 text-dark fw-bold">' . Carbon::parse($x->created_at)->format('d-m-Y') . '</span>';
                } else {
                    $insert_desc = '';
                }




                $rentpay_dt=$x->rentpay_dt;
                if($rentpay_dt==''){
                    $rentpay_dt_char = '<span class="ms-2 badge badge-light-danger fw-bold"> لم يتم إدخال أي دفعة للآن  </span>';
                }

                else{



                $rentpay_dt =   Carbon::parse($rentpay_dt)->format('d-m-Y');
                $today =   Carbon::parse(now())->format('d-m-Y');
                $rentpay_price=$x->rentpay_price;


                $newDateTime = Carbon::now()->addDays(30);
                $newDateTime = Carbon::parse($newDateTime)->format('d-m-Y');


                $today = date("Y-m-d", strtotime($today));
                $rentpay_dt = date("Y-m-d", strtotime($rentpay_dt));
                $newDateTime = date("Y-m-d", strtotime($newDateTime));



                if ($rentpay_dt > $today and  $rentpay_dt > $newDateTime) {
                    $rentpay_dt_char = $rentpay_dt.'<br>'.'<span class="ms-2 badge badge-light-info fw-bold">ساري</span>'.'<br>'.
                    '<div class="fw-bold text-success">' . $x->rentpay_price . '</div>';
                }
              else  if ($rentpay_dt > $today and  $rentpay_dt < $newDateTime) {
                    $rentpay_dt_char = $rentpay_dt.'<br>'.'<span class="ms-2 badge badge-light-info fw-bold">على وشك الاستحقاق</span>'.'<br>'.
                    '<div class="fw-bold text-success">' . $x->rentpay_price . '</div>';
                }

                else if ($rentpay_dt == $today) {
                    $rentpay_dt_char = $rentpay_dt.'<br>'.'<span class="ms-2 badge badge-light-danger fw-bold">مستحق الان</span>'.'<br>'.
                    '<div class="fw-bold text-success">' . $x->rentpay_price . '</div>';
                }
                else{
                    $rentpay_dt_char = '<span class="ms-2 badge badge-light-primary fw-bold">يحتاج الى تحديث</span>';

                }
            }







                if ($x->sm_desc == '3') {
                    $sm_desc_char = '<span class="ms-2 badge badge-light-warning fw-bold">شارف على الانتهاء</span>';
                } else if ($x->sm_desc == '2') {
                    $sm_desc_char = '<span class="ms-2 badge badge-light-danger fw-bold">منتهي</span>';
                } else if ($x->sm_desc == '1') {
                    $sm_desc_char = '<span class="ms-2 badge badge-light-success fw-bold">سارية</span>';
                } else {
                    $sm_desc_char = '<span class="ms-2 badge badge-light-info fw-bold">غير مدخل</span>';
                }
                $sm_desc_txt = '<div class="d-flex flex-column justify-content-center">';
                if ($x->municip_no != '') {
                    $sm_desc_txt .= '<div class="fw-bold text-dark">' . $x->municip_no . '</div>';
                }
                if ($x->municip_edt != '') {
                    $sm_desc_txt .= '<div class="fw-bold text-info">' . $x->municip_edt . '</div>
<div class="fw-bold text-info">' . $sm_desc_char . '</div>';
                }


                if ($x->shel_desc == '3') {
                    $shel_desc_char = '<span class="ms-2 badge badge-light-warning fw-bold">شارف على الانتهاء</span>';
                } else if ($x->shel_desc == '2') {
                    $shel_desc_char = '<span class="ms-2 badge badge-light-danger fw-bold">منتهي</span>';
                } else if ($x->shel_desc == '1') {
                    $shel_desc_char = '<span class="ms-2 badge badge-light-success fw-bold">سارية</span>';
                } else {
                    $shel_desc_char = '<span class="ms-2 badge badge-light-info fw-bold">غير مدخل</span>';
                }
                $shel_desc_txt = '<div class="d-flex flex-column justify-content-center">';
                if ($x->health_no != '') {
                    $shel_desc_txt .= '<div class="fw-bold text-dark">' . $x->health_no . '</div>';
                }
                if ($x->health_edt != '') {
                    $shel_desc_txt .= '<div class="fw-bold text-info">' . $x->health_edt . '</div>
<div class="fw-bold text-info">' . $shel_desc_char . '</div>';
                }

                if ($x->sd_desc == '3') {
                    $sd_desc_char = '<span class="ms-2 badge badge-light-warning fw-bold">شارف على الانتهاء</span>';
                } else if ($x->sd_desc == '2') {
                    $sd_desc_char = '<span class="ms-2 badge badge-light-danger fw-bold">منتهي</span>';
                } else if ($x->sd_desc == '1') {
                    $sd_desc_char = '<span class="ms-2 badge badge-light-success fw-bold">سارية</span>';
                } else {
                    $sd_desc_char = '<span class="ms-2 badge badge-light-info fw-bold">غير مدخل</span>';
                }
                $sd_desc_txt = '<div class="d-flex flex-column justify-content-center">';
                if ($x->defence_no != '') {
                    $sd_desc_txt .= '<div class="fw-bold text-dark">' . $x->defence_no . '</div>';
                }
                if ($x->defence_edt != '') {
                    $sd_desc_txt .= '<div class="fw-bold text-info">' . $x->defence_edt . '</div>
<div class="fw-bold text-info">' . $sd_desc_char . '</div>';
                }
                if ($x->sc_desc == '3') {
                    $sc_desc_char = '<span class="ms-2 badge badge-light-warning fw-bold">شارف على الانتهاء</span>';
                } else if ($x->sc_desc == '2') {
                    $sc_desc_char = '<span class="ms-2 badge badge-light-danger fw-bold">منتهي</span>';
                } else if ($x->sc_desc == '1') {
                    $sc_desc_char = '<span class="ms-2 badge badge-light-success fw-bold">سارية</span>';
                } else {
                    $sc_desc_char = '<span class="ms-2 badge badge-light-info fw-bold">غير مدخل</span>';
                }
                $sc_desc_txt = '<div class="d-flex flex-column justify-content-center">';


                if ($x->comme_no != '') {
                    $sc_desc_txt = $sc_desc_txt . '<div class="fw-bold text-dark">' . $x->comme_no . '</div>';
                }
                if ($x->comme_edt != '') {
                    $sc_desc_txt = $sc_desc_txt . '<div class="fw-bold text-info">' . $x->comme_edt . '</div>
<div class="fw-bold text-info">' . $sc_desc_char . '</div>';
                }




                $count_shop_note = DB::table('shop_note')->where('is_deleted', '=' , 0 )
                ->where('note_type_id', '!=' , 3 )->where('shop_id', '=' ,$x->shop_id )->count();
                if ($count_shop_note != 0) {
                    $count_shop_note = '<span class="ms-2 badge badge-light-danger fw-bold">' . $count_shop_note . '</span>';
                } else {
                    $count_shop_note = '<span class="ms-2 badge badge-light-dark fw-bold">' . $count_shop_note . '</span>';
                }
                $row = array();
                $row[] = $i;
                $row[] = $x->shop_name;
                $row[] = $x->establishment_number;
                $row[] = $x->manager_name;
                $row[] = $x->shop_respon;
                $row[] = $x->city_name;
                $row[] = $x->shop_mobile;
                $row[] = $x->shop_location;
                $row[] = $sm_desc_txt;
                $row[] = $sc_desc_txt;
                $row[] = $sr_desc_txt;
                $row[] = $sd_desc_txt;
                $row[] = $rentpay_dt_char;
                $row[] = $x->note;
                $row[] = $count_shop_note;
                $row[] = $insert_desc;
                if (
                    Perm::get_function_access(32) || Perm::get_function_access(33)
                    || Perm::get_function_access(34) || Perm::get_function_access(35)
                    || Perm::get_function_access(36) || Perm::get_function_access(37)
                    || Perm::get_function_access(38)) {

                        $opt = '<div class="btn-group btn-group-sm" role="group"  >';


                        if (Perm::get_function_access(32) || Perm::get_function_access(33) || Perm::get_function_access(34) ) {
                            $opt = $opt . '<div class="dropdown" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px">
        <button class="btn btn-warning dropdown-toggle" style="padding:6px 10px 5.88px !important;font-size: 0.9rem;" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">الاجراءات</button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">';
        if (Perm::get_function_access(32)) {
            $opt .= '
    <li><a class="dropdown-item upd_shop fw-bolder text-dark"   data-url=' . "'" . route('dashboard.shop.upd_shop') . "'" . ' onclick="upd_shop(' . "'" . $x->shop_id . "'" . ')">  <i class="far fa-edit fa-fw text-info"></i> تعديل البيانات</a></li>
    ';
                            }
                            if (Perm::get_function_access(33)) {
                                $opt .= '
    <li><a class="dropdown-item upd_file fw-bolder text-dark"   data-url=' . "'" . route('dashboard.shop.upd_file') . "'" . ' onclick="upd_file(' . "'" . $x->shop_id . "'" . ')">  <i class="far fa-file-archive fa-fw text-success"></i> ملف المحل</a></li>
    ';
    $opt .= '
    <li><a class="dropdown-item upd_rentpay fw-bolder text-dark"  data-url=' . "'" . route('dashboard.shop.upd_rentpay') . "'" . ' onclick="upd_rentpay(' . "'" . $x->shop_id . "'" . ')">  <i class="fas fa-sticky-note fa-fw text-success"></i> ادارة الدفعات</a></li>
    ';
                            }
                            if (Perm::get_function_access(34)) {
                                $opt .= '<li><a class="dropdown-item fw-bolder text-dark"  onclick="del_shop(' . "'" . $x->shop_id . "'" . ')">  <i class="fas fa-trash-alt fa-fw text-danger"></i> حذف محل</a></li>';
                            }




                                $opt .= '
            <li><a class="dropdown-item print_shop_pdf fw-bolder text-dark"   data-url=' . "'" . route('dashboard.report.print_shop_pdf') . "'" . ' onclick="print_shop_pdf(' . "'" . $x->shop_id . "'" . ')">  <i class="fas fa-print fa-fw text-primary"></i> طباعة</a></li>
            ';


                            $opt .= '
        <div class="my-2 separator fw-bolder text-info" ></div>
        </ul>
        </div>';
                        }





















                    if (Perm::get_function_access(35)|| Perm::get_function_access(36) || Perm::get_function_access(37)|| Perm::get_function_access(38)) {
                        $opt = $opt . '<div class="dropdown" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px">
<button class="btn btn-info dropdown-toggle" style="padding:6px 10px 5.88px !important;font-size: 0.9rem;" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">الملاحظات</button>
<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">';
                        if (Perm::get_function_access(35)) {
                            $opt = $opt . '
<li><a class="dropdown-item upd_note fw-bolder text-dark"  data-url=' . "'" . route('dashboard.shop.upd_note') . "'" . ' onclick="upd_note(' . "'" . $x->shop_id . "'" . ')">  <i class="fas fa-bell fa-fw text-info"></i> اضافة ملاحظة</a></li>
';
                        }
                        if (Perm::get_function_access(36) || Perm::get_function_access(37) || Perm::get_function_access(38)) {
                            $opt .= '
<li><a class="dropdown-item upd_remark fw-bolder text-dark"  data-url=' . "'" . route('dashboard.shop.upd_remark') . "'" . ' onclick="upd_remark(' . "'" . $x->shop_id . "'" . ')">  <i class="fas fa-sticky-note fa-fw text-success"></i> عرض ملاحظة</a></li>
';

                        }


                        $opt .= '
                        <li><a class="dropdown-item shop_note_history fw-bolder text-dark"  data-url=' . "'" . route('dashboard.shop.shop_note_history') . "'" . ' onclick="shop_note_history(' . "'" . $x->shop_id . "'" . ')">  <i class="fas fa-history fa-fw text-danger"></i>عرض حركة السجلات</a></li>
                        ';
                        $opt = $opt . '<div class="my-2 separator fw-bolder text-info" ></div>
</ul>
</div>';
                    }
                    $opt .= '</div>';
                    $row[] = $opt;
                }
                $data[] = $row;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data);
            echo json_encode($output);
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }















    public function upd_rentpay(Request $request)
    {
        if (Perm::get_function_access(33) ) {
            $id = $request->id;
            $issamecreateshop = $this->issamecreateshop($id);
            if ($issamecreateshop) {
                $shop = DB::table('shop')
                ->leftJoin('manager', 'shop.manager_id', '=', 'manager.manager_id')
                ->leftJoin('city', 'shop.city_id', '=', 'city.city_id')
                ->leftJoin('users', 'shop.create_user', '=', 'users.id')
                ->leftJoin('shop_comme', 'shop.shop_id', '=', 'shop_comme.shop_id')
                ->leftJoin('shop_municip', 'shop.shop_id', '=', 'shop_municip.shop_id')
                ->leftJoin('shop_rent', 'shop.shop_id', '=', 'shop_rent.shop_id')
                ->select('shop.*', 'manager.manager_name', 'city.city_name', 'users.name', 'shop_comme.*', 'shop_municip.*', 'shop_rent.*', 'shop.shop_id',)
                ->where('shop.shop_id', $id)->first();
                return view('dashboard.shop.upd_rentpay', compact('shop'));
            }
        }
    }


    public function updrentpay(Request $request)
    {
        if (Perm::get_function_access(33)) {

            $rentpay_id = $request->rentpay_id;
            $shop_id = $request->shop_id;
            // $shop_note_id = $request->shop_note_id;
            //



            $issamecreateshop= $this->issamecreateshop($shop_id);
            if ($issamecreateshop) {
            $attributeNames = array(
                'shop_id' => 'المحل'
            );


                $validator = Validator::make($request->all(), [
                'shop_id' => ['required'],
                'rentpay_price' => ['required'],
                'rentpay_dt' => ['nullable', 'date'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $result2= DB::table('shop_rentpay')
                       ->updateOrInsert(
                           ['rentpay_id' =>$rentpay_id],
                           [
                               'shop_id' => $shop_id,
                               'rentpay_dt' =>  $request->rentpay_dt,
                               'rentpay_price' =>  $request->rentpay_price,
                               'rentpay_note' => $request->rentpay_note,
                               'updated_at' => Carbon::now(),
                               'update_user' => Auth::user()->id,
                           ]
                       );



                $result['url'] = route('dashboard.shop.tbl_rentpay');
                $result['status'] = $result2;
                $result['message_out'] = 'تمت الإضافة بنجاح .';
            }
            return response()->json($result);
        }
    }
}


    public function tbl_rentpay(Request $request)
    {
        if ($request->ajax()) {
            return view('dashboard.shop.tbl_rentpay');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_rentpay(Request $request)
    {
        if (Perm::get_function_access(33) ) {
            $shop_id = $request->shop_id;
            $list_total = Shop::serachrentpaycount($shop_id);
            $list = Shop::serachrentpaydata($shop_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();


                $row[] = $i;
                $row[] = Carbon::parse($x->rentpay_dt)->format('d-m-Y');
                $row[] = $x->rentpay_price;
                $row[] = $x->rentpay_note;
                $row[] = Carbon::parse($x->created_at)->format('d-m-Y');
                if (Perm::get_function_access(37)||Perm::get_function_access(38)) {
                    $opt = '<div class="btn-group btn-group-sm" role="group"  >';
                    if (Perm::get_function_access(33)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm change_rentpay" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"    data-url=' . "'" . route('dashboard.shop.change_rentpay') . "'" . ' onclick="change_rentpay(' . "'" . $x->rentpay_id  . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_rentpay(' . "'" . $x->rentpay_id  . "'" . ')"> <i class="fas fa-trash-alt fa-fw"></i>  </a>';
                    }
                    $opt .= '</div>';
                    $row[] = $opt;
                }
                $data[] = $row;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data);
            echo json_encode($output);
        }
    }



    function del_rentpay(Request $request)
    {
        if (Perm::get_function_access(33)) {
            $result['status'] = true;
            $result['message'] = 'تم';

            $id = $request->id;
            if($id!=''){
                try {

                    $delete = DB::delete('delete from shop_rentpay where rentpay_id = ?', [$id]);
                    if ($delete) {
                        $result['status'] = true;
                        $result['message'] = 'تم';
                    } else {
                        $message = 'لا يمكن الحذف';
                        $result['status'] = false;
                        $result['message'] = $message;
                    }
                } catch (\Exception $exception) {
                    $message = 'لا يمكن الحذف لانه يوجد بيانات معتمدة';
                    $result['status'] = false;
                    $result['message'] = $message;
                }
            }
            echo json_encode($result);


        }
    }


    public function change_rentpay(Request $request)
    {
        if (Perm::get_function_access(33)) {
            $rentpay_id= $request->id;
            $shop_rentpay = DB::table('shop_rentpay')->where('rentpay_id', $rentpay_id)->first();
            return view('dashboard.shop.change_rentpay', compact('shop_rentpay'));
        }
    }































    public function shop_note_history(Request $request)
    {
        if (Perm::get_function_access(35)|| Perm::get_function_access(36) || Perm::get_function_access(37)|| Perm::get_function_access(38)) {

            $id = $request->id;
            $issamecreateshop = $this->issamecreateshop($id);
            if ($issamecreateshop) {
                $shop = DB::table('shop')->where('shop_id', $id)->first();
                $page_title = 'إنشاء ملف ';
                $note_type = DB::table('note_type')->get();
                $const = array("note_type", "page_title");
                return view('dashboard.shop.shop_note_history', compact('shop', $const));
            }
        }
    }
    public function tbl_history(Request $request)
    {
        if (Perm::get_function_access(35)|| Perm::get_function_access(36) || Perm::get_function_access(37)|| Perm::get_function_access(38)) {

            return view('dashboard.shop.tbl_history');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_history(Request $request)
    {
        if (Perm::get_function_access(35)|| Perm::get_function_access(36) || Perm::get_function_access(37)|| Perm::get_function_access(38)) {


            $shop_id = $request->shop_id;
            $list_total = shop::serachhistorycount($shop_id);
            $list = shop::serachhistorydata($shop_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                $row[] = $i;
                $row[] = $x->note_type_name;
                $row[] = $x->remark;
                $row[] = $x->old_remark;
                $row[] = $x->note_type_name_old;

                $row[] = $x->name;
                $row[] = Carbon::parse($x->change_at)->format('d-m-Y');
                //  $row[] =$depend_desc;
                $data[] = $row;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data);
            echo json_encode($output);
        }
    }

    function del_shop(Request $request)
    {
        if (Perm::get_function_access(34)) {
            $id = $request->id;
            $issamecreateshop = $this->issamecreateshop($id);
            if ($issamecreateshop) {
                try {
                    $delete = DB::delete('delete from shop where shop_id = ?', [$id]);
                    if ($delete) {
                        $result['status'] = true;
                        $result['message'] = 'تم';
                    } else {
                        $message = 'لا يمكن الحذف';
                        $result['status'] = false;
                        $result['message'] = $message;
                    }
                } catch (\Exception $exception) {
                    $message = 'لا يمكن الحذف لانه يوجد بيانات معتمدة';
                    $result['status'] = false;
                    $result['message'] = $message;
                }
                echo json_encode($result);
            }
        }
    }


    function del_shop_rentpay(Request $request)
    {
        if (Perm::get_function_access(33)) {
            $result['status'] = true;
            $result['message'] = 'تم';

            $id = $request->id;
            if($id!=''){
                try {

                    $delete = DB::delete('delete from shop_rentpay where rentpay_id = ?', [$id]);
                    if ($delete) {
                        $result['status'] = true;
                        $result['message'] = 'تم';
                    } else {
                        $message = 'لا يمكن الحذف';
                        $result['status'] = false;
                        $result['message'] = $message;
                    }
                } catch (\Exception $exception) {
                    $message = 'لا يمكن الحذف لانه يوجد بيانات معتمدة';
                    $result['status'] = false;
                    $result['message'] = $message;
                }
            }
            echo json_encode($result);


        }
    }

    public function upd_shop(Request $request)
    {
        if (Perm::get_function_access(32)) {
            $id = $request->id;
            $shop = DB::table('shop')->where('shop_id', $id)->first();
            $manager = $this->get_manager();
            $city = DB::table('city')->get();
            $const = array("manager", "city");
            return view('dashboard.shop.upd_shop', compact('shop', $const));
        }
    }


    public function upd_file(Request $request)
    {
        if (Perm::get_function_access(33)) {
            $id = $request->id;
            $issamecreateshop = $this->issamecreateshop($id);
            if ($issamecreateshop) {
                $shop = DB::table('shop')
                    ->leftJoin('manager', 'shop.manager_id', '=', 'manager.manager_id')
                    ->leftJoin('city', 'shop.city_id', '=', 'city.city_id')
                    ->leftJoin('users', 'shop.create_user', '=', 'users.id')
                    ->leftJoin('shop_comme', 'shop.shop_id', '=', 'shop_comme.shop_id')
                    ->leftJoin('shop_municip', 'shop.shop_id', '=', 'shop_municip.shop_id')
                    ->leftJoin('shop_defence', 'shop.shop_id', '=', 'shop_defence.shop_id')
                    ->leftJoin('shop_rent', 'shop.shop_id', '=', 'shop_rent.shop_id')
                    ->select('shop.*', 'manager.manager_name', 'city.city_name', 'users.name', 'shop_comme.*', 'shop_municip.*', 'shop_defence.*', 'shop_rent.*', 'shop.shop_id',)
                    ->where('shop.shop_id', $id)->first();

                   // dd( $shop_rentpay);
                    $shop_attach = DB::table('shop_attach')->where('shop_id', $id)->get();

                $page_title = 'إنشاء ملف ';
                $manager = $this->get_manager();
                $city = DB::table('city')->get();
                $const = array("manager", "city", "page_title");
                return view('dashboard.shop.upd_file', compact('shop','shop_attach',$const));
            }
        }
    }


    public function updfile(Request $request)
    {
        if (Perm::get_function_access(33)) {
            $shop_id = $request->shop_id;








            $issamecreateshop = $this->issamecreateshop($shop_id);
            if ($issamecreateshop) {
                $attributeNames = array(
                    'comme_sso' => 'رقم الموحد',
                    'comme_no' => 'رقم السجل التجاري',
                    'comme_sdt' => 'تاريخ إصدار السجل',
                    'comme_sdt_h' => 'تاريخ إصدار السجل',

                    'comme_edt' => 'تاريخ انتهاء السجل',
                    'comme_edt_h' => 'تاريخ انتهاء السجل',

                    'shop_mobile' => 'رقم جوال المسؤول',
                    'comme_city' => 'المدينة',
                    'municip_no' => 'رقم الرخصة',
                    'municip_sdt' => 'تاريخ إصدار الرخصة',
                    'municip_sdt_h' => 'تاريخ إصدار الرخصة',

                    'municip_edt' => 'تاريخ انتهاء الرخصة',
                    'municip_edt_h' => 'تاريخ انتهاء الرخصة',

                    'municip_city' => 'المدينة',
                    'rent_no' => 'رقم العقد',
                    'rent_name' => 'اسم المؤجر',
                    'rent_sdt' => 'تاريخ بداية العقد',
                    'rent_sdt_h' => 'تاريخ بداية العقد',

                    'rent_edt' => 'تاريخ نهاية العقد',
                    'rent_edt_h' => 'تاريخ نهاية العقد',

                    'kt_docs_repeater_rent.*.rent_dt' => 'تاريخ الدفعة',
                    'kt_docs_repeater_rent.*.rentpay_price' => 'مبلغ الايجار',

                );
                $validator = Validator::make($request->all(), [
                    'shop_id' => ['required'],
                    'files.*' => ['nullable', 'file', 'mimes:csv,txt,xlx,xlsx,xls,pdf,ppt,pptx,doc,docx,xlsx,jpg,jpeg,bmp,png,rtf,zip'],
                    'comme_sdt' => ['nullable', 'date'],
                    'comme_sdt_h' => ['nullable', 'date'],

                    'comme_edt' => ['nullable', 'date'],
                    'comme_edt_h' => ['nullable', 'date'],

                    'municip_sdt' => ['nullable', 'date'],
                    'municip_sdt_h' => ['nullable', 'date'],

                    'municip_edt' => ['nullable', 'date'],
                    'municip_edt_h' => ['nullable', 'date'],

                    'rent_sdt' => ['nullable', 'date'],
                    'rent_sdt_h' => ['nullable', 'date'],

                    'rent_edt' => ['nullable', 'date'],
                    'rent_edt_h' => ['nullable', 'date'],

                    'kt_docs_repeater_rent.*.rent_dt' => ['nullable', 'date'],
                    'kt_docs_repeater_rent.*.rentpay_price' => ['nullable', 'numeric'],


                ]);
                $validator->setAttributeNames($attributeNames);
                if ($validator->fails()) {
                    $result['status'] = false;
                    $result['message'] = $validator->errors();
                    $result['message_out'] = '';
                } else {
                    $ERROR_FLAG = 0;
                    $user_photo = '';
                    $commefile_url = '';
                    $comme_attach_name = '';
                    $comme_attach_extension = '';
                    if ($request->hasFile('commefile')) {
                        $comme_attach_name = $request->commefile->getClientOriginalName();
                        $comme_attach_extension = $request->commefile->extension();
                        $commefile_name = time() . '.' . $request->commefile->extension();
                        $request->commefile->move(public_path('uploads/users/images/'), $commefile_name);
                        $commefile_url = 'uploads/users/images/' . $commefile_name;
                        if (File::exists($request->commefile_db)) {
                            File::delete($request->commefile_db);
                        }
                    } else {
                        $commefile_url = $request->commefile_db;
                    }
                    $municipfile_url = '';
                    $municip_attach_name = '';
                    $municip_attach_extension = '';
                    if ($request->hasFile('municipfile')) {
                        $municip_attach_name = $request->municipfile->getClientOriginalName();
                        $municip_attach_extension = $request->municipfile->extension();
                        $municipfile_name = time() . '.' . $request->municipfile->extension();
                        $request->municipfile->move(public_path('uploads/users/images/'), $municipfile_name);
                        $municipfile_url = 'uploads/users/images/' . $municipfile_name;
                        if (File::exists($request->municipfile_db)) {
                            File::delete($request->municipfile_db);
                        }
                    } else {
                        $municipfile_url = $request->municipfile_db;
                    }
                    $healthfile_url = '';
                    $health_attach_name = '';
                    $health_attach_extension = '';
                    if ($request->hasFile('healthfile')) {
                        $health_attach_name = $request->healthfile->getClientOriginalName();
                        $health_attach_extension = $request->healthfile->extension();
                        $healthfile_name = time() . '.' . $request->healthfile->extension();
                        $request->healthfile->move(public_path('uploads/users/images/'), $healthfile_name);
                        $healthfile_url = 'uploads/users/images/' . $healthfile_name;
                        if (File::exists($request->healthfile_db)) {
                            File::delete($request->healthfile_db);
                        }
                    } else {
                        $healthfile_url = $request->healthfile_db;
                    }

                    $defencefile_url = '';
                    $defence_attach_name = '';
                    $defence_attach_extension = '';
                    if ($request->hasFile('defencefile')) {
                        $defence_attach_name = $request->defencefile->getClientOriginalName();
                        $defence_attach_extension = $request->defencefile->extension();
                        $defencefile_name = time() . '.' . $request->defencefile->extension();
                        $request->defencefile->move(public_path('uploads/users/images/'), $defencefile_name);
                        $defencefile_url = 'uploads/users/images/' . $defencefile_name;
                        if (File::exists($request->defencefile_db)) {
                            File::delete($request->defencefile_db);
                        }
                    } else {
                        $defencefile_url = $request->defencefile_db;
                    }

                    $defencefile_url1 = '';
                    $defence_attach_name1 = '';
                    $defence_attach_extension1 = '';
                    if ($request->hasFile('defencefile1')) {
                        $defence_attach_name1 = $request->defencefile1->getClientOriginalName();
                        $defence_attach_extension1 = $request->defencefile1->extension();
                        $defencefile_name1 = time() . '.' . $request->defencefile1->extension();
                        $request->defencefile1->move(public_path('uploads/users/images/'), $defencefile_name1);
                        $defencefile_url1 = 'uploads/users/images/' . $defencefile_name1;
                        if (File::exists($request->defencefile_db1)) {
                            File::delete($request->defencefile_db1);
                        }
                    } else {
                        $defencefile_url1 = $request->defencefile_db1;
                    }
                    $defencefile_url2 = '';
                    $defence_attach_name2 = '';
                    $defence_attach_extension2 = '';
                    if ($request->hasFile('defencefile2')) {
                        $defence_attach_name2 = $request->defencefile2->getClientOriginalName();
                        $defence_attach_extension2 = $request->defencefile2->extension();
                        $defencefile_name2 = time() . '.' . $request->defencefile2->extension();
                        $request->defencefile2->move(public_path('uploads/users/images/'), $defencefile_name2);
                        $defencefile_url2 = 'uploads/users/images/' . $defencefile_name2;
                        if (File::exists($request->defencefile_db2)) {
                            File::delete($request->defencefile_db2);
                        }
                    } else {
                        $defencefile_url2 = $request->defencefile_db2;
                    }
                    $defencefile_url3 = '';
                    $defence_attach_name3 = '';
                    $defence_attach_extension3 = '';
                    if ($request->hasFile('defencefile3')) {
                        $defence_attach_name3 = $request->defencefile3->getClientOriginalName();
                        $defence_attach_extension3 = $request->defencefile3->extension();
                        $defencefile_name3 = time() . '.' . $request->defencefile3->extension();
                        $request->defencefile3->move(public_path('uploads/users/images/'), $defencefile_name3);
                        $defencefile_url3 = 'uploads/users/images/' . $defencefile_name3;
                        if (File::exists($request->defencefile_db3)) {
                            File::delete($request->defencefile_db3);
                        }
                    } else {
                        $defencefile_url3 = $request->defencefile_db3;
                    }
                    $defencefile_url4 = '';
                    $defence_attach_name4 = '';
                    $defence_attach_extension4 = '';
                    if ($request->hasFile('defencefile4')) {
                        $defence_attach_name4 = $request->defencefile4->getClientOriginalName();
                        $defence_attach_extension4 = $request->defencefile4->extension();
                        $defencefile_name4 = time() . '.' . $request->defencefile4->extension();
                        $request->defencefile4->move(public_path('uploads/users/images/'), $defencefile_name4);
                        $defencefile_url4 = 'uploads/users/images/' . $defencefile_name4;
                        if (File::exists($request->defencefile_db4)) {
                            File::delete($request->defencefile_db4);
                        }
                    } else {
                        $defencefile_url4 = $request->defencefile_db4;
                    }
                    $defencefile_url5 = '';
                    $defence_attach_name5 = '';
                    $defence_attach_extension5 = '';
                    if ($request->hasFile('defencefile5')) {
                        $defence_attach_name5 = $request->defencefile5->getClientOriginalName();
                        $defence_attach_extension5 = $request->defencefile5->extension();
                        $defencefile_name5 = time() . '.' . $request->defencefile5->extension();
                        $request->defencefile5->move(public_path('uploads/users/images/'), $defencefile_name5);
                        $defencefile_url5 = 'uploads/users/images/' . $defencefile_name5;
                        if (File::exists($request->defencefile_db5)) {
                            File::delete($request->defencefile_db5);
                        }
                    } else {
                        $defencefile_url5 = $request->defencefile_db5;
                    }
                    $defencefile_url6 = '';
                    $defence_attach_name6 = '';
                    $defence_attach_extension6 = '';
                    if ($request->hasFile('defencefile6')) {
                        $defence_attach_name6 = $request->defencefile6->getClientOriginalName();
                        $defence_attach_extension6 = $request->defencefile6->extension();
                        $defencefile_name6 = time() . '.' . $request->defencefile6->extension();
                        $request->defencefile6->move(public_path('uploads/users/images/'), $defencefile_name6);
                        $defencefile_url6 = 'uploads/users/images/' . $defencefile_name6;
                        if (File::exists($request->defencefile_db6)) {
                            File::delete($request->defencefile_db6);
                        }
                    } else {
                        $defencefile_url6 = $request->defencefile_db6;
                    }
                    $rentfile_url = '';
                    $rent_attach_name = '';
                    $rent_attach_extension = '';
                    if ($request->hasFile('rentfile')) {
                        $rent_attach_name = $request->rentfile->getClientOriginalName();
                        $rent_attach_extension = $request->rentfile->extension();
                        $rentfile_name = time() . '.' . $request->rentfile->extension();
                        $request->rentfile->move(public_path('uploads/users/images/'), $rentfile_name);
                        $rentfile_url = 'uploads/users/images/' . $rentfile_name;
                        if (File::exists($request->rentfile_db)) {
                            File::delete($request->rentfile_db);
                        }
                    } else {
                        $rentfile_url = $request->rentfile_db;
                    }
                    $result2 = DB::table('shop_comme')
                        ->updateOrInsert(
                            ['shop_comme_id' => $request->shop_comme_id],
                            [
                                'shop_id' => $shop_id,
                                'comme_sso' => $request->comme_sso,
                                'comme_no' => $request->comme_no,
                                'comme_sdt' => $request->comme_sdt,
                                'comme_sdt_h' => $request->comme_sdt_h,
                                'comme_edt' => $request->comme_edt,
                                'comme_edt_h' => $request->comme_edt_h,
                                'comme_city' => $request->comme_city,
                                'comme_attach_name' => $comme_attach_name,
                                'comme_attach_extension' => $comme_attach_extension,
                                'comme_attach_url' => $commefile_url,
                                'comme_note' => $request->comme_note,
                                'updated_at' => Carbon::now(),
                                'update_user' => Auth::user()->id,
                            ]
                        );
                    $result2 = DB::table('shop_municip')
                        ->updateOrInsert(
                            ['shop_municip_id' => $request->shop_municip_id],
                            [
                                'shop_id' => $shop_id,
                                'municip_no' => $request->municip_no,
                                'municip_sdt' => $request->municip_sdt,
                                'municip_sdt_h' => $request->municip_sdt_h,

                                'municip_edt' => $request->municip_edt,
                                'municip_edt_h' => $request->municip_edt_h,

                                'municip_city' => $request->municip_city,

                                'municip_active' => $request->municip_active,
                                'municip_width' => $request->municip_width,
                                'municip_name' => $request->municip_name,
                                'municip_region' => $request->municip_region,

                                'municip_attach_name' => $municip_attach_name,
                                'municip_attach_extension' => $municip_attach_extension,
                                'municip_attach_url' => $municipfile_url,
                                'municip_note' => $request->municip_note,
                                'updated_at' => Carbon::now(),
                                'update_user' => Auth::user()->id,
                            ]
                        );

                    $result2 = DB::table('shop_rent')
                        ->updateOrInsert(
                            ['shop_rent_id' => $request->shop_rent_id],
                            [
                                'shop_id' => $shop_id,
                                'rent_no' => $request->rent_no,
                                'rent_name' => $request->rent_name,
                                'rent_mobile' => $request->rent_mobile,
                                'rent_name' => $request->rent_name,
                                'rent_sdt' => $request->rent_sdt,
                                'rent_sdt_h' => $request->rent_sdt_h,

                                'rent_edt' => $request->rent_edt,
                                'rent_edt_h' => $request->rent_edt_h,

                                'rent_attach_name' => $rent_attach_name,
                                'rent_attach_extension' => $rent_attach_extension,
                                'rent_attach_url' => $rentfile_url,
                                'rent_note' => $request->rent_note,
                                'updated_at' => Carbon::now(),
                                'update_user' => Auth::user()->id,
                            ]
                        );



                    $result2 = DB::table('shop_defence')
                        ->updateOrInsert(
                            ['shop_defence_id' => $request->shop_defence_id],
                            [
                                'shop_id' => $shop_id,
                                'defence_no' => $request->defence_no,
                                'defence_sdt' => $request->defence_sdt,
                                'defence_sdt_h' => $request->defence_sdt_h,

                                'defence_edt' => $request->defence_edt,
                                'defence_edt_h' => $request->defence_edt_h,
                                'defence_city' => $request->defence_city,
                                'defence_attach_name' => $defence_attach_name,
                                'defence_attach_extension' => $defence_attach_extension,
                                'defence_attach_url' => $defencefile_url,
                                'defence_attach_name1' => $defence_attach_name1,
                                'defence_attach_extension1' => $defence_attach_extension1,
                                'defence_attach_url1' => $defencefile_url1,
                                'defence_attach_name2' => $defence_attach_name2,
                                'defence_attach_extension2' => $defence_attach_extension2,
                                'defence_attach_url2' => $defencefile_url2,
                                'defence_attach_name3' => $defence_attach_name3,
                                'defence_attach_extension3' => $defence_attach_extension3,
                                'defence_attach_url3' => $defencefile_url3,
                                'defence_attach_name4' => $defence_attach_name4,
                                'defence_attach_extension4' => $defence_attach_extension4,
                                'defence_attach_url4' => $defencefile_url4,
                                'defence_attach_name5' => $defence_attach_name5,
                                'defence_attach_extension5' => $defence_attach_extension5,
                                'defence_attach_url5' => $defencefile_url5,
                                'defence_attach_name6' => $defence_attach_name6,
                                'defence_attach_extension6' => $defence_attach_extension6,
                                'defence_attach_url6' => $defencefile_url6,
                                'defence_note' => $request->defence_note,
                                'updated_at' => Carbon::now(),
                                'update_user' => Auth::user()->id,
                            ]
                        );






                        $file_url = '';
                        if ($request->hasfile('files')) {
                            foreach ($request->file('files') as $key => $file) {
                                $orginal_name = $file->getClientOriginalName();
                                $ext = $file->extension();
                                $fileName = time() . rand(1, 99) . '.' . $file->extension();
                                $file->move(public_path('uploads/mol/'), $fileName);
                                $file_url = 'uploads/mol/' . $fileName;
                                if (isset($request->emp_att_id[$key])) {
                                    $result2 = DB::table('shop_attach')
                                        ->where('shop_attach_id', $request->emp_att_id[$key])
                                        ->update([
                                            'shop_attach_name' => $orginal_name,
                                            'shop_attach_url' => $file_url,
                                            'shop_attach_extension' => $ext,
                                        ]);
                                    if (File::exists($request->image_url_emp[$key])) {
                                        File::delete($request->image_url_emp[$key]);
                                    }
                                    $result2 = 1;

                                } else {

                                    $result_upload = DB::table('shop_attach')->insertGetId([
                                        'shop_id' => $shop_id,
                                        'shop_attach_name' => $orginal_name,
                                        'shop_attach_url' => $file_url,
                                        'shop_attach_extension' => $ext,

                                    ]);
                                }


                            }
                        }








                    $result['status'] = $result2;
                    $result['message_out'] = 'تم الحفظ بنجاح';
                    $result['message'] = '';

                }
                return response()->json($result);
            }
        }
    }


    public function upd_note(Request $request)
    {
        if (Perm::get_function_access(35)) {
            $id = $request->id;
            $issamecreateshop = $this->issamecreateshop($id);
            if ($issamecreateshop) {
                $shop = DB::table('shop')
                    ->leftJoin('manager', 'shop.manager_id', '=', 'manager.manager_id')
                    ->leftJoin('city', 'shop.city_id', '=', 'city.city_id')
                    ->leftJoin('users', 'shop.create_user', '=', 'users.id')
                    ->leftJoin('shop_comme', 'shop.shop_id', '=', 'shop_comme.shop_id')
                    ->leftJoin('shop_municip', 'shop.shop_id', '=', 'shop_municip.shop_id')
                    ->leftJoin('shop_rent', 'shop.shop_id', '=', 'shop_rent.shop_id')
                    ->select('shop.*', 'manager.manager_name', 'city.city_name', 'users.name', 'shop_comme.*', 'shop_municip.*', 'shop_rent.*', 'shop.shop_id',)
                    ->where('shop.shop_id', $id)->first();
                // $shop =DB::table('shop')->where('shop_id',$id)->first();
                //$shop_attach =DB::table('shop_attach')->where('shop_id',$id)->get();
                $sub_add_shop = "1";
                $page_title = 'إنشاء ملف ';
                $note_type = DB::table('note_type')->get();
                $const = array("note_type", "page_title");
                return view('dashboard.shop.upd_note', compact('shop', $const));
            }
        }
    }


    public function updnote(Request $request)
    {
        if (Perm::get_function_access(35)) {
            $shop_id = $request->shop_id;
            $issamecreateshop = $this->issamecreateshop($shop_id);
            if ($issamecreateshop) {

                $attributeNames = array(
                    'note_type_id' => 'نوع الملاحظة',
                );
                $validator = Validator::make($request->all(), [
                    'shop_id' => ['required'],
                    'file.*' => ['nullable', 'file', 'mimes:csv,txt,xlx,xlsx,xls,pdf,ppt,pptx,doc,docx,xlsx,jpg,jpeg,bmp,png,rtf,zip'],
                ]);
                $validator->setAttributeNames($attributeNames);
                if ($validator->fails()) {
                    $result['status'] = false;
                    $result['message'] = $validator->errors();
                    $result['message_out'] = '';
                } else {
                    $ERROR_FLAG = 0;
                    $result2 = DB::table('shop_note')->insertGetId([
                        'shop_id' => $shop_id,
                        'note_type_id' => $request->note_type_id,
                        'remark' => $request->remark,
                        'created_note_at' => Carbon::now(),
                        'create_note_user' => Auth::user()->id,
                    ]);
                    if ($result2 != '') {
                        $file_url = '';
                        if ($request->hasfile('files')) {
                            foreach ($request->file('files') as $key => $file) {
                                $orginal_name = $file->getClientOriginalName();
                                $ext = $file->extension();
                                $fileName = time() . rand(1, 99) . '.' . $file->extension();
                                $file->move(public_path('uploads/mol/'), $fileName);
                                $file_url = 'uploads/mol/' . $fileName;
                                $result_upload = DB::table('note_attach')->insertGetId([
                                    'shop_note_id' => $result2,
                                    'note_attach_name' => $orginal_name,
                                    'note_attach_url' => $file_url,
                                    'note_attach_extension' => $ext,
                                ]);
                            }
                        }
                        $result3 = DB::table('shop_note_history')->insertGetId([
                            'shop_note_id' => $result2,
                            'change_user' => Auth::user()->id,
                            'change_at' => Carbon::now(),
                            'note_type_id' => $request->note_type_id,
                            'remark' => $request->remark
                        ]);
                        $result['status'] = $result2;
                        $result['message_out'] = 'تم الحفظ بنجاح';
                    } else {
                        if (File::exists($user_photo)) {
                            File::delete($user_photo);
                        }
                    }
                }
                return response()->json($result);
            }
        }
    }


    public function upd_remark(Request $request)
    {
        if (Perm::get_function_access(36) || Perm::get_function_access(37) || Perm::get_function_access(38)) {
            $id = $request->id;
            $issamecreateshop = $this->issamecreateshop($id);
            if ($issamecreateshop) {
                $shop = DB::table('shop')
                    ->leftJoin('manager', 'shop.manager_id', '=', 'manager.manager_id')
                    ->leftJoin('city', 'shop.city_id', '=', 'city.city_id')
                    ->leftJoin('users', 'shop.create_user', '=', 'users.id')
                    ->leftJoin('shop_comme', 'shop.shop_id', '=', 'shop_comme.shop_id')
                    ->leftJoin('shop_municip', 'shop.shop_id', '=', 'shop_municip.shop_id')
                    ->leftJoin('shop_rent', 'shop.shop_id', '=', 'shop_rent.shop_id')
                    ->select('shop.*', 'manager.manager_name', 'city.city_name', 'users.name', 'shop_comme.*', 'shop_municip.*', 'shop_rent.*', 'shop.shop_id',)
                    ->where('shop.shop_id', $id)->first();
                $page_title = 'إنشاء ملف ';
                $note_type = DB::table('note_type')->get();
                $const = array("note_type", "page_title");
                return view('dashboard.shop.upd_remark', compact('shop', $const));
            }
        }
    }


    public function updremark(Request $request)
    {
        if (Perm::get_function_access(37)) {

            $shop_note_id = $request->shop_note_id;
            $shop_id = $request->shop_id;
            $issamecreateshop= $this->issamecreateshop($shop_id);
            if ($issamecreateshop) {
            $attributeNames = array(
                'note_type_id' => 'نوع الملاحظة',
            );
                $shop_note = DB::table('shop_note')->where('shop_note_id', $shop_note_id)->first();
                $old_remark = $shop_note->remark;
                $old_note_type_id = $shop_note->note_type_id;


                $validator = Validator::make($request->all(), [
                'shop_id' => ['required'],
                'note_type_id' => ['required'],
                'file.*' => ['nullable', 'file', 'mimes:csv,txt,xlx,xlsx,xls,pdf,ppt,pptx,doc,docx,xlsx,jpg,jpeg,bmp,png,rtf,zip'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $result2 = DB::table('shop_note')
                    ->where('shop_note_id', $shop_note_id)
                    ->update([
                        'note_type_id' => $request->note_type_id,
                        'remark' => $request->remark,
                        'updated_note_at' => Carbon::now(),
                        'update_note_user' => Auth::user()->id,
                    ]);
                $file_url = '';
                if ($request->hasfile('files')) {
                    foreach ($request->file('files') as $key => $file) {
                        $orginal_name = $file->getClientOriginalName();
                        $ext = $file->extension();
                        $fileName = time() . rand(1, 99) . '.' . $file->extension();
                        $file->move(public_path('uploads/mol/'), $fileName);
                        $file_url = 'uploads/mol/' . $fileName;
                        if (isset($request->emp_att_id[$key])) {
                            $result2 = DB::table('note_attach')
                                ->where('note_attach_id', $request->emp_att_id[$key])
                                ->update([
                                    'note_attach_name' => $orginal_name,
                                    'note_attach_url' => $file_url,
                                    'note_attach_extension' => $ext,
                                ]);
                            if (File::exists($request->image_url_emp[$key])) {
                                File::delete($request->image_url_emp[$key]);
                            }
                            $result2 = 1;
                        } else {
                            $result_upload = DB::table('note_attach')->insertGetId([
                                'shop_note_id' => $shop_note_id,
                                'note_attach_name' => $orginal_name,
                                'note_attach_url' => $file_url,
                                'note_attach_extension' => $ext,
                            ]);
                        }
                    }
                }
                $result3 = DB::table('shop_note_history')->insertGetId([
                    'shop_note_id' => $shop_note_id,
                    'change_user' => Auth::user()->id,
                    'change_at' => Carbon::now(),
                    'note_type_id' => $request->note_type_id,
                    'remark' => $request->remark,
                    'old_remark' => $old_remark,
                    'old_note_type_id' => $old_note_type_id,
                ]);

                $result['url'] = route('dashboard.shop.tbl_remark');
                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';
            }
            return response()->json($result);
        }
    }
}


    public function tbl_remark(Request $request)
    {
        if ($request->ajax()) {
            return view('dashboard.shop.tbl_remark');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_remark(Request $request)
    {
        if (Perm::get_function_access(36) || Perm::get_function_access(37) || Perm::get_function_access(38)) {
            $shop_id = $request->shop_id;
            $list_total = Shop::serachremarkcount($shop_id);
            $list = Shop::serachremarkdata($shop_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                $depend = $x->depend;
                if ($depend == '1') {
                    $depend_desc = '<span class="ms-2 badge badge-light-success fw-bold">معتمد</span>';
                } else {
                    $depend_desc = '<span class="ms-2 badge badge-light-danger fw-bold">غير معتمد</span>';
                }
                $note_type_id = $x->note_type_id;
                if ($note_type_id == '1') {
                    $note_type_desc = '<span class="ms-2 badge badge-light-danger fw-bold">' . $x->note_type_name . '</span>';
                 } else if ($note_type_id == '2') {
                        $note_type_desc = '<span class="ms-2 badge badge-light-info fw-bold">' . $x->note_type_name . '</span>';
                    } else {
               $note_type_desc = '<span class="ms-2 badge badge-light-success fw-bold">' . $x->note_type_name . '</span>';
                }
                $row[] = $i;
                $row[] = $note_type_desc;
                $row[] = $x->remark;
                $row[] = $x->name;
                $row[] = Carbon::parse($x->created_note_at)->format('d-m-Y');
                if (Perm::get_function_access(37)||Perm::get_function_access(38)) {
                    $opt = '<div class="btn-group btn-group-sm" role="group"  >';
                    if (Perm::get_function_access(37)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm change_remark" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"    data-url=' . "'" . route('dashboard.shop.change_remark') . "'" . ' onclick="change_remark(' . "'" . $x->shop_note_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(38)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_remark(' . "'" . $x->shop_note_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw"></i>  </a>';
                    }
                    $opt .= '</div>';
                    $row[] = $opt;
                }
                $data[] = $row;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data);
            echo json_encode($output);
        }
    }


    function del_remark(Request $request)
    {
        if (Perm::get_function_access(38)) {

            $id = $request->id;


            $shop_chk = DB::table('shop_note')
                ->Join('shop', 'shop.shop_id', '=', 'shop_note.shop_id')
                ->select('shop_note.*')
                ->where('shop_note.shop_note_id', $id)->first();
             //   dd($shop_chk);
            $id = $shop_chk->shop_note_id;
            $worker_id = $shop_chk->shop_id;
            $old_remark = $shop_chk->remark;
            $old_note_type_id = $shop_chk->note_type_id;









            $delete = DB::table('shop_note')
                ->where('shop_note_id', $id)
                ->update([
                    'is_deleted' => 1,
                    'deleted_at' => Carbon::now(),
                    'deleted_user' => Auth::user()->id,
                ]);
            // $result3 = DB::table('shop_note_history')->insertGetId([
            //     'shop_note_id' => $id,
            //     'change_user' => Auth::user()->id,
            //     'change_at' => Carbon::now(),
            //     'is_deleted' => 1,
            //     'remark' => 'تم حذف '
            // ]);

            $result3 = DB::table('shop_note_history')->insertGetId([
                'shop_note_id' => $id,
                'change_user' => Auth::user()->id,
                'change_at' => Carbon::now(),
                'is_deleted' => 1,
                'remark' => 'تم حذف ',
                'old_note_type_id' => $old_note_type_id,
                'old_remark' => $old_remark,

            ]);

            if ($delete) {
                $result['status'] = true;
                $result['message'] = 'تم';
            } else {
                $message = 'لا يمكن الحذف';
                $result['status'] = false;
                $result['message'] = $message;
            }
            echo json_encode($result);
        }
    }


    public function change_remark(Request $request)
    {
        if (Perm::get_function_access(37)) {
            $shop_note_id = $request->id;
            /*     $calculate =  DB::table('shop_note')->where('shop_note_id',$id)->first();
                 $shop_id=$calculate->shop_id;*/
            $shop_note = DB::table('shop_note')->where('shop_note_id', $shop_note_id)->first();
            $note_type = DB::table('note_type')->get();
            $note_attach = DB::table('note_attach')->where('shop_note_id', $shop_note_id)->get();
            return view('dashboard.shop.change_remark', compact('shop_note', 'note_type', 'note_attach'));
        }
    }


    public function store(Request $request)
    {
        if (Perm::get_function_access(30)) {

            $shop_name_old = $request->old('shop_name');
            $attributeNames = array(
                'shop_name' => 'اسم المحل',
                'paymentcalculate_month_vals_month_val' => 'المبلغ المطلوب',
                'manager_id' => 'قائد المحل',
                'shop_respon' => 'اسم المسؤول',
                'shop_mobile' => 'رقم جوال المسؤول',
                'city_id' => 'المدينة',
                'shop_location' => 'موقع المحل',
            );
            $validator = Validator::make($request->all(), [
                'shop_name' => ['required', 'string'],
                'manager_id' => ['required', 'string'],
                'calculate_month_val' => ['required', 'integer'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                /* $user_photo='';
                 if ($request->hasFile('avatar')) {
                             $imageName = time() . '.' . $request->avatar->extension();
                                $request->avatar->move(public_path('uploads/users/images/'), $imageName);
                          $user_photo = 'uploads/users/images/' . $imageName;
                         }
                        $passportfile_url='';
                         if ($request->hasFile('passportfile')) {
                             $passportfile_name = time() . '.' . $request->passportfile->extension();
                                $request->passportfile->move(public_path('uploads/users/images/'), $passportfile_name);
                          $passportfile_url = 'uploads/users/images/' . $passportfile_name;
                         }
                         $ssnfile_url='';
                         if ($request->hasFile('ssnfile')) {
                             $ssnfile_name = time() . '.' . $request->ssnfile->extension();
                                $request->ssnfile->move(public_path('uploads/users/images/'), $ssnfile_name);
                          $ssnfile_url = 'uploads/users/images/' . $ssnfile_name;
                         }*/
                $result2 = DB::table('shop')->insertGetId([
                    'shop_name' => $request->shop_name,
                    'establishment_number' => $request->establishment_number,
                    'calculate_month_val' => $request->calculate_month_val,
                    'manager_id' => $request->manager_id,
                    'shop_respon' => $request->shop_respon,
                    'shop_mobile' => $request->shop_mobile,
                    'city_id' => $request->city_id,
                    'shop_location' => $request->shop_location,
                    'note' => $request->note,
                    'created_at' => Carbon::now(),
                    'create_user' => Auth::user()->id,
                ]);
                if ($result2 != '') {
                    $result['status'] = $result2;
                    $result['message_out'] = 'تم الحفظ بنجاح';
                } else {
                    $message = 'لا يمكن الحفظ';
                    $result['status'] = false;
                    $result['message_out'] = $message;
                }
            }
            return response()->json($result);
        }
    }




    public function delete_file(Request $request)
    {
        $shop_id = $request->shop_id;
        $ssnfile_url = $request->ssnfile_url;
        $type = $request->type;
        if ($type == 'commefile') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result2 = DB::table('shop_comme')->where('shop_comme_id', $shop_id)->update([
                'comme_attach_name' => '',
                'comme_attach_extension' => '',
                'comme_attach_url' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;
            echo json_encode($result);
        }


        if ($type == 'municipfile') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result2 = DB::table('shop_municip')->where('shop_municip_id', $shop_id)->update([
                'municip_attach_name' => '',
                'municip_attach_extension' => '',
                'municip_attach_url' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }

        if ($type == 'rentfile') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result2 = DB::table('shop_rent')->where('shop_rent_id', $shop_id)->update([
                'rent_attach_name' => '',
                'rent_attach_extension' => '',
                'rent_attach_url' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }
        if ($type == 'defencefile') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result2 = DB::table('shop_defence')->where('shop_defence_id', $shop_id)->update([
                'defence_attach_name' => '',
                'defence_attach_extension' => '',
                'defence_attach_url' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }
        if ($type == 'defencefile1') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result2 = DB::table('shop_defence')->where('shop_defence_id', $shop_id)->update([
                'defence_attach_name1' => '',
                'defence_attach_extension1' => '',
                'defence_attach_url1' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }
        if ($type == 'defencefile2') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result2 = DB::table('shop_defence')->where('shop_defence_id', $shop_id)->update([
                'defence_attach_name2' => '',
                'defence_attach_extension2' => '',
                'defence_attach_url2' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }
        if ($type == 'defencefile3') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result3 = DB::table('shop_defence')->where('shop_defence_id', $shop_id)->update([
                'defence_attach_name3' => '',
                'defence_attach_extension3' => '',
                'defence_attach_url3' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }
        if ($type == 'defencefile4') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result4 = DB::table('shop_defence')->where('shop_defence_id', $shop_id)->update([
                'defence_attach_name4' => '',
                'defence_attach_extension4' => '',
                'defence_attach_url4' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }
        if ($type == 'defencefile5') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result5 = DB::table('shop_defence')->where('shop_defence_id', $shop_id)->update([
                'defence_attach_name5' => '',
                'defence_attach_extension5' => '',
                'defence_attach_url5' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }
        if ($type == 'defencefile6') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result6 = DB::table('shop_defence')->where('shop_defence_id', $shop_id)->update([
                'defence_attach_name6' => '',
                'defence_attach_extension6' => '',
                'defence_attach_url6' => '',
            ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }
        if ($type == 'shop_attach') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result2 = DB::delete('delete from shop_attach where shop_attach_id = ?', [$shop_id]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }
    }


    public function updstore(Request $request)
    {
        if (Perm::get_function_access(32)) {
            $id = $request->shop_id_db;
            $issamecreateshop= $this->issamecreateshop($id);
            if ($issamecreateshop) {
            $attributeNames = array(
                'shop_name' => 'اسم المحل',
                'calculate_month_val' => 'المبلغ المطلوب',
                'manager_id' => 'قائد المحل',
                'shop_respon' => 'اسم المسؤول',
                'shop_mobile' => 'رقم جوال المسؤول',
                'city_id' => 'المدينة',
                'shop_location' => 'موقع المحل',
            );
            $validator = Validator::make($request->all(), [
                'shop_name' => ['required', 'string'],
                'manager_id' => ['required', 'string'],
                'calculate_month_val' => ['required', 'integer'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $result2 = DB::table('shop')
                    ->where('shop_id', $id)
                    ->update([
                        'shop_name' => $request->shop_name,
                        'establishment_number' => $request->establishment_number,
                        'calculate_month_val' => $request->calculate_month_val,
                        'manager_id' => $request->manager_id,
                        'shop_respon' => $request->shop_respon,
                        'shop_mobile' => $request->shop_mobile,
                        'city_id' => $request->city_id,
                        'shop_location' => $request->shop_location,
                        'note' => $request->note,
                        'updated_at' => Carbon::now(),
                        'update_user' => Auth::user()->id,
                    ]);
                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';
            }
            return response()->json($result);
            } else {
                $message = 'لا يمكن التعديل لانه ليس انت مدخله';
                $result['status'] = false;
                $result['message_out'] = $message;
                $result['message'] = '';

                echo json_encode($result);

            }
        }
    }


    public function updfile_______(Request $request)
    {
        $id = $request->shop_id_db;
        $shop_name = $request->old('shop_name');

        $attributeNames = array(
            'shop_name' => 'اسم المحل',
            'calculate_month_val' => 'المبلغ المطلوب',
            'manager_id' => 'قائد المحل',
            'shop_respon' => 'اسم المسؤول',
            'shop_mobile' => 'رقم جوال المسؤول',
            'city_id' => 'المدينة',
            'shop_location' => 'موقع المحل',
        );
        $validator = Validator::make($request->all(), [
            'shop_name' => ['required', 'string'],
            'manager_id' => ['required', 'string'],
            'calculate_month_val' => ['required', 'integer'],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
        } else {
            $ERROR_FLAG = 0;


            $result2 = DB::table('shop')
                ->where('shop_id', $id)
                ->update([
                    'shop_name' => $request->shop_name,
                    'calculate_month_val' => $request->calculate_month_val,
                    'manager_id' => $request->manager_id,
                    'shop_respon' => $request->shop_respon,
                    'shop_mobile' => $request->shop_mobile,
                    'city_id' => $request->city_id,
                    'shop_location' => $request->shop_location,
                    'note' => $request->note,
                    'updated_at' => Carbon::now(),
                    'update_user' => Auth::user()->id,
                ]);


            $result['status'] = $result2;
            $result['message_out'] = 'تم الحفظ بنجاح';

        }
        return response()->json($result);

    }


}
