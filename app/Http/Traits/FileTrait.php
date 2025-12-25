<?php
namespace App\Http\Traits;
use Illuminate\Support\Str;
/*use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;*/

Trait FileTrait
{
    public function uploadFile($file, $path)
    {
       if ( $file ) {
           $name= $file->getClientOriginalName();
           $file_name       = Str::random(5).'.'.$name;
          // $file->move(public_path($path),$file_name);
           $file->move(public_path('uploads/mol/'), $file_name);

           return $file_name;
       }
    }
}
