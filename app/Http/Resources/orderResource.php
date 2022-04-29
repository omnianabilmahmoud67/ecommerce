<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\order_itemResource;
use App;

class orderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        $title         = App::getLocale() == 'en' ? 'title_en' : 'title_ar';
        $payment       = ["cash" => awtTrans('كاش'), "transfer" => awtTrans('تحويل بنكي'), "online" => awtTrans('اون لاين')];
        $status   = [
            "new"     => awtTrans('طلب جديد'),
            "current" => awtTrans('طلب مؤكد'),
            "in_way"  => awtTrans('جاري التوصيل'),
            "refused" => awtTrans('طلب مرفوض'),
            "cancel"  => awtTrans('طلب ملغى'),
            "finish"  => awtTrans('طلب منتهي'),
        ];
        return [
            'id'               => (int)    $this->id,
            'first_name'       => (string) $this->first_name,
            'last_name'        => (string) $this->last_name,
            'country_id'       => (int)    $this->country_id,
            'city'             => (string) $this->city,
            'first_street'     => (string) $this->first_street,
            'second_street'    => (string) $this->second_street,
            'phone'            => (string) $this->phone,
            'email'            => (string) $this->email,
            'notes'            => (string) $this->notes,
            'status'           => (string) $this->status,
            'status_f'         => isset($status[$this->status]) ? $status[$this->status] : awtTrans('طلب جديد'),
            'sub_total'        => (float)  $this->sub_total,
            'discount'         => (float)  $this->discount,
            'delivery'         => (float)  $this->delivery,
            // 'value_added'       => (float)  $this->value_added,
            'total'            => (float)  $this->total,
            'payment_method'   => (string) $this->payment_method,
            'payment_method_f' => isset($payment[$this->payment_method]) ? $payment[$this->payment_method] : awtTrans('كاش'),
            'date'             => date('Y-m-d', strtotime($this->created_at)),
            'time'             => date('h:i a', strtotime($this->created_at)),
            'from'             => $this->created_at->diffForHumans(),

            'order_items'       => order_itemResource::collection($this->items)
        ];
    }
}
