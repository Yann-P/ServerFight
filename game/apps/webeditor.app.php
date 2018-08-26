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
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
    if(mysql_num_rows(mysql_query("SELECT * FROM applications_by_players WHERE player_id = {$player['id']} AND application_id = 6")) != 1) exit;
?>
<style>
</style>
<script>
$('#app_webeditor #change_email')
  .unbind('submit')
  .bind('submit', function(event) {
    event.preventDefault();
    var password = $(this).find('input[name=password]').val(),
        email = $(this).find('input[name=email]').val();
    $.ajax({ 
      cache: false,
      type: "POST",
      url: "game/apps/profil.app.php",
      data: {ajax: true, change_email: true, password: password, email: email},
      success: function (data) {
        if(data == 1) {
          dialog("Modifié", "Votre adresse e-mail a été modifiée avec succès.");
          reload_app('profil');
        }
        else dialog("Erreur", data);
      }
    });
  });
</script>
<div class="app" id="app_webeditor">
  <div class="box">
    <h3><img class="icon" src="design/icons/webeditor.png">WebEditor</h3><br />
    Mettez ici en page tous vos sites avant de les héberger sur un de vos serveurs.
  </div>
  <div class="box">
  <?php 
    if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE player_id = {$player['id']}")) == 0) {
      echo "Vous n'avez pas de site, crééz en avec la commande <code>site create</code> !";
    }
    else {
      echo "Sélectionner un site ";
      echo '<select id="site_edit">';
      echo '<option value="">Choisissez</option>';
      $resource_sites = mysql_query("SELECT * FROM sites WHERE player_id = {$player['id']}");
      while($site = mysql_fetch_array($resource_sites)) {
        echo '<option value="'.$site['adress'].'">'.$site['adress'].'</option>';
      }
      echo '</select>';
    }
  ?>
  </div>
</div>
<br />
<?php
  }
?>