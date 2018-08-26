<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
  session_start();
  require_once("../socle.php");
  if(!check_session()) exit;
  $player = retreive_player($_SESSION['id']);
  $account = retreive_account($_SESSION['id']);
  
  if(isset($_POST['code'])) { 
    $code = mysql_real_escape_string($_POST['code']);
    $resource_matrice = mysql_query("SELECT * FROM matrice WHERE code = '{$code}'");
    if(mysql_num_rows($resource_matrice) == 1) {
      $matrice = mysql_fetch_array($resource_matrice);
      if($matrice['code'] == $code && $matrice['won_by'] == "") {
        if($matrice['tokens'] == -1) {
          mysql_query("UPDATE accounts SET bonus = ".($account['bonus']+1)." WHERE id = {$account['id']}");
          mysql_query("UPDATE matrice SET won_by = '{$_SESSION['pseudo']}' WHERE code = '{$matrice['code']}'");
          mysql_query("INSERT INTO messenger VALUES('', '0', '#sys', '".utf8_encode($_SESSION['pseudo'].' a recopié en premier la matrice et remporte 1 bonus :D !')."', '0', '0')");
          echo json_encode(array("result" => "Félicitations ! Vous avez été le premier à recopier cette matrice 'Bonus' et vous gagnez 1 bonus !"));
        }
        elseif($matrice['tokens'] == -2) {
          mysql_query("UPDATE accounts SET bonus = ".($account['bonus']+5)." WHERE id = {$account['id']}");
          mysql_query("UPDATE matrice SET won_by = '{$_SESSION['pseudo']}' WHERE code = '{$matrice['code']}'");
          mysql_query("INSERT INTO messenger VALUES('', '0', '#sys', '".utf8_encode($_SESSION['pseudo'].' a recopié en premier la matrice et remporte 5 bonus :youpi: !')."', '0', '0')");
          echo json_encode(array("result" => "Félicitations ! Vous avez été le premier à recopier cette matrice 'Super bonus' et vous gagnez 5 bonus !"));
        }
        else {
          mysql_query("UPDATE players SET tokens = ".($player['tokens']+$matrice['tokens'])." WHERE id = {$player['id']}");
          mysql_query("UPDATE matrice SET won_by = '{$_SESSION['pseudo']}' WHERE code = '{$matrice['code']}'");
          mysql_query("INSERT INTO messenger VALUES('', '0', '#sys', '".utf8_encode($_SESSION['pseudo'].' a recopié en premier la matrice et remporte '.$matrice['tokens'].' tokens :) !')."', '0', '0')");
          echo json_encode(array("result" => "Félicitations ! Vous avez été le premier à recopier cette matrice et vous gagnez {$matrice['tokens']} tokens !"));
        }
        add_to_pub_logs("{$account['pseudo']} a recopié une matrice avant tout le monde.");
      }
      else echo json_encode(array("result" => "Vous n'avez pas été le plus rapide, désolé... {$matrice['won_by']} l'a recopié avant vous :p"));
    }
    else echo json_encode(array("result" => "Vous avez mal recopié la matrice :p"));
  }
?>  