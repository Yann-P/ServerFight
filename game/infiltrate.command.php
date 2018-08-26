<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
$arg = explode(' ', $arguments, 2);

if (!isset($arg[0])) response("failure", "<b>Syntaxe :</b> infiltrate [ip]", false);
$ip = mysql_real_escape_string($arg[0]);

if(!isset($_SESSION['server']))
  response("failure", "Aucune connexion n'est établie.", false);
if($_SESSION['server']['ip'] == "localhost@".$_SESSION['pseudo'])
  response("failure", "Infiltrer un serveur avec votre localhost ? Vous voulez rire ?", false);
if($_SESSION['server']['time_worked'] + 5 > 10)
  response("failure", "Votre serveur a besoin de travailler 5 heures pour faire cela, et la température actuelle de votre serveur (".($_SESSION['server']['time_worked']*10)."%) ne le permet pas actuellement.", false);
if(mysql_num_rows(mysql_query("SELECT * FROM servers WHERE ip = '$ip'")) != 1)
  response("failure", "Résolution de l'adresse IP échouée.", false);
if($player['level'] < 25)
  response("failure", "Vous devez atteindre le niveau 25 pour pirater.", false);
  
$server = mysql_fetch_array(mysql_query("SELECT * FROM servers WHERE ip = '$ip'"));

$target = retreive_player($server['player_id']);
if($target['level'] < 25)
  response("failure", "Votre victime est d'un niveau trop faible pour être victime de piratage !", false);
if(abs($target['average_servers_security'] - $player['average_servers_security']) > 1)
  response("failure", "Le palier entre votre security level et celui du propriétaire du serveur est trop important (+/- 1).", false);
if(time() - 60*60*12 < $target['last_hacked_timestamp'])
  response("failure", "Ce joueur a déjà été attaqué il y a moins de 12 heures...", false);
if($target['last_hacked_player_id'] == $player['id'])
  response("failure", "Le dernier piratage ayant été subi par ce joueur était déjà de votre part.", false);
  
if($server['player_id'] == $player['id'])
  response("failure", "Impossible ! Ce serveur est à vous !", false);
if(preg_match('/^localhost(.+)$/', $server['ip'])) 
  response("failure", "Access denied !", false);
  
$slug = $server['slug'];

mysql_query("UPDATE servers SET time_worked = ".($_SESSION['server']['time_worked'] + 5)." WHERE ip = '{$_SESSION['server']['ip']}'");
$_SESSION['server']['time_worked'] += 5;

mysql_query("INSERT INTO infiltrations_history VALUES('', '{$player['id']}', '$ip', '$slug', '{$server['player_id']}')");

add_to_logs("Récupération du slug de $ip");
add_to_pub_logs("Un sevreur vient d'être infiltré !");

response( "success", "Votre serveur a travaillé 5 heures, est monté à ".($_SESSION['server']['time_worked']*10)."% de la température interne maximale.<br />"
        . "Le code crypté du serveur $ip pu être récupéré : $slug.<br />"
        . "Essayez de le décoder.", "reload_app('servercracker');");
       
?>