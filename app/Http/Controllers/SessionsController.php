<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'destroy']);
    }

    public function destroy(){
        auth()->logout();

        return redirect('/');
    }

    protected function respondError($message){
        return back()->withInput();
    }
}
