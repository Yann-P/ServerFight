<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
   
  if(isset($_GET) && isset($_SESSION['id'])) {
    $player = retreive_player($_SESSION['id']); 
    if($player['servers'] < 2) {
      exit('<div class="app" id="app_decryptlab"><div class="box"><h3><img class="icon" src="design/asterisk.png" />Serveur autre que localhost requis !</h3><br />Pour ouvrir votre historique des piratages, et dans le cas général, pour : <ul><li>Commencer à sécuriser vos serveurs</li><li>Pirater les serveurs des autres</li></ul>, vous devez posséder un serveur autre que localhost.<br /><br />Pour cela, utilisez la commande buy server. Il est recommandé de suivre le guide en haut à droite et de ne pas en acheter trop tôt. Vous seriez aussitôt piraté par un autre joueur.</div>');
    }
?>
<style>
</style>
<script>
  $('#app_servercracker .app_table')
    .find('tr:odd')
    .css('background', 'rgba(255, 255, 255, 0.2)');
</script>
<div class="app" id="app_history">
  <div class="box">
    <h3><img class="icon" src="design/icons/history.png" />Historique des piratages <img class="icon" src="design/icons/refresh_small.png" style="cursor:pointer;" onclick="reload_app('servercracker');" /></h3>
  </div>
  <div class="box">
    <b>Voici les 10 derniers codes de serveurs infiltrés.</b>
  </div>  
    <table class="app_table" id="infiltrations_history">
    <tr>
      <th>IP</th>
      <th>Code crypté trouvé</th>
      <th>Appartient à</th>
    </tr>
    <?php
      $resource_history_entries = mysql_query("SELECT * FROM infiltrations_history WHERE player_id = {$player['id']} ORDER BY id ASC LIMIT 10");
      while ($history_entry = mysql_fetch_array($resource_history_entries)) {
        $owner = retreive_player($history_entry['owner']);
        $owner_account = retreive_account($owner['account_id']);
        echo '<tr>';
        echo '<td>'.$history_entry['ip'].'</td>';
        echo '<td>'.$history_entry['slug'].'</td>';
        echo '<td>'.grade_pseudo($owner_account['pseudo'], $owner_account['grade']).'</td>';
        echo '</tr>';
      }
    ?>
  </table>
  <br />
</div>

<?php
  }
?>