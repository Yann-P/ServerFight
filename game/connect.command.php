<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
$arg = explode(' ', $arguments, 2);

if (!isset($arg[0])) response("failure", "<b>Syntaxe :</b> connect localhost | connect [ip] [code]", false);
$ip = mysql_real_escape_string($arg[0]);

if(isset($_SESSION['server'])) response("failure", "Vous êtes déjà connecté à un serveur. Utilisez 'disconnect'.", false);

if($ip == "localhost") {
  $server = mysql_fetch_array(mysql_query("SELECT * FROM servers WHERE ip = 'localhost@".$_SESSION['pseudo']."'"));
  $_SESSION['server'] = array(
    "player_id" => $server['player_id'],
    "ip" => $server['ip'],
    "rams" => $server['rams'],
    "ram_containers" => 2,
    "time_worked" => $server['time_worked']
  );
  
  add_to_logs("Connexion à localhost");
  response("success", "Connecté à votre serveur local.<br /><span style='color:orange;'>{</span> <br />&nbsp;&nbsp;Sécurité [invulnérable]<br />&nbsp;&nbsp;RAM [{$server['rams']}]<br />&nbsp;&nbsp;Température interne [".($server['time_worked']*10)."%]<br /><span style='color:orange;'>}</span>", "reload_app('servermanager');");
}

if(!isset($arg[1])) response("failure", "[Syntaxe] connect localhost | connect [ip] [code]", false);
$code = mysql_real_escape_string($arg[1]);
$code = str_replace(' ', '', $code);


if(mysql_num_rows(mysql_query("SELECT * FROM servers WHERE ip = '$ip'")) != 1)
  response("failure", "Résolution de l'adresse IP échouée.", false);
  
$server = mysql_fetch_array(mysql_query("SELECT * FROM servers WHERE ip = '$ip'"));

if($server['code'] != trim($code))
  response("failure", "Accès refusé.", false);

if($server['player_id'] != $player['id'] && !isset($_SESSION['special_connect_permission']))
  response("failure", "Accès refusé, vous n'avez pas piraté ce serveur.", false);
  
$_SESSION['server'] = array(
  "player_id" => $server['player_id'],
  "ip" => $server['ip'],
  "code" => $server['code'],
  "slug" => $server['slug'],
  "rams" => $server['rams'],
  "ram_containers" => $server['ram_containers'],
  "time_worked" => $server['time_worked']
);

add_to_logs("Connexion à $ip.");
add_to_pub_logs("{$_SESSION['pseudo']} vient de se connecter à un de ses serveurs.");

response("success", "Connecté à $ip.<br /><span style='color:orange;'>{</span><br />&nbsp;&nbsp;Sécurité [".strlen($server['code'])."]<br />&nbsp;&nbsp;RAM [{$server['rams']}]<br />&nbsp;&nbsp;RAM containers [{$server['ram_containers']}] <br />&nbsp;&nbsp;Température interne [".($server['time_worked']*10)."%]<br /><span style='color:orange;'>}</span>", "reload_app('servermanager');");
?>