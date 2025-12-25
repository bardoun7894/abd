<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Violation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;

class ViolationController extends Controller
{
    use ApimtitTrait;

    public function __construct()
    {
        $this->middleware('ishaveaccess:12');
    }

    public function index()
    {
        if (Perm::get_function_access(76)) {
            $page_title = 'إضافة مخالفة محل';
            $sel_violation = array("page_title");
            $violation_side = DB::table('violation_side')->get();
            return view('dashboard.violation.index', compact('violation_side', $sel_violation));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function views()
    {
        if (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79)) {
            $manager = $this->get_manager();
            $page_title = 'إدارة مخالفات المحلات';
            return view('dashboard.violation.view', compact('manager', 'page_title'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79))) {
            return view('dashboard.violation.tbl_violation');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_violation(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79))) {
            $shop_id = $request->shop_id;
            $manager_id = $request->manager_id;
            $violation_month_desc = $request->violation_month_desc;
            if ($violation_month_desc != '') {
                $array_name = explode("-", $violation_month_desc);
                $violation_month_m = $array_name[0];
                $violation_month_y = $array_name[1];
            } else {
                $violation_month_m = '';
                $violation_month_y = '';
            }

            $violation_no = $request->violation_no;
            $violation_ispay = $request->violation_ispay;
            $comme_no = $request->comme_no;
            $municip_no = $request->municip_no;
            $shop_respon = $request->shop_respon;

            $list_totl = Violation::sumspendcountdesc('', $violation_month_m, $violation_month_y, $shop_id, $manager_id, $violation_no, $violation_ispay,
                $comme_no, $municip_no, $shop_respon);
            $violation_val_all_pay = 0;
            $violation_val_pay = 0;
            $violation_val_not_pay = 0;
            foreach ($list_totl as $x_sum) {
                $violation_val_all_pay = $x_sum->violation_val_all_pay;
                $violation_val_pay = $x_sum->violation_val_pay;
                $violation_val_not_pay = $x_sum->violation_val_not_pay;
            }
            $list_total = Violation::serachspendcountdesc($violation_month_m, $violation_month_y, $shop_id, $manager_id, $violation_no, $violation_ispay,
                $comme_no, $municip_no, $shop_respon);
            $list = Violation::serachspenddatadesc($violation_month_m, $violation_month_y, $shop_id, $manager_id, $violation_no, $violation_ispay,
                $comme_no, $municip_no, $shop_respon);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            $sum_violation_month_val = 0;
            $sum_sum_det_violation_month_pay = 0;
            $sum_sum_det_violation_month_remain = 0;

            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                if ($x->violation_ispay == '1') {
                    $violation_desc = '<span class="ms-2 badge badge-light-success fw-bold">مدفوع</span>';
                } else {
                    $violation_desc = '<span class="ms-2 badge badge-light-danger fw-bold">غير مدفوع</span>';
                }


                if ($x->violation_id != '') {
                    $violation_no= '<span class="ms-2 text-info fw-bold">' . $x->violation_no . '</span>';
                } else {
                    $violation_no= '';
                }
                $row[] = $i;
                $row[] = $violation_no;

                $row[] = $x->shop_name;
                $row[] = $x->manager_name;
                $row[] = Carbon::parse($x->violation_dt)->format('d-m-Y');
                $row[] = $x->violation_val;
                $row[] = $violation_desc;
                $row[] = $x->violation_side_name;
                $row[] = $x->violation_cause;
                $row[] = $x->shop_respon;
                $row[] = $x->comme_no;
                $row[] = $x->municip_no;
                $row[] = $x->name . "<br>" . Carbon::parse($x->created_at)->format('d-m-Y');
                if (Perm::get_function_access(78) || Perm::get_function_access(79)) {
                    $opt = '<div class="btn-group btn-group-sm " role="group"  >';
                    if (Perm::get_function_access(78)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_violation" style="margin-left: .5rem;"    data-url=' . "'" . route('dashboard.violation.upd_violation') . "'" . ' onclick="upd_violation(' . "'" . $x->violation_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(79)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-left: .5rem;" onclick="del_violation(' . "'" . $x->violation_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
                    }

$opt .= '<a class="btn btn-sm btn-dark btn-icon btn-icon-sm  violation_note_history" style="margin-left: .5rem;"    data-url=' . "'" . route('dashboard.violation.violation_note_history') . "'" . ' onclick="violation_note_history(' . "'" . $x->violation_id . "'" . ')"> <i class="fas fa-history fa-fw "></i></a>';



                    $opt .= '</div>';
                    $row[] = $opt;
                }
                $data[] = $row;
            }
            $output = array(
                "violation_val_all_pay" => $violation_val_all_pay,
                "violation_val_pay" => $violation_val_pay,
                "violation_val_not_pay" => $violation_val_not_pay,
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data);
            echo json_encode($output);
        }
    }


    public function violation_note_history(Request $request)
    {
        if (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79)) {
            $id = $request->id;

                $violation = DB::table('violation')->where('violation_id', $id)->first();
                // $violation_no = $violation->violation_no;
                // $violation_dt = $violation->violation_dt;
                // $violation_val = $violation->violation_val;
                // $violation_ispay = $violation->violation_ispay;
                // $violation_cause = $violation->violation_cause;



                $sub_add_worker = "1";
                $page_title = 'إنشاء ملف ';
                $note_type = DB::table('note_type')->get();
                $const = array("note_type", "page_title");
                return view('dashboard.violation.violation_note_history', compact('violation', $const));

        }
    }

    public function tbl_history(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79))) {
            return view('dashboard.violation.tbl_history');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_history(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(77) || Perm::get_function_access(78) || Perm::get_function_access(79))) {

            $violation_id = $request->violation_id;

            $list_total = Violation::serachhistorycount($violation_id);
            $list = Violation::serachhistorydata($violation_id);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;

                if ($x->violation_ispay == '1') {
                    $violation_desc = '<span class="ms-2 badge badge-light-success fw-bold">مدفوع</span>';
                } else {
                    $violation_desc = '<span class="ms-2 badge badge-light-danger fw-bold">غير مدفوع</span>';
                }

                if ($x->old_violation_ispay == '1') {
                    $old_violation_desc = '<span class="ms-2 badge badge-light-success fw-bold">مدفوع</span>';
                } else {
                    $old_violation_desc = '<span class="ms-2 badge badge-light-danger fw-bold">غير مدفوع</span>';
                }
                $row = array();

                $row[] = $i;

                $row[] = $x->violation_val;
                $row[] = $x->old_violation_val;

                $row[] = $violation_desc;
                $row[] = $old_violation_desc;

                $row[] = $x->violation_no;
                $row[] = $x->old_violation_no;

                $row[] = $x->violation_dt;
                $row[] = $x->old_violation_dt;


                $row[] = $x->violation_cause;
                $row[] = $x->old_violation_cause;



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



    function del_violation(Request $request)
    {
        if (Perm::get_function_access(79)) {
            $id = $request->id;
            $violation = DB::table('violation')->where('violation_id', $id)->first();
            $old_violation_no = $violation->violation_no;
            $old_violation_side_id = $violation->violation_side_id;
            $old_violation_dt = $violation->violation_dt;
            $old_violation_val = $violation->violation_val;
            $old_violation_ispay = $violation->violation_ispay;
            $old_violation_cause = $violation->violation_cause;
            $old_updated_at = $violation->updated_at;
            $old_updated_user = $violation->updated_user;
            $old_is_deleted = $violation->is_deleted;

            $delete = DB::table('violation')
                ->where('violation_id', $id)
                ->update([
                    'is_deleted' => 1,
                    'deleted_at' => Carbon::now(),
                    'deleted_user' => Auth::user()->id,
                ]);
            if ($delete) {
                $result['status'] = true;
                $result['message'] = 'تم';



                $result22 = DB::table('violation_history')->insertGetId([
                    'violation_id' => $id,
                    'violation_no' => $request->violation_no,
                    'violation_side_id' => $request->violation_side_id,
                    'violation_dt' => $request->violation_dt,
                    'violation_val' => $request->violation_val,
                    'violation_ispay' => $request->violation_ispay ? 1 : 0,
                    'violation_cause' => $request->violation_cause,
                    'change_user' => Auth::user()->id,
                    'change_at' => Carbon::now(),
                    'old_violation_no' => $old_violation_no,
                    'old_violation_side_id' => $old_violation_side_id,
                    'old_violation_dt' => $old_violation_dt,
                    'old_violation_val' => $old_violation_val,
                    'old_violation_ispay' => $old_violation_ispay,
                    'old_violation_cause' => $old_violation_cause,
                    'old_updated_at' => $old_updated_at,
                    'old_updated_user' => $old_updated_user,
                    'old_is_deleted' => $old_is_deleted,
                    'is_deleted' => 1,
                ]);





            } else {
                $message = 'لا يمكن الحذف';
                $result['status'] = false;
                $result['message'] = $message;
            }
            echo json_encode($result);
        }
    }


    public function upd_violation(Request $request)
    {
        if (Perm::get_function_access(78)) {
            $id = $request->id;
            $violation = DB::table('violation')->where('violation_id', $id)->first();
            $shop_id = $violation->shop_id;
            $shop = DB::table('shop')->where('shop_id', $shop_id)->first();
            $violation_side = DB::table('violation_side')->get();
            $violation_attach = DB::table('violation_attach')->where('violation_id', $id)->get();
            return view('dashboard.violation.upd_violation', compact('violation', 'shop', 'violation_side', 'violation_attach'));
        }
    }


    public function store(Request $request)
    {
        if (Perm::get_function_access(76)) {
            $shop_id = $request->shop_id;
            $attributeNames = array(
                'shop_id' => 'اسم المحل',
                'violation_no' => 'رقم المخالفة',
                'violation_dt' => 'تاريخ المخالفة',
                'violation_val' => 'قيمة المخالفة',
                'files.*' => 'نوع الملف',
                'violation_ispay' => 'هل تم دفعة المخالفة',
            );
            $validator = Validator::make($request->all(), [
                'shop_id' => ['required', 'integer'],
                'violation_dt' => ['required', 'date'],
                'files.*' => ['nullable', 'file', 'mimes:csv,txt,xlx,xlsx,xls,pdf,ppt,pptx,doc,docx,xlsx,jpg,jpeg,bmp,png,rtf,zip'],
                'violation_val' => ['required', 'numeric'],
                'violation_no' => ['required', 'unique:violation'],

            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $violation_id = DB::table('violation')->insertGetId([
                    'shop_id' => $request->shop_id,
                    'violation_no' => $request->violation_no,
                    'violation_side_id' => $request->violation_side_id,
                    'violation_dt' => $request->violation_dt,
                    'violation_val' => $request->violation_val,
                    'violation_ispay' => $request->violation_ispay ? 1 : 0,
                    'violation_cause' => $request->violation_cause,
                    'created_at' => Carbon::now(),
                    'create_user' => Auth::user()->id,
                ]);
                if ($violation_id != '') {
                    $file_url = '';
                    if ($request->hasfile('files')) {
                        foreach ($request->file('files') as $key => $file) {
                            $orginal_name = $file->getClientOriginalName();
                            $ext = $file->extension();
                            $fileName = time() . rand(1, 99) . '.' . $file->extension();
                            $file->move(public_path('uploads/mol/'), $fileName);
                            $file_url = 'uploads/mol/' . $fileName;

                            $result_upload = DB::table('violation_attach')->insertGetId([
                                'violation_id' => $violation_id,
                                'violation_attach_name' => $orginal_name,
                                'violation_attach_url' => $file_url,
                                'violation_attach_extension' => $ext,

                            ]);
                        }
                    }
                    $ERROR_FLAG = 0;
                    $result['status'] = $violation_id;
                    $result['message_out'] = 'تم الحفظ بنجاح';
                } else {
                    $result['status'] = false;
                    $result['message_out'] = 'لم يتم الحفظ';

                }
            }

            return response()->json($result);

        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function updstore(Request $request)
    {
        if (Perm::get_function_access(78)) {
            $violation_id = $request->violation_id_db;

            $attributeNames = array(
                'shop_id' => 'اسم المحل',
                'violation_no' => 'رقم المخالفة',
                'violation_dt' => 'تاريخ المخالفة',
                'violation_val' => 'قيمة المخالفة',
                'files.*' => 'نوع الملف',
                'violation_ispay' => 'هل تم دفعة المخالفة',
            );
            $validator = Validator::make($request->all(), [
                'shop_id' => ['required', 'integer'],
                'violation_dt' => ['required', 'date'],
                'files.*' => ['nullable', 'file', 'mimes:csv,txt,xlx,xlsx,xls,pdf,ppt,pptx,doc,docx,xlsx,jpg,jpeg,bmp,png,rtf,zip'],
                'violation_val' => ['required', 'numeric'],
                'violation_no' => ['required', 'unique:violation,violation_no,'.$violation_id.",violation_id"],


            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';

            } else {

                $violation_history = DB::table('violation')->where('violation_id', $violation_id)->first();
                $old_violation_no = $violation_history->violation_no;
                $old_violation_side_id = $violation_history->violation_side_id;
                $old_violation_dt = $violation_history->violation_dt;
                $old_violation_val = $violation_history->violation_val;
                $old_violation_ispay = $violation_history->violation_ispay;
                $old_violation_cause = $violation_history->violation_cause;
                $old_updated_at = $violation_history->updated_at;
                $old_updated_user = $violation_history->updated_user;
                $old_is_deleted = $violation_history->is_deleted;


                $result2 = DB::table('violation')
                    ->where('violation_id', $violation_id)
                    ->update([
                        'shop_id' => $request->shop_id,
                        'violation_no' => $request->violation_no,
                        'violation_side_id' => $request->violation_side_id,
                        'violation_dt' => $request->violation_dt,
                        'violation_val' => $request->violation_val,
                        'violation_ispay' => $request->violation_ispay ? 1 : 0,
                        'violation_cause' => $request->violation_cause,
                        'updated_at' => Carbon::now(),
                        'updated_user' => Auth::user()->id,

                    ]);




                    $result22 = DB::table('violation_history')->insertGetId([
                        'violation_id' => $violation_id,
                        'violation_no' => $request->violation_no,
                        'violation_side_id' => $request->violation_side_id,
                        'violation_dt' => $request->violation_dt,
                        'violation_val' => $request->violation_val,
                        'violation_ispay' => $request->violation_ispay ? 1 : 0,
                        'violation_cause' => $request->violation_cause,
                        'change_user' => Auth::user()->id,
                        'change_at' => Carbon::now(),
                         'old_violation_no' => $old_violation_no,
                        'old_violation_side_id' => $old_violation_side_id,
                        'old_violation_dt' => $old_violation_dt,
                        'old_violation_val' => $old_violation_val,
                        'old_violation_ispay' => $old_violation_ispay,
                        'old_violation_cause' => $old_violation_cause,
                        'old_updated_at' => $old_updated_at,
                        'old_updated_user' => $old_updated_user,
                        'old_is_deleted' => $old_is_deleted,

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
                            $result2 = DB::table('violation_attach')
                                ->where('violation_attach_id', $request->emp_att_id[$key])
                                ->update([
                                    'violation_attach_name' => $orginal_name,
                                    'violation_attach_url' => $file_url,
                                    'violation_attach_extension' => $ext,
                                ]);
                            if (File::exists($request->image_url_emp[$key])) {
                                File::delete($request->image_url_emp[$key]);
                            }
                            $result2 = 1;

                        } else {

                            $result_upload = DB::table('violation_attach')->insertGetId([
                                'violation_id' => $violation_id,
                                'violation_attach_name' => $orginal_name,
                                'violation_attach_url' => $file_url,
                                'violation_attach_extension' => $ext,

                            ]);
                        }


                    }
                }

                $ERROR_FLAG = 0;
                $result['status'] = 1;
                $result['message_out'] = 'تم الحفظ بنجاح';
            }


            return response()->json($result);

        }
    }
}
