<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Validator;
use App\Models\Sub_section;
use App\Models\Service;
use App\Models\Service_image;
use App\Models\Service_group;
use App\Models\Service_group_property;
use App\Models\Property;
use App\Models\Property_item;
use Illuminate\Http\Request;

class serviceController extends Controller
{
    #index
    public function index()
    {
        $data = Service::get();
        return view('dashboard.services.index', compact('data'));
    }

    #new
    public function new()
    {
        $data = [
            'width' => 100 / (Property::count() + 1),
        ];
        return view('dashboard.services.new', $data);
    }

    #store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photos'                 => 'required',
            'title_ar'               => 'required|max:255',
            'title_en'               => 'required|max:255',
            'price'                  => 'required',
            'delivery'               => 'required',
            'desc_ar'                => 'required',
            'desc_en'                => 'required',
            'short_desc_ar'          => 'required',
            'short_desc_en'          => 'required',
            //'amount'                 => 'required',
        ],[
            'title_ar.required'      => 'اسم المنتج بالعربية مطلوب',
            'title_en.required'      => 'اسم المنتج بالانجليزية مطلوب',
            'short_desc_ar.required' => 'معلومات اضافية المنتج بالعربية مطلوب',
            'short_desc_en.required' => 'معلومات اضافية المنتج بالانجليزية مطلوب',
            'desc_ar.required'       => 'تفاصيل المنتج بالعربية مطلوب',
            'desc_en.required'       => 'تفاصيل المنتج بالانجليزية مطلوب',
            'price.required'         => 'سعر المنتج مطلوب',
            'delivery.required'      => 'سعر التوصيل مطلوب',
            'photos.required'        => 'يجب على الاقل اختيار صورة واحدة للمنتج',
            //'amount.required'        => 'يجب اضافة سمة على الاقل للمنتج',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #sub_section
        $sub_section = Sub_section::whereId($request->sub_section_id)->first();
        $request->request->add(['section_id' => $sub_section->section_id]);

        #store new service
        $service = Service::create($request->except(['_token','photos','amount','size' , 'color']));

        #Images
        if($request->has('photos')){
            foreach($request->file('photos') as $photo){
                Service_image::create(['service_id' => $service->id ,'image' => upload_image($photo, 'public/images/services')]);
            }
        }

        #properties
        $properties = Property::get();
        $amounts    = $request->amount;
        foreach ($amounts as $key => $amount) {
            if(is_null($amount)) continue;
            $group = Service_group::create(['service_id' => $service->id ,'amount' => $amount ,'price' => $request->price]);
            foreach($properties as $property){
                $title               = $property->title_en;
                $property_id         = $property->id;
                $property_item_id    = $request->$title[$key];
                Service_group_property::create(['service_id' => $service->id ,'group_id' => $group->id ,'property_id' => $property_id ,'property_item_id' => $property_item_id ]);
            }
        }

        #add adminReport
        admin_report('أضافة المنتج ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }

    #edit
    public function edit($id)
    {
        $data = [
            'data'  => Service::whereId($id)->firstOrFail(),
            'width' => 100 / (Property::count() + 1),
        ];
        return view('dashboard.services.edit', $data);
    }

    #update
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar'          => 'required|max:255',
            'title_en'          => 'required|max:255',
            'short_desc_ar'     => 'required',
            'short_desc_en'     => 'required',
            'desc_ar'           => 'required',
            'desc_en'           => 'required',
            'price'             => 'required',
            'delivery'          => 'required',
            //'amount'            => 'required',
        ],[
            'title_ar.required'          => 'اسم المنتج بالعربية مطلوب',
            'title_en.required'          => 'اسم المنتج بالانجليزية مطلوب',
            'short_desc_ar.required'     => 'معلومات اضافية المنتج بالعربية مطلوب',
            'short_desc_en.required'     => 'معلومات اضافية المنتج بالانجليزية مطلوب',
            'desc_ar.required'           => 'تفاصيل المنتج بالعربية مطلوب',
            'desc_en.required'           => 'تفاصيل المنتج بالانجليزية مطلوب',
            'price.required'             => 'سعر المنتج مطلوب',
            'delivery.required'          => 'سعر التوصيل مطلوب',
            //'amount.required'            => 'يجب اضافة سمة على الاقل للمنتج',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #sub_section
        $sub_section = Sub_section::whereId($request->sub_section_id)->first();
        $request->request->add(['section_id' => $sub_section->section_id]);

        #update service
        $service = Service::whereId($request->id)->first();
        $service->update($request->except(['_token','photos','amount','size' , 'color']));

        #Images
        if($request->has('photos')){
            foreach($request->file('photos') as $photo){
                Service_image::create(['service_id' => $service->id ,'image' => upload_image($photo, 'public/images/services')]);
            }
        }

        #properties
        Service_group::where('service_id' , $request->id)->delete();
        $properties = Property::get();
        $amounts    = $request->amount;
        foreach ($amounts as $key => $amount) {
            if(is_null($amount)) continue;
            $group = Service_group::create(['service_id' => $service->id ,'amount' => $amount ,'price' => $request->price]);
            foreach($properties as $property){
                $title               = $property->title_en;
                $property_id         = $property->id;
                $property_item_id    = $request->$title[$key];
                Service_group_property::create(['service_id' => $service->id ,'group_id' => $group->id ,'property_id' => $property_id ,'property_item_id' => $property_item_id ]);
            }
        }

        #add adminReport
        admin_report('تعديل المنتج ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم التعديل بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم التعديل بنجاح')]);
    }

    #delete one
    public function delete(Request $request)
    {
        #get service
        $service = Service::whereId($request->id)->firstOrFail();
        $title_ar = $service->title_ar;

        #send FCM

        #delete service
        $service->delete();

        #add adminReport
        admin_report('حذف المنتج ' . $title_ar);

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #delete more than one or all
    public function delete_all(Request $request)
    {
        $type = $request->type;
        #get services
        if ($type == 'all') $services = Service::get();
        else {
            $ids = $request->service_ids;
            $first_ids   = ltrim($ids, ',');
            $second_ids  = rtrim($first_ids, ',');
            $service_ids = explode(',', $second_ids);
            $services = Service::whereIn('id', $service_ids)->get();
        }

        foreach ($services as $service) {
            #send FCM

            #delete service
            $service->delete();
        }

        #add adminReport
        admin_report('حذف اكتر من منتج');

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }
}
