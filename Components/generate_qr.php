<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

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

if ($student_id <= 0 || $amount <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid student or amount.'
    ]);
    exit();
}

$billNumber = 'STU-' . $student_id . '-' . time();

try {
    $individualInfo = new IndividualInfo(
        bakongAccountID: 'khim_reaksmey@bkrt',
        merchantName: 'RUPP Store',
        merchantCity: 'PHNOM PENH',
        currency: KHQRData::CURRENCY_KHR,
        amount: round($amount, 2),
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
        'md5' => $md5
    ]);
} catch (Throwable $error) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate Bakong QR.',
        'detail' => $error->getMessage()
    ]);
}
