<?php

include __DIR__ . '/../src/Curl.php';
include __DIR__ . '/../src/Bank.php';

$apiKey = 'api-key-anda';
$apiSignature = 'api-signature anda';

$request = (new Esyede\CekMutasi\Curl())
    ->enableDebug(__DIR__ . '/debug.txt'); // aktifkan debug

$serviceCode = 'bca';
$packageCode = 'basic';
$username = 'testing';
$password = 'password';
$accountNumber = '1234567891';
$accountName = 'Nama Pemilik';
$ipnCallbackUrl = 'http://localhost/callback';
$status = 'ACTIVE';

$bank = new Esyede\CekMutasi\Bank($apiKey, $apiSignature, $request);

// Tambah rekening
$addAccount = $bank->add(
    $serviceCode,
    $packageCode,
    $username,
    $password,
    $accountNumber,
    $accountName,
    $ipnCallbackUrl,
    $status
);

print_r($addAccount); die;

// Untuk method - method lainnya caranya sama saja.