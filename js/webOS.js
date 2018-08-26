/**
 * Author Yann Pellegrini
 * Date 2011
 * Licence GPLv3
 */


CONFIG['last_message_seen'] = 0;
CONFIG['ajax_in_progress'] = 0;
CONFIG['CSRF_proof_ajax_in_progress'] = 0;

var recent_activity_interval,
    refresh_interval;

$(document).ready(function(event) {
  show_log_in();
  $(document)
    .ajaxStart(function() {
      CONFIG['ajax_in_progress'] += 1;
      if(CONFIG['ajax_in_progress'] >= 3) {
        dialog("Erreur réseau", "Votre navigateur a beaucoup de requêtes sans réponses. Vous devriez actualiser la page.<br />Causes probables : perte de la connexion internet, latence dûe au serveur.");
      }
    })
    .ajaxStop(function() {
      CONFIG['ajax_in_progress'] -= 1;
    });
  
  $("#right_bar .new:odd").css('background', 'rgba(255, 255, 255, 0.05)');
  $("#right_bar .new:even").css('background', 'rgba(0, 0, 0, 0.05)');
  
  if($.cookie("last_new_seen") == 'null') $.cookie("last_new_seen", 0, { expires: 1000 });
  
  console.log('Cookie : '+$.cookie("last_new_seen"));
  console.log('Last : '+$("#right_bar_container").find('.new:first').attr('data-id'));
  
  if(parseInt($("#right_bar_container").find('.new:first').attr('data-id')) >  $.cookie("last_new_seen")) {
    $('<div></div>', {'id': 'news_alert'})
      .css({
        'position': 'fixed',
        'right': '60px',
        'top': '20px',
        'background': 'url("design/news_notification.png")',
        'color': '#3795C1',
        'font-size': '15px',
        'width': '17px',
        'height': '18px',
        'padding': '2px 5px 5px 5px',
        'font-weight': 'bold',
        'text-shadow': '1px 1px 0 white'
      })
      .html(Math.abs(parseInt($("#right_bar_container").find('.new:first').attr('data-id')) -  $.cookie("last_new_seen")))
      .appendTo($('body'));
  }
  
  $("#right_bar_container")
    .css('height', $(document).height())
    .data('visible', false)
    .mouseover(function() {
      if(!$(this).data('visible')) {
        $(this).stop().animate({'right': '-150px'}, 200);
        $('#news_alert').stop().animate({'right': '80px'}, 200);
      }
    })
    .mouseout(function() {
      if(!$(this).data('visible')) {
        $(this).stop().animate({'right': '-170px'}, 200);
        $('#news_alert').stop().animate({'right': '60px'}, 200);
      }
    })
    .click(function() {
      if(!$(this).data('visible')) {
        $('#news_alert').remove();
        $.cookie("last_new_seen", $(this).find('.new:first').attr('data-id'), { expires: 1000 });
        $(this)
          .data('visible', true)
          .stop()
          .animate({'right': '-20px'});
      }
      else {
        $(this)
          .data('visible', false)
          .stop()
          .animate({'right': '-170px'});
      }
    });
})

function check_commands(data) {
  if(data) {
    if(data.action) {
      // Erreur session inexistante (automatique).
      if (data.action.command == "%01") { dialog("Erreur", "Vous n'êtes plus connecté... cela peut venir du fait que votre session a expiré."); show_log_in(); return false; }
      // Déconnexion forcée.
      else if (data.action.command == "%02") { log_out(); }
      // Message
      else if (data.action.command == "%03") { dialog("Message", data.action.message); }
      // Exécuter du JS
      else if (data.action.command == "%04") { eval(data.action.message); }
      // Déconnexion forcée + message.
      else if (data.action.command == "%05") { log_out(); dialog("Déconnecté", data.action.message);}
      
      return true;  
    }
    else {
      return true; 
    } 
  }  
  else dialog("Erreur", "Impossible de traiter la réponse puisqu'elle est vide.");
  return true;
}

function dialog(title, message) {
  var dialog_id = unique_id();
  $('<div></div>')
    .addClass('dialog')
    .html(
       '<table><tr valign="top"><td><img src="design/alert.png" alt="Alerte" style="margin-right:10px;" /></td>'
    +'<td><b>'+title+'</b><br /><br />'+message+'</td></tr>'
    )
    .attr('data-id', dialog_id)
    .append('<br /><br /><center><input type="button" value="Fermer" onclick="closeDialog('+dialog_id+');"></center>')
    .css('left', ($(document).width()/2)-185)
    .appendTo('body')
    .hide()
    .fadeIn(700);
  $('<div></div>').addClass('filter').attr('data-id', dialog_id).appendTo($('body')).fadeIn(700);  
}

function closeDialog(dialog_id) { 
  $('.dialog[data-id='+dialog_id+'], .filter[data-id='+dialog_id+']').remove();
}

function unique_id() { 
	return new Date().getTime();
}

function csrf() {
  if(confirm("Erreur CSRF.\nCauses possibles : jeu ouvert dans 2 onglets, sur 2 ordinateurs différents, bug (cela arrive), etc.\nPour des raisons de sécurité, vous devez actualiser la page. Voulez-vous le faire maintenant ?")) window.location.reload();
}

function bsod() {
  $('#body').html('');
  $('<div></div>', {'id': 'bsod'}).css({'position': 'fixed', 'top': 0, 'left': 0, 'width': '100%', 'height': '100%', 'padding': '50px', 'z-index': '100000', 'background': 'blue', 'color': 'white', 'font-family':'system, courier, consolas, arial', 'font-size': '20px'}).html("<b>*** STOP</b><br /><br />Un problème a été détecté et ServerFight a bloqué le WebOS pour des raisons de sécurité.<br />Nous vous demandons de bien vouloir actualiser la page Web.<br /><br /><br /><b>ERROR_INVALID_CSRF_TOKEN</b> at <em>messenger.app.php</em><div style='position:fixed;bottom:100px;width:100%;'><div style='text-align:right;display:block;font-size:15px;padding-right:100px'><hr /><br />Vous l'avez reconnu ? Le célèbre écran-bleu-de-la-mort !<br />En réalité, un petit problème est effectivement survenu et nous vous prions d'actualiser la page.<br />Mais cette reproduction du célèbre 'BSOD' n'apporte-t-elle pas un peu de réalisme ? ^____^</div></div>").appendTo($('body'));
}