<?php
/*
 Author Yann Pellegrini
 Date 2011
 Licence GPLv3 
*/
  session_start();
  require_once("socle.php");
?>  
<!DOCTYPE html>
<html>
  <head>
    <title>ServerFight</title>
    <meta charset="utf-8" />
    <link rel="icon" href="favicon.ico" />
  	<link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="design/webOS.css" />
    <link rel="stylesheet" href="design/ui/jquery-ui.css" />
    <script>CONFIG = {};</script>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery-ui.js"></script>
    <script type="text/javascript" src="js/plugins.js"></script>
    <script type="text/javascript" src="js/webOS.js"></script>
    <script type="text/javascript" src="js/log_in.js"></script>
    <script type="text/javascript" src="js/desktop.js"></script>
    <script type="text/javascript" src="js/apps.js"></script>
    <script type="text/javascript" >
      $(document).ready(function() {
        var accordions_content = {
          '1' : $('.accordion[data-id=1]').html(),
          '2' : $('.accordion[data-id=2]').html(),
          '3' : $('.accordion[data-id=3]').html()
        };
        $.each($('.accordion.close'), function(index, obj) {
          $obj = $(obj);
          $obj.html('');
          $.each($(this).attr('data-title').split(''), function(index, letter) {
            $obj.append(letter+'<br />');
          });
        });
        $('.accordion.close').live('click', function() {
          $('.accordion.open').html('').stop().animate({
            'width': '20px',
            'padding': '5px',
            'height': '360px',
            'opacity': '0.8'
          }, 300, function() {
            $(this).removeClass('open').addClass('close');
            $obj = $(this);
            $obj.html('');
            $.each($(this).attr('data-title').split(''), function(index, letter) {
              $obj.append(letter+'<br />');
            });
          });
          $(this).html('').stop().animate({
            'width': '300px',
            'padding': '20px',
            'height': '330px',
            'opacity': '1'
          }, 300, function() {
            $(this).removeClass('close').addClass('open');
            $(this).html(accordions_content[$(this).attr('data-id')]);
          });
        });
      });
    </script>
    <?php 
      if(isset($_SESSION['id'])) {
        echo "<script type='text/javascript' >CONFIG['token'] = '".renew_token($_SESSION['id'])."';</script>";
        echo "<script type='text/javascript' >$(function() { $('#log_in_container').hide(); get_desktop(); $('#achievements_container').show(); $('#bar').show(); $('#right_bar_container').hide(); $('#cgu').hide(); $('#news_alert').hide(); clearInterval(recent_activity_interval); });</script>";
      }
    ?>
  </head>
  <body>
    <audio id="message_sound" preload="preload">
      <source src="design/message.mp3" type="audio/mpeg">
    </audio>
    <div id="achievements_container" ondbclick="$('this').remove();"><div id="achievements"><img class="smiley" src="design/smiley/cretin.png" /> Content de vous revoir !</div></div>
    <div id="desktop"></div>
    <div id="log_in_container">
      <table>
        <tr>
          <td>
            <div class="accordion open" data-title="CONNEXION" data-id="1">
              <form id="log_in">
                <h3>Bienvenue sur Server Fight</h3>
                Merci de vous connecter sur le WebOs.<br /><br />
                <img src="design/user.png" title="Pseudo" class="icon"><input type="text" name="pseudo" placeholder="Pseudo" /><br />
                <img src="design/key.png" title="Mot de passe" class="icon"><input type="password" name="password" placeholder="Mot de passe" /><br />
                <span id="sign_in" style="display: none;">
                <img src="design/key.png" title="Confirmation" class="icon"><input type="password" name="confirm" placeholder="Confirmation du mot de passe" /><br />
                <img src="design/email.png" title="Email" class="icon"><input type="text" name="email" placeholder="Adresse e-mail" /><br />
                <img src="log_in/captcha.php" title="Anti-robot" class="icon" id="captcha_image" onclick="this.src='log_in/captcha.php';" title="Cliquez ici pour obtenir un nouveau code si celui ci est illisible."><input style="width:170px;" type="text" name="captcha" placeholder="Recopiez l'image" />
                <span style="color:grey;text-shadow:1px 1px 0 white;font-size:11px;">Recopiez les 6 chiffres. Cliquez dessus pour le <a href="#" onclick="document.getElementById('captcha_image').src='log_in/captcha.php';">regénérer</a>.</span>
                <br />
                </span>
                <br />
                <input type="submit" value="Valider" SALT/><input type="button" value="Créer un compte !" onclick="$('#presentation').hide();$('#sign_in').fadeIn(1000);$('input[type=submit]').val('Terminer').css('width', '100%');$('input[type=button]').hide();" />
                <div id="presentation">
                  <div>ServerFight est un <em>jeu de simulation</em> amateur dans lequel vous gérez des serveurs virtuels, effectuez des missions et piratez les serveurs d'autres joueurs.</div>
                  <div id="recent_activity">
                    <table style="width:100%;"><tr style="vertical-align:top;"><td style="width:25%;color:#333;text-align:center;"><b>Connectés</b><br /><span style="font-size:45px;">?</span><td style="width:75%;">...</td><tr></table>  
                  </div>
                </div>
              </form>
            </div>
          </td>
          <td>
            <div class="accordion close" data-title="STATISTIQUES" data-id="2">
              <h3>Statiques du jeu</h3>
              Le jeu en chiffres<br /><br />
              <ul>
              <?php
                $last_account = mysql_fetch_array(mysql_query("SELECT * FROM accounts ORDER BY id DESC LIMIT 1"));
                echo "<li><b>Inscriptions :</b> {$last_account['id']}</li>";
                echo "<li><b>Joueurs actifs :</b> ".mysql_num_rows(mysql_query("SELECT * FROM accounts"))."</li><br />";
                echo "<li><b>Serveurs en jeu :</b> ".mysql_num_rows(mysql_query("SELECT * FROM servers"))."</li>";
                echo "<li><b>Infiltrations dans les serveurs entre joueurs :</b> ".mysql_num_rows(mysql_query("SELECT * FROM infiltrations_history"))."</li>";
                echo "<li><b>Parties de DecryptLab :</b> ".mysql_num_rows(mysql_query("SELECT * FROM decryptlab_rooms"))."</li>";
                echo "<li><b>Nombre de sites :</b> ".mysql_num_rows(mysql_query("SELECT * FROM sites"))."</li><br />";
                $hour = strftime("%H");
                $active_players_inweek = mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE last_activity_timestamp > ".(time()-60*60*24*7)));
                echo "<li><b>Connectés cette semaine :</b> $active_players_inweek</li>";
                $active_players_today = mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE last_activity_timestamp > ".(time()-60*60*24)));
                echo "<li><b>Connectés aujourd'hui :</b> $active_players_today</li>";
                echo "<li><b>En train de jouer :</b> ".mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE last_activity_timestamp > ".(time() - 30)." ORDER BY grade DESC"))."</li>";
                ?>
              </ul>
            </div>
          </td>
          <td>
            <div class="accordion close" data-title="OUBLI DE MOT DE PASSE" data-id="3">
              Cette fonctionnalité n'est pas encore implémentée.<br />
            </div>
          </td>
        </tr>
      </table>
    </div>
    <div id="bar_container">
      <ul id="bar" style="display:none;"></ul>
    </div>
    <div id="cgu">Yann P. - Tous droits réservés - <a href="#" onclick="alert('Merci de me contacter à [admin email] en attendant un système de contact durable.')">Contact</a> - <a href="cgu/" target="_blank">CGU</a> - <a href="legal/" target="_blank">Mentions légales</a></div>
  </body>
</html>