<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use App\Models\Neighborhood;
use App\Models\Order;
use App\Models\Order_provider;
use App\Models\Rate;
use App\Models\Section;
use App\Models\Service_group_property;
use App\Models\Order_item;
use App\Models\Package;
use App\Models\Package_subscripe;
use App\Models\Promo_code;
use App\Models\Service;
use App\Models\Service_like;
use App\Models\Service_group;
use App\Models\Cart;
use App\Models\Property_item;
use App\Models\User_address;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;
use Hash;
use Auth;

use function GuzzleHttp\json_decode;

class orderController extends Controller
{

    #search page
    public function search(Request $request)
    {
        $queries = '';

        if($request->has('title')){
            $title          = $request->title;
            $queries        = 'title=' . $title;
        }

        if($request->has('filter')){
            $filter      = $request->filter;
            $queries     .= '&filter=' . $filter;
        }

        if($request->has('min_price')){
            $min_price      = $request->min_price;
            $queries        .= '&min_price=' . $min_price;
        }

        if($request->has('max_price')){
            $max_price      = $request->max_price;
            $queries        .= '&max_price=' . $max_price;
        }

        if($request->has('property_item_ids')){
            $property_item_ids  = implode(',' , $request->property_item_ids);
            $queries            .= '&property_item_ids=' . $property_item_ids;
        }

        if($request->has('section_id')){
            $section_id     = $request->section_id;
            $queries        .= '&cat=' . $section_id;
        }

        if($request->has('sub_section_id')){
            $sub_section_id = $request->sub_section_id;
            $queries        .= '&sub_cat=' . $sub_section_id;
        }

        return redirect('all-sections/?' . $queries);
    }

    #all_sections page
    public function all_sections()
    {
        #query string
        $queries = [];
        parse_str($_SERVER['QUERY_STRING'], $queries);
        $section_id        = 0;
        $sub_section_id    = 0;
        $min_price         = 0;
        $max_price         = 0;
        $title             = '';
        $filter            = '';
        $property_item_ids = '';
        if (isset($queries['title']) && !empty($queries['title']))         $title          = $queries['title'];
        if (isset($queries['filter']) && !empty($queries['filter']))       $filter         = $queries['filter'];
        if (isset($queries['min_price']) && !empty($queries['min_price'])) $min_price      = $queries['min_price'];
        if (isset($queries['max_price']) && !empty($queries['max_price'])) $max_price      = $queries['max_price'];
        if (isset($queries['cat']) && !empty($queries['cat']))             $section_id     = $queries['cat'];
        if (isset($queries['sub_cat']) && !empty($queries['sub_cat']))     $sub_section_id = $queries['sub_cat'];
        if (isset($queries['property_item_ids']) && !empty($queries['property_item_ids'])) $property_item_ids = $queries['property_item_ids'];

        $first_ids          = ltrim($property_item_ids, ',');
        $second_ids         = rtrim($first_ids, ',');
        $property_item_ids  = empty($second_ids) ? [] : explode(',', $second_ids);
        // dd($title,$filter,$min_price,$max_price,$section_id,$sub_section_id,$property_item_ids);

        $query = Service::query();
        if (!empty($title))
            $query->where('title_ar', 'like', '%' . $title . '%')->orWhere('title_en', 'like', '%' . $title . '%');
        if (!empty($min_price))
            $query->where('price', '>=' , $min_price);
        if (!empty($max_price))
            $query->where('price', '<=' , $max_price);
        if (!empty($section_id))
            $query->where('section_id', $section_id);
        if (!empty($sub_section_id))
            $query->where('sub_section_id', $sub_section_id);
        if (!empty($property_item_ids)) {
            $query->whereHas('group_properties', function($q) use ($property_item_ids){
                return $q->whereIn('property_item_id' , $property_item_ids);
            });
        }
        if(isset($filter) && $filter == 'newer')      $query->orderBy('id' , 'desc');
        if(isset($filter) && $filter == 'cheper')     $query->orderBy('price' , 'asc');
        if(isset($filter) && $filter == 'expensive')  $query->orderBy('price' , 'desc');
        if(isset($filter) && $filter == 'salled')     $query->orderBy('salled_count' , 'desc');
        $data = $query->get();

        return view('site.all_sections', ['data' => $data,'queries' => $queries]);
    }

    public function show_service($service_id){
        $service = Service::whereId($service_id)->firstOrFail();
        $data = [
            'service'  => $service,
            'services' => Service::where('id' , '!=' , $service->id)->where(function($q) use ($service){
                return $q->where(function($qq) use ($service){
                    return $qq->where('title_ar' , 'like' , '%' . $service->title_ar . '%')->orWhere('title_en' , 'like' , '%' . $service->title_en . '%');
                })->orWhere('sub_section_id' , $service->sub_section_id);
            })->get(),
        ];
        return view('site.show_service' , $data);
    }

    public function show_likes(Request $request){
        return view('site.show_likes');
    }

    public function add_to_like(Request $request){
        $validator = Validator::make($request->all(),[
            'service_id'    => 'required|exists:services,id',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #store or delete like
        $request->request->add(['user_id' => Auth::id()]);
        $service = Service_like::where('service_id' , $request->service_id)->where('user_id' , Auth::id())->first();
        if(isset($service)) $service->delete();
        else Service_like::create($request->except(['_token']));

        return response()->json(['value' => 1, 'msg' => awtTrans('تم بنجاح')]);
    }

    public function show_cart(Request $request){
        return view('site.show_cart');
    }

    public function add_to_cart(Request $request){
        $validator = Validator::make($request->all(),[
            'service_id'        => 'required|exists:services,id',
            'size_id'           => 'required|exists:property_items,id',
            'color_id'          => 'required|exists:property_items,id',
            'count'             => 'required',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #data
        $service = Service::whereId($request->service_id)->first();
        $group   = Service_group::whereServiceId($request->service_id)->whereHas('group_properties' , function($q) use ($request){
            return $q->where('property_id' , '1')->where('property_item_id' , $request->size_id);
        })->whereHas('group_properties' , function($q) use ($request){
            return $q->where('property_id' , '2')->where('property_item_id' , $request->color_id);
        })->first();

        $size    = Property_item::whereId($request->size_id)->first();
        $color   = Property_item::whereId($request->color_id)->first();
        #check amount
        if (isset($group) && (int) $group->amount >= $request->count) {
            #check cart
            $add = Cart::where('user_id' , Auth::id())->where('group_id' , $group->id)->first();
            if (isset($add)) {
                #update cart
                $next_count  = (int) $add->count + $request->count;
                #check amount
                if ($next_count > $group->amount) return response()->json(['value' => 0, 'msg' => awtTrans('لا يوجد الكمية المطلوبة لهذا المنتج')]);
                $add->total     = $group->price * $next_count;
                $add->count     = $next_count;
                $add->save();
            } else {
                #add to cart
                $add = new Cart;
                $add->user_id       = Auth::id();
                $add->service_id    = $request->service_id;
                $add->group_id      = $group->id;
                $add->size          = $size->title_en;
                $add->color         = $color->title_en;
                $add->hex           = $color->color;
                $add->delivery      = get_delivery($service);
                $add->total         = $group->price * $request->count;
                $add->count         = $request->count;
                $add->save();
            }

            Session::flash('success', awtTrans('تم الاضافة الى السلة بنجاح'));
            return response()->json(['value' => 1, 'msg' => awtTrans('تم الاضافة الى السلة بنجاح')]);
        }

        return response()->json(['value' => 0, 'msg' => awtTrans('لا يوجد الكمية المطلوبة لهذا المنتج')]);
    }

    public function update_to_cart(Request $request){
        $validator = Validator::make($request->all(),[
            'cart_id' => 'required|exists:carts,id',
            'count'   => 'required',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        /** update cart **/
        $add     = Cart::whereId($request->cart_id)->first();
        $group   = Service_group::whereId($add->group_id)->first();
        if ($group->amount >= $request->count) {
            if ($request->count > 0) {
                $add->count     = $request->count;
                $add->total     = $group->price * $request->count;
                $add->save();
            } else {
                $add->delete();
            }

            return response()->json([
            'value'     => 1,
            'maxCart'   => view('site.cart')->render(),
            'miniCart'  => view('site.cart_mini')->render()
        ]);
        }

        return response()->json(['value' => 0, 'msg' => awtTrans('لا يوجد كمية')]);
    }

    public function check_promo(Request $request){
        $validator = Validator::make($request->all(),[
            'code' => 'required',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        # get discount
        $promo = check_promo_code(Auth::id(), $request->code);
        #invalid promo code
        if (!isset($promo)) return response()->json(['value' => 0, 'msg' => trans('api.invaildPromo')]);
        if (isset($promo) && $promo->discount == 0) return response()->json(['value' => 0, 'msg' => trans('api.invaildPromo')]);

        return response()->json([
            'value'     => 1,
            'maxCart'   => view('site.cart', ['promo' => $promo , 'discount' => (float) $promo->discount])->render(),
            'miniCart'  => view('site.cart_mini')->render()
        ]);

    }

    public function update_all_cart(Request $request){
        $validator = Validator::make($request->all(),[
            'email'      => 'required|email',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        Session::flash('success', awtTrans('تم ارسال رابط الى بريدك الإلكتروني'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم ارسال رابط الى بريدك الإلكتروني')]);
    }

    public function checkout(Request $request)
    {
        return view('site.checkout' , compact('request'));
    }

    public function fill_address_data(Request $request)
    {
        $address = User_address::whereId($request->address_id)->firstOrFail();
        return view('site.address_data' , compact('address'));
    }

    public function show_profile(){
        #query string
        $queries = [];
        parse_str($_SERVER['QUERY_STRING'], $queries);
        $tab = 'order';
        if (isset($queries['tab']) && !empty($queries['tab'])) $tab  = $queries['tab'];

        $data = [
            'tab'       => $tab,
            'addresses' => Auth::user()->addresses,
            'orders'    => Order::where('user_id', Auth::id())->where('user_seen' , '1')->orderBy('id', 'desc')->get(),
        ];
        return view('site.show_profile' , $data);
    }

    public function update_profile(Request $request){
        $validator = Validator::make($request->all(),[
            'first_name' => 'required|max:255',
            'last_name'  => 'nullable|max:255',
            'email'      => 'required|max:255|email|unique:users,email,' . Auth::id(),
            'phone'      => 'required|min:9|max:13',
            'photo'      => 'nullable|image',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #avatar
        if ($request->hasFile('photo')) $request->request->add(['avatar' => upload_image($request->file('photo'), 'public/images/users')]);
        #user
        $user = User::whereId(Auth::id())->first();
        $user->update($request->except(['_token' , 'photo']));

        Session::flash('success', awtTrans('تم بنجاح'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم بنجاح')]);
    }

    public function update_password(Request $request){
        $validator = Validator::make($request->all(),[
            'old_password'      => 'required',
            'password'          => 'required|confirmed|min:6|max:255',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        if (Hash::check(request('old_password'), Auth::user()->password)) {
            $user = User::whereId(Auth::id())->first();
            $user->update($request->except(['_token' , 'old_password' , 'password_confirmation']));

            Session::flash('success', awtTrans('تم بنجاح'));
            return response()->json(['value' => 1, 'msg' => awtTrans('تم بنجاح')]);
        }

        return response()->json(['value' => 0, 'msg' => awtTrans('كلمة المرور القديمة غير صحيحة')]);
    }

    public function storeAddress(Request $request){
        $validator = Validator::make($request->all(),[
            'first_name'    => 'required|max:255',
            'last_name'     => 'nullable|max:255',
            'country_id'    => 'required|max:255|exists:countries,id',
            'city'          => 'required|max:255',
            'first_street'  => 'required|max:255',
            'second_street' => 'required|max:255',
            'phone'         => 'required|min:7|max:13',
            'email'         => 'required|max:255|email',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #store address
        $request->request->add(['user_id' => Auth::id()]);
        User_address::create($request->except(['_token' , 'id']));

        Session::flash('success', awtTrans('تم الحفظ'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ')]);
    }

    public function updateAddress(Request $request){
        $validator = Validator::make($request->all(),[
            'id'            => 'required|exists:user_addresses,id',
            'first_name'    => 'required|max:255',
            'last_name'     => 'nullable|max:255',
            'country_id'    => 'required|max:255|exists:countries,id',
            'city'          => 'required|max:255',
            'first_street'  => 'required|max:255',
            'second_street' => 'required|max:255',
            'phone'         => 'required|min:7|max:13',
            'email'         => 'required|max:255|email',
        ]);

        #error response
        if ($validator->fails()) return response()->json(['value' => 0, 'msg' => $validator->errors()->first()]);

        #store address
        $address = User_address::whereId($request->id)->first();
        $address->update($request->except(['_token' , 'id']));

        Session::flash('success', awtTrans('تم الحفظ'));
        return response()->json(['value' => 1, 'msg' => awtTrans('تم الحفظ')]);
    }

    public function deleteAddress($id){
        User_address::whereId($id)->delete();

        Session::flash('success', awtTrans('تم بنجاح'));
        return back();
    }

    #order post
    public function store_order(Request $request)
    {
        $this->validate($request, [
            'address_id'         => 'required|exists:user_addresses,id',
            'promo_id'           => 'nullable|exists:promo_codes,id',
            'sub_total'          => 'required',
            'discount'           => 'required',
            'delivery'           => 'required',
            'total'              => 'required',
            'payment_method'     => 'required|in:cash,transfer,online',
        ]);

        #json decode
        $items = Cart::where('user_id' , Auth::id())->get();
        #faild response
        if (count($items) == 0) {
            session()->flash('success', awtTrans('لا يوجد منتجات بالسلة'));
            return redirect()->route('show_cart');
        }

        #store order
        $user       = User::whereId(Auth::id())->first();
        $promo      = Promo_code::whereId($request->promo_id)->first();
        $address    = User_address::whereId($request->address_id)->first();
        $request->request->add([
            'status'         => 'new',
            'user_id'        => Auth::id(),
            'promo_discount' => isset($promo) ? $promo->discount : 0,
            'first_name'     => $address->first_name,
            'last_name'      => $address->last_name,
            'country_id'     => $address->country_id,
            'city'           => $address->city,
            'first_street'   => $address->first_street,
            'second_street'  => $address->second_street,
            'phone'          => convert_phone_to_international_format($address->phone, $address->phone_code),
            'email'          => $address->email,
            'notes'          => $address->notes,
        ]);
        $order = Order::create($request->except(['_token']));

        #store order items
        foreach ($items as $item) {
            $service = Service::whereId($item->service_id)->first();
            $size    = Service_group_property::whereGroupId($item->group_id)->where('property_id' , '1')->first();
            $color   = Service_group_property::whereGroupId($item->group_id)->where('property_id' , '2')->first();
            Order_item::create([
                'order_id'         => $order->id,
                'service_id'       => $item->service_id,
                'group_id'         => $item->group_id,
                'count'            => $item->count,
                'total'            => $item->total,
                'delivery'         => $item->delivery,
                'size'             => isset($size)  ? $size->item->title_en : '',
                'color'            => isset($color) ? $color->item->title_en : '',
                'hex'              => isset($color) ? $color->item->color : '#000000',
                'service_title_ar' => $service->title_ar,
                'service_title_en' => $service->title_en,
            ]);
        }

        #delete cart
        // Cart::where('user_id' , Auth::id())->delete();

        #success response
        if($request->payment_method == 'online') {
            Session::put('order_id' , $order->id);
            return redirect('my_fatoorah');
        }

        session()->flash('success', awtTrans('تم بنجاح'));
        return redirect('show-profile?tab=order');
    }

    #show order bill page
    public function order_bill($order_id)
    {
        $order = Order::whereId($order_id)->firstOrFail();
        $days  = json_decode($order->days_id);
        return view('site.order_bill', ['order' => $order, 'days' => $days]);
    }

    #show order details page
    public function order_details($order_id)
    {
        $order = Order::whereId($order_id)->firstOrFail();
        $days  = json_decode($order->days_id);
        return view('site.order_details', ['order' => $order, 'days' => $days]);
    }

    #show current_orders page
    public function current_orders()
    {
        $orders = Order::where('user_id', Auth::id())->whereIn('status', ['pre-order', 'new', 'agree'])->get();
        return view('site.current_orders', ['orders' => $orders]);
    }

    #show finish_orders page
    public function finish_orders()
    {
        $orders = Order::where('user_id', Auth::id())->where('status', 'finish')->get();
        return view('site.finish_orders', ['orders' => $orders]);
    }

    #show new_orders page
    public function coach_new_orders()
    {
        $orders = Order_provider::where('provider_id', Auth::id())->where('status', 'new')->get();
        return view('site.coach_new_orders', ['orders' => $orders]);
    }

    #show current_orders page
    public function coach_current_orders()
    {
        $orders = Order::where('provider_id', Auth::id())->where('status', 'agree')->get();
        return view('site.coach_current_orders', ['orders' => $orders]);
    }

    #show finish_orders page
    public function coach_finish_orders()
    {
        $orders = Order::where('provider_id', Auth::id())->where('status', 'finish')->get();
        return view('site.coach_finish_orders', ['orders' => $orders]);
    }

    #show new_orders page
    public function new_coach_orders()
    {
        $orders = Coach_request::where('coach_id', Auth::id())->where('status', 'new')->get();
        return view('site.new_coach_orders', ['orders' => $orders]);
    }

    #show current_orders page
    public function current_coach_orders()
    {
        $orders = Coach_request::where('coach_id', Auth::id())->where('status', 'agree')->get();
        return view('site.current_coach_orders', ['orders' => $orders]);
    }

    #show change_order_status page
    public function change_order_status($order_id, $status)
    {
        $order = Order::whereId($order_id)->firstOrFail();
        if ($status == 'agree') {
            if ($order->status == 'new') {
                Order_provider::where('order_id', $order_id)->update(['status' => 'agree']);
                $order->update([
                    'status'         => $status,
                    'provider_id'    => Auth::id(),
                    'provider_name'  => Auth::user()->name,
                    'provider_phone' => Auth::user()->phone,
                    'provider_email' => Auth::user()->email,
                ]);
            } else {
                session()->flash('danger', awtTrans('تم الموافقة على الطلب من مدربة اخرى بالفعل'));
                return back();
            }
        } elseif ($status == 'cancel') {
            Order_provider::where('order_id', $order_id)->where('provider_id', Auth::id())->delete();
        } else {
            Order_provider::where('order_id', $order_id)->delete();
            $order->update(['status' => $status]);
        }

        #success response
        session()->flash('success', awtTrans('تم بنجاح'));
        return back();
    }

    public function change_request_status($order_id, $status)
    {
        $order = Coach_request::whereId($order_id)->firstOrFail();
        if ($status == 'agree') {
            $order->update(['status' => 'agree']);
        } elseif ($status == 'cancel') {
            $order->delete();
        }

        #success response
        session()->flash('success', awtTrans('تم بنجاح'));
        return back();
    }

    #rate order page
    public function rate_order(Request $request)
    {
        // $order = Order::whereId($request->order_id)->firstOrFail();
        $rate = Rate::where('from_id' , Auth::id())->where('to_id' , $request->coach_id)->first();
        if(isset($rate)) $rate->update(['rate' => $request->rate]);
        else{
            Rate::create([
                'from_id'  => Auth::id(),
                'to_id'    => $request->coach_id,
                'order_id' => null,
                'rate'     => $request->rate,
            ]);
        }

        #success response
        session()->flash('success', awtTrans('تم بنجاح'));
        return back();
    }
}
