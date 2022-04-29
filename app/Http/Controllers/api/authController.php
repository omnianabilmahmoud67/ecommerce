<?php

namespace App\Http\Controllers\api;
#Basic

use App\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\userResource;
#config->app
use Validator;
use Auth;
use Hash;
use Mail;
use App\Mail\ActiveCode;
#Models
use App\Models\Role;
use App\User;

class authController extends Controller
{
    #sign up
    public function register(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name'  => 'nullable|max:255',
            'email'      => 'required|max:255|email|unique:users,email',
            'phone'      => 'required|min:9|max:13',
            'phone_code' => 'required',
            'password'   => 'required|min:6|max:255',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #send code to client's email
        $code = active_code();
        Mail::to($request->email)->send(new ActiveCode('Code is : ' . $code));
        #store new client
        $request->request->add(['active' => 0, 'blocked' => 0, 'code' => $code, 'avatar' => '/public/user.png', 'user_type' => 'client', 'role_id' => Role::first()->id]);
        $user = User::create($request->except(['_token', 'lang']));

        #success response
        return api_response(1, trans('api.codeSentToPhoneSuccessfully'), new userResource($user), ['status' => user_status($user)]);
    }

    #sign in
    public function login(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'email'       => 'required|email',
            'password'    => 'required|min:6',
            'device_id'   => 'required|max:255',
            'device_type' => 'nullable|in:android,ios',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #send code to client's email
        if (Auth::attempt(['email' => $request->email, 'password' => request('password')])) {
            $user = Auth::user();
            #update user's device
            update_device($user, $request->device_id, $request->device_type);
            #success response
            return api_response('1', trans('api.loggedInSuccessfully'), new userResource($user), ['status' => user_status($user)]);
        }

        #faild response
        return api_response(0, trans('api.wrongLogin'));
    }

    #logout
    public function logout(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'device_id'   => 'required',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #remove user's device id
        Device::where('device_id', $request->device_id)->delete();

        #success response
        return api_response('1', trans('api.loggedOutSuccessfully'));
    }

    #active account
    public function activeAccount(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:users,id',
            'code'      => 'required',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #get user
        $user = User::whereId($request->user_id)->first();
        #check code
        if ($user->code == $request->code) {
            #update client data
            $user->code   = rand(1111, 9999);
            $user->active = '1';
            $user->save();

            #success response
            return api_response(1, trans('api.activeSuccess'), new userResource($user), ['status' => user_status($user)]);
        }

        #faild response
        return api_response(0, trans('api.wrongCode'));
    }

    #resend code
    public function resendCode(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #get client
        $user = User::whereId($request->user_id)->first();

        #send code to client's email
        $code = active_code();
        Mail::to($user->email)->send(new ActiveCode('Code is : ' . $code));

        #update client data
        $user->code  = $code;
        $user->save();

        #success response
        return api_response(1, trans('api.send'));
    }

    #check email
    public function checkEmail(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'email'   => 'required',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #get client
        $user = User::whereEmail($request->email)->first();
        #check client exist
        if (!isset($user)) return api_response(0, trans('api.wronPEmail'));

        #send code to client's email
        $code = active_code();
        Mail::to($user->email)->send(new ActiveCode('Code is : ' . $code));

        #update client data
        $user->code  = $code;
        $user->save();

        #success response
        return api_response(1, trans('api.codeSentToPhoneSuccessfully'), new userResource($user), ['status' => user_status($user)]);
    }

    #check code
    public function checkCode(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:users,id',
            'code'        => 'required',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #get client
        $user = User::whereId($request->user_id)->first();

        #success response
        if ($user->code == $request->code) return api_response(1, trans('api.rightCode'));

        #faild response
        return api_response(0, trans('api.wrongCode'));
    }

    #reset password
    public function resetPassword(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:users,id',
            'password'    => 'required|min:6',
            'device_id'   => 'nullable|max:255',
            'device_type' => 'nullable|in:android,ios',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #get client
        $user = User::whereId($request->user_id)->first();
        #update client
        $user->code      = rand(1111, 9999);
        $user->password  = bcrypt($request->password);
        $user->save();

        #update user's device
        if (!is_null($request->device_id)) update_device($user, $request->device_id, $request->device_type);

        #success response
        return api_response(1, trans('api.passwordUpdated'), new userResource($user), ['status' => user_status($user)]);
    }

    #update password
    public function updatePassword(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,id',
            'old_password' => 'required|min:6',
            'password'     => 'required|min:6',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #get client
        $user = User::whereId($request->user_id)->first();
        #check password
        if (Hash::check(request('old_password'), $user->password)) {
            #update client password
            $user->password  = bcrypt($request->password);
            $user->save();

            #success response
            return api_response(1, trans('api.passwordUpdated'), new userResource($user), ['status' => user_status($user)]);
        }

        #faild response
        return api_response(0, trans('api.wrongPassword'));
    }

    #update user
    public function updateUser(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'    => 'required|exists:users,id',
            'first_name' => 'nullable|max:255',
            'last_name'  => 'nullable|max:255',
            'email'      => 'nullable|max:255|email|unique:users,email,' . $request->user_id,
            'phone'      => 'nullable|min:9|max:13',
            'phone_code' => 'nullable',
            'photo'      => 'nullable|image',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #avatar
        if ($request->hasFile('photo')) $request->request->add(['avatar' => upload_image($request->file('photo'), 'public/images/users')]);
        #update client
        $user = User::whereId($request->user_id)->first();
        $user->update($request->except(['user_id', 'lang', 'photo']));

        #success response
        return api_response(1, trans('api.save'), new userResource($user), ['status' => user_status($user)]);
    }

    #show user
    public function showUser(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #get client
        $user = User::whereId($request->user_id)->first();

        #success response
        return api_response(1, trans('api.send'), new userResource($user), ['status' => user_status($user)]);
    }
}
