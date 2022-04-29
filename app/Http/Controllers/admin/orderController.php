<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class orderController extends Controller
{
    #index
    public function index($status)
    {
        $data = Order::whereStatus($status)->get();
        return view('dashboard.orders.index', compact('data'));
    }

    #show
    public function show($id)
    {
        $order = Order::whereId($id)->firstOrFail();
        $days  = json_decode($order->days_id);
        return view('dashboard.orders.show', compact('order', 'days'));
    }

    #change
    public function change($id)
    {
        Order::whereId($id)->update(['status' => 'in_way']);
        return back()->with('success', awtTrans('الطلب في الطريق الان'));
    }
}
