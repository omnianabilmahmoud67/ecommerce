<?php

namespace App\Http\Controllers\api;

#Basic
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
#resources
use App\Http\Resources\userResource;
use App\Http\Resources\orderResource;
#config->app
use Validator;
use Auth;
use Hash;
use App;
#Mail
use Mail;
use App\Mail\ActiveCode;
#Models
use App\Models\Cart;
use App\Models\Order;
use App\Models\Order_item;
use App\Models\User_address;
use App\Models\Property;
use App\Models\Property_item;
use App\Models\Service;
use App\Models\Service_group;
use App\Models\Service_group_property;
use App\Models\Promo_code;
use App\User;

class orderController extends Controller
{
    #check promo code
    public function checkPromo(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,id',
            'code'         => 'required',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        # get discount
        $promo = check_promo_code($request->user_id, $request->code);
        #invalid promo code
        if (!isset($promo)) return api_response(0, trans('api.invaildPromo'), ['id' => 0 , 'discount' => 0]);
        if (isset($promo) && $promo->discount == 0) return api_response(0, trans('api.invaildPromo'), ['id' => 0 , 'discount' => 0]);
        #success promo code
        return api_response(1, trans('api.send'), ['id' => $promo->id , 'discount' => (float) $promo->discount]);
    }

    #store order
    public function storeOrder(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'            => 'required|exists:users,id',
            'address_id'         => 'required|exists:user_addresses,id',
            'promo_id'           => 'nullable|exists:promo_codes,id',
            'sub_total'          => 'required',
            'discount'           => 'required',
            'delivery'           => 'required',
            'total'              => 'required',
            'payment_method'     => 'required|in:cash,transfer,online',
            // 'value_added'        => 'required',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #json decode
        $items = Cart::where('user_id' , $request->user_id)->get();
        #faild response
        if (count($items) == 0) return api_response(0, awtTrans('لا يوجد منتجات بالسلة'));

        #store order
        $user       = User::whereId($request->user_id)->first();
        $promo      = Promo_code::whereId($request->promo_id)->first();
        $address    = User_address::whereId($request->address_id)->first();
        $request->request->add([
            'status'         => 'new',
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
        $order = Order::create($request->except(['lang']));

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
        Cart::where('user_id' , $request->user_id)->delete();

        #success response
        return api_response(1, trans('api.save'), new orderResource($order), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #show all orders
    public function showAllOrders(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'status'  => 'nullable|in:new,current,refused,finish',
        ]);

        if(!empty($request->status))
            $orders = Order::where('user_id', $request->user_id)->where('status', $request->status)->where('user_seen' , '1')->orderBy('id', 'desc')->get();
        else
            $orders = Order::where('user_id', $request->user_id)->where('user_seen' , '1')->orderBy('id', 'desc')->get();

        #success response
        return api_response(1, trans('api.send'), orderResource::collection($orders), ['notification_count' => user_notify_count($request->user_id)]);
    }

    #show order
    public function showOrder(Request $request)
    {
        #validation
        $validator = Validator::make($request->all(), [
            'user_id'  => 'nullable|exists:users,id',
            'order_id' => 'required|exists:orders,id',
        ]);

        #error response
        if ($validator->fails()) return api_response(0, $validator->errors()->first());

        #success response
        return api_response(1, trans('api.save'), new orderResource(Order::whereId($request->order_id)->first()), ['notification_count' => user_notify_count($request->user_id)]);
    }
}
