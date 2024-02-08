<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class SSOController extends Controller
{
    public function getLogin(Request $request)
    {
        $request->session()->put("state", $state =  Str::random(40));
        $query = http_build_query([
        "client_id" => "9b4792ef-bec3-4bbe-8f5c-7a6bf6271cff",
        "redirect_url" => "http://127.0.0.1:8080/callback",
        "response_type" => "code",
        "scope" => "view-user",
        "state" => $state
        ]);
    return redirect("http://127.0.0.1:8000/oauth/authorize?" . $query);
    }

    public function getCallback(Request $request)
    {
        try {
        $state = $request->session()->pull("state");
        throw_unless(strlen($state) > 0 && $state == $request->state, \InvalidArgumentException::class);

        $response = Http::asForm()->post("http://127.0.0.1:8000/oauth/token", [
            "grant_type" => "authorization_code",
            "client_id" => "9b4792ef-bec3-4bbe-8f5c-7a6bf6271cff",
            "client_secret" => "agfJBaJGnDYVsEPOtkrXV9qN7uWHRUecjKhEPbLI",
            "redirect_uri" => "http://127.0.0.1:8080/callback",
            "code" => $request->code
        ]);

        $responseJson = $response->json();

        if (isset($responseJson['access_token'])) {
            $request->session()->put('access_token', $responseJson['access_token']);
            return redirect(route("sso.connect"));
        } else {
            Log::error('Access token not found in OAuth response', ['response' => $responseJson]);
            return response()->json(['error' => 'Access token not found'], 500);
        }
        } catch (\Exception $e) {
            Log::error('Error processing callback', ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function connectUser(Request $request)
    {
        $access_token = $request->session()->get("access_token");
        $response = Http::withHeaders([
            "Accept" => "application/json",
            "Authorization" => "Bearer " . $access_token,
        ])->get("http://127.0.0.1:8000/api/user");
        return $response->json();
    }
}
