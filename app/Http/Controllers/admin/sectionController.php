<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Validator;
use App\Models\Section;
use Illuminate\Http\Request;

class sectionController extends Controller
{
    #index
    public function index()
    {
        $data = Section::get();
        return view('dashboard.sections', compact('data'));
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
        #store new section
        $section = Section::create($request->except(['_token', 'photo']));

        #add adminReport
        admin_report('أضافة القسم ' . $request->title_ar);

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
        #update section
        $section = Section::whereId($request->id)->first();
        $section->update($request->except(['_token', 'photo']));

        #add adminReport
        admin_report('تعديل القسم ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم التعديل بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم التعديل بنجاح')]);
    }

    #delete one
    public function delete(Request $request)
    {
        #get section
        $section = Section::whereId($request->id)->firstOrFail();
        $title_ar = $section->title_ar;

        #send FCM

        #delete section
        $section->delete();

        #add adminReport
        admin_report('حذف القسم ' . $title_ar);

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #delete more than one or all
    public function delete_all(Request $request)
    {
        $type = $request->type;
        #get sections
        if ($type == 'all') $sections = Section::get();
        else {
            $ids = $request->section_ids;
            $first_ids   = ltrim($ids, ',');
            $second_ids  = rtrim($first_ids, ',');
            $section_ids = explode(',', $second_ids);
            $sections = Section::whereIn('id', $section_ids)->get();
        }

        foreach ($sections as $section) {
            #send FCM

            #delete section
            $section->delete();
        }

        #add adminReport
        admin_report('حذف اكتر من قسم');

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }
}
