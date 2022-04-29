<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Validator;
use App\Models\Property_item;
use Illuminate\Http\Request;

class property_itemController extends Controller
{
    #index
    public function index($id)
    {
        $data = Property_item::wherePropertyId($id)->get();
        return view('dashboard.property_items', compact('data', 'id'));
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

        #store new property_item
        $property_item = Property_item::create($request->except(['_token']));

        #add adminReport
        admin_report('أضافة الخاصية ' . $request->title_ar);

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

        #update property_item
        $property_item = Property_item::whereId($request->id)->first();
        $property_item->update($request->except(['_token']));

        #add adminReport
        admin_report('تعديل الخاصية ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم التعديل بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم التعديل بنجاح')]);
    }

    #delete one
    public function delete(Request $request)
    {
        #get property_item
        $property_item = Property_item::whereId($request->id)->firstOrFail();
        $title_ar = $property_item->title_ar;

        #send FCM

        #delete property_item
        $property_item->delete();

        #add adminReport
        admin_report('حذف الخاصية ' . $title_ar);

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #delete more than one or all
    public function delete_all(Request $request)
    {
        $type = $request->type;
        #get property_items
        if ($type == 'all') $property_items = Property_item::get();
        else {
            $ids = $request->property_item_ids;
            $first_ids   = ltrim($ids, ',');
            $second_ids  = rtrim($first_ids, ',');
            $property_item_ids = explode(',', $second_ids);
            $property_items = Property_item::whereIn('id', $property_item_ids)->get();
        }

        foreach ($property_items as $property_item) {
            #send FCM

            #delete property_item
            $property_item->delete();
        }

        #add adminReport
        admin_report('حذف اكتر من خاصية');

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }
}
