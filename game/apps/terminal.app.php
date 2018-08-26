<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
   
  if(isset($_POST['command']) && isset($_POST['token'])) {
    session_start();
    require_once("../../socle.php");
    if(!check_session()) exit;
    $player = retreive_player($_SESSION['id']);
    
    $token = mysql_real_escape_string($_POST['token']);
    if(!check_token($_SESSION['id'], $token)) exit(json_encode(array("token" => false)));
    
    function response($type, $message, $script) {
      if($type != "success" && $type != "failure") exit;
      echo json_encode(array("response" => array("type" => $type, "message" => $message, "script" => $script), "token" => renew_token($_SESSION['id'])));
      $player = retreive_player($_SESSION['id']);
      recalculate_player_infos($player['id']);
      exit;
    }
    
    $command_matches = !!preg_match_all('@([a-z]+) (.+)@', strtolower(htmlspecialchars(mysql_real_escape_string($_POST['command']))), $cmatches);
    $bind_matches = preg_match('#^\$(.+?)$#', strtolower(htmlspecialchars(mysql_real_escape_string($_POST['command']))), $bmatches);
    if($bind_matches) {
      $shortcut = mysql_real_escape_string($bmatches[1]);
      $resource_bind = mysql_query("SELECT * FROM bind WHERE player_id = {$player['id']} AND shortcut = '$shortcut'");
      if(mysql_num_rows($resource_bind) != 0) {
        $bind = mysql_fetch_array($resource_bind);
        $command_matches = !!preg_match_all('@([a-z]+) (.+)@', htmlspecialchars($bind['command']), $cmatches);
        if($command_matches) {
          $command   = $cmatches[1][0];
          $arguments = $cmatches[2][0];
          require("../commands.php");
          if(!run_command($command, $arguments)) response("failure", "Commande invalide.", false);
        }
        else {
          require("../commands.php");
          if(!run_command($bind['command'], false)) response("failure", "Commande invalide.", false);
        }
      }
      else response("failure", "Commande non définie.", false);
    }
    elseif($command_matches) {
      $command   = $cmatches[1][0];
      $arguments = $cmatches[2][0];
      
      require("../commands.php");
      if(!run_command($command, $arguments)) response("failure", "Cette commande n'a pas été reconnue. Elles sont listées dans le guide du jeu.", false);
    }
    else {
      require("../commands.php");
      if(!run_command(mysql_real_escape_string($_POST['command']), false)) response("failure", "[Syntaxe] Cette commande est incomplète.", false);
    }  
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
?>
<style>
  #app_terminal {
    font-size:13px;
    font-family:consolas, courier, arial;
    color:white;
  }
  #app_terminal input[type=text] {
    background:black;
    color:white;
    height:12px;
    width:400px;
    margin:0;
    padding:0;
    vertical-align:middle;
    font-family:consolas;
    font-size:12px;
    outline:none;
  }
</style>
<script>
  function random_chars(l) {
    var c = new Array("a","b","c","d","e","f","g","h","i",'j','k','l','m','n','o','p','q','r','s','t', 'a', 'e', 'i', 'o');
    var s =''; for(i = 0; i < l; i++) { s = s + c[Math.floor(Math.random()*c.length)]; } return s;
  }
  
  function random_file(l) {
    var l = new Array(".exe",".lib",".js",".bat",".php",".html",".css");
    var e = l[Math.floor(Math.random()*l.length)];
    var file_name = random_chars(Math.round(Math.random()*8)+1) + '_' + (Math.round(Math.random()*1000)+10) + e;
    return file_name;
  }
  
  setTimeout(function() {
    $('#app_terminal').append('<span style="color:gold">→ Terminal ServerFight v1.2</span><br />');
    setTimeout(function() {
      $('#app_terminal').append('<span style="color:gold">→ Initialisation...</span><br /><br />');
    }, 300);
    setTimeout(function() {
      var delay = 50;
      for(var i = 0; i < 6 ; i++) {
        setTimeout(function() {
          $('#app_terminal').append('<span style="color:chartreuse">→ [OK]</span>&nbsp;&nbsp;&nbsp;<span style="color:gold">Chargement du module '+random_file()+'</span><br />');
        }, delay);
        delay += (Math.round(Math.random()*100)+50);
      }
      setTimeout(function() {
        $('#app_terminal').html('').append('<span style="color:chartreuse"> → Prêt.</span>');
        new_line();
      }, 1500);
    }, 900);
  }, 500);
  function new_line() {
    var $user_input = $('<input />')
    .attr({'type': 'text', 'placeholder': 'Double cliquez sur la console pour donner le focus.'})
    .addClass('current_user_input');
    
    $('#app_terminal').append('<br /><b class="user_cmd"><?php echo $_SESSION['pseudo']; ?>@ServerFight → </b>').append($user_input);
    $('#app_terminal input').attr('disabled', 'disabled').blur();
    $('#app_terminal input:last').removeAttr('disabled').focus();
    
    $('#app_terminal input').unbind('keydown');
    $('#app_terminal input:last')
      .unbind('keydown')
      .bind('keydown', function(event) {
        if(event.keyCode == 13 && $(this).val() != "") {
          if(CONFIG['CSRF_proof_ajax_in_progress'] != 0) {
            dialog("Attendez le serveur !", "Vous ne pouvez faire qu'une chose à la fois.");
            return false;
          }
          CONFIG['CSRF_proof_ajax_in_progress'] += 1;
          $.ajax({ 
            cache: false,
            type: "POST",
            url: "game/apps/terminal.app.php",
            data: {command: $(this).val(), token: CONFIG['token']},
            dataType: 'json',
            success: function (data) {
              CONFIG['CSRF_proof_ajax_in_progress'] -= 1;
              if(data.token) CONFIG['token'] = data.token;
              else {
                csrf();
                return false;
              }
              if(data.response.script) eval(data.response.script);

              if(data.response.type == "success") message_color = "chartreuse";
              else message_color = "red";
              $('#app_terminal').append('<br /> <span style="color:'+message_color+'">→ '+data.response.message+'</span>');
              new_line();
            }
          });
          
          CONFIG['terminal_history_index'] += 1;
          
          CONFIG['terminal_history'][CONFIG['terminal_history_index']] = $('#app_terminal input:last').val();
          
          if(CONFIG['terminal_history'] <= 2) CONFIG['terminal_history_position'] = CONFIG['terminal_history_index'] - 1;
          else CONFIG['terminal_history_position'] = CONFIG['terminal_history_index'] + 1;
        }
        else if (event.keyCode == 38) {
          event.preventDefault();
          if (CONFIG['terminal_history_position'] >= 1) {
            CONFIG['terminal_history_position'] = CONFIG['terminal_history_position'] - 1;
            $('#app_terminal input:last').val(CONFIG['terminal_history'][CONFIG['terminal_history_position']]);
          }
        }
        else if (event.keyCode == 40) {
          event.preventDefault();
          if (CONFIG['terminal_history_position'] < CONFIG['terminal_history_index']) {
            CONFIG['terminal_history_position'] = CONFIG['terminal_history_position'] + 1;
            $('#app_terminal input:last').val(CONFIG['terminal_history'][CONFIG['terminal_history_position']]);
          }
        }
      });
      
    $('#app_terminal .user_cmd').unbind('mouseenter').unbind('mouseleave').unbind('click');
    $('#app_terminal .user_cmd:last')
      .unbind('mouseenter')
      .bind('mouseenter', function() {
        $(this).css({'text-shadow': '0 0 5px white', 'cursor': 'pointer'});
      })
      .unbind('mouseleave')
      .bind('mouseleave', function() {
        $(this).css({'text-shadow': 'none', 'cursor': 'auto'});
      })
      .unbind('click')
      .bind('click', function() {
        $('#app_terminal input:last').focus();
      });
  }  
  $('#app_terminal').dblclick(function() {
    $('#app_terminal input:last').focus();
  });
    
</script>
<div class="app" id="app_terminal">
</div>

<?php
  }
?>