<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
$arg = explode(' ', $arguments, 2);

if (!isset($arg[0])) response("failure", "[Syntaxe] download [nom de l'application]", false);
$to_download = mysql_real_escape_string($arg[0]);

if($to_download == "messenger") {
  if(mysql_num_rows(mysql_query("SELECT * FROM applications_by_players WHERE player_id = {$player['id']} AND application_id = 1")) == 0) {
    mysql_query("INSERT INTO applications_by_players VALUES('', '1', '{$player['id']}')");
    response("success", "Téléchargement [100%]", "get_desktop(true); dialog('Messenger', 'Cette application est désormais installée sur votre WebOS !'); ");
  }
  else response("failure", "Vous avez déjé téléchargé cette application.", false);
}
elseif($to_download == "servermanager") {
  if(mysql_num_rows(mysql_query("SELECT * FROM applications_by_players WHERE player_id = {$player['id']} AND application_id = 2")) == 0) {
    mysql_query("INSERT INTO applications_by_players VALUES('', '2', '{$player['id']}')");
    response("success", "Téléchargement [100%]", "get_desktop(true); dialog('ServerManager', 'Cette application est désormais installée sur votre WebOS !'); ");
  }
  else response("failure", "Vous avez déjé téléchargé cette application.", false);
}
elseif($to_download == "servercracker") {
  if(mysql_num_rows(mysql_query("SELECT * FROM applications_by_players WHERE player_id = {$player['id']} AND application_id = 3")) == 0) {
    mysql_query("INSERT INTO applications_by_players VALUES('', '3', '{$player['id']}')");
    response("success", "Téléchargement [100%]", "get_desktop(true); dialog('ServerCracker', 'Cette application est désormais installée sur votre WebOS !')"); 
  }
  else response("failure", "Vous avez déjé téléchargé cette application.", false);
}
elseif($to_download == "browser") {
  if(mysql_num_rows(mysql_query("SELECT * FROM applications_by_players WHERE player_id = {$player['id']} AND application_id = 4")) == 0) {
    mysql_query("INSERT INTO applications_by_players VALUES('', '4', '{$player['id']}')");
    response("success", "Téléchargement [100%]", "get_desktop(true); dialog('Navigateur', 'Cette application est désormais installée sur votre WebOS !')"); 
  }
  else response("failure", "Vous avez déjé téléchargé cette application.", false);
}
elseif($to_download == "decryptlab") {
  if(mysql_num_rows(mysql_query("SELECT * FROM applications_by_players WHERE player_id = {$player['id']} AND application_id = 5")) == 0) {
    mysql_query("INSERT INTO applications_by_players VALUES('', '5', '{$player['id']}')");
    response("success", "Téléchargement [100%]", "get_desktop(true); dialog('DecryptLab', 'Cette application est désormais installée sur votre WebOS !')"); 
  }
  else response("failure", "Vous avez déjé téléchargé cette application.", false);
}
else response("failure", "Cette application n'a pas été trouvée...", false);
?>