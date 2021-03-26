<?php

/**
 * Plugin Name: BFitDiplomas
 * Plugin URI:  https://www.enutt.net/
 * Description: Generador de diplomas apra las videoclases
 * Version:     1.0
 * Author:      Enutt S.L.
 * Author URI:  https://www.enutt.net/
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bfit-diplomas
 *
 * PHP 7.3
 * WordPress 5.6
 */

//Actions ----------------------
function bfit_cursos_diploma_after_content($content) { 
  global $post, $current_user;
  if($post->post_type == "course" && is_user_logged_in()) { 
    if(Sensei_Course::is_user_enrolled( $post->ID, $current_user->ID )) {
      $button = "<a href='/wp-admin/admin-ajax.php?action=generate_diploma&lesson_id=".$post->ID."' class='button'>".__("Descargar diploma")."</a>";
      return $button.$content;
    } else return $content;
  } else return $content;
}
add_action( 'the_content', 'bfit_cursos_diploma_after_content' );


//AJAX ----------------------
function bfitGenerateDiploma() {
  global $current_user;
  if (!is_user_logged_in() ) return false; 
  wp_get_current_user();
  
  $font = plugin_dir_path( __FILE__ )."/fonts/Dosis-Regular.ttf";
  $name_font_size = 120;
  $title_font_size = 80;

  //Abrimos la imagen
  $img = imagecreatefromjpeg(plugin_dir_path( __FILE__ ).'/diploma.jpg');

  //NOMBRE ------------------------
  $txt = mb_strtoupper($current_user->user_firstname." ".$current_user->user_lastname);
  //Centramos Texto
  $image_width = imagesx($img);  
  $text_box = imagettfbbox($name_font_size, 0,$font,$txt);
  $text_width = $text_box[2]-$text_box[0];
  $x = ($image_width/2) - ($text_width/2);
  //Escribimos texto
  $black = imagecolorallocate($img, 0, 0, 0);
  imagettftext($img, $name_font_size, 0, $x, 940, $black, $font, $txt);

  //TÍTULO ------------------------
  $txt = bfitGenerateDiplomaSplitAndCenterText(get_the_title($_REQUEST['lesson_id']));

  //Centramos Texto Linea 1
  $text_box = imagettfbbox($title_font_size, 0,$font,$txt[0]);
  $text_width = $text_box[2]-$text_box[0];
  $x = ($image_width/2) - ($text_width/2);
  //Escribimos texto
  $black = imagecolorallocate($img, 0, 0, 0);
  imagettftext($img, $title_font_size, 0, $x, 1300, $black, $font, $txt[0]);

  //Centramos Texto Linea 1
  $text_box = imagettfbbox($title_font_size, 0,$font,$txt[1]);
  $text_width = $text_box[2]-$text_box[0];
  $x = ($image_width/2) - ($text_width/2);
  //Escribimos texto
  $black = imagecolorallocate($img, 0, 0, 0);
  imagettftext($img, $title_font_size, 0, $x, 1420, $black, $font, $txt[1]);

  //Mostramos imagen
  header('Content-type: image/jpeg');
  header('Content-Disposition: attachment; filename="diploma.jpg"');
  imagejpeg($img);
  imagedestroy($img);

  //Cerramos todo
  wp_die();
}

add_action('wp_ajax_generate_diploma', 'bfitGenerateDiploma');
add_action('wp_ajax_nopriv_generate_diploma', 'bfitGenerateDiploma');

function bfitGenerateDiplomaSplitAndCenterText($string) {
  $string = mb_strtoupper($string);
  $breakpoint = ceil(strlen($string) / 2);
  $words = explode (" ", $string);
  $count = 0;
  foreach ($words as $word) {
    $count = $count + strlen($word) + 1;
    if ($breakpoint < $count) $line_large[] = $word;
    else $line_short[] = $word;
  }

  return array(implode(" ", $line_short), implode(" ", $line_large));
}
