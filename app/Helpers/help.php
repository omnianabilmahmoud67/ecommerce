<?php
#Models
use App\Models\Subscription;
use App\Models\Page;
use App\Models\Rate;
use App\Models\Section;
use App\Models\Admin_report;
use App\Models\Country;
use App\Models\Device;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\Promo_code;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Social;
use App\Models\Service;
use App\Models\Service_like;
use App\Models\Service_group;
use App\Models\Service_group_property;
use App\Models\Property;
use App\Models\Property_item;
use App\User;
#packages
use Carbon\Carbon;
#vendor files
use Illuminate\Support\Facades\App;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

/*
|----------------------------------------------------|
|----------------------------------------------------|
|                 awtTrans Helper Start              |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#Using to solve a problem at awtTrans()
function Translate($text, $lang)
{
    $api  = 'trnsl.1.1.20190807T134850Z.8bb6a23ccc48e664.a19f759906f9bb12508c3f0db1c742f281aa8468';
    $url = file_get_contents('https://translate.yandex.net/api/v1.5/tr.json/translate?key=' . $api
        . '&lang=ar' . '-' . $lang . '&text=' . urlencode($text));
    $json = json_decode($url);
    return $json->text[0];
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|               Permission Helper Start              |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#check if user has Permission
function hasPermission($routeName)
{
    if (Auth::guest()) return false;
    $role = Role::whereId(Auth::user()->role_id)->first();
    if (!isset($role)) return false;

    $permissions = Permission::whereRoleId(Auth::user()->role_id)->pluck('name')->toArray();
    return in_array($routeName, $permissions);
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|                 path Helper Start                  |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#Dashboard path
function dashboard_path()
{
    return url('/public/dashboard');
}

#Site path
function site_path()
{
    return url('/public/site');
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|                 phone Helper Start                 |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#convert arabic number to english format
function convert_to_english($string)
{
    $newNumbers = range(0, 9);
    $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
    $string =  str_replace($arabic, $newNumbers, $string);
    return $string;
}

#convert phone to soudi arabia format
function convert_phone_to_international_format($phone, $code = '966')
{
    $withoutZero  = ltrim(convert_to_english($phone), '0');
    $filter_zero  = ltrim($withoutZero, $code . '0');
    $filter_code  = ltrim($filter_zero, $code);
    $full_phone   = $code . $filter_code;
    return $full_phone;
}


/*
|----------------------------------------------------|
|----------------------------------------------------|
|                 Image Helper Start                 |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#upload multi-part image
function upload_image($photo, $dir)
{
    #upload image
    if (!is_dir($dir))
        mkdir($dir, 0777);
    $name = date('d-m-y') . time() . rand() . '.' . $photo->getClientOriginalExtension();
    $photo->move($dir . '/', $name);
    return '/' . $dir . '/' . $name;
}

#upload image base64
function save_image($base64_img, $img_name, $path)
{
    $image = base64_decode($base64_img);
    $pathh = $_SERVER['DOCUMENT_ROOT'] . '/' . $path . '/' . $img_name . '.png';
    file_put_contents($pathh, $image);
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|                 SMS Helper Start                   |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#send sms
function send_sms($phone, $msg, $package = 'zain')
{
    $data = [
        'username' => 'fofo7070',
        'password' => '123456',
        'sender'   => 'efadh.com',
    ];
    //$link = "https://www.zain.im/index.php/api/sendsms/?user=fofo7070&pass=123456&to=$phones&message=$msg&sender=efadh.com";
    switch ($package) {
        case 'our_sms':
            send_sms_our_sms($phone, $msg, $data);
            break;
        case 'zain':
            send_sms_zain($phone, $msg, $data);
            break;
        case 'mobily':
            send_sms_mobily($phone, $msg, $data);
            break;
        case 'yammah':
            send_sms_yammah($phone, $msg, $data);
            break;
        case 'hisms':
            send_sms_hisms($phone, $msg, $data);
            break;
        default:
            return false;
    }
}

#our_sms package
function send_sms_our_sms($phone, $msg, $data)
{
    sleep(1);
    $username   = $data['username'];
    $password   = $data['password'];
    $sender     = $data['sender'];
    $text       = urlencode($msg);
    $to         = '+' . $phone;
    // auth call
    //$url = "http://www.oursms.net/api/sendsms.php?username=$user&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=full";
    //لارجاع القيمه json
    $url = "http://www.oursms.net/api/sendsms.php?username=$username&password=$password&numbers=$to&message=$text&sender=$sender&unicode=E&return=json";
    // لارجاع القيمه xml
    //$url = "http://www.oursms.net/api/sendsms.php?username=$user&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=xml";
    // لارجاع القيمه string
    //$url = "http://www.oursms.net/api/sendsms.php?username=$user&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E";

    // Call API and get return message
    //fopen($url,"r");
    //return $url;
    $ret = file_get_contents($url);
    //echo nl2br($ret);
}

#zain package
function send_sms_zain($phone, $msg, $data)
{
    sleep(1);
    $username   = $data['username'];
    $password   = $data['password'];
    $sender     = $data['sender'];
    $to         = $phone; // Should be like 966530007039
    $text       = urlencode($msg . '   ');
    //$link = "https://www.zain.im/index.php/api/sendsms/?user=user&pass=123456&to=$phones&message=$msg&sender=sender";
    $link = "https://www.zain.im/index.php/api/sendsms/?user=$username&pass=$password&to=$to&message=$text&sender=$sender";

    /*
        *  return  para      can be     [ json , xml , text ]
        *  username  :  your username on safa-sms
        *  passwpord :  your password on safa-sms
        *  sender    :  your sender name
        *  numbers   :  list numbers delimited by ,     like    966530007039,966530007039,966530007039
        *  message   :  your message text
        */

    /*
        * 100   Success Number
        */

    if (function_exists('curl_init')) {
        $curl = @curl_init($link);
        @curl_setopt($curl, CURLOPT_HEADER, FALSE);
        @curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        @curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        $source = @curl_exec($curl);
        @curl_close($curl);
        if ($source) {
            return $source;
        } else {
            return @file_get_contents($link);
        }
    } else {
        return @file_get_contents($link);
    }
}

#mobily package
function send_sms_mobily($phone, $msg, $data)
{
    sleep(1);
    $url        = 'http://api.yamamah.com/SendSMS';
    $username   = $data['username'];
    $password   = $data['password'];
    $sender     = $data['sender'];
    $to         = $phone; // Should be like 966530007039
    $text       = urlencode($msg);
    $sender     = urlencode($sender);
    $fields   = array(
        "Username"        => $username,
        "Password"        => $password,
        "Tagname"         => $sender,
        "Message"         => $text,
        "RecepientNumber" => $to,
    );
    $fields_string = json_encode($fields);
    //open connection
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        CURLOPT_POSTFIELDS => $fields_string
    ));

    $result = curl_exec($ch);
    curl_close($ch);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

#yammah package
function send_sms_yammah($phone, $msg, $data)
{
    sleep(1);
    $url        = 'api.yamamah.com/SendSMS';
    $username   = $data['username'];
    $password   = $data['password'];
    $sender     = $data['sender'];
    $to         = $phone; // Should be like 966530007039
    $text       = urlencode($msg);
    $fields = array(
        "Username" => $username,
        "Password" => $password,
        "Message" => $text,
        "RecepientNumber" => $to, //'00966'.ltrim($numbers,'0'),
        "ReplacementList" => "",
        "SendDateTime" => "0",
        "EnableDR" => False,
        "Tagname" => $sender,
        "VariableList" => "0"
    );

    $fields_string = json_encode($fields);

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        CURLOPT_POSTFIELDS => $fields_string
    ));
    $result = curl_exec($ch);
    curl_close($ch);
}

#hisms package
function send_sms_hisms($phone, $msg, $data)
{
    sleep(1);
    $url        = 'https://www.hisms.ws/api.php?send_sms&';
    $username   = $data['username'];
    $password   = $data['password'];
    $sender     = $data['sender'];
    $to         = $phone; // Should be like 966530007039
    $text       = urlencode($msg);
    $fields = [
        "username" => $username,
        "password" => $password,
        "numbers"  => $to,
        "sender"   => $sender,
        "message"  => $text,
    ];

    //open connection
    $ch = curl_init($url);
    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => $url . http_build_query($fields, null, '&'),
            CURLOPT_RETURNTRANSFER => true
        ]
    );

    $result = curl_exec($ch);
    curl_close($ch);
    // echo $result;
}

#alfa-cell
function send_alfa_cell($phone, $msg)
{

    $apiKey     = '';
    $sender     = '';
    $url        = 'https://www.alfa-cell.com/api/msgSend.php?apiKey=' . urlencode($apiKey) . '&numbers=' . urlencode($phone) . '&sender=' . urlencode($sender) . '&msg=' . urlencode($msg) . '&timeSend=0&dateSend=0&applicationType=68&domainName=aait.sa&msgId=15176';
    $json       = json_decode(file_get_contents($url), true);

    return $json;
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|              Payment Helper Start                  |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#HyperPay
function is_success($code)
{

    $arr = [
        '000.000.000',
        '000.000.100',
        '000.100.110',
        '000.100.111',
        '000.100.112',
        '000.300.000',
        '000.300.100',
        '000.300.101',
        '000.300.102',
        '000.600.000',
        '000.200.100'
    ];

    return in_array($code, $arr) ? true : false;
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|                  Mail Helper Start                 |
|----------------------------------------------------|
|----------------------------------------------------|
*/


function send_mail($email, $msg)
{
    // In case any of our lines are larger than 70 characters, we should use wordwrap()
    $message = wordwrap($msg, 70, "\r\n");

    // Send
    mail($email, settings('site_name_en'), $message);
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|                  API Helper Start                  |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#api response format
function api_response($key, $msg, $data = null, $anotherKey = [])
{
    $all_response['key']         = (int) $key;
    $all_response['msg']         = $msg;
    if (!empty($anotherKey)) {
        foreach ($anotherKey as $key => $value) {
            $all_response[$key] = $value;
        }
    }
    if (!is_null($data)) $all_response['data'] = $data;
    return response()->json($all_response);
}

#set lang
function set_lang($lang)
{
    /** Set Site Lang **/
    $lang == 'en' ? App::setLocale('en') : App::setLocale('ar');
    /** Set Carbon Lang **/
    $lang == 'en' ? Carbon::setLocale('en') : Carbon::setLocale('ar');
}

#user status
function user_status($user)
{
    $status = 'active';
    if ($user->active == 0) $status = 'non-active';
    if ($user->blocked == 1) $status = 'blocked';
    return $status;
}

#order status
// function order_status($order)
// {
//     $status = $order->status;
//     if ($status == 'pre-order') $status_desc  = awtTrans('طلب جديد غير مدفوع');
//     if ($status == 'new')       $status_desc  = awtTrans('طلب جديد');
//     if ($status == 'agree')     $status_desc  = awtTrans('طلب حالي');
//     if ($status == 'refused')   $status_desc  = awtTrans('طلب ملغي');
//     if ($status == 'finish')    $status_desc  = awtTrans('طلب منتهي');
//     return $status_desc;
// }

#user rate
function user_rate($user_id)
{
    $rate = Rate::where('to_id', $user_id)->avg('rate');
    return $rate > 0 ? $rate : 0;
}

#order status
function order_status($status)
{
    $order_status  = [
        "pre-order" => awtTrans('طلب جديد غير مدفوع'),
        "new"       => awtTrans('طلب جديد'),
        "agree"     => awtTrans('طلب حالي'),
        "refused"   => awtTrans('طلب ملغي'),
        "finish"    => awtTrans('طلب منتهي'),
    ];
    return isset($order_status[$status]) ? $order_status[$status] : awtTrans('طلب جديد');
}

#update user device
function update_device($user, $device_id, $device_type = null)
{
    Device::where('device_id', $device_id)->delete();
    Device::create(
        ['user_id' => $user->id, 'device_id' => $device_id, 'device_type' => $device_type]
    );
}

#send notify
function send_notify($to_id, $message_ar, $message_en, $order_id = null, $order_status = null)
{
    Notification::create([
        'to_id'        => $to_id,
        'message_ar'   => $message_ar,
        'message_en'   => $message_en,
        'type'         => is_null($order_id) ? 'notify' : 'order',
        'order_id'     => $order_id,
        'order_status' => $order_status,
        'seen'         => 0,
    ]);
}

#user unseen notification count
function user_notify_count($user_id = null)
{
    return Notification::unSeenMessage($user_id);
}

#check if user make this service in favourite
function is_favourite($user_id, $service_id)
{
    $service = Service_like::where('user_id' , $user_id)->where('service_id' , $service_id)->first();
    return isset($service);
}

#get delivery
function get_delivery($service){
    return $service->delivery;
}

#get service group
function get_service_group($service_id)
{
    $size_ids = Service_group_property::distinct('property_item_id')->where('service_id' , $service_id)->where('property_id' , 1)
                ->orderBy('property_item_id' , 'asc')->pluck('property_item_id')->toArray();

    $data = [];
    foreach($size_ids as $i=>$size_id){
        $color_arr = [];
        $property  = Property_item::whereId($size_id)->first();
        $data[$i]['id']     = $size_id;
        $data[$i]['title']  = isset($property) ? $property->title : '';

        $group_ids  = Service_group_property::where('service_id' , $service_id)->where('property_item_id' , $size_id)->pluck('group_id')->toArray();
        $colors     = Service_group_property::where('service_id' , $service_id)
                                 ->where('property_id' , 2)
                                 ->whereIn('group_id' , $group_ids)
                                 ->orderBy('property_item_id' , 'asc')
                                 ->get();
        foreach($colors as $j=>$color){
            $property  = Property_item::whereId($color->property_item_id)->first();
            $color_arr[$j]['id']        = $property->id;
            $color_arr[$j]['group_id']  = $color->group_id;
            $color_arr[$j]['amount']    = $color->group->amount;
            $color_arr[$j]['price']     = $color->group->price;
            $color_arr[$j]['title']     = isset($property) ? $property->title : '';
            $color_arr[$j]['color']     = isset($property) ? $property->color : '';
        }

        $data[$i]['colors']  = $color_arr;
    }

    return $data;
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|            PromoCode Helper Start                  |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#check promo code
function check_promo_code($user_id, $code)
{
    $promo = Promo_code::whereCode($code)->first();
    if (!isset($promo)) return null; // wrong promoCode

    $used_by = is_null($promo->used_by) ? [] : json_decode($promo->used_by);
    if (in_array($user_id, $used_by) || $promo->status == 'invalid') return 0; // invalid promoCode

    return $promo;
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|                  FCM Helper Start                  |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#send FCM
function send_fcm($device_id, $data, $type, $setBadge = 0)
{
    $priority = 'high'; // or 'normal'
    // $action = 'FLUTTER_NOTIFICATION_CLICK';
    // if ($device->device_type == 'web') $action = '/';
    $optionBuilder = new OptionsBuilder();
    $optionBuilder->setTimeToLive(60 * 20);
    $optionBuilder->setPriority($priority);
    $notificationBuilder = new PayloadNotificationBuilder($data['title']);
    $notificationBuilder->setBody($data['body'])->setSound('default');
    //$notificationBuilder->setBody($data['message'])->setSound('default')->setBadge($setBadge)->setClickAction($action);

    $option = $optionBuilder->build();
    $notification = $notificationBuilder->build();
    $dataBuilder = new PayloadDataBuilder();
    $dataBuilder->addData($data);
    $data = $dataBuilder->build();

    if ($type == 'android') {
        $downstreamResponse = FCM::sendTo($token, $option, null, $data);
    } else {
        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
    }

    $downstreamResponse->numberSuccess();
    $downstreamResponse->numberFailure();
    $downstreamResponse->numberModification();
}



function send_one_signal($notmessage, $tokens, $status = 'order')
{
    $content = array(
        "en" => $notmessage
    );
    $token[] = $tokens;
    $fields = array(
        'app_id' => "3c2196e0-f2c1-4d97-82c3-a24314ef49fb",
        'include_player_ids' => $token,
        'data' => array("foo" => "bar", "notificationType" => $status),
        "heading" => "headings",

        'contents' => $content,
        "content_available" => true,
    );

    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic N2FiMTMzNDktNGVjOC00NTg0LWI4ZDAtNGRmNjZiN2QwNTZj'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|            adminReport Helper Start                |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#store adminReport
function admin_report($desc)
{
    $text = 'قام المدير ' . Auth::user()->name . ' ب' . $desc;
    Admin_report::create(['desc' => $text]);
}

/*
|----------------------------------------------------|
|----------------------------------------------------|
|                Youtube Helper Start                |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#get youtube embed link
function get_embed_link($youtubeUrl)
{
    // Extract id
    preg_match(
        "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/",
        $youtubeUrl,
        $videoId
    );
    return $youtubeVideoId = isset($videoId[1]) ? 'https://www.youtube.com/embed/' . $videoId[1] : "";
}


/*
|----------------------------------------------------|
|----------------------------------------------------|
|                 data Helper Start                  |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#get value from settings DB
function settings($key)
{
    $setting = Setting::firstOrCreate(['key' => $key]);
    return $setting->value;
}

#get social links
function social($key)
{
    $social = Social::firstOrCreate(['key' => $key]);
    return $social->value;
}

#get users by (userType , orderBy , paginateCount)
function get_users_by($userType = 'client', $orderBy = 'desc', $paginateCount = 24)
{
    if ($paginateCount != 0)
        $users = User::where('user_type', $userType)->orderBy('id', $orderBy)->paginate($paginateCount);
    else
        $users = User::where('user_type', $userType)->orderBy('id', $orderBy)->get();

    return $users;
}

#get all countries
function get_countries($orderBy = 'title_ar')
{
    $countries = Country::orderBy($orderBy, 'asc')->get();
    return $countries;
}

#get page
function get_page($title, $col = null)
{
    $page = Page::where('title_en', 'like', '%' . $title . '%')->first();
    if (!isset($page)) return '';
    if (is_null($col)) return $page;
    return $page->$col;
}


/*
|----------------------------------------------------|
|----------------------------------------------------|
|                  Arabic Helper Start               |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#convert from english day to arabic format
function day_to_arabic($day)
{
    $dayArray = [
        'Saturday'  => 'السبت',
        'saturday'  => 'السبت',
        'Sunday'    => 'الاحد',
        'sunday'    => 'الاحد',
        'Monday'    => 'الاثنين',
        'monday'    => 'الاثنين',
        'Tuesday'   => 'الثلاثاء',
        'tuesday'   => 'الثلاثاء',
        'Wednesday' => 'الاربعاء',
        'wednesday' => 'الاربعاء',
        'Thursday'  => 'الخميس',
        'thursday'  => 'الخميس',
        'Friday'    => 'الجمعة',
        'friday'    => 'الجمعة',
    ];

    return isset($dayArray[$day]) ? $dayArray[$day] : '';
}

#convert from english month to arabic format
function month_to_arabic($month)
{
    $lang  = App::getLocale();
    $data = [
        "Jan" => "يناير",
        "Feb" => "فبراير",
        "Mar" => "مارس",
        "Apr" => "أبريل",
        "May" => "مايو",
        "Jun" => "يونيو",
        "Jul" => "يوليو",
        "Aug" => "أغسطس",
        "Sep" => "سبتمبر",
        "Oct" => "أكتوبر",
        "Nov" => "نوفمبر",
        "Dec" => "ديسمبر"
    ];
    return $lang == 'ar' && isset($data[$month]) ? $data[$month] : $month;
}

#convert from english time(a) to arabic format
function time_to_arabic($time)
{
    $lang  = App::getLocale();
    if ($lang == 'ar') $time = $time == 'pm' ? 'مساءا' : 'صباحا';
    return $time;
}


/*
|----------------------------------------------------|
|----------------------------------------------------|
|                  other Helper Start                |
|----------------------------------------------------|
|----------------------------------------------------|
*/

#remove html tags
function fix_text($text)
{
    $text = str_ireplace(array("\r", "\n", "\t"), '', $text);
    $text = str_ireplace(array("&nbsp;", "&hellip;", "&ndash;"), '', $text);
    $text = strip_tags($text);
    $text = stripslashes($text);
    return $text;
}

#get dates range from start_date to end_date
function get_date_range($startDate, $endDate)
{
    $aryRange = array();

    $iDateFrom = mktime(1, 0, 0, substr($startDate, 5, 2),     substr($startDate, 8, 2), substr($startDate, 0, 4));
    $iDateTo = mktime(1, 0, 0, substr($endDate, 5, 2),     substr($endDate, 8, 2), substr($endDate, 0, 4));

    if ($iDateTo >= $iDateFrom) {
        array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
        while ($iDateFrom < $iDateTo) {
            $iDateFrom += 86400; // add 24 hours
            array_push($aryRange, date('Y-m-d', $iDateFrom));
        }
    }
    return $aryRange;
}

#check if value in key,value array
function is_in_array($array, $key, $key_value)
{
    $within_array = false;
    $result = [];
    foreach ($array as $k => $v) {

        if (is_array($v)) {
            $within_array = is_in_array($v, $key, $key_value);
            if ($within_array == true) {
                break;
            }
        } else {
            if ($v == $key_value && $k == $key) {
                $within_array = true;
                break;
            }
        }
    }
    return $within_array;
}

#randome active code
function active_code()
{
    $code = '1234'; //for test work
    // $code = rand(1111, 9999); //for real work
    return $code;
}

#genrate randome number
function generate_number($count)
{
    return str_random((int) $count);
}

#get price
function get_price($section_id = null)
{
    $section = Section::whereId($section_id)->first();
    return isset($section) ? $section->price : 0;
}

#is order rated
function is_rated($order_id = null)
{
    $rate = Rate::where('order_id', $order_id)->first();
    return isset($rate);
}

#is subscripe
function is_subscripe($coach_id = null)
{
    $subscription = Subscription::where('company_id', Auth::id())->where('coach_id', $coach_id)->first();
    return isset($subscription);
}
