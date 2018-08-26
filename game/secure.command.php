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
$new_code_length = $current_code_length+1;
$required_rams = ($new_code_length*100)-600;

if($_SESSION['server']['rams'] < $required_rams)
  response("failure", "Vous pouvez augmenter le niveau du sécurité du serveur toutes les 100 RAMs que le serveur possède.", false);

$new_server_code = random_string('123456789', $new_code_length);
$new_server_slug = random_string('azertyuiopqsdfghjklmwxcvbn', $new_code_length);

if(mysql_num_rows(mysql_query("SELECT * FROM servers WHERE code = '$new_server_code'")) != 0
|| mysql_num_rows(mysql_query("SELECT * FROM servers WHERE slug = '$new_server_slug'")) != 0)
  response("failure", "Des interférences ont empêché votre requête d'aboutir, rééssayez.", false);

$_SESSION['server']['code'] = $new_server_code;
$_SESSION['server']['slug'] = $new_server_slug;

mysql_query("UPDATE servers SET code = '$new_server_code', slug = '$new_server_slug' WHERE ip = '{$_SESSION['server']['ip']}'");

add_to_logs("Sécurisation de {$_SESSION['server']['ip']} vers $new_code_length caractères");
add_to_pub_logs("Un serveur vient d'être sécurisé par son propriétaire.");

response("success", "Vous avez sécurisé le code de ce serveur. Sa longueur est maintenant de $new_code_length et vous avez considérablement moins de chances de vous faire pirater désormais !<br />Par conséquent les codes du serveur ont changé.", "reload_app('servermanager');");

?>