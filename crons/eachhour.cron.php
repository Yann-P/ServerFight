<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
  require_once("socle.php"); // change path or copy file
  add_to_logs("Cron // -1 heure");
  
  //Retrait de 10% de temp�rature
  
  $resource_servers = mysql_query("SELECT * FROM servers");
  while($server = mysql_fetch_array($resource_servers)) {
    if($server['time_worked'] != 0) mysql_query("UPDATE servers SET time_worked = ".($server['time_worked']-1)." WHERE id = {$server['id']}");
  }
  
  //M�J de l'ordre d'apparition des sites
  
  $resource_sites = mysql_query("SELECT * FROM sites WHERE hosted_on != '0'");
  while($site = mysql_fetch_array($resource_sites)) {
    $resource_sites_internal = mysql_query("SELECT * FROM sites");
    $counter = 0;
    while($site_internal = mysql_fetch_array($resource_sites_internal)) {
      if(preg_match('/_'.$site['adress'].'_/', $site_internal['content'])) {
        $counter++; 
      }
    }
    mysql_query("UPDATE sites SET nb_links = '$counter' WHERE adress = '{$site['adress']}'");
  }
  
  // Supression des salles de decryptlab abandonn�es
  
  $resource_dead_decryptlab_rooms = mysql_query("SELECT * FROM decryptlab_rooms WHERE won_by = '-1' AND revelation_timestamp < ".(time()-60*60));
  while($dead_decryptlab_room = mysql_fetch_array($resource_dead_decryptlab_rooms)) {
    mysql_query("DELETE FROM decryptlab_rooms WHERE id = {$dead_decryptlab_room['id']}");
  }
?>
