<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */

  function run_command($command, $arguments) {  
    $player = retreive_player($_SESSION['id']);
    
    if(isset($_SESSION['server'])) {
      $current_server = mysql_fetch_array(mysql_query("SELECT * FROM servers WHERE ip = '{$_SESSION['server']['ip']}'"));
      if(!preg_match('/^localhost(.+)$/', $_SESSION['server']['ip']) && $current_server['code'] != $_SESSION['server']['code']) { 
        unset($_SESSION['server']);
        response("failure", "Déconnecté du serveur : quelqu'un a changé le code de ce serveur...", false);
      }
      $_SESSION['server']['time_worked'] = $current_server['time_worked'];
    }
    
    if($arguments) {
      if($command == "buy")            include("buy.command.php");
      elseif($command == "connect")        include("connect.command.php"); 
      elseif($command == "work")           include("work.command.php");
      elseif($command == "infiltrate")     include("infiltrate.command.php");
      elseif($command == "crack")          include("crack.command.php");
      elseif($command == "ping")           include("ping.command.php");
      elseif($command == "bind")           include("bind.command.php");
      
      // BELOW: DEBUG COMMANDS
      elseif($command == "gve" && $_SESSION['grade'] > 1) { // GIVE TOKENS
        $arg = explode(' ', $arguments, 2);
        $pseudo = $arg[0];
        $quantity = $arg[1];
        $account = retreive_account($pseudo, true);
        mysql_query("UPDATE players SET tokens = tokens+$quantity WHERE account_id = {$account['id']}");
        response("success", "[OK]", false);
      }
      elseif($command == "rwt" && $_SESSION['grade'] > 1) { // RESET WORK TIME
        $arg = explode(' ', $arguments, 2);
        $ip = $arg[0];
        if(isset($_SESSION['server'])) $_SESSION['server']['time_worked'] = 0;
        if($ip = "all") mysql_query("UPDATE servers SET time_worked = 0");
        else mysql_query("UPDATE servers SET time_worked = 0 WHERE ip = '$ip'");
        response("success", "[OK]", false);
      }
      elseif($command == "fma" && $_SESSION['grade'] > 1) { // FLASHMATRICE
        $arg = explode(' ', $arguments, 2);
        $tokens = $arg[0];
        if(mysql_num_rows(mysql_query("SELECT * FROM matrice WHERE won_by = ''")) == 0) {
          $matrice = rand(0, 1)?random_string("010101010%$*", 10):random_string("010101010}#?", 10);
          mysql_query("TRUNCATE TABLE matrice");
          mysql_query("INSERT INTO matrice VALUES('', '$matrice', '$tokens', '')");
          response("success", "[OK]", false);
        }
        else response("success", "[FAILURE] Matrice sans gagnant déjà existante", false);
      }
    }
    // FIN.
    
    else {
      if($command == "disconnect") {
        unset($_SESSION['server']);
        response("success", " Déconnecté du serveur.", "reload_app('servermanager');");
      }
      elseif($command == "servrandom")       include("servrandom.command.php");
      elseif($command == "changepassword")   include("changepassword.command.php");
      elseif($command == "secure")           include("secure.command.php");
      elseif($command == "destroy")          include("destroy.command.php");
    }
    return false;
  }
?>