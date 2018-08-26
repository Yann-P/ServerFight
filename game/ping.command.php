<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
$arg = explode(' ', $arguments, 2);

if (!isset($arg[0])) response("failure", "[Syntaxe] ping [ip]", false);
$ip = mysql_real_escape_string($arg[0]);
  
$resource_server = mysql_query("SELECT * FROM servers WHERE ip = '$ip'");
$server = mysql_fetch_array($resource_server);

if(mysql_num_rows($resource_server) != 0 && !preg_match('/^localhost(.+)$/', $server['ip'])) {
  $owner = retreive_player($server['player_id']);
  $owner_account = retreive_account($owner['account_id']);
  response("success", "Le serveur $ip appartient à {$owner_account['pseudo']}. Il possède un niveau de sécurité de ".strlen($server['code']).".", false);
}
else response("failure", "Le serveur n'a pas répondu.", false);
?>