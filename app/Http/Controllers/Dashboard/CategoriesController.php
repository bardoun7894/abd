<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\User;


class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //  return view('')
        //   echo "dddddddddd";
        // $results = DB::select( DB::raw("SELECT * FROM categories WHERE 1=1") );
        // $results = DB::select('select * from users where id = ?', [1]);
        //$results = DB::select( DB::raw("SELECT * FROM categories WHERE 1=1") );

//$results = Categories::all();
        $results = Categories::xxx();
//dd($results);


        $results1 = Categories::all();
//dd($results1);


        $catisdel = Categories::isdel()->get();
//dd($catisdel);


        $catisde3 = Categories::status(0)->get();
//dd($catisde3);


        $catisde4 = Categories::first();
        //return $catisde4->attributesToArray();
//dd($catisde4->attributesToArray());


        $catisde4 = User::all();
//return $catisde4->toArray();

//$catisdel2=Categories::select("*")->status(0)->get();
//dd($catisdel2);

//Post::select("*")->status(1)->get();

        return view('dashboard.categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        //  $request->post(emp_name);
        // echo $request->post('emp_name');

        // $name = $request->input('name');
        //  $name = $request->input('name', 'Sally');//default value
        //  $name = $request->input('products.0.name');//array
        //  $names = $request->input('products.*.name');//array
        //  $input = $request->input();
        //  $name = $request->query('name');

        //  $query = $request->query();
        //  $name = $request->input('user.name');
        //  $name = $request->string('name')->trim();
        //  $archived = $request->boolean('archived');
        //  $birthday = $request->date('birthday');
        //  $elapsed = $request->date('elapsed', '!H:i', 'Europe/Madrid');

//$name = $request->name;
//$input = $request->only('username', 'password');
//$input = $request->except(['credit_card']);

        /*if ($request->has('name')) {
            //
        }
        */

        /*

        if ($request->has(['name', 'email'])) {
            //
        }
        */

//$request->flash();
//$request->flashOnly(['username', 'email']);
//$request->flashExcept('password');
//return redirect('form')->withInput();
// return redirect()->route('user.create')->withInput();

        /*return redirect('form')->withInput(
           $request->except('password')
       );*/


        /*$username = $request->old('username');
        <input type="text" name="username" value="{{ old('username') }}">*/


        $request->all();
        $result['status'] = true;
        $result['message'] = "تم حفظ البيانات بنجاح";

        echo json_encode($result);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
