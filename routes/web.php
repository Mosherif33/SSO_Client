<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get("/login", function(Request $request) {
    $request->session()->put("state", $state =  Str::random(40));
    $query = http_build_query([
        "client_id" => "9b435c8c-c031-4bf8-ada0-d8e46a41a681",
        "redirect_uri" => "http://127.0.0.1:8080/callback",
        "response_type" => "code",
        "scope" => "",
        "state" => $state
    ]);
    return redirect("http://127.0.0.1:8000/oauth/authorize?" . $query);
});

Route::get("/callback", function (Request $request) {
    $state = $request->session()->pull("state");

    throw_unless(strlen($state) > 0 && $state == $request->state, \InvalidArgumentException::class);

    $response = Http::asForm()->post(
        "http://127.0.0.1:8000/oauth/token",
        [
            "grant_type" => "authorization_code",
            "client_id" => "9b435c8c-c031-4bf8-ada0-d8e46a41a681",
            "client_secret" => "e8be0fbnRTwPMEVEAcqQvlOaLaM2dSWJRSOPPzmR",
            "redirect_uri" => "http://127.0.0.1:8080/callback",
            "code" => $request->code
        ]
    );

    return $response->json();
});

Route::get("/authuser", function(Request $request) {
    $access_token = $request->session()->get("access_token");
    $response = Http::withHeaders([
        "Accept" => "application/json",
        "Authorization" => "Bearer " . $access_token
    ])->get("http://127.0.0.1:8000/api/user");
    return $response->json();
});
