<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Perm;
use PDF;
use App\Http\Traits\ApimtitTrait;
use App\Models\Shop;
use App\Models\User;

class PurchaseController extends Controller
{
    use ApimtitTrait;
    public function __construct()
    {
        $this->middleware('ishaveaccess:9');
    }

    public function index()
    {
        if (Perm::get_function_access(55)) {
            $page_title = 'ادخال بيانات مصاريف شراء ';
            //$manager = DB::table('manager')->get();
            $shops = Shop::get();
            $manager = $this->get_manager();
            $const = array("shops", "page_title", "manager");
            return view('dashboard.purchase.index', compact($const));
        }
    }

    public function views()
    {
        if (Perm::get_function_access(56) || Perm::get_function_access(57) || Perm::get_function_access(58)) {
            //$manager = DB::table('manager')->get();
            $manager = $this->get_manager();
            $shops = Shop::get();
            $create_users = User::get();
            $city = DB::table('city')->get();
            $page_title = 'عرض بيانات مصاريف شراء ';
            return view('dashboard.purchase.view', compact('manager', 'city', 'page_title', 'shops', 'create_users'));
        }
    }


    public function tbl(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(56) || Perm::get_function_access(57) || Perm::get_function_access(58))) {
            return view('dashboard.purchase.tbl_purchase', compact("request"));
        } else {
            return "Request Not Ajax Type";
        }
    }


    public function sel_purchase_list(Request $request)
    {
        $string = $request->q;
        $page = $request->page;
        $response = Purchase::sel_purchase_list($string, $page);
        echo json_encode($response);
    }


    public function ajax_search_purchase(Request $request)
    {
        if ($request->ajax() and (Perm::get_function_access(56) || Perm::get_function_access(57) || Perm::get_function_access(58))) {
            $purchase_no = $request->purchase_no;
            $purchase_dt_from = $request->purchase_dt_from;
            $purchase_dt_to = $request->purchase_dt_to;
            $purchase_respon = $request->purchase_respon;
            $manager_id = $request->manager_id;
            $shop_id = $request->shop_id;
            $create_users = $request->create_users;
            $list_total = Purchase::serachspendcount($purchase_no, $purchase_dt_from, $purchase_dt_to, $purchase_respon, $manager_id, $shop_id, $request->shops, $create_users);
            $list = Purchase::serachspenddata($purchase_no, $purchase_dt_from, $purchase_dt_to, $purchase_respon, $manager_id, $shop_id, $request->shops, $create_users);
            $data = array();
            $no = $_POST['start'];
            $i = 0;
            foreach ($list as $x) {
                $no++;
                $i++;
                $row = array();
                $row[] = $i;
                $row[] = $x->purchase_no;
                $row[] = Carbon::parse($x->purchase_dt)->format('d-m-Y');
                $row[] = $x->purchase_price;

                $row[] = number_format($x->purchase_price - $x->purchase_price / 1.15, 2);
                $row[] = number_format($x->purchase_price / 1.15, 2);
                $row[] = $x->tax_number;

                $shop = Shop::find($x->shop_id);
                if ($request->shops == "on")
                    $row[] = isset($shop) ? ($shop->shop_name . " - " . ($shop->municip->municip_no ?? "")) : "";
                else
                    $row[] = $x->manager_name;

                $row[] = $x->purchase_respon;
                $row[] = User::find($x->create_user)->name;

                $row[] = Carbon::parse($x->created_at)->format('d-m-Y');
                $row[] = $x->note;
                if (Perm::get_function_access(57) || Perm::get_function_access(58)) {
                    $opt = '<div class="btn-group btn-group-sm " role="group">';
                    if (Perm::get_function_access(57)) {
                        $opt .= '<a class="btn btn-sm btn-success btn-icon btn-icon-sm  upd_purchase" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.purchase.upd_purchase') . "'" . ' onclick="upd_purchase(' . "'" . $x->purchase_id . "'" . ')"> <i class="far fa-edit fa-fw"></i></a>';
                        $opt .= '<a class="btn btn-sm btn-primary btn-icon btn-icon-sm  upd_purchase" style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" data-url=' . "'" . route('dashboard.purchase.upd_purchase') . "'" . ' onclick="upd_purchase(' . "'" . $x->purchase_id . "',true" . ')"> <i class="far fa-eye fa-fw"></i></a>';
                    }
                    if (Perm::get_function_access(58)) {
                        $opt .= '<a class="btn btn-sm btn-danger btn-icon btn-icon-sm " style="margin-bottom: 5px;margin-top: 5px;margin-left:5px !important" onclick="del_purchase(' . "'" . $x->purchase_id . "'" . ')"> <i class="fas fa-trash-alt fa-fw "></i>  </a>';
                    }
                    $row[] = $opt;
                }
                $data[] = $row;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $list_total,
                "recordsFiltered" => $list_total,
                "data" => $data
            );
            echo json_encode($output);
        }
    }


    function del_purchase(Request $request)
    {
        if (Perm::get_function_access(58)) {
            $id = $request->id;
            try {
                $delete = DB::delete('delete from purchase where purchase_id = ?', [$id]);
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


    public function upd_purchase(Request $request)
    {
        if (Perm::get_function_access(57)) {

            $id = $request->id;
            $purchase = DB::table('purchase')->where('purchase_id', $id)->first();
            $shops = Shop::get();
            $can_edit = true;
            $view_page = $request["view"];
            if (!auth()->user()->isAdmin) {
                if ($purchase->create_user != auth()->user()->id) {
                    $request["view"] = "true";
                    $can_edit = false;
                }
            }
            $page_title = 'تعديل بيانات العمال';
            //$manager = DB::table('manager')->get();
            $manager = $this->get_manager();
            $city = DB::table('city')->get();
            $const = array("shops", "manager", "city", "page_title");
            $view = ($request["view"]);
            return view('dashboard.purchase.upd_purchase', compact('purchase', $const, 'view', "can_edit", "view_page"));
        }
    }


    public function store(Request $request)
    {
        if (Perm::get_function_access(55)) {
            $purchase_name_old = $request->old('purchase_name');
            $attributeNames = array(
                'purchase_no' => 'رقم الفاتورة',
                'purchase_dt' => 'تاريخ الفاتورة',
            );
            $validator = Validator::make($request->all(), [
                'purchase_no' => ['required', 'string', 'unique:purchase'],
                'purchase_dt' => ['required', 'date'],
            ]);

            if (($request->manager_id =="" and $request->shop_id=="") || (!isset($request->manager_id ) and !isset($request->shop_id ))) {
                $result['status'] = false;
                $result['message'] ="الرجاء اختيار قائد مجموعة , او محل ";
                $result['message_out'] ="الرجاء اختيار قائد مجموعة , او محل ";
                return response()->json($result);

            }


            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $purchasefile_url = '';
                if ($request->hasFile('purchasefile')) {
                    $purchasefile_name = time() . '.' . $request->purchasefile->extension();
                    $request->purchasefile->move(public_path('uploads/users/images/'), $purchasefile_name);
                    $purchasefile_url = 'uploads/users/images/' . $purchasefile_name;
                }
                $result2 = DB::table('purchase')->insertGetId([
                    'purchase_no' => $request->purchase_no,
                    'purchase_price' => $request->purchase_price,
                    'purchase_dt' => $request->purchase_dt,
                    'shop_id' => $request->shop_id ?? null,
                    'tax_number' => $request->tax_number,
                    'manager_id' => $request->manager_id ?? NULL,

                    'purchase_respon' => $request->purchase_respon,
                    'purchasefile' => $purchasefile_url,
                    'note' => $request->note,
                    'created_at' => Carbon::now(),
                    'create_user' => Auth::user()->id,
                ]);
                if ($result2 != '') {
                    $result['status'] = $result2;
                    $result['message_out'] = 'تم الحفظ بنجاح';
                } else {
                    if (File::exists($purchasefile_url)) {
                        File::delete($purchasefile_url);
                    }
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
        $purchase_id = $request->purchase_id;
        $purchasefile_url = $request->purchasefile_url;
        $type = $request->type;
        if ($type == 'purchase_attach') {
            if (File::exists($purchasefile_url)) {
                File::delete($purchasefile_url);
            }
            $result2 = DB::table('purchase_attach')->where('purchase_attach_id', $purchase_id)->delete();
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;
            echo json_encode($result);
        }


        if ($type == 'purchasefile') {
            if (File::exists($purchasefile_url)) {
                File::delete($purchasefile_url);
            }
            $result2 = DB::table('purchase')
                ->where('purchase_id', $purchase_id)
                ->update([
                    'purchasefile' => '',
                ]);
            $message = 'تم الحذف الملف';
            $result['status'] = true;
            $result['message'] = $message;
            echo json_encode($result);
        }
        if ($type == 'passportfile') {
            if (File::exists($purchasefile_url)) {
                File::delete($purchasefile_url);
            }
            $result2 = DB::table('purchase')
                ->where('purchase_id', $purchase_id)
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
        if (Perm::get_function_access(57)) {
            $id = $request->purchase_id_db;
            $purchase_name = $request->old('purchase_name');
            $attributeNames = array(
                'purchase_no' => 'رقم الفاتورة',
                'purchase_dt' => 'تاريخ الفاتورة',
            );
            $validator = Validator::make($request->all(), [
                'purchase_no' => ['required', Rule::unique('purchase', 'purchase_no')->ignore($id, 'purchase_id')],
                'purchase_dt' => ['required', 'date'],

            ]);
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $validator->errors();
                $result['message_out'] = '';
            } else {
                $ERROR_FLAG = 0;
                $purchasefile_url = '';
                if ($request->hasFile('purchasefile')) {
                    $purchasefile_name = time() . '.' . $request->purchasefile->extension();
                    $request->purchasefile->move(public_path('uploads/users/images/'), $purchasefile_name);
                    $purchasefile_url = 'uploads/users/images/' . $purchasefile_name;
                    if (File::exists($request->purchasefile_db)) {
                        File::delete($request->purchasefile_db);
                    }
                } else {
                    $purchasefile_url = $request->purchasefile_db;
                }
                $result2 = DB::table('purchase')
                    ->where('purchase_id', $id)
                    ->update([
                        'purchase_no' => $request->purchase_no,
                        'purchase_price' => $request->purchase_price,
                        'purchase_dt' => $request->purchase_dt,
                        'shop_id' => $request->shop_id ?? null,
                        'manager_id' => $request->manager_id ?? NULL,

                        'tax_number' => $request->tax_number,

                        'purchase_respon' => $request->purchase_respon,
                        'purchasefile' => $purchasefile_url,
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

    /**
     * T-B4 — AI prefill for the purchase (invoice) add form. Reuses the existing
     * invoice-extraction pipeline (InvoiceExtractionService::extractInvoice +
     * InvoicePurchaseMapper::buildPurchaseRow) rather than rebuilding it. Nothing
     * is saved here — the user reviews the prefilled fields and submits the normal
     * add form, which writes to the real `purchase` table via store().
     */
    public function aiExtract(Request $request)
    {
        // Spec 008 bundle 2 (ai-permissions) — gated separately from the rest of
        // the purchases module (which stays under ishaveaccess:9): only this AI
        // action needs function 212 (or the master 210).
        if (! Perm::ai_access(Perm::AI_PURCHASE_INVOICE)) {
            return response()->json(['status' => false, 'message_out' => 'ليست لديك صلاحية لاستخدام قراءة الفواتير بالذكاء الاصطناعي'], 403);
        }

        $request->validate([
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:20480',
        ]);

        $file = $request->file('invoice');
        $ds = app(\App\Services\DocumentStorage::class);
        $tmp = $ds->tempWorkingCopy($file);
        try {
            $extracted = app(\App\Services\InvoiceExtractionService::class)->extractInvoice($tmp);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message_out' => 'تعذّر استخراج بيانات الفاتورة: '.$e->getMessage()], 422);
        } finally {
            @unlink($tmp);
        }
        $stored = $ds->store($file, 'purchase');
        $fileUrl = route('dashboard.documents.serve', ['module' => 'purchase', 'filename' => $stored['filename']]);
        $extracted['image_path'] = $fileUrl;
        $extracted['batch_id'] = null;
        $extracted['page_number'] = null;
        $row = \App\Services\InvoicePurchaseMapper::buildPurchaseRow($extracted, null, null, (int) Auth::id());

        \App\Services\AuditLogger::log('purchase', null, \App\Services\AuditLogger::EXTRACT, [
            'note' => 'استخراج فاتورة شراء بالذكاء الاصطناعي',
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'purchase_no' => $row['purchase_no'],
                'purchase_dt' => $row['purchase_dt'],
                'purchase_respon' => $row['purchase_respon'],
                'purchase_price' => $row['purchase_price'],
                'tax_number' => $row['tax_number'],
                'note' => $row['note'],
                'needs_review' => $extracted['needs_review'] ?? false,
                'confidence' => $extracted['confidence'] ?? null,
                'invoice_file_url' => $extracted['image_path'],
            ],
        ]);
    }
}
