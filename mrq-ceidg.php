<?php
/*
Plugin Name: Pobieranie danych z CEIDG po numerze NIP.
Description: Komunikacja z Hurtownią danych CEIDG.
Version: 0.01
Author: Sellmark Marek Buga
Author URI: http://www.maraQja.pl/
License: Commercial
*/

function mrq_ceidg_add_js(){
	
	wp_enqueue_script( 'mrq-ceidg',  plugins_url('',__FILE__) . '/mrq-ceidg.js', ['jquery'], "0.01", true );
}

add_action( 'wp_enqueue_scripts', 'mrq_ceidg_add_js' );

add_action('wp_ajax_nopriv_mrq_ceidg','mrq_ceidg_ajax_endpoint_number_one'); //for non logged in user
add_action('wp_ajax_mrq_ceidg','mrq_ceidg_ajax_endpoint_number_one'); //for nlogged in user


function mrq_ceidg_ajax_endpoint_number_one(){
    $data = $_REQUEST;
	$nonceOK = wp_verify_nonce($data['_wpnonce'], 'ceidg_check');
	if(!$nonceOK){
		wp_send_json_error(
             ['msg' => 'WP nonce got too old. Try again.']
        );
	}
    $numer_nip = trim($data['numerek_nip']);
    $numer_nip = str_replace('-','',$numer_nip);
    if( strlen($numer_nip) != 10 ){
        wp_send_json_error(['msg' => 'Błędny nr NIP']);
    }
	
    $base_url = 'https://dane.biznes.gov.pl/api/ceidg/v1/firmy?query&status=AKTYWNY';
    $tokenJWT = 'eyJraWQiOiJjZWlkZyIsImFsZyI6IkhTNTEyIn0.eyJnaXZlbl9uYW1lIjoiTWFyZWsiLCJwZXNlbCI6Ijg4MDkwNzExMjE1IiwiaWF0IjoxNjE5MjQ0OTI2LCJmYW1pbHlfbmFtZSI6IkJ1Z2EiLCJjbGllbnRfaWQiOiJVU0VSLTg4MDkwNzExMjE1LU1BUkVLLUJVR0EifQ.lcJsqFEmfSRsbjqR0xwVCzGFTxgePGLTlUw_RAMUHmn56RJjV5hBLNlqCfXcAxzPaiJqJGzT4MfiOdwa5UGQlQ';

    $final_url = $base_url . '&nip=' . $numer_nip;
    $authorization = 'Authorization: Bearer '.$tokenJWT;
    
	$ch = curl_init($final_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$response = curl_exec($ch);
	if(curl_errno($ch)){
		throw new Exception(curl_error($ch));
	}
	curl_close($ch);
	$response_arr = json_decode($response,1);
    if($response_arr['firmy'][0]){
        wp_send_json_success($response_arr['firmy'][0]);
    }
    else{
        wp_send_json_error(['msg' => 'Błędny nr NIP']);
    }
	
	return false;
}

add_action( 'init', 'mrq_register_shortcodes');

function mrq_register_shortcodes(){
    add_shortcode('ceidg-form', 'mrq_ceidg_get_form');
}

function mrq_ceidg_get_form() {
    ?>
    <form id="mrq_ceidg_form">
    <input id="numerek_nip" width="30" type="text" name="numerek_nip" placeholder="Podaj NIP" />
	<?php wp_nonce_field('ceidg_check') ?>
    <a class="mrq-btn" id="mrq_ceidg_check" href="javascript:void(0);" >Sprawdź NIP w CEIDG</a>
    </form>
    <div id="results2">
        <p class="label hidden">Nazwa firmy:</p>
        <h2 class="nazwafirmy"></h2>
        <p class="label hidden">Adres:</p>
        <p class="adres"></p>
        <p class="label hidden">NIP:</p>
        <p class="nip"></p>
        <p class="label hidden">REGON:</p>
        <p class="regon"> </p>
        <p class="label hidden">Data rozpoczęcia:</p>
        <p class="datarozpoczecia"></p>
        <p class="label hidden">STATUS:</p>
        <p class="status"></p>
    </div>
    <style>
		#results2{margin-top:30px;}
		.hidden{display:none}
		.label{font-size:14px;margin:0;}
		a.mrq-btn{margin:30px;}
		input#numerek_nip {width:200px!important;}
    </style>
    <?php 
}
