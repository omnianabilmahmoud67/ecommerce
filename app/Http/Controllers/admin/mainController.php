<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;
use Auth;

class mainController extends Controller
{
    #lang
    public function language($lang)
    {
        Session()->has('language') ? Session()->forget('language') : '';
        if ($lang == 'ar') {
            Session()->put('language', 'ar');
        } else {
            Session()->put('language', 'en');
        }
        return back();
    }

    #contact seen
    public function contact_seen(Request $request)
    {
        Contact::whereId($request->id)->update(['seen' => true]);
        return true;
    }

    #home page
    public function home()
    {
        return view('dashboard.index');
    }

    #login page
    public function login()
    {
        return view('dashboard.login');
    }

    #login post
    public function post_login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email'      => 'required|email',
                'password'   => 'required|min:6',
            ]
        );

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #login success
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password]))
            return response()->json(['value' => 1, 'msg' => awtTrans('تم تسجيل الدخول بنجاح')]);

        #faild success
        return response()->json(['value' => 0, 'msg' => awtTrans('البيانات غير صحيحة قم بإعادة المحاولة')]);
    }

    #logout page
    public function logout()
    {
        Auth::logout();
        return back();
    }
}
