<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
$arg = explode(' ', $arguments, 2);

if (!isset($arg[0])) response("failure", "<b>Syntaxe :</b> work [temps de travail]", false);
$time_to_work = round(mysql_real_escape_string($arg[0]));

if(!isset($_SESSION['server']))
  response("failure", "Aucune connexion n'est établie.", false);

if(!is_numeric($time_to_work) || $time_to_work <= 0 || $time_to_work > 10)
  response("failure", "Le temps de travail du serveur est incorrect.", false);
if($_SESSION['server']['time_worked'] + $time_to_work > 10) {
  if($_SESSION['server']['time_worked'] == 10) response("failure", "La température interne du serveur est au maximum.", false);
  else response("failure", "Vous pouvez encore faire travailler votre serveur ".(10-$_SESSION['server']['time_worked'])." heures pour arriver à 100% de la température interne actuellement à ".($_SESSION['server']['time_worked']*10)."%.", false);
}

$money = round((( $player['level']/2 + $_SESSION['server']['rams']*2 ) / ($player['servers']/2)) * $time_to_work * 1.5);

mysql_query("UPDATE servers SET time_worked = ".($_SESSION['server']['time_worked'] + $time_to_work)." WHERE ip = '{$_SESSION['server']['ip']}'");
mysql_query("UPDATE players SET tokens = ".($player['tokens']+$money)." WHERE id = {$player['id']}");
$_SESSION['server']['time_worked'] += $time_to_work;

add_to_logs("Travail du serveur {$_SESSION['server']['ip']} pendant $time_to_work.");
add_to_pub_logs("Un serveur vient de travailler $time_to_work heures.");

response( "success", "Votre serveur ".$_SESSION['server']['ip']." a travaillé $time_to_work heures, est monté à ".($_SESSION['server']['time_worked']*10)."% de la température maximum "
        . "et vous a rapporté $money tokens.", "reload_app('servermanager');");

?>