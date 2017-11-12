<?php 
/*
Plugin Name: Wilbertz: custom search form
Description: Search data from CSV file and redirect to a page location corresponding to the search term. USE SHORTCODE [wilbertz_search_form]
Plugin URI:  https://wilbertz.co.za
Author:      Wilbert Muza
Version:     1.0
*/
ob_clean(); ob_start();

//myplguin_admin_page function creates the form in the admin dashboard menu
function myplguin_admin_page(){
	?>
	
	<div class="wrap">
 
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
 
    <form method="post" >
 
        <div id="universal-message-container">
            <h2>Enter </h2>
 
            <div class="options">
                <p>
                    <label>Enter the ZIPCODE</label>
                    <br />
                    <input type="text" name="zipcode" value="" oninput"CheckmaxLength(this)" onkeypress="return isNumeric(event)" maxlength = "5" required/>					
                </p>
				<p>
                    <label>Enter Page to be opened</label>
                    <br />
                    <input type="text" name="webpage" value="" required/>					
                </p>
        </div>
 
        <?php
            wp_nonce_field( 'acme-settings-save', 'acme-custom-message' );
            submit_button();
        ?>
 
    </form>
 
</div>


	<?php
	//writing the details saved from the form to the csv file
	   	if(isset($_POST['zipcode'])) {	
		$file = __DIR__ . '/docs/read.csv';
		
		$list = array (
					array($_POST['zipcode'], $_POST['webpage'])
				);	
				
		$fp = fopen($file, 'a');

		foreach ($list as $fields) {
			fputcsv($fp, $fields, ',');
		}

		fclose($fp);
				
		echo "Data saved successfully";
	    
		}	
}


//creating menu in the admin dashboard
function my_admin_menu() {
	add_menu_page( 'Wilbert Form', 'Wilbertz Form', 'manage_options', 'myplugin-admin-page.php', 'myplguin_admin_page', 'dashicons-tickets', 10  );
}

add_action( 'admin_menu', 'my_admin_menu' );



// enqueue js scripts
function wilbertz_public_enqueue_scripts( $hook ) {

	// define script url
	$script_url = plugins_url( 'js/jquery-ui.js', __FILE__ );

	// enqueue script
	wp_enqueue_script( 'ajax-public', $script_url, array( 'jquery' ) );
	
	// create nonce
	$nonce = wp_create_nonce( 'wilbertz_public' );

	// define ajax url
	$ajax_url = admin_url( 'wilbertz_search.php' );

	// define script
	$script = array( 'nonce' => $nonce, 'ajaxurl' => $ajax_url );

	// localize script
	wp_localize_script( 'ajax-public', 'wilbertz_public', $script );
	
}
add_action( 'wp_enqueue_scripts', 'wilbertz_public_enqueue_scripts' );



//define the search function
function wilbertz_search_function (){

	$arr = array();
	$file_name = plugins_url( 'docs/read.csv', __FILE__ );
	
	if (($handle = fopen($file_name, "r")) !== FALSE) {
			
			echo '<script type="text/javascript"> jQuery(function(){ var availablezip = [';
			
			while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {        
				echo '"'. $data[0] .'",';	
				$arr[$data[0]] = $data;		 
			}
			echo ']; jQuery( "#zip" ).autocomplete({source: availablezip, autoFocus: true, select: function(event, ui) {jQuery("#zip").val(ui.item.value);jQuery("#searchForm").submit();return false; }});} );</script>';
			
			fclose($handle);
	}
	
	echo '<label for="zip">ZIPCODE: </label>
		  <form id="searchForm" method="post">
		  <input id="zip" type="number" name="zipp" oninput="CheckmaxLength(this)" onkeypress="return isNumeric(event)" maxlength = "5">
		  <input type="submit" style="visibility: hidden;" />
		  </form>';
		  
	if(isset($_POST['zipp'])) {
		  if(isset($arr[$_POST['zipp']])) {
				// do arr					
				$rowNum = $arr[$_POST['zipp']]; 
				// print_r($rowNum[1]);
				$url = "../".$rowNum[1];
				wp_redirect($url);
				exit();

		  }
	}
	echo '<script type="text/javascript">
	  // check the maximum length on the number input field
	  function CheckmaxLength(object){
		if (object.value.length > object.maxLength){object.value = object.value.slice(0, object.maxLength)}
	  }
	   // validate the input value
		function isNumeric (evt) {
		var theEvent = evt || window.event;
		var key = theEvent.keyCode || theEvent.which;
		key = String.fromCharCode (key);
		var regex = /[0-9]/;
		if ( !regex.test(key) ) {
		  theEvent.returnValue = false;
		  if(theEvent.preventDefault) theEvent.preventDefault();
		}
	  }</script>';
	
}


//plugin shortcode
function wilbertz_search_shortcode( $atts ){
	return wilbertz_search_function ();
}
add_shortcode( 'wilbertz_search_form', 'wilbertz_search_shortcode' );






















