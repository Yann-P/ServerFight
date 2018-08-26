/**
 * Author Yann Pellegrini
 * Date 2011
 * Licence GPLv3
 */

CONFIG['launched_apps'] = [];
CONFIG['browser_history'] = new Array();
CONFIG['browser_history_index'] = 0;
CONFIG['terminal_history'] = new Array();
CONFIG['terminal_history_index'] = 0;

function launch_app(app) {
  if($.inArray(app.name, CONFIG['launched_apps']) == -1) {
    CONFIG['launched_apps'].push(app.name);
    
    var $window = open_window(app.full_name, false),
        window_id = $window.attr('data-id'),
        $window_content = $('.window[data-id='+window_id+'] .window_content'),
        $loader = $('<img />').attr('src', 'design/icons/'+app.name+'.png').addClass('icon');
        
    $window.find('.window_title .close').click(function(event) { 
      
      var updated_launched_apps_array = [];
      $.each(CONFIG['launched_apps'], function(index, launched_app) {
        var app_name = $window_content.find('.app').attr('id');
        if('app_'+launched_app != app_name) updated_launched_apps_array.push(launched_app);
      });
      CONFIG['launched_apps'] = updated_launched_apps_array;
    });    

    if(app.name == "terminal") $window_content.css({'background': 'black'});
    
    $window_content.html($('<div class="window_loading"></div>').append($loader));
    $loader.css({'-webkit-animation': 'spin 5s infinite linear', '-moz-animation': 'spin 5s infinite linear', '-ms-animation': 'spin 5s infinite linear'});
    
    $.ajax({ 
      cache: false,
      type: "POST",
      url: "game/apps.php",
      data: {launch: app.name, token: CONFIG['token']},
      success: function (data) {
        if(data == "0") {
          csrf();
          return;
        }
        setTimeout(function() {
          $window_content.html(data);
        }, 500);
      }
    });
  }
  else {
    $('.window:has(.app#app_'+app.name+')').click().find('.window_content .app').effect('bounce', {'times': 3, 'distance': 20}, 250);
  }  
}

function reload_app(app_name) {
  var $window = $('.window:has(.app#app_'+app_name+')'),
      window_id = $window.attr('data-id'),
      $window_content = $('.window[data-id='+window_id+'] .window_content'),
      $loader = $('<img />').attr('src', 'design/icons/'+app_name+'.png').addClass('icon window_loading');
      $filter = $('<div></div>').css({"position": "absolute", "height": "100%", "width": "100%", "top": 0, "left": 0, "background": "rgba(255, 255, 255, 0.5)"}).addClass('window_loading_filter');
  
  if(app_name == "messenger") CONFIG['last_message_seen']-=20;
  
  $window_content.append($filter).append($loader);
  $loader.css({'-webkit-animation': 'spin 5s infinite linear', '-moz-animation': 'spin 5s infinite linear', '-ms-animation': 'spin 5s infinite linear'});
  
  setTimeout(function() {
    $.ajax({ 
      cache: false,
      type: "POST",
      url: "game/apps.php",
      data: {launch: app_name, token: CONFIG['token']},
      success: function (data) {
        if(data == "0") {
          csrf();
          return;
        }
        setTimeout(function() {
          $window_content.find($('.window_loading')).remove();
          $window_content.find($('.window_loading_filter')).remove();
          $window_content.html(data);
        }, 500);
      }
    });
  }, 150);
}

function show_profil(pseudo) {
  if($('#app_profil_'+pseudo).length != 0) {
    $('.window:has(#app_profil_'+pseudo+')').click();
    return false;
  }
  $.ajax({ 
    cache: false,
    type: "POST",
    url: "game/profil.php",
    data: {pseudo: pseudo},
    success: function (data) {
      open_window("Profil de "+pseudo, data);
    }
  });
}

function go_site(adress) {
  if($('#app_browser').length == 0) {
    if($('.desktop_icon[data-name=browser]').length != 1) {
      dialog("Erreur", "Pour vous rendre sur un site vous devez installer le navigateur sur votre WebOS.");
    }
    else {
      $('.desktop_icon[data-name=browser]').click();
      setTimeout(function() {
        browse_url(adress);
        $('.window:has(#app_browser)').click();
      }, 3000);
    }
  }
  else {
    setTimeout(function() {
      browse_url(adress);
      $('.window:has(#app_browser)').click();
    }, 100);
  }
}