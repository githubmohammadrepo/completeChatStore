<?php
require_once("connection.php");

$object = new stdClass();

class SendTo20Store
{
    private $user_id;
    private $orders;
    private $dev_array; //array
    private $get_array; //array
    private $hika_user_id;
    private $conn;
    private $vendor_id;
    private $products;
    public $last_id;
    public $orderTableInfo;
    public $row;
    public $messageProducts;
    
    public function __construct($conn, $orders, $user_id)
    {
        foreach ($orders as $order) {
            $this->vendor_id = $order["vendor_id"];
        }
        $this->conn = $conn;

        // set hikashop user_id
        $this->getHikashopUserId($user_id);
        //get order infos
    }
    /**
     * get hikashop user id
     */
    public function getHikashopUserId($user_id)
    {
        $statusComplete = false;
        
        try {
            // run your code here
        $sql = "SELECT `user_id` FROM pish_hikashop_user WHERE user_cms_id=$user_id LIMIT 1";
            
            $result = $this->conn->query($sql);
            if ($result) {
                $rowcount = $result->num_rows;
                if ($rowcount > 0) {
                    
                    $row = $result->fetch_assoc();
                    $this->hika_user_id = $row['user_id'];

                } else {
                    $statusComplete = false;
                }
            } else {
                $statusComplete = false;
                throw new Exception("error accured when get order_product infos");
                
            }
        } catch (exception $e) {
            //code to handle the exception
            return $e->getMessage();
        }
        return $statusComplete;
    }

    /**
     * select process
     */
    private function SelectAction($sql)
    {
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return [];
        }
    }


    /***
     * find all order product order table by last order table id
     */
    public function getAllOrderProductTable()
    {
        $statusComplete = false;
        try {
            // run your code here
            $this->row =$sql = "SELECT * FROM `pish_hikashop_order_product`".
            " WHERE `order_id` = ".$this->last_id.";"; //have error
            
            
            $result = $this->conn->query($sql);
            if ($result) {
                $rowcount = $result->num_rows;
                if ($rowcount > 0) {
                    $dev_array = array();
                    for ($i = 0; $i < $result->num_rows; $i++) {
                        $row = $result->fetch_assoc();
                        $dev_array[$i] = $row;
                    }
                    
                    $message = '';
                    //create message for send
                    foreach($dev_array as $key => $value){
                        $message .='تعداد ';
                        $message .= $value['order_product_quantity'].' ';
                        $message .='نام محصول: ';
                        $message .= $value['order_product_name'];
                        $message .= ' <br>';
                        
                    }
                    $this->messageProducts = $message;

                    return true;
                } else {
                    $statusComplete = false;
                }
            } else {
                $statusComplete = false;
                throw new Exception("error accured when get order_product infos");
                
            }
        } catch (exception $e) {
            //code to handle the exception
            return $e->getMessage();
        }
        return $statusComplete;
    }
    /***
     * insert into order table
     */
    public function insertOrderTable($type)
    {
        $this->last_id++;
        $time = time();
        if ($type == 'sell') {
            $type = 'sell';
            $vendor_id = 0;
            $this->row = $sqlSellOrderType = "INSERT INTO pish_hikashop_order (order_user_id, order_status, order_id, order_created, order_modified, order_vendor_id)" .
                " VALUES($this->hika_user_id, '" . $type . "', $this->last_id, $time, $time,$vendor_id)";
            $rows = $this->conn->query($sqlSellOrderType);

            // if($rows->num_rows>0){

            // }else{

            // }
        }

        if ($type == 'subsell') {
            $type = 'subsell';

            $vendor_id = 0;
            $this->row = $sqlSellOrderType = "INSERT INTO pish_hikashop_order (order_user_id, order_status, order_id, order_created, order_modified, order_vendor_id)" .
                " VALUES($this->hika_user_id, '" . $type . "', $this->last_id, $time, $time,0)";
            $rows = $this->conn->query($sqlSellOrderType);
        }
    }

    /***
     * insert into customer_vendor table
     */
    public function insertCustomerVendorTable()
    {
        $statusComplete=false;
        try {
            // run your code here
            foreach ($this->vendor_id as $key => $VID) {

                $this->row = $sqlSellOrderType = "INSERT INTO `pish_customer_vendor`  (`customer_id`, `vendor_id` ) VALUES ($this->hika_user_id," . $VID['id'] . ")";
                $result = $this->conn->query($sqlSellOrderType);
                if($result){
                    $statusComplete =true;
                }else{
                    $statusComplete = false;
                    throw new Exception("error accured when insert vendor ids");
                }

            }
        } catch (exception $e) {
            //code to handle the exception
            $this->row = $e->getMessage();
        }
        return $statusComplete;
    }

    /***
     * find last order table id
     */
    public function getLastOrderTableId()
    {
        $this->row =$sql = "SELECT * FROM `pish_hikashop_order`".
        " WHERE `order_user_id` = ".$this->hika_user_id." ".
        " AND order_type = 'sale'".
        " order by order_created DESC".
        " limit 1"; //have error
        $rows = $this->conn->query($sql);
        if ($rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            
            // $this->last_id = $rows['order_id'];
            $this->orderTableInfo = $rows;
            $this->last_id = $row['order_id'];
            $this->error = $rows;
            return true;
        } else {
            $this->last_id = -1;
            return false;
        }
    }

    /***
     * insert into order_product
     */
    public function insertOrderProductTable($type)
    {
        if ($type == 'sell') {

            foreach ($this->products as $product) {

                $product_id = $product["product_id"];
                $cart_product_quantity = $product["quantity"];
                $product_name = $product["product_name"];
                $product_price = $product["product_price"];
                $product_code = 'product_' . $product["product_id"];

                //if i==0 save all product
                $sql = "INSERT INTO pish_hikashop_order_product (order_id, product_id, order_product_quantity, order_product_name, order_product_code,
                order_product_price) VALUES ($this->last_id, $product_id, $cart_product_quantity, '$product_name', '$product_code', " . ($product_price ? $product_price : 0) . ")";

                ($this->conn->query($sql));
            }
        }

        if ($type == 'subsell') {

            foreach ($this->products as $product) {

                $product_id = $product["product_id"];
                $cart_product_quantity = $product["quantity"];
                $product_name = $product["product_name"];
                $product_price = $product["product_price"];
                $product_code = 'product_' . $product["product_id"];

                //if i==0 save all product
                $sql = "INSERT INTO pish_hikashop_order_product (order_id, product_id, order_product_quantity, order_product_name, order_product_code,
                order_product_price) VALUES ($this->last_id, $product_id, $cart_product_quantity, '$product_name', '$product_code', " . ($product_price ? $product_price : 0) . ")";

                ($this->conn->query($sql));
            }
        }
    }
}

//using class

$card = array(
    'user_id' => 963,
    'orders' => array(
        array(
            'vendor_id' => array(
                array(
                    'id' => 107518
                ),

                array(
                    'id' => 129307
                ),

                array(
                    'id' => 129308
                ),

                array(
                    'id' => 129306
                ),

                array(
                    'id' => 128141
                )

            ),

            'products' => array(
                array(
                    'product_id' => 52235,
                    'quantity' => 1,
                    'product_name' => ' جرم گیر 4000 گرمی سورمه ای اکتیو',
                    'product_price' => 0
                ),

                array(
                    'product_id' => 51966,
                    'quantity' => 1,
                    'product_name' => ' جرم گیر 4000 گرمی سورمه ای اکتیو',
                    'product_price' => 0,
                ),

                array(
                    'product_id' => 50661,
                    'quantity' => 1,
                    'product_name' => ' جرم گیر 4000 گرمی سورمه ای اکتیو',
                    'product_price' => 0
                )

            )

        )

    )

);

//other code
$json = file_get_contents('php://input');
$post = json_decode($json, true);
// $post = $card;
$user_id = $post["user_id"];
$orders = $post["orders"];
$message='';

try {
    // throw new Exception("Some error message");

    if ($user_id) {
        $sendStore = new SendTo20Store($conn, $orders, $user_id);
        
        
        // step 1 => get order by user_id
        if($sendStore->getLastOrderTableId()){
            // $object->response = $sendStore->row->order_id;
            
            // step 2 => insert order data into pish_customer_vendor with default status undone
            if($sendStore->insertCustomerVendorTable()){
                //$object->response = 'vendor id saved complete';
                
                if($sendStore->getAllOrderProductTable()){
                    $object->response = 'ok';
                    
                    $message = $sendStore->messageProducts;
                    $object->response = 'ok';
                    
                }else{
                    $object->response = 'notok';
                }

                
            }else{
                $object->response = 'notok';
            }

        }else{
            $object->response = 'notok';
        }

        //stemp3 ===last ***  => sent chat message

    } else {
        $object->response = 'notok';
    }


    // $object->response = 'ok';
} catch (Exception $e) {
    // echo $e->getMessage();
    $object->response = 'notok';
}
// print_r(json_encode(['name' => $sendStore->row]));

$jsonEncode = json_encode([$object,$message], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

echo $jsonEncode;