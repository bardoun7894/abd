<?php

namespace App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Workers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use Carbon\Carbon;
use Perm;
use PDF;
use DateTime;
use App\Http\Traits\ApimtitTrait;
class WorkersController extends Controller
{

    use ApimtitTrait;

    public function __construct()
    {
        $this->middleware('ishaveaccess:2');
        //Perm::get_controll_access(2);
        //Perm::get_controll_access(2);
        //dd(Auth::id());
        /*
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
        $this->user = Auth::user();
        dd(Auth::id());
        return $next($request);
        });*/
    }

    public function import()
    {
        if (Perm::get_function_access(72)) {
            $page_title = 'استيراد ملف';
            $const = array("page_title");
            return view('dashboard.workers.import', compact($const));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function impfile(Request $request)
    {
        if (Perm::get_function_access(72)) {
            try {
                if ($request->hasFile('uploadFile')) {
                    $file = $request->file('uploadFile');

                    if (!$file->isValid()) {
                        return response()->json([
                            'status' => false,
                            'message' => ['error' => 'الملف غير صالح'],
                            'message_out' => 'الرجاء المحاولة مرة أخرى'
                        ]);
                    }

                    $extension = strtolower($file->getClientOriginalExtension());
                    $imageName = time() . '.' . $extension;
                    $path = $file->move(public_path('uploads/users/images/'), $imageName);
                    $inputFileName = public_path('uploads/users/images/' . $imageName);

                    try {
                        $reader = ($extension == 'csv')
                            ? \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv')
                            : \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

                        $reader->setReadDataOnly(true);
                        $spreadsheet = $reader->load($inputFileName);
                        $worksheet = $spreadsheet->getActiveSheet();
                        $allDataInSheet = $worksheet->toArray(null, true, true, true);

                        // تجاهل الصف الأول (العناوين)
                        $headers = array_shift($allDataInSheet);

                        $successCount = 0;
                        $errorCount = 0;
                        $errors = [];

                        foreach ($allDataInSheet as $row) {
                            try {
                                // التحقق من البيانات المطلوبة
                                if (empty($row['A']) || empty($row['B'])) {
                                    $errorCount++;
                                    continue;
                                }

                                // معالجة التواريخ
                                $dop = !empty($row['G']) ? Carbon::parse($row['G'])->format('Y-m-d') : null;
                                $dos = !empty($row['H']) ? Carbon::parse($row['H'])->format('Y-m-d') : null;
                                $doe = !empty($row['I']) ? Carbon::parse($row['I'])->format('Y-m-d') : null;
                                $dob = !empty($row['J']) ? Carbon::parse($row['J'])->format('Y-m-d') : null;

                                // معالجة الجنسية
                                $nation = DB::table('nation')
                                    ->where('nation_name_ar', $row['D'])
                                    ->first();

                                if (!$nation) {
                                    $nation_id = DB::table('nation')->insertGetId([
                                        'nation_name_ar' => $row['D']
                                    ]);
                                } else {
                                    $nation_id = $nation->nation_id;
                                }

                                // معالجة المهنة
                                $job = DB::table('job')
                                    ->where('job_name', $row['E'])
                                    ->first();

                                if (!$job) {
                                    $job_id = DB::table('job')->insertGetId([
                                        'job_name' => $row['E']
                                    ]);
                        } else {
                                    $job_id = $job->job_id;
                                }

                                // إدخال أو تحديث بيانات العامل
                                $worker = DB::table('workers')
                                    ->where('ssn', $row['A'])
                                    ->first();

                                $workerData = [
                                    'worker_name' => $row['B'],
                                    'ssn' => $row['A'],
                                    'nation_id' => $nation_id,
                                    'passport_no' => $row['F'],
                                    'dop' => $dop,
                                    'dos' => $dos,
                                    'doe' => $doe,
                                    'dob' => $dob,
                                    'inside' => strtolower($row['K']) == 'نعم' ? 0 : 1,
                                    'end_can_hij' => $row['L'],
                                    'job_id' => $job_id,
                                    'is_imp' => 1,
                                    'imp_user' => Auth::user()->id,
                                    'imp_dt' => Carbon::now(),
                                    'emp_file' => $inputFileName
                                ];

                                if ($worker) {
                                    DB::table('workers')
                                        ->where('worker_id', $worker->worker_id)
                                        ->update($workerData + [
                                    'updated_at' => Carbon::now(),
                                            'update_user' => Auth::user()->id
                                ]);
                        } else {
                                    DB::table('workers')->insert($workerData + [
                                'created_at' => Carbon::now(),
                                        'create_user' => Auth::user()->id
                                    ]);
                                }

                                $successCount++;
                            } catch (\Exception $e) {
                                $errorCount++;
                                $errors[] = "خطأ في السطر " . $row['A'] . ": " . $e->getMessage();
                            }
                        }

                        return response()->json([
                            'status' => true,
                            'message' => [
                                'success_count' => $successCount,
                                'error_count' => $errorCount,
                                'errors' => $errors
                            ],
                            'message_out' => "تم استيراد $successCount سجل بنجاح، مع $errorCount أخطاء"
                        ]);

                    } catch (\Exception $e) {
                        if (file_exists($inputFileName)) {
                            unlink($inputFileName);
                        }
                        throw $e;
                    }
                }

                return response()->json([
                    'status' => false,
                    'message' => ['error' => 'لم يتم العثور على الملف'],
                    'message_out' => 'الرجاء اختيار ملف'
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => ['error' => $e->getMessage()],
                    'message_out' => 'حدث خطأ أثناء معالجة الملف: ' . $e->getMessage()
                ]);
            }
        }

        return redirect()->route('show_not_allow')->send();
    }


    public function index()
    {
        //  $this->checkPermission('2');
        //   Perm::get_controll_access(1);
        if (Perm::get_function_access(10)) {
            //   Perm::get_controll_access(2);
            //if(Perm::get_controll_access(1)){
            $sel_worker = "1";
            $sub_add_worker = "1";
            $page_title = 'ادخال بيانات العمال';
            $work_place = DB::table('work_place')->get();
            $manager = $this->get_manager();
            $nation = DB::table('nation')->get();
            $job = DB::table('job')->get();
            $const = array("work_place", "job", "nation", "sel_worker", "page_title", "manager");
            return view('dashboard.workers.index', compact($const));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }

    public function store(Request $request)
    {
        if (Perm::get_function_access(10)) {
            $worker_name_old = $request->old('worker_name');
            $attributeNames = array(
                'worker_name' => 'اسم العامل ',
                'ssn' => 'رقم الإقامة / الوطني للسعوديين',
                'files.*' => 'نوع الملف',
                'dob' => 'تاريخ الميلاد',
                'dop' => 'تاريخ انتهاء الجواز',
                'dos' => 'تاريخ اصدار الاقامة',
                //'avatar' => 'صورة الشخصية للعامل ',
            );
            $validator = Validator::make($request->all(), [
                'worker_name' => ['required', 'string'],
                'ssn' => ['required', 'unique:workers',],
                'files.*' => ['nullable', 'file', 'mimes:csv,txt,xlx,xlsx,xls,pdf,ppt,pptx,doc,docx,xlsx,jpg,jpeg,bmp,png,rtf,zip'],
                'dob' => ['nullable', 'date'],
                'dop' => ['nullable', 'date'],
                'dos' => ['nullable', 'date'],
            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $input = $request->all();
                $user_photo = '';
                if ($request->hasFile('avatar')) {
                    $imageName = time() . '.' . $request->avatar->extension();
                    $request->avatar->move(public_path('uploads/users/images/'), $imageName);
                    $user_photo = 'uploads/users/images/' . $imageName;
                }
                $passportfile_url = '';
                if ($request->hasFile('passportfile')) {
                    $passportfile_name = time() . '.' . $request->passportfile->extension();
                    $request->passportfile->move(public_path('uploads/users/images/'), $passportfile_name);
                    $passportfile_url = 'uploads/users/images/' . $passportfile_name;
                }

                $ssnfile_url = '';
                if ($request->hasFile('ssnfile')) {
                    $ssnfile_name = time() . '.' . $request->ssnfile->extension();
                    $request->ssnfile->move(public_path('uploads/users/images/'), $ssnfile_name);
                    $ssnfile_url = 'uploads/users/images/' . $ssnfile_name;
                }
                /* $input['avatar'] = $user_photo;
               $input = request()->except(['_token','worker_id_db','avatar_remove','X-CSRF-TOKEN','experience_cert','good_manners_cert','submitButton','files']);
               $result2= DB::table('workers')->insertGetId($input);*/
                $result2 = DB::table('workers')->insertGetId([
                    'worker_name' => $request->worker_name,
                    'registration_number' => $request->registration_number,
                    'manager_id' => $request->manager_id,
                    'avatar' => $user_photo,
                    'ssn' => $request->ssn,
                    'ssnfile' => $ssnfile_url,
                    'dos' => $request->dos,
                    'doe' => $request->doe,
                    'dob' => $request->dob,
                    'mobile' => $request->mobile,
                    'phone' => $request->phone,
                    'nation_id' => $request->nation_id,
                    'passport_no' => $request->passport_no,
                    'passportfile' => $passportfile_url,
                    'dop' => $request->dop,
                    'inside' => $request->inside? 1 : 0,
                    'dow' => $request->dow,
                    'job_id' => $request->job_id,
                    'work_place_id' => $request->work_place_id,
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

                            $result_upload = DB::table('workers_attach')->insertGetId([
                                'worker_id' => $result2,
                                'workers_attach_name' => $orginal_name,
                                'workers_attach_url' => $file_url,
                                'workers_attach_extension' => $ext,

                            ]);
                        }
                    }
                    $result['status'] = $result2;
                    $result['message_out'] = 'تم الحفظ بنجاح';
                } else {
                    if (File::exists($user_photo)) {
                        File::delete($user_photo);
                    }
                }
            }
            return response()->json($result);
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function views()
    {
        if (Perm::get_function_access(70) || Perm::get_function_access(11) || Perm::get_function_access(12) || Perm::get_function_access(13) || Perm::get_function_access(14)
            || Perm::get_function_access(15) || Perm::get_function_access(16) || Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {
            $work_place = DB::table('work_place')->get();
            $job = DB::table('job')->get();
            $manager = $this->get_manager();
            $nation = DB::table('nation')->get();
            $page_title = 'عرض بيانات العمال';
            return view('dashboard.workers.view', compact('work_place', 'job', 'page_title', 'manager', 'nation'));
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(70) || Perm::get_function_access(11) || Perm::get_function_access(12) || Perm::get_function_access(13) || Perm::get_function_access(14)
                || Perm::get_function_access(15) || Perm::get_function_access(16) || Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19))) {
            return view('dashboard.workers.tbl_workers');
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function sel_worker_list(Request $request)
    {
        $string = $request->q;
        $page = $request->page;
        $response = Workers::sel_worker_list($string, $page);
        echo json_encode($response);
    }


    public function ajax_search_workers(Request $request)
    {
        $order_date = $request["order_date"];
        if ($request->ajax() and (Perm::get_function_access(70) || Perm::get_function_access(11) || Perm::get_function_access(12) || Perm::get_function_access(13) || Perm::get_function_access(14)
                || Perm::get_function_access(15) || Perm::get_function_access(16) || Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19))) {
            $worker_name = $request->worker_name;
            $ssn = $request->ssn;
            $work_place_id = $request->work_place_id;
            $doe = $request->doe;
            $updatedcancal_at = $request->updatedcancal_at;
            $job_id = $request->job_id;
            $end_dt = $request->end_dt;
            $end_p_dt = $request->end_p_dt;
            $manager_id = $request->manager_id;
            $inside = $request->inside;
            $is_imp = $request->is_imp;
            $nation = $request->nation;
            $list_totl = Workers::sumspenddata($worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt, $manager_id, $inside, $is_imp, $nation, $request["residence_month"],$request["residence_year"],$request["passport_month"],$request["passport_year"]);
            $all_imp = 0;
            $all_imp_not_cancal = 0;
            $all_imp_cancal = 0;
            $all_not_cancal = 0;
            $all_cancal = 0;
            $not_have_manger = 0;
            $out_ksa = 0;
            $in_ksa = 0;
            foreach ($list_totl as $x_sum) {
                $all_imp = $x_sum->all_imp;
                $all_imp_not_cancal = $x_sum->all_imp_not_cancal;
                $all_imp_cancal = $x_sum->all_imp_cancal;
                $all_not_cancal = $x_sum->all_not_cancal;
                $all_cancal = $x_sum->all_cancal;
                $not_have_manger = $x_sum->not_have_manger;
                $out_ksa = $x_sum->out_ksa;
                $in_ksa = $x_sum->in_ksa;
            }
            $list_total = Workers::serachspendcount($worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt, $manager_id, $inside, $is_imp, $nation , $request["residence_month"],$request["residence_year"],$request["passport_month"],$request["passport_year"]);
            $list = Workers::serachspenddata($worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt, $manager_id, $inside, $is_imp, $nation , $order_date , $request["residence_month"],$request["residence_year"],$request["passport_month"],$request["passport_year"]);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $i++;
        if ($x->doe_desc == '3') {
                    $doe_desc_char = '<br><span class="ms-2 badge badge-light-warning fw-bold">شارف على الانتهاء</span>';
                } else if ($x->doe_desc == '2') {
                    $doe_desc_char = '<br><span class="ms-2 badge badge-light-danger fw-bold">منتهي</span>';
                } else if ($x->doe_desc == '1') {
                    $doe_desc_char = '<br><span class="ms-2 badge badge-light-success fw-bold">سارية</span>';
                } else {
                    $doe_desc_char = '<br><span class="ms-2 badge badge-light-info fw-bold">غير مدخل</span>';
                }
                if ($x->dop_desc == '3') {
                    $dop_desc_char = '<br><span class="ms-2 badge badge-light-warning fw-bold">شارف على الانتهاء</span>';
                } else if ($x->dop_desc == '2') {
                    $dop_desc_char = '<br><span class="ms-2 badge badge-light-danger fw-bold">منتهي</span>';
                } else if ($x->dop_desc == '1') {
                    $dop_desc_char = '<br><span class="ms-2 badge badge-light-success fw-bold">سارية</span>';
                } else {
                    $dop_desc_char = '<br><span class="ms-2 badge badge-light-info fw-bold">غير مدخل</span>';
                }
                if ($x->nation_name_ar != '') {
                    $nation_name_ar = '<br><span class="ms-2 badge badge-light-info fw-bold">' . $x->nation_name_ar . '</span>';
                } else {
                    $nation_name_ar = '';
                }

                if ($x->inside == 1) {
                    $inside_desc = '<span class="ms-2 badge badge-light-success fw-bold">داخل المملكة</span>';
                } else {
                    $inside_desc = '<span class="ms-2 badge badge-light-danger fw-bold">خارج المملكة</span>';
                }

                if ($x->is_imp == 1) {
                    $is_imp_desc = '<span class="ms-2 badge badge-light-info fw-bold">مستورد</span>';
                    $is_imp_desc .= '<br><span class="ms-2 text-dark fw-bold">' . Carbon::parse($x->imp_dt)->format('d-m-Y h:i A') . '</span>';
                    $is_imp_desc .= '<br><span class="ms-2 text-dark fw-bold">' . $x->imp_user . '</span>';
                } else {
                    $is_imp_desc = '<span class="ms-2 badge badge-light-success fw-bold">ادخال يدوي</span>';
                }
                if ($x->name != '') {
                    $insert_desc = '<br><span class="ms-2 text-dark fw-bold">' . $x->name . '</span>';
                    $insert_desc .= '<br><span class="ms-2 text-dark fw-bold">' . Carbon::parse($x->created_at)->format('d-m-Y') . '</span>';
                } else {
                    $insert_desc = '';
                }
                if ($x->avatar != '') {
                    $avatar = $x->avatar;
                } else {
                    $avatar = 'assets/media/avatars/blank.png';
                }
                if ($x->updatecancal_user != '') {
                    $updatecancal_user_desc = '<span class="ms-2 badge badge-light-danger fw-bold">منهي الخدمات</span><br><span class="ms-2 badge badge-light-info fw-bold">' . Carbon::parse($x->updatedcancal_at)->format('d-m-Y') . '</span>';
                } else {
                    $updatecancal_user_desc = '<span class="ms-2 badge badge-light-success fw-bold">مستمر في العمل</span>';
                }
                $row = array();
                $row[] = ++$no;

                $row[] = '<div class="d-flex align-items-center">
																<div class="me-2 position-relative">
																	<div class="symbol symbol-35px symbol-circle">
																		<img alt="" src="' . $avatar . '" onclick="showLargeImage(this.src)" style="cursor: pointer;">

																	</div>
																</div>
																<div class="d-flex flex-column justify-content-center">
																	<a href="" class="text-gray-800 text-hover-primary">' . $x->worker_name . '</a>
																	<div class="fw-bold text-info">' . $x->mobile . '</div>

																</div>
															</div>';
                if ($x->count_work_note != 0) {
                    $count_work_note = '<span class="ms-2 badge badge-light-danger fw-bold">' . $x->count_work_note . '</span>';
                } else {
                    $count_work_note = '<span class="ms-2 badge badge-light-dark fw-bold">' . $x->count_work_note . '</span>';
                }
                $row[] = $x->ssn;
                $row[] = $x->registration_number;

                $row[] = $x->manager_name;
                $row[] = $count_work_note;
                $row[] = $x->dos ? Carbon::parse($x->dos)->format('d-m-Y') : '';
                $row[] = $x->doe ? Carbon::parse($x->doe)->format('d-m-Y') . $doe_desc_char : $doe_desc_char;
                $row[] = $x->dop ? Carbon::parse($x->dop)->format('d-m-Y') . $dop_desc_char : $dop_desc_char;
                $row[] = $x->dob;

                $row[] = $x->nation_name_ar;
                $row[] = $x->dow ? Carbon::parse($x->dow)->format('d-m-Y') : '';
                $row[] = $x->work_place_name;
                $row[] = $x->job_name;
                $row[] = $inside_desc;
                $row[] = $x->note;
                $row[] = $updatecancal_user_desc;
                $row[] = $insert_desc;
                $row[] = $is_imp_desc;
                if (
                    Perm::get_function_access(12) || Perm::get_function_access(13) || Perm::get_function_access(15) || Perm::get_function_access(14) ||
                    Perm::get_function_access(16) || Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {
                    $opt = '<div class="btn-group btn-group-sm" role="group"  >';
                    if (Perm::get_function_access(12) || Perm::get_function_access(13) || Perm::get_function_access(15) || Perm::get_function_access(14)) {
                        $opt = $opt . '<div class="dropdown" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px">
    <button class="btn btn-warning dropdown-toggle" style="padding:2px 10px 4.89px !important" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">الاجراءات</button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">';
                        if (Perm::get_function_access(12)) {
                            $opt .= '
<li><a class="dropdown-item upd_workers fw-bolder text-dark"   data-url=' . "'" . route('dashboard.workers.upd_workers') . "'" . ' onclick="upd_workers(' . "'" . $x->worker_id . "'" . ')">  <i class="far fa-edit fa-fw text-info"></i> تعديل</a></li>
';
                        }

                        if (Perm::get_function_access(13)) {
                            $opt .= '<li><a class="dropdown-item fw-bolder text-dark"  onclick="del_workers(' . "'" . $x->worker_id . "'" . ')">  <i class="fas fa-trash-alt fa-fw text-danger"></i> حذف عامل</a></li>';
                        }

                        if (Perm::get_function_access(15)) {
                            $opt .= '
<li><a class="dropdown-item cancal_workers fw-bolder text-dark"   data-url=' . "'" . route('dashboard.workers.cancal_workers') . "'" . ' onclick="cancal_workers(' . "'" . $x->worker_id . "'" . ')">  <i class="fas fa-stop-circle fa-fw text-warning"></i> انهاء عامل</a></li>
';
                        }


                        if (Perm::get_function_access(14)) {
                            $opt .= '
        <li><a class="dropdown-item print_worker_pdf fw-bolder text-dark"   data-url=' . "'" . route('dashboard.report.print_worker_pdf') . "'" . ' onclick="print_worker_pdf(' . "'" . $x->worker_id . "'" . ')">  <i class="fas fa-print fa-fw text-primary"></i> طباعة</a></li>
        ';
                        }
                        if (Perm::get_function_access(72)) {

                            $opt .= '
       <li><a class="dropdown-item fw-bolder text-dark" href=' . "'" . $x->emp_file . "'" . '>  <i class="fas fa-cloud-download-alt fa-fw text-success"></i> تنزيل ملف الاستيراد</a></li>
       ';
                        }
                        $opt .= '
    <div class="my-2 separator fw-bolder text-info" ></div>
    </ul>
    </div>';
                    }


                    if (Perm::get_function_access(16) || Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {
                        $opt = $opt . '<div class="dropdown" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px">
<button class="btn btn-info dropdown-toggle" style="padding:2px 10px 4.89px !important" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">الملاحظات</button>
<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">';
                        if (Perm::get_function_access(16)) {
                            $opt .= '
<li><a class="dropdown-item upd_note fw-bolder text-dark"  data-url=' . "'" . route('dashboard.workers.upd_note') . "'" . ' onclick="upd_note(' . "'" . $x->worker_id . "'" . ')">  <i class="fas fa-bell fa-fw text-info"></i> اضافة ملاحظة</a></li>
';
                        }
                        if (Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {
                            $opt .= '
<li><a class="dropdown-item upd_remark fw-bolder text-dark"  data-url=' . "'" . route('dashboard.workers.upd_remark') . "'" . ' onclick="upd_remark(' . "'" . $x->worker_id . "'" . ')">  <i class="fas fa-sticky-note fa-fw text-success"></i> عرض ملاحظة</a></li>
';
                        }
                        $opt .= '
<li><a class="dropdown-item worker_note_history fw-bolder text-dark"  data-url=' . "'" . route('dashboard.workers.worker_note_history') . "'" . ' onclick="worker_note_history(' . "'" . $x->worker_id . "'" . ')">  <i class="fas fa-history fa-fw text-danger"></i>عرض حركة السجلات</a></li>
';

                        $opt .= '
<div class="my-2 separator fw-bolder text-info" ></div>
</ul>
</div>';
                    }
                    $opt .= '</div>';
                    $opt .= '</div>';
                    $row[] = $opt;
                }


                $data[] = $row;
            }
            $output = array(
                "all_imp" => $all_imp,
                "all_imp_not_cancal" => $all_imp_not_cancal,
                "all_imp_cancal" => $all_imp_cancal,
                "all_not_cancal" => $all_not_cancal,
                "all_cancal" => $all_cancal,
                "not_have_manger" => $not_have_manger,
                "out_ksa" => $out_ksa,
                "in_ksa" => $in_ksa,
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data);
            echo json_encode($output);
        } else {
            return redirect()->route('show_not_allow')->send();
        }
    }


    public function upd_note(Request $request)
    {
        if (Perm::get_function_access(16)) {
            $id = $request->id;
            $issamecreateworker = $this->issamecreateworker($id);
            if ($issamecreateworker) {
                $worker = DB::table('workers')->where('worker_id', $id)->first();
                $sub_add_worker = "1";
                $page_title = 'إنشاء ملف ';
                $note_type = DB::table('note_type')->get();
                $const = array("note_type", "page_title");
                return view('dashboard.workers.upd_note', compact('worker', $const));
            }
        }
    }

    public function worker_note_history(Request $request)
    {
        if (Perm::get_function_access(16)) {
            $id = $request->id;
            $issamecreateworker = $this->issamecreateworker($id);
            if ($issamecreateworker) {
                $worker = DB::table('workers')->where('worker_id', $id)->first();

                $sub_add_worker = "1";
                $page_title = 'إنشاء ملف ';
                $note_type = DB::table('note_type')->get();
                $const = array("note_type", "page_title");
                return view('dashboard.workers.worker_note_history', compact('worker', $const));
            }
        }
    }


    public function updnote(Request $request)
    {
        $worker_id = $request->worker_id;

        if (Auth()->user()->emp_job != 1) {
            $workers_chk = DB::table('workers')
                ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                ->where('workers_manager.user_id', Auth::user()->id)
                ->where('worker_id', $worker_id)->first();
            $worker_id = $workers_chk->worker_id;
        }


        $attributeNames = array(
            'note_type_id' => 'نوع الملاحظة',
        );
        $validator = Validator::make($request->all(), [
            'worker_id' => ['required'],
            'file.*' => ['nullable', 'file', 'mimes:csv,txt,xlx,xlsx,xls,pdf,ppt,pptx,doc,docx,xlsx,jpg,jpeg,bmp,png,rtf,zip'],
        ]);
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
            $result['message_out'] = '';
        } else {
            $ERROR_FLAG = 0;


            $result2 = DB::table('worker_note')->insertGetId([
                'worker_id' => $worker_id,
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

                        $result_upload = DB::table('noteworker_attach')->insertGetId([
                            'worker_note_id' => $result2,
                            'note_attach_name' => $orginal_name,
                            'note_attach_url' => $file_url,
                            'note_attach_extension' => $ext,

                        ]);


                    }
                }


                $result3 = DB::table('worker_note_history')->insertGetId([
                    'worker_note_id' => $result2,
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


    public function upd_remark(Request $request)
    {
        if (Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {
            $id = $request->id;


            $issamecreateworker = $this->issamecreateworker($id);
            if ($issamecreateworker) {
                $worker = DB::table('workers')
                    ->leftJoin('users', 'workers.create_user', '=', 'users.id')
                    ->select('workers.*')
                    ->where('workers.worker_id', $id)->first();


                $sub_add_worker = "1";
                $page_title = 'إنشاء ملف ';
                $note_type = DB::table('note_type')->get();
                $const = array("note_type", "page_title");
                return view('dashboard.workers.upd_remark', compact('worker', $const));
            }
        }
    }


    public function updremark(Request $request)
    {
        if (Perm::get_function_access(18)) {
            $worker_note_id = $request->worker_note_id;
            $worker_id = $request->worker_id;

            if (Auth()->user()->emp_job != 1) {
                $workers_chk = DB::table('workers')
                    ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('worker_id', $worker_id)->first();
                $worker_id = $workers_chk->worker_id;
            }


            $worker_note = DB::table('worker_note')->where('worker_note_id', $worker_note_id)->first();
            $old_remark = $worker_note->remark;
            $old_note_type_id = $worker_note->note_type_id;


            $attributeNames = array(
                'note_type_id' => 'نوع الملاحظة',
            );
            $validator = Validator::make($request->all(), [
                'worker_id' => ['required'],
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

                $result2 = DB::table('worker_note')
                    ->where('worker_note_id', $worker_note_id)
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
                            $result2 = DB::table('noteworker_attach')
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

                            $result_upload = DB::table('noteworker_attach')->insertGetId([
                                'worker_note_id' => $worker_note_id,
                                'note_attach_name' => $orginal_name,
                                'note_attach_url' => $file_url,
                                'note_attach_extension' => $ext,

                            ]);
                        }


                    }
                }


                $result3 = DB::table('worker_note_history')->insertGetId([
                    'worker_note_id' => $worker_note_id,
                    'change_user' => Auth::user()->id,
                    'change_at' => Carbon::now(),
                    'note_type_id' => $request->note_type_id,
                    'remark' => $request->remark,
                    'old_remark' => $old_remark,
                    'old_note_type_id' => $old_note_type_id,
                ]);


                $result['url'] = route('dashboard.workers.tbl_remark');


                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';


            }
            return response()->json($result);
        }
    }

    public function tbl_history(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19))) {
            return view('dashboard.workers.tbl_history');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_history(Request $request)
    {
        if (Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {

            $worker_id = $request->worker_id;

            $list_total = Workers::serachhistorycount($worker_id);
            $list = Workers::serachhistorydata($worker_id);
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


    public function tbl_remark(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19))) {
            return view('dashboard.workers.tbl_remark');
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function ajax_search_remark(Request $request)
    {
        if (Perm::get_function_access(17) || Perm::get_function_access(18) || Perm::get_function_access(19)) {

            $worker_id = $request->worker_id;

            $list_total = Workers::serachremarkcount($worker_id);
            $list = Workers::serachremarkdata($worker_id);
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
                //  $row[] =$depend_desc;
                if (Perm::get_function_access(18) || Perm::get_function_access(19)) {

                    $opt = '<div class="btn-group btn-group-sm" role="group"  >';
                    if (Perm::get_function_access(18)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm change_remark" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important"    data-url=' . "'" . route('dashboard.workers.change_remark') . "'" . ' onclick="change_remark(' . "'" . $x->worker_note_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(19)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_remark(' . "'" . $x->worker_note_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw"></i>  </a>';
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
        if (Perm::get_function_access(19)) {
            $id = $request->id;
            $workers_chk = DB::table('worker_note')
                ->Join('workers', 'workers.worker_id', '=', 'worker_note.worker_id')
                ->select('worker_note.*')
                ->where('worker_note.worker_note_id', $id)->first();
            $id = $workers_chk->worker_note_id;
            $worker_id = $workers_chk->worker_id;
            $old_remark = $workers_chk->remark;
            $old_note_type_id = $workers_chk->note_type_id;

            $issamecreateworker = $this->issamecreateworker($worker_id);
            if ($issamecreateworker) {

                $delete = DB::table('worker_note')
                    ->where('worker_note_id', $id)
                    ->update([
                        'is_deleted' => 1,
                        'deleted_at' => Carbon::now(),
                        'deleted_user' => Auth::user()->id,

                    ]);


                $result3 = DB::table('worker_note_history')->insertGetId([
                    'worker_note_id' => $id,
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
    }


    public function change_remark(Request $request)
    {
        if (Perm::get_function_access(18)) {
            $worker_note_id = $request->id;
            $worker_note = DB::table('worker_note')->where('worker_note_id', $worker_note_id)->first();
            $note_type = DB::table('note_type')->get();
            $noteworker_attach = DB::table('noteworker_attach')->where('worker_note_id', $worker_note_id)->get();
            return view('dashboard.workers.change_remark', compact('worker_note', 'note_type', 'noteworker_attach'));
        }
    }


    function del_workers(Request $request)
    {
        if (Perm::get_function_access(13)) {
            $id = $request->id;
            $issamecreateworker = $this->issamecreateworker($id);
            if ($issamecreateworker) {

                try {


                    $delete = DB::delete('delete from workers where worker_id = ?', [$id]);
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
            } else {
                $message = 'لا يمكن الحذف لانه ليس انت مدخله';
                $result['status'] = false;
                $result['message'] = $message;
                echo json_encode($result);

            }
        }
    }


    public function upd_workers(Request $request)
    {
        if (Perm::get_function_access(12)) {
            $id = $request->id;


            $issamecreateworker = $this->issamecreateworker($id);
            if ($issamecreateworker) {
             //   $workers = DB::table('workers')->where('worker_id', $id)->first();




                $workers = DB::table('workers')
                    ->leftJoin('worker_health', 'workers.worker_id', '=', 'worker_health.worker_id')
                    ->select('worker_health.*','workers.*','workers.worker_id')
                    ->where('workers.worker_id', $id)->first();


                $workers_attach = DB::table('workers_attach')->where('worker_id', $id)->get();
              /*  $worker_health = DB::table('worker_health')->where('worker_id', $id)->first();
dd($worker_health);*/

                $manager = $this->get_manager();

                $sub_add_worker = "1";
                $page_title = 'تعديل بيانات العمال';
                $work_place = DB::table('work_place')->get();
                $nation = DB::table('nation')->get();
                $job = DB::table('job')->get();
                $const = array("work_place", "job", "nation", "page_title", "workers_attach",  "manager");
                return view('dashboard.workers.upd_workers', compact('work_place', 'workers', $const));
            }
        }
    }


    public function cancal_workers(Request $request)
    {
        if (Perm::get_function_access(15)) {

            $id = $request->id;


            $issamecreateworker = $this->issamecreateworker($id);
            if ($issamecreateworker) {
                $workers = DB::table('workers')->where('worker_id', $id)->first();


                $sub_add_worker = "1";
                $page_title = 'انهاء بيانات العمال';
                $const = array("page_title");
                return view('dashboard.workers.cancal_workers', compact('workers', $const));
            }
        }
    }


    public function delete_file(Request $request)
    {

        $worker_id = $request->worker_id;
        $ssnfile_url = $request->ssnfile_url;
        $type = $request->type;


        if ($type == 'workers_attach') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }

            $result2 = DB::table('workers_attach')->where('workers_attach_id', $worker_id)->delete();
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;

            echo json_encode($result);
        }


        if ($type == 'ssnfile') {
            if (File::exists($ssnfile_url)) {
                File::delete($ssnfile_url);
            }
            $result2 = DB::table('workers')
                ->where('worker_id', $worker_id)
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
            $result2 = DB::table('workers')
                ->where('worker_id', $worker_id)
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
        if (Perm::get_function_access(12)) {

            $id = $request->worker_id_db;
            //  $ishavegroupworker = $this->ishavegroupworker($id);
            //  if($ishavegroupworker){
            $issamecreateworker = $this->issamecreateworker($id);
            if ($issamecreateworker) {

                $attributeNames = array(
                    'worker_name' => 'اسم العامل ',
                    'ssn' => 'رقم الإقامة / الوطني للسعوديين',
                    'files.*' => 'نوع الملف',
                    'dob' => 'تاريخ الميلاد',
                    'dop' => 'تاريخ انتهاء الجواز',
                    'dos' => 'تاريخ اصدار الاقامة',
                    //'avatar' => 'صورة الشخصية للعامل ',
                );
                $validator = Validator::make($request->all(), [
                    'worker_name' => ['required', 'string'],
                    'ssn' => ['required', Rule::unique('workers', 'ssn')->ignore($id, 'worker_id')],
                    'files.*' => ['nullable', 'file', 'mimes:csv,txt,xlx,xlsx,xls,pdf,ppt,pptx,doc,docx,xlsx,jpg,jpeg,bmp,png,rtf,zip'],
                    'dob' => ['nullable', 'date'],
                    'dop' => ['nullable', 'date'],
                    'dos' => ['nullable', 'date'],
                ]);
                $validator->setAttributeNames($attributeNames);


                if ($validator->fails()) {
                    $result['status'] = false;
                    $result['message'] = $validator->errors();
                    $result['message_out'] = '';
                } else {
                    $ERROR_FLAG = 0;
                    $user_photo = '';
                    if ($request->hasFile('avatar')) {
                        $imageName = time() . '.' . $request->avatar->extension();
                        $request->avatar->move(public_path('uploads/users/images/'), $imageName);
                        $user_photo = 'uploads/users/images/' . $imageName;
                        if (File::exists($request->avatar_db)) {
                            File::delete($request->avatar_db);
                        }
                    } else {
                        $user_photo = $request->avatar_db;
                    }


                    $passportfile_url = '';
                    if ($request->hasFile('passportfile')) {
                        $passportfile_name = time() . '.' . $request->passportfile->extension();
                        $request->passportfile->move(public_path('uploads/users/images/'), $passportfile_name);
                        $passportfile_url = 'uploads/users/images/' . $passportfile_name;
                        if (File::exists($request->passportfile_db)) {
                            File::delete($request->passportfile_db);
                        }
                    } else {
                        $passportfile_url = $request->passportfile_db;
                    }

                    $ssnfile_url = '';
                    if ($request->hasFile('ssnfile')) {
                        $ssnfile_name = time() . '.' . $request->ssnfile->extension();
                        $request->ssnfile->move(public_path('uploads/users/images/'), $ssnfile_name);
                        $ssnfile_url = 'uploads/users/images/' . $ssnfile_name;
                        if (File::exists($request->ssnfile_db)) {
                            File::delete($request->ssnfile_db);
                        }

                    } else {
                        $ssnfile_url = $request->ssnfile_db;
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
                    /*      $input = $request->all();
                          $input = request()->except(['_token','worker_id_db','avatar_remove','X-CSRF-TOKEN']);
                          $input['avatar'] = $user_photo;
                         $result2=Workers::where('worker_id',$id)->update($input);*/


                    //    if( Auth()->user()->emp_job!=1){
                    //     $workers_chk = DB::table('workers')
                    //     ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    //     ->where('workers_manager.user_id', Auth::user()->id)
                    //     ->where('worker_id',$id)->first();
                    //     $id=$workers_chk->worker_id;
                    // }


                    $result2 = DB::table('workers')
                        ->where('worker_id', $id)
                        ->update([
                            'worker_name' => $request->worker_name,
                    'registration_number' => $request->registration_number,
                            'manager_id' => $request->manager_id,
                            'avatar' => $user_photo,
                            'ssn' => $request->ssn,
                            'ssnfile' => $ssnfile_url,
                            'dos' => $request->dos,
                            'doe' => $request->doe,
                            'dob' => $request->dob,
                            'mobile' => $request->mobile,
                            'phone' => $request->phone,
                            'nation_id' => $request->nation_id,
                            'passport_no' => $request->passport_no,
                            'passportfile' => $passportfile_url,
                            'dop' => $request->dop,
                            'inside' => $request->inside? 1 : 0,
                            'dow' => $request->dow,
                            'job_id' => $request->job_id,
                            'work_place_id' => $request->work_place_id,
                            'note' => $request->note,
                            'updated_at' => Carbon::now(),
                            'update_user' => Auth::user()->id,
                        ]);

                        $result2 = DB::table('worker_health')
                        ->updateOrInsert(
                            ['worker_health_id' => $request->worker_health_id],
                            [
                                'worker_id' => $id,
                                'health_no' => $request->health_no,
                                'health_edt' => $request->health_edt,
                                'health_attach_name' => $health_attach_name,
                                'health_attach_extension' => $health_attach_extension,
                                'health_attach_url' => $healthfile_url,
                                'health_note' => $request->health_note,
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
                                $result2 = DB::table('workers_attach')
                                    ->where('workers_attach_id', $request->emp_att_id[$key])
                                    ->update([
                                        'workers_attach_name' => $orginal_name,
                                        'workers_attach_url' => $file_url,
                                        'workers_attach_extension' => $ext,
                                    ]);
                                if (File::exists($request->image_url_emp[$key])) {
                                    File::delete($request->image_url_emp[$key]);
                                }
                                $result2 = 1;

                            } else {

                                $result_upload = DB::table('workers_attach')->insertGetId([
                                    'worker_id' => $id,
                                    'workers_attach_name' => $orginal_name,
                                    'workers_attach_url' => $file_url,
                                    'workers_attach_extension' => $ext,

                                ]);
                            }


                        }
                    }
                    $result['status'] = $result2;
                    $result['message_out'] = 'تم الحفظ بنجاح';
                    $result['message'] = '';

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


    public function updcancal(Request $request)
    {
        if (Perm::get_function_access(15)) {


            $worker_id = $request->worker_id;


            if (Auth()->user()->emp_job != 1) {
                $workers_chk = DB::table('workers')
                    ->Join('workers_manager', 'workers.manager_id', '=', 'workers_manager.manager_id')
                    ->where('workers_manager.user_id', Auth::user()->id)
                    ->where('worker_id', $worker_id)->first();
                $worker_id = $workers_chk->worker_id;
            }


            $attributeNames = array(
                'worker_id' => 'رقم العامل',
                'doc' => 'تاريخ إنهاء العمل',
                'files.*' => 'نوع الملف',
                'note_cancal' => 'سبب إنهاء العمل',
                'canacal_type_id' => 'نوع الاجراء',

            );
            $validator = Validator::make($request->all(), [
                'worker_id' => ['required'],
                'files.*' => ['nullable', 'file', 'mimes:csv,txt,xlx,xlsx,xls,pdf,ppt,pptx,doc,docx,xlsx,jpg,jpeg,bmp,png,rtf,zip'],
                //  'doc' => ['required','date'],
                //   'note_cancal' => ['required']
                'doc' => [Rule::requiredIf($request->canacal_type_id != 1)],

                'note_cancal' => [Rule::requiredIf($request->canacal_type_id != 1)],

            ]);
            $validator->setAttributeNames($attributeNames);


            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $cancalfile = '';
                $cancalfile_url = '';
                if ($request->hasFile('cancalfile')) {
                    $cancalfile_name = time() . '.' . $request->cancalfile->extension();
                    $request->cancalfile->move(public_path('uploads/users/images/'), $cancalfile_name);
                    $cancalfile_url = 'uploads/users/images/' . $cancalfile_name;
                    if (File::exists($request->cancalfile_db)) {
                        File::delete($request->cancalfile_db);
                    }
                } else {
                    $cancalfile_url = $request->cancalfile_db;
                }


                if ($request->canacal_type_id != 1) {
                    $result2 = DB::table('workers')
                        ->where('worker_id', $worker_id)
                        ->update([
                            'doc' => $request->doc,
                            'note_cancal' => $request->note_cancal,
                            'cancalfile' => $cancalfile_url,
                            'updatedcancal_at' => Carbon::now(),
                            'updatecancal_user' => Auth::user()->id,
                        ]);
                } else {
                    $result2 = DB::table('workers')
                        ->where('worker_id', $worker_id)
                        ->update([
                            'doc' => null,
                            'note_cancal' => null,
                            'cancalfile' => null,
                            'updatedcancal_at' => null,
                            'updatecancal_user' => null,
                        ]);
                }


                $result['status'] = $result2;
                $result['message_out'] = 'تم الحفظ بنجاح';

            }
            return response()->json($result);

        }
    }


    public function create()
    {
        //
    }


    public function store___________(Request $request)
    {
        $worker_name_old = $request->old('worker_name');
        $validator = Validator::make($request->all(), [
            'worker_name' => 'required',
            'ssn' => 'required',
            //  'work_place_id' => 'required',
            'doe' => 'required',

        ], [
            'worker_name.required' => 'اسم العامل العمل',
            'ssn.required' => 'رقم الإقامة / الوطني للسعوديين  مطلوب',
            // 'work_place_id.required' => 'مكان العمل مطلوب',
            'doe.required' => 'تاريخ إنتهاء الإقامة  مطلوب',
        ]);
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $validator->errors();
        } else {

            $ERROR_FLAG = 0;
            /*   if( $request->GOVERNORATE_IN!='16001999'){
               $OTHER_AREA_IN='';
               }
               else{
               $OTHER_AREA_IN=$request->OTHER_AREA_IN;
               }*/
            /*    $procedureName2 = 'workers_pkg.ADD_WORKER_INFO_PR';
                $bindings2 = [
                    'SSN_IN' => session('user_id'),
                    'NAME_IN' =>session('Name'),
                    'MOBILE_IN' => $request->MOBILE_IN ,
                    'GOVERNORATE_IN' => $request->GOVERNORATE_IN,
                     'OTHER_AREA_IN' => $request->OTHER_AREA_IN,
                    'EMPLOYER_IN' => $request->EMPLOYER_IN,
                    'WORKPLACE_IN' => $request->WORKPLACE_IN ,
                    'WORK_FIELD_IN' => $request->WORK_FIELD_IN ,
                     'PARTNERS_IN' => $request->PARTNERS_IN ,
                    'DORMITORY_IN' => $request->DORMITORY_IN,
                     'NOTES_IN' => $request->NOTES_IN,
                      'INSERT_USER_IN' =>session('user_id'),
                    'codeMsg' => [
                        'value' => &$ERROR_FLAG,
                        'length' => 9,
                    ],
                ];
         $result2 = DB::connection('oracle')->executeProcedure($procedureName2, $bindings2); */
            //  $result2=Workers::create($request->all());

            //  $result2=Workers::create($request->worker_name);

            $result2 = Workers::create([
                'worker_name' => $request->worker_name
            ]);

            $result['status'] = $result2;
            $result['message'] = 'تم الحفظ بنجاح';
            //  $result['url'] = route('operator.workers.tbl');
        }
        return response()->json($result);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreWorkersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store__________(Request $request)
    {

        /*  $vv=Workers::ins_tbl();
          echo $vv;*/


        /*    $dataSet[] = [
              'worker_name'  => $request->worker_name,
              'phone'    => $request->phone,
              'remarks'       => $request->remarks,
          ];

          DB::table('workers')->insert($dataSet);*/


        /*
            $data = $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);*/


        /* $dataSet[] = $request->validate([
             'worker_name' => 'required',
             'phone' => 'required|email',
             'remarks' => 'required'
         ]);*/

        $dataSet[] = $request->validate([
            'worker_name' => 'required',
        ]);
        $vv = Workers::create($request->all());

        /*
            $dataSet[] = [
                'worker_name'  => $request->worker_name,
                'phone'    => $request->phone,
                'remarks'       => $request->remarks,
            ];*/
//var_dump($dataSet);
//echo $request->worker_name;
        //  $vv=Workers::ins_tblarr($request->worker_name);
        //echo $vv;


        //
        //  $catisde4 = Workers::all();
        //  return $catisde4->toArray();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Workers $workers
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Spec 004 B3 — AI prefill for the worker-add form. Accepts a Saudi Iqama /
     * passport / national ID scan (image/PDF), runs OCR, and returns the fields for
     * the screen to fill (worker_name, ssn, passport_no, dob, doe, dop, suggested
     * nationality). Nothing is saved here — the user confirms in the normal worker
     * form, which writes to the real `workers` table.
     */
    public function aiExtract(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:20480',
        ]);

        $file = $request->file('document');
        $dir = public_path('uploads/workers/ai');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $name = \Illuminate\Support\Str::random(8).'_'.time().'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $file->move($dir, $name);
        $abs = $dir.'/'.$name;

        try {
            $data = app(\App\Services\WorkerAiExtractor::class)->extract($abs);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message_out' => 'تعذّر استخراج البيانات: '.$e->getMessage()], 422);
        }

        \App\Services\AuditLogger::log('worker', null, \App\Services\AuditLogger::EXTRACT, [
            'note' => 'استخراج وثيقة هوية عامل بالذكاء الاصطناعي',
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'worker_name' => $data['worker_name'],
                'ssn' => $data['ssn'],
                'passport_no' => $data['passport_no'],
                'dob' => $data['dob'],
                'doe' => $data['doe'],
                'dop' => $data['dop'],
                'nation_id' => $data['nation_id'],
                'nationality_name' => $data['nationality_name'],
                'confidence' => $data['field_confidence'],
                'document_url' => 'uploads/workers/ai/'.$name,
            ],
        ]);
    }
}
