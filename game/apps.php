<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */

  session_start();
  require_once("../socle.php");
  
  if(!check_session()) exit;
  $player = retreive_player($_SESSION['id']);
  
  if(isset($_POST['launch']) && isset($_POST['token'])) {
    $app_to_launch = mysql_real_escape_string($_POST['launch']);
    $token = mysql_real_escape_string($_POST['token']);

    if(!check_token($_SESSION['id'], $token)) {
      exit("0");
    }

    if(file_exists("apps/".$app_to_launch.".app.php")) {
      include("apps/".$app_to_launch.".app.php");
    }
    exit;
  }
  
?>  