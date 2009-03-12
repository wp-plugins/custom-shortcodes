<?php
/* 
Plugin Name: Customfields Shortcode
Version: 0.5
Description: Manage custom fields using the insert shortcodes [custom name="field-name" value="field-value"] or HTML conditional comments &lt;!--custom name="field-name" value="field-value"--&gt; in text of post. It's a hook for desktop blog clients, which don't support customfields natively.
Plugin URI: http://iskariot.ru/wordpress/remix/#custom-short
Author: Sergey M.
Author URI: http://iskariot.ru/
*/ 

//Пытаемся поправить шоткоды в виде комментариев
add_filter('content_save_pre', 'cfsc_right_shortcodes');
function cfsc_right_shortcodes($content) {
	$content=preg_replace('~(\<|&lt;)!--custom\s(.*?)--(>|&gt;)~i','<!--custom \\2-->',$content);
	return $content;
}

//Находим все псевдотеги, вставляем произвольные поля, если надо
add_action('save_post', 'cfsc_add_customfield');
function cfsc_add_customfield($post_ID) {
	//подбираем потс
	$post = get_post($post_ID);

	//подбираем все псевдотеги
	preg_match_all('~\[custom\s([^\]]*?)\]~i',$post->post_content,$matches);
	preg_match_all('~(\<|&lt;)!--custom(.*?)--(>|&gt;)~i',$post->post_content,$matches2);
	//соединяем обав варианта
	$matches[1]=array_merge($matches[1],$matches2[2]);
	
	$n = count( $matches[1] );
	for($i=0;$i<$n;$i++){
		//вытаскиваем из них атрибуты
		preg_match_all('~name\s*=\s*"([^"]*?)"~',$matches[1][$i],$reg);
		$name=$reg[1][0];
		preg_match_all('~value\s*=\s*"([^"]*?)"~',$matches[1][$i],$reg);
		$value=$reg[1][0];
		
		//если есть такое имя
		if(!empty($name)) {
			if(empty($value)) {
				//удаляем из БД если значение пустое
				delete_post_meta( $post_ID, $name);
				}
				else{
				//вставляем в БД если значение не пустое
				update_post_meta( $post_ID, $name, $value );
				}
			//кеш обновит он сам
			}
		}//for
}

//Убираем все вхождения наших шоткодов
add_shortcode('custom', 'cfsc_remove_shortcode');
function cfsc_remove_shortcode($atts) {
	extract(shortcode_atts(array(
	'name' => '',
	'value' => '',
	), $atts));
	//на самом деле, все это - только для того, чтобы не отображать псевдотег
	return "";
}

//Убираем все вхождения наших шоткодов в виду комментариев - на всякий случай
add_filter('the_content', 'cfsc_remove_shortcode2',6);
function cfsc_remove_shortcode2($content) {
	$content=preg_replace('~(\<|&lt;)!--custom\s(.*?)--(>|&gt;)~i','',$content);
	return $content;
}


?>