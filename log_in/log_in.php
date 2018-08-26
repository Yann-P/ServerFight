<?php
/*
 Author Yann Pellegrini
 Date 2011
 Licence GPLv3 
*/

  session_start();
  require_once("../socle.php");
  
  if(isset($_POST['action'])) { // On détermine si il s'agit d'une connexion ou une inscription
    
    if($_POST['action'] == "log_out") {
      if(!isset($_SESSION['id'])) exit('3');
      if(isset($_POST['token'])) {
        $token = mysql_real_escape_string($_POST['token']);
        if(check_token($_SESSION['id'], $token)) {
          add_to_logs("Déconnexion");
          session_unset();
          echo 1;
        }
        else {
          add_to_logs("Echec CSRF - Déconnexion");
          echo 2;
        }
      }
    }

    if($_POST['action'] == "log_in" && isset($_POST['pseudo']) && isset($_POST['password'])) {
      $pseudo = mysql_real_escape_string($_POST['pseudo']);
      $password = mysql_real_escape_string($_POST['password']);
        
      if(mysql_num_rows(mysql_query("SELECT * FROM ban_ip WHERE ip = '{$_SERVER['REMOTE_ADDR']}'")) != 0) {
        echo json_encode(array("notifications" => array("type" => "error", "message" => "Vous êtes banni définitivement.")));
        exit;
      }
    
      $resource_account = mysql_query("SELECT * FROM accounts WHERE BINARY pseudo = '$pseudo'");
      $account = mysql_fetch_array($resource_account);
      if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE BINARY pseudo = '$pseudo'")) != 0) {
        if(sha1(SALT.md5($password.SALT)) == $account['password']) {
          $_SESSION['id'] = $account['id'];
          $_SESSION['pseudo'] = $pseudo;
          $_SESSION['grade'] = $account['grade'];
          if(isset($_SESSION['server'])) unset($_SESSION['server']);
          
          mysql_query("UPDATE accounts SET last_log_in_ip = '".$_SERVER['REMOTE_ADDR']."', last_log_in_timestamp = '".time()."', last_log_in_user_agent = '".mysql_real_escape_string(htmlspecialchars($_SERVER['HTTP_USER_AGENT']))."' WHERE pseudo = '$pseudo'");
          
          add_to_logs("Connexion de ".$pseudo);
          add_to_pub_logs("Un joueur s'est connecté à l'instant sur le jeu.");
          
          echo json_encode(array("token" => renew_token($account['id'])));
        }
        else echo json_encode(array("notifications" => array("type" => "error", "message" => "Le mot de passe est incorrect.")));
      }
      else echo json_encode(array("notifications" => array("type" => "error", "message" => "L'identifiant n'existe pas.")));
    }
    
    elseif($_POST['action'] == "sign_in" && isset($_POST['pseudo']) && isset($_POST['password']) && isset($_POST['confirm']) && isset($_POST['email']) && isset($_POST['captcha'])) {
      $pseudo = mysql_real_escape_string($_POST['pseudo']);
      $password = mysql_real_escape_string($_POST['password']);
      $confirm = mysql_real_escape_string($_POST['confirm']);
      $email = mysql_real_escape_string($_POST['email']);
      $captcha = mysql_real_escape_string($_POST['captcha']);
      
      $errors = "";
  
      if(mysql_num_rows(mysql_query("SELECT * FROM ban_ip WHERE ip = '{$_SERVER['REMOTE_ADDR']}'")) != 0)
        $errors .= "Vous êtes banni définitivement de ServerFight et ne pouvez pas recréer de compte.<br />";
        
      if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE sign_in_ip = '{$_SERVER['REMOTE_ADDR']}'")) >= 5
      || mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE last_log_in_ip = '{$_SERVER['REMOTE_ADDR']}'")) >= 5)
        $errors .= "Vous avez déjà créé 2 comptes sur cette IP.<br />";
        
      if($_SESSION['captcha'] != $captcha)
        $errors .= "Le captcha est incorrect.<br />";
      if(strlen($pseudo) > 12 || strlen($pseudo) < 3)
        $errors .= "Pseudo : entre 3 et 12 caractères.<br />";
      if(strlen($password) > 50 || strlen($password) < 6)
        $errors .= "Mot de passe : entre 6 et 50 caractères.<br />";
      if(mysql_num_rows(mysql_query("SELECT pseudo FROM accounts WHERE pseudo = '$pseudo'")) != 0)
        $errors .= "Ce pseudo est déjà utilisé.<br />";
      if(mysql_num_rows(mysql_query("SELECT email FROM accounts WHERE email = '$email'")) != 0)
        $errors .= "Cete adresse e-mail est déjà utilisée.<br />";
      if($password != $confirm)
        $errors .= "La confirmation du mot de passe est invalide.<br />";  
      if(!preg_match('/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\.-]{2,}[\.][a-zA-Z]{2,4}$/', $email))
        $errors .= "Merci de vérifier votre adresse e-mail.<br />";  
      if(!preg_match('/^[a-zA-Z0-9_-]{3,12}$/', $pseudo))
        $errors .= "Votre pseudo contient des caractères non autorisés.<br />";
        
      if($errors != "") echo json_encode(array("notifications" => array("type" => "error", "message" => $errors)));
      else {
        $_SESSION['captcha'] = random_string("123456789", 5);
        $salted_password = sha1(SALT.md5($password.SALT)); // BAD!!
        mysql_query("INSERT INTO accounts VALUES('', '$pseudo', '$salted_password', '$email', '1', '{$_SERVER['REMOTE_ADDR']}', '', '".time()."', '', '', '', '".mysql_real_escape_string(htmlspecialchars($_SERVER['HTTP_USER_AGENT']))."', '', '".random_string("", 50)."', '0', '0', '0', '0')");
        $last_account = mysql_fetch_array(mysql_query("SELECT id FROM accounts ORDER BY id DESC LIMIT 1"));
        $last_id = $last_account['id'];
        mysql_query("INSERT INTO players VALUES('$last_id', '$last_id', '0', '500', '1', '5', '0', '1', '0', '0', '0')");
        mysql_query("INSERT INTO servers VALUES('', '$last_id', 'localhost@$pseudo', '-', '-', '5', '1', '0')");
        recalculate_player_infos($last_id);
        
        add_to_logs("Inscription de ".$pseudo);
        add_to_pub_logs("Un nouveau joueur à rejoint ServerFight !");
        
        mysql_query("INSERT INTO messenger VALUES('', '1', 'Admin', 'Inscription de $pseudo (IP : {$_SERVER['REMOTE_ADDR']})', '#modo', '0')");
        echo 1;
      }  
    }
  }
?>