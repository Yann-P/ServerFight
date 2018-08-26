<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
// Attention, cette commande ne comporte pas d'argument
// Récupère 5 serveurs au hasard

$resource_players_in_range = mysql_query("SELECT * FROM players WHERE level >= 25 AND average_servers_security <= ".($player['average_servers_security']+1)." AND average_servers_security >= ".($player['average_servers_security']-1));
$targets = array();
while($player_in_range = mysql_fetch_array($resource_players_in_range)) {
  $resource_servers = mysql_query("SELECT * FROM servers WHERE player_id = {$player_in_range['id']}");
  while($server = mysql_fetch_array($resource_servers)) {
    if(!preg_match('/^localhost(.+)$/', $server['ip'])) {
      $owner = retreive_account($player_in_range['account_id']);
      if($owner['id'] != $_SESSION['id']) {
        $hash = array(
          "ip" => $server['ip'],
          "owner" => $owner['pseudo']
        );
        array_push($targets, $hash);
      }
    }  
  }
}

if(sizeof($targets) >= 3) {
  $response = 'Voici quelques serveurs appartenant à des joueurs proches de votre security level : <br />';
  $rand_keys = array_rand($targets, 3);
  
  $response .= $targets[$rand_keys[0]]['ip']." appartenant à ".$targets[$rand_keys[0]]['owner']."<br />";
  $response .= $targets[$rand_keys[1]]['ip']." appartenant à ".$targets[$rand_keys[1]]['owner']."<br />";
  $response .= $targets[$rand_keys[2]]['ip']." appartenant à ".$targets[$rand_keys[2]]['owner']."<br />";
  
  response('success', $response, false);
}
else if(sizeof($targets) == 2) {
  $response = 'Voici quelques serveurs appartenant à des joueurs proches de votre security level : <br />';
  $rand_keys = array_rand($targets, 2);
  
  $response .= $targets[$rand_keys[0]]['ip']." appartenant à ".$targets[$rand_keys[0]]['owner']."<br />";
  $response .= $targets[$rand_keys[1]]['ip']." appartenant à ".$targets[$rand_keys[1]]['owner']."<br />";
  
  response('success', $response, false);
}
else if(sizeof($targets) == 1) {
  $response = 'Voici un serveur appartenant à un joueur proche de votre security level : <br />';
  
  $response .= $targets[0]['ip']." appartenant à ".$targets[0]['owner']."<br />";
  
  response('success', $response, false);
}
response('failure', "Vous n'avez personne avec un security level proche de vous (+/- 25 niveaux) pour le moment, ou bien ces personnes n'ont pas encore de serveur...", false);



?>