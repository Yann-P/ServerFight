/**
 * Author Yann Pellegrini
 * Date 2011
 * Licence GPLv3
 */


function show_log_in() {
  unload_desktop();
  update_bar(false, false);
  $("#achievements_container").hide();
  $('#cgu, #right_bar_container, #news_alert').show();
  $('#bar').slideUp().slideDown();
  CONFIG['last_message_seen'] = 0;
  
  $('#log_in_container').show();
  
  $('#log_in')
    .die('submit')
    .live('submit', function(event) {
      event.preventDefault();
      submit_log_in($('#log_in input[name=pseudo]').val(), $('#log_in input[name=password]').val(), $('#log_in input[name=confirm]').val(), $('#log_in input[name=email]').val(), $('#log_in input[name=captcha]').val());
    });
    
  $('#log_in input[name=pseudo]').focus();
  
  recent_activity_interval = setInterval(function() {
    $.post('get_recent_activity.php', {retreive: true}, function(data) { $('#recent_activity').html(data); });
  }, 2500);
  
}

function submit_log_in(pseudo, password, confirm, email, captcha) {
  if(pseudo && password && !confirm) { // Connexion
    $.ajax({ 
      cache: false,
      type: "POST",
      url: "log_in/log_in.php",
      dataType: "json",
      data: {action: "log_in", pseudo: pseudo, password: password},
      success: function (data) { 
        if(data.notifications && data.notifications.type == "error") dialog("Impossible de continuer", data.notifications.message);
        else if(data.token) {
          CONFIG['token'] = data.token;
          $('#log_in_container').fadeOut();
          $('#log_in_container input[type=text], #log_in_container input[type=password]').val('');
          $("#achievements_container").show();
          $('#cgu, #right_bar_container, #news_alert').hide();
          $('#bar').slideUp().slideDown();
          clearInterval(recent_activity_interval);
          get_desktop();
        }
      }
    });
  }
  else if(pseudo && password && confirm && email && captcha) { // Inscription
      $.ajax({ 
        cache: false,
        type: "POST",
        url: "log_in/log_in.php",
        dataType: "json",
        data: {action: "sign_in", pseudo: pseudo, password: password, confirm: confirm, email: email, captcha: captcha},
        success: function (data) { 
          if(data.notifications && data.notifications.type == "error") dialog("Impossible de continuer", data.notifications.message);
          else if(data == 1) {
            dialog("Compte créé avec succès !", "Vous êtes maintenant connecté sur Server Fight. Utilisez vos identifiants pour votre prochaine venue.<br />Bon jeu !");
            submit_log_in(pseudo, password, "", "", "");
          }  
        }
      });
  }
  else {
    dialog("Impossible de continuer", "Il reste des champs non renseignés.");
  } 
}

function log_out() {
  $.post('log_in/log_in.php', {action: 'log_out', token: CONFIG['token']}, function() {
    show_log_in();
  });
}