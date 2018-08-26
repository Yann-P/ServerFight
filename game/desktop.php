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
  $account = retreive_account($_SESSION['id']);

  // Icones.
  $desktop_icons = array();
  
  $hash = array("id" => 456, "name" => "terminal", "full_name" => "Terminal"); array_push($desktop_icons, $hash);

  $resource_apps = mysql_query("SELECT * FROM applications");

  while($app = mysql_fetch_assoc($resource_apps)) {
    $hash = array("id" => $app['id'], "name" => $app['name'], "full_name" => utf8_encode($app['full_name']));
    array_push($desktop_icons, $hash);
  }


  if($_SESSION['grade'] == 3) { $hash = array("id" => 567, "name" => "admin", "full_name" => "Administration"); array_push($desktop_icons, $hash); }
  if($_SESSION['grade'] >= 2) { $hash = array("id" => 678, "name" => "modo", "full_name" => "Modération"); array_push($desktop_icons, $hash); }
  $hash = array("id" => 123, "name" => "guide", "full_name" => "Guide du hacker"); array_push($desktop_icons, $hash);
  $hash = array("id" => 234, "name" => "rank", "full_name" => "Classement"); array_push($desktop_icons, $hash);
  $hash = array("id" => 789, "name" => "mp", "full_name" => "Messages privés"); array_push($desktop_icons, $hash);
  $hash = array("id" => 210, "name" => "profil", "full_name" => "Mon profil"); array_push($desktop_icons, $hash);
  
  $bar = array("level" => $player['level'], "tokens" => $player['tokens'], "servers" => $player['servers'], "rams" => $player['rams'], "average_servers_security" => $player['average_servers_security'], "hacked_servers" => $player['hacked_servers'], "bonus" => $account['bonus']);  
  echo json_encode(array("desktop_icons" => $desktop_icons, "bar" => $bar, "time" => time()));
?>