<?php
/**
 * Plugin Name: Stock Tools
 * Description: A WordPress plugin with a set of tools for displaying stock information.
 * Version: 1.1
 * Author: Patrick Daly
 * Author URI: http://developdaly.com
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

add_shortcode( 'stock-tools', 'stock_tools_shortcode' );

/*
 * Executes the [stock-tools] shortcode.
 */
function stock_tools_shortcode( $atts ) {

	extract( shortcode_atts( array(
       	'exchange'		=> '',
		'symbol'		=> '',
		'image_height'	=> '180',
		'image_width'	=> '300'
	), $atts ) );

	// Obtain quote info	
	$quote = url_get_contents( 'http://finance.google.com/finance/info?client=ig&q='. "{$exchange}" .':'. "{$symbol}" );
		
	// Remove CR's from output - make it one line
	$json = str_replace( "\n", "", $quote );
	
	// Remove //, [ and ] to build qualified string
	$data = substr( $json, 4, strlen( $json ) -5) ;
	
	//decode JSON data
	$json_output = json_decode( utf8_decode( $data ) );

	// Get the last price
	$last = $json_output->l;
	
	// Get amount changed
	$change_val = $json_output->c;
	
	// Get percentage changed
	$change_percent = $json_output->cp;

	// Get last trade
	$last_trade = $json_output->lt;
	
	// Determine positive/negative change
	if( strstr( $json_output->c, '+' ) )
		$change = 'positive';
	elseif( strstr( $json_output->c, '-' ) )
		$change = 'negative';
	else
		$change = 'no-change';
	
	$output = '<div class="stock-tools '. $change .'">';	
	$output .= '<div class="stock-tools-symbol">'. "{$symbol}" .'</div>';
	$output .= '<div class="stock-tools-last">'. $last .'</div>';
	$output .= '<div class="stock-tools-change">'. $change_val .' ('. $change_percent .'%)</div>';
	$output .= '<div class="stock-tools-image"><img src="http://chart.finance.yahoo.com/t?s='. "{$symbol}" .'&lang=en-US&region=US&width='. "{$image_width}" .'&height='. "{$image_height}" .'" height="'. "{$image_height}" .'" width="'. "{$image_width}" .'"></div>';
	$output .= '</div><!-- .stock-tools -->';
	
	return $output;

}

/*
 * Use cURL to get the file contents.
 * (Using instead of file_get_contents() because some hosts disallow it)
 */
function url_get_contents ($Url) {
	if (!function_exists('curl_init')){ 
		die('CURL is not installed!');
	}
	
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $Url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$output = curl_exec( $ch );
	curl_close( $ch );
	return $output;
}
