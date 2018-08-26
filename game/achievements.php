<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */

  function hasApp($player_id, $id) {
    return mysql_num_rows(mysql_query("SELECT * FROM applications_by_players WHERE player_id = $player_id AND application_id = $id")) == 1;
  }
  function countServs($player_id) {
    return mysql_num_rows(mysql_query("SELECT * FROM servers WHERE player_id = $player_id"));
  }
  function localhostInfos($col) {
    $localhostInfos = mysql_fetch_array(mysql_query("SELECT * FROM servers WHERE ip = 'localhost@".$_SESSION['pseudo']."'"));
    return $localhostInfos[$col];
  }
  function firstServInfos($col) {
    $firstServInfos = mysql_fetch_array(mysql_query("SELECT * FROM servers WHERE ip != 'localhost@".$_SESSION['pseudo']."'"));
    return $firstServInfos[$col];
  }
  if(0 == 1) $achievement = "";
  elseif(countServs($player['id']) > 2)
    $achievement = "Vous avez encore besoin de moi pour vous aider ? Comme je suis partout à la fois, je vous explique aussi comment jouer dans le Guide du Hacker.";
  elseif($player['level'] >= 100)
    $achievement = "La commande servrandom vous aidera à choisir des proies. Utilisez ensuite la commande infiltrate, enfin la commande crack. Reportez vous au guide du hacker et bonne chasse !";
  elseif($player['level'] >= 30 && countServs($player['id']) > 1 && strlen(firstServInfos('code')) >= 8)
    $achievement = "Vous voilà mieux protégé, mais vous n'êtes pas invulnérable ! Car bien que votre serveur soit moins facile a pirater avec un long code, plus un joueur a de RAM sur votre serveur, plus il sera facile pour lui de se l'approprier. Continuez donc de le sécuriser. Dès que vous arriverez au niveau 100 nous passerons à l'offensive !";
  elseif($player['level'] >= 30 && countServs($player['id']) > 1)
    $achievement = "Vous progressez comme une bombe atomique. A partir ce cet instant, vous êtes devenu à la fois un pirate et une proie. Commençons par le côté défensif : sécurisez votre serveur. Pour cela, utilisez la commande secure toutes les 100 RAM que vous installez sur votre serveur. Nous nous retrouverons lorsque le niveau de sécurité de votre serveur aura augmenté à 8 si vous ne vous êtes pas déjà fait pirater entre temps :p";
  elseif(countServs($player['id']) > 1)
    $achievement = "Félicitations ! Ouvrez ServerManager et contemplez votre acquisition. Puis connectez vous au serveur et équipez le au maximum en RAM. Vous pouvez toujours l'exploiter comme vous le faites pour localhost. Allez prochain objectif : atteindre le niveau 30 et commencer à pirater. Vous devriez entamer la lecture du Guide à ce sujet. Surtout, n'attendez pas le niveau 30 pour commencer à renforcer la sécurité de votre serveur avec la commande 'secure' ! Si vous avez besoin d'aide, demandez de l'aide le t'chat (download messenger).";
  elseif(localhostInfos('rams') > 16 && $player['tokens'] >= 1000)
    $achievement = "Bravo {$_SESSION['pseudo']} ! Nous allons maintenant acheter un serveur avec une vraie IP et toutes les fonctionnalités pour le piratage ! Je vous invite a taper 'buy server' dans le Terminal.";
  elseif(localhostInfos('rams') > 19)
    $achievement = "Très bien, nous allons passer aux choses sérieuses. Réunissez moi 1000 tokens et achetons un serveur. Vous en aurez l'usage pour gagner plus d'argent, et aussi... vous lancer dans le piratage ! Mouhahaha !";
  elseif(localhostInfos('time_worked') == 10)
    $achievement = "Eh bien, {$_SESSION['pseudo']}, vous progressez vite ! Tellement vite que votre serveur a épuisé son temps de travail ^___^ Dès demain, vous saurez quoi faire : connect, work, buy ram... On se retrouve demain quand vous aurez fait travailler votre serveur et atteint 20 RAM. En attendant, d'autres applications vous attendent pour vous occuper aujourd'hui. Un t'chat entre joueurs par exemple (download messenger) ;). Et tout cela, vous le retrouverez dans le Guide du Hacker, application indispensable pour tout joueur de ServerFight ! Piochez des catégories qui vous intéressent dans le sommaire et jetez rapidement un coup d'oeil à ce qui vous attend ! Piratage, décryptage...";
  elseif(localhostInfos('rams') >= 12)
    $achievement = "Il vous reste encore un peu de temps de travail pour localhost, comme vous pouvez le voir dans ServerManager sous la petite horloge. Je vous rappelle la commande ;) work [temps de travail]";
  elseif(localhostInfos('time_worked') > 0)
    $achievement = "Enfin un peu d'argent ! C'est le droit chemin vers la richesse. Achetez des RAM avec ce que vous avez gagné. Plus de RAM, encore plus d'argent ! Plus d'argent, toujours plus de RAM...";
  elseif(localhostInfos('rams') == 10)
    $achievement = "Parfait ! Je suppose que vous vous demandez a quoi va vous servir ce serveur suréquipé ;) ! Pour le début du jeu, il ne vous sera utile que pour gagner de l'argent. Pour cela, vous pouvez faire 'travailler' le serveur et toucher une somme de tokens proportionnellement à votre niveau et à la RAM que possède votre serveur. Tapez par exemple 'work 5'. le 5 correspond au temps de travail. Vous avez 10 heures de temps de travail par serveur et par jour !";
  elseif(isset($_SESSION['server']))
    $achievement = "Vous voilà connecté ! Vous devez maintenant savoir que votre serveur possède des RAM qui vont lui permettre d'être plus performant. Une RAM coûte 100 tokens, vous avez de quoi en acheter quelques unes :) ... Tapez 'buy ram 5' dans le terminal.";
  else
    $achievement = "Bienvenue à vous, nouveau joueur ! Je vous laisse découvrir l'interface du jeu. ServerManager, par exemple, est votre liste de serveurs avec leurs caractéristiques. localhost est votre premier serveur, ouvrez le terminal et essayez par exemple de taper 'connect localhost' (sans les guillemets) :)";
?>