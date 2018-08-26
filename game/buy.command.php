<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */

$arg = explode(' ', $arguments, 4);

$to_buy = mysql_real_escape_string($arg[0]);

if($to_buy == "server") {

  if (isset($arg[1])) response("failure", "<b>Syntaxe :</b> buy server | buy ram [quantité] | buy ramcomtainer", false);
  
  if($player['servers'] == 1) $price = 500;
  else $price = 5000;
  
  if(mysql_num_rows(mysql_query("SELECT * FROM servers WHERE player_id = {$player['id']}")) >= 20)
    response("failure", "Vous ne pouvez pas avoir plus de 20 serveurs.", false);  
  if($player['tokens'] < $price) {
    if($player['servers'] == 1) response("failure", "Il vous faut $price tokens pour vous acheter votre premier serveur.", false);
    else response("failure", "Il vous faut au $price tokens pour vous acheter un autre serveur.", false);
  }
    
  
  $new_server_ip = rand(0, 255).'.'.rand(0, 255).'.'.rand(0, 255).'.'.rand(0, 255);
  $new_server_code = random_string('123456789', 6);
  $new_server_slug = random_string('azertyuiopqsdfghjklmwxcvbn', 6);
  if(  mysql_num_rows(mysql_query("SELECT * FROM servers WHERE ip = '$new_server_ip'")) != 0
    || mysql_num_rows(mysql_query("SELECT * FROM servers WHERE code = '$new_server_code'")) != 0
    || mysql_num_rows(mysql_query("SELECT * FROM servers WHERE slug = '$new_server_slug'")) != 0) {
    response("failure", "Des interférences ont empêché votre requête d'aboutir, rééssayez.", false);
  }
  
  mysql_query("UPDATE players SET tokens = ".($player['tokens']-$price)." WHERE id = {$player['id']}");
  mysql_query("INSERT INTO servers VALUES('', '{$player['id']}', '$new_server_ip', '$new_server_code', '$new_server_slug', '5', '1', '0')");
  unset($_SESSION['server']);
  
  add_to_logs("Achat de serveur à $new_server_ip");
  add_to_pub_logs("{$_SESSION['pseudo']} s'est approprié un nouveau serveur !");
  
  response(  "success", "Vous avez acqui un nouveau serveur !<br />"
           . "Une IP lui a été attribuée : $new_server_ip<br />"
           . "Son niveau de sécurité initial est 6.<br />"
           . "Il a été ajouté à ServerManager.", "reload_app('servermanager');");

}
elseif($to_buy == "ram") {

  if (!isset($arg[1])) response("failure", "<b>Syntaxe :</b> buy server | buy ram [quantité] | buy ramcomtainer", false);
  $quantity  = round(mysql_real_escape_string($arg[1]));
  
  $price = 100 * $quantity;

  if(!isset($_SESSION['server']))
    response("failure", "Aucune connexion n'est établie. Veuillez vous connecter à un serveur.", false);
  if(!is_numeric($quantity) || $quantity <= 0)
    response("failure", "La quantité spécifiée n'est pas correcte.", false);
  if($player['tokens'] < $price)
    response("failure", "Vous n'avez pas assez d'argent, sachant qu'une RAM coute 100 tokens.", false);
  if($_SESSION['server']['rams'] + $quantity > ($_SESSION['server']['ram_containers']*100))
    response("failure", "Votre serveur ne peut pas recevoir plus de RAM. Achetez des RAM containers.", false);
    
  mysql_query("UPDATE players SET tokens = ".($player['tokens'] - $price)." WHERE id = {$player['id']}");
  mysql_query("UPDATE servers SET rams = ".($_SESSION['server']['rams'] + $quantity)." WHERE ip = '{$_SESSION['server']['ip']}'");
  $_SESSION['server']['rams'] += $quantity;
  
  add_to_logs("Achat de $quantity RAM au serveur {$_SESSION['server']['ip']}");
  add_to_pub_logs("{$_SESSION['pseudo']} a équipé son serveur en RAM.");
  
  response(  "success", "Vous avez acquis $quantity RAM<br />"
         . "$price tokens vous ont été débités.<br />"
         . "IP du serveur destinataire : {$_SESSION['server']['ip']}", "reload_app('servermanager');");
}
elseif($to_buy == "ramcontainer") {

  $price = 1000 * $_SESSION['server']['ram_containers'];
  
  if(!isset($_SESSION['server']))
    response("failure", "Aucune connexion n'est établie. Veuillez vous connecter à un serveur.", false);
  if($_SESSION['server']['ip'] == "localhost@".$_SESSION['pseudo'])
    response("failure", "Vous ne pouvez pas augmenter la capacité de votre localhost.", false);
  if($player['tokens'] < $price)
    response("failure", "Vous n'avez pas assez d'argent, sachant qu'un RAM container coûte 1000 multiplé par la quantité de RAM containers que vous possédez déjà. Il vous faut $price tokens.", false);
    
  mysql_query("UPDATE players SET tokens = ".($player['tokens'] - $price)." WHERE id = {$player['id']}");
  mysql_query("UPDATE servers SET ram_containers = ".($_SESSION['server']['ram_containers'] + 1)." WHERE ip = '{$_SESSION['server']['ip']}'");
  $_SESSION['server']['ram_containers'] += 1;
  
  add_to_logs("Achat d'un RAM container au serveur {$_SESSION['server']['ip']}");
  add_to_pub_logs("{$_SESSION['pseudo']} a agrandi son serveur.");
  
  response(  "success", "Vous avez acquis 1 nouveau RAM container. Vous pouvez maintenant mettre jusqu'à ".($_SESSION['server']['ram_containers']*100)." RAM sur votre serveur.<br />"
           . "$price tokens vous ont été débités.<br />"
           . "IP du serveur destinataire : {$_SESSION['server']['ip']}", "reload_app('servermanager');");
}  
else response("failure", "Vérifiez votre requête.", false);
?>