<?php 
namespace NagadAPI;

use NagadAPI\Helpers\NagadHelper;
use Illuminate\Support\Facades\Http;

class NagadGenerator
{
    protected $MERCHANT_ID;
    protected $MERCHANT_ACCOUNT;
    protected $BASE_URL;
    protected $ORDER_ID;
    protected $AMOUNT;
    protected $ADDITIONAL;
    protected $DATETIME;
    protected $PAYMENT_REF_ID;
    protected $CHALLANGE;
    protected $CALLBACK_URL;
    protected $REDIRECT_URL;
    protected $CALLBACK_RESPONSE;
    protected $VERIFIED_RESPONSE;
    protected $HELPER;
    protected $TIMEZONE;

    /**
     * generateSensitiveData
     *
     * @return array
     */
    protected function generateSensitiveData() : array
    {
        return [
            'merchantId' => $this->MERCHANT_ID,
            'datetime' => $this->DATETIME,
            'orderId' => $this->ORDER_ID,
            'challenge' => $this->HELPER->generateRandomString()
        ];
    }
    
    /**
     * generateSensitiveDataOrder
     *
     * @return array
     */
    protected function generateSensitiveDataOrder() : array 
    {
        return [
            'merchantId' => $this->MERCHANT_ID,
            'orderId' => $this->ORDER_ID,
            'currencyCode' => '050',        //050 = BDT
            'amount' => $this->AMOUNT,
            'challenge' => $this->CHALLANGE
        ];
    }
    
    /**
     * generatePaymentRequest
     *
     * @param  mixed $sensitiveData
     * @return array
     */    
    protected function generatePaymentRequest(array $sensitiveData) : array
    {
        return $this->HELPER->HttpPostMethod($this->BASE_URL.config('nagad.endpoints.checkout-init').'/'.$this->MERCHANT_ID.'/'.$this->ORDER_ID, [
            'accountNumber' => $this->MERCHANT_ACCOUNT,
            'dateTime' => $this->DATETIME,
            'sensitiveData' => $this->HELPER->EncryptDataWithPublicKey(json_encode($sensitiveData)),
            'signature' => $this->HELPER->SignatureGenerate(json_encode($sensitiveData))
        ]);
    }
    
    /**
     * decryptInitialResponse
     *
     * @param  mixed $response
     * @return bool
     */
    protected function decryptInitialResponse(array $response): bool
    {
        $plainResponse = json_decode($this->HELPER->DecryptDataWithPrivateKey($response['sensitiveData']), true);

        if(isset($plainResponse['paymentReferenceId']) && isset($plainResponse['challenge'])) {
            $this->PAYMENT_REF_ID = $plainResponse['paymentReferenceId'];
            $this->CHALLANGE = $plainResponse['challenge'];
            return true;
        }
        return false;
    }
    
    /**
     * completePaymentRequest
     *
     * @param  mixed $sensitiveOrderData
     * @return array
     */
    protected function completePaymentRequest(array $sensitiveOrderData): array
    {
        return $this->HELPER->HttpPostMethod($this->BASE_URL.config('nagad.endpoints.checkout-complete').'/'.$this->PAYMENT_REF_ID, [
            'sensitiveData' => $this->HELPER->EncryptDataWithPublicKey(json_encode($sensitiveOrderData)),
            'signature' => $this->HELPER->SignatureGenerate(json_encode($sensitiveOrderData)),
            'merchantCallbackURL' => $this->CALLBACK_URL,
            'additionalMerchantInfo' => (object)$this->ADDITIONAL
        ]);
    }
    
    /**
     * verifyPayment
     *
     * @return void
     */
    protected function verifyPayment()
    {
        $payment_ref_id = $this->CALLBACK_RESPONSE->payment_ref_id;
        $this->VERIFIED_RESPONSE = $this->HELPER->HttpGetMethod($this->BASE_URL.config('nagad.endpoints.payment-verify').'/'.$payment_ref_id);
    }
}