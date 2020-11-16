<?php
include "connection.php";

/*$json = '{
  "lat": "35.7908",
  "lng": "51.43781944"
  }';*/

$json = file_get_contents('php://input');
$post = json_decode($json, true);

$lat = $post["lat"];
$lng = $post["lng"];

//mohammad ravnad
//ichange this sql --> this line (HAVING distance < 1 and user_id is not null)
$sql5= "SELECT 
id,user_id, 
(
   6371*
   acos(cos(radians($lat)) * 
   cos(radians(latitude)) * 
   cos(radians(longitude) - 
   radians($lng)) + 
   sin(radians($lat)) * 
   sin(radians(latitude)))
) AS distance 
FROM pish_phocamaps_marker_store 
HAVING user_id is not null And LENGTH(user_id)
ORDER BY distance LIMIT 0, 20";
$result = $conn->query($sql5);
if ($result->num_rows > 0) 
{
    for ($i = 0; $i < $result-> num_rows; $i++)
    {
        $row = $result->fetch_assoc();
        $dev_array[$i] = $row;
    }

    $jsonEncode = json_encode($dev_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    echo $jsonEncode;

} else {
 $dev_array[0]['id'] = "notok"; 
 $dev_array[1]['user_id'] = "null";
 $dev_array[2]['distance'] = "null"; 
    
    $jsonEncode = json_encode($dev_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    echo $jsonEncode;
}
$conn->close();
?>