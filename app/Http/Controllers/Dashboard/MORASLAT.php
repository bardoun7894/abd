<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Moraslat;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;


class MoraslatController extends Controller
{
    use ApimtitTrait;

    public function __construct()
    {
        $this->middleware('ishaveaccess:8');
    }
    public function index()
    {
        if (Perm::get_function_access(49)) {
            $page_title = 'ادخال بيانات المراسلات  ';
            $moraslat_type = DB::table('moraslat_type')->get();
            $const = array("moraslat_type", "page_title");
            return view('dashboard.moraslat.index', compact($const));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function load_moraslat_form(Request $request)
    {
        $moraslat_type_id = $request->moraslat_type_id;
        $desc = $request->desc;
        $moraslat_id = $request->moraslat_id;

        if ($desc == 1) {
            if ($moraslat_type_id == 1) {
                $users = DB::table('users')->where('id', '!=', Auth::user()->id)->get();

                $moraslat_categoty = DB::table('moraslat_categoty')->get();
                $const = array("users", "moraslat_categoty");
                return view('dashboard.moraslat.moraslat_shop', compact($const));


            } else if ($moraslat_type_id == 2) {
                $users = DB::table('users')->where('id', '!=', Auth::user()->id)->get();

                $moraslat_categoty = DB::table('moraslat_categoty')->get();
                $const = array("users", "moraslat_categoty");
                return view('dashboard.moraslat.moraslat_workall', compact($const));


            } else if ($moraslat_type_id == 3) {

                $users = DB::table('users')->where('id', '!=', Auth::user()->id)->get();

                $moraslat_categoty = DB::table('moraslat_categoty')->get();
                $const = array("users", "moraslat_categoty");
                return view('dashboard.moraslat.moraslat_workspec', compact($const));

            }

        } else {


            if ($moraslat_type_id == 1) {
                $moraslat_id = $request->moraslat_id;
                $moraslat = DB::table('moraslat')->where('moraslat_id', $moraslat_id)->first();
                $shop_id = $moraslat->shop_id;
                if ($shop_id != '') {
                    $shop = DB::table('shop')->where('shop_id', $shop_id)->first();
                    $corr = 1;
                } else {
                    $shop = '';
                    $corr = 0;
                }
                $moraslat_attach = DB::table('moraslat_attach')->where('moraslat_id', $moraslat_id)->get();

                $user = DB::table('users')->get();
                $moraslat_categoty = DB::table('moraslat_categoty')->get();
                $const = array("user", "moraslat_categoty", "shop", 'corr', "moraslat_attach");
                return view('dashboard.moraslat.moraslat_shop_upd', compact('moraslat', $const));
            } else if ($moraslat_type_id == 2) {
                $moraslat_id = $request->moraslat_id;
                $moraslat = DB::table('moraslat')->where('moraslat_id', $moraslat_id)->first();
                $users = DB::table('users')->where('id', '!=', Auth::user()->id)->get();
                $moraslat_attach = DB::table('moraslat_attach')->where('moraslat_id', $moraslat_id)->get();

                $moraslat_categoty = DB::table('moraslat_categoty')->get();
                $const = array("users", "moraslat_categoty", "moraslat_attach");
                return view('dashboard.moraslat.moraslat_workall_upd', compact('moraslat', $const));

            } else if ($moraslat_type_id == 3) {
                $moraslat_id = $request->moraslat_id;
                $moraslat = DB::table('moraslat')->where('moraslat_id', $moraslat_id)->first();
                $worker_id = $moraslat->worker_id;
                if ($worker_id != '') {
                    $worker = DB::table('workers')->where('worker_id', $worker_id)->first();
                    $corr = 1;
                } else {
                    $worker = '';
                    $corr = 0;
                }
                $users = DB::table('users')->where('id', '!=', Auth::user()->id)->get();
                $moraslat_attach = DB::table('moraslat_attach')->where('moraslat_id', $moraslat_id)->get();

                $moraslat_categoty = DB::table('moraslat_categoty')->get();
                $const = array("users", "moraslat_categoty", "worker", 'corr', "moraslat_attach");
                return view('dashboard.moraslat.moraslat_workspec_upd', compact('moraslat', $const));
            }


        }

    }

    public function views()
    {
        if (Perm::get_function_access(50) || Perm::get_function_access(51) || Perm::get_function_access(52) || Perm::get_function_access(53) || Perm::get_function_access(54)) {
            $users = DB::table('users')->get();
            $moraslat_type = DB::table('moraslat_type')->get();
            $moraslat_categoty = DB::table('moraslat_categoty')->get();
            $moraslat_status = DB::table('moraslat_status')->get();
            $page_title = 'عرض المعاملات الصادرة';
            return view('dashboard.moraslat.view', compact('users', 'moraslat_type', 'moraslat_categoty', 'page_title', 'moraslat_status'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(50) || Perm::get_function_access(51) || Perm::get_function_access(52) || Perm::get_function_access(53) || Perm::get_function_access(54))) {
            return view('dashboard.moraslat.tbl_moraslat');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function sel_moraslat_list(Request $request)
    {
        $string = $request->q;
        $page = $request->page;
        $response = Moraslat::sel_moraslat_list($string, $page);
        echo json_encode($response);
    }


    public function ajax_search_moraslat(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(50) || Perm::get_function_access(51) || Perm::get_function_access(52) || Perm::get_function_access(53) || Perm::get_function_access(54))) {

            $moraslat_type_id = $request->moraslat_type_id;
            $moraslat_categoty_id = $request->moraslat_categoty_id;
            $moraslat_dt_from = $request->moraslat_dt_from;
            $moraslat_dt_to = $request->moraslat_dt_to;
            $user_id = $request->user_id;
            $worker_id = $request->worker_id;
            $shop_id = $request->shop_id;
            $moraslat_id = $request->moraslat_id;

            $moraslat_status_id = $request->moraslat_status_id;

            $list_total = Moraslat::serachspendcount($moraslat_id, $moraslat_type_id, $moraslat_categoty_id, $moraslat_dt_from, $moraslat_dt_to, $user_id, $worker_id, $shop_id,$moraslat_status_id);
            $list = Moraslat::serachspenddata($moraslat_id, $moraslat_type_id, $moraslat_categoty_id, $moraslat_dt_from, $moraslat_dt_to, $user_id, $worker_id, $shop_id,$moraslat_status_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                if ($x->is_read == '0') {
                    $is_read_desc = '<span class="ms-2 badge badge-light-danger fw-bold">غير مقروء</span>';
                } else if ($x->is_read == '1') {
                    $is_read_desc = '<span class="ms-2 badge badge-light-success fw-bold">مقروء</span>';
                    $is_read_desc .= '<br><span class="ms-2 badge badge-light-info fw-bold">' . $x->read_dt . '</span>';
                } else {
                    $is_read_desc = '<span class="ms-2 badge badge-light-info fw-bold">غير مدخل</span>';
                }
                if ($x->moraslat_status_id == '') {
                    $moraslat_status_id_desc = '<span class="ms-2 badge badge-light-info fw-bold">جديدة</span>';
                } else if ($x->moraslat_status_id == '1') {
                    $moraslat_status_id_desc = '<span class="ms-2 badge badge-light-success fw-bold">' . $x->moraslat_status_name . '</span>';
                } else if ($x->moraslat_status_id == '2') {
                    $moraslat_status_id_desc = '<span class="ms-2 badge badge-light-warning fw-bold">' . $x->moraslat_status_name . '</span>';
                } else if ($x->moraslat_status_id == '3') {
                    $moraslat_status_id_desc = '<span class="ms-2 badge badge-light-danger fw-bold">' . $x->moraslat_status_name . '</span>';
                } else {
                    $moraslat_status_id_desc = '<span class="ms-2 badge badge-light-warning fw-bold">غير مدخل</span>';
                }
                if ($x->create_user == Auth::user()->id) {
                    $type_desc = '<span class="ms-2 badge badge-light-info fw-bold">صادر</span>';
                } else if ($x->user_id == Auth::user()->id) {
                    $type_desc = '<span class="ms-2 badge badge-light-danger fw-bold">وارد</span>';
                } else {
                    $type_desc = '';
                }
                $row[] = $x->moraslat_id;
                $row[] = $x->moraslat_type_name . "<br>" . $type_desc;
                $row[] = $x->moraslat_categoty_name;
                $row[] = $x->moraslat_respon;
                $row[] = $x->emp_name;
                $row[] = $x->shop_name;
                $row[] = $x->worker_name;
                $row[] = $x->note;
                $row[] = $is_read_desc;
                $row[] = $moraslat_status_id_desc;

                $row[] = $x->name;
                $row[] = Carbon::parse($x->created_at)->format('d-m-Y');

                if (Perm::get_function_access(51) || Perm::get_function_access(52) || Perm::get_function_access(53) || Perm::get_function_access(54)) {

                    $opt = '<div class="btn-group btn-group-sm " role="group">';

                    if (Perm::get_function_access(53)) {
                        // if ($x->user_id == Auth::user()->id) {
                        //     $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  open_moraslat" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.moraslat.open_moraslat') . "'" . ' onclick="open_moraslat(' . "'" . $x->moraslat_id . "'" . ')"> <i class="fas fa-folder-open fa-fw"></i></a>';
                        // }

                        if ($x->user_id == Auth::user()->id) {
                            // if (($x->is_read == '0' and $x->moraslat_status_id =='') ||($x->is_read == '0' and $x->moraslat_status_id =='3') ||  ($x->is_read == '1' and  $x->moraslat_status_id !='1')) {
if ((($x->is_read == '0'||$x->is_read == '1') and $x->moraslat_status_id =='') ||($x->is_read == '0' and $x->moraslat_status_id =='3')||  ($x->is_read == '1' and  $x->moraslat_status_id =='2')) {

                                $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  open_moraslat" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.moraslat.open_moraslat') . "'" . ' onclick="open_moraslat(' . "'" . $x->moraslat_id . "'" . ')"> <i class="fas fa-folder-open fa-fw"></i></a>';
                        }
                    }

                    if ($x->create_user  == Auth::user()->id) {
                        if ($x->moraslat_status_id == '3' and  ($x->is_read == '0' || $x->is_read == '1') ) {
                            $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  open_moraslat" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.moraslat.open_moraslat') . "'" . ' onclick="open_moraslat(' . "'" . $x->moraslat_id . "'" . ')"> <i class="fas fa-folder-open fa-fw"></i></a>';
                    }
                }

                    }

                    if (Perm::get_function_access(51)) {
                        if (($x->is_read == '0'||$x->moraslat_status_id == '3') and $x->create_user  == Auth::user()->id) {
                            $opt .= '<a class="btn btn-sm btn-info btn-icon btn-icon-sm  upd_moraslat" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.moraslat.upd_moraslat') . "'" . ' onclick="upd_moraslat(' . "'" . $x->moraslat_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                        }
                    }

                    if (Perm::get_function_access(52)) {
                        if ($x->is_read == '0' and $x->create_user  == Auth::user()->id) {
                            $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_moraslat(' . "'" . $x->moraslat_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
                        }
                    }

                    if (Perm::get_function_access(54)) {
                       // if ($x->is_read == '1') {
                            $opt .= '<a class="btn btn-sm btn-warning btn-icon btn-icon-sm  show_history" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.moraslat.show_history') . "'" . ' onclick="show_history(' . "'" . $x->moraslat_id . "'" . ')"> <i class="fas fa-history fa-fw"></i></a>';
                        //}
                    }

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

    function del_moraslat(Request $request)
    {
        if (Perm::get_function_access(52)) {

            $id = $request->id;
            try {
                $delete = DB::delete('delete from Moraslat where moraslat_id = ?', [$id]);
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


    public function upd_moraslat(Request $request)
    {
        if (Perm::get_function_access(51)) {
            $id = $request->id;
            $moraslat = DB::table('moraslat')->where('moraslat_id', $id)->first();
            $sub_add_moraslat = "1";
            $page_title = 'تعديل بيانات العمال';
            $users = DB::table('users')->where('id', '!=', Auth::user()->id)->get();
            $moraslat_type = DB::table('moraslat_type')->get();
            $moraslat_categoty = DB::table('moraslat_categoty')->get();
            $const = array("users", "moraslat_type", "moraslat_categoty", "page_title");
            return view('dashboard.moraslat.upd_moraslat', compact('moraslat', $const));
        }
    }


    public function show_history(Request $request)
    {
        if (Perm::get_function_access(54)) {
            $id = $request->id;
            $moraslat_history = DB::table('moraslat_history')
                ->leftJoin('users', 'moraslat_history.change_user', '=', 'users.id')
                ->leftJoin('moraslat_status', 'moraslat_history.moraslat_status_id', '=', 'moraslat_status.moraslat_status_id')
                ->select('moraslat_history.*', 'users.name as emp_name', 'moraslat_status.moraslat_status_name')
                ->where('moraslat_history.moraslat_id', $id)->get();
            $page_title = 'عرض سجلات المعاملة';
            $const = array("page_title");
            return view('dashboard.moraslat.show_history', compact('moraslat_history', $const));
        }
    }


    public function open_moraslat(Request $request)
    {
        if (Perm::get_function_access(53)) {
            $id = $request->id;
            $moraslat_attach = DB::table('moraslat_attach')->where('moraslat_id', $id)->get();
            $moraslat = DB::table('moraslat')
                ->leftJoin('users', 'moraslat.user_id', '=', 'users.id')
                ->leftJoin('moraslat_type', 'moraslat.moraslat_type_id', '=', 'moraslat_type.moraslat_type_id')
                ->leftJoin('moraslat_categoty', 'moraslat.moraslat_categoty_id', '=', 'moraslat_categoty.moraslat_categoty_id')
                ->leftJoin('shop', 'moraslat.shop_id', '=', 'shop.shop_id')
                ->leftJoin('workers', 'moraslat.worker_id', '=', 'workers.worker_id')
                ->select('moraslat.*', 'users.name as emp_name', 'moraslat_type.moraslat_type_name', 'moraslat_categoty.moraslat_categoty_name', 'shop.shop_name', 'workers.worker_name')
                ->where('moraslat.moraslat_id', $id)->first();
            $sub_add_moraslat = "1";
            $page_title = 'فتح المراسلة';
            $user_id_db = $moraslat->user_id;
           // $users = DB::table('users')->where('id', '!=', Auth::user()->id)->get();
//dd($moraslat);





            if ($user_id_db == Auth::user()->id) {
            $users = DB::table('users')->where('id', '=', $moraslat->create_user)->get();
            }
            else{
                $users = DB::table('users')->where('id', '=', $moraslat->user_id)->get();

            }

            if ($user_id_db == Auth::user()->id) {
                $is_read = 1;
                $read_dt = Carbon::now();
            } else {
                $is_read = 0;
                $read_dt = '';
            }
            $result2 = DB::table('moraslat')
                ->where('moraslat_id', $id)
                ->update([
                    'is_read' => $is_read,
                    'read_dt' => $read_dt,
                ]);
            $moraslat_status = DB::table('moraslat_status')->get();
            $const = array("users", "moraslat_status", "page_title");
            return view('dashboard.moraslat.open_moraslat', compact('moraslat', 'moraslat_attach', $const));
        }
    }


    public function store(Request $request)
    {
        if (Perm::get_function_access(49)) {
            $moraslat_name_old = $request->old('moraslat_name');
            $attributeNames = array(
                'moraslat_type_id' => 'نوع المراسلة',
                'moraslat_categoty_id' => 'درجة الأهمية',
                'moraslat_respon' => 'نص المعاملة',
                'shop_id' => 'اسم المحل',
                'worker_id' => 'اسم العامل',
                'user_id' => 'توجية الى ',
            );
            $validator = Validator::make($request->all(), [
                'moraslat_type_id' => ['required', 'string'],
                'moraslat_respon' => ['required'],
                'user_id' => ['required'],
                'moraslat_categoty_id' => ['required'],
                'shop_id' => [Rule::requiredIf($request->moraslat_type_id == 1), 'nullable'],
                'worker_id' => [Rule::requiredIf($request->moraslat_type_id == 3), 'nullable']
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $result2 = DB::table('moraslat')->insertGetId([
                    'moraslat_type_id' => $request->moraslat_type_id,
                    'moraslat_categoty_id' => $request->moraslat_categoty_id,
                    'shop_id' => $request->shop_id,
                    'worker_id' => $request->worker_id,
                    'moraslat_respon' => $request->moraslat_respon,
                    'user_id' => $request->user_id,
                    'note' => $request->note,
                    'created_at' => Carbon::now(),
                    'create_user' => Auth::user()->id,
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
                            $result_upload = DB::table('moraslat_attach')->insertGetId([
                                'moraslat_id' => $result2,
                                'moraslat_attach_name' => $orginal_name,
                                'moraslat_attach_url' => $file_url,
                                'moraslat_attach_extension' => $ext,

                            ]);
                        }
                    }
                    $result['status'] = $result2;
                    $result['message_out'] = 'تم الحفظ بنجاح';
                } else {
                    if (File::exists($Moraslatfile_url)) {
                        File::delete($Moraslatfile_url);
                    }
                    $message = 'لا يمكن الحفظ';
                    $result['status'] = false;
                    $result['message_out'] = $message;
                }
            }
            return response()->json($result);
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }



    public function delete_file(Request $request)
    {

        $moraslat_id = $request->moraslat_id;
        $Moraslatfile_url = $request->Moraslatfile_url;
        $type = $request->type;
        if ($type == 'moraslat_attach') {
            if (File::exists($Moraslatfile_url)) {
                File::delete($Moraslatfile_url);


            }

            $result2 = DB::table('moraslat_attach')->where('moraslat_attach_id', $moraslat_id)->delete();
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }


        if ($type == 'Moraslatfile') {
            if (File::exists($Moraslatfile_url)) {
                File::delete($Moraslatfile_url);


            }
            $result2 = DB::table('moraslat')
                ->where('moraslat_id', $moraslat_id)
                ->update([
                    'Moraslatfile' => '',
                ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }

        if ($type == 'passportfile') {
            if (File::exists($Moraslatfile_url)) {
                File::delete($Moraslatfile_url);
            }
            $result2 = DB::table('moraslat')
                ->where('moraslat_id', $moraslat_id)
                ->update([
                    'passportfile' => '',
                ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }


    }


    public function updstore(Request $request)
    {
        if (Perm::get_function_access(51)) {

            $id = $request->moraslat_id_db;
            $attributeNames = array(
                'moraslat_type_id' => 'نوع المراسلة',
                'moraslat_categoty_id' => 'درجة الأهمية',
                'moraslat_respon' => 'نص المعاملة',
                'shop_id' => 'اسم المحل',
                'worker_id' => 'اسم العامل',
                'user_id' => 'توجية الى ',
            );
            $validator = Validator::make($request->all(), [
                'moraslat_type_id' => ['required', 'string'],
                'moraslat_respon' => ['required'],
                'user_id' => ['required'],
                'moraslat_categoty_id' => ['required'],
                'shop_id' => [Rule::requiredIf($request->moraslat_type_id == 1), 'nullable'],
                'worker_id' => [Rule::requiredIf($request->moraslat_type_id == 3), 'nullable']
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;


                $result2 = DB::table('moraslat')
                    ->where('moraslat_id', $id)
                    ->update([
                        'moraslat_type_id' => $request->moraslat_type_id,
                        'moraslat_categoty_id' => $request->moraslat_categoty_id,
                        'shop_id' => $request->shop_id,
                        'worker_id' => $request->worker_id,
                        'moraslat_respon' => $request->moraslat_respon,
                        'user_id' => $request->user_id,
                        'note' => $request->note,
                        'updated_at' => Carbon::now(),
                        'update_user' => Auth::user()->id,
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
                            $result2 = DB::table('moraslat_attach')
                                ->where('moraslat_attach_id', $request->emp_att_id[$key])
                                ->update([
                                    'moraslat_attach_name' => $orginal_name,
                                    'moraslat_attach_url' => $file_url,
                                    'moraslat_attach_extension' => $ext,
                                ]);
                            if (File::exists($request->image_url_emp[$key])) {
                                File::delete($request->image_url_emp[$key]);
                            }
                            $result2 = 1;

                        } else {

                            $result_upload = DB::table('moraslat_attach')->insertGetId([
                                'moraslat_id' => $id,
                                'moraslat_attach_name' => $orginal_name,
                                'moraslat_attach_url' => $file_url,
                                'moraslat_attach_extension' => $ext,

                            ]);
                        }


                    }
                }
                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';
            }
            return response()->json($result);
        }
    }


    public function updopenstore(Request $request)
    {
        $id = $request->moraslat_id;
        $user_id_db = $request->user_id_db;
        $attributeNames = array(
            'moraslat_status_id' => 'نوع الاجراء',
            'status_note' => 'ملاحظات',
            'return_desc' => 'سبب الارجاع',
            'user_id' => ' توجية الى ',

        );
        $validator = Validator::make($request->all(), [
            'moraslat_status_id' => ['required'],
            'status_note' => ['required'],
            'moraslat_id' => ['required'],
            'return_desc' => [Rule::requiredIf($request->moraslat_status_id == 3)],
            'user_id' => [Rule::requiredIf($request->moraslat_status_id == 3)],

        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
        } else {
            $ERROR_FLAG = 0;

            if ($user_id_db == Auth::user()->id) {
                $is_read = 1;
                $read_dt = Carbon::now();
            } else {
                $is_read = 0;
                $read_dt = '';
            }

            $result2 = DB::table('moraslat')
                ->where('moraslat_id', $id)
                ->update([
                    'moraslat_status_id' => $request->moraslat_status_id,
                    'status_note' => $request->status_note,
                    'status_dt' => Carbon::now(),
                    'is_read' => $is_read,
                    'read_dt' => $read_dt,

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
                        $result2 = DB::table('moraslat_attach')
                            ->where('moraslat_attach_id', $request->emp_att_id[$key])
                            ->update([
                                'moraslat_attach_name' => $orginal_name,
                                'moraslat_attach_url' => $file_url,
                                'moraslat_attach_extension' => $ext,
                            ]);
                        if (File::exists($request->image_url_emp[$key])) {
                            File::delete($request->image_url_emp[$key]);
                        }
                        $result2 = 1;

                    } else {

                        $result_upload = DB::table('moraslat_attach')->insertGetId([
                            'moraslat_id' => $id,
                            'moraslat_attach_name' => $orginal_name,
                            'moraslat_attach_url' => $file_url,
                            'moraslat_attach_extension' => $ext,

                        ]);
                    }


                }
            }


            $result3 = DB::table('moraslat_history')->insertGetId([
                'moraslat_id' => $id,
                'change_user' => Auth::user()->id,
                'change_at' => Carbon::now(),
                'moraslat_status_id' => $request->moraslat_status_id,
                'status_note' => $request->status_note,
                'status_dt' => Carbon::now()
            ]);
            $result['url_notify_count'] = route('notify_num');

            $result['status'] = $result2;
            $result['message_out'] = 'تم الحفظ بنجاح';
        }
        return response()->json($result);

    }


}
