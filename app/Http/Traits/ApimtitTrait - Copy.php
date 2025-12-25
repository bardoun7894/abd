<?php
namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

//use App\Models\Student;
trait ApimtitTrait {
    public function jawdaapi() {
        // Fetch all the students from the 'student' table.
      //  $student = Student::all();
        //return view('welcome')->with(compact('student'));
      //  dd(Auth::user());




$user_id= Auth::user()->id;

//$user_id= 413346586;



//echo $user_id;
$response = Http::withHeaders([
'TOKEN' => 'XyYRLm5g6bDctPk2jtV8xiNnBWaU2KLeEmXDzJOY2SCSqyrPsQ'])->get('https://e.services.gov.ps/api/ministry/getAppsByServiceIDAndIdNo/'.$user_id);
$jsonData = $response->json();

return $jsonData;
//dd($jsonData);
//echo "<pre> status:";
/*print_r($response->status());

        echo "<br/> ok:";

        print_r($response->ok());

        echo "<br/> successful:";

        print_r($response->successful());

        echo "<br/> serverError:";

        print_r($response->serverError());

        echo "<br/> clientError:";

        print_r($response->clientError());

        echo "<br/> headers:";

        print_r($response->headers());*/
        }


        public function checkPermission() {


    $user_id= Auth::user()->id;
dd($user_id);

            }
}
