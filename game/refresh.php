<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
  session_start();
  require_once("../socle.php");
  if(!check_session()) exit(json_encode(array('action' => array('command' => '%01', 'message' => false))));
  $account = retreive_account($_SESSION['id']);
  $player = retreive_player($_SESSION['id']);
  
  function retreive_action() { // S'ex�cutent une par une.
    $resource_action = mysql_query("SELECT * FROM actions WHERE account_id = {$_SESSION['id']} ORDER BY id ASC LIMIT 1");
    if(mysql_num_rows($resource_action) != 0) {
      $action = mysql_fetch_array($resource_action);
      mysql_query("DELETE FROM actions WHERE id = {$action['id']}");
      return array("command" => $action['command'], "message" => $action['message']);
    }
    else return false;
  }
  
  function retreive_new_messages($last_message_seen) {
    $resource_messages = mysql_query("SELECT * FROM messenger WHERE id > ".$last_message_seen." ORDER BY id DESC LIMIT 30");
    $messages = array();
    while($message = mysql_fetch_array($resource_messages)) {
      $author = retreive_account($message['account_id']);
      
      if($message['mpto'] !== '0') {
        if($message['mpto'] == "#modo") {
          $hash = array("id" => $message['id'], "pseudo" => grade_pseudo($message['pseudo'], $author['grade']), "message" => link_site(smiley(utf8_decode(stripslashes(htmlspecialchars($message['message']))))), "mpto" => utf8_encode('<img src="design/users/moderator.png" class="icon" /> Mod�rateurs'));
          if($_SESSION['grade'] >= 2) array_unshift($messages, $hash);
        }
        else {
          $receiver = retreive_account($message['mpto'], true);
          $hash = array("id" => $message['id'], "pseudo" => grade_pseudo($message['pseudo'], $author['grade']), "message" => link_site(smiley(utf8_decode(stripslashes(htmlspecialchars($message['message']))))), "mpto" => grade_pseudo($receiver['pseudo'], $receiver['grade']));
          if($message['mpto'] == $_SESSION['pseudo'] || $message['pseudo'] == $_SESSION['pseudo'] || ($_SESSION['grade'] == 2 && $author['grade'] != 3 && $receiver['grade'] != 3) || $_SESSION['grade'] == 3) {
            array_unshift($messages, $hash);
          }
        }
      }
      else { // Ce n'est pas un MP.
        if($message['pseudo'] == "#modo")
          $hash = array("id" => $message['id'], "pseudo" => utf8_encode('<img src="design/users/moderator.png" class="icon" />~ Mod�ration'), "message" => '<b>'.link_site(smiley(utf8_decode(stripslashes(htmlspecialchars($message['message']))))).'</b>', "mpto" => false);
        elseif($message['pseudo'] == "#sys")
          $hash = array("id" => $message['id'], "pseudo" => "#sys", "message" => link_site(smiley(utf8_decode(stripslashes(htmlspecialchars($message['message']))))), "mpto" => false);
        elseif($message['pseudo'] == "#delmsg")
          $hash = array("id" => $message['id'], "pseudo" => "#delmsg", "message" => link_site(smiley(utf8_decode(stripslashes(htmlspecialchars($message['message']))))), "mpto" => false);
        else
          $hash = array("id" => $message['id'], "pseudo" => grade_pseudo($message['pseudo'], $author['grade']), "message" => link_site(smiley(utf8_decode(stripslashes(htmlspecialchars($message['message']))))), "mpto" => false);
        array_unshift($messages, $hash);
      }
    }
    return $messages;
  }
  
  function retreive_messenger_online() {
    $resource_accounts_online_chatting = mysql_query("SELECT * FROM accounts WHERE last_activity_timestamp > ".(time() - 30)." AND last_messenger_activity_timestamp > ".(time() - 30)." ORDER BY grade DESC, id ASC");
    $resource_accounts_online_not_chatting = mysql_query("SELECT * FROM accounts WHERE last_activity_timestamp > ".(time() - 30)." AND last_messenger_activity_timestamp <= ".(time() - 30)." ORDER BY grade DESC, id ASC");
    $online = array();
    while($account_online_chatting = mysql_fetch_array($resource_accounts_online_chatting)) {
      array_push($online, grade_pseudo($account_online_chatting['pseudo'], $account_online_chatting['grade']));
    }
    while($account_online_not_chatting = mysql_fetch_array($resource_accounts_online_not_chatting)) {
      array_push($online, '<span style="opacity:0.5">'.grade_pseudo($account_online_not_chatting['pseudo'], $account_online_not_chatting['grade'].'</span>'));
    }
    return $online;
  }
  
  function retreive_matrice() {
    $resource_matrice = mysql_query("SELECT * FROM matrice WHERE won_by = ''");
    if(mysql_num_rows($resource_matrice) != 0) {  
      $matrice = mysql_fetch_array($resource_matrice);
      $image = imagecreatetruecolor(260, 35);
      
      if($matrice['tokens'] == -1) $color = imagecolorallocate($image, 255, 100, 0);
      elseif($matrice['tokens'] == -2) $color = imagecolorallocate($image, 255, 0, 0);
      else $color = imagecolorallocate($image, 127, 255, 0);
      
      $red = imagecolorallocate($image, 255, 0, 0);
      $blue = imagecolorallocate($image, 0, 0, 255);
      $black = imagecolorallocate($image, 0, 0, 0);
      $font = '../design/consolas.ttf';
      
      $e = rand(0, 4);
      if($e == 0)     $order = array(0,1,7,3,5,4,6,2,8,9);
      elseif($e == 1) $order = array(9,1,7,3,4,5,6,2,8,0);
      elseif($e == 2) $order = array(0,1,7,6,4,5,3,2,8,9);
      elseif($e == 3) $order = array(9,8,2,3,5,4,6,7,1,0);
      elseif($e == 4) $order = array(0,8,2,6,5,4,3,7,1,9);
        
      $lol = array();
      for($i = 0; $i < 10; $i++) {
        imagettftext($image, 35, 0, $i*26, 35, $color, $font, $matrice['code'][$order[$i]]);
      }
      imagecolortransparent($image, $black);

      ob_start();
      imagepng($image);
      $content = ob_get_contents();
      ob_end_clean();
      
      return array("id" => $matrice['id'], "order" => $order, "content" => base64_encode($content));
    }
    else return false;
  }
  
  function retreive_new_mps() {
    $resource_new_mps = mysql_query("SELECT * FROM mp WHERE receiver_id = '{$_SESSION['id']}' AND has_been_read = 0");
    return mysql_num_rows($resource_new_mps);
  }
  
  function retreive_decryptlab_rooms() {
    $resource_decryptlab_rooms = mysql_query("SELECT * FROM decryptlab_rooms WHERE won_by = '-1'");
    return mysql_num_rows($resource_decryptlab_rooms);
  }
  
  if(isset($_POST['refresh']) && isset($_POST['last_message_seen'])) {
    $last_message_seen = mysql_real_escape_string($_POST['last_message_seen']);
    $messenger_opened = mysql_real_escape_string($_POST['messenger_opened']);
    $synchroserveur = isset($_POST['synchroserveur'])?mysql_real_escape_string($_POST['synchroserveur']):"b";
    
    if($synchroserveur == "a") {
      mysql_query("UPDATE accounts SET cheat = '1' WHERE pseudo = '{$account['pseudo']}'");
    }

    if($messenger_opened == 1) {
      mysql_query("UPDATE accounts SET last_messenger_activity_timestamp = '".time()."' WHERE pseudo = '{$account['pseudo']}'");
      $resource_last_message = mysql_query("SELECT * FROM messenger ORDER BY id DESC LIMIT 1");
      $last_message = mysql_fetch_array($resource_last_message);
      if(mysql_num_rows($resource_last_message) != 0) $new_last_message_seen = $last_message['id'];
      else $new_last_message_seen = 0;
    }
    else $new_last_message_seen = 0;
    
    
    $bar = array("level" => $player['level'], "tokens" => $player['tokens'], "servers" => $player['servers'], "rams" => $player['rams'], "average_servers_security" => $player['average_servers_security'], "hacked_servers" => $player['hacked_servers'], "bonus" => $account['bonus']);
    
    include('achievements.php');
    
    $response = array();
    $response["matrice"] = retreive_matrice();
    $response["action"] = retreive_action();
    $response["messages"] = $messenger_opened?retreive_new_messages($last_message_seen):array();
    $response["last_message_seen"] = $new_last_message_seen;
    $response["players_online"] = $messenger_opened?retreive_messenger_online():array();
    $response["bar"] = $bar;
    $response["new_mps"] = retreive_new_mps();
    $response["decryptlab_rooms"] = retreive_decryptlab_rooms();
    $response["achievements"] = $achievement;

    echo json_encode($response);
  }
?>  