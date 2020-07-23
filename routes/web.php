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

Route::get('contribution/store', [
    'as'=>'contribution.store',
    'uses' => 'ContributionController@store'
]);

Route::get('contribution/update', [
    'as'=>'contribution.update',
    'uses' => 'ContributionController@update'
]);

Route::get('contribution/show', 'ContributionController@show');

Route::get('repository/store', [
    'as'=>'repository.store',
    'uses' => 'RepositoryController@store'
]);

Route::get('repository/update', [
    'as'=>'repository.update',
    'uses' => 'RepositoryController@update'
]);

Route::get('repository/show', 'RepositoryController@show');

Route::get('skill/store', [
    'as'=>'skill.store',
    'uses' => 'SkillController@store'
]);

Route::get('skill/update', [
    'as'=>'skill.update',
    'uses' => 'SkillController@update'
]);

Route::get('skill/show', 'SkillController@show');
