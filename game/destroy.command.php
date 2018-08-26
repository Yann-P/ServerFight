<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
if(!isset($_SESSION['server']))
  response("failure", "Aucune connexion n'est établie.", false);
if($_SESSION['server']['ip'] == "localhost@".$_SESSION['pseudo'])
  response("failure", "Détruire votre localhost<br />Hem.<br />Hem, hem... :tousse:<br />Suicidaire ?", false);

$server = mysql_fetch_array(mysql_query("SELECT * FROM servers WHERE ip = '{$_SESSION['server']['ip']}'"));

if($server['player_id'] != $player['id'])
  response("failure", "Ce serveur n'est pas à vous... si vous venez de le pirater et que vous voulez le détruire utilisez changepassword avant.", false);

if((isset($_SESSION['destroy_confirm']) && $_SESSION['destroy_confirm'] == false) || !isset($_SESSION['destroy_confirm'])) {
  $_SESSION['destroy_confirm'] = true;
  response("failure", "<span style='color:orange;'>Vous êtes sur le point de détrure définitivement ce serveur. Répétez la commande pour poursuivre tout de même.</span>", false);
}
else {
  $_SESSION['destroy_confirm'] = false;
  
  mysql_query("DELETE FROM servers WHERE ip = '{$_SESSION['server']['ip']}'");
  add_to_logs("Destroy de {$_SESSION['server']['ip']}");
  unset($_SESSION['server']);
  response("success", "Ce serveur a été réduit en fine poudre.", "reload_app('servermanager');");
}

?>