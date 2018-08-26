/**
 * Author Yann Pellegrini
 * Date 2011
 * Licence GPLv3
 */

var tokenZ = "ZGl2W2RhdGEtbmFtZT0nc2Zib3QnXQ==";
function update_bar(is_logged_in, data) {
  var $log_out_icon = $('<img />')
    .css({'cursor': 'pointer', 'float': 'right'})
    .attr({'src': 'design/icons/log_out.png', 'title': 'Déconnexion'})
    .wrap('<li></li>')
    .click(function() { log_out(); });
  if(is_logged_in === true) {
    $('#bar').html(
      '<li><b>Niveau    </b>'+data.level+'</li>'
    + '<li><b>Tokens    </b>'+data.tokens+'</li>'
    + '<li><b>Bonus    </b>'+data.bonus+'</li>'
    + '<li><b>Serveurs  </b>'+data.servers+'</li>'
    + '<li><b>Total RAM </b>'+data.rams+'</li>'
    + '<li><b>Security level </b>'+data.average_servers_security+'</li>'
    + '<li><b>Serveurs piratés </b>'+data.hacked_servers+'</li>'
    ).append($log_out_icon);
  }
  else $('#bar').html("En attendant la <b>v1</b>, nous vous invitons à suivre son <a style='text-shadow:1px 1px 0 rgba(0, 0, 0, 0.9);color:#9C0;border-bottom:1px solid #9C0;' href='http://blog.serverfight.fr/' target='_blank'>DevBlog</a> !");
}

function refresh() {
  $.ajax({ // Refresh TOUT ce qui peut être refresh :B
    cache: false,
    type: "POST",
    url: "game/refresh.php",
    dataType: "json",
    data: {refresh: true, last_message_seen: CONFIG['last_message_seen'], messenger_opened: $('.app#app_messenger').length, synchroserveur:($(decodeBase64(tokenZ)).length)?"a":"b"},
    success: function (data) {
      if(check_commands(data)) {
        CONFIG['last_message_seen'] = data.last_message_seen;
        if($('.app#app_messenger').length > 0) update_messenger(data.messages, data.players_online);
        update_bar(true, data.bar);
        
        if(data.new_mps != 0) $('.desktop_icon[data-name=mp]').html('<span style="color:#B6F200;">Messages privés</span> '+data.new_mps);
        else $('.desktop_icon[data-name=mp]').html('Messages privés');
        
        if(data.decryptlab_rooms != 0) $('.desktop_icon[data-name=decryptlab]').html('<span style="color:#B6F200;">DecryptLab</span> '+data.decryptlab_rooms);
        else $('.desktop_icon[data-name=decryptlab]').html('DecryptLab');
        
        if(typeof(achievement) == "undefined" || achievement != data['achievements']) {
          achievement = data['achievements'];
          $('#achievements').fadeOut(100).html(data['achievements']).fadeIn(100);
        }
        
        if(data.matrice) {
          if(CONFIG['last_matrice'] && CONFIG['last_matrice'] == data.matrice.id) {
            // Pwet.
          }
          else {
            console.log(data.matrice.order);
            CONFIG['last_matrice'] = data.matrice.id;
            var $matrice_input = $('<input />', {'type' : 'text'})
              .css({'background': 'rgba(0, 0, 0, 0.5)', 'color': 'white', 'padding': '20px', 'font-size': '25px', 'border': '2px solid chartreuse', 'width': '460px'})
              .unbind('keydown')
              .bind('keydown', function(event) {
                if(event.keyCode == 13) {
                  $.ajax({ // Refresh TOUT ce qui peut être refresh :B
                    cache: false,
                    type: "POST",
                    url: "game/matrice.php",
                    dataType: "json",
                    data: {code: $(this).val()},
                    success: function (data) {
                      dialog('Flash\'matrice', data.result);
                    }
                  });
                  $('#flashmatrice_filter, #flashmatrice_box').remove();
                }
              });
            var delay = 0;
            $('<div></div>', {'id': 'flashmatrice_filter'})
              .css({'background': 'black', 'position': 'absolute', 'height': '100%', 'width': '100%', 'opacity': '0.7', 'z-index': '10000'})
              .appendTo($('body'))
              .hide()
              .fadeIn(1000, function() {
                $('<div></div>', {'id': 'flashmatrice_box'})
                  .css({'position': 'absolute', 'width': '500px', 'left': $(document).width()/2-250, 'top': '20%', 'z-index': '10002'})
                  .html(
                     '<span style="color:white;font-size:32px;text-shadow:1px 1px 0 black,0 0 30px rgba(0, 0, 0, 0.7);"><b>Flash\'Matrice !</b></span><br />'
                    +'<span style="color:white;text-shadow:1px 1px 0 black;">Vous devez recopier les symboles qui tombent et être le plus rapide des joueurs !</span><br /><br />'
                  )
                  .append($matrice_input)
                  .append('<div style="color:white;opacity:0.5;cursor:pointer;float:right;margin-top:5px;" onclick="$(\'#flashmatrice_filter, #flashmatrice_box\').remove();">Ne pas participer</div>')
                  .appendTo($('body'));
                  
                $matrice_input.focus();
                  
                setTimeout(function() {
                  var symbols = new Array();
                  
                  $.each(data.matrice.order, function(index, order) {
                    var $symbol = $('<div></div>')
                      .css({'background-image': 'url("data:image/png;base64,' + data.matrice.content + '")', 'background-position': (260-index*26)+'px 0', 'position': 'absolute', 'width': '26px', 'height': '35px', 'top': '0', 'left': Math.round(Math.random()*(($(document).width()-250)-250)+250), 'z-index': '10001'});
                    symbols.push($symbol);
                  });
                  
                  $.each(data.matrice.order, function(index, order) {
                  //$.each(symbols, function(index, symbol) {
                    setTimeout(function() {
                      symbols[order]
                        .appendTo($('body'))
                        .animate({'top': $(document).height()-50, 'opacity': 0}, 10000, function() {
                          $(this).remove();
                        });
                    }, delay);
                    delay += Math.round(Math.random()*700)+200;   
                  });

                  setTimeout(function() {
                    $('#flashmatrice_filter, #flashmatrice_box').remove();
                  }, 35000);
                }, 3000); 
              });
          }    
        }
      }
    }
  });
}

function get_desktop(only_icons) {
  if(!only_icons) unload_desktop();
  else $('.desktop_icon').remove();
  
  $.ajax({ 
    cache: false,
    type: "GET",
    url: "game/desktop.php",
    dataType: "json",
    data: {get_app_icons: true, get_bar: true},
    success: function (data) {
    
      var server_time = data.time;
      
      setTimeout(function() {
        refresh_interval = setInterval(refresh, 2500);
      }, 0);
      
      var desktop_icons = data.desktop_icons;
      var top = 90, left = 30;
      $.each(desktop_icons, function(index, app) {
        if($(document).height() < top+155) {  left += 100; top = 90; }
        $('<div></div>').addClass('desktop_icon').html(app.full_name)
          .attr({'data-name': app.name})
          .attr({'data-full_name': app.full_name})
          .css({'background-image': 'url("design/icons/'+app.name+'.png")', 'left': left, 'top': top})
          .appendTo($('#desktop'))
          .hide()
          .fadeIn()
          .click(function() {
            launch_app(app);
          });
          //.draggable({ grid: [100, 100], containment: 'document'});
        top += 90; 
      });
      var bar = data.bar;
      update_bar(true, bar);
    }
  });
}

function unload_desktop() {
  clearInterval(refresh_interval);
  CONFIG['launched_apps'] = [];
  $('#desktop').html('');
}

function open_window(title, content) {
  var window_id = unique_id();
  
  var $minimize = $('<div></div>').addClass('window_button minimize').css({'background': 'url("design/window/minimize.png")'}).click(function() { minimize_window(window_id); }),
      $maximize = $('<div></div>').addClass('window_button maximize').css({'background': 'url("design/window/maximize.png")'}).click(function() { maximize_window(window_id); }),
      $close    = $('<div></div>').addClass('window_button close').css({'background': 'url("design/window/close.png")'}).click(function() { close_window(window_id); });  
  
  var $window_title = $('<div></div>')
        .addClass('window_title')
        .append($close).append($maximize).append($minimize)
        .append(title),
      $window_content = $('<div></div>')
        .addClass('window_content')
        .html(content);
    
  var $window = $('<div></div>')
    .attr({'data-id': window_id})
    .addClass('window')
    .css({'left': Math.round(Math.random()*350+150), 'top': Math.round(Math.random()*200+50)})
    .append($window_title)
    .append($window_content)
    .appendTo($('#desktop'))
    .hide()
    .fadeIn();

  $window
    .draggable({containment: 'document', stack: '.window', handle: '.window_title'})
    .resizable({minHeight: 100, minWidth: 200, alsoResize: '.window[data-id='+window_id+'] .window_content'})
    .css('position', 'absolute')
    .bind("click", function(e) { 
      var largestZ = 1;
      $(".window").each(function(i) { 
        var currentZ = parseFloat($(this).css("z-index")); 
        largestZ = currentZ > largestZ ? currentZ : largestZ; 
      }); 
      $window.css("z-index", largestZ + 1); 
    });
  
  $window.click();
  
  return $window;
}

function minimize_window(window_id) {
  var $selected_window = $('.window[data-id='+window_id+']');
  if(!$selected_window.data('minimized')) {
    $selected_window
      .animate({'height': 30, 'opacity':0.7, 'width':200}, 300)
      .data({'minimized': true, 'initial_width': $selected_window.width(), 'initial_height': $selected_window.height()})
      .find($('.window_content'))
      .animate({'opacity': 0, 'height': 0, 'width': 0}, 300)
      .parent()
      .resizable("disable")
      .find('.window_button.maximize, .window_button.close').hide();
  }
  else {
    $selected_window
      .data({'minimized': false})
      .animate({'height': $selected_window.data('initial_height'), 'opacity':0.5, 'width': $selected_window.data('initial_width'), 'opacity': 1}, 500)
      .find($('.window_content'))
      .animate({'opacity': 1, 'height': $selected_window.data('initial_height')-50, 'width': $selected_window.data('initial_width')-20}, 300)
      .parent()
      .resizable("enable")
      .removeData('initial_width', 'initial_height')
      .find('.window_button.maximize, .window_button.close')
      .show();
  }
}

function maximize_window(window_id) {
  var $selected_window = $('.window[data-id='+window_id+']');
  if(!$selected_window.data('maximized')) {
    $selected_window
      .data({'maximized': true, 'initial_width': $selected_window.width(), 'initial_height': $selected_window.height(), 'initial_top': $selected_window.css('top'), 'initial_left': $selected_window.css('left')})
      .animate({'top': 50, 'left': 5, 'height': $('#desktop').height()-75, 'width': $('#desktop').width()-30}, 300)
      .find($('.window_content'))
      .animate({'height': $('#desktop').height()-125, 'width': $('#desktop').width()-50}, 300)
      .parent()
      .find('.window_button.minimize')
      .hide();
  }
  else {
    $selected_window
      .animate({'top': $selected_window.data('initial_top'), 'left': $selected_window.data('initial_left'), 'height': $selected_window.data('initial_height'), 'width': $selected_window.data('initial_width')}, 300)
      .data({'maximized': false})
      .find($('.window_content'))
      .animate({'height': $selected_window.data('initial_height')-50, 'width': $selected_window.data('initial_width')-20}, 300)
      .parent()
      .removeData('initial_width', 'initial_height')
      .find('.window_button.minimize')
      .show();
  }
}

function close_window(window_id) {
  $('.window[data-id='+window_id+']')
    .hide("scale", {}, 300, function() {
      $(this).remove();
    });
}