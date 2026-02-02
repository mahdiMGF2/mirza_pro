<?php
ini_set('error_log', 'error_log');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jdf.php';
require_once __DIR__ . '/../botapi.php';
require_once __DIR__ . '/../Marzban.php';
require_once __DIR__ . '/../function.php';
require_once __DIR__ . '/../panels.php';
require_once __DIR__ . '/../keyboard.php';
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
$Payment_report = select("Payment_report", "*", "id_order", $data['PaymentID'], "select");
if (!$Payment_report)
    return;
$apitronseller = select("PaySetting", "*", "NamePay", "apiternado", "select")['ValuePay'];
if ($Payment_report['payment_Status'] == "expire")
    return;
$setting = select("setting", "*", null, null, "select");
$price = $Payment_report['price'];
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
if ($Payment_report['payment_Status'] != "paid") {
    $headers = [
        'Content-Type' => "application/json",
        'x-api-key' => $apitronseller
    ];
    $req = new CurlRequest("https://bot.tronado.cloud/Order/GetStatus");
    $req->setHeaders($headers);
    $order_id = explode('TrndOrderID_', $data['Hash'])[1];
    $response = $req->post(array('id' => $order_id));
    $response = is_string($response['body']) ? json_decode($response['body'], true) : false;
    if ($response && $response['IsPaid'] && $data['IsPaid'] && $data['TronAmount'] == $response['TronAmount']) {
        echo json_encode(array("status" => true));
        $textbotlang = languagechange('../text.json');
        DirectPayment($data['PaymentID'], "../images.jpg");
        $pricecashback = select("PaySetting", "ValuePay", "NamePay", "chashbackiranpay2", "select")['ValuePay'];
        $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
        if ($pricecashback != "0") {
            $result = ($Payment_report['price'] * $pricecashback) / 100;
            $Balance_confrim = intval($Balance_id['Balance']) + $result;
            update("user", "Balance", $Balance_confrim, "id", $Balance_id['id']);
            $pricecashback = number_format($pricecashback);
            $text_report = "🎁 کاربر عزیز مبلغ $result تومان به عنوان هدیه واریز به حساب شما واریز گردید.";
            sendmessage($Balance_id['id'], $text_report, null, 'HTML');
        }
        $paymentreports = select("topicid", "idreport", "report", "paymentreport", "select")['idreport'];
        $balancelow = "";
        if ($data['TronAmount'] < $data['ActualTronAmount']) {
            $balancelow = "❌ کاربر کمتر از مبلغ تعیین شده واریز کرده است.";
        }
        $text_reportpayment = "💵 پرداخت جدید
$balancelow
- 👤 نام کاربری کاربر : @{$Balance_id['username']}
- 🆔آیدی عددی کاربر : {$Balance_id['id']}
- 💸 مبلغ تراکنش $price
- 🔗 <a href = \"https://tronscan.org/#/transaction/{$data['Hash']}\">لینک پرداخت </a>
- 📥 مبلغ واریز شده ترون. : {$data['TronAmount']}
- 💳 روش پرداخت :  ترونادو";
        $Status_change = "paid";
        $statement = $pdo->prepare("UPDATE Payment_report SET payment_Status = :payment_Status WHERE id_order = :id_order");
        $statement->bindValue(':payment_Status', $Status_change);
        $statement->bindValue(':id_order', $Payment_report['id_order']);
        $statement->execute();
        $database = json_encode($data);
        $statement = $pdo->prepare("UPDATE Payment_report SET dec_not_confirmed = :dec_not_confirmed WHERE id_order = :id_order");
        $statement->bindValue(':dec_not_confirmed', $database);
        $statement->bindValue(':id_order', $Payment_report['id_order']);
        $statement->execute();
        if (strlen($setting['Channel_Report']) > 0) {
            telegram('sendmessage', [
                'chat_id' => $setting['Channel_Report'],
                'message_thread_id' => $paymentreports,
                'text' => $text_reportpayment,
                'parse_mode' => "HTML"
            ]);
        }
    }
}