<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Validator;
use App\User;
use Illuminate\Http\Request;

class providerController extends Controller
{
    #index
    public function index()
    {
        $data = get_users_by('provider', 'asc', 0);
        return view('dashboard.providers', compact('data'));
    }

    #store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar'     => 'required|image',
            'name'       => 'required|max:255',
            'email'      => 'required|max:255|email|unique:users,email',
            'phone'      => 'required|min:9|max:13|unique:users,phone',
            'address'    => 'required|max:255',
            'password'   => 'required|min:6|max:255',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #avatar
        if ($request->hasFile('photo')) $avatar = upload_image($request->file('photo'), 'public/images/users');
        else $avatar = '/public/user.png';
        #store new provider
        $request->request->add(['active' => 1, 'blocked' => 0, 'avatar' => $avatar, 'user_type' => 'provider', 'role_id' => Role::first()->id]);
        $user = User::create($request->except(['_token', 'photo']));

        #add adminReport
        admin_report('أضافة المندوب ' . $request->name);

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }

    #update
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar'     => 'nullable|image',
            'name'       => 'required|max:255',
            'phone'      => 'required|min:9|max:13|unique:users,phone,' . $request->id,
            'email'      => 'required|max:255|email|unique:users,email,' . $request->id,
            // 'password'   => 'nullable|min:6|max:255',
            'address'    => 'required|max:255',

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
        admin_report('تعديل المندوب ' . $request->name);

        #success response
        session()->flash('success', awtTrans('تم التعديل بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم التعديل بنجاح')]);
    }

    #delete one
    public function delete(Request $request)
    {
        #get provider
        $user = User::whereId($request->id)->firstOrFail();
        $name = $user->name;

        #send FCM

        #delete provider
        $user->delete();

        #add adminReport
        admin_report('حذف المندوب ' . $name);

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #delete more than one or all
    public function delete_all(Request $request)
    {
        $type = $request->type;
        #get providers
        if ($type == 'all') $users = User::where('user_type', 'provider')->get();
        else {
            $ids = $request->user_ids;
            $first_ids   = ltrim($ids, ',');
            $second_ids  = rtrim($first_ids, ',');
            $user_ids    = explode(',', $second_ids);
            $users = User::whereIn('id', $user_ids)->get();
        }

        foreach ($users as $user) {
            #send FCM

            #delete provider
            $user->delete();
        }

        #add adminReport
        admin_report('حذف اكتر من مندوب');

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #change provider status (active - blocked)
    public function change_provider_status(Request $request)
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
        $user->blocked ? admin_report(' حظر المندوب ' . $user->name) : admin_report('ألغاء حظر المندوب ' . $user->name);
        $msg = $user->blocked ? awtTrans('حظر') : awtTrans('مفعل');
        return $msg;
    }

    #send notify
    public function send_notify(Request $request)
    {
        if ($request->type == 'all') $users = User::where('user_type', 'provider')->get();
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
