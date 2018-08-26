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
  
    if(isset($_POST['create_room']) && isset($_POST['bet'])) {
      unset($_SESSION['decryptlab_room_id']);
      $bet = mysql_real_escape_string($_POST['bet']);
      if(!is_numeric($bet) || $bet < 25 || $bet > 1000000) exit("La mise est invalide. Vous ne pouvez miser en dessous de 25 tokens.");
      if($bet > $player['tokens']) exit("La mise est invalide par rapport aux tokens qu'il vous reste.");
      
      $resource_last_room_created = mysql_query("SELECT * FROM decryptlab_rooms WHERE revelation_timestamp > '".(time()-(60))."' AND creator_id = {$player['id']}");
      if(mysql_num_rows($resource_last_room_created) < 3) {
      
        $participants = array();
        array_push($participants, $_SESSION['id']);
        
        //$method = rand(0, 1);
        $method = 0; 
        if($method == 0) {
          do {
            $words = array ('binary', 'computer', 'server', 'keyboard', 'cursor', 'mouse', 'application', 'tchat', 'email', 'message',
                            'screen', 'window', 'button', 'enter', 'escape', 'hacker', 'code', 'secret', 'infiltration', 'jeu', 'chrome',  
                            'switch', 'hacking', 'firefox', 'linux', 'macintosh', 'matrice', 'cle', 'electronic', 'terminal');
            
            $indice = rand(0, count($words)-1);
            $decrypted = $words[$indice];
            
            $s = $decrypted; 
            $n = strlen($s); 
            $encrypted =  $s[0] . str_shuffle( substr($s, 1, $n-2) ) . $s[$n-1]; 
          }
          while($encrypted == $decrypted);
        }
        else {
        }
        
        mysql_query("UPDATE players SET tokens = ".($player['tokens']-$bet)." WHERE id = {$player['id']}");
        mysql_query("INSERT INTO decryptlab_rooms VALUES('', '{$player['id']}', '$bet', '".(time()+100)."', '$decrypted', '$encrypted', '$method', '".json_encode($participants)."', '-1')");
        $last_entry = mysql_fetch_array(mysql_query("SELECT * FROM decryptlab_rooms ORDER BY id DESC LIMIT 1"));
        $_SESSION['decryptlab_room_id'] = $last_entry['id'];
        echo 1;
      }
      else echo "Vous êtes limité à 3 salles par intervalle de 24 heures.";
    }
    if(isset($_POST['join_room']) && isset($_POST['id'])) {
      $id = mysql_real_escape_string($_POST['id']);
      
      if(isset($_SESSION['decryptlab_room_id'])) unset($_SESSION['decryptlab_room_id']);
      if(mysql_num_rows(mysql_query("SELECT * FROM decryptlab_rooms WHERE id = $id")) == 0) exit("Erreur.");
      $decryptlab_room = mysql_fetch_array(mysql_query("SELECT * FROM decryptlab_rooms WHERE id = $id"));
      if($decryptlab_room['won_by'] == -1) {
        if($decryptlab_room['revelation_timestamp']-time() >= 0) {
          if($decryptlab_room['bet'] <= $player['tokens']) {
            mysql_query("UPDATE players SET tokens = ".($player['tokens']-$decryptlab_room['bet'])." WHERE id = {$player['id']}");
            $_SESSION['decryptlab_room_id'] = $id;
            $participants = json_decode($decryptlab_room['participants']);
            array_push($participants, $player['id']);
            mysql_query("UPDATE decryptlab_rooms SET participants = '".json_encode($participants)."' WHERE id = $id");
            echo 1;
          }
          else echo "Vous n'avez pas assez de tokens.";
        }
        else echo "Vous arrivez trop tard. Cette salle ne recrute plus.";
      }
      else echo "Vous arrivez trop tard. Cette salle est close.";
    }
    if(isset($_POST['retreive_encrypted'])) {
      if(!isset($_SESSION['decryptlab_room_id'])) exit(0);
      $decryptlab_room = mysql_fetch_array(mysql_query("SELECT * FROM decryptlab_rooms WHERE id = {$_SESSION['decryptlab_room_id']}"));
      if($decryptlab_room['won_by'] == -1) {
        if($decryptlab_room['revelation_timestamp']-time() <= 0) {
          $participants = json_decode($decryptlab_room['participants']);
          if(in_array($player['id'], $participants)) {
            if(sizeof($participants) == 1) {
              mysql_query("UPDATE players SET tokens = ".($player['tokens']+$decryptlab_room['bet'])." WHERE id = {$player['id']}");
              mysql_query("DELETE FROM decryptlab_rooms WHERE id = {$_SESSION['decryptlab_room_id']}");
              unset($_SESSION['decryptlab_room_id']);
              echo 2;
              exit;
            }
            else echo $decryptlab_room['encrypted'];
          }
          else echo 0;
        }
        else echo 0;
      }
      else {
        unset($_SESSION['decryptlab_room_id']);
        echo 0;
      }
    }
    if(isset($_POST['retreive_players_in_room'])) {
      if(!isset($_SESSION['decryptlab_room_id'])) exit(0);
      $decryptlab_room = mysql_fetch_array(mysql_query("SELECT * FROM decryptlab_rooms WHERE id = {$_SESSION['decryptlab_room_id']}"));
      if($decryptlab_room['won_by'] == -1) {
        if($decryptlab_room['revelation_timestamp']-time() >= 0) {
          $participants = json_decode($decryptlab_room['participants']);
          if(in_array($player['id'], $participants)) { 
            $html = "";
            foreach($participants as $participant) {
              $player_in_room = retreive_player($participant);
              $player_in_room_account = retreive_account($player_in_room['account_id']);
              $html .= grade_pseudo($player_in_room_account['pseudo'], $player_in_room_account['grade'])."<br />";
            }
            echo $html;
          }
          else echo 0;
        }
        else echo 0;
      }
      else echo 0;
    }
    if(isset($_POST['is_winner_interval'])) {
      if(!isset($_SESSION['decryptlab_room_id'])) exit(0);
      $decryptlab_room = mysql_fetch_array(mysql_query("SELECT * FROM decryptlab_rooms WHERE id = {$_SESSION['decryptlab_room_id']}"));
      $participants = json_decode($decryptlab_room['participants']);
      if(in_array($player['id'], $participants)) {
        if($decryptlab_room['revelation_timestamp']-time() <= 0) {
          if($decryptlab_room['won_by'] == -1) {
            echo 0;
          }
          else {
            unset($_SESSION['decryptlab_room_id']);
            $winner = retreive_player($decryptlab_room['won_by']);
            $winner_account = retreive_account($winner['account_id']);
            echo "Le joueur ".$winner_account['pseudo']." a décrypté \"".$decryptlab_room['encrypted']."\" en premier. Il fallait trouver \"".$decryptlab_room['decrypted']."\" Il remporte la somme des mises, soit ".(sizeof($participants)*$decryptlab_room['bet'])." tokens !";
          }
        }
        else echo "Erreur.";
      }
      else echo "Erreur.";
    }
    
    if(isset($_POST['submit_response']) && isset($_POST['decrypted'])) {
      $decrypted = mysql_real_escape_string($_POST['decrypted']);
      
      if(!isset($_SESSION['decryptlab_room_id'])) exit(0);
      
      $decryptlab_room = mysql_fetch_array(mysql_query("SELECT * FROM decryptlab_rooms WHERE id = {$_SESSION['decryptlab_room_id']}"));
      if($decryptlab_room['won_by'] != -1) exit(0);
      if($decryptlab_room['revelation_timestamp']-time() > 0) exit(0);
      
      $participants = json_decode($decryptlab_room['participants']);
      if(!in_array($player['id'], $participants)) exit("Erreur.");
      
      if($decryptlab_room['decrypted'] == $decrypted) {
        mysql_query("UPDATE players SET tokens = ".($player['tokens']+(sizeof($participants)*$decryptlab_room['bet']))." WHERE id = {$player['id']}");
        mysql_query("UPDATE decryptlab_rooms SET won_by = {$player['id']} WHERE id = {$_SESSION['decryptlab_room_id']}");
        echo 1;
      }
      else echo 0;
    }
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {  
    if(mysql_num_rows(mysql_query("SELECT * FROM applications_by_players WHERE player_id = {$player['id']} AND application_id = 5")) != 1) exit;
?>
<style>
  #app_decryptlab #players_in_room {
    height:500px;
    width:100%;
    margin:10px 0 0 10px;
    font-size:15px;
    font-weight:bold;
  }
  
  #app_decryptlab #game {
    
  }
  
  #app_decryptlab input.response {
    font-size:17px;
    width:300px;
    margin-top:10px;
    padding:10px;
  }
</style>
<script>
  $('#app_decryptlab #create_room')
    .unbind('submit')
    .bind('submit', function(event) { 
      event.preventDefault();
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/decryptlab.app.php",
        data: {ajax: true, create_room: true, bet: $(this).find('input[type=text]').val()},
        success: function (data) {
          if(data == 1) reload_app('decryptlab');
          else dialog("Erreur lors de la création d'une salle", data);
        }
      });
    });
    
  $('#app_decryptlab .join_room')
    .unbind('click')
    .bind('click', function(event) { 
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/decryptlab.app.php",
        data: {ajax: true, join_room: true, id: $(this).attr('data-id')},
        success: function (data) {
          if(data == 1) reload_app('decryptlab');
          else dialog("Impossible de rejoindre cette salle", data);
        }
      });
    });
</script>
<div class="app" id="app_decryptlab">
  <?php
    $play_in_progress = false;
    if(isset($_SESSION['decryptlab_room_id'])) {
      $resource_decryptlab_room = mysql_query("SELECT * FROM decryptlab_rooms WHERE id='{$_SESSION['decryptlab_room_id']}'");
      if(mysql_num_rows($resource_decryptlab_room) == 1) $play_in_progress = true;
    }
    if($play_in_progress === false) {
  ?>
  <div class="box">
    <h3><img class="icon" src="design/icons/decryptlab.png" />Bienvenue dans DecryptLab.</h3>
    <p>
      DecryptLab oppose des joueurs dans des salles où chacun investit une mise et est confronté, en même temps, à une épreuve de décryptage. Le permier à réussir le décryptage gagne l'ensemble des mises.<br />
      Chaque joueur peut créer 3 salles chaque jour. 
    </p>
  </div>
  <div class="box">
    <form id="create_room">
      <img class="icon" src="design/icons/star.png" /><input type="submit" value="Créer une salle" /> avec une mise de <input type="text" name="bet" size="2" value="25" /> tokens.
    </form>
  </div>
  <div class="box">
    <h4 style="cursor:pointer;margin:0;" onclick="reload_app('decryptlab');"><img class="icon" src="design/icons/refresh_small.png" />Actualiser la liste des salles.</h4>
  </div>  
  <table class="app_table" id="rooms">
    <tr>
      <th>Créateur de la salle</th>
      <th>Mise</th>
      <th>Participants</th>
      <th>Rejoindre</th>
    </tr>
  <?php
    $resource_rooms = mysql_query("SELECT * FROM decryptlab_rooms WHERE won_by = '-1'");
    while ($room = mysql_fetch_array($resource_rooms)) {
      $room_creator = retreive_player($room['creator_id']);
      $room_creator_account = retreive_account($room_creator['account_id']);
      echo '<tr>';
        echo '<td>'.grade_pseudo($room_creator_account['pseudo'], $room_creator_account['grade']).'</td>';
        echo '<td>'.$room['bet'].'</td>';
        echo '<td>'.sizeof(json_decode($room['participants'])).'</td>';
        if($room['revelation_timestamp']-time() > 0) echo '<td><input class="join_room" type="button" value="Rejoindre" data-id="'.$room['id'].'" /></td>';
        else echo '<td><input class="join_room" type="button" value="Partie en cours" disabled="disabled" /></td>';
      echo '</tr>';
    }
  ?>
  </table>
  <?php
    }
    else {
  ?>
    <table style="width:100%;">
      <tr style="vertical-align:top;">
        <td style="width:80%;">
          <script>
            var initial_time = <?php 
              $decryptlab_room = mysql_fetch_array(mysql_query("SELECT * FROM decryptlab_rooms WHERE id='{$_SESSION['decryptlab_room_id']}'"));
              if($decryptlab_room['revelation_timestamp']-time() > 0) echo $decryptlab_room['revelation_timestamp']-time(); 
              else echo "0"; ?>,
                counter_interval,
                refresh_players_interval,
                is_winner_interval;
            
            $('#app_decryptlab #counter_content').text(initial_time);
            
            if(typeof(counter_interval) != "undefined") clearInterval(counter_interval);
            if(typeof(refresh_players_interval) != "undefined") clearInterval(refresh_players_interval);
            if(typeof(is_winner_interval) != "undefined") clearInterval(is_winner_interval);
            
            refresh_players_interval = setInterval(function() {
              $.ajax({ 
                cache: false,
                type: "POST",
                url: "game/apps/decryptlab.app.php",
                data: {ajax: true, retreive_players_in_room: true},
                success: function (data) {
                  if(data == 0) dialog("DecryptLab", "Echec lors de la récupération des joueurs dans la salle");
                  else {
                    if($("<div></div>").html(data).text() != $("#app_decryptlab #players_in_room").text()) {
                      $("#app_decryptlab #players_in_room").html(data).hide().fadeIn(200).hide().fadeIn(200);
                    }
                    else $("#app_decryptlab #players_in_room").html(data);
                  }
                }
              });
            }, 4000);
            
            setTimeout(function() { clearInterval(refresh_players_interval) }, <?php $decryptlab_room = mysql_fetch_array(mysql_query("SELECT * FROM decryptlab_rooms WHERE id='{$_SESSION['decryptlab_room_id']}'")); if($decryptlab_room['revelation_timestamp']-time() > 0) echo ($decryptlab_room['revelation_timestamp']-time())*1000; else echo "0"; ?>);
            
            counter_interval = setInterval(function() {
              if(parseInt($('#app_decryptlab #counter_content').text()) <= 0) {
                
                is_winner_interval = setInterval(function() {
                  $.ajax({ 
                    cache: false,
                    type: "POST",
                    url: "game/apps/decryptlab.app.php",
                    data: {ajax: true, is_winner_interval: true},
                    success: function (data) {
                      if(data != 0) {
                        dialog("DecryptLab", data);
                        clearInterval(is_winner_interval);
                        reload_app("decryptlab");
                      }
                    }
                  });
                }, 3000);
                
                clearInterval(counter_interval);
                $.ajax({ 
                  cache: false,
                  type: "POST",
                  url: "game/apps/decryptlab.app.php",
                  data: {ajax: true, retreive_encrypted: true},
                  success: function (data) {
                    if(data == 0) {
                      dialog("DecryptLab", "Partie inexistante, déjà terminée, ou requête erronée.");
                      reload_app('decryptlab');
                    }
                    else if(data == 2) {
                      dialog("DecryptLab", "Personne n'a rejoint votre salle... Votre mise vous a été rendue.");
                      reload_app('decryptlab');
                    } 
                    else {
                      $('#app_decryptlab #counter').hide();
                      $('#app_decryptlab #game').html("<div style='font-size:20px;display:block;text-align:center;'>Décryptez : <span style='font-family:courier'>"+data+"</span></div>");
                      $('#app_decryptlab #game').append('<center><input class="response" type="text" /></center><br /><br /><br /><small>Vous ne comprenez rien ? C\'est normal la première fois. Observez bien comment les autres font pour décrypter et faites pareil la prochaine fois :)</small>');
                      $('.response').unbind('keydown');
                      $('.response:last')
                        .focus()
                        .bind('keydown', function(event) {
                          if(event.keyCode == 13) {
                            $.ajax({ 
                              cache: false,
                              type: "POST",
                              url: "game/apps/decryptlab.app.php",
                              data: {ajax: true, submit_response: true, decrypted: $(this).val()},
                              success: function (data) {
                                if(data == 0) dialog("DecryptLab", "Désolé, cette réponse est erronée...");
                              }
                            });
                          }
                        });
                    }
                  }
                });
              }
              else {
                $('#app_decryptlab #code').hide();
                $('#app_decryptlab #counter').show();
                $('#app_decryptlab #counter_content').text(parseInt($('#app_decryptlab #counter_content').text())-1);
              }
            }, 1000);
          </script>
          <div class="box" id="game">
            <div style='font-size:20px;display:block;text-align:center;'>Attente de personnes dans la salle... <span id="counter_content">100</span></div>
          </div>
        </td>
        <td style="width:20;">
          <div id="players_in_room"></div>
        </td>
      </tr>
    </table>
  <?php
    }
  ?>
</div>
<br />
<?php
  }
?>