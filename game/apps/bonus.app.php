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
    
    if(isset($_POST['bonus'])) {
      if($_POST['bonus'] == "1") { // Coup de vent, -20% sur tous les serveurs
        if($account['bonus'] < 2) exit('0');
        $resource_player_servers = mysql_query("SELECT * FROM servers WHERE player_id = {$player['id']}");
        while($server = mysql_fetch_array($resource_player_servers)) {
          if($server['time_worked'] <= 2) {
            mysql_query("UPDATE servers SET time_worked = '0' WHERE id = {$server['id']}");
          }
          else {
            mysql_query("UPDATE servers SET time_worked = '".($server['time_worked']-2)."' WHERE id = {$server['id']}");
          }
        }
        echo "Vos serveurs peuvent maintenant travailler 2 heures de plus !";
        
        //add_to_player_history($player['id'], "Utilisation d'un bonus : réduction de 20% la température de tous les seveurs.");
        
        mysql_query("INSERT INTO bonus_logs VALUES('', '{$account['pseudo']} | UTILISATION // coup_de_vent', '".time()."')");
        mysql_query("UPDATE accounts SET bonus = ".($account['bonus']-2)." WHERE id = {$account['id']}");
      }
      elseif($_POST['bonus'] == "2") { // Douche froide, 0%
        if($account['bonus'] < 4) exit('0');
        $resource_player_servers = mysql_query("SELECT * FROM servers WHERE player_id = {$player['id']}");
        while($server = mysql_fetch_array($resource_player_servers)) {
          mysql_query("UPDATE servers SET time_worked = '0' WHERE id = {$server['id']}");
        }
        echo "Vos serveurs ont récupéré toutes leurs heures de travail !";
        
        //add_to_player_history($player['id'], "Utilisation d'un bonus : remise à zéro de la température de tous les serveurs");
        
        mysql_query("INSERT INTO bonus_logs VALUES('', '{$account['pseudo']} | UTILISATION // douche_froide', '".time()."')");
        mysql_query("UPDATE accounts SET bonus = ".($account['bonus']-4)." WHERE id = {$account['id']}");
      }
    }
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
?>
<style>
  #app_bonus .option {
    margin-bottom:10px;
    padding:7px;
    background:rgba(255, 255, 255, 0.3);
    border:1px solid rgba(255, 255, 255, 0.4);
    border-radius:2px;
    cursor:pointer;
  }
  #app_bonus .option:hover {
    background:rgba(255, 255, 255, 0.4);
    border:1px solid rgba(255, 255, 255, 0.5);
  }
  #app_bonus .option b {
    font-size:17px;
  }
</style>
<script>
$('#app_bonus #buy_bonus')
  .unbind('click')
  .bind('click', function() {
    if($('#app_bonus #buy_container').css('display') == "none") {
      $('#app_bonus #buy_container').fadeIn();
    }
    else $('#app_bonus #buy_container').fadeOut();
  });
  
$('#app_bonus .option')
  .unbind('click')
  .bind('click', function() {
    if(confirm("Confirmer cette action ?")) {
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/bonus.app.php",
        data: {ajax: true, bonus: $(this).attr('data-id')},
        success: function (data) {
          if(data == '0') {
            dialog("Echec...", "Vous n'avez pas assez de Bonus pour cela...");
          }
          else {
            dialog("Merci !", data);
            reload_app('bonus');
            reload_app('servermanager');
          }
        }
      });
    }
  });
  
function submit_code() {
  var code = $('#code_0').val();
  $.ajax({ 
    cache: false,
    type: "GET",
    url: "game/apps/bonus_buy.php",
    data: {code: code},
    success: function (data) {
      
      if(data == '0') {
        dialog("Echec...", "Ce code n'est pas valide. Merci de rééssayer. Si vous êtes sur de l'avoir recopié, envoyez aussitôt que possible le code défectueux par MP à l'admin !");
      }
      else {
        dialog("Merci !", "Vous avez été crédité de 5 bonus !");
        reload_app('bonus');
      }
    }
  });
}

</script>
<div class="app" id="app_bonus" data-name="bonus">
  <div class="box">
    <h3><img class="icon" src="design/icons/bonus.png">Bonus !</h3><br />
    Bienvenue sur l'application bonus. Ici, vous pouvez dépenser des bonus pour booster votre partie sur ServerFight en gagnant du temps de jeu. Les bonus se gagnent avec les Flash'matrice oranges et rouges.<br /><br />Vous pouvez aussi <b>acheter</b> des bonus par SMS ou Audiotel. Tous les bonus achetés vous seront restitués en cas de remise à zéro du jeu au changement de version.
  </div>
  <div class="box">
    <h3>Utiliser un Bonus - cliquez sur le bonus qui vous intéresse :</h3><br />
    <div class="option" data-id="1">
      <span style="float:right;"><big><b>2 bonus</b></big><img class="icon" src="design/icons/bonus.png" style="width:20px;height:20px;"></span>
      <img class="icon" src="design/icons/wind.png" style="width:20px;height:20px;"> <b>Coup de vent</b>
      <p>
        Cette bise matinale fera redescendre la température de l'intégralité de vos serveurs de <b>20%</b>.
     </p>
    </div>
    <div class="option" data-id="2">
      <span style="float:right;"><big><b>4 bonus</b></big><img class="icon" src="design/icons/bonus.png" style="width:20px;height:20px;"></span>
      <img class="icon" src="design/icons/cold.png" style="width:20px;height:20px;"> <b>Douche froide</b>
      <p>
        Cette douche d'eau en poudre <u>remettra la température de tous vos serveurs à <b>0%</b></u>.
      </p>
    </div>
  </div>
  <br /><hr /><br />
  <div id="buy_container">
      <h3><img class="icon" src="design/icons/lightning.png">Acheter des bonus</h3><br />
      <!-- Was used for inapp purchases. -->
    </td></tr></table>
    </div>
</div>
<br />
<?php
  }
?>