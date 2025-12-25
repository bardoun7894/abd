<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop_rent extends Model
{
    use HasFactory;
    protected $table = "shop_rent";
    protected $primaryKey = 'shop_rent_id';
    protected $guarded = [];

}
