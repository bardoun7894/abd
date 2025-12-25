<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Traits\ApimtitTrait;
//use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
                use ApimtitTrait;

    public function index(){
      //  dd(Auth::user());
       $response = $this->jawdaapi();
//dd($response);

       //echo  $response->msg[0]->msgNo;

   // echo   $response['apps']['msg'] ;

$msgNo= $response['apps']['msg']['msgNo'];
$msgText= $response['apps']['msg']['msgText'];
if($msgText==1){
$app_no= $response['apps']['data'][0]['app_no'];
$ser_no= $response['apps']['data'][0]['ser_no'];
$app_status= $response['apps']['data'][0]['app_status'];
$desc_app_status= $response['apps']['data'][0]['desc_app_status'];
$min_id= $response['apps']['data'][0]['min_id'];
$min_name= $response['apps']['data'][0]['min_name'];
$serv_no= $response['apps']['data'][0]['serv_no'];
$serv_name= $response['apps']['data'][0]['serv_name'];
}
else{
    $app_no= '-';
$ser_no= '-';
$app_status= '-';
$desc_app_status= $response['apps']['msg']['msgText'];;
$min_id= '-';
$min_name= '-';
$serv_no='-';
$serv_name='-';

}

$apps_arr = array("app_no", "ser_no", "app_status", "desc_app_status", "min_id", "min_name", "serv_no", "serv_name");
                return view('dashboard/index',compact('msgNo','msgText', $apps_arr));

    }

}
