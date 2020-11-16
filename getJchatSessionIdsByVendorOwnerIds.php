<?php
require_once("connection.php");

class GetJchatSessionIdsByVendorOwnerIds {
  private $conn;
  private $vendorOwnerIds;
  public $error;
  
  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  /**
   * set vendorOwner ids 
   */
  public function setVendorOwnerIds($vendorOwnerIds){
    $this->vendorOwnerIds = $vendorOwnerIds;
  }

  /**
   * !important law
   *    sessionId last user in pish_session is equal to jchat_sessionstatus session_id
   *  result => pish_session.session_id == jchat_sessionstatus.session_id
   * 
   */
  public function getVendorOwnerIds()
  {
    $statusComplete = false;
    try {
      // run your code here

     $this->error =  $sql = "SELECT * from pish_session" .
        " WHERE userid IN (" . implode(",", $this->vendorOwnerIds) . ")" .
        " GROUP by userid" .
        " ORDER BY time DESC;";
      $result = $this->conn->query($sql);
      if ($result) {
       $rowcount=mysqli_num_rows($result);
        if($rowcount>0){
            $dev_array= Array();
            for ($i = 0; $i < $result->num_rows; $i++)
            {
                $row = $result->fetch_assoc();
                $dev_array[$i] = $row;
            }

          return $dev_array;
        }else{
          $statusComplete = false;
        }
      } else {
        $statusComplete = false;
        throw new Exception("error accured when insert vendor ids");
      }
    } catch (exception $e) {
      //code to handle the exception
      return $e->getMessage();
    }
    return $statusComplete;
  }


}

/**
 * using class
 */

//get posted data
$json = file_get_contents('php://input');
$post = json_decode($json, true);

$vendorOwnerIds = $post['vedorOwnerIds'];

//create init from class
$init =  new GetJchatSessionIdsByVendorOwnerIds($conn);

$init->setVendorOwnerIds($vendorOwnerIds);

$result = $init->getVendorOwnerIds();
echo json_encode($result);