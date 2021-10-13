<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 'ApiController@login'); // Login
Route::post('/match_otp', 'ApiController@matchOTP'); // Match OTP
Route::get('/check_user/{user_id}/{device_id}', 'ApiController@checkUser'); // Check User
Route::get('/departments', 'ApiController@departments'); // Departments list
Route::get('/get_advisors/{dealer_id}', 'ApiController@getAdvisors'); // Advisor list
Route::get('/get_models/{dealer_id}', 'ApiController@getModels'); // Model list
Route::get('/get_treatments/{dealer_id}/{model_id}', 'ApiController@getTreatments'); // Treatment list
Route::post('/add_job', 'ApiController@addJob'); // Add job
Route::get('/get_jobs/{user_id}/{date}', 'ApiController@getJobs'); // list of jobs
Route::post('/edit_job', 'ApiController@editJob'); // Edit job

Route::get('/get_images', 'ApiController@getImages'); // list of images
Route::get('/get_videos', 'ApiController@getVideos'); // list of videos
Route::get('/get_calendar/{user_id}', 'ApiController@getCalendar'); // get calendar for home screen
Route::get('/get_calendar_by_month/{user_id}/{monthYear}', 'ApiController@getCalendarByMonth'); // get calendar for home screen
Route::post('/add_services', 'ApiController@addServices'); // Add service load
Route::get('/get_url', 'ApiController@getUrl'); // Get url for web view
Route::get('/search_job/{user_id}/{regn_no}', 'ApiController@searchJob'); // Search job by regn no.
Route::get('/search_history_job/{dealer_id}/{regn_no}', 'ApiController@searchHistoryJob'); // Search history job by regn no.
Route::get('/get_app_version', 'ApiController@getAppVersion'); // Get url for web view
Route::get('/privacy_policy', 'ApiController@privacyPolicy'); // Get privacy policy for web view
Route::post('/attendance', 'ApiController@attendance'); // Mark attendance for app users
Route::post('/check_attendance', 'ApiController@checkAttendance'); // check attendance for app users

Route::get('send_mail','HomeController@send_mail');

Route::post('get_distance','ApiController@getDistance');
Route::post('my_profile','ApiController@myProfile');