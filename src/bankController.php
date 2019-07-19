<?php

class BankController {
    public function success() {
        if(empty($_POST)) exit;

        $transaction = BankService::getTransaction();

        BankRepository::createTransaction($transaction);

        if($transaction['resultCode'] !=='0')
            throw new Exception('There was a technical issue. The transaction was not completed');

        if($transaction['statusFlag'] !== 'Success')
            throw new Exception('The transaction was not approved');

        if(!BankService::isValidHashKey($transaction)) 
            throw new Exception('Not valid hashKey');

        BankRepository::deleteTicketByOrderId($transaction['merchantReference']);

        OrderService::confirmOrder($transaction['merchantReference'], $transaction['transactionId'] . ' - ' . $transaction['approvalCode']);
    }

    public function failure() {
        if(empty($_POST)) exit;

        $transaction = BankService::getTransaction();

        BankRepository::createTransaction($transaction);
        BankRepository::deleteTicketByOrderId($transaction['merchantReference']);
    }
}

?>