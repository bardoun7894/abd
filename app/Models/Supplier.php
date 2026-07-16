<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Suppliers master (Spec 002 FR-105) in the main DB. Extracted invoices are matched
 * to a known supplier by tax number (exact) or name (fuzzy); a new supplier is
 * created on approval when no confident match exists.
 */
class Supplier extends Model
{
    use SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'name', 'tax_number', 'cr_number', 'phone', 'address', 'notes', 'create_user',
    ];
}
