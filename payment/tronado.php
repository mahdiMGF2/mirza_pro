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
$data = json_decode(file_get_contents("php://input"),true);
$Payment_report = select("Payment_report", "*", "id_order", $data['PaymentID'], "fetch");
if($Payment_report['payment_Status'] == "expire")return;
$setting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM setting"));
$price = $Payment_report['price'];
    $datatextbotget = select("textbot", "*",null ,null ,"fetchAll");
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
    if($Payment_report['payment_Status'] != "paid"){
        if($data['IsPaid']){
            echo "پرداخت با موفقیت انجام  شد";
    $textbotlang = languagechange('../text.json');
    DirectPayment($data['PaymentID'],"../images.jpg");
    $pricecashback = select("PaySetting", "ValuePay", "NamePay", "chashbackiranpay2","select")['ValuePay'];
    $Balance_id = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '{$Payment_report['id_user']}' LIMIT 1"));
    if($pricecashback != "0"){
       $result = ($Payment_report['price'] * $pricecashback) / 100;
        $Balance_confrim = intval($Balance_id['Balance']) +$result ;
        update("user","Balance",$Balance_confrim, "id",$Balance_id['id']); 
        $pricecashback =  number_format($pricecashback);
        $text_report = "🎁 کاربر عزیز مبلغ $result تومان به عنوان هدیه واریز به حساب شما واریز گردید.";
        sendmessage($Balance_id['id'], $text_report, null, 'HTML');
    }
    $paymentreports = select("topicid","idreport","report","paymentreport","select")['idreport'];
    if($data['TronAmount'] < $data['ActualTronAmount']){
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
    $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
    $Status_change = "paid";
    $stmt->bind_param("ss", $Status_change, $Payment_report['id_order']);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE Payment_report SET dec_not_confirmed = ? WHERE id_order = ?");
    $database = json_encode($data);
    $stmt->bind_param("ss", $database, $Payment_report['id_order']);
    $stmt->execute();
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage',[
        'chat_id' => $setting['Channel_Report'],
        'message_thread_id' => $paymentreports,
        'text' => $text_reportpayment,
        'parse_mode' => "HTML"
        ]);
    }
        }
    }