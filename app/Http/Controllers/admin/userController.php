<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Validator;
use App\User;
use Illuminate\Http\Request;

class userController extends Controller
{
    #index
    public function index()
    {
        $data = get_users_by('client', 'asc', 0);
        return view('dashboard.users', compact('data'));
    }

    #store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo'      => 'nullable|image',
            'first_name' => 'required|max:255',
            'last_name'  => 'nullable|max:255',
            'email'      => 'required|max:255|email|unique:users,email',
            'phone'      => 'required|min:9|max:13',
            'phone_code' => 'required',
            'password'   => 'required|min:6|max:255',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #avatar
        if ($request->hasFile('photo')) $avatar = upload_image($request->file('photo'), 'public/images/users');
        else $avatar = '/public/user.png';
        #store new client
        $request->request->add(['active' => 1, 'blocked' => 0, 'avatar' => $avatar, 'user_type' => 'client', 'role_id' => Role::first()->id]);
        $user = User::create($request->except(['_token', 'photo']));

        #add adminReport
        admin_report('أضافة العميل ' . $user->name);

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }

    #update
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo'      => 'nullable|image',
            'first_name' => 'required|max:255',
            'last_name'  => 'nullable|max:255',
            'email'      => 'required|max:255|email|unique:users,email,' . $request->id,
            'phone'      => 'required|min:9|max:13',
            'phone_code' => 'required',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #avatar
        if ($request->hasFile('photo')) $request->request->add(['avatar' => upload_image($request->file('photo'), 'public/images/users')]);
        #update client
        $user = User::whereId($request->id)->first();
        $user->update($request->except(['_token', 'photo']));

        #add adminReport
        admin_report('تعديل العميل ' . $user->name);

        #success response
        session()->flash('success', awtTrans('تم التعديل بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم التعديل بنجاح')]);
    }

    #delete one
    public function delete(Request $request)
    {
        #get client
        $user = User::whereId($request->id)->firstOrFail();
        $name = $user->name;

        #send FCM

        #delete client
        $user->delete();

        #add adminReport
        admin_report('حذف العميل ' . $name);

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #delete more than one or all
    public function delete_all(Request $request)
    {
        $type = $request->type;
        #get clients
        if ($type == 'all') $users = User::where('user_type', 'client')->get();
        else {
            $ids = $request->user_ids;
            $first_ids   = ltrim($ids, ',');
            $second_ids  = rtrim($first_ids, ',');
            $user_ids    = explode(',', $second_ids);
            $users = User::whereIn('id', $user_ids)->get();
        }

        foreach ($users as $user) {
            #send FCM

            #delete client
            $user->delete();
        }

        #add adminReport
        admin_report('حذف اكتر من عميل');

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #change client status (active - blocked)
    public function change_user_status(Request $request)
    {
        //check data
        $user = User::whereId($request->id)->first();
        if (!isset($user)) return 0;
        //update data
        $user->blocked = $user->blocked == 1 ? 0 : 1;
        $user->save();

        if ($user->blocked) {
            $lang = $user->lang ?? 'ar';
            $body = [
                'msg_ar'  => 'تم حظر حسابك الان من قبل الادارة',
                'msg_en'  => 'your account is blocked now by admin',
            ];
            $data   = [];
            $data['title']      = settings('site_name');
            $data['body']       = $body['msg_' . $lang];
            $data['status']     = 'user_blocked';
            // foreach ($user->Devices as $device) {
            //     send_fcm($device->device_id, $data, $device->device_type);
            // }

            // Device::where('user_id', $user->id)->delete();
        }
        //add report
        $user->blocked ? admin_report(' حظر العميل ' . $user->name) : admin_report('ألغاء حظر العميل ' . $user->name);
        $msg = $user->blocked ? awtTrans('حظر') : awtTrans('مفعل');
        return $msg;
    }

    #send notify
    public function send_notify(Request $request)
    {
        if ($request->type == 'all') $users = User::where('user_type', 'client')->get();
        else $users = User::whereId($request->id)->get();

        foreach ($users as $user) {
            $lang    = $user->lang;
            $message = $request->message;

            #send FCM
        }

        session()->flash('success', awtTrans('تم الإرسال بنجاح'));
        return back();
    }
}
