<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

$amount = isset($_GET['amount']) ? (float) $_GET['amount'] : 0;
$student_id = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
$qrMode = strtolower(trim($_GET['qr_mode'] ?? 'static'));
$useDynamicAmount = in_array($qrMode, ['dynamic', 'amount'], true);
$currencyInput = strtoupper(trim($_GET['currency'] ?? 'USD'));
$currency = $currencyInput === 'KHR' ? KHQRData::CURRENCY_KHR : KHQRData::CURRENCY_USD;
$bakongAccountId = trim(getenv('BAKONG_ACCOUNT_ID') ?: 'khim_reaksmey@bkrt');
$merchantName = trim(getenv('BAKONG_MERCHANT_NAME') ?: 'RUPP Pay');
$merchantCity = trim(getenv('BAKONG_MERCHANT_CITY') ?: 'PHNOM PENH');

if ($student_id <= 0 || $amount <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid student or amount.'
    ]);
    exit();
}

$billNumber = 'STU-' . $student_id . '-' . time();
$qrAmount = $useDynamicAmount ? $amount : 0.0;

try {
    $individualInfo = new IndividualInfo(
        bakongAccountID: $bakongAccountId,
        merchantName: $merchantName,
        merchantCity: $merchantCity,
        currency: $currency,
        amount: round($qrAmount, 2),
        billNumber: $billNumber,
        storeLabel: 'RUPP',
        terminalLabel: 'POS-001'
    );

    $khqrResponse = BakongKHQR::generateIndividual($individualInfo);
    $status = $khqrResponse->status ?? [];
    $data = $khqrResponse->data ?? [];

    if (($status['code'] ?? 1) !== 0 || empty($data['qr'])) {
        throw new RuntimeException($status['message'] ?? 'Failed to generate KHQR string.');
    }

    $qrString = $data['qr'];
    $md5 = $data['md5'] ?? md5($qrString);

    $writer = new SvgWriter();
    $qrCode = new QrCode(
        data: $qrString,
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::Low,
        size: 260,
        margin: 10,
        roundBlockSizeMode: RoundBlockSizeMode::None
    );

    $result = $writer->write($qrCode);

    echo json_encode([
        'success' => true,
        'fallback' => false,
        'message' => 'Bakong QR generated successfully.',
        'qr_string' => $qrString,
        'qr_image' => $result->getDataUri(),
        'bill_no' => $billNumber,
        'md5' => $md5,
        'qr_mode' => $useDynamicAmount ? 'dynamic' : 'static',
        'currency' => $currencyInput,
        'merchant_name' => $merchantName,
        'merchant_account' => $bakongAccountId,
        'merchant_city' => $merchantCity,
        'amount' => round($qrAmount, 2)
    ]);
} catch (Throwable $error) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate Bakong QR.',
        'detail' => $error->getMessage()
    ]);
}
