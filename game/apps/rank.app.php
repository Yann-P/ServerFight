<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
   
  if(isset($_POST['ajax']) && isset($_POST['order_by']) && isset($_POST['page'])) {
    session_start();
    require_once("../../socle.php");
    if(!check_session()) exit;
    
    if($_POST['order_by'] == "level") $order_by = 'level';
    elseif($_POST['order_by'] == "average_servers_security") $order_by = 'average_servers_security';
    elseif($_POST['order_by'] == "servers") $order_by = 'servers';
    elseif($_POST['order_by'] == "hacked_servers") $order_by = 'hacked_servers';
    else exit;
    
    $page = mysql_real_escape_string($_POST['page']);
    $resource_total_ranked_players = mysql_query("SELECT * FROM players ORDER BY $order_by DESC");
    $num_pages = mysql_num_rows($resource_total_ranked_players)/20;
    if(!is_numeric($page) || $page > $num_pages) exit("Erreur pagination.");
    $min_result = $page*20;
    $max_result = $min_result+20-$page*20;
    
    $resource_ranked_players = mysql_query("SELECT * FROM players ORDER BY $order_by DESC LIMIT ".$min_result.", ".$max_result);
    $ranked_players = array();
    
    $html = '<table class="app_table">';
    $html .= '<tr><th>Pseudo</th><th>Niveau</th><th>Security level</th><th>Nombre de serveurs</th><th>Serveurs piratés</th></tr>';
    while($ranked_player = mysql_fetch_array($resource_ranked_players)) {
      $ranked_player_account = retreive_account($ranked_player['account_id']);
      $html .= '<tr>';
      $html .= '<td>'.grade_pseudo($ranked_player_account['pseudo'], $ranked_player_account['grade']).'</td>';
      $html .= '<td>'.$ranked_player['level'].'</td>';
      $html .= '<td>'.$ranked_player['average_servers_security'].'</td>';
      $html .= '<td>'.$ranked_player['servers'].'</td>';
      $html .= '<td>'.$ranked_player['hacked_servers'].'</td>';
      $html .= '</tr>';
    }
    $html .= '</table>';
    $html .= '<table style="width:100%;">';
    $html .= '<tr>';
    $html .= '<td style="width:10%;">';
      if($page != 0) $html .= '<input type="button" id="previous_page" value="<">';
    $html .= '</td>';
    $html .= '<td style="width:80%;">';
    $html .= '<select id="page">';
    
    for($i = 0; $i <= $num_pages; $i++) {
      if($page == $i) $html .= '<option value="'.$i.'" selected="selected">Page '.($i+1).'</option>';
      else $html .= '<option value="'.$i.'">Page '.($i+1).'</option>';
    }
    $html .= '</select>';
    $html .= '</td>';
    $html .= '<td style="width:10%;">';
      if($page+1 < $num_pages) $html .= '<input type="button" id="next_page" value=">">';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    echo $html;
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
?>
<style>
  #app_rank #page,  #app_rank #next_page,  #app_rank #previous_page{
    width:100%;
  }
</style>
<script>
  retreive_rank($('#order_by').val(), 0);
  
  $('#app_rank #order_by, #app_rank #page').die('change').live('change', function() {
    retreive_rank($('#app_rank #order_by').val(), $('#app_rank #page').val());
  });
  
  $('#app_rank #next_page').die('click').live('click', function() {
    $('#app_rank #page').val(parseInt($('#app_rank #page').val())+1).change();
  });
  
  $('#app_rank #previous_page').die('click').live('click', function() {
    $('#app_rank #page').val(parseInt($('#app_rank #page').val())-1).change();
  });
  
  function retreive_rank(order_by, page) {
    $('#app_rank #rank table:first').css('opacity', '0.5');
    $.ajax({ 
      cache: false,
      type: "POST",
      url: "game/apps/rank.app.php",
      data: {ajax: true, order_by: order_by, page: page},
      success: function (data) {
        $('#app_rank #rank')
          .html(data)
          .find('table:first')
          .css('opacity', '1')
          .find('tr:odd')
          .css('background', 'rgba(255, 255, 255, 0.2)');
      }
    });
  }
</script>
<div class="app" id="app_rank">
  <div class="box">
    <b>Trier par</b> : <select id="order_by"><option value="level" selected="selected">Niveau global</option><option value="average_servers_security">Security level</option><option value="servers">Nombre de serveurs</option><option value="hacked_servers">Serveurs piratés</option></select>
  </div>
  <div id="rank"></div>
  <br />
</div>
<br />
<?php
  }
?>