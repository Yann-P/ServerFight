<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */

$arg = explode(' ', $arguments, 2);

if (!isset($arg[0]) || !isset($arg[1])) response("failure", "[Syntaxe] bind [raccourci] [commande]", false);
$shortcut = mysql_real_escape_string($arg[0]);
$command = mysql_real_escape_string($arg[1]);

if(strlen($shortcut) > 15 || strlen($shortcut) < 2) response("failure", "Raccourci : 2-15 caractères.", false);
if(strlen($command) > 150 || strlen($command) < 4) response("failure", "Commande : 3-150 caractères.", false);

$resource_bind = mysql_query("SELECT * FROM bind WHERE player_id = {$player['id']} AND shortcut = '$shortcut'");

if(mysql_num_rows($resource_bind) != 0) {
  if($command == "null") {
    mysql_query("DELETE FROM bind WHERE player_id = {$player['id']} AND shortcut = '$shortcut'");
    response("success", "Le raccourci ${$shortcut} a été supprimé.", false);
  }
  else {
    mysql_query("UPDATE bind SET command = '$command' WHERE player_id = {$player['id']} AND shortcut = '$shortcut'");
    response("success", "Le raccourci ${$shortcut} a été modifié pour la commande '{$command}'.", false);
  }
}
else {
  if(mysql_num_rows($resource_bind) > 25) response("failure", "Vous ne pouvez créer plus de 25 raccourcis.", false);
  mysql_query("INSERT INTO bind VALUE('', '{$player['id']}', '$shortcut', '$command')");
  response("success", "Le raccourci $".$shortcut." a été créé et associé à la commande '{$command}'.", false);
}
?>