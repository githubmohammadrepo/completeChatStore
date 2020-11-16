<!-- {source} -->

 

<?php

 

session_start();

 

$vendor_ids = array();

 

?>

 

 

 

 

 

 

 

<!DOCTYPE html>

 

 

 

<html lang="en">

 

 

 

 

 

 

 

<head>

 

 

 

  <meta charset="UTF-8">

 

 

 

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

 

 

 

  <title>Document</title>

 

 

 

</head>

 

 

 

 

 

 

 

<body>

 

 

 

 

 

 

 

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>

 

 

 

  <?php

 

     $user_id = (JFactory::getUser())->id;

 

  // $user_cms_id = (JFactory::getUser()->id);

 

  $user_id = 963;

//

  error_reporting(E_ALL);

 

  ini_set('error_reporting', E_ALL);

 

  ini_set('display_errors', 1);

 

  // start get card info

 

  $fields = ['user_id' => $user_id];

 

  $user_id = json_encode($fields);

 

  $basket = array();

 

  $cardSaved = false;

 

  $foundStore = 0;

 

  // $session = JFactory::getSession();

 

  $session = array();

 

  //get nearest stores

 

  /**

 

 

 

 

 

 

 

   * start get nearest shops

 

 

 

 

 

 

 

   */

 

  function getCurrentUserLocation($user_id, &$post)

  {

 

    $userLocationUrl = 'http://hypertester.ir/serverHypernetShowUnion/getUserLocation.php';

 

    $ch = curl_init();

 

    curl_setopt($ch, CURLOPT_URL, $userLocationUrl);

 

    curl_setopt($ch, CURLOPT_POSTFIELDS, $user_id);

 

    // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

 

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

 

    $result = curl_exec($ch);

 

    if (curl_errno($ch)) {

 

      $error_msg = curl_error($ch);

 

      print_r($error_msg);

    }

 

    curl_close($ch);

 

    // echo $result;

 

    $userLocationResult = (json_decode($result, true));

 

    foreach ($userLocationResult as $key => $value) {

 

      if ($key == 0) {

      } else {

 

        $post['lat'] = $value['latitude'];

 

        $post['lng'] = $value['longitude'];

      }

    }

  }

 

  //define function select neares shop and create cart for user

 

  /**

 

 

 

   *

 

 

 

   * @return array

 

 

 

   *

 

 

 

   */

 

  function selectNearestShop($post, &$foundStore, $user_id, &$vendor_ids)

  {

 

    $url = "http://hypertester.ir/serverHypernetShowUnion/SelectNearestShop.php";

 

    $ch = curl_init();

 

    curl_setopt($ch, CURLOPT_URL, $url);

 

    curl_setopt($ch, CURLOPT_POST, 1);

 

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

 

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

 

    $output = curl_exec($ch);

 

    if (curl_errno($ch)) {

 

      $error_msg = curl_error($ch);

    }

 

    curl_close($ch);

 

    $contents = json_decode($output, true);

 

    $foundStore = -1;

 

    // end select nearest shop

 

    if ($contents && count($contents) > 0) {

 

      if ($contents[0]['id'] == "notok") {

 

        $foundStore = -1;

 

        return [];

      } else {

 

        $ids = [];

 

        for ($j = 0; $j < count($contents); $j++) {

 

          $ids[] = [

 

            'id' => $contents[$j]['id'],

 

            'user_id' => $contents[$j]['user_id'],

 

          ];

        }

 

        $vendor_ids = $ids;

 

        // var_dump($products);

 

        $foundStore = 1;

 

        $card = [

 

          'user_id' => json_decode($user_id)->user_id,

 

          'orders' => [[

 

            'vendor_id' => $ids,

 

            // 'products' => $products,

 

          ]],

 

        ];

 

        return $card;

      }

    } else {

 

      $foundStore = -1;

 

      return [];

    }

  }

 

 

 

  function sentUserCartTo20Store($card, &$cardSaved, $vendor_ids)

  {

 

    $url = "http://hypertester.ir/serverHypernetShowUnion/Sendto20StoreAndNotify.php";

 

    $ch = curl_init();

 

    curl_setopt($ch, CURLOPT_URL, $url);

 

    curl_setopt($ch, CURLOPT_POST, 1);

 

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($card));

 

    // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['sentTo20Store'=>true]));

 

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

 

    $output = curl_exec($ch);

 

    curl_close($ch);

 

    // var_dump($output);

 

    $contents = json_decode($output);

 

    // if($contents instanceof stdClass)

    return $contents;

  }

 

if (isset($_POST) && isset($_POST["lat"]) && isset($_POST["lng"])) {

 

    $post = [

 

      'lat' => 0,

 

      'lng' => 0,

 

    ];

 

    // $url = "http://hypernetshow.com/serverHypernetShowUnion/SelectNearestShop.php";

 

    // start get lat and lng location current user.

 

    // using class

 

    getCurrentUserLocation($user_id, $post);

 

    // end get lat and lng location current user. **completed**

 

    // start select nearest shop

 

    $card = selectNearestShop($post, $foundStore, $user_id, $vendor_ids);

 

    //////////  save card  ///////////////

 

 

 

    if ($card && count($card)) {

      $contents = sentUserCartTo20Store($card, $cardSaved, $vendor_ids);

      if ($contents[0]->response == 'notok') {

     echo " <h2>محصولات شما قبلا به فروشگاه های نزدیک فرستاده شده است.</h2>";

        //show error

        

        var_dump($contents);

        } else {

        

        echo $contents[1];

        

        $cardSaved = true;

        

        //sent message to stores

        

        $vendor_user_ids = array();

        

        foreach ($vendor_ids as $key => $value) {

        

            array_push($vendor_user_ids, $value['user_id']);

        }

        

        //get all sessionIds belongs to vendor own user_id

        

        $url = 'http://hypertester.ir/serverHypernetShowUnion/getJchatSessionIdsByVendorOwnerIds.php';

        

        // start get card info

        

        $ch = curl_init();

        

        curl_setopt($ch, CURLOPT_URL, $url);

        

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($vendorOwnerSessionIds = ['vedorOwnerIds' => $vendor_user_ids]));

        

        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        

        $result = curl_exec($ch);

        

        if (curl_errno($ch)) {

        

            $error_msg = curl_error($ch);

        

            print_r($error_msg);

        }

        

        curl_close($ch);

        

        $vendorOwnerIdSessionIds = json_decode($result);

        

        var_dump($vendorOwnerIdSessionIds);

        

        ?>

        

        

        

        

        

        

        

        

        

        

        

        <script>

        let jsonLiveSite = "http://hypertester.ir/index.php?option=com_jchat&format=json"

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        var postObject;

        

        

        <?php foreach ($vendor_user_ids as $key => $storeOwnUser_id) { ?>

        

        postObject = {

       "message": "<?php echo $contents[1]; ?>",

        

          "task": "stream.saveEntity",

        

        

          // "to": "128fc5f5396b80b81d6120ee059f975e",//error

        

        

        

          <?php

        

            foreach ($vendorOwnerIdSessionIds as $key => $value) {

        

                if ($value->userid == $storeOwnUser_id) {

        

                    ?>

        

        

              "to": "<?php echo $value->session_id; ?>", //error - solved => ownUser sessionId

        

        

        

        

        

        

        

          <?php

        

                    break;

                }

            }

        

            ?>

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

          "tologged": "<?php echo $storeOwnUser_id; ?>"

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        };

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        $.post(jsonLiveSite, postObject, function(response) {

          console.log(response);

          postObject = null;

        });

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        <?php }?>

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        // console.log('hi mohammad', '');

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        // console.log('jqEvent' + jqEvent);

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        // console.log('this' + this);

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        // console.log('ae:' + ae);

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        // console.log('complete object:' + completeObject);

        </script>

        

        

        

        <?php

        

        }

        

 

 

      ///////////////////////////////////////

 

    } else {

 

      echo 'hi';

    }

  }

 

  /**

 

 

 

 

 

 

 

   * end get nearest shops

 

 

 

 

 

 

 

   */

 

  ?>

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

  <!-- last step -->

 

 

 

 

 

 

 

  <!-- additional files for show infos -->

 

 

 

 

 

 

 

  <?php

 

  // Create connection

 

  $imagePath = "http://www.fishopping.ir/images/com_hikashop/upload/";

 

  $p_ids = [];

 

  // $session = JFactory::getSession();

 

  // $basket = $session->get('store_basket');

 

  if ($basket) {

 

    // var_dump($p_ids);

 

    //query

 

    // var_dump($arr_res);

 

    // $arr_res[$i]['product_image'] = $imagePath . $row['product_image'];

 

  }

 

  if ($cardSaved) {

 

  ?>

 

    <div style="text-align: center; background-color: #eee; padding: 10px; margin-bottom: 10px; color: green; font-size: 16px; font-weight: bold;">

 

 

      <p>سبد خرید با موفقیت برای فروشگاه های نزدیک شما ارسال شد. </p>

 

 

    </div>

 

  <?php

 

  }

 

  if (true) {

 

 

  ?>

 

 

    <form method="post" style="text-align: left;">

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

      <input type="hidden" id="lat" name="lat" value="">

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

      <input type="hidden" id="lng" name="lng" value="">

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

      <button type="submit" name"send_card">ارسال سبد خرید</button>

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

    </form>

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

    <?php

 

    if ($foundStore == -1) {

 

    ?>

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

      <div style="text-align: center; background-color: #eee; padding: 10px; margin-bottom: 10px; color: red; font-size: 16px; font-weight: bold;">

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

        <p>فروشگاهی در نزدیکی شما پیدا نشد.</p>

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

      </div>

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

    <?php

 

    }

 

    ?>

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

  <?php

 

  } else {

 

  ?>

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

    <div>

 

 

 

 

 

 

 

      <p style="width: 100%; background-color: #eee; padding: 10px; text-align: center;">

 

 

 

 

 

 

 

        سبد خرید خالی می باشد.

 

 

 

 

 

 

 

      </p>

 

 

 

 

 

 

 

    </div>

 

 

 

 

 

 

 

 

 

 

 

 

 

 

 

  <?php

 

  }

 

  ?>

 

 

 

 

 

 

 

<!-- {/source} -->