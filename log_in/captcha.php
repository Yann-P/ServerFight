<!--
 Author Yann Pellegrini
 Date 2011
 Licence GPLv3 
-->

<?php
session_start();

function generernombre($length)
{
    $chars = "123456789";
    $key = '';
    for($i = 1; $i <= $length; $i++)
    {
      $key .= $chars[rand(0, strlen($chars)-1)];
    }
    return $key;
}

function genererimage($mot)
{
    $img = imagecreate (60,15) or die ("Problème de création");
    $background_color = imagecolorallocate ($img, 255, 255, 255);
    $ecriture_color = imagecolorallocate($img, 0, 0, 0);
    imagestring ($img, 10, 4, 0, $mot , $ecriture_color);
    imagepng($img);
}


function captcha()
{
    $mot = generernombre(6);
    $_SESSION['captcha'] = $mot;
    genererimage($mot);

}

header("Content-type: image/png");
captcha();
?>