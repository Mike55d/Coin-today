<?php

namespace App\Http\Controllers;

use App\Models\ApiTrack;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return the server IP if the client IP is not found using this method.
    }

    public function home(): View
    {
        $currencies = Http::withoutVerifying()->get('http://api.currencylayer.com/list', [
            'access_key' => env("CURRENCY_LAYER_KEY")
        ]);
        return view('home', ['currencies' => $currencies->json()['currencies']]);
    }

    public function getCurrency(Request $request)
    {
        $userIp = $this->getIp();
        $userRequests = ApiTrack::all()->where('ip_user', $userIp)->values();
        if (count($userRequests) > 4) {
            return response('Limit requests.', 500);
        }
        ApiTrack::create([
            'ip_user' => $userIp,
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'amount' => $request->query('amount'),
        ]);
        $convert = Http::withoutVerifying()->get('http://api.currencylayer.com/convert', [
            'access_key' => env("CURRENCY_LAYER_KEY"),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'amount' => $request->query('amount'),
        ]);
        return $convert->json();
    }
}
