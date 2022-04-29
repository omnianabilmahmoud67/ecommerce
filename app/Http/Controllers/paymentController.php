<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Auth;
use Session;
use App\User;
use Illuminate\Http\Request;

class paymentController extends Controller
{
    /*
    |----------------------------------------------------|
    |             my_fatoorah Payment Start              |
    |----------------------------------------------------|
    */

    #my_fatoorah
    public function my_fatoorah()
    {
        $token  = "cxu2LdP0p0j5BGna0velN9DmzKJTrx3Ftc0ptV8FmvOgoDqvXivkxZ_oqbi_XM9k7jgl3SUriQyRE2uaLWdRumxDLKTn1iNglbQLrZyOkmkD6cjtpAsk1_ctrea_MeOQCMavsQEJ4EZHnP4HoRDOTVRGvYQueYZZvVjsaOLOubLkdovx6STu9imI1zf5OvuC9rB8p0PNIR90rQ0-ILLYbaDZBoQANGND10HdF7zM4qnYFF1wfZ_HgQipC5A7jdrzOoIoFBTCyMz4ZuPPPyXtb30IfNp47LucQKUfF1ySU7Wy_df0O73LVnyV8mpkzzonCJHSYPaum9HzbvY5pvCZxPYw39WGo8pOMPUgEugtaqepILwtGKbIJR3_T5Iimm_oyOoOJFOtTukb_-jGMTLMZWB3vpRI3C08itm7ealISVZb7M3OMPPXgcss9_gFvwYND0Q3zJRPmDASg5NxRlEDHWRnlwNKqcd6nW4JJddffaX8p-ezWB8qAlimoKTTBJCe5CnjT4vNjnWlJWscvk38VNIIslv4gYpC09OLWn4rDNeoUaGXi5kONdEQ0vQcRjENOPAavP7HXtW1-Vz83jMlU3lDOoZsdEKZReNYpvdFrGJ5c3aJB18eLiPX6mI4zxjHCZH25ixDCHzo-nmgs_VTrOL7Zz6K7w6fuu_eBK9P0BDr2fpS";
        //$basURL = "https://api.myfatoorah.com"; //Live
        $basURL = "https://apitest.myfatoorah.com"; // Test
        $user   = Auth::user();
        $rand   = rand(11111111, 99999999);
        #get id
        $id = Session::get('order_id');
        #order
        $order = Order::whereId($id)->firstOrFail();
        $amount = $order->total;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$basURL/v2/SendPayment",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"NotificationOption\":\"LNK\",\"CustomerName\": \"$user->name\",\"DisplayCurrencyIso\": \"SAR\", \"MobileCountryCode\":\"+966\",\"CustomerMobile\": \"$user->phone\",\"CustomerEmail\": \"$user->email\",\"InvoiceValue\": $amount,\"CallBackUrl\": \"https://matjar.wafrnalak.com/my_fatoorah_success\",\"ErrorUrl\": \"https://matjar.wafrnalak.com/my_fatoorah_faild\",\"Language\": \"en\",\"CustomerReference\" :\"ref 1\",\"CustomerCivilId\":$rand,\"UserDefinedField\": \"Custom field\",\"ExpireDate\": \"\",\"CustomerAddress\" :{\"Block\":\"\",\"Street\":\"\",\"HouseBuildingNo\":\"\",\"Address\":\"\",\"AddressInstructions\":\"\"},\"InvoiceItems\": [{\"ItemName\": \"Products\",\"Quantity\": 1,\"UnitPrice\": $amount}]}",
            CURLOPT_HTTPHEADER => array("Authorization: Bearer $token", "Content-Type: application/json"),

        ));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            session()->flash('success', awtTrans('حدث خطأ اثناء عملية الدفع'));
            return redirect()->route('site_home');
        } else {
            $json   = json_decode((string) $response, true);
            if (!isset($json["IsSuccess"]) || !$json["IsSuccess"]) {
                session()->flash('success', awtTrans('حدث خطأ اثناء عملية الدفع'));
                return redirect()->route('site_home');
            }
            $payment_url = $json["Data"]["InvoiceURL"];
            return redirect($payment_url);
        }
    }

    #my_fatoorah_success
    public function my_fatoorah_success(Request $request)
    {
        #get id
        $id = Session::get('order_id');
        Session::forget('order_id');
        #update order
        $order = Order::whereId($id)->first();
        $order->update(['payment_done' => 1]);
        #redirect with msg
        session()->flash('success', awtTrans('تم بنجاح'));
        return redirect('show-profile?tab=order');
    }

    #my_fatoorah_faild
    public function my_fatoorah_faild(Request $request)
    {
        Session::forget('id');
        session()->flash('success', awtTrans('حدث خطأ اثناء عملية الدفع'));
        return redirect()->route('site_home');
    }

    /*
    |----------------------------------------------------|
    |----------------------------------------------------|
    |             HyperPay Payment Start                 |
    |----------------------------------------------------|
    |----------------------------------------------------|
    */

    /********* For Test  ***********/
    // Testing Cards for credit Card :
    // 4111111111111111 05/21 cvv2 123  (Success)
    // 5204730000002514 05/22 cvv2 251  (Fail)

    /********* create form  ***********/
    public function createForm(Request $request)
    {

        $this->validate($request, [
            'user_id'    => 'required|exists:users,id',
            'amount'     => 'required',
            // 'order_id'   => 'required',
        ]);

        //data
        $user   = User::whereId($request->user_id)->firstOrFail();
        $amount = $request->amount;

        $url = "https://test.oppwa.com/v1/checkouts";
        $data = "entityId=8ac7a4c86fd10a7a016fd10ff55e0014" .
            "&amount=" . $amount . // Amount
            "&currency=SAR" . //SAR
            "&testMode=EXTERNAL" .
            "&merchantTransactionId=" . rand(1111111111, 9999999999) .
            "&customer.email=" . $user->email .
            "&paymentType=DB";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:Bearer OGFjN2E0Yzg2ZmQxMGE3YTAxNmZkMTBmN2ExYzAwMGZ8TU1BU1g5N0RjYQ=='
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        $responseData = json_decode($responseData, true);
        $code = isset($responseData['result']['code']) ? $responseData['result']['code']  : '-1';

        #Success
        if (is_success($code)) return view('payment.hyperpay', compact('responseData'));
        #Faild
        return api_response(0, 'faild');
    }

    /********* payment Result  ***********/
    public function paymentResult(Request $request)
    {
        $url  = "https://test.oppwa.com" . $request['resourcePath'];
        $url .= "?entityId=8ac7a4c86fd10a7a016fd10ff55e0014";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:Bearer OGFjN2E0Yzg2ZmQxMGE3YTAxNmZkMTBmN2ExYzAwMGZ8TU1BU1g5N0RjYQ=='
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        $responseData = json_decode($responseData, true);
        $code = isset($responseData['result']['code']) ? $responseData['result']['code']  : '-1';

        #Success
        if (is_success($code)) return api_response(1, 'success');
        #Faild
        return api_response(0, 'faild');
    }
}
