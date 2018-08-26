<?php
/*
 Author Yann Pellegrini
 Date 2011
 Licence GPLv3 
*/

$host = "localhost";
$user = "ServerFight";
$pass = "";
$base = "ServerFightBeta";


$connect = mysql_connect($host, $user, $pass);
mysql_select_db($base, $connect);

define('ROOT_PATH', dirname(__FILE__) . '/');

define("SALT", "INSERTHEREPRIVATESALT");

function check_session() {
  //add_to_logs("Session.");
  if(isset($_SESSION['id']) && isset($_SESSION['pseudo']) && isset($_SESSION['grade'])) {
    if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE id={$_SESSION['id']}")) == 0) {
      session_unset();
      return false;
    }
    mysql_query("UPDATE accounts SET last_activity_timestamp = '".time()."' WHERE pseudo = '{$_SESSION['pseudo']}'");
    return true;
  }
  else return false;
}

function pair($nombre)
{
   return (($nombre-1)%2);
}

function check_token($account_id, $token) {
  $account = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE id = $account_id"));
  if($token == $account['token']) return true;
  return false;
}

function renew_token($account_id) {
  $new_token = random_string("", 50);
  mysql_query("UPDATE accounts SET token = '$new_token' WHERE id = $account_id");
  return $new_token;
}

function retreive_player($id) {
  return mysql_fetch_array(mysql_query("SELECT * FROM players WHERE account_id = $id"));
}

function retreive_account($id_or_pseudo, $retreive_by_pseudo = false) {
  if(!$retreive_by_pseudo) return mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE id = $id_or_pseudo"));
  else return mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '$id_or_pseudo'"));
}

function random_string($chars, $length)
{
  if($chars == "") $chars = "azertyuiopqsdfghjklmwxcvbn123456789";
	$key = '';
	for($i = 1; $i <= $length; $i++)
	{
		$key .= $chars[rand(0, strlen($chars)-1)];
	}
	return $key;
}

// Commandes de jeu

function recalculate_player_infos($player_id) {
  $total_rams = 0;
  $total_servers = 0;
  $resource_servers = mysql_query("SELECT * FROM servers WHERE player_id = $player_id");
  $average_servers_security = 0;
  while($server = mysql_fetch_array($resource_servers)) { $total_servers++; $total_rams+=$server['rams']; if(!preg_match('/^localhost(.+)$/', $server['ip'])) { $average_servers_security += strlen($server['code']); }}
  $level = round(($total_rams/2)+($total_servers*$total_servers)-3);
  if($total_servers == 1) $average_servers_security = 0;
  else $average_servers_security /= ($total_servers-1);
  mysql_query("UPDATE players SET rams = $total_rams, servers = $total_servers, level = $level, average_servers_security = $average_servers_security WHERE id = $player_id");
}

// ! Commandes de jeu

function is_banned($pseudo) {
  if(mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE pseudo = '$pseudo'")) != 1) return true;
  $account = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE pseudo = '$pseudo'"));
  if($account['banned_until'] > time()) return true;
  return false;
}

function grade_pseudo($pseudo, $grade) {
  if(is_banned($pseudo)) return '<span style="cursor:pointer;" onclick="show_profil(\''.$pseudo.'\');"><img class="icon" src="design/users/banned.png"><s>'.($pseudo).'</s></span>';
  else if($grade == 3) return '<span style="cursor:pointer;" onclick="show_profil(\''.$pseudo.'\');"><img class="icon" src="design/users/administrator.png">'.($pseudo).'</span>';
  else if($grade == 2) return '<span style="cursor:pointer;" onclick="show_profil(\''.$pseudo.'\');"><img class="icon" src="design/users/moderator.png">'.($pseudo).'</span>';
  else if($grade == 1) return '<span style="cursor:pointer;" onclick="show_profil(\''.$pseudo.'\');"><img class="icon" src="design/users/member.png">'.($pseudo).'</span>';
  else return '<span style="cursor:pointer;" onclick="show_profil(\''.$pseudo.'\');"><img class="icon" src="design/users/banned.png">'.$pseudo.'</span>'; // Banni
}

function link_site($message) {
  if(preg_match_all('#_([a-zA-Z0-9-.]+)_#siU', $message, $matches)) {
    for ($i=0;$i<count($matches[1]);$i++) {
      if(    mysql_num_rows(mysql_query("SELECT * FROM sites WHERE adress = '".mysql_real_escape_string($matches[1][$i])."'")) == 1
          || mysql_num_rows(mysql_query("SELECT * FROM servers WHERE ip = '".mysql_real_escape_string($matches[1][$i])."'")) == 1) {
        $message = str_replace($matches[0][$i], '<a href="#" onclick="go_site(\''.$matches[1][$i].'\');">'.$matches[1][$i].'</a>', $message);
      }
    }
  }
  return $message;
}

function smiley($message) {
  $smiley_text = array(':)', '=)', ':D',
                       ':(', ':p', ';)',
                       ':o', ':/',':rire:',
                       ':charte:', 'è_é',':keepcool:',
                       'mdr','^^','-_-', '?_?',
                       ':salut:','*-*',':B',':youpi:', 'NyanNyanCat');
  $smiley_img = array('happy.png','happy.png','excited.png',
                      'sad.png','tongue.png', 'wink.png',
                      'shocked.png','confused.png','rotfl.png',
                      'charte.png','enerve.png','keepcool.png',
                      'rotfl.png','cute.png','bored.png','dequoi.png',
                      'bye.png','wilt.png','cretin.png','youpi.png', 'nyan.gif');
  
  $message = ' '.$message.' ';

  $num_smilies = count($smiley_text);
  for ($i = 0; $i < $num_smilies; ++$i)
  $message = preg_replace("#(?<=.\W|\W.|^\W)".preg_quote($smiley_text[$i], '#')."(?=.\W|\W.|\W$)#m", '$1<img class="smiley" src="design/smiley/'.$smiley_img[$i].'" alt="'.substr($smiley_img[$i], 0, strrpos($smiley_img[$i], '.')).'" />$2', $message);

  return substr($message, 1, -1);
}

function add_to_logs($message) {
  if(isset($_SESSION['pseudo'])) {
    $pseudo = $_SESSION['pseudo'];
    
    $account = retreive_account($_SESSION['id']);
    $player = retreive_player($_SESSION['id']);
    
    $account_session = array(
      "account" => $account,
      "player" => $player
    );
  }
  else {
    $pseudo = "NULL";
    $account_session = "NULL";
  }
  if(isset($_SESSION['server'])) $server_session = $_SESSION['server'];
  else $server_session = "NULL";
  mysql_query("INSERT INTO logs VALUES('', '".time()."', '$pseudo', '".date('H:i').' | '.$message."', '".json_encode($account_session)."', '".json_encode($server_session)."')");
}

function add_to_pub_logs($message) {
  mysql_query("INSERT INTO pub_logs VALUES('', '".$message."')");
}

function parseint($string) {
	if(preg_match('/(\d+)/', $string, $array)) {
		return $array[1];
	} else {
		return 0;
	}
}

function encodeURI($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}
?>