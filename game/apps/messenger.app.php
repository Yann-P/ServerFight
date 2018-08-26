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
    
    if(isset($_POST['message']) && isset($_POST['token'])) {
      $message = mysql_real_escape_string($_POST['message']);
      $token = mysql_real_escape_string($_POST['token']);
      
      if(!check_token($_SESSION['id'], $token)) {
        add_to_logs("Messenger -> Token $token erroné !");
        exit(json_encode(array("token" => false)));
      }
      
      $errors = "";
      if(is_banned($account['pseudo'])) $errors .= "Vous êtes banni du t'chat pour le moment. <br />";
      if(trim($message) == "") $errors .= "Votre message est vide. <br />";
      if(strlen($message) > 500) $errors .= "Votre message est trop long. <br />";
      $last_message = mysql_fetch_array(mysql_query("SELECT * FROM messenger WHERE account_id = {$account['id']} ORDER BY id DESC LIMIT 1"));
      if(strtolower($last_message['message']) == strtolower(utf8_encode($message))) $errors .= "Votre message est identique au précédent. <br />";
      
      if(!isset($_SESSION['flood_wait'])) $_SESSION['flood_wait'] = 3;
      
      if(time() - $_SESSION['flood_wait'] < $last_message['timestamp']) {
        if($_SESSION['flood_wait'] <= 20) $_SESSION['flood_wait'] +=2;
        
        if($_SESSION['flood_wait'] > 5) $errors .= "Doucement, merci !<br />Attendez maintenant {$_SESSION['flood_wait']}s entre chaque message. Cela ne sert à rien d'insister.";
        else $errors .= "Merci de ne pas poster vos messages si rapidement les uns après les autres.<br />";
      }
      else {
        $_SESSION['flood_wait'] = 3;
      }
      
      if($errors == "") {
        $last_message = mysql_fetch_array(mysql_query("SELECT * FROM messenger ORDER BY id DESC LIMIT 1"));
        if(rand(0, 150) == 150 && $last_message['pseudo'] != $account['pseudo']) {
          if(mysql_num_rows(mysql_query("SELECT * FROM matrice WHERE won_by = ''")) == 0) {
            $tokens = rand(100, 500);
            $matrice = rand(0, 1)?random_string("010101010%$*", 10):random_string("010101010}#?", 10);
            mysql_query("INSERT INTO matrice VALUES('', '$matrice', '$tokens', '')");
          }
        }
      
        mysql_query("UPDATE players SET last_messenger_activity='{$_SERVER['REQUEST_TIME']}' WHERE account_id='{$_SESSION['id']}'");
        $mp_matches = preg_match('#^@(.+?) (.+)$#', $message, $matches);
        if($mp_matches && $matches[1] && $matches[2]) {
          $receiver = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '{$matches[1]}'"));
          $message = $matches[2];
          if((strtolower($matches[1]) == ".modo" || strtolower($matches[1]) == "#modo") && $account['grade'] > 1) {
            mysql_query("INSERT INTO messenger VALUES('', '{$player['id']}', '{$_SESSION['pseudo']}', '".utf8_encode($message)."', '#modo', '{$_SERVER['REQUEST_TIME']}')");
          }
          elseif(strtolower($matches[1]) == "!" && $account['grade'] > 1) {
            mysql_query("INSERT INTO messenger VALUES('', '0', '#modo', '".utf8_encode($message)."', '0', '{$_SERVER['REQUEST_TIME']}')");
          }
          else {
            if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE id = '{$receiver['id']}'")) == 0) {
              echo json_encode(array("errors" => "Ce joueur n'existe pas.", "token" => renew_token($_SESSION['id'])));
              exit;
            }
            mysql_query("INSERT INTO messenger VALUES('', '{$player['id']}', '{$_SESSION['pseudo']}', '".utf8_encode($message)."', '{$receiver['pseudo']}', '{$_SERVER['REQUEST_TIME']}')");
          }
        }
        else {
          mysql_query("INSERT INTO messenger VALUES('', '{$player['id']}', '{$_SESSION['pseudo']}', '".utf8_encode($message)."', '0', '{$_SERVER['REQUEST_TIME']}')");
        }
      }
      echo json_encode(array("errors" => $errors, "token" => renew_token($_SESSION['id'])));
    }
    elseif(isset($_POST['delmsg']) && isset($_POST['message_id'])) {
      if($account['grade'] < 2) exit('5');
      
      $msg_id = mysql_real_escape_string($_POST['message_id']);
      
      $resource_message_to_delete = mysql_query("SELECT * FROM messenger WHERE id = '$msg_id'");
      if(mysql_num_rows($resource_message_to_delete) == 1) {
        $message_to_delete = mysql_fetch_array($resource_message_to_delete);
        $author = retreive_account($message_to_delete['account_id']);
        if($author['grade'] == 3 && $account['grade'] == 2) exit('1');
        if($account['grade'] == 2 && ($author['pseudo'] == "#sys" || $author['pseudo'] == "#delmsg")) exit('9');
        mysql_query("DELETE FROM messenger WHERE id = '$msg_id'");
        mysql_query("INSERT INTO messenger VALUES('', '0', '#delmsg', '$msg_id', '0', '0')");
        echo 800;
      }
      else echo mysql_num_rows($resource_message_to_delete);
    }
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
  
?>
<style>
  #app_messenger input[type=text] {
    width:100%;
    margin:0;
    padding:3px 0 3px 0;
  }
  
  #app_messenger #messages {
    margin-top:10px;
    vertical-align:top;
    width:100%;
    text-shadow:1px 1px 0 rgba(255, 255, 255, 0.5);
    border:1px solid rgba(0, 0, 0, 0.3);
  }
  
  #app_messenger #messages .message_container {
    width:100%; 
  }
  
  #app_messenger #messages .public_message_container {
    background:rgba(0, 0, 0, 0.1);
    border-bottom:1px solid rgba(0, 0, 0, 0.2);
    border-top:1px solid rgba(255, 255, 255, 0.4);
  }
  
  #app_messenger #messages .private_message_container {
    background:rgba(100, 0, 0, 0.2);
    border-bottom:1px solid rgba(0, 0, 0, 0.2);
    border-top:1px solid rgba(255, 255, 255, 0.4);
  }
  
  #app_messenger #messages .special_message_container {
    background:rgba(150, 200, 0, 0.4);
    border-bottom:1px solid rgba(0, 0, 0, 0.2);
    border-top:1px solid rgba(255, 255, 255, 0.4);
  }
  
  #app_messenger #messages .message { padding:10px; }
  
  #app_messenger #online {
    height:500px;
    width:100%;
    margin:10px 0 0 10px;
    font-size:15px;
    font-weight:bold;
  }
  
  #app_messenger .delmsg {
    <?php if($_SESSION['grade'] < 2) echo "display:none;"; ?>
  }
</style>
<script>
  var $message_input = $('<input />')
    .attr({'type': 'text'})
    .unbind('keydown')
    .bind('keydown', function(event) {
      if(event.keyCode == 13) send_message($('#app_messenger input[type=text]').val());
    });
    
  $('#app_messenger')
    .prepend($message_input);
    
  $('#app_messenger .delmsg')
    .die('click')
    .live('click', function(event) {
      event.preventDefault();
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/messenger.app.php",
        data: {ajax: true, delmsg: true, message_id: $(this).attr('data-id')},
        dataType: 'json',
        success: function (data) {
          //
        }
      });
    });
    
  // Les messages sont récupérés AUTOMATIQUEMENT (client: desktop.js, server: refresh.php)
  function update_messenger(messages, players_online) {
    if(CONFIG['last_message_seen'] == 0) $('.special_message_container, .private_message_container, .public_message_container').remove();
    
    if(messages.length > 0) {
      document.getElementById("message_sound").play();
      document.title='* ServerFight';
      setTimeout(function() {
        document.title=' ServerFight';
        setTimeout(function() {
          document.title='* ServerFight';
          setTimeout(function() {
            document.title='ServerFight';
          }, 700);
        }, 700);
      }, 700);  
    }
    
    $.each(messages, function(index, message) {
      if($('.message_container[data-id='+message.id+']').length == 0) {
        if(message.pseudo == "#delmsg")
          $('.message_container[data-id='+message.message+']').slideUp(200, function() { $(this).remove(); });
        else {
          if(message.mpto) $formated_message = $('<div></div>')
              .attr('data-id', message.id)
              .addClass('message_container private_message_container')
              .html('<div class="message"><a href="#" class="delmsg" data-id="'+message.id+'">X</a><b>'+message.pseudo+'</b> → <b>'+message.mpto+'</b> : '+message.message+'</div>');
          else {
            if(message.pseudo == "#sys") $formated_message = $('<div></div>')
              .attr('data-id', message.id)
              .addClass('message_container public_message_container special_message_container')
              .html('<div class="message"><a href="#" class="delmsg" data-id="'+message.id+'">X</a><b>'+message.message+'</b></div>');
            else $formated_message = $('<div></div>')
              .attr('data-id', message.id)
              .addClass('message_container public_message_container')
              .html('<div class="message"><a href="#" class="delmsg" data-id="'+message.id+'">X</a><b>'+message.pseudo+'</b> : '+message.message+'</div>');
          }
          $formated_message.prependTo($('#app_messenger #messages')).hide().slideDown();
          if($('.message_container').length > 35) $('.message_container:last').slideUp(function() { $(this).remove(); })
        }
      }
    });
    
    $('#app_messenger #online').html('');
    $.each(players_online, function(index, player_online) {
      $('#app_messenger #online').append(player_online+'<br />');
    });
  }
  
  function send_message(message) {
    if(CONFIG['CSRF_proof_ajax_in_progress'] != 0) {
      dialog("Attendez le serveur !", "Vous ne pouvez faire qu'une chose à la fois.");
      return false;
    }
    if($.trim(message)) {
      CONFIG['CSRF_proof_ajax_in_progress'] += 1;
      $message_input.attr('disabled', 'disabled');
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/messenger.app.php",
        data: {ajax: true, message: message, token: CONFIG['token']},
        dataType: 'json',
        success: function (data) {
          CONFIG['CSRF_proof_ajax_in_progress'] -= 1;
          $message_input.removeAttr('disabled');
          if(data.token) CONFIG['token'] = data.token;
          else {
            csrf();
            return false;
          }
          if(data.errors == "") {
            $('#app_messenger input[type=text]').val('').focus();
          }  
          else dialog("Erreur", data.errors);
        }
      });
    }
  }  
</script>
<div class="app" id="app_messenger">
  <table style="width:100%;">
    <tr style="vertical-align:top;">
      <td style="width:80%">
        <div id="messages"></div>
      </td>
      <td style="width:20%">
        <div id="online"></div>
      </td>
    </tr>  
  </table>
</div>

<?php
  }
?>