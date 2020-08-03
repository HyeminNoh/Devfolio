<?php

use App\User;
use App\Repositories\UserRepository;
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

/* main page */
Route::get('/', function (UserRepository $userRepo) {
    return view('welcome', ['userList'=>$userRepo->all()]);
})->name('root');

/* social login */
Route::get('social/{provider}', [
    'as' => 'social.login',
    'uses' => 'SocialController@execute',
]);

/* session destory */
Route::get('auth/logout', [
    'as' => 'sessions.destroy',
    'uses' => 'SessionsController@destroy',
]);

/* show report page */
Route::get('report/{githubId}', [
    'as' => 'report.show',
    'uses' => 'ReportController@show'
]);

/* update report data */
Route::get('report/{userIdx}/{type}/update', [
    'as' => 'skill.update',
    'uses' => 'ReportController@update'
]);

/* get report json data */
Route::get('report/{userIdx}/{type}/get', 'ReportController@get');
