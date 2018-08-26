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
    
    if(isset($_POST['change_password']) && isset($_POST['old_pass']) && isset($_POST['new_pass']) && isset($_POST['confirm'])) {
      $old_pass = mysql_real_escape_string($_POST['old_pass']);
      $new_pass = mysql_real_escape_string($_POST['new_pass']);
      $confirm = mysql_real_escape_string($_POST['confirm']);
      
      if($account['password'] != sha1(SALT.md5($old_pass.SALT)))
        exit("Votre ancien mot de passe ne correspond pas à ce que vous avez saisi.");
      if(strlen($new_pass) > 50 || strlen($new_pass) < 6)
        exit("La longueur du mot de passe doit être comprise entre 6 et 50 caractères.");
      if($confirm != $new_pass)
        exit("La confirmation du mot de passe est invalide.<br />");
      $salted_password = sha1(SALT.md5($new_pass.SALT));
      mysql_query("UPDATE accounts SET password = '$salted_password' WHERE id = '{$_SESSION['id']}'");
      echo 1;
    }
    if(isset($_POST['change_email']) && isset($_POST['password']) && isset($_POST['email'])) {
      $password = mysql_real_escape_string($_POST['password']);
      $email = mysql_real_escape_string($_POST['email']);
      
      if($account['password'] != sha1(SALT.md5($password.SALT)))
        exit("Le mot de passe saisi est invalide.");
      if(mysql_num_rows(mysql_query("SELECT email FROM accounts WHERE email = '$email'")) != 0)
        exit("Cete adresse e-mail est déjà utilisée.");
      if(!preg_match('/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\.-]{2,}[\.][a-zA-Z]{2,4}$/', $email))
        exit("Cete adresse e-mail n'est pas correcte.");
        
      mysql_query("UPDATE accounts SET email = '$email' WHERE id = '{$_SESSION['id']}'");
      echo 1;
    }  
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
?>
<style>
</style>
<script>
$('#app_profil #change_password')
  .unbind('submit')
  .bind('submit', function(event) {
    event.preventDefault();
    var old_pass = $(this).find('input[name=old]').val(),
        new_pass = $(this).find('input[name=new]').val(),
        confirm = $(this).find('input[name=confirm]').val();
    $.ajax({ 
      cache: false,
      type: "POST",
      url: "game/apps/profil.app.php",
      data: {ajax: true, change_password: true, old_pass: old_pass, new_pass: new_pass, confirm: confirm},
      success: function (data) {
        if(data == 1) {
          dialog("Modifié", "Votre mot de passe a été modifié avec succès.");
          reload_app('profil');
        }
        else dialog("Erreur", data);
      }
    });
  });
  
$('#app_profil #change_email')
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
<div class="app" id="app_profil">
  <div class="box">
    <h3><img class="icon" src="design/icons/profil.png">Votre profil, <?php echo $_SESSION['pseudo']; ?> ! <img class="smiley" src="design/smiley/cretin.png"></h3>
  </div>
  <div class="box">
    <table>
      <tr style="vertical-align:top;">
        <td style="padding-right:30px;">
          <h4>Modifier votre mot de passe</h4><br />
          <form id="change_password">
            <table>
              <tr>
                <td>Ancien</td>
                <td><input type="password" name="old" /></td>
              </tr>
              <tr>
                <td>Nouveau</td>
                <td><input type="password" name="new" /></td>
              </tr>
              <tr>
                <td>Confirmez</td>
                <td><input type="password" name="confirm" /></td>
              </tr>
              <tr>
                <td></td>
                <td><input type="submit" value="Modifier" /></td>
              </tr>
            </table>
          </form>
        </td>
        <td>
          <h4>Modifier votre adresse e-mail</h4><br />
          <form id="change_email">
            <table>
              <tr>
                <td>Mot de passe</td>
                <td><input type="password" name="password" /></td>
              </tr>
              <tr>
                <td>Email</td>
                <td><input type="text" name="email" /></td>
              </tr>
              <tr>
                <td></td>
                <td><input type="submit" value="Modifier" /></td>
              </tr>
            </table>
          </form>
        </td>
      </tr>
    </table>
  </div>
</div>
<br />
<?php
  }
?>