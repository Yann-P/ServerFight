<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
$arg = explode(' ', $arguments, 2);

if (!isset($arg[0]) || !isset($arg[1])) response("failure", "[Syntaxe] crack [code crypté] [temps de travail]", false);

$slug = mysql_real_escape_string($arg[0]);
$time_to_work = mysql_real_escape_string($arg[1]);

if(!isset($_SESSION['server']))
  response("failure", "Aucune connexion n'est établie.", false);
if($_SESSION['server']['ip'] == "localhost@".$_SESSION['pseudo'])
  response("failure", "Cette action n'est pas disponible sous votre localhost. Vous avez besoin d'un autre serveur.", false);
if(mysql_num_rows(mysql_query("SELECT * FROM servers WHERE slug = '$slug'")) != 1)
  response("failure", "Ce code crypté n'est attribué à aucun serveur.", false);
if(!is_numeric($time_to_work) || $time_to_work <= 0 || $time_to_work > 10)
  response("failure", "Le temps de travail du serveur est incorrect.", false);
if($_SESSION['server']['time_worked'] + $time_to_work > 10) {
  if($_SESSION['server']['time_worked'] == 10) response("failure", "La température interne du serveur est au maximum.", false);
  else response("failure", "Vous pouvez encore faire travailler votre serveur ".(10-$_SESSION['server']['time_worked'])." heures pour arriver à 100% de la température interne actuellement à ".($_SESSION['server']['time_worked']*10)."%.", false);
}
if($player['level'] < 25)
  response("failure", "Vous devez atteindre le niveau 25 pour pirater.", false);
  
$server = mysql_fetch_array(mysql_query("SELECT * FROM servers WHERE slug = '$slug'"));

$target = retreive_player($server['player_id']);
if($server['player_id'] == $player['id'])
  response("failure", "Impossible ! Ce serveur est à vous !", false);
if($target['level'] < 25)
  response("failure", "Votre victime est d'un niveau trop faible pour être victime de piratage !", false);
if(abs($target['average_servers_security'] - $player['average_servers_security']) > 1)
  response("failure", "Le palier entre votre security level et celui du propriétaire du serveur est trop important. (+/- 1)", false);
if(preg_match('/^localhost(.+)$/', $server['ip'])) 
  response("failure", "Access denied ! (mais bien essayé)", false);
if(time() - 60*60*12 < $target['last_hacked_timestamp'])
  response("failure", "Ce joueur a déjà été attaqué il y a moins de 12 heures...", false);
if($target['last_hacked_player_id'] == $player['id'])
  response("failure", "Le dernier piratage ayant été subi par ce joueur était déjà de votre part.", false);
  
$code = $server['code'];
$ip = $server['ip'];

mysql_query("UPDATE servers SET time_worked = ".($_SESSION['server']['time_worked'] + $time_to_work)." WHERE ip = '{$_SESSION['server']['ip']}'");
$_SESSION['server']['time_worked'] += $time_to_work;

$chance = round((strlen($slug)*strlen($slug)*strlen($slug) - $_SESSION['server']['rams']) / $time_to_work);
$tirage = rand(0, $chance);

if($tirage != 0 && $chance > 0) {
  add_to_logs("FAILED Tentative de décryptage du code de $ip pendant $time_to_work.");
  add_to_pub_logs("Un serveur vient de subir une attaque !");
  
  response( "failure", "Tentative de décryptage du code de $ip (niveau de sécurité : ".strlen($slug).") pendant $time_to_work. La température interne de serveur est montée à ".($_SESSION['server']['time_worked']*10)."%.<br />"
          . "Votre serveur n'a pas réussi a décrypter le code cette fois.<br />"
          . "Améliorez vos chances en équipant votre serveur de RAM.", false);
}
else {
  $_SESSION['special_connect_permission'] = $ip;
  add_to_logs("Réussite du décryptage du code de $ip pendant $time_to_work");
  response( "success", "Tentative de décryptage du code de $ip (niveau de sécurité : ".strlen($slug).") pendant $time_to_work. La température interne de serveur est montée à ".($_SESSION['server']['time_worked']*10)."%.<br />"
          . "Vous avez réussi à pirater ce serveur !<br />"
          . "Voici son code : $code <br />"
          . "Vous pouvez maintenant vous connecter dessus, changer son mot de passe et vous l'approprier. Soyez rapide.", false);
}

       
?>