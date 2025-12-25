<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    // تعريف الحقول التي يمكن تعبئتها
    protected $fillable = [
        'owner_name', 'owner_id', 'vehicle_type', 'plate_number',
        'serial_number', 'model', 'color', 'license_id', 'license_serial',
        'license_image', 'license_expiry', 'custodian_name', 'custodian_phone',
        'insurance_company', 'policy_number', 'insurance_issue', 'insurance_expiry', 'insurance_image',
        'operation_card_number', 'operation_card_issue', 'operation_card_expiry', 'operation_card_image',
        'manager_id', 'custodian_id', // الحقول المضافة
        'driver_card_number', 'driver_name', 'driver_id',
        'driver_license_category', 'driver_license_image', 'driver_license_expiry','byUser'
    ];

    /**
     * Get the user that owns the Vehicle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manager()
    {
        return $this->belongsTo(Manager::class, 'manager_id');
    }



        /**
     * Get the user that owns the Vehicle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'byUser');
    }


}
