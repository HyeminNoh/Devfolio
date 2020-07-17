<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if(Auth::check()){ // 로그인이 완료된 사용자
        return redirect('home');
    }
    return view('welcome');
})->name('root');

Route::get('/home', 'HomeController@index')->name('home');

/* social login */
Route::get('social/{provider}',[
    'as'=>'social.login',
    'uses' => 'SocialController@execute',
]);

Route::get('auth/logout', [
    'as' => 'sessions.destroy',
    'uses' => 'SessionsController@destroy',
]);

// Route::get('/user/detail', 'UserController@show');
