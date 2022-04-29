<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class settingController extends Controller
{
    #index
    public function index()
    {
        return view('dashboard.settings');
    }

    #update
    public function update(Request $request)
    {


        if ($request->hasFile('logo')) {
            $logo = Setting::firstOrCreate(['key' => 'logo']);
            $logo->value = upload_image($request->file('logo'), 'public/images/setting');
            $logo->save();
        }

        foreach ($request->keys as $key => $value) {
            $add = Setting::firstOrCreate(['key' => $key]);
            $add->value = $value;
            $add->save();
        }

        #add adminReport
        admin_report('تحديث الإعدادات');

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }

    #social
    public function social(Request $request)
    {
        foreach ($request->keys as $key => $value) {
            $add = Setting::firstOrCreate(['key' => $key]);
            $add->value = $value;
            $add->save();
        }

        #add adminReport
        admin_report('تحديث بيانات مواقع التواصل');

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }

    #update
    public function location(Request $request)
    {
        foreach ($request->keys as $key => $value) {
            $add = Setting::firstOrCreate(['key' => $key]);
            $add->value = $value;
            $add->save();
        }

        #add adminReport
        admin_report('تحديث الخريطة');

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }

    #seo
    public function seo(Request $request)
    {
        foreach ($request->keys as $key => $value) {
            $add = Setting::firstOrCreate(['key' => $key]);
            $add->value = $value;
            $add->save();
        }

        #add adminReport
        admin_report('تحديث بيانات محرك البحث');

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }
}
