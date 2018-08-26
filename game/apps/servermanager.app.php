<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
   
  if(isset($_GET) && isset($_SESSION['id'])) {
?>
<style>
</style>
<script>
  if(typeof(animation_interval) != "undefined") clearInterval(animation_interval);
  var animation_interval;
  
  $('#app_servermanager .app_table')
    .find('tr:odd')
    .css('background', 'rgba(255, 255, 255, 0.2)');
    
  animation_interval = setInterval(function() {
    $('#app_servermanager img[src="design/icons/server_power.png"]').animate({'opacity':0.2}, 200, function() {
      $('#app_servermanager img[src="design/icons/server_power.png"]').animate({'opacity':1}, 200);
    });
  }, 500);
</script>
<div class="app" id="app_servermanager">
  <div class="box">
    <h3><img class="icon" src="design/icons/servermanager.png" />ServerManager v1.02 <img class="icon" src="design/icons/refresh_small.png" style="cursor:pointer;" onclick="reload_app('servermanager');" /></h3>
  </div>
  <table class="app_table" id="servers">
    <tr>
      <th></th>
      <th><img src="design/icons/temperature.png" title="Température interne" /></th>
      <th>IP</th>
      <th>RAMs</th>
      <th>Code</th>
      <th>Code crypté</th>
      <th>Niv. sécurité</th>
    </tr>
  <?php
    $resource_servers = mysql_query("SELECT * FROM servers WHERE player_id = {$player['id']}");
    while ($server = mysql_fetch_array($resource_servers)) {
      if($server['ip'] != "localhost@".$_SESSION['pseudo']) {
        if(isset($_SESSION['server']) && $server['ip'] == $_SESSION['server']['ip']) echo '<td><img src="design/icons/server_power.png" /></td>';
        else echo '<td title="Identifiant"><img title="Serveur n°'.$server['id'].'" src="design/icons/server.png" /></td>';
        echo '<td>'.(($server['time_worked']*10)).'%</td>';
        echo '<td>'.$server['ip'].'</td>';
        echo '<td>'.$server['rams'].' / '.($server['ram_containers']*100).'</td>';
        echo '<td><code>'.wordwrap($server['code'], 10, "<br />", true).'</code></td>';
        echo '<td><code>'.wordwrap($server['slug'], 10, "<br />", true).'</code></td>';
        echo '<td>'.strlen($server['code']).'</td>';
      }
      else {
        if(isset($_SESSION['server']) && $server['ip'] == $_SESSION['server']['ip']) echo '<td><img src="design/icons/server_power.png" /></td>';
        else echo '<td><img src="design/icons/server.png" /></td>';
        echo '<td>'.(($server['time_worked']*10)).'%</td>';
        echo '<td>localhost</td>';
        echo '<td>'.$server['rams'].' / 200</td>';
        echo '<td>-</td>';
        echo '<td>-</td>';
        echo '<td>Invulnérable</td>';
      }
      echo '</tr>';
    }
  ?>
  </table>
  <div class="box">
    <img class="icon" src="design/icons/information.png" />ServerManager ouvre les commandes suivantes en console :
    <ul>
      <li><code>connect localhost</code></li>
      <li><code>connect [ip] [code]</code></li>
      <li><code>disconnect</code></li> 
      <li><code>...</code></li> 
    </ul>
    <br />
    Aide disponible dans le Guide du hacker.
  </div>
  <br />
</div>

<?php
  }
?>