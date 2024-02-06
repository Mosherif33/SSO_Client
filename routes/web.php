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

Route::get("/authuser", function() {
    $access_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI5YjQzNWM4Yy1jMDMxLTRiZjgtYWRhMC1kOGU0NmE0MWE2ODEiLCJqdGkiOiI1ZDgyMDUyOTVhZDJhY2NkMzAyNjZhOTUyMmE2MjMzZTc1NDU0OTkzNTU4NGVmMGRlOTc4MGQ2NDE3YzE2YjQ2OGE4YWVjOGEzOTkwNDFjYiIsImlhdCI6MTcwNzIzMjY0Ny4zNjQ1NzEsIm5iZiI6MTcwNzIzMjY0Ny4zNjQ1OSwiZXhwIjoxNzA3MzE5MDQ3LjMyMTI1Miwic3ViIjoiMSIsInNjb3BlcyI6W119.QHEfSnmFoHc_IE6cfrBKVS0RFRASpQjb2RrIUJKpxykjCBYx_C2xGJfWItIaeyX-SmM7vgm8XiWDbxehZHpygdlXLbOKHTSLIMSu_LFPXSl_6A4ySyj9AI6QvUtw6TsAY7hqedarCAxF6Pf2U_SUcurTOsctps1JHNdozXcghICcYjC_aBmhuJonhQb8HdZnI-3Do2mDpnyCSGBf1oFgKsJigbvRqYJZZ3eFRzu9FL7W_fa6u-HK2RwV2yrIG-tRoctVPLjdJF1nhJIQInY08IvdS259oJbaVq3rIXS97NWyVOw8OuZ74z2ztooXDuwYVP9ItmUXyQSEu0tHgfXY7ie6heJuxl_NjJD87GOAlUVqaraKE5dZek1n5SAN27HUi2uHjbtLwY95lFLl2sqdU7VGyXrLc-Qm1YlF1Ztld5B2xwGl8q60OyIB0OK_0PKZ7Zv81YzlIOixX77w7cLX03CP8AtNYdRWVW08xRWZj1XZ_5Zp9HFPi8pRZuF9k_cE9h_MnzlOmJil6vyAdW3uhaoonLiRYY1NgxEldelcm62wAFBjpYlSn1vzfhXphIpuiagDla-WghGbLCjtlXlQJqVAcqkR5pK5nRuUQa6c_We7seHwXrL5DtbtOBGH_jkq63VBG4Yw4BQSrwW6zSqAZVsVbr9trFqvBn19zq95vls";
    $response = Http::withHeaders([
        "Accept" => "application/json",
        "Authorization" => "Bearer " . $access_token,
    ])->get("http://127.0.0.1:8000/api/user");
    return $response->json();
});
