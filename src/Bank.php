<?php

namespace Esyede\CekMutasi;

use DateTime;

class Bank
{
    const BASE_URL = 'https://api.cekmutasi.co.id/v1';

    private $apiKey;
    private $apiSignature;
    private $request;

    /**
     * Konstruktor.
     *
     * @param string                 $apiKey
     * @param string                 $apiSignature
     * @param \Esyede\CekMutasi\Curl $request
     */
    public function __construct($apiKey, $apiSignature, Curl $request)
    {
        $this->apiKey = $apiKey;
        $this->apiSignature = $apiSignature;
        $this->request = $request->withOption('HTTPHEADER', [
            'Api-Key: ' . $apiKey,
            'Accept: application/json',
        ]);
    }

    /**
     * Tambah rekening bank.
     *
     * @param string $serviceCode
     * @param string $packageCode
     * @param string $username
     * @param string $password
     * @param string $accountNumber
     * @param string $accountName
     * @param string $ipnCallbackUrl
     * @param string $status
     *
     * @return \stdClass
     */
    public function add(
        $serviceCode,
        $packageCode,
        $username,
        $password,
        $accountNumber,
        $accountName,
        $ipnCallbackUrl,
        $status
    ) {
        $payloads = [
            'service_code' => strtolower($serviceCode),
            'package_code' => strtolower($packageCode),
            'username' => $username,
            'password' => $password,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'ipn_url' => $ipnCallbackUrl,
            'status' => strtoupper($status),
        ];

        return $this->request
            ->to(self::BASE_URL . '/bank/add')
            ->withData(http_build_query($payloads))
            ->returnResponseObject()
            ->post();
    }

    /**
     * Update akun bank.
     *
     * @param int    $accountId
     * @param string $password
     * @param string $accountNumber
     * @param string $ipnCallbackUrl
     * @param string $status
     *
     * @return \stdClass
     */
    public function update(
        $accountId,
        $password,
        $accountNumber,
        $ipnCallbackUrl,
        $status
    ) {
        $payloads = [
            'id' => $accountId,
            'password' => $password,
            'account_number' => $accountNumber,
            'ipn_url' => $ipnCallbackUrl,
            'status' => strtoupper($status),
        ];

        return $this->request
            ->to(self::BASE_URL . '/bank/update')
            ->withData(http_build_query($payloads))
            ->returnResponseObject()
            ->post();
    }

    /**
     * Hapus akun bank.
     *
     * @param int $accountId
     *
     * @return \stdClass
     */
    public function delete($accountId)
    {
        $payloads = [
            'id' => $accountId,
        ];

        return $this->request
            ->to(self::BASE_URL . '/bank/delete')
            ->withData(http_build_query($payloads))
            ->returnResponseObject()
            ->post();
    }

    /**
     * Daftar rekening bank.
     *
     * @param string $serviceCode
     * @param string $accountNumber
     *
     * @return \stdClass
     */
    public function lists($serviceCode, $accountNumber)
    {
        $payloads = [
            'service_code' => $serviceCode,
            'account_number' => $accountNumber,
        ];

        $payloads =  http_build_query($payloads);

        return $this->request
            ->to(self::BASE_URL . '/bank/list?' . $payloads)
            ->returnResponseObject()
            ->get();
    }

    /**
     * Cari mutasi bank.
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param float     $amount
     * @param string    $type
     * @param string    $description
     * @param string    $serviceCode
     * @param string    $accountNumber
     *
     * @return \stdClass
     */
    public function searchBank(
        DateTime $startDate,
        DateTime $endDate,
        $amount,
        $type,
        $description,
        $serviceCode,
        $accountNumber
    ) {
        $payloads = [
            'date' => [
                'from' => $startDate->format('Y-m-d H:i:s'),
                'to' => $endDate->format('Y-m-d H:i:s'),
            ],
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'service_code' => $serviceCode,
            'account_number' => $accountNumber,
        ];

        return $this->request
            ->to(self::BASE_URL . '/bank/search')
            ->withData(http_build_query($payloads))
            ->returnResponseObject()
            ->post();
    }

    /**
     * Cari mutasi paypal.
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string    $username
     * @param float     $amount
     * @param string    $email
     * @param string    $transactionId
     * @param string    $currency
     *
     * @return \stdClass
     */
    public function searchPaypal(
        DateTime $startDate,
        DateTime $endDate,
        $username,
        $amount,
        $email,
        $transactionId,
        $currency
    ) {
        $payloads = [
            'date' => [
                'from' => $startDate->format('Y-m-d H:i:s'),
                'to' => $endDate->format('Y-m-d H:i:s'),
            ],
            'username' => $username,
            'amount' => $amount,
            'email' => $email,
            'transactionid' => $transactionId,
            'currency' => $currency,
        ];

        return $this->request
            ->to(self::BASE_URL . '/paypal/search')
            ->withData(http_build_query($payloads))
            ->returnResponseObject()
            ->post();
    }

    /**
     * Notifikasi whatsapp.
     *
     * @param int    $mutationId
     * @param string $senderPhone
     * @param string $receiverPhone
     *
     * @return \stdClass
     */
    public function whatsapp($mutationId, $senderPhone, $receiverPhone)
    {
        $payloads = [
            'mutation_id' => $mutationId,
            'sender_phone' => $senderPhone,
            'receiver_phone' => $receiverPhone,
        ];

        return $this->request
            ->to(self::BASE_URL . '/whatsapp/send')
            ->withData(http_build_query($payloads))
            ->returnResponseObject()
            ->post();
    }
}