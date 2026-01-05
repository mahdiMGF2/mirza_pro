<?php
ini_set('error_log', 'error_log');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jdf.php';
require_once __DIR__ . '/../botapi.php';
require_once __DIR__ . '/../Marzban.php';
require_once __DIR__ . '/../function.php';
require_once __DIR__ . '/../keyboard.php';
require_once __DIR__ . '/../panels.php';
require __DIR__ . '/../vendor/autoload.php';
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

$ManagePanel = new ManagePanel();
$data = json_decode(file_get_contents("php://input"), true);
$hashid = $data['hashid'];
$authority = $data['authority'];
$StatusPayment = $data['status'];
$setting = select("setting", "*");
$PaySetting = select("PaySetting", "*", "NamePay", "marchent_floypay", "select")['ValuePay'];
$Payment_reports = select("Payment_report", "*", "id_order", $hashid, "select");
$invoice_id = $Payment_reports['id_order'];
$price = $Payment_reports['price'];
$datatextbotget = select("textbot", "*", null, null, "fetchAll");
$datatxtbot = array();
foreach ($datatextbotget as $row) {
    $datatxtbot[] = array(
        'id_text' => $row['id_text'],
        'text' => $row['text']
    );
}
$datatextbot = array(
    'textafterpay' => '',
    'textaftertext' => '',
    'textmanual' => '',
    'textselectlocation' => '',
    'textafterpayibsng' => ''
);
foreach ($datatxtbot as $item) {
    if (isset($datatextbot[$item['id_text']])) {
        $datatextbot[$item['id_text']] = $item['text'];
    }
}
// verify Transaction
$dec_payment_status = "";
$payment_status = "";
if ($StatusPayment == 100) {
    $curl = curl_init();
    $data = [
        "ApiKey" => $PaySetting,
        "authority" => $authority,
        "hashid" => $invoice_id,
    ];
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://tetra98.ir/api/verify",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: application/json'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response, true);
    if (!empty($response['status']) && $response['status'] == 100) {
        $payment_status = "پرداخت موفق";
        $dec_payment_status = "از انجام تراکنش متشکریم!";
        $Payment_report = select("Payment_report", "*", "id_order", $invoice_id, "select");
        if ($Payment_report['payment_Status'] != "paid") {
            $textbotlang = languagechange('../text.json');
            DirectPayment($invoice_id, "../images.jpg");
            $pricecashback = select("PaySetting", "ValuePay", "NamePay", "chashbackiranpay1", "select")['ValuePay'];
            $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
            if ($pricecashback != "0") {
                $result = ($Payment_report['price'] * $pricecashback) / 100;
                $Balance_confrim = intval($Balance_id['Balance']) + $result;
                update("user", "Balance", $Balance_confrim, "id", $Balance_id['id']);
                $pricecashback = number_format($pricecashback);
                $text_report = "🎁 کاربر عزیز مبلغ $result تومان به عنوان هدیه واریز به حساب شما واریز گردید.";
                sendmessage($Balance_id['id'], $text_report, null, 'HTML');
            }
            update("Payment_report", "payment_Status", "paid", "id_order", $Payment_report['id_order']);
            $paymentreports = select("topicid", "idreport", "report", "paymentreport", "select")['idreport'];
            $price = number_format($price);
            $text_report = "💵 پرداخت جدید
        
آیدی عددی کاربر : {$Payment_report['id_user']}
نام کاربری کاربر : {$Balance_id['username']}
مبلغ تراکنش $price
روش پرداخت : ارزی ریالی اول";
            if (strlen($setting['Channel_Report']) > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $paymentreports,
                    'text' => $text_report,
                    'parse_mode' => "HTML"
                ]);
            }
        }
    } else {
        $payment_status = "ناموفق";
        $dec_payment_status = "";
    }
}
?>
<html>

<head>
    <title>فاکتور پرداخت</title>
    <style>
        @font-face {
            font-family: 'vazir';
            src: url('/Vazir.eot');
            src: local('☺'), url('../fonts/Vazir.woff') format('woff'), url('../fonts/Vazir.ttf') format('truetype');
        }

        body {
            font-family: vazir;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .confirmation-box {
            background-color: #ffffff;
            border-radius: 8px;
            width: 25%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }

        h1 {
            color: #333333;
            margin-bottom: 20px;
        }

        p {
            color: #666666;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="confirmation-box">
        <h1><?php echo $payment_status ?></h1>
        <p>شماره تراکنش:<span><?php echo $invoice_id ?></span></p>
        <p>مبلغ پرداختی: <span><?php echo $price ?></span>تومان</p>
        <p>تاریخ: <span> <?php echo jdate('Y/m/d') ?> </span></p>
        <p><?php echo $dec_payment_status ?></p>
    </div>
</body>

</html>