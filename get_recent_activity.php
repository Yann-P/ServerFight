<?php
/*
 Author Yann Pellegrini
 Date 2011
 Licence GPLv3 
*/
  session_start();
  require_once("socle.php");
  
  if(isset($_POST['retreive'])) {
    $html = '<table style="width:100%;">';
    $html .= '<tr style="vertical-align:top;">';
    $html .= '<td style="width:25%;color:#333;text-align:center;">';
    $html .= '<b>Connectés</b><br />';
    $html .= '<span style="font-size:45px;">'.mysql_num_rows(mysql_query("SELECT * FROM accounts WHERE last_activity_timestamp > ".(time() - 30)." ORDER BY grade DESC")).'</span>';
    $html .= '<td style="width:75%;">';
    $resource_pub_logs = mysql_query("SELECT * FROM pub_logs ORDER BY id DESC LIMIT 5");
    while($pub_log = mysql_fetch_array($resource_pub_logs)) {
      $html .= $pub_log['message'].'<br />';
    }
    $html .= '</td>';
    $html .= '<tr>';
    $html .= '</table>';
    
    echo $html;
  }
?>  