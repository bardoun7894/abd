<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CashboxLedger;
use App\Models\CashReceipt;
use App\Services\CashboxService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;
use Perm;

/**
 * Spec 008 bundle 1 (cashbox): /cashbox ledger page + server-side DataTable +
 * receipt/void endpoints + printable voucher PDF. Guarded by per_function 220
 * (view) / 221 (void) — see 2026_07_20_120200_seed_cashbox_permissions.php.
 * All actual writes go through CashboxService, never direct table inserts.
 */
class CashboxController extends Controller
{
    private const PERM_VIEW = 220;
    private const PERM_VOID = 221;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (! Perm::get_function_access(self::PERM_VIEW)) {
            return redirect()->route('show_not_allow');
        }

        $service = new CashboxService();

        return view('dashboard.cashbox.index', [
            'balance' => $service->currentBalance(),
            'canVoid' => Perm::get_function_access(self::PERM_VOID),
        ]);
    }

    public function ajax_search(Request $request)
    {
        if (! Perm::get_function_access(self::PERM_VIEW)) {
            return response()->json(['error' => 'ليس لديك صلاحية'], 403);
        }

        $query = CashboxLedger::query()
            ->betweenDates($request->input('from'), $request->input('to'))
            ->sourceType($request->input('source_type'))
            ->changedBy($request->input('change_user'));

        $total = CashboxLedger::count();
        $filtered = (clone $query)->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 50);
        $length = $length > 0 ? $length : 50;

        $rows = $query->orderByDesc('entry_id')->skip($start)->take($length)->get();

        $receiptIds = $rows->pluck('receipt_id')->unique()->filter()->values();
        $receipts = CashReceipt::whereIn('receipt_id', $receiptIds)->get()->keyBy('receipt_id');

        $userIds = $rows->pluck('change_user')->unique()->filter()->values();
        $users = $userIds->isEmpty() ? collect() : DB::table('users')->whereIn('id', $userIds)->pluck('name', 'id');

        $data = [];
        $i = $start;
        foreach ($rows as $row) {
            $i++;
            $receipt = $receipts->get($row->receipt_id);
            $isVoid = $receipt && (int) $receipt->is_void === 1;
            $directionLabel = $row->direction === 'in' ? 'قبض' : ($row->reversal_of_entry_id ? 'إلغاء (عكسي)' : 'صرف');
            $directionCls = $row->direction === 'in' ? 'badge-light-success' : 'badge-light-danger';

            $actions = '';
            if ($receipt) {
                $actions .= '<a class="btn btn-sm btn-light-primary" target="_blank" href="' . route('dashboard.cashbox.receipt.print', $receipt->receipt_id) . '">طباعة</a> ';
                // Source types with their own module-owned reversal flow (currently
                // only shop_rentpay — see ShopController::rentpayVoid, which voids the
                // receipt AND flips rentpay_status back to unpaid in one transaction)
                // must NOT also expose the generic void button here: voiding straight
                // from the ledger page would reverse the cash entry without touching
                // rentpay_status, leaving the rent row showing "مدفوع" against a voided
                // receipt. Void those from their own module screen instead.
                $hasModuleOwnedVoid = in_array($row->source_type, ['shop_rentpay'], true);
                if (Perm::get_function_access(self::PERM_VOID) && $row->direction === 'in' && ! $isVoid && ! $hasModuleOwnedVoid) {
                    $actions .= '<button type="button" class="btn btn-sm btn-light-danger cashbox_void" data-id="' . $receipt->receipt_id . '">إلغاء</button>';
                }
            }

            $data[] = [
                $i,
                $row->change_at ? Carbon::parse($row->change_at)->format('d-m-Y H:i') : '',
                $row->source_type,
                '<span class="badge ' . $directionCls . '">' . $directionLabel . '</span>',
                number_format((float) $row->amount, 2),
                number_format((float) $row->balance_after, 2),
                $receipt->payer_name ?? '',
                $users->get($row->change_user, ''),
                $isVoid ? '<span class="badge badge-light-danger">ملغى</span>' : '<span class="badge badge-light-success">سارٍ</span>',
                $actions,
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    public function storeReceipt(Request $request)
    {
        if (! Perm::get_function_access(self::PERM_VIEW)) {
            return response()->json(['status' => false, 'message_out' => 'ليس لديك صلاحية'], 403);
        }

        $request->validate([
            'source_type' => 'required|string|max:20',
            'source_id' => 'required|integer',
            'amount' => 'required|numeric|gt:0',
            'receipt_date' => 'required|date',
            'payer_name' => 'nullable|string|max:255',
        ]);

        try {
            $receipt = (new CashboxService())->recordReceipt([
                'source_type' => $request->input('source_type'),
                'source_id' => $request->input('source_id'),
                'amount' => $request->input('amount'),
                'receipt_date' => $request->input('receipt_date'),
                'payer_name' => $request->input('payer_name'),
                'received_by' => Auth::id(),
                'note' => $request->input('note'),
                'create_user' => Auth::id(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message_out' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => true,
            'receipt_id' => $receipt->receipt_id,
            'receipt_no' => $receipt->receipt_no,
            'message_out' => 'تم تسجيل سند القبض',
        ]);
    }

    public function voidReceipt(Request $request)
    {
        if (! Perm::get_function_access(self::PERM_VOID)) {
            return response()->json(['status' => false, 'message_out' => 'ليس لديك صلاحية'], 403);
        }

        $request->validate([
            'receipt_id' => 'required|integer',
            'reason' => 'required|string',
        ]);

        if (trim((string) $request->input('reason')) === '') {
            return response()->json(['status' => false, 'message_out' => 'سبب الإلغاء مطلوب'], 422);
        }

        try {
            (new CashboxService())->voidReceipt(
                (int) $request->input('receipt_id'),
                $request->input('reason'),
                Auth::id()
            );
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message_out' => $e->getMessage()], 422);
        }

        return response()->json(['status' => true, 'message_out' => 'تم إلغاء السند']);
    }

    public function print($id)
    {
        if (! Perm::get_function_access(self::PERM_VIEW)) {
            return redirect()->route('show_not_allow');
        }

        $receipt = CashReceipt::where('receipt_id', $id)->firstOrFail();
        $receivedByName = $receipt->received_by ? DB::table('users')->where('id', $receipt->received_by)->value('name') : '';

        $html = view('dashboard.cashbox.receipt_pdf', [
            'receipt' => $receipt,
            'receivedByName' => $receivedByName,
        ])->render();

        PDF::Output('receipt-' . $receipt->receipt_no . '.pdf', 'I');
    }
}
