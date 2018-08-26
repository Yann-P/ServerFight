<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
   
  function bbcode($text) {
    $text = preg_replace('#\[h1](.+?)\[/h1]#si','<h1>$1</h1>',$text); 
    $text = preg_replace('#\[h2](.+?)\[/h2]#si','<h2>$1</h2>',$text);
    $text = preg_replace('#\[h3](.+?)\[/h3]#si','<h3>$1</h3>',$text);
    $text = preg_replace('#\[h4](.+?)\[/h4]#si','<h4>$1</h4>',$text);
    $text = preg_replace('#\[h5](.+?)\[/h5]#si','<h5>$1</h5>',$text);
    $text = preg_replace('#\[h6](.+?)\[/h6]#si','<h6>$1</h6>',$text);
    $text = preg_replace('#\[b](.+?)\[/b]#si','<b>$1</b>',$text); 
    $text = preg_replace('#\[u](.+?)\[/u]#si','<u>$1</u>',$text);
    $text = preg_replace('#\[i](.+?)\[/i]#si','<em>$1</em>',$text);
    $text = preg_replace('#\[img](.+?)\[/img]#si','<img style="max-width:100%;" src="$1" />',$text);
    $text = preg_replace('#\[right](.+?)\[/right]#si','<div style="text-align:right; width:100%;">$1</div>',$text); 
    $text = preg_replace('#\[left](.+?)\[/left]#si','<div style="text-align:left; width:100%;">$1</div>',$text); 
    $text = preg_replace('#\[center](.+?)\[/center]#si','<div style="text-align:center; width:100%;">$1</div>',$text); 
    $text = preg_replace('#\[justify](.+?)\[/justify]#si','<div style="text-align:justify; width:100%;">$1</div>',$text); 
    $text = preg_replace('#\[color=(.+?)](.+?)\[/color]#si','<span style="color:$1;">$2</span>',$text); 
    $text = preg_replace('#\[size=([0-9]{1,2})](.+?)\[/size]#si','<span style="font-size:$1px;">$2</span>',$text); 
    $text = preg_replace('#\[font=(arial|verdana|georgia|calibri|courier|consolas)](.+?)\[/font]#si','<span style="font-family:$1;">$2</span>',$text);
    $text = preg_replace('#\[cadre border=(.+?) background=(.+?) padding=(.+?)](.+?)\[/cadre]#si','<div style="display:block;border:1px solid $1;background:$2;padding:$3px;">$4</div>',$text);
    $text = preg_replace('`\[list](.+?)\[/list]`si','<ul>$1</ul>',$text);
    $text = preg_replace('`\[\*](.+?)\[/\*]`si','<li>$1</li>',$text);
    return $text;
  }
  
  if(isset($_POST['ajax'])) {
    session_start();
    require_once("../../socle.php");
    if(!check_session()) exit;
    $account = retreive_account($_SESSION['id']); 
    $player = retreive_player($_SESSION['id']); 
    
    if(isset($_POST['url'])) {
      $url = mysql_real_escape_string($_POST['url']);
      if($url == "about:sitelist") {
        $resource_sites = mysql_query("SELECT * FROM sites WHERE hosted_on != '0' ORDER BY nb_links DESC");
        echo '<table class="app_table">';
        echo '<tr><th>Adresse</th><th>Propriétaire</th><th>Hébergé sur</th><th>Pointé</th></tr>';
        $sites = array();
        while($site = mysql_fetch_array($resource_sites)) {
          $hash = array();
          $owner = retreive_player($site['player_id']);
          $owner_account = retreive_account($owner['account_id']);
          $hash['link'] = '<a href="#" onclick="browse_url(\''.$site['adress'].'\')">'.$site['adress'].'</a>';
          $hash['owner'] =  grade_pseudo($owner_account['pseudo'], $owner_account['grade']);
          $hash['host'] = '<a href="#" onclick="browse_url(\''.$site['hosted_on'].'\')">'.$site['hosted_on'].'</a>';
          $hash['counter'] = $site['nb_links'].' fois';
          array_push($sites, $hash);
        }
        foreach ($sites as $site) {
          echo '<tr>';
          echo "<td>{$site['link']}</td>";
          echo "<td>{$site['owner']}</td>";
          echo "<td>{$site['host']}</td>";
          echo "<td>{$site['counter']}</td>";
          echo '</tr>';
        }
        echo '</table>';
        exit;
      }
      elseif($url == "about:sitemanager") {
        echo '<div class="box">';
        echo '  <form id="create_site">';
        echo '    <img class="icon" src="design/icons/star.png">';
        echo '    <input type="submit" value="Créer un site" />';
        echo '    <input type="text" name="adress" placeholder="à l\'adresse..." />';
        echo '  </form>';
        echo '</div>';
        
        echo '<div class="box">';
        echo '  <form id="edit_site">';
        echo '    <img class="icon" src="design/icons/bullet_arrow.png">';
        echo '    <input type="submit" value="Editer" />';
        echo '    <select name="adress">';
        echo '       <option value="">le site...</option>';
                    $resource_sites = mysql_query("SELECT * FROM sites WHERE player_id = {$player['id']}");
                    while($site = mysql_fetch_array($resource_sites)) {
                      echo '<option value="'.$site['adress'].'">'.$site['adress'].'</option>';
                    }
        echo '    </select>';
        echo '  </form>';
        echo '</div>';
        
        echo '<div class="box">';
        echo '  <form id="host_site">';
        echo '    <img class="icon" src="design/icons/server.png">';
        echo '    <input type="submit" value="Héberger" />';
        echo '    <select name="adress">';
        echo '      <option value="">le site...</option>';
                    $resource_sites = mysql_query("SELECT * FROM sites WHERE player_id = {$player['id']}");
                    while($site = mysql_fetch_array($resource_sites)) {
                      echo '<option value="'.$site['adress'].'">'.$site['adress'].'</option>';
                    }
        echo '    </select>';
        echo '    <select name="server">';
        echo '      <option value="">sur le serveur...</option>';
        echo '      <option value="0">[Aucun]</option>';
                    $resource_servers = mysql_query("SELECT * FROM servers WHERE player_id = {$player['id']} AND ip != 'localhost@".$_SESSION['pseudo']."'");
                    while($server = mysql_fetch_array($resource_servers)) {
                      echo '<option value="'.$server['ip'].'">'.$server['ip'].'</option>';
                    }
        echo '    </select>';
        echo '  </form>';
        echo '</div>';
        
        echo '<div class="box">';
        echo '  <form id="delete_site">';
        echo '    <img class="icon" src="design/icons/delete.png">';
        echo '    <input type="submit" value="Supprimer" />';
        echo '    <select name="adress">';
        echo '       <option value="">le site...</option>';
                    $resource_sites = mysql_query("SELECT * FROM sites WHERE player_id = {$player['id']}");
                    while($site = mysql_fetch_array($resource_sites)) {
                      echo '<option value="'.$site['adress'].'">'.$site['adress'].'</option>';
                    }
        echo '    </select>';
        echo '  </form>';
        echo '</div>';
        exit;
      }
      else {
        $resource_site = mysql_query("SELECT * FROM sites WHERE adress = '$url'");
        if(mysql_num_rows($resource_site) == 1) {
          $site = mysql_fetch_array($resource_site);
          $owner = retreive_player($site['player_id']);
          $owner_account = retreive_account($owner['account_id']);
          if($site['hosted_on'] == '0') {
            echo '<div id="site_view">';
            echo '<h1>'.$url.' n\'est pas accessible</h1>'
                ."<p>Ce site n'est pas hébergé. Il doit être associé à un serveur pour pouvoir être affiché.</p>";
            echo '</div>';
            exit;
          }
          $owner = retreive_player($site['player_id']);
          $owner_account = retreive_account($owner['account_id']);
          echo '<div id="site_view">';
          echo stripslashes(link_site(bbcode(nl2br(htmlspecialchars($site['content'])))));
          echo '</div>';
          exit;
        }
        else {
          $resource_server = mysql_query("SELECT * FROM servers WHERE ip = '$url'");
          if(mysql_num_rows($resource_server) == 1) {
            $server = mysql_fetch_array($resource_server);
            $owner = retreive_player($server['player_id']);
            $owner_account = retreive_account($owner['account_id']);
            echo '<div id="site_view">';
            echo '<h1>'.$url.' </h1>'
                ."<p>Le ping a répondu en ".rand(100, 600)." ms."
                ."</p>"
                .'<br /><hr />'
                .'<em>ServerFight Server - '.$url.'@'.$owner_account['pseudo'].' (Security : '.(strlen($server['code'])).').';
            echo '</div>';
            exit;
          }
        }
        echo '<br /><img class="icon" src="design/icons/browser.png"><big><b>Routage impossible</b> URL invalide ou serveur non trouvé.</big>';
      }
    }
    
    
    if(isset($_POST['create_site']) && isset($_POST['adress'])) {
      $adress = mysql_real_escape_string($_POST['adress']);
      
      if(is_banned($account['pseudo'])) exit("Vous êtes banni et ne pouvez effectuer d'action sur les sites.");
      if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE player_id = {$player['id']}")) >= 5)
        exit("Vous ne pouvez pas avoir plus de 5 sites. Vous pouvez toujours en supprimer un avec site delete.");
      if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE player_id = {$player['id']}")) >= $player['servers'])
        exit("Vous ne pouvez pas créer plus de sites que vous n'avez de serveurs.");
      if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE adress = '$adress'")) != 0)
        exit("Cette adresse est déjà utilisée.");
      if(!preg_match('/^[a-z0-9-]{5,25}$/', $adress))
        exit("L'adresse de votre site doit se composer uniquement de minuscules, 5-25 caractères. Vous pouvez utiliset un tiret. Exemple : mon-super-site.");
      mysql_query("INSERT INTO sites VALUES('', '{$player['id']}', '$adress', 'Le contenu de votre site ici', '0', '0')");
      
      add_to_logs("Création d'un site : $adress");
      add_to_pub_logs("{$account['pseudo']} a créé un site.");
      
      echo 1;
    }
    
    
    if(isset($_POST['edit_site']) && isset($_POST['adress'])) {
      $adress = mysql_real_escape_string($_POST['adress']);
      
      if(is_banned($account['pseudo'])) exit("0");
      if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE adress = '$adress'")) != 1) exit('0');
      $site = mysql_fetch_array(mysql_query("SELECT * FROM sites WHERE adress = '$adress'"));
      if($site['player_id'] != $player['id']) exit('0');
      
      echo json_encode(array("site_content" => stripslashes($site['content'])));
    }
    
    
    if(isset($_POST['host_site']) && isset($_POST['adress']) && isset($_POST['server'])) {
      $adress = mysql_real_escape_string($_POST['adress']);
      $server_ip = mysql_real_escape_string($_POST['server']);
      if(is_banned($account['pseudo'])) exit("Vous êtes banni et ne pouvez effectuer d'action sur les sites.");
      $site = mysql_fetch_array(mysql_query("SELECT * FROM sites WHERE adress = '$adress'"));
      if($site['player_id'] != $player['id']) exit;

      if($server_ip == '0') {
        mysql_query("UPDATE sites SET hosted_on = '0' WHERE adress = '$adress'");
        echo 2;
        exit;
      }
      
      $server = mysql_fetch_array(mysql_query("SELECT * FROM servers WHERE ip = '$server_ip'"));
      if(preg_match('/^localhost(.+)$/', $server_ip)) exit("Impossible d'héberger un site sur localhost.");
      if($server['player_id'] != $player['id']) exit("Ce serveur n'est pas à vous.");
      if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE adress = '$adress'")) != 1) exit("Sélection invalide.");
      if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE hosted_on = '$server_ip'")) != 0) exit("Ce serveur héberge déjà un site.");
      
      mysql_query("UPDATE sites SET hosted_on = '$server_ip' WHERE adress = '$adress'");
      echo 1;
    }
    
    
    if(isset($_POST['delete_site']) && isset($_POST['adress'])) {
      $adress = mysql_real_escape_string($_POST['adress']);
      
      if(is_banned($account['pseudo'])) exit("Vous êtes banni et ne pouvez effectuer d'action sur les sites.");
      if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE adress = '$adress'")) != 1) exit("Sélection invalide.");
      $site = mysql_fetch_array(mysql_query("SELECT * FROM sites WHERE adress = '$adress'"));
      if($site['player_id'] != $player['id']) exit;
      mysql_query("DELETE FROM sites WHERE adress = '$adress'");
      echo 1;
    }
    if(isset($_POST['preview']) && isset($_POST['bbcode'])) {
      echo stripslashes(link_site(bbcode(nl2br(htmlspecialchars($_POST['bbcode'])))));
    }
    if(isset($_POST['save']) && isset($_POST['adress']) && isset($_POST['bbcode'])) {
      $adress = mysql_real_escape_string($_POST['adress']);
      $content = nl2br(mysql_real_escape_string($_POST['bbcode']));
      
      if(is_banned($account['pseudo'])) exit("Vous êtes banni et ne pouvez effectuer d'action sur les sites.");
      if(mysql_num_rows(mysql_query("SELECT * FROM sites WHERE adress = '$adress'")) != 1) exit("Sélection invalide.");
      $site = mysql_fetch_array(mysql_query("SELECT * FROM sites WHERE adress = '$adress'"));
      if($site['player_id'] != $player['id']) exit;
      
      mysql_query("UPDATE sites SET content = '$content' WHERE adress = '$adress'");
      echo 1;
    }
  }
  elseif(isset($_GET) && isset($_SESSION['id'])) {
?>
<style>
  #app_browser #navigation {
    display:block;
    height:30px;
    border:1px solid rgba(0, 0, 0, 0.4);
    background:rgba(0, 0, 0, 0.3);
    padding:7px;
    margin-bottom:10px;
  }
  
  #app_browser #navigation .icon {
    height:25px;
    width:25px;
    float:right;
    cursor:pointer;
  }
  
  #app_browser #url {
    width:65%;
  }
  
  #app_browser #view {
    width:100%;
  }
  
  #app_browser #site_view {
    background:rgba(255, 255, 255, 0.8);
    border:1px solid white;
    display:block;
    height:100%;
    padding:10px;
    color:black;
    font-size:13px;
    font-family:"Calibri";
  }
  
  #app_browser #site_view h1, #app_browser #site_view h2, #app_browser #site_view h3, #app_browser #site_view h4, #app_browser #site_view h5, #app_browser #site_view h6 {
    color:black;
  }
  
  #app_browser #preview {
    display:block;
    padding:10px;
    background:rgba(255, 255, 255, 0.8);
    border:1px solid white;
    color:black;
    font-size:13px;
    font-family:"Calibri";
  }
  
  #app_browser #preview h1, #app_browser #preview h2, #app_browser #preview h3, #app_browser #preview h4, #app_browser #preview h5, #app_browser #preview h6 {
    color:black;
  }
  
  #app_browser #site_editor_textarea {
    width:100%;
    height:200px;
    font-family:'courier new';
    background:url("design/bbcode_editor/background.png");
    color:white;
    border:1px solid black;
    text-shadow:1px 1px 0 black;
    font-weight:bold;
    font-size: 12px;
  }
  
  #app_browser #editor_buttons {
    text-align:right;
    margin-bottom:10px;
    display:block;
    cursor:pointer;
  }
  
  #app_browser #editor_buttons .main_editor_button {
    
  }
</style>
<script>
  function browse_url(url) {
    $('#app_browser #url').val(url);
    $('#app_browser #view').css('opacity', '0.5');
    $.ajax({ 
      cache: false,
      type: "POST",
      url: "game/apps/browser.app.php",
      data: {ajax: true, url: url},
      success: function (data) {
        $('#app_browser #view').css('opacity', '1');
        $('#app_browser #view').html(data);
        $('#app_browser .app_table')
          .html(data)
          .find('tr:odd')
          .css('background', 'rgba(255, 255, 255, 0.2)');
      }
    });
  }
  
  $('#app_browser #create_site')
    .die('submit')
    .live('submit', function(event) {
      event.preventDefault();
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/browser.app.php",
        data: {ajax: true, create_site: true, adress: $(this).find('input[name=adress]').val()},
        success: function (data) { 
          if(data == 1) {
            dialog("Navigateur", "Vous avez bien créé ce site ! Vous devez maintenant l'éditer, puis le publier en l'hébergant sur un serveur.");
            browse_url("about:sitemanager");
          }
          else dialog("Navigateur", data);
        }
      });
    });
    
  $('#app_browser #host_site')
    .die('submit')
    .live('submit', function(event) {
      event.preventDefault();
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/browser.app.php",
        data: {ajax: true, host_site: true, adress: $(this).find('select[name=adress]').val(), server: $(this).find('select[name=server]').val()},
        success: function (data) { 
          if(data == 1) {
            dialog("Navigateur", "Ce site est maintenant hébergé et disponible pour les autres joueurs !");
            browse_url("about:sitemanager");
          }
          else if(data == 2) {
            dialog("Navigateur", "Ce site n'est maintenant plus hébergé.");
            browse_url("about:sitemanager");
          } 
          else dialog("Navigateur", data);
        }
      });
    }); 
    
  $('#app_browser #delete_site')
    .die('submit')
    .live('submit', function(event) {
      event.preventDefault();
      if(confirm("Confirmer cette action ?")) {
        if(confirm("Supprimer définitivement ce site et son contenu ?")) {
          $.ajax({ 
            cache: false,
            type: "POST",
            url: "game/apps/browser.app.php",
            data: {ajax: true, delete_site: true, adress: $(this).find('select[name=adress]').val()},
            success: function (data) { 
              if(data == 1) {
                dialog("Navigateur", "Ce site a bien été supprimé.");
                browse_url("about:sitemanager");
              }
              else dialog("Navigateur", data);
            }
          });
        }
      }
    });
    
  $('#app_browser #edit_site')
    .die('submit')
    .live('submit', function(event) {
      event.preventDefault();
      var adress = $(this).find('select[name=adress]').val();
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/browser.app.php",
        dataType: 'json',
        data: {ajax: true, edit_site: true, adress: adress},
        success: function (data) { 
          if(data == 0) {
            dialog("Navigateur", "Impossible d'éditer ce site...");
            browse_url("about:sitemanager");
          }
          else {
            $('#app_browser #view')
              .html(
               '<div id="editor_buttons">'
               +'<img title="Titre 1" class="icon" src="design/bbcode_editor/text_heading_1.png" onclick="balise(\'[h1]\', \'[/h1]\', \'site_editor_textarea\')" />'
               +'<img title="Titre 2" class="icon" src="design/bbcode_editor/text_heading_2.png" onclick="balise(\'[h2]\', \'[/h2]\', \'site_editor_textarea\')" />'
               +'<img title="Titre 3" class="icon" src="design/bbcode_editor/text_heading_3.png" onclick="balise(\'[h3]\', \'[/h3]\', \'site_editor_textarea\')" />'
               +'<img title="Titre 1" class="icon" src="design/bbcode_editor/text_heading_4.png" onclick="balise(\'[h4]\', \'[/h4]\', \'site_editor_textarea\')" />'
               +'&nbsp;&nbsp;'
               +'<img title="Couleur" class="icon" src="design/bbcode_editor/palette.png" onclick="add_bal(\'color\', \'site_editor_textarea\')" />'
               +'<img title="Taille" class="icon" src="design/bbcode_editor/text_smallcaps.png" onclick="add_bal(\'size\', \'site_editor_textarea\')" />'
               +'<img title="Police" class="icon" src="design/bbcode_editor/font.png" onclick="add_bal(\'font\', \'site_editor_textarea\')" />'
               +'&nbsp;&nbsp;'
               +'<img title="Gras" class="icon" src="design/bbcode_editor/text_bold.png" onclick="balise(\'[b]\', \'[/b]\', \'site_editor_textarea\')" />'
               +'<img title="Italique" class="icon" src="design/bbcode_editor/text_italic.png" onclick="balise(\'[i]\', \'[/i]\', \'site_editor_textarea\')" />'
               +'<img title="Souligné" class="icon" src="design/bbcode_editor/text_underline.png" onclick="balise(\'[u]\', \'[/u]\', \'site_editor_textarea\')" />'
               +'&nbsp;&nbsp;'
               +'<img title="Image" class="icon" src="design/bbcode_editor/image.png" onclick="balise(\'[img]\', \'[/img]\', \'site_editor_textarea\')" />'
               +'&nbsp;&nbsp;'
               +'<img title="Texte à gauche" class="icon" src="design/bbcode_editor/text_align_left.png" onclick="balise(\'[left]\', \'[/left]\', \'site_editor_textarea\')" />'
               +'<img title="Texte centré" class="icon" src="design/bbcode_editor/text_align_center.png" onclick="balise(\'[center]\', \'[/center]\', \'site_editor_textarea\')" />'
               +'<img title="Texte à droite" class="icon" src="design/bbcode_editor/text_align_right.png" onclick="balise(\'[right]\', \'[/right]\', \'site_editor_textarea\')" />'
               +'<img title="Texte justifié" class="icon" src="design/bbcode_editor/text_align_justity.png" onclick="balise(\'[justify]\', \'[/justify]\', \'site_editor_textarea\')" />'
               +'&nbsp;&nbsp;'
               +'<img title="Aperçu" class="icon main_editor_button" src="design/bbcode_editor/eye.png" id="refresh_preview" />'
               +'<img title="Enregistrer et quitter" class="icon main_editor_button" src="design/bbcode_editor/tick.png" id="save_site" data-adress="'+adress+'" />'
               +'</div>'
               +'<textarea id="site_editor_textarea"></textarea><br />'
               +'<br />'
               +"<div id=\"preview\"><span style=\"color:red\"><h2>Créer un site sur ServerFight</h2><ul><li>Les CGU s'appliquent aussi aux sites. Il est en outre <u>formellement interdit de faire de la publicité</u> ou de diffuser des propos illégaux, obscènes, racistes, etc.</li><li>Une petite exception est faite : les liens vers les sites personnels ou vers des sites a but non lucratif sont autorisés.</li><li>Le joueur est tenu <u>entièrement responsable</u> s'il viole un droit d'auteur dans le contenu de son site et encourt des sanctions.</li><li>Les images dépassant du cardre dans l'affichage du site avec la taille de fenêtre par défaut sont à proscrire. De même, les sites trop lourds visant a provoquer des ralentissements chez le joueur sont interdits.</li></li><li>Tout site non conforme a ces règles vaudra un avrtissement, et si l'avertissement est ignoré, le site sera supprimé sans pré-avis.</li><li>Bon jeu !</li></ul></span></div>"
              );
            $('#app_browser #site_editor_textarea').val(data.site_content);
          }
        }
      });
    });
  
  $('#app_browser #refresh_preview')
    .die('click')
    .live('click', function() {
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/browser.app.php",
        data: {ajax: true, preview: true, bbcode: $('#app_browser #site_editor_textarea').val()},
        success: function (data) { 
          $('#app_browser #preview').html(data);
        }
      });
    });
    
  $('#app_browser #save_site')
    .die('click')
    .live('click', function() {
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "game/apps/browser.app.php",
        data: {ajax: true, save: true, adress: $(this).attr('data-adress'), bbcode: $('#app_browser #site_editor_textarea').val()},
        success: function (data) { 
          browse_url("about:sitemanager");
        }
      });
    });
  
  $('#app_browser #url')
    .unbind('keydown')
    .bind('keydown', function(event) {
      if(event.keyCode == 13) {
        browse_url($('.app#app_browser #url').val());
      }
    })
    .val('about:sitelist');
    
  browse_url('about:sitelist');
  
  $('#app_browser #home')
    .unbind('click')
    .bind('click', function(event) {
      browse_url('about:sitelist');
    });
  $('#app_browser #sitemanager')
    .unbind('click')
    .bind('click', function(event) {
      browse_url('about:sitemanager');
    });
  $('#app_browser #next')
    .unbind('click')
    .bind('click', function(event) {
      alert("Bientot fonctionnel");
    });
  $('#app_browser #previous')
    .unbind('click')
    .bind('click', function(event) {
      alert("Bientot fonctionnel");
    });
  $('#app_browser #refresh')
    .unbind('click')
    .bind('click', function(event) {
      browse_url($('.app#app_browser #url').val());
    });
    
  var isMozilla = (navigator.userAgent.toLowerCase().indexOf('gecko')!=-1) ? true : false;
  var regexp = new RegExp("[\r]","gi");

  function remplace(e,c,b){var a=e;var d=c.length;while(a.indexOf(c)>-1){pos=a.indexOf(c);a=(a.substring(0,pos)+b+a.substring((pos+d),a.length))}return a}
  
  function add_bal(a, c) {
  var f = document.getElementById(c);
  var e = "";
  if (a == "size") {
    e = "Taille du texte (8 à 50 pixels) ?";
    bal = prompt(e);
    if (!bal && a == "size") {
      balise_debut = "[" + a + "]"
    }
    else {
      balise_debut = "[" + a + '=' + bal + ']'
    }
    balise_fin = "[/" + a + "]"
  }
  else if (a == "color") {
    e = "Couleur du texte (white, yellow, #F00, #A501F4, etc) ?";
    bal = prompt(e);
    if (!bal && a == "color") {
      balise_debut = "[" + a + "]"
    }
    else {
      balise_debut = "[" + a + '=' + bal + ']'
    }
    balise_fin = "[/" + a + "]"
  }
  else if (a == "font") {
    e = "Police du texte (Verdana, Arial, Courier, Georgia, etc) ?";
    bal = prompt(e);
    if (!bal && a == "font") {
      balise_debut = "[" + a + "]"
    }
    else {
      balise_debut = "[" + a + '=' + bal + ']'
    }
    balise_fin = "[/" + a + "]"
  }
  balise(balise_debut, balise_fin, c);
  if (document.getElementById(a)) {
    document.getElementById(a).options[0].selected = true
  }
}

function balise(c, f, e) {
  var h = document.getElementById(e);
  var b = h.scrollTop;
  c = remplace(c, "<br />", "\n");
  if (f == "") {
    c = " " + c + " "
  }
  if (h.curseur) {
    h.curseur.text = c + h.curseur.text + f
  }
  else {
    if (h.selectionStart >= 0 && h.selectionEnd >= 0) {
      if (h.selectionStart == h.selectionEnd && empty(trim(h.value.substring(h.selectionEnd, h.value.length)))) {
        h.setSelectionRange(h.value.length, h.value.length)
      }
      var a = h.value.substring(0, h.selectionStart);
      var d = h.value.substring(h.selectionStart, h.selectionEnd);
      var g = h.value.substring(h.selectionEnd);
      h.value = a + c + d + f + g;
      h.focus();
      h.setSelectionRange(a.length + c.length, h.value.length - g.length - f.length)
    }
    else {
      h.value += c + f;
      h.focus()
    }
  }
  h.scrollTop = b
}


</script>
<div class="app" id="app_browser">
  <div id="navigation">
    <input type="text" id="url" />
    <img class="icon" src="design/icons/refresh.png" id="refresh" />
    <img class="icon" src="design/icons/arrow_right.png" id="next" />
    <img class="icon" src="design/icons/arrow_left.png" id="previous" />
    <img class="icon" src="design/icons/sitemanager.png" id="sitemanager" />
    <img class="icon" src="design/icons/home_page.png" id="home" />
  </div>  
  <div id="view"></div>
  <br />
</div>
<br />
<?php
  }
?>