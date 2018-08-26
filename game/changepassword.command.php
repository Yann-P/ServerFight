<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */

// Attention, cette commande ne comporte pas d'argument

if(!isset($_SESSION['server']))
  response("failure", "Aucune connexion n'est établie.", false);
if($_SESSION['server']['ip'] == "localhost@".$_SESSION['pseudo'])
  response("failure", "Cette action n'est pas valable pour votre localhost.", false);
  
$current_code_length = strlen($_SESSION['server']['code']);

$new_server_code = random_string('123456789', $current_code_length);
$new_server_slug = random_string('azertyuiopqsdfghjklmwxcvbn', $current_code_length);

if(mysql_num_rows(mysql_query("SELECT * FROM servers WHERE code = '$new_server_code'")) != 0
|| mysql_num_rows(mysql_query("SELECT * FROM servers WHERE slug = '$new_server_slug'")) != 0)
  response("failure", "Des interférences ont empêché votre requête d'aboutir, rééssayez.", false);


if(!isset($_SESSION['last_code_changement']) || $_SESSION['last_code_changement'] < time() - (1 * 60)) {
  $_SESSION['server']['code'] = $new_server_code;
  $_SESSION['server']['slug'] = $new_server_slug;
  $_SESSION['last_code_changement'] = time();
  
  mysql_query("UPDATE servers SET code = '$new_server_code', slug = '$new_server_slug', player_id = {$player['id']} WHERE ip = '{$_SESSION['server']['ip']}'");

  if(isset($_SESSION['special_connect_permission'])) {
    $old_owner = retreive_player($_SESSION['server']['player_id']);
    $old_owner_account = retreive_account($old_owner['account_id']);
    

    $resource_site_hosted_by_server = mysql_query("SELECT * FROM sites WHERE hosted_on = '{$_SESSION['server']['ip']}'");
    if(mysql_num_rows($resource_site_hosted_by_server) == 1) {
      $site_hosted_by_server = mysql_fetch_array($resource_site_hosted_by_server);
      mysql_query("UPDATE sites SET player_id = {$player['id']}, hosted_on = '0' WHERE adress = '{$site_hosted_by_server['adress']}'");
      $message = "Votre serveur {$_SESSION['server']['ip']} a été piraté par {$_SESSION['pseudo']} malgré son niveau de sécrité de ".strlen($_SESSION['server']['code']).".<br />Il appartient désormais à ce joueur.<br />Comme une mauvaise nouvelle n'arrive jamais seule, votre site {$site_hosted_by_server['adress']} est parti avec ^__^";
    }
    else $message = "Votre serveur {$_SESSION['server']['ip']} a été piraté par {$_SESSION['pseudo']} malgré son niveau de sécrité de ".strlen($_SESSION['server']['code']).".<br />Il appartient désormais à ce joueur.";
    
    mysql_query("UPDATE players SET last_hacked_timestamp = '".time()."', last_hacked_player_id = '{$player['id']}' WHERE id = {$old_owner['id']}");
    
    mysql_query("INSERT INTO actions VALUES('', '{$old_owner['account_id']}', '%03', 'Serveur {$_SESSION['server']['ip']} piraté.<br /><br />".addslashes($message)."')");
    mysql_query("INSERT INTO actions VALUES('', '{$old_owner['account_id']}', '%03', 'Rappel : serveur {$_SESSION['server']['ip']} piraté.<br /><br />".addslashes($message)."')");
    unset($_SESSION['special_connect_permission']);
    
    $message_tchat = "Un serveur avec niveau de sécurité de ".strlen($_SESSION['server']['code'])." vient d'être piraté sur le jeu !";
    mysql_query("INSERT INTO messenger VALUES('', '0', '#sys', '".utf8_encode(addslashes($message_tchat))."', '0', '0')");
    
    recalculate_player_infos($old_owner['id']);
    mysql_query("UPDATE players SET hacked_servers = ".($player['hacked_servers']+1)." WHERE id = {$player['id']}");
    add_to_logs("Finalisation du piratage de {$_SESSION['server']['ip']} de {$old_owner_account['pseudo']}. (Changement du pass)");
    add_to_pub_logs("Un serveur vient d'être piraté !");
    response("success", "Félicitations. Ce serveur est maintenant à vous. Si il hébergait un site, vous l'avez récupéré ! Son ancien propriétaire a été prévenu.", false);
  }
  
  add_to_logs("Changement du mot de passe de {$_SESSION['server']['ip']}");
  response("success", "Vous avez modifié le code de ce serveur. Le code encrypté a donc également changé.", false);
}
else response("failure", "Merci de patienter au moins 10 minutes entre deux changements de code.", false);

?>