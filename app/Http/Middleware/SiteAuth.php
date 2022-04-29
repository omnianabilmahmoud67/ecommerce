<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class SiteAuth
{
    public function handle($request, Closure $next)
    {
        //if user not login
        if (Auth::guest()) {
            $msg = awtTrans('قم بتسجيل دخولك اولا');
            if($request->ajax()) return response()->json(['value' => 2, 'msg' => $msg]);
            Session::put('danger' , $msg);
            return redirect()->route('site_home');
        }
        //if user not active
        elseif (Auth::user()->active != '1') {
            $msg = awtTrans('في انتظار تفعيل حسابك من قبل الادارة');
            Auth::logout();
            if($request->ajax()) return response()->json(['value' => 2, 'msg' => $msg]);
            Session::put('danger' , $msg);
            return redirect()->route('site_home');
        }
        //if user blocked
        elseif (Auth::user()->blocked != '0') {
            $msg = awtTrans('قم بمراجعة حسابك مع الادارة');
            Auth::logout();
            if($request->ajax()) return response()->json(['value' => 2, 'msg' => $msg]);
            Session::put('danger' , $msg);
            return redirect()->route('site_contact');
        }

        return $next($request);
    }
}
