<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
   
  if(isset($_POST['ajax'])) {
    session_start();
    require_once("../../socle.php");
    if(!check_session()) exit;
    $account = retreive_account($_SESSION['id']); 
    $player = retreive_player($_SESSION['id']); 
    
    if(isset($_POST['send_message']) && isset($_POST['pseudo']) && isset($_POST['title']) && isset($_POST['message'])) {
      $pseudo = mysql_real_escape_string($_POST['pseudo']);
      $title = mysql_real_escape_string($_POST['title']);
      $message = mysql_real_escape_string($_POST['message']);
      
      if(trim(strlen($title)) < 2) exit("Le titre de votre message est trop court.");
      if(trim(strlen($message)) < 10) exit("Le message est trop court.");
      if(strlen($title) > 50) exit("Le titre de votre message ne peut excéder 50 caractères.");
      if(strlen($message) > 2500) exit("Le message ne peut excéder 2500 caractères.");
      
      if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE pseudo = '$pseudo'")) == 1) {
        if(!is_banned($account['pseudo'])) {
          $receiver = retreive_account($pseudo, true);
          if($receiver['id'] == $account['id']) exit("Impossible de vous envoyer un message à vous même...");
          mysql_query("INSERT INTO mp VALUES('', '{$account['id']}', '{$receiver['id']}', '".utf8_encode($title)."', '".utf8_encode($message)."', '0', '".time()."')");
          echo 1;
        }
        else echo "Vous êtes banni pour le moment et ne pouvez envoyer de message privé.";
      }
      else echo "Ce pseudo n'existe pas.";
    }
    elseif(isset($_POST['delete_message']) && isset($_POST['id'])) {
      $id = mysql_real_escape_string($_POST['id']);
      $resource_mp = mysql_query("SELECT * FROM mp WHERE id = $id");
      if(mysql_num_rows($resource_mp) == 1) {
        $mp = mysql_fetch_array($resource_mp);
        if($mp['receiver_id'] == $account['id']) {
          mysql_query("DELETE FROM mp WHERE id = $id");
        }
      }
    }
    elseif(isset($_POST['delete_all'])) {
      mysql_query("DELETE FROM mp WHERE receiver_id = {$account['id']}");
    }
    elseif(isset($_POST['read_message']) && isset($_POST['id'])) {
      $id = mysql_real_escape_string($_POST['id']);
      $resource_mp = mysql_query("SELECT * FROM mp WHERE id = $id");
      if(mysql_num_rows($resource_mp) == 1) {
        $mp = mysql_fetch_array($resource_mp);
        if($mp['receiver_id'] == $account['id']) {
          mysql_query("UPDATE mp SET has_been_read = 1 WHERE id = $id");
        }
      }
    }
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
?>
<style>
  #app_mp #send {
    display:none;
  }
  #app_mp .mp {
    display:block;
    padding:10px;
    margin-bottom:5px;
    background:rgba(0, 0, 0, 0.1);
    border:1px solid rgba(0, 0, 0, 0.2);
  }
  #app_mp .mp .title {
    cursor:pointer;
    text-shadow:1px 1px 0 rgba(255, 255, 255, 0.5);
  }
  #app_mp .mp .title .delete {
    float:right;
  }
  #app_mp .mp .content {
    display:none;
    padding-left:5px;
    margin-top:5px;
    margin-bottom:5px;
    border-left:2px solid rgba(0, 0, 0, 0.1);
    font-size:12px;
  }
</style>
<script>
  $('#app_mp #send_mp')
    .unbind('submit')
    .bind('submit', function(event) {
      event.preventDefault();
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/mp.app.php",
        data: {ajax: true, send_message: true, pseudo: $(this).find('input[name=pseudo]').val(), title: $(this).find('input[name=title]').val(), message: $(this).find('textarea[name=message]').val()},
        success: function (data) {
          if(data == 1) {
            dialog("Messages privés", "Votre message a bien été envoyé !");
            reload_app('mp');
          }
          else {
            dialog("Messages privés", data);
          }
        }
      });
    });
    
  $('#app_mp .mp .title a')
    .unbind('click')
    .bind('click', function(event) {
      var id = $(this).attr('data-id');
      var $mp = $(this).parent().parent();
      if($mp.find('.content').css('display') == 'none') {
        $('.mp .content').hide();
        $mp.find('.content').slideDown();
        $mp.find('.title a').css('font-weight', 'normal');
        $.ajax({ 
          cache: false,
          type: "POST",
          url: "game/apps/mp.app.php",
          data: {ajax: true, read_message: true, id: id},
        });
      }
      else {
        $mp.find('.content').slideUp();
      }
    });
    
  $('#app_mp .mp .title .delete')
    .unbind('click')
    .bind('click', function(event) {
      event.preventDefault();
      if(confirm("Supprimer ce message ?")) {
        $.ajax({ 
          cache: false,
          type: "POST",
          url: "game/apps/mp.app.php",
          data: {ajax: true, delete_message: true, id: $(this).attr('data-id')},
          success: function (data) {
            dialog("Messages privés", "Ce message a bien été supprimé.");
            reload_app('mp');
          }
        });
      }
    });
    
  $('#app_mp #clear_mp_box')
    .unbind('click')
    .bind('click', function(event) {
      event.preventDefault();
      if(confirm("Confirmer cette action ?")) {
        $.ajax({ 
          cache: false,
          type: "POST",
          url: "game/apps/mp.app.php",
          data: {ajax: true, delete_all: true},
          success: function (data) {
            reload_app('mp');
          }
        });
      }
    });
</script>
<div class="app" id="app_mp">
  <div class="box">
    <h3><img class="icon" src="design/icons/mp.png">Votre messagerie privée. <img class="icon" src="design/icons/refresh_small.png" style="cursor:pointer;" onclick="reload_app('mp');" /></h3>
  </div>
  <div id="mp_commands">
    <input type="button" value="Rédiger un message privé" onclick="$('#mp_commands').hide(); $('#app_mp #read').hide(); $('#app_mp #send').fadeIn();" /> 
    <input type="button" value="Vider ma boîte" id="clear_mp_box" />
  </div><br />
  <div id="send" class="box">
    <form id="send_mp">
      <table>
        <tr>
          <td>Destinataire</td>
          <td><input type="text" name="pseudo" maxlength="50" /></td>
        </tr>
        <tr>
          <td>Titre</td>
          <td><input type="text" name="title" maxlength="50" /></td>
        </tr>
        <tr>
          <td>Message</td>
          <td><textarea name="message" cols="40" rows="5"></textarea></td>
        </tr>
        <tr>
          <td></td>
          <td><input type="submit" value="Envoyer" /><input type="button" value="Annuler" onclick="if(confirm('Confirmer cette action ?')) { reload_app('mp'); }" /></td>
        </tr>
      </table>
    </form>
  </div>
  <br />
  <div id="read">
    <?php
      $resource_mp = mysql_query("SELECT * FROM mp WHERE receiver_id = '{$_SESSION['id']}' ORDER BY id DESC");
      while($mp = mysql_fetch_array($resource_mp)) {
        $sender = retreive_account($mp['sender_id']);
        echo '<div class="mp">';
          echo '<div class="title">';
            echo '<b>'.grade_pseudo($sender['pseudo'], $sender['grade']).'</b><span class="delete" data-id="'.$mp['id'].'"><img class="icon" src="design/icons/delete.png" /></span>';
            echo ' - ';
            if($mp['has_been_read'] == 0) echo '<a href="#" style="font-weight:bold;" data-id="'.$mp['id'].'">'.stripslashes(utf8_decode(htmlspecialchars($mp['title']))).'</a>';
            else echo '<a href="#" data-id="'.$mp['id'].'">'.stripslashes(utf8_decode(htmlspecialchars($mp['title']))).'</a>';
          echo '</div>';
          echo '<div class="content">';
            echo stripslashes(utf8_decode(nl2br(htmlspecialchars($mp['message'])))).'<br /><br /><input type="button" style="padding:2px;" value="Répondre" onclick="$(\'#mp_commands\').hide(); $(this).hide(); $(\'#app_mp #read\').hide(); $(\'#app_mp #send\').show(); $(\'#app_mp #send\').find(\'input[name=pseudo]\').val(\''.$sender['pseudo'].'\'); $(\'#app_mp #send\').find(\'input[name=title]\').val(\'> Reponse\'); $(\'#app_mp #send\').find(\'textarea[name=message]\').focus();" />';
          echo '</div>';
        echo '</div>';
      }
    ?>
  </div>
  <br />
</div>
<br />
<?php
  }
?>