<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
  if(isset($_POST['ajax'])) {
    session_start();
    require_once("../../socle.php");
    if(!check_session() || $_SESSION['grade'] != 3) exit("Vous n'êtes pas administrateur");
    
    if(isset($_POST['del'])) {
      if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE pseudo = '{$_POST['del']}'")) == 0) exit;
      $target = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '{$_POST['del']}'"));
      $target_player = mysql_fetch_array(mysql_query("SELECT * FROM players WHERE account_id = '{$target['id']}'"));
      mysql_query("DELETE FROM servers WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM applications_by_players WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM actions WHERE account_id={$target['id']}");
      mysql_query("DELETE FROM sites WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM infiltrations_history WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM bind WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM decryptlab_rooms WHERE creator_id={$target_player['id']}");
      mysql_query("DELETE FROM messenger WHERE account_id={$target['id']}");
      mysql_query("DELETE FROM players WHERE id={$target_player['id']}");
      mysql_query("DELETE FROM accounts WHERE id={$target['id']}");
    }
    
    if(isset($_POST['connect'])) {
      $pseudo = $_POST['connect'];
      $resource_account = mysql_query("SELECT * FROM accounts WHERE BINARY pseudo = '$pseudo'");
      $account = mysql_fetch_array($resource_account);
      if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE BINARY pseudo = '$pseudo'")) != 0) {
        session_unset();
        $_SESSION['id'] = $account['id'];
        $_SESSION['pseudo'] = $pseudo;
        $_SESSION['grade'] = $account['grade'];
      }
      exit;
    }
    
    if(isset($_POST['raz'])) {
      if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE pseudo = '{$_POST['raz']}'")) == 0) exit;
      $target = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '{$_POST['raz']}'"));
      $target_player = mysql_fetch_array(mysql_query("SELECT * FROM players WHERE account_id = '{$target['id']}'"));
      
      mysql_query("DELETE FROM servers WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM applications_by_players WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM actions WHERE account_id={$target['id']}");
      mysql_query("DELETE FROM sites WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM infiltrations_history WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM bind WHERE player_id={$target_player['id']}");
      mysql_query("DELETE FROM decryptlab_rooms WHERE creator_id={$target_player['id']}");
      mysql_query("DELETE FROM messenger WHERE account_id={$target['id']}");
      
      mysql_query("INSERT INTO servers VALUES('', '{$target_player['id']}', 'localhost@{$_POST['raz']}', '-', '-', '5', '1', '0')");
      mysql_query("UPDATE players SET level = 1, tokens = 500, servers = 1, rams = 5, average_servers_security = 1, mission_id = 1, hacked_servers = 1 WHERE id={$target_player['id']}");
      exit;
    }
    
    elseif(isset($_POST['send_action']) && isset($_POST['target']) && isset($_POST['action']) && isset($_POST['message'])) {
      if($_POST['target'] == '*all') {
        $resource_all = mysql_query("SELECT * FROM accounts");
        while($member_account = mysql_fetch_array($resource_all)) {
          mysql_query("INSERT INTO actions VALUES('', '{$member_account['id']}', '{$_POST['action']}', '{$_POST['message']}')");
        }  
        echo 1;
      }
      elseif($_POST['target'] == '*logged') {
        $resource_accounts_online = mysql_query("SELECT * FROM accounts WHERE last_activity_timestamp > ".(time() - 30));
        while($account_online = mysql_fetch_array($resource_accounts_online)) {
          mysql_query("INSERT INTO actions VALUES('', '{$account_online['id']}', '{$_POST['action']}', '{$_POST['message']}')");
        }
        echo 2;
      }
      else {
        $target = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '{$_POST['target']}'"));
        mysql_query("INSERT INTO actions VALUES('', '{$target['id']}', '{$_POST['action']}', '{$_POST['message']}')");
        echo $target['id'];
      }
      exit;
    }
    
    elseif(isset($_POST['logs'])) {
      $logs = array();
      $resource_logs = mysql_query("SELECT * FROM logs ORDER BY id DESC LIMIT 50");
      
      $html .= '<table cellpadding="5" cellspacing="0">';
      $html .= '<tr><th>Date</th><th>Pseudo</th><th>Message</th><th>Session du serveur</th></tr>';
      while($log = mysql_fetch_array($resource_logs)) {
        $html .= '<tr>';
        $html .= '<td>'.$log['date'].'</td>';
        $html .= '<td>'.$log['pseudo'].'</td>';
        $html .= '<td>'.$log['message'].'</td>';
        //$html .= '<td>'.$log['account_session'].'</td>';
        $html .= '<td>'.$log['server_session'].'</td>';
        $html .= '</tr>';
      }
      $html .= '</table>';
      echo $html;
      exit;
    }
    elseif(isset($_POST['servers'])) {
      $servers = array();
      $resource_servers = mysql_query("SELECT * FROM servers ORDER BY id DESC LIMIT 50");

      $html .= '<table cellpadding="5" cellspacing="0">';
      $html .= '<tr><th>Prporiétaire</th><th>IP</th><th>Code et sécu</th><th>Slug</th><th>RAMs</th><th>RAM containers</th><th>Temps de travail</th></tr>';
      while($server = mysql_fetch_array($resource_servers)) {
        $owner = retreive_account($server['player_id']);
        $html .= '<tr>';
        $html .= '<td>'.$owner['pseudo'].' ('.$server['player_id'].') </td>';
        $html .= '<td>'.$server['ip'].'</td>';
        $html .= '<td>'.$server['code'].' ('.strlen($server['code']).')</td>';
        $html .= '<td>'.$server['slug'].'</td>';
        $html .= '<td>'.$server['rams'].'</td>';
        $html .= '<td>'.$server['ram_containers'].'</td>';
        $html .= '<td>'.$server['time_worked'].'</td>';
        $html .= '</tr>';
      }
      $html .= '</table>';
      echo $html;
      exit;
    }
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
?>
<style>
  #app_admin input[type=text] {
    width:100%;
    margin:0;
    padding:3px 0 3px 0;
  }
</style>
<script>
  function retreive_logs() {
    $.ajax({ 
      cache: false,
      type: "POST",
      url: "game/apps/admin.app.php",
      data: {ajax:true, logs: true},
      success: function (data) {
        $('#app_admin table tr')
          .find('td')
          .eq(1)
          .find('div')
          .html(data)
          .find('tr:odd')
          .css('background', 'rgba(0, 0, 0, 0.2)')
          .parent()
          .find('tr:even')
          .css('background', 'rgba(255, 255, 255, 0.2)')
          .parent()
          .find('td')
          .css('border-left', '1px solid rgba(0, 0, 0, 0.2)');
      }
    });
  }
  function retreive_servers() {
    $.ajax({ 
      cache: false,
      type: "POST",
      url: "game/apps/admin.app.php",
      data: {ajax:true, servers: true},
      success: function (data) {
        $('#app_admin table tr')
          .find('td')
          .eq(1)
          .find('div')
          .html(data)
          .find('tr:odd')
          .css('background', 'rgba(0, 0, 0, 0.2)')
          .parent()
          .find('tr:even')
          .css('background', 'rgba(255, 255, 255, 0.2)')
          .parent()
          .find('td')
          .css('border-left', '1px solid rgba(0, 0, 0, 0.2)');
      }
    });
  }
  function send_action() {
  
    var $form = $('<form></form>', {'id': 'action_form'})
      .unbind('submit').bind('submit', function(e) {
        e.preventDefault();
        $.ajax({
          type: "POST",
          url: "game/apps/admin.app.php",
          data: {ajax: true, send_action: true, target: $(this).find('input[name=target]').val(), action: $(this).find('select[name=action]').val(), message: $(this).find('textarea[name=message]').val()},
          success: function (data) {
            send_action();
          }
        });
      })
      .html(
        ' <input type="text" placeholder="Cible [pseudo, *logged ou *all]" name="target"><br />'
        + '<select name="action"><option value="%02">Déconnexion forcée</option><option value="%03">Message</option><option value="%04">JavaScript</option><option value="%05">Kick avec message</option></select><br />'
        + '<textarea name="message"></textarea><br />'
        + '<input type="submit" value="Envoyer">'
      );
    $('#app_admin table tr')
      .find('td')
      .eq(1)
      .find('div')
      .html($form);
  }
  function connect() {
    var connect = prompt('pseudo');
    if(confirm("Attention.")) {
    if(confirm("Connexion sous le compte "+connect+" ?")) {
      $.ajax({
        type: "POST",
        url: "game/apps/admin.app.php",
        data: {ajax: true, connect: connect},
        success: function (data) {
          window.location.reload();
        }
      });
    }
    }
  }
  function raz() {
    var raz = prompt('pseudo');
    if(confirm("Attention.")) {
    if(confirm("Remettre à zéro le compte "+raz+" ?")) {
      $.ajax({
        type: "POST",
        url: "game/apps/admin.app.php",
        data: {ajax: true, raz: raz},
        success: function (data) {
          alert("RAZ effectué");
        }
      });
    }
    }
  }
  function del() {
    var del = prompt('pseudo');
    if(confirm("Attention.")) {
    if(confirm("SUPPRIMER le compte "+del+" ?")) {
      $.ajax({
        type: "POST",
        url: "game/apps/admin.app.php",
        data: {ajax: true, del: del},
        success: function (data) {
          alert("DEL effectué");
        }
      });
    }
    }
  }
</script>
<div class="app" id="app_admin">
  <table style="width:100%;">
    <tr style="vertical-align:top;">
      <td style="width:15%">
        <div class="box">
          <input type="button" value="Logs " onclick="javascript:retreive_logs();" /><br />
          <input type="button" value="Servs" onclick="javascript:retreive_servers();" /><br />
          <input type="button" value="Act°s" onclick="javascript:send_action();" />
          <input type="button" value="Connect." onclick="javascript:connect();" />
          <input type="button" value="R.A.Z" onclick="javascript:raz();" />
          <input type="button" value="Suppr" onclick="javascript:del();" />
        </div>
      </td>
      <td style="width:85%">
        <div class="box"></div>
      </td>
    </tr>  
  </table>
</div>

<?php
  }
?>