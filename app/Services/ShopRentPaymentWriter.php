<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The single place that writes دفعات الإيجار into the legacy `shop_rentpay`
 * table.
 *
 * There are two entirely separate ways a lease enters this system — the shop
 * screen (ShopController, contract attached to a shop) and the Leases AI module
 * (LeaseController, a batch of contracts reviewed and approved). They used to
 * be disconnected: the Leases module wrote only to `lease_contracts` /
 * `lease_payments`, which the «ادارة دفعات الايجار» screen never reads, so a
 * lease approved there produced no دفعات anywhere the client could see.
 *
 * Both now funnel through this class so the insert shape, the never-duplicate
 * rule, and the "look exactly like an employee entry" convention cannot drift
 * apart between them.
 */
class ShopRentPaymentWriter
{
    /** Does this shop already have any دفعات? Generation must never overwrite. */
    public function shopHasPayments($shopId): bool
    {
        return DB::table('shop_rentpay')->where('shop_id', $shopId)->exists();
    }

    /**
     * Insert one row per scheduled payment.
     *
     * @param array $rows LeaseScheduleGenerator rows: ['due_date' => , 'amount' => ]
     * @return int number of rows written
     */
    public function write($shopId, array $rows, $userId): int
    {
        $now = Carbon::now();
        $written = 0;

        foreach ($rows as $row) {
            DB::table('shop_rentpay')->insert([
                'shop_id' => $shopId,
                'rentpay_dt' => $row['due_date'],
                'rentpay_price' => $row['amount'],
                // Deliberately NULL, exactly like a manually-added دفعة (client
                // feedback 2026-07-26: "اجعلها حالها من حال مدخلات الموظف").
                // Provenance is not lost — it stays in laravel.log and in the
                // create_user / created_at columns.
                'rentpay_note' => null,
                'rentpay_status' => 'unpaid',
                'created_at' => $now,
                'updated_at' => $now,
                'create_user' => $userId,
                'update_user' => $userId,
            ]);
            $written++;
        }

        return $written;
    }
}
