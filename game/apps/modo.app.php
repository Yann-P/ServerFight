<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  

  if(isset($_POST['ajax'])) {
    session_start();
    require_once("../../socle.php");
    if(!check_session() || $_SESSION['grade'] < 2) exit;
    
    
    if(isset($_POST['send_action']) && isset($_POST['target'])) {
      $action = mysql_real_escape_string($_POST['send_action']);
      $target = mysql_real_escape_string($_POST['target']);
      
      if($action == "message" && isset($_POST['message'])) {
        $message = htmlspecialchars(mysql_real_escape_string($_POST['message']));
        if(strlen($message) > 250) exit('5');
        
        if($target == '*logged') {
          $resource_accounts_online = mysql_query("SELECT * FROM accounts WHERE last_activity_timestamp > ".(time() - 20));
          while($account_online = mysql_fetch_array($resource_accounts_online)) {
            mysql_query("INSERT INTO actions VALUES('', '{$account_online['id']}', '%03', '$message')");
            add_to_logs("Message aux connectés : $message");
          }
        }
        else {
          if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'")) != 1) exit('5');
          $target = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'"));
          mysql_query("INSERT INTO actions VALUES('', '{$target['id']}', '%03', '$message')");
          add_to_logs("Message à $target : $message");
        }
        
        exit;
      } //*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****
      elseif($action == "kick" && isset($_POST['message'])) {
        $message = htmlspecialchars(mysql_real_escape_string($_POST['message']));
        if(strlen($message) > 250) exit('5');
        if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'")) != 1) exit('5');
        $target1 = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'"));
        mysql_query("INSERT INTO actions VALUES('', '{$target1['id']}', '%05', '$message')");
        add_to_logs("Kick de $target : $message");
        exit;
      }//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****
      elseif($action == "messagechat" && isset($_POST['message'])) {
        echo 1;
        $message = utf8_encode(mysql_real_escape_string($_POST['message']));
        if(strlen($message) > 1000) exit('5');
        mysql_query("INSERT INTO messenger VALUES('', '0', '#sys', '$message', '0', '0')");
        add_to_logs("MessageChat : $message");
        exit;
      }//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****
      elseif($action == "ban" && isset($_POST['message']) && isset($_POST['time'])) {
        $time = mysql_real_escape_string($_POST['time']);
        $message = htmlspecialchars(mysql_real_escape_string($_POST['message']));
        if(strlen($message) > 250) exit('5');
        if(!is_numeric($time) || $time <= 0 || $time > 999) exit;
        if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'")) != 1) exit('5');
        $banned_until = time()+(60*60*$time);
        $target1 = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'"));
        mysql_query("UPDATE accounts SET banned_until = '$banned_until' WHERE id={$target1['id']}");
        mysql_query("INSERT INTO actions VALUES('', '{$target1['id']}', '%03', '<b>Banni $time heures :</b> $message')");
        add_to_logs("Ban de $target : $message");
        exit;
      }//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****
      elseif($action == "supprsite" && isset($_POST['message'])) {
        $message = htmlspecialchars(mysql_real_escape_string($_POST['message']));
        if(strlen($message) > 250) exit('5');
        if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE adress = '$target'")) != 1) exit('5');
        mysql_query("DELETE FROM sites WHERE adress = '$target'");
        add_to_logs("DEL dite $target pour la raison suivante : $message");
        exit;
      }//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****
      elseif($action == "suppraccount" && isset($_POST['message'])) {
        $message = htmlspecialchars(mysql_real_escape_string($_POST['message']));
        if(strlen($message) > 250) exit('5');
        if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'")) != 1) exit('5');
        
        $target1 = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'"));
        $target_player = mysql_fetch_array(mysql_query("SELECT * FROM players WHERE account_id = '{$target1['id']}'"));
        
        mysql_query("INSERT INTO ban_ip VALUES('', '{$target1['last_log_in_ip']}')");
        
        mysql_query("DELETE FROM servers WHERE player_id={$target_player['id']}");
        mysql_query("DELETE FROM applications_by_players WHERE player_id={$target_player['id']}");
        mysql_query("DELETE FROM actions WHERE account_id={$target1['id']}");
        mysql_query("DELETE FROM sites WHERE player_id={$target_player['id']}");
        mysql_query("DELETE FROM infiltrations_history WHERE player_id={$target_player['id']}");
        mysql_query("DELETE FROM bind WHERE player_id={$target_player['id']}");
        mysql_query("DELETE FROM decryptlab_rooms WHERE creator_id={$target_player['id']}");
        mysql_query("DELETE FROM messenger WHERE account_id={$target1['id']}");
        mysql_query("DELETE FROM mp WHERE sender_id={$target1['id']} OR receiver_id = {$target1['id']}");
        mysql_query("DELETE FROM players WHERE id={$target_player['id']}");
        mysql_query("DELETE FROM accounts WHERE id={$target1['id']}");
        
        add_to_logs("DEL DU COMPTE $target et ban IP pour la raison suivante : $message");
        exit;
      }//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****//*****
      elseif($action == "clear" && isset($_POST['message'])) {
        $message = htmlspecialchars(mysql_real_escape_string($_POST['message']));
        if(strlen($message) > 250) exit('5');
        if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'")) != 1) exit('5');
        
        $target1 = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '$target'"));
        $target_player = mysql_fetch_array(mysql_query("SELECT * FROM players WHERE account_id = '{$target1['id']}'"));
        mysql_query("DELETE FROM sites WHERE player_id={$target_player['id']}");
        mysql_query("DELETE FROM messenger WHERE account_id={$target1['id']}");
        mysql_query("DELETE FROM mp WHERE sender_id={$target1['id']} OR receiver_id = {$target1['id']}");
        
        add_to_logs("CLEAR DU COMPTE $target pour la raison suivante : $message");
        exit;
      }
    }
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
?>
<script>
  
$('#app_modo #message')
  .unbind('submit').bind('submit', function(e) {
    e.preventDefault();
    if(confirm("CONFIRMER le message ?")) $.ajax({
      type: "POST",
      url: "game/apps/modo.app.php",
      data: {ajax:true, send_action: "message", target: $(this).find('input[name=target]').val(), message: $(this).find('textarea[name=message]').val()},
      success: function (data) {
        if(data == 5) alert("Impossible, vous avez mal rempli un champ.");
        else {
          alert("Message envoyé.");
          reload_app('modo');
        }
      }
    });
  });
  
$('#app_modo #kick')
  .unbind('submit').bind('submit', function(e) {
    e.preventDefault();
    if(confirm("CONFIRMER le kick ?")) $.ajax({
      type: "POST",
      url: "game/apps/modo.app.php",
      data: {ajax:true, send_action: "kick", target: $(this).find('input[name=target]').val(), message: $(this).find('textarea[name=message]').val()},
      success: function (data) {
        if(data == 5) alert("Impossible, vous avez mal rempli un champ.");
        else {
          alert("Kick envoyé.");
          reload_app('modo');
        }
      }
    });
  });
  
$('#app_modo #ban')
  .unbind('submit').bind('submit', function(e) {
    e.preventDefault();
    if(confirm("CONFIRMER le ban ?")) $.ajax({
      type: "POST",
      url: "game/apps/modo.app.php",
      data: {ajax:true, send_action: "ban", target: $(this).find('input[name=target]').val(), time: $(this).find('input[name=time]').val(), message: $(this).find('textarea[name=message]').val()},
      success: function (data) {
        if(data == 5) alert("Impossible, vous avez mal rempli un champ.");
        else {
          alert("Ban envoyé.");
          reload_app('modo');
        }
      }
    });
  });
$('#app_modo #messagechat')
  .unbind('submit').bind('submit', function(e) {
    e.preventDefault();
    if(confirm("CONFIRMER l'annonce ?")) $.ajax({
      type: "POST",
      url: "game/apps/modo.app.php",
      data: {ajax:true, send_action: "messagechat", target: false, message: $(this).find('textarea[name=message]').val()},
      success: function (data) {
        if(data == 5) alert("Impossible, vous avez mal rempli un champ.");
        else {
          alert("Annonce envoyée.");
          reload_app('modo');
        }
      }
    });
  });
$('#app_modo #supprsite')
  .unbind('submit').bind('submit', function(e) {
    e.preventDefault();
    
    if(confirm("CONFIRMER la suppression de site ?")) $.ajax({
      type: "POST",
      url: "game/apps/modo.app.php",
      data: {ajax:true, send_action: "supprsite", target: $(this).find('input[name=target]').val(), message: $(this).find('textarea[name=message]').val()},
      success: function (data) {
        if(data == 5) alert("Impossible, vous avez mal rempli un champ.");
        else {
          alert("Site supprimé.");
          reload_app('modo');
        }
      }
    });
  });
  
$('#app_modo #suppraccount')
  .unbind('submit').bind('submit', function(e) {
    e.preventDefault();
    
    if(confirm("CONFIRMER la suppression de compte et le BAN DEFINITIF de l'IP ???")) $.ajax({
      type: "POST",
      url: "game/apps/modo.app.php",
      data: {ajax:true, send_action: "suppraccount", target: $(this).find('input[name=target]').val(), message: $(this).find('textarea[name=message]').val()},
      success: function (data) {
        if(data == 5) alert("Impossible, vous avez mal rempli un champ.");
        else {
          alert("Compte supprimé, IP bloquée.");
          reload_app('modo');
        }
      }
    });
  });
  
$('#app_modo #clear')
  .unbind('submit').bind('submit', function(e) {
    e.preventDefault();
    
    if(confirm("Supprimer messages, MP et sites du joueur ?")) $.ajax({
      type: "POST",
      url: "game/apps/modo.app.php",
      data: {ajax:true, send_action: "clear", target: $(this).find('input[name=target]').val(), message: $(this).find('textarea[name=message]').val()},
      success: function (data) {
        if(data == 5) alert("Impossible, vous avez mal rempli un champ.");
        else {
          alert("Clear effectué");
          reload_app('modo');
        }
      }
    });
  });
</script>
<div class="app" id="app_modo">
  <span style="color:red;font-size:17px;">Toutes vos actions sont enregistrées. N'utilisez pas ces outils à mauvais escient.<br /><br /></span>
  <br />
  <h2>Message</h2>
  <form id="message">
    <b>Cible</b> *logged pour tous les connectés, ou pseudo du joueur : <br /><input type="text" name="target" /><br />
    Message<br />
    <textarea name="message"></textarea><br />
    <span style="color:red">Vérifiez bien le formulaire avant de valider !</span>
    <input type="submit" value="Envoyer le message" /><br />
  </form>
  <hr /><br />
  <h2>Déconnexion forcée</h2><br />
  <form id="kick">
    <b>Cible</b> pseudo du joueur :<br /> <input type="text" name="target" /><br />
    Raison<br /><textarea name="message"></textarea><br />
    <span style="color:red">Vérifiez bien le formulaire avant de valider !</span>
    <input type="submit" value="Kick" /><br />
  </form><br />
  <hr /><br />
  <h2>Bannissement</h2>
  <form id="ban">
    <b>Cible</b> pseudo du joueur :<br /> <input type="text" name="target" /><br />
    <b>Temps</b> en heures : <br /><input type="text" name="time" /><br />
    Raison<br /><textarea name="message"></textarea><br />
    <span style="color:red">Vérifiez bien le formulaire avant de valider !</span>
    <input type="submit" value="Bannir" /><br />
    1 mn = 0.016 ; 3mn = 0.05 ; 15mn = 0.25 ; 30mn = 0.5 ; 2 jours : 48 ; 3 jours : 72 ; 1 semaine = 168
  </form>
  <h2>Annonce sur le t'chat</h2>
  <form id="messagechat">
    Message<br /><textarea name="message"></textarea><br />
    <span style="color:red">Vérifiez bien le formulaire avant de valider !</span>
    <input type="submit" value="Envoyer" /><br />
  </form>
  <hr /><br />
  <h2>Suppression de site</h2>
  <form id="supprsite">
    <b>Adresse du site</b><br /> <input type="text" name="target" /><br />
    Raison<br /><textarea name="message"></textarea><br />
    <span style="color:red">Vérifiez bien le formulaire avant de valider !</span>
    <input type="submit" value="Supprimer définitivement" /><br />
  </form>
  <hr /><br />
  <h2>Suppression de traces d'un joueur sur la communauté</h2>
  Etape 1 : supprimez les messages insultants dans le t'chat.<br />
  Etape 2 : entrez le pseudo ici et tous ses messages, MP, sites, etc, seront supprimés.
  <form id="clear">
    <b>Pseudo</b><br /> <input type="text" name="target" /><br />
    Raison<br /><textarea name="message"></textarea><br />
    <span style="color:red">Vérifiez bien le formulaire avant de valider !</span>
    <input type="submit" value="Supprimer définitivement" /><br />
  </form>
  <hr /><br />
  <h2>Suppression de compte et ban IP</h2>
  <form id="suppraccount">
    <b>Pseudo</b><br /> <input type="text" name="target" /><br />
    Raison<br /><textarea name="message"></textarea><br />
    <span style="color:red">Vérifiez bien le formulaire avant de valider !</span>
    <input type="submit" value="Supprimer définitivement !!!" /><br />
  </form>
</div>

<?php
  }
?>