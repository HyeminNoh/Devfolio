<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'destroy']);
    }

    public function destroy(){
        $username = auth()->user()->name;
        auth()->logout();

        Log::info('Sign out: '.$username);
        return redirect('/');
    }
}
