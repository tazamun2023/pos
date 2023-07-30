<?php

/**
 * boots pos.
 */
function pos_boot($ul, $pt, $lc, $em, $un, $type = 1, $pid = null)
{
    $ch = curl_init();
    $request_url = ($type == 1) ? base64_decode(config('author.lic1')) : base64_decode(config('author.lic2'));

    $pid = is_null($pid) ? config('author.pid') : $pid;

    $curlConfig = [CURLOPT_URL => $request_url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS => [
            'url' => $ul,
            'path' => $pt,
            'license_code' => $lc,
            'email' => $em,
            'username' => $un,
            'product_id' => $pid,
        ],
    ];
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = 'C'.'U'.'RL '.'E'.'rro'.'r: ';
        $error_msg .= curl_errno($ch);

        return redirect()->back()
            ->with('error', $error_msg);
    }
    curl_close($ch);

    if ($result) {
        $result = json_decode($result, true);

        if ($result['flag'] == 'valid') {
            // if(!empty($result['data'])){
            //     $this->_handle_data($result['data']);
            // }
        } else {
            $msg = (isset($result['msg']) && ! empty($result['msg'])) ? $result['msg'] : 'I'.'nvali'.'d '.'Lic'.'ense Det'.'ails';

            return redirect()->back()
                ->with('error', $msg);
        }
    }
}

if (! function_exists('humanFilesize')) {
    function humanFilesize($size, $precision = 2)
    {
        $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $step = 1024;
        $i = 0;

        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }

        return round($size, $precision).$units[$i];
    }
}

/**
 * Checks if the uploaded document is an image
 */
if (! function_exists('isFileImage')) {
    function isFileImage($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $array = ['png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF'];
        $output = in_array($ext, $array) ? true : false;

        return $output;
    }
}

function isAppInstalled()
{
    $envPath = base_path('.env');

    return file_exists($envPath);
}

/**
 * Checks if pusher has credential or not
 *
 * and return boolean
 */
function isPusherEnabled()
{
    $is_pusher_enabled = false;

    if (! empty(config('broadcasting.connections.pusher.key')) && ! empty(config('broadcasting.connections.pusher.secret')) && ! empty(config('broadcasting.connections.pusher.app_id')) && ! empty(config('broadcasting.connections.pusher.options.cluster')) && (config('broadcasting.connections.pusher.driver') == 'pusher')) {
        $is_pusher_enabled = true;
    }

    return $is_pusher_enabled;
}

/**
 * Checks if user agent is mobile or not
 *
 * @return bool
 */
if (! function_exists('isMobile')) {
    function isMobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
            return true;
        } else {
            return false;
        }
    }
}

if (! function_exists('str_ordinal')) {
    /**
     * Append an ordinal indicator to a numeric value.
     *
     * @param  string|int  $value
     * @param  bool  $superscript
     * @return string
     */
    function str_ordinal($value, $superscript = false)
    {
        $number = abs($value);

        $indicators = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        $suffix = $superscript ? '<sup>'.$indicators[$number % 10].'</sup>' : $indicators[$number % 10];
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            $suffix = $superscript ? '<sup>th</sup>' : 'th';
        }

        return number_format($number).$suffix;
    }

    function OurSMS($toNumber, $body) {
        $username = 'eis1980';
        $token = 'GHK7L6UrRHs6yGyKSAu5';
        $src = 'MHAM';
        $dests = $toNumber;
        $body = $body;
        $priority = 1;
        $delay = 0;
        $validity = 0;
        $maxParts = 0;
        $dlr = 0;
        $prevDups = 0;
        $msgClass = 'promotional';

        $data = array(
            'username' => $username,
            'token' => $token,
            'src' => $src,
            'dests' => $dests,
            'body' => $body,
            'priority' => $priority,
            'delay' => $delay,
            'validity' => $validity,
            'maxParts' => $maxParts,
            'dlr' => $dlr,
            'prevDups' => $prevDups,
            'msgClass' => $msgClass
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.oursms.com/api-a/msgs");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        curl_close($ch);

        return $server_output === "OK";
    }


    function validate_mobile($mobile)
    {
        return preg_match('/^((?:[+?0?0?966]+)(?:\s?\d{2})(?:\s?\d{7}))$/', $mobile);
    }

    function checkoutTamara($package=null, $business_id=null, $user=null, $amount=null, $mobile= null)
    {
//        dd($package);
//dd($amount);
        $url = "https://api.tamara.co/checkout";
        $order_number = '123456789';
        $price = (double)$amount;
//        $user_name = $user['surname'].' '.$user['first_name']??$user['first_name'].' '.$user['last_name']??$user['last_name'];
        $user_email = $user->email;
        $user_first_name = $user->first_name??$user->first_name;
        $user_last_name = $user->last_name??$user->last_name;
//        $package_name = $package['name'];

        $customer_mobile = $mobile;


//dd($customer_mobile);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Accept: application/json",
            "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhY2NvdW50SWQiOiJmNGY0MzI5ZS1iN2RiLTQ2MWMtYTIwZi1jMWE2ZThiM2Y2ZTQiLCJ0eXBlIjoibWVyY2hhbnQiLCJzYWx0IjoiMDk3NDEwNjVjZTg1NzY1ZDY3ZTlkMWU2Y2IzYzVhYWYiLCJpYXQiOjE2NjgxNzIyNDEsImlzcyI6IlRhbWFyYSBQUCJ9.VmOzv2dlwUTOSx6VvBS1D7mvFVTNl668NBwu0ax591YNYKQpS6WCAXEg870JbRUud47gC-oSMYSC5bhCPXLD--s4Vdi6TiEzLEK-gH5IcIRf-LEjVxwh-BP-YZCFxpRInPrQ1SwhmStzk_SVxDlzwSswKom5qkCHwH4yle0CV1ai3-UYqupQYf8L07sPl_mAVw47Rj7VLulF4YnpSt3W6HoNss1IogOVpFr8TJimgqZH3jGzfgh2px-qfN2DeXqS4mUnCXu439RQUUIsF11OIcj5JQupTgdk0SWmBPDtV3Mt97e414cUPkocn7mwFa3axGJeiCZO-nMtyaAGbm0P4g",
            "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = <<<DATA
{
  "order_reference_id": "$order_number",
  "order_number": "$order_number",
  "total_amount": {
    "amount": $price,
    "currency": "SAR"
  },
  "description": "string",
  "country_code": "SA",
  "payment_type": "PAY_BY_INSTALMENTS",
  "instalments": null,
  "locale": "en_US",
  "items": [
    {
      "reference_id": "123456",
      "type": "Digital",
      "name": "Lego City 8601",
      "sku": "SA-12436",
      "image_url": "https://www.example.com/product.jpg",
      "quantity": 1,
      "unit_price": {
        "amount": "$price",
        "currency": "SAR"
      },
      "discount_amount": {
        "amount": "0.00",
        "currency": "SAR"
      },
      "tax_amount": {
        "amount": "0.00",
        "currency": "SAR"
      },
      "total_amount": {
        "amount": "$price",
        "currency": "SAR"
      }
    }
  ],
  "consumer": {
    "first_name": "$user_first_name",
    "last_name": "$user_last_name",
    "phone_number": "$customer_mobile",
    "email": "$user_email"
  },
  "billing_address": {
    "first_name": "$user_first_name",
    "last_name": "$user_last_name",
    "line1": "billing_address",
    "line2": "string",
    "region": "Riyadh",
    "postal_code": "12211",
    "city": "Riyadh",
    "country_code": "SA",
    "phone_number": "$customer_mobile"
  },
  "shipping_address": {
    "first_name": "$user_first_name",
    "last_name": "$user_last_name",
    "line1": "Riyadh",
    "line2": "Riyadh",
    "region": "Riyadh",
    "postal_code": "12211",
    "city": "Riyadh",
    "country_code": "SA",
    "phone_number": "$customer_mobile"
  },
  "discount": {
    "name": "NO_DISCOUNT",
    "amount": {
      "amount": "0.00",
      "currency": "SAR"
    }
  },
  "tax_amount": {
    "amount": "0.00",
    "currency": "SAR"
  },
  "shipping_amount": {
    "amount": "0.00",
    "currency": "SAR"
  },
  "merchant_url": {
   "success": "http://accounts-erp.test/checkout/success",
    "failure": "http://accounts-erp.test/checkout/failure",
    "cancel": "http://accounts-erp.test/checkout/cancel",
    "notification": "http://accounts-erp.test/payments/tamarapay"
  },
  "platform": "Magento",
  "is_mobile": false,
  "risk_assessment": {
    "customer_age": 22,
    "customer_dob": "31-01-2000",
    "customer_gender": "Male",
    "customer_nationality": "SA",
    "is_premium_customer": true,
    "is_existing_customer": true,
    "is_guest_user": true,
    "account_creation_date": "31-01-2019",
    "platform_account_creation_date": "string",
    "date_of_first_transaction": "31-01-2019",
    "is_card_on_file": true,
    "is_COD_customer": true,
    "has_delivered_order": true,
    "is_phone_verified": true,
    "is_fraudulent_customer": true,
    "total_ltv": 501.5,
    "total_order_count": 12,
    "order_amount_last3months": 301.5,
    "order_count_last3months": 2,
    "last_order_date": "31-01-2021",
    "last_order_amount": 301.5,
    "reward_program_enrolled": true,
    "reward_program_points": 300
  },
  "expires_in_minutes": 0,
  "additional_data": {
    "delivery_method": "home delivery",
    "pickup_store": "Store A",
    "store_code": "Store code A",
    "vendor_amount": 0,
    "merchant_settlement_amount": 0,
    "vendor_reference_code": "string"
  }
}
DATA;
//dd($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($curll_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
//        dd($resp);
        curl_close($curl);

        return json_decode($resp, true);

    }
}
