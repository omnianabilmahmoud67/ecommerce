<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Neighborhood;
use App\Models\Role;
use App\Models\Mail_list;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\ActiveCode;
use Mail;
use Session;
use Auth;

class mainController extends Controller
{
    #site language
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

    #home page
    public function home()
    {
        return view('site.index');
    }

    #common_question page
    public function common_question()
    {
        return view('site.common_question');
    }

    #site_condition page
    public function site_condition()
    {
        return view('site.site_condition');
    }

    #all_events page
    public function all_events()
    {
        return view('site.all_events');
    }

    #event page
    public function event($id)
    {
        $event = Event::whereId($id)->firstOrFail();
        return view('site.event', ['event' => $event]);
    }

    #contact_us page
    public function contact_us()
    {
        return view('site.contact_us');
    }

    #post_contact_us page
    public function post_contact_us(Request $request)
    {
        #store contact us
        $request->request->add(['seen' => '0']);
        Contact::create($request->all());

        #success response
        session()->flash('success', awtTrans('تم الارسال بنجاح'));
        return back();
    }

    #mail_list
    public function mail_list(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email'      => 'required|email',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #check email
        $check = Mail_list::whereEmail($request->email)->first();
        if(isset($check)) return response()->json(['value' => 0, 'msg' => awtTrans('البريد الالكتروني مشترك بالفعل')]);

        #store Mail_list
        Mail_list::create($request->except(['_token']));

        #success response
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الاشتراك بنجاح')]);
    }

    #page
    public function page($title)
    {
        $page = get_page($title);
        return view('site.page', ['page' => $page]);
    }

    #login page
    public function login()
    {
        return view('site.login');
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
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            #update user's device
            if (!is_null($request->device_id)) update_device(Auth::user(), $request->device_id, 'web');

            if (Auth::check() && Auth::user()->blocked != '0') {
                Session::put('danger' , awtTrans('قم بمراجعة حسابك مع الادارة'));
                Auth::logout();
                return response()->json(['value' => 0, 'msg' => awtTrans('قم بمراجعة حسابك مع الادارة')]);
            }
            //success response
            else{
                session()->flash('success', awtTrans('تم تسجيل الدخول بنجاح'));
            }

            #Success response
            return response()->json(['value' => 1, 'msg' => awtTrans('تم تسجيل الدخول بنجاح')]);
        }

        #faild response
        return response()->json(['value' => 0, 'msg' => awtTrans('البيانات غير صحيحة قم بإعادة المحاولة')]);
    }

    #register page
    public function register($type)
    {
        if ($type == 'all')   return view('site.register');
        if ($type == 'coach')   return view('site.coach_register');
        if ($type == 'company') return view('site.company_register');
        if ($type == 'dietitians') return view('site.dietitians_register');
        if ($type == 'courses') return view('site.courses_register');
        if ($type == 'online_courses') return view('site.online_courses_register');
        if ($type == 'designers') return view('site.designers_register');
        return view('site.client_register');
    }

    #register post
    public function post_register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo'                  => 'nullable|image',
            'first_name'             => 'required|max:255',
            'last_name'              => 'nullable|max:255',
            'email'                  => 'required|max:255|email|unique:users,email',
            'phone'                  => 'required|min:7|max:13',
            // 'phone_code'             => 'required',
            'password'               => 'required|confirmed|min:6|max:255',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

            #avatar
        if ($request->hasFile('photo')) $avatar = upload_image($request->file('photo'), 'public/images/users');
        else $avatar = '/public/user.png';
        #store new user
        $request->request->add([
            'blocked' => 0, 'avatar' => $avatar,
            'role_id' => Role::first()->id
        ]);
        $user = User::create($request->except(['_token', 'password_confirmation' ,'photo']));

        #LOGIN
        Auth::login($user);

        session()->flash('success', awtTrans('تم تسجيل بياناتك بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم تسجيل بياناتك بنجاح')]);
    }

    #logout page
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    #forget page
    public function forget_password()
    {
        return view('site.forget_password');
    }

    #forget post
    public function post_forget_password(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email'      => 'required|email',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        $user = User::whereEmail($request->email)->first();
        if (!isset($user))  return response()->json(['value' => 0, 'msg' => awtTrans('بريد إلكتروني غير مسجل')]);

        /** If Login Success But User's account has block **/
        if ($user->checked == '0') {
            Session::put('danger' , awtTrans('قم بمراجعة حسابك مع الادارة'));
            Auth::logout();
            return response()->json(['value' => 0, 'msg' => awtTrans('قم بمراجعة حسابك مع الادارة')]);
        }

        $code = active_code();
        $user->code = $code;
        $user->save();

        /** Send Code To User's Using Email**/
        $msg = awtTrans('لتعديل كلمة المرور استخدم هذا الرابط : ') . '<br>' . '<a href="' . url('reset-password/' . $user->id . '/' . $code) . '">' . url('reset-password/' . $user->id . '/' . $code) . '</a>';
        Mail::to($request->email)->send(new ActiveCode($msg));

        Session::flash('success', awtTrans('تم ارسال رابط الى بريدك الإلكتروني'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم ارسال رابط الى بريدك الإلكتروني')]);
    }

    #reset page
    public function reset_password($id , $code)
    {
        $user = User::whereId($id)->whereCode($code)->firstOrFail();
        return view('site.reset_password', ['user' => $user]);
    }

    #reset post
    public function post_reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password'    => 'required|confirmed|min:6|max:255',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #check user
        $user = User::whereId($request->user_id)->firstOrFail();
        if (!isset($user)) return response()->json(['value' => 0, 'msg' => awtTrans('حدث خطأ ما')]);

        #success code so reset new password
        $user->password = $request->password;
        $user->save();

        Session::flash('success', awtTrans('تم تعديل كلمة المرور بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم تعديل كلمة المرور بنجاح')]);
    }
}
