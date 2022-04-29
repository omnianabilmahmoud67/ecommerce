<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Validator;
use App\Models\Property;
use Illuminate\Http\Request;

class propertyController extends Controller
{
    #index
    public function index()
    {
        $data = Property::get();
        return view('dashboard.propertys', compact('data'));
    }

    #store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar'          => 'required|max:255',
            'title_en'          => 'required|max:255',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #store new property
        $property = Property::create($request->except(['_token']));

        #add adminReport
        admin_report('أضافة السمة ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }

    #update
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar'          => 'required|max:255',
            'title_en'          => 'required|max:255',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #update property
        $property = Property::whereId($request->id)->first();
        $property->update($request->except(['_token']));

        #add adminReport
        admin_report('تعديل السمة ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم التعديل بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم التعديل بنجاح')]);
    }

    #delete one
    public function delete(Request $request)
    {
        #get property
        $property = Property::whereId($request->id)->firstOrFail();
        $title_ar = $property->title_ar;

        #send FCM

        #delete property
        $property->delete();

        #add adminReport
        admin_report('حذف السمة ' . $title_ar);

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #delete more than one or all
    public function delete_all(Request $request)
    {
        $type = $request->type;
        #get propertys
        if ($type == 'all') $propertys = Property::get();
        else {
            $ids = $request->property_ids;
            $first_ids   = ltrim($ids, ',');
            $second_ids  = rtrim($first_ids, ',');
            $property_ids = explode(',', $second_ids);
            $propertys = Property::whereIn('id', $property_ids)->get();
        }

        foreach ($propertys as $property) {
            #send FCM

            #delete property
            $property->delete();
        }

        #add adminReport
        admin_report('حذف اكتر من سمة');

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }
}
