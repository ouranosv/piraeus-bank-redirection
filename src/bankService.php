<?php

class BankService {
    public static function issueTicket($order) {
        $params = array(
            'Request' => array(
                'Username' => Settings::bankUsername(),
                'Password' => Settings::bankPassword(),
                'MerchantId' => Settings::bankMerchantId(),
                'PosId' => Settings::bankPosId(),
                'AcquirerId' => Settings::bankAcquirerId(),
                'MerchantReference' => $order['id'],
                'RequestType' => Settings::bankRequestType(),
                'Amount' => $order['totalAmount'],
                'CurrencyCode' => Settings::bankCurrencyCode(),
                'ExpirePreauth' => 0,
                'Installments' => 0,
                'Bnpl' => 0
            )
        );

        $wsdl = Settings::bankIssueTicketUrl();

        $options = array(
            'uri'=> 'http://schemas.xmlsoap.org/soap/envelope/',
            'style'=> SOAP_RPC,
            'use'=> SOAP_ENCODED,
            'soap_version'=> SOAP_1_1,
            'cache_wsdl'=> WSDL_CACHE_NONE,
            'connection_timeout'=> 15,
            'trace'=> true,
            'encoding'=> 'UTF-8',
            'exceptions'=> true
        );

        $soap = new SoapClient($wsdl, $options);
        $data = $soap->issueNewTicket($params);

        if($data->IssueNewTicketResult->ResultCode != 0) {
            return $data->IssueNewTicketResult->ResultDescription;
        }
        else {
            BankRepository::createTicket($order['id'], $data->IssueNewTicketResult->TranTicket);
            return null;
        }
    }

    public static function isValidHashKey($transaction) {
        $ticket = BankRepository::getTicketByOrderId($transaction['merchantReference']);
        if($ticket == null) return false;

        $calculatedHashKey = BankService::calculateHashKey($ticket['ticket'], $transaction);

        if($calculatedHashKey !== $transaction['hashKey'])
            return false;

        return true;
    }

    public static function calculateHashKey($ticket, $transaction) {
        $concatValues = sprintf(
            '%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s',
            $ticket,
            Settings::bankPosId(),
            Settings::bankAcquirerId(),
            $transaction['merchantReference'],
            $transaction['approvalCode'],
            $transaction['parameters'],
            $transaction['responseCode'],
            $transaction['supportReferenceID'],
            $transaction['authStatus'],
            $transaction['packageNo'],
            $transaction['statusFlag']
        );

        return strtoupper(hash_hmac('sha256', $concatValues, $ticket, false));
    }

    public static function getTransaction() {
        return array(
            'supportReferenceID' => $_POST['SupportReferenceID'], 
            'resultCode' => $_POST['ResultCode'],
            'resultDescription' => $_POST['ResultDescription'], 
            'statusFlag' => $_POST['StatusFlag'],
            'responseCode' => $_POST['ResponseCode'], 
            'responseDescription' => $_POST['ResponseDescription'],
            'languageCode' => $_POST['LanguageCode'], 
            'merchantReference' => $_POST['MerchantReference'],
            'transactionDateTime' => $_POST['TransactionDateTime'], 
            'transactionId' => $_POST['TransactionId'],
            'cardType' => $_POST['CardType'], 
            'packageNo' => $_POST['PackageNo'],
            'approvalCode' => $_POST['ApprovalCode'], 
            'retrievalRef' => $_POST['RetrievalRef'],
            'authStatus' => $_POST['AuthStatus'], 
            'parameters' => $_POST['Parameters'],
            'hashKey' => $_POST['HashKey'], 
            'paymentMethod' => $_POST['PaymentMethod']
        );
    }
}

?>