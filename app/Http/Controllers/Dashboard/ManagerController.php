<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Manager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;


class ManagerController extends Controller
{
    use ApimtitTrait;

    public function __construct()
    {
        $this->middleware('ishaveaccess:7');
    }



    public function index()
    {
        if (Perm::get_function_access(45)) {
            $page_title = 'ادخال بيانات المجموعة';
        $manager = DB::table('manager')->get();
        $city = DB::table('city')->get();
        $const = array("manager", "city",  "page_title");
        return view('dashboard.manager.index', compact($const));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function views()
    {
        if(Perm::get_function_access(46) || Perm::get_function_access(47) || Perm::get_function_access(48)){
        $manager = DB::table('manager')->get();
        $city = DB::table('city')->get();
        $page_title = 'عرض بيانات المجموعة';
        return view('dashboard.manager.view', compact('manager', 'city', 'page_title'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(46) || Perm::get_function_access(47) || Perm::get_function_access(48))) {
            return view('dashboard.manager.tbl_manager');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function sel_manager_list(Request $request)
    {
        $string = $request->q;
        $page = $request->page;
        //   $job= $request->job;
        $response = manager::sel_manager_list($string, $page);
        //   dd($response);
        echo json_encode($response);
    }


    public function ajax_search_manager(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(46) || Perm::get_function_access(47) || Perm::get_function_access(48))) {

            $manager_name = $request->manager_name;
            $manager_mobile = $request->manager_mobile;
            $list_total = manager::serachspendcount($manager_name, $manager_mobile);
            $list = manager::serachspenddata($manager_name, $manager_mobile);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                $row[] = $i;
                $row[] = $x->manager_name;
                $row[] = $x->manager_mobile;
                $row[] = $x->note;
                $row[] = Carbon::parse($x->created_at)->format('d-m-Y');
                if( Perm::get_function_access(47) || Perm::get_function_access(48)) {

                    $opt = '<div class="btn-group btn-group-sm " role="group">';
                    if (Perm::get_function_access(47)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_manager" style="margin-left: .5rem;" data-url=' . "'" . route('dashboard.manager.upd_manager') . "'" . ' onclick="upd_manager(' . "'" . $x->manager_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }

                    if (Perm::get_function_access(48)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-left: .5rem;" onclick="del_manager(' . "'" . $x->manager_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
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


    function del_manager(Request $request)
    {
        if (Perm::get_function_access(48)) {

            $id = $request->id;
            try {
                $delete = DB::delete('delete from manager where manager_id = ?', [$id]);
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


    public function upd_manager(Request $request)
    {
        if (Perm::get_function_access(47)) {

            $id = $request->id;
            $manager = DB::table('manager')->where('manager_id', $id)->first();
//$manager_attach =DB::table('manager_attach')->where('manager_id',$id)->get();
            $sub_add_manager = "1";
            $page_title = 'تعديل بيانات المجموعة';
            $const = array("page_title");
            return view('dashboard.manager.upd_manager', compact('manager', $const));
        }
    }


    public function store(Request $request)
    {
        if (Perm::get_function_access(45)) {

            $manager_name_old = $request->old('manager_name');
            $attributeNames = array(
                'manager_name' => 'اسم القائد',
                'manager_mobile' => 'رقم جوال القائد',
            );
            $validator = Validator::make($request->all(), [
                'manager_name' => ['required', 'string'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $result2 = DB::table('manager')->insertGetId([
                    'manager_name' => $request->manager_name,
                    'manager_mobile' => $request->manager_mobile,
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

        $manager_id = $request->manager_id;
        $ssnfile_url = $request->ssnfile_url;
        $type = $request->type;


        if ($type == 'manager_attach') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);


            }

            $result2 = DB::table('manager_attach')->where('manager_attach_id', $manager_id)->delete();
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }


        if ($type == 'ssnfile') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);


            }
            $result2 = DB::table('manager')
                ->where('manager_id', $manager_id)
                ->update([
                    'ssnfile' => '',
                ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }

        if ($type == 'passportfile') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result2 = DB::table('manager')
                ->where('manager_id', $manager_id)
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
        if (Perm::get_function_access(47)) {

            $id = $request->manager_id_db;
            $manager_name = $request->old('manager_name');

            $attributeNames = array(
                'manager_name' => 'اسم القائد',
                'manager_mobile' => 'رقم جوال القائد',
            );
            $validator = Validator::make($request->all(), [
                'manager_name' => ['required', 'string'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;


                $result2 = DB::table('manager')
                    ->where('manager_id', $id)
                    ->update([
                        'manager_name' => $request->manager_name,
                        'manager_mobile' => $request->manager_mobile,
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


}
