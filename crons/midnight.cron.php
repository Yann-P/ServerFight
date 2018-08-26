<?php

  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
  require_once("socle.php");  // change path or copy file
  mysql_query("UPDATE servers SET time_worked = 0");
  add_to_logs("Cron // RaZ du temps de travail de tous les serveurs du jeu.");
?>