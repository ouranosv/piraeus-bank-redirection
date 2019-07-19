<?php

class BankRepository {
    private static $initialized = false;
    private static $conn;

    private static function initialize()
    {
        if (self::$initialized)
            return;

        self::$initialized = true;
        self::$conn = DBHelper::connection();
    }

    public static function getTicketByOrderId($orderId)
    {
        self::initialize();

        $sql = "select t.* from banktickets t where t.orderId = ?";
        
        $stmt = self::$conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if($res->num_rows > 0)
            return $res->fetch_assoc();
        else
            return null;
    }

    public static function createTicket($orderId, $ticket)
    {
        self::initialize();

        $sql = "insert into banktickets 
                    (orderId, ticket) 
                values 
                    (?, ?)";
        
        $stmt = self::$conn->prepare($sql);
        $stmt->bind_param("is", $orderId, $ticket);
        $stmt->execute();
        
        return $stmt->affected_rows;
    }

    public static function createTransaction($transaction)
    {
        self::initialize();

        $sql = "insert into banktransactions
                    (supportReferenceID
                    ,resultCode
                    ,resultDescription
                    ,statusFlag
                    ,responseCode
                    ,responseDescription
                    ,languageCode
                    ,merchantReference
                    ,transactionDateTime
                    ,transactionId
                    ,cardType
                    ,packageNo
                    ,approvalCode
                    ,retrievalRef
                    ,authStatus
                    ,parameters
                    ,hashKey
                    ,paymentMethod)
                values
                    (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        
        $stmt = self::$conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssssss", 
            $transaction['supportReferenceID'], 
            $transaction['resultCode'],
            $transaction['resultDescription'], 
            $transaction['statusFlag'],
            $transaction['responseCode'], 
            $transaction['responseDescription'],
            $transaction['languageCode'], 
            $transaction['merchantReference'],
            $transaction['transactionDateTime'], 
            $transaction['transactionId'],
            $transaction['cardType'], 
            $transaction['packageNo'],
            $transaction['approvalCode'], 
            $transaction['retrievalRef'],
            $transaction['authStatus'], 
            $transaction['parameters'],
            $transaction['hashKey'], 
            $transaction['paymentMethod']
        );
        $stmt->execute();
        
        return $stmt->affected_rows;
    }

    public static function deleteTicketByOrderId($orderId)
    {
        self::initialize();

        $sql = "delete from banktickets where orderId = ?";
        
        $stmt = self::$conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        
        return $stmt->affected_rows;
    }
}

?>