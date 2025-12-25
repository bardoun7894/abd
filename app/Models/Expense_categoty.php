<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense_categoty extends Model
{
    use HasFactory;
    protected $table = "expense_categoty";
    protected $primaryKey = 'expense_categoty_id';

    
    /**
     * Get all of the comments for the Worker
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

}
