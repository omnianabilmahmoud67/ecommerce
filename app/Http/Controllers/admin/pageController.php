<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Validator;
use App\Models\Page;
use Illuminate\Http\Request;

class pageController extends Controller
{
    #index
    public function index()
    {
        $data = Page::orderBy('title_ar', 'asc')->get();
        return view('dashboard.pages', compact('data'));
    }

    #store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar'      => 'required|max:255',
            'title_en'      => 'nullable|max:255',
            'desc_ar'       => 'required',
            'desc_en'       => 'nullable',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #store new page
        Page::create($request->except(['_token']));

        #add adminReport
        admin_report('أضافة صفحة ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم الحفظ بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ بنجاح')]);
    }

    #update
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar'      => 'required|max:255',
            'title_en'      => 'nullable|max:255',
            'desc_ar'       => 'required',
            'desc_en'       => 'nullable',
        ]);

        #error response
        if ($validator->fails())
            return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #update page
        Page::whereId($request->id)->update($request->except(['_token']));

        #add adminReport
        admin_report('تعديل صفحة ' . $request->title_ar);

        #success response
        session()->flash('success', awtTrans('تم التعديل بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم التعديل بنجاح')]);
    }

    #delete one
    public function delete(Request $request)
    {
        #get page
        $page = Page::whereId($request->id)->firstOrFail();
        $title_ar = $page->title_ar;

        #send FCM

        #delete page
        $page->delete();

        #add adminReport
        admin_report('حذف الصفحة ' . $title_ar);

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }

    #delete more than one or all
    public function delete_all(Request $request)
    {
        $type = $request->type;
        #get pages
        if ($type == 'all') $pages = Page::get();
        else {
            $ids = $request->page_ids;
            $first_ids   = ltrim($ids, ',');
            $second_ids  = rtrim($first_ids, ',');
            $page_ids    = explode(',', $second_ids);
            $pages = Page::whereIn('id', $page_ids)->get();
        }

        foreach ($pages as $page) {
            #send FCM

            #delete page
            $page->delete();
        }

        #add adminReport
        admin_report('حذف اكتر من صفحة');

        #success response
        return back()->with('success', awtTrans('تم الحذف'));
    }
}
