<?php

namespace App\Http\Controllers\api;

#Basic
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
#resources
use App\Http\Resources\bankResource;
use App\Http\Resources\notificationResource;
use App\Http\Resources\pageResource;
use App\Http\Resources\sectionResource;
use App\Http\Resources\sliderResource;
use App\Http\Resources\sub_sectionResource;
use App\Http\Resources\serviceResource;
use App\Http\Resources\serviceDetailsResource;
use App\Http\Resources\dataResource;
use App\Http\Resources\cartResource;
use App\Http\Resources\countryResource;
use App\Http\Resources\addressResource;
#config->app
use Validator;
use Auth;
use App;
use Hash;
#Mail
use Mail;
use App\Mail\ActiveCode;
#Models
use App\Models\Role;
use App\Models\Section;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Bank_account;
use App\Models\Bank_transfer;
use App\Models\Contact;
use App\Models\Device;
use App\Models\Slider;
use App\Models\Sub_section;
use App\Models\User_address;
use App\Models\Property_item;
use App\Models\Service;
use App\Models\Service_group;
use App\Models\Service_group_property;
use App\Models\Service_like;
use App\Models\Service_review;
use App\Models\Cart;
use App\Models\Country;
use App\User;

class apiController extends Controller
{

    /*
    |----------------------------------------------------|
    |                Home Page Start                     |
    |----------------------------------------------------|
    */

    #home
    public function home(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        $data = [
            'sliders'  => sliderResource::collection(Slider::get()),
            'sections' => sectionResource::collection(Section::get()),
            'services' => serviceResource::collection(Service::orderBy('id' , 'desc')->get()),
        ];
        #success response
        return api_response(1, trans('api.send'), $data, ['notification_count' => user_notify_count($request->user_id)]);
    }

    /*
    |----------------------------------------------------|
    |                 data Page Start                    |
    |----------------------------------------------------|
    */

    #show country
    public function showCountry(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        $title = App::getLocale() == 'en' ? 'title_en' : 'title_ar';
        $data = [
            'countrys'      => countryResource::collection(Country::orderBy($title)->get()),
        ];
        #success response
        return api_response(1, trans('api.send'), $data, ['notification_count' => user_notify_count($request->user_id)]);
    }

    #data
    public function showData(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        $data = [
            'sections'      => sectionResource::collection(Section::get()),
            'sizes'         => dataResource::collection(Property_item::where('property_id' , '1')->get()),
            'colors'        => dataResource::collection(Property_item::where('property_id' , '2')->get()),
        ];
        #success response
        return api_response(1, trans('api.send'), $data, ['notification_count' => user_notify_count($request->user_id)]);
    }

    #show section
    public function showSection(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #success response
        return api_response(1, trans('api.send'), sectionResource::collection(Section::get()), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #show sub_section
    public function showSubSection(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'    => 'nullable|exists:users,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #success response
        return api_response(1, trans('api.send'), sub_sectionResource::collection(Sub_section::whereSectionId($request->section_id)->get()), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #show services & search & filter
    public function showServices(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'           => 'nullable|exists:users,id',
            'section_id'        => 'nullable', // 0=all
            'sub_section_id'    => 'nullable', // 0=all
            'property_item_ids' => 'nullable', // [id,id,id] or 0=all
            'min_price'         => 'nullable', // 0=all
            'max_price'         => 'nullable', // 0=all
            'title'             => 'nullable', // nullable=all
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #
        $first_ids          = ltrim($request->property_item_ids, ',');
        $second_ids         = rtrim($first_ids, ',');
        $property_item_ids  = empty($second_ids) ? [] : explode(',', $second_ids);

        $query = Service::query();
        if (!empty($request->title))
            $query->where('title_ar', 'like', '%' . $request->title . '%')->orWhere('title_en', 'like', '%' . $request->title . '%');
        if (!empty($request->min_price))
            $query->where('price', '>=' , $request->min_price);
        if (!is_null($request->max_price) && $request->max_price != -1)
            $query->where('price', '<=' , $request->max_price);
        if (!empty($request->section_id))
            $query->where('section_id', $request->section_id);
        if (!empty($request->sub_section_id))
            $query->where('sub_section_id', $request->sub_section_id);
        if (!empty($request->property_item_ids)) {
            $query->whereHas('group_properties', function($q) use ($property_item_ids){
                return $q->whereIn('property_item_id' , $property_item_ids);
            });
        }
        $data = $query->get();

        #success response
        return api_response(1, trans('api.send'), serviceResource::collection($data), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #show Favourite services
    public function showFavouriteServices(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        $data = Service::whereHas('Likes' , function($q) use ($request){
            return $q->where('user_id' , $request->user_id);
        })->get();

        #success response
        return api_response(1, trans('api.send'), serviceResource::collection($data), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #show service
    public function showService(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'           => 'nullable|exists:users,id',
            'service_id'        => 'nullable|exists:services,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #success response
        return api_response(1, trans('api.send'), new serviceDetailsResource(Service::whereId($request->service_id)->first()), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #store review
    public function storeReview(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'service_id'        => 'required|exists:services,id',
            'review_id'         => 'required|exists:reviews,id',
            'rate'              => 'nullable',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #store review
        Service_review::create($request->except(['lang']));

        #success response
        return api_response(1, trans('api.save'));
    }

    #add to like
    public function addToLike(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'service_id'        => 'required|exists:services,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #store or delete like
        $service = Service_like::where('service_id' , $request->service_id)->where('user_id' , $request->user_id)->first();
        if(isset($service)) $service->delete();
        else Service_like::create($request->except(['lang']));

        #success response
        return api_response(1, trans('api.save'));
    }

    /*
    |---------------------------------------------|
    |                 Cart Pages                  |
    |---------------------------------------------|
    */

    #store cart
    public function add_to_cart(Request $request)
    {
        /** Validate Request **/
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'service_id'        => 'required|exists:services,id',
            'group_id'          => 'required|exists:service_groups,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #data
        $service = Service::whereId($request->service_id)->first();
        $group   = Service_group::whereId($request->group_id)->first();
        $size    = Service_group_property::whereGroupId($request->group_id)->where('property_id' , '1')->first();
        $color   = Service_group_property::whereGroupId($request->group_id)->where('property_id' , '2')->first();
        #check amount
        if ((int) $group->amount > 0) {
            #check cart
            $add = Cart::where('user_id' , $request->user_id)->where('group_id' , $request->group_id)->first();
            if (isset($add)) {
                #update cart
                $next_count  = (int) $add->count + 1;
                $sub_data = [
                    'sub_total' => Cart::where('user_id' , $request->user_id)->sum('total'),
                    'delivery'  => Cart::distinct('service_id')->where('user_id' , $request->user_id)->sum('delivery')
                ];
                #check amount
                if ($next_count > $group->amount) return api_response('0', awtTrans('لا يوجد الكمية المطلوبة لهذا المنتج'),null,$sub_data);
                $add->total     = $group->price * ($add->count + 1);
                $add->count     = $add->count + 1;
                $add->save();
            } else {
                #add to cart
                $add = new Cart;
                $add->user_id       = $request->user_id;
                $add->service_id    = $request->service_id;
                $add->group_id      = $request->group_id;
                $add->size          = $size->item->title_en;
                $add->color         = $color->item->title_en;
                $add->hex           = $color->item->color;
                $add->delivery      = get_delivery($service);
                $add->total         = $group->price;
                $add->count         = 1;
                $add->save();
            }

            $sub_data = [
                'sub_total' => Cart::where('user_id' , $request->user_id)->sum('total'),
                'delivery' => Cart::distinct('service_id')->where('user_id' , $request->user_id)->sum('delivery')
            ];

            return api_response('1', awtTrans('تم الاضافة الى السلة بنجاح'), new cartResource($add), $sub_data);
        }

        $sub_data = [
            'sub_total' => Cart::where('user_id' , $request->user_id)->sum('total'),
            'delivery' => Cart::distinct('service_id')->where('user_id' , $request->user_id)->sum('delivery')
        ];

        return api_response('0', awtTrans('لا يوجد الكمية المطلوبة لهذا المنتج') ,null, $sub_data);
    }

    #update cart
    public function update_to_cart(Request $request)
    {
        /** Validate Request **/
        $validate = Validator::make($request->all(), [
            'cart_id' => 'required|exists:carts,id',
            'count'   => 'required',
        ]);

        /** Send Error Massages **/
        if ($validate->fails())
            return api_response('0', $validate->errors()->first());

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
            $sub_data = [
                'sub_total' => Cart::where('user_id' , $request->user_id)->sum('total'),
                'delivery'  => Cart::distinct('service_id')->where('user_id' , $request->user_id)->sum('delivery')
            ];
            return api_response('1', awtTrans('تم تحديث السلة بنجاح'), new cartResource($add) , $sub_data);
        }

        $sub_data = [
            'sub_total' => Cart::where('user_id' , $request->user_id)->sum('total'),
            'delivery'  => Cart::distinct('service_id')->where('user_id' , $request->user_id)->sum('delivery')
        ];
        return api_response('0', awtTrans('لا يوجد الكمية المطلوبة لهذا المنتج') ,null, $sub_data);
    }

    #update all cart
    public function update_all_cart(Request $request)
    {
        /** Validate Request **/
        $validate = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,id',
            'items'        => 'required',
        ]);

        /** Send Error Massages **/
        if ($validate->fails())
            return api_response('0', $validate->errors()->first());

        /** add to cart **/
        $items = json_decode($request->items);
        Cart::where('user_id', $request->user_id)->delete();
        $checkQ = false;
        foreach ($items as $item) {
            #data
            $service = Service::whereId($item->service_id)->first();
            $group   = Service_group::whereId($item->group_id)->first();
            $size    = Service_group_property::whereGroupId($item->group_id)->where('property_id' , '1')->first();
            $color   = Service_group_property::whereGroupId($item->group_id)->where('property_id' , '2')->first();
            if ($group->amount >= $item->count && $item->count > 0) {
                $add = new Cart;
                $add->user_id       = $request->user_id;
                $add->service_id    = $item->service_id;
                $add->group_id      = $item->group_id;
                $add->size          = $size->item->title_en;
                $add->color         = $color->item->title_en;
                $add->hex           = $color->item->color;
                $add->delivery      = get_delivery($service);
                $add->count         = $item->count;
                $add->total         = $group->price * $item->count;
                $add->save();
            } elseif($item->count > 0) {
                $checkQ = true;
            }
        }

        $sub_data = [
            'sub_total' => Cart::where('user_id' , $request->user_id)->sum('total'),
            'delivery' => Cart::distinct('service_id')->where('user_id' , $request->user_id)->sum('delivery')
        ];

        if($checkQ) $msg = awtTrans('لا يوجد الكمية المطلوبة لمنتج او اكثر');
        else        $msg = awtTrans('تم تحديث السلة بنجاح');
        return api_response('1', $msg ,null, $sub_data);
    }

    #show cart
    public function show_cart(Request $request)
    {
        /** Validate Request **/
        $validate = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,id',
        ]);

        /** Send Error Massages **/
        if ($validate->fails())
            return api_response('0', $validate->errors()->first());

        $sub_data = [
            'sub_total' => Cart::where('user_id' , $request->user_id)->sum('total'),
            'delivery' => Cart::distinct('service_id')->where('user_id' , $request->user_id)->sum('delivery'),
            'notification_count' => user_notify_count($request->user_id)
        ];

        /** Send Success Massage **/
        return api_response('1', trans('api.send'), CartResource::collection(Cart::where('user_id', $request->user_id)->get()), $sub_data);
    }

    /*
    |----------------------------------------------------|
    |                Address Page Start                  |
    |----------------------------------------------------|
    */

    #show address
    public function showAddress(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #success response
        return api_response(1, trans('api.send'), addressResource::collection(User_address::where('user_id', $request->user_id)->get()), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #store address
    public function storeAddress(Request $request)
    {
        /** Validate Request **/
        $validate = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'first_name'    => 'required|max:255',
            'last_name'     => 'nullable|max:255',
            'country_id'    => 'required|max:255|exists:countries,id',
            'city'          => 'required|max:255',
            'first_street'  => 'required|max:255',
            'second_street' => 'required|max:255',
            'phone'         => 'required|min:7|max:13',
            'email'         => 'required|max:255|email',
        ]);

        /** Send Error Massages **/
        if ($validate->fails()) return api_response('0', $validate->errors()->first());

        #store address
        User_address::create($request->except(['lang']));

        #success response
        return api_response(1, trans('api.save'));
    }

    #update address
    public function updateAddress(Request $request){
        /** Validate Request **/
        $validate = Validator::make($request->all(), [
            'address_id'    => 'required|exists:user_addresses,id',
            'first_name'    => 'required|max:255',
            'last_name'     => 'nullable|max:255',
            'country_id'    => 'required|max:255|exists:countries,id',
            'city'          => 'required|max:255',
            'first_street'  => 'required|max:255',
            'second_street' => 'required|max:255',
            'phone'         => 'required|min:7|max:13',
            'email'         => 'required|max:255|email',
        ]);

        /** Send Error Massages **/
        if ($validate->fails()) return api_response('0', $validate->errors()->first());

        #update address
        $address = User_address::whereId($request->address_id)->first();
        $address->update($request->except(['lang' , 'address_id']));

        #success response
        return api_response(1, trans('api.save'));
    }

    #delete address
    public function deleteAddress(Request $request){
        /** Validate Request **/
        $validate = Validator::make($request->all(), [
            'address_id'    => 'required|exists:user_addresses,id',
        ]);

        /** Send Error Massages **/
        if ($validate->fails()) return api_response('0', $validate->errors()->first());

        #delete address
        User_address::whereId($request->address_id)->delete();

        #success response
        return api_response(1, trans('api.save'));
    }

    /*
    |----------------------------------------------------|
    |                static Page Start                   |
    |----------------------------------------------------|
    */

    #page (About us , Conditions)
    public function page(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'title'   => 'required|in:about,condition',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #success response
        return api_response(1, trans('api.send'), new pageResource(Page::where('title_en', 'like', '%' . $request->title . '%')->first()), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #contact us
    public function contactUs(Request $request)
    {
        #store contact us
        $request->request->add(['seen' => '0']);
        Contact::create($request->except(['lang']));

        #success response
        return api_response(1, trans('api.send'));
    }

    /*
    |----------------------------------------------------|
    |               Bank Page Start                      |
    |----------------------------------------------------|
    */

    #bank Account
    public function bankAccount(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #success response
        return api_response(1, trans('api.send'), bankResource::collection(Bank_account::get()), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #bank Transfer
    public function bankTransfer(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #bank transfer
        Bank_transfer::create($request->except(['lang']));

        #success response
        return api_response(1, trans('api.send'));
    }

    /*
    |----------------------------------------------------|
    |           Notification Page Start                  |
    |----------------------------------------------------|
    */

    #show Notification
    public function showNotification(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #update seen
        Notification::whereToId($request->user_id)->update(['seen' => 1]);

        #success response
        return api_response(1, trans('api.send'), notificationResource::collection(Notification::whereToId($request->user_id)->orderBy('id', 'desc')->get()), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #delete Notification
    public function deleteNotification(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'notification_id' => 'nullable|exists:notifications,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #delete notification
        Notification::whereId($request->notification_id)->delete();

        #success response
        return api_response(1, trans('api.delete'));
    }
}
