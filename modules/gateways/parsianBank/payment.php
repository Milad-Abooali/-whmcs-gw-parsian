<?php
/**
 **************************************************************************
 * Parsian Bank Gateway
 * parsianBank/payment.php
 * Send Request & Callback
 * @author           Milad Abooali <m.abooali@hotmail.com>
 * @version          1.0
 **************************************************************************
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedFunctionInspection
 * @noinspection SpellCheckingInspection
 * @noinspection PhpIncludeInspection
 * @noinspection PhpDeprecationInspection
 * @noinspection PhpUndefinedMethodInspection
 * @noinspection PhpUnhandledExceptionInspection
 **************************************************************************
 */

global $CONFIG;
$cb_output = [$_POST,$_GET];
$cb_gw_name = 'parsianBank';
$action = isset($_GET['a']) ? $_GET['a'] : false;
$root_path     = '../../../';
$includes_path = '../../../includes/';
include($root_path.((file_exists($root_path.'init.php'))?'init.php':'dbconnect.php'));
include($includes_path.'functions.php');
include($includes_path.'gatewayfunctions.php');
include($includes_path.'invoicefunctions.php');
$modules    = getGatewayVariables($cb_gw_name);
if (!$modules['type']) die('Module Not Activated');
$amount 			= intval($_REQUEST['amount']);
$invoice_id 	    = $_REQUEST['invoiceid'];
$gw_id          	= $modules['cb_gw_id'];

/**
 * Error Codes Translator
 * @param string $ResCode
 * @return string
 */
function errorCodeT($ResCode='') {
    switch($ResCode){
        case '-32768':
            $response = 'خطای ناشناخته رخ داده است';
            break;
        case '-1552':
            $response = 'برگشت تراکنش مجاز نمی باشد';
            break;
        case '-1551':
            $response = 'برگشت تراکنش قبلاً انجام شده است';
            break;
        case '-1550':
            $response = 'برگشت تراکنش در وضعیت جاری امکان پذیر نمی باشد';
            break;
        case '-1549':
            $response = 'زمان مجاز برای درخواست برگشت تراکنش به اتمام رسیده است';
            break;
        case '-1548':
            $response = 'فراخوانی سرویس درخواست پرداخت قبض ناموفق بود';
            break;
        case '-1540':
            $response = 'تاييد تراکنش ناموفق مي باشد';
            break;
        case '-1536':
            $response = 'فراخوانی سرویس درخواست شارژ تاپ آپ ناموفق بود';
            break;
        case '-1533':
            $response = 'تراکنش قبلاً تایید شده است';
            break;
        case '1532':
            $response = 'تراکنش از سوی پذیرنده تایید شد';
            break;
        case '-1531':
            $response = 'تراکنش به دلیل انصراف شما در بانک ناموفق بود';
            break;
        case '-1530':
            $response = 'پذیرنده مجاز به تایید این تراکنش نمی باشد';
            break;
        case '-1528':
            $response = 'اطلاعات پرداخت یافت نشد';
            break;
        case '-1527':
            $response = 'انجام عملیات درخواست پرداخت تراکنش خرید ناموفق بود';
            break;
        case '-1507':
            $response = 'تراکنش برگشت به سوئیچ ارسال شد';
            break;
        case '-1505':
            $response = 'تایید تراکنش توسط پذیرنده انجام شد';
            break;
        case '-132':
            $response = 'مبلغ تراکنش کمتر از حداقل مجاز می باشد';
            break;
        case '-131':
            $response = 'Token نامعتبر می باشد';
            break;
        case '-130':
            $response = 'Token زمان منقضی شده است';
            break;
        case '-128':
            $response = 'قالب آدرس IP معتبر نمی باشد';
            break;
        case '-127':
            $response = 'آدرس اینترنتی معتبر نمی باشد';
            break;
        case '-126':
            $response = 'کد شناسایی پذیرنده معتبر نمی باشد';
            break;
        case '-121':
            $response = 'رشته داده شده بطور کامل عددی نمی باشد';
            break;
        case '-120':
            $response = 'طول داده ورودی معتبر نمی باشد';
            break;
        case '-119':
            $response = 'سازمان نامعتبر می باشد';
            break;
        case '-118':
            $response = 'مقدار ارسال شده عدد نمی باشد';
            break;
        case '-117':
            $response = 'طول رشته کم تر از حد مجاز می باشد';
            break;
        case '-116':
            $response = 'طول رشته بیش از حد مجاز می باشد';
            break;
        case '-115':
            $response = 'شناسه پرداخت نامعتبر می باشد';
            break;
        case '-114':
            $response = 'شناسه قبض نامعتبر می باشد';
            break;
        case '-113':
            $response = 'پارامتر ورودی خالی می باشد';
            break;
        case '-112':
            $response = 'شماره سفارش تکراری است';
            break;
        case '-111':
            $response = 'مبلغ تراکنش بیش از حد مجاز پذیرنده می باشد';
            break;
        case '-108':
            $response = 'قابلیت برگشت تراکنش برای پذیرنده غیر فعال می باشد';
            break;
        case '-107':
            $response = 'قابلیت ارسال تاییده تراکنش برای پذیرنده غیر فعال می باشد';
            break;
        case '-106':
            $response = 'قابلیت شارژ برای پذیرنده غیر فعال می باشد';
            break;
        case '-105':
            $response = 'قابلیت تاپ آپ برای پذیرنده غیر فعال می باشد';
            break;
        case '-104':
            $response = 'قابلیت پرداخت قبض برای پذیرنده غیر فعال می باشد';
            break;
        case '-103':
            $response = 'قابلیت خرید برای پذیرنده غیر فعال می باشد';
            break;
        case '-102':
            $response = 'تراکنش با موفقیت برگشت داده شد';
            break;
        case '-101':
            $response = 'پذیرنده اهراز هویت نشد';
            break;
        case '-100':
            $response = 'پذیرنده غیرفعال می باشد';
            break;
        case '-1':
            $response = 'خطای سرور';
            break;
        case '0':
            $response = 'عملیات موفق می باشد';
            break;
        case '1':
            $response = 'صادرکننده ی کارت از انجام تراکنش صرف نظر کرد';
            break;
        case '2':
            $response = 'عملیات تاییدیه این تراکنش قبلا باموفقیت صورت پذیرفته است';
            break;
        case '3':
            $response = 'پذیرنده ی فروشگاهی نامعتبر می باشد';
            break;
        case '5':
            $response = 'از انجام تراکنش صرف نظر شد';
            break;
        case '6':
            $response = 'بروز خطايي ناشناخته';
            break;
        case '8':
            $response = 'باتشخیص هویت دارنده ی کارت، تراکنش موفق می باشد';
            break;
        case '9':
            $response = 'درخواست رسيده در حال پي گيري و انجام است ';
            break;
        case '10':
            $response = 'تراکنش با مبلغي پايين تر از مبلغ درخواستي ( کمبود حساب مشتري ) پذيرفته شده است ';
            break;
        case '12':
            $response = 'تراکنش نامعتبر است';
            break;
        case '13':
            $response = 'مبلغ تراکنش نادرست است';
            break;
        case '14':
            $response = 'شماره کارت ارسالی نامعتبر است (وجود ندارد)';
            break;
        case '15':
            $response = 'صادرکننده ی کارت نامعتبراست (وجود ندارد)';
            break;
        case '17':
            $response = 'مشتري درخواست کننده حذف شده است ';
            break;
        case '20':
            $response = 'در موقعيتي که سوئيچ جهت پذيرش تراکنش نيازمند پرس و جو از کارت است ممکن است درخواست از کارت ( ترمينال) بنمايد اين پيام مبين نامعتبر بودن جواب است';
            break;
        case '21':
            $response = 'در صورتي که پاسخ به در خواست ترمينا ل نيازمند هيچ پاسخ خاص يا عملکردي نباشيم اين پيام را خواهيم داشت ';
            break;
        case '22':
            $response = 'تراکنش مشکوک به بد عمل کردن ( کارت ، ترمينال ، دارنده کارت ) بوده است لذا پذيرفته نشده است';
            break;
        case '30':
            $response = 'قالب پیام دارای اشکال است';
            break;
        case '31':
            $response = 'پذیرنده توسط سوئی پشتیبانی نمی شود';
            break;
        case '32':
            $response = 'تراکنش به صورت غير قطعي کامل شده است. به عنوان مثال تراکنش سپرده گزاري که از ديد مشتري کامل شده است ولي مي بايست تکميل گردد.';
            break;
        case '54':
        case '33':
            $response = 'تاریخ انقضای کارت سپری شده است';
            break;
        case '38':
            $response = 'تعداد دفعات ورود رمزغلط بیش از حدمجاز است. کارت توسط دستگاه ضبط شود';
            break;
        case '39':
            $response = 'کارت حساب اعتباری ندارد';
            break;
        case '40':
            $response = 'عملیات درخواستی پشتیبانی نمی گردد';
            break;
        case '41':
            $response = 'کارت مفقودی می باشد';
            break;
        case '43':
            $response = 'کارت مسروقه می باشد';
            break;
        case '45':
            $response = 'قبض قابل پرداخت نمی باشد';
            break;
        case '51':
            $response = 'موجودی کافی نمی باشد';
            break;
        case '55':
            $response = 'رمز کارت نا معتبر است';
            break;
        case '56':
            $response = 'کارت نا معتبر است';
            break;
        case '57':
            $response = 'انجام تراکنش مربوطه توسط دارنده ی کارت مجاز نمی باشد';
            break;
        case '58':
            $response = 'انجام تراکنش مربوطه توسط پایانه ی انجام دهنده مجاز نمی باشد';
            break;
        case '59':
            $response = 'کارت مظنون به تقلب است';
            break;
        case '61':
            $response = 'مبلغ تراکنش بیش از حد مجاز می باشد';
            break;
        case '62':
            $response = 'کارت محدود شده است';
            break;
        case '63':
            $response = 'تمهیدات امنیتی نقض گردیده است';
            break;
        case '65':
            $response = 'تعداد درخواست تراکنش بیش از حد مجاز می باشد';
            break;
        case '68':
            $response = 'پاسخ لازم براي تکميل يا انجام تراکنش خيلي دير رسيده است';
            break;
        case '69':
            $response = 'تعداد دفعات تکرار رمز از حد مجاز گذشته است ';
            break;
        case '75':
            $response = 'تعداد دفعات ورود رمزغلط بیش از حدمجاز است';
            break;
        case '78':
            $response = 'کارت فعال نیست';
            break;
        case '79':
            $response = 'حساب متصل به کارت نا معتبر است یا دارای اشکال است';
            break;
        case '80':
            $response = 'درخواست تراکنش رد شده است';
            break;
        case '81':
            $response = 'کارت پذيرفته نشد';
            break;
        case '83':
            $response = 'سرويس دهنده سوئيچ کارت تراکنش را نپذيرفته است';
            break;
        case '84':
            $response = 'در تراکنشهايي که انجام آن مستلزم ارتباط با صادر کننده است در صورت فعال نبودن صادر کننده اين پيام در پاسخ ارسال خواهد شد ';
            break;
        case '91':
            $response = 'سيستم صدور مجوز انجام تراکنش موقتا غير فعال است و يا  زمان تعيين شده براي صدور مجوز به پايان رسيده است';
            break;
        case '92':
            $response = 'مقصد تراکنش پيدا نشد';
            break;
        case '93':
            $response = 'امکان تکميل تراکنش وجود ندارد';
            break;
        default:
            $response = 'پرداخت تراکنش به دلیل انصراف در صفحه بانک ناموفق بود';
            break;
    }
    return $response;
}

/**
 * Telegram Notify
 * @param $notify
 */
function notifyTelegram($notify) {
    global $modules;
    $row = "------------------";
    $pm= "\n".$row.$row.$row."\n".$notify['title']."\n".$row."\n".$notify['text'];
    $chat_id = $modules['cb_telegram_chatid'];
    $botToken = $modules['cb_telegram_bot']; // "291958747:AAF65_lFLaap35HS5zYxSbO1ycNb8Pl2vTk";
    $data = ['chat_id' => $chat_id, 'text' => $pm];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot$botToken/sendMessage");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_exec($curl);
    curl_close($curl);
}

/**
 * Email Notify
 * @param $notify
 */
function notifyEmail($notify) {
    global $modules;
    global $cb_output;
    $receivers = explode(',', $modules['cb_email_address']);
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/plain;charset=UTF-8" . "\r\n";
    $headers .= "From: ".$modules['cb_email_from']."\r\n";
    if($receivers) foreach ($receivers as $receiver){
        $cb_output['mail'][] = mail($receiver, $notify['title'], $notify['text'], $headers);
    }
}


if($action==='callback') {
    $tran_id  = $order_id  = $invoice_id;
    $ref_code = $_POST['SaleReferenceId'];
    if(!empty($invoice_id)) {
        $cb_output['invoice_id'] = $invoice_id;
        if(!empty($order_id) && !empty($tran_id) && !empty($ref_code)) {
            $cb_output['tran_id'] = $tran_id;
            $invoice_id = checkCbInvoiceID($invoice_id, $modules['name']);
            $results = select_query("tblinvoices", "", array("id" => $invoice_id));
            $data = mysql_fetch_array($results);
            $db_amount = strtok($data['total'], '.');
            if ($_POST['ResCode'] == '0') {
                include_once('nusoap.php');
                $client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
                $namespace='http://interfaces.core.sw.bps.com/';
                $parameters = array(
                    'terminalId' 		=> $modules['cb_gw_terminal_id'],
                    'userName' 			=> $modules['cb_gw_user'],
                    'userPassword' 		=> $modules['cb_gw_pass'],
                    'orderId'           => $_POST['SaleOrderId'],
                    'saleOrderId'       => $_POST['SaleOrderId'],
                    'saleReferenceId'   => $_POST['SaleReferenceId']
                );
                $cb_output['res']['result'] = $bpVerifyRequest = $client->call('bpVerifyRequest', $parameters, $namespace);
                if($bpVerifyRequest == 0) {
                    $bpSettleRequest = $client->call('bpSettleRequest', $parameters, $namespace);
                    if($bpSettleRequest == 0) {
                        $cartNumber = $_POST['CardHolderPan'];
                        addInvoicePayment($invoice_id, $ref_code, $amount, 0, $cb_gw_name);
                        logTransaction($modules["name"], array('invoiceid' => $invoice_id,'order_id' => $order_id,'amount' => $amount." ".(($modules['cb_gw_unit']>1) ? 'Toman' : 'Rial'),'tran_id' => $tran_id,'RefId' => $_POST['RefId'],'SaleReferenceId' => $ref_code,'CardNumber' => $cartNumber,'status' => "OK"), "موفق");
                        $notify['title'] = $cb_gw_name.' | '."تراکنش موفق";
                        $notify['text']  = "\n\rGateway: $cb_gw_name\n\rAmount: $amount ".(($modules['cb_gw_unit']>1) ? 'Toman' : 'Rial')."\n\rOrder: $order_id\n\rInvoice: $invoice_id\n\rCart Number: $cartNumber";
                        if($modules['cb_email_on_success']) notifyEmail($notify);
                        if($modules['cb_telegram_on_success']) notifyTelegram($notify);
                    }
                    else {
                        $client->call('bpReversalRequest', $parameters, $namespace);
                        logTransaction($modules["name"], array('invoiceid' => $invoice_id,'order_id' => $order_id,'amount' => $amount." ".(($modules['cb_gw_unit']>1) ? 'Toman' : 'Rial'),'tran_id' => $tran_id, 'status' => $bpSettleRequest), "ناموفق");
                        $notify['title'] = $cb_gw_name.' | '."تراکنش ناموفق";
                        $notify['text']  = "\n\rGateway: $cb_gw_name\n\rAmount: $amount ".(($modules['cb_gw_unit']>1) ? 'Toman' : 'Rial')."\n\rOrder: $order_id\n\rInvoice: $invoice_id\n\rError: به دلیل رخ دادن خطا در پرداخت، درخواست بازگشت وجه داده شد.";
                        if($modules['cb_email_on_error']) notifyEmail($notify);
                        if($modules['cb_telegram_on_error']) notifyTelegram($notify);
                    }
                }
                else {
                    $client->call('bpReversalRequest', $parameters, $namespace);
                    logTransaction($modules["name"], array('invoiceid' => $invoice_id,'order_id' => $order_id,'amount' => $amount." ".(($modules['cb_gw_unit']>1) ? 'Toman' : 'Rial'),'tran_id' => $tran_id, 'status' => $bpVerifyRequest), "ناموفق");
                    $notify['title'] = $cb_gw_name.' | '."تراکنش ناموفق";
                    $notify['text']  = "\n\rGateway: $cb_gw_name\n\rAmount: $amount ".(($modules['cb_gw_unit']>1) ? 'Toman' : 'Rial')."\n\rOrder: $order_id\n\rInvoice: $invoice_id\n\rError: به دلیل رخ دادن خطا در پرداخت، درخواست بازگشت وجه داده شد.";
                    if($modules['cb_email_on_error']) notifyEmail($notify);
                    if($modules['cb_telegram_on_error']) notifyTelegram($notify);
                }
            } else {
                logTransaction($modules["name"], array('invoiceid' => $invoice_id,'order_id' => $order_id,'amount' => $amount." ".(($modules['cb_gw_unit']>1) ? 'Toman' : 'Rial'),'tran_id' => $tran_id, 'status' => $_POST['ResCode']), "ناموفق");
                $notify['title'] = $cb_gw_name.' | '."تراکنش ناموفق";
                $notify['text']  = "\n\rGateway: $cb_gw_name\n\rAmount: $amount ".(($modules['cb_gw_unit']>1) ? 'Toman' : 'Rial')."\n\rOrder: $order_id\n\rInvoice: $invoice_id\n\rError: به دلیل رخ دادن خطا در پرداخت، درخواست بازگشت وجه داده شد.";
                if($modules['cb_email_on_error']) notifyEmail($notify);
                if($modules['cb_telegram_on_error']) notifyTelegram($notify);
            }


        }
        $action = $CONFIG['SystemURL'] . "/viewinvoice.php?id=" . $invoice_id;
        header('Location: ' . $action);
        //print("<pre>".print_r($cb_output,true)."</pre>");
    }
    else {
        echo "invoice id is blank";
    }
}
else if($action==='send') {
    $callback_URL   = $CONFIG['SystemURL']."/modules/gateways/$cb_gw_name/payment.php?a=callback&invoiceid=". $invoice_id.'&amount='.$amount;
    $parameters = array(
        'LoginAccount'		=> $modules['cb_gw_pass'],
        'Amount' 			=> $amount,
        'OrderId' 			=> $invoice_id.mt_rand(10, 100),
        'CallBackUrl' 		=> $callback_URL,
        'AdditionalData' 	=> ''
    );
    if(extension_loaded('soap')){
        try {
            $client	= new SoapClient('https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL',array('soap_version'=>'SOAP_1_1','cache_wsdl'=>WSDL_CACHE_NONE  ,'encoding'=>'UTF-8'));
            $result	= $client->SalePaymentRequest(array("requestData" => $parameters));
            $Request = array(
                'Status'	=>	$result->SalePaymentRequestResult->Status,
                'Token'		=>	$result->SalePaymentRequestResult->Token
            );
        }
        catch(Exception $e){
            $Request = array('Status' =>	'-1','Token' =>	'');
        }
    }
    else{
        $Request = array('Status' =>	'-2','Token' =>	'');
    }
    $Request = (object)$Request;
    if($Request->Status == 0 && $Request->Token > 0){
        redirect();
        echo "<img src='/modules/gateways/$cb_gw_name/logo.png' alt='$cb_gw_name'>
        <script type='text/javascript'>window.location.assign('https://pec.shaparak.ir/NewIPG/?Token=".$Request->Token."')</script>";
        exit;
    }
    else{
        echo '<!DOCTYPE html> 
            <html xmlns="http://www.w3.org/1999/xhtml" lang="fa"><head><title>خطا در ارسال به بانک</title>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <style>body{font-family:tahoma;text-align:center;margin-top:30px;}</style></head><body>
            <div align="center" dir="rtl" style="font-family:tahoma;font-size:12px;border:1px dotted #c3c3c3; width:60%; margin: 50px auto 0px auto;line-height: 25px;padding-left: 12px;padding-top: 8px;">
            <span style="color:#ff0000;"><b>خطا در ارسال به بانک</b></span><br/>
            <p style="text-align:center;">'.PecStatus($Request->Status).' (کد خطا : '.$Request->Status.') ؛ در صورت نیاز با پشتیبانی تماس بگیرید</p>
            <a href="'.$CONFIG['SystemURL'].'/viewinvoice.php?id='.$invoice_id.'">بازگشت</a>
            </div></body></html>';
    }
}