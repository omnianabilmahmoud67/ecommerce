<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Validator;
use App\Models\Sub_section;
use Illuminate\Http\Request;

class sub_sectionController extends Controller
{
    #index
    public function index($id)
    {
        $data = Sub_section::whereSectionId($id)->get();
        return view('dashboard.sub_sections', compact('data', 'id'));
    }

    #store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar'          => 'required|max:255',
            'title_en'          => 'nullable|max:255',
            'short_desc_en'     => 'nullable',
            'short_desc_en'     => 'nullable',
            'desc_en'           => 'nullable',
            'desc_en'           => 'nullable',
            'image'             => 'nullable|image',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #image
        if ($request->hasFile('photo')) $request->request->add(['image' => upload_image($request->file('photo'), 'public/images/sections')]);
        #store new sub_section
        $sub_section = Sub_section::create($request->except(['_token', 'photo']));

        #add adminReport
        admin_report('أضافة القسم الفرعي ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }

    #update
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar'          => 'required|max:255',
            'title_en'          => 'nullable|max:255',
            'short_desc_en'     => 'nullable',
            'short_desc_en'     => 'nullable',
            'desc_en'           => 'nullable',
            'desc_en'           => 'nullable',
            'image'             => 'nullable|image',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #image
        if ($request->hasFile('photo')) $request->request->add(['image' => upload_image($request->file('photo'), 'public/images/sections')]);
        #update sub_section
        $sub_section = Sub_section::whereId($request->id)->first();
        $sub_section->update($request->except(['_token', 'photo']));

        #add adminReport
        admin_report('تعديل القسم الفرعي ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم التعديل بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم التعديل بنجاح')]);
    }

    #delete one
    public function delete(Request $request)
    {
        #get sub_section
        $sub_section = Sub_section::whereId($request->id)->firstOrFail();
        $title_ar = $sub_section->title_ar;

        #send FCM

        #delete sub_section
        $sub_section->delete();

        #add adminReport
        admin_report('حذف القسم الفرعي ' . $title_ar);

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #delete more than one or all
    public function delete_all(Request $request)
    {
        $type = $request->type;
        #get sub_sections
        if ($type == 'all') $sub_sections = Sub_section::get();
        else {
            $ids = $request->sub_section_ids;
            $first_ids   = ltrim($ids, ',');
            $second_ids  = rtrim($first_ids, ',');
            $sub_section_ids = explode(',', $second_ids);
            $sub_sections = Sub_section::whereIn('id', $sub_section_ids)->get();
        }

        foreach ($sub_sections as $sub_section) {
            #send FCM

            #delete sub_section
            $sub_section->delete();
        }

        #add adminReport
        admin_report('حذف اكتر من قسم فرعي');

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }
}
