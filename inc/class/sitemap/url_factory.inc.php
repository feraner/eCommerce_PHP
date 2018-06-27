<?php
//网站地图
// include config file
require_once 'config.inc.php';

// prepares a string to be included in an URL
function _prepare_url_text($string)
{
  // remove all characters that aren't a-z, 0-9, dash, underscore or space
  $NOT_acceptable_characters_regex = '#[^-a-zA-Z0-9_ ]#';
  $string = preg_replace($NOT_acceptable_characters_regex, '', $string);

  // remove all leading and trailing spaces
  $string = trim($string); 

  // change all dashes, underscores and spaces to dashes
  $string = preg_replace('#[-_ ]+#', '-', $string); 

  // return the modified string
  return $string;
}

// builds a category link
function make_category_url($category_name, $category_id, $page = 1)
{
  // prepare the category name for inclusion in URL
  $clean_category_name = _prepare_url_text($category_name);

  // build the keyword-rich URL
  $url = SITE_DOMAIN . '/Products/' . 
         $clean_category_name . '-C' . $category_id . '/';
         
  // add page number if page is different than 1
  $url = ($page == 1) ? $url : $url . 'Page-' . $page . '/';
   
  // return the URL
  return $url;
}

// builds a product link 
function make_category_product_url($category_name, $category_id, $product_name, $product_id)
{
  // prepare the product name and category name for inclusion in URL
  $clean_category_name = _prepare_url_text($category_name);
  $clean_product_name = _prepare_url_text($product_name);

  // build the keyword-rich URL
  $url = SITE_DOMAIN . '/Products/' . 
         $clean_category_name . '-C' . $category_id . '/' . 
         $clean_product_name . '-P' . $product_id . '.html';

  // return the URL
  return $url;
}

// builds a link to a media file
function make_media_url($id, $name, $extension)
{
  // prepare the medium name for inclusion in URL
  $clean_name = _prepare_url_text ($name);

  // build the keyword-rich URL 
  $url = SITE_DOMAIN . '/' . $clean_name . '-M' . $id . '.' . $extension;

  // return the URL
  return $url;
}
?>
