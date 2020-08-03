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

Route::get('/', function (UserRepository $userRepo) {
    return view('welcome', ['userList'=>$userRepo->all()]);
})->name('root');

Route::get('portfolio/{githubId}', function (UserRepository $userRepo, $githubId) {
    return view('portfolio', ['user' => $userRepo->getGithub($githubId)]);
})->name('portfolio');

/* social login */
Route::get('social/{provider}', [
    'as' => 'social.login',
    'uses' => 'SocialController@execute',
]);

Route::get('auth/logout', [
    'as' => 'sessions.destroy',
    'uses' => 'SessionsController@destroy',
]);

Route::get('report/{userIdx}/{type}/update', [
    'as' => 'skill.update',
    'uses' => 'ReportController@update'
]);

Route::get('report/{userIdx}/{type}/show', 'ReportController@show');
