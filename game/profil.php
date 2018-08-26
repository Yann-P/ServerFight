<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
  session_start();
  require_once("../socle.php");
  
  if(!check_session()) exit;
  
  if(isset($_POST['pseudo'])) {
    $pseudo = mysql_real_escape_string($_POST['pseudo']);
    if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE BINARY pseudo = '$pseudo'")) == 1) {
      $account = retreive_account($pseudo, true);
      $player = retreive_player($account['id']);
      ?>
        <style>
        </style>
        <script>
          $('#app_profil .app_table')
            .find('tr:odd')
            .css('background', 'rgba(255, 255, 255, 0.2)');
        </script>
        <div class="app" id="app_view_profil" data-name="view_profil">
          <?php
          if($_SESSION['grade'] > 1 && $account['cheat']==1) {
          ?>
          <div class="box"><span style="color:red;font-size:20px">Ce joueur a déjà utilisé un bot de cheat.</span></div>
          <?php
        }
          ?>
          <div class="box">
            <center>
              <h2><img class="icon" src="design/icons/user_silhouette.png" /><?php echo $account['pseudo']; ?></h2>
            </center>
            <hr />
            <ul>
              <li><b>Date d'inscription :</b> <?php echo $account['sign_in_timestamp']?date('d/m/Y à H:i', $account['sign_in_timestamp']):"Non défini"; ?></li>
              <li><b>Dernière connexion :</b> <?php echo $account['last_log_in_timestamp']?date('d/m/Y à H:i', $account['last_log_in_timestamp']):"Non défini"; ?></li>
              <br />
              <li><b>Niveau :</b> <?php echo $player['level']; ?></li>
              <li><b>Security level :</b> <?php echo $player['average_servers_security']; ?></li>
              <li><b>Nombre de serveurs :</b> <?php echo $player['servers']; ?></li>
            </ul>
          </div>
          <?php
            if($_SESSION['grade'] > 1) {
              ?>
          <div class="box">
            <h3><img class="icon" src="design/icons/bullet_arrow.png" />Modération</h3><br />
            ID player = <?php echo $player['id'];?> account = <?php echo $account['id'];?><br />
            <br />
            <b>IP d'inscription</b> <?php echo $account['sign_in_ip'];?><br />
            <b>IP dernière connexion</b> <?php echo $account['last_log_in_ip']; ?><br />
            <br />
            <b>UserAgent inscription</b> <?php echo htmlentities($account['sign_in_user_agent']); ?><br />
            <b>UserAgent dernière connexion</b> <?php echo htmlentities($account['last_log_in_user_agent']); ?><br />
            <br />
            <b>Email</b> <?php echo $account['email']; ?><br />
            </ul>
          </div>
              <?php
            }
          ?>
          <br />
          <h3>Ses serveurs</h3><br />
          <table class="app_table" id="servers">
            <tr>
              <th>&nbsp;</th>
              <th>IP</th>
              <th>Niv. sécurité</th>
            </tr>
          <?php
            $resource_servers = mysql_query("SELECT * FROM servers WHERE player_id = {$player['id']}");
            while ($server = mysql_fetch_array($resource_servers)) {
              echo '<tr>';
              if($server['ip'] != "localhost@".$account['pseudo']) {
                echo '<td><img src="design/icons/server.png" /></td>';
                echo '<td>'.$server['ip'].'</td>';
                echo '<td>'.strlen($server['code']).'</td>';
              }
              echo '</tr>';
            }
          ?>
          </table>
          <br />
          <h3>Ses sites</h3><br />
          <table class="app_table" id="sites">
            <tr>
              <th>Adresse</th>
              <th>Hébergé sur</th>
              <th>Pointé</th>
            </tr>
          <?php
            $resource_sites = mysql_query("SELECT * FROM sites WHERE hosted_on != '0' AND player_id = {$player['id']} ORDER BY nb_links DESC");
            while($site = mysql_fetch_array($resource_sites)) {
              $owner = retreive_player($site['player_id']);
              $owner_account = retreive_account($owner['account_id']);
              echo '<tr>';
              echo '<td><a href="#" onclick="go_site(\''.$site['adress'].'\')">'.$site['adress'].'</a></td>';
              echo '<td><a href="#" onclick="go_site(\''.$site['hosted_on'].'\')">'.$site['hosted_on'].'</a></td>';
              echo '<td>'.$site['nb_links'].' fois</td>';
              echo '</tr>';
            }
          ?>
          </table>
          <br />
        </div>
      <?php
    }
  }
  
?>  