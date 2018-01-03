<?php
/*
Plugin Name: FAQs Manager
Description: This will allow to add unlimited Q&A for Posts and Categories and display using Widget
Author: Abc
Version: 1.0
*/
global $wpdb;
define( "MYFAQ_URL", untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( "MYFAQ_PATH", untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( "MYFAQ_TABLE", $wpdb->prefix. "faqs");


function authorizemyplugin() {

$site_url = 'http://testbin.pinetech.in/devLic/PluginValidate.php';
	//$site_url = 'http://bookmarklovers.com/Premium/php/PluginValidate.php';
	$ch = curl_init();
	$timeout = 5; // set to zero for no timeout
	curl_setopt ($ch, CURLOPT_URL, $site_url);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$domain = $_SERVER['HTTP_HOST'];
	$postData = 'access='.get_option('bml_access').'&transid='.get_option('bml_transid').'&site='.$domain.'&checkit=checkit';
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $postData);
	ob_start();
	curl_exec($ch);
	curl_close($ch);
	$authorise = ob_get_contents();
	ob_end_clean();
	
	return $authorise;
}
$auth=0;
$authorise=authorizemyplugin();
	if($authorise=="Authorised!") {
	$auth=1;
	}

class MyFAQManager {
	
	
	
	function admin_enqueue(){
		wp_register_style('faq-css', MYFAQ_URL . '/style.css');
		wp_enqueue_style('faq-css');
	}
	/** post / page **/
	function save_post($post_id){
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		
		if ( isset($_POST['faqpar']) ) {
			if( ! wp_verify_nonce( $_POST['faqpar'], 'faqpar_faq_nonce' ) ) {
		        wp_die( __( 'Cheatin&#8217; uh?' ) );
		    }
			
			
			//var_dump($_REQUEST['faq']);
			///die;
			if ( isset($_REQUEST['faq']['a']) && count($_REQUEST['faq']['a']) > 0 ){
				update_post_meta($post_id, "faqs", $_REQUEST['faq']);
			} else {
				delete_post_meta($post_id, "faqs");
			}
			
			if ( isset($_REQUEST['_enable_faq']) ) {
				update_post_meta($post_id, "_enable_faq", 1);
			} else {
				delete_post_meta($post_id, "_enable_faq");
			}
		}	
	}
	
	function faqs_form($post){
		wp_nonce_field( 'faqpar_faq_nonce', 'faqpar', TRUE, TRUE );
		$faqs = get_post_meta($post->ID, "faqs", TRUE);
		
		$total_faqs = $faqs && isset($faqs['a']) && count($faqs['a']) > 0 ? count($faqs['a']) : 0;
		$enable_faq = get_post_meta($post->ID, '_enable_faq', TRUE);
		$chk_enable = ( $enable_faq && $enable_faq == 1 ) ? 'checked="checked"' : NULL;
		?>
		<p>
			<label for="_enable_faq">Enable FAQ</label>
			<input type="checkbox" name="_enable_faq" id="_enable_faq" <?php echo $chk_enable; ?> value="1" />
		</p>
		<div class='faq-manager'>
			<div id='static'>
				<div>
					<p><label for=''>Question</label><span type='text' class='txt' name='faq[q][]' /></p>
					<p><label for=''>Answer</label><span type='text' class='txt' name='faq[a][]' /></p>
					<p class='faq_controls'>
						<input type='button' class='add_new faq-button ' value='+' />
						<input type='button' class='remove faq-button ' value='-' />
					</p>
				</div>
			</div>
			
			<div id='faqs'>
				<?php
				for($i = 0; $i < $total_faqs; $i++) {
					?>
					<div>
						<p><label for=''>Question</label><input type='text' class='txt' name='faq[q][]' value='<?php echo $faqs['q'][$i]; ?>' /></p>
						<p><label for=''>Answer</label><input type='text' class='txt' name='faq[a][]' value='<?php echo $faqs['a'][$i]; ?>' /></p>
						<p class='faq_controls'>
							<input type='button' class='add_new faq-button ' value='+' />
							<input type='button' class='remove faq-button ' value='-' />
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<div id='controls'>
				<p>
					<input type='button' class='add_new button button-primary button-large' value='Ad FAQ' />
				</p>
			</div>
			
		</div>
		<script type='text/javascript'>
			jQuery(document).ready(function($){
				$(document).on("click", ".remove", function(){
					if ( confirm("are you sure to remove this?") ) {
						$(this).parent().parent().remove();
					}
					
				});
				
				$(document).on('click', ".add_new", function(){
					_static_html = $("div#static").html().replace(/span/gi, "input");
					$("#faqs").append(_static_html);
				});
			});
		</script>
		<?php
	}
     
	function faq_meta_box() {
	global $auth;
	
	if($auth==1) {
		add_meta_box(
			'faqs_boxid',
			'FAQs',
			array('MyFAQManager', 'faqs_form'),
			'post',
			'advanced'
		);
		add_meta_box(
			'faqs_boxid',
			'FAQs',
			array('MyFAQManager', 'faqs_form'),
			'page',
			'advanced'
		);
	}
	}
	
	/** category **/
	function add_category_fields($taxonomy){
		wp_nonce_field( 'faqpar_faq_nonce', 'faqpar', TRUE, TRUE );
		global $auth;
		echo $auth;
		echo authorizemyplugin();
		if($auth==1) {
		?>
		<p>
			<label for="_enable_faq">Enable FAQ</label>
			<input type="checkbox" name="_enable_faq" id="_enable_faq" value="1" />
		</p>
		<div class='faq-manager'>
			<div id='static'>
				<div>
					<p><label for=''>Question</label><span type='text' class='txt' name='faq[q][]' /></p>
					<p><label for=''>Answer</label><span type='text' class='txt' name='faq[a][]' /></p>
					<p class='faq_controls'>
						<input type='button' class='add_new faq-button ' value='+' />
						<input type='button' class='remove faq-button ' value='-' />
					</p>
				</div>
			</div>
			
			<div id='faqs'>
			</div>
			<div id='controls'>
				<p>
					<input type='button' class='add_new button button-primary button-large' value='Ad FAQ' />
				</p>
			</div>
			
		</div>
		<script type='text/javascript'>
			jQuery(document).ready(function($){
				$(document).on("click", ".remove", function(){
					if ( confirm("are you sure to remove this?") ) {
						$(this).parent().parent().remove();
					}
					
				});
				
				$(document).on('click', ".add_new", function(){
					_static_html = $("div#static").html().replace(/span/gi, "input");
					$("#faqs").append(_static_html);
				});
			});
		</script>
		<?php
		}
	}

	function edit_category_fields($taxonomy){
	global $auth;
	if($auth==1) {
		wp_nonce_field( 'faqpar_faq_nonce', 'faqpar', TRUE, TRUE );
		$cat_id = $taxonomy->term_id;
		$faqs = get_option('category_' . $cat_id);
		$total_faqs = $faqs && isset($faqs['a']) && count($faqs['a']) > 0 ? count($faqs['a']) : 0;
		
		$chk_enable = ( isset($faqs['enabled']) && $faqs['enabled'] == 1 ) ? 'checked="checked"' : NULL;
		?>
		<p>
			<label for="_enable_faq">Enable FAQ</label>
			<input type="checkbox" name="_enable_faq" id="_enable_faq" <?php echo $chk_enable; ?> value="1" />
		</p>
		<div class='faq-manager'>
			<div id='static'>
				<div>
					<p><label for=''>Question</label><span type='text' class='txt' name='faq[q][]' /></p>
					<p><label for=''>Answer</label><span type='text' class='txt' name='faq[a][]' /></p>
					<p class='faq_controls'>
						<input type='button' class='add_new faq-button ' value='+' />
						<input type='button' class='remove faq-button ' value='-' />
					</p>
				</div>
			</div>
			
			<div id='faqs'>
				<?php
				for($i = 0; $i < $total_faqs; $i++) {
					?>
					<div>
						<p><label for=''>Question</label><input type='text' class='txt' name='faq[q][]' value='<?php echo $faqs['q'][$i]; ?>' /></p>
						<p><label for=''>Answer</label><input type='text' class='txt' name='faq[a][]' value='<?php echo $faqs['a'][$i]; ?>' /></p>
						<p class='faq_controls'>
							<input type='button' class='add_new faq-button ' value='+' />
							<input type='button' class='remove faq-button ' value='-' />
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<div id='controls'>
				<p>
					<input type='button' class='add_new button button-primary button-large' value='Ad FAQ' />
				</p>
			</div>
			
		</div>
		<script type='text/javascript'>
			jQuery(document).ready(function($){
				$(document).on("click", ".remove", function(){
					if ( confirm("are you sure to remove this?") ) {
						$(this).parent().parent().remove();
					}
					
				});
				
				$(document).on('click', ".add_new", function(){
					_static_html = $("div#static").html().replace(/span/gi, "input");
					$("#faqs").append(_static_html);
				});
			});
		</script>
		<?php
	}
	}

	function save_category_fields( $cat_id ) {
		if ( isset($_POST['faqpar']) ) {
			if ( isset($_REQUEST['faq']['a']) && count($_REQUEST['faq']['a']) > 0 ){
				if ( isset( $_REQUEST['_enable_faq'] ) ){
					$_REQUEST['faq']['enabled'] = 1;
				} else {
					$_REQUEST['faq']['enabled'] = 0;
				}
				update_option( 'category_' . $cat_id, $_REQUEST['faq'] );
			}
			
			
		}
	}
 	
	/** custom table **/
	function install() {
		$sql = "
		CREATE TABLE IF NOT EXISTS " . MYFAQ_TABLE . " (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `question` text NOT NULL,
			  `answer` text NOT NULL,
			  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			);
		";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
    }
	
	function myfaq_manager_func(){
		do_action("display_faq_manager");
	}
	
	function myfaq_manage_func(){
		do_action("faq_manage");
	}
	
	function myfaq_process_func(){
		do_action("faq_process");
	}
	
	function admin_menu() {
		add_menu_page( 'FAQ Manager', 'FAQ Manager', 'administrator', 'myfaq-manager', array( 'MyFAQManager', 'myfaq_manager_func' ) );
		add_submenu_page( '', 'Manage FAQ', 'Manage FAQ', 'administrator', 'myfaq-manage', array( 'MyFAQManager', 'myfaq_manage_func' ) );
		add_submenu_page( '', 'Process FAQ', 'Process FAQ', 'administrator', 'myfaq-process', array( 'MyFAQManager', 'myfaq_process_func' ) );
	}
}

/** posts / pages **/
add_action( 'add_meta_boxes', array( 'MyFAQManager', 'faq_meta_box' ) );
add_action( 'admin_enqueue_scripts', array( 'MyFAQManager', 'admin_enqueue' ) );
add_action( 'save_post', array( 'MyFAQManager', 'save_post' ) );

/** category management **/
add_action( 'category_add_form_fields' , array( 'MyFAQManager' , 'add_category_fields') , 10 , 10 );
add_action( 'category_edit_form_fields' , array( 'MyFAQManager' , 'edit_category_fields') );
add_action( 'edited_category' , array( 'MyFAQManager' , 'save_category_fields' ) , 10 , 2 );
add_action( 'created_category', array( 'MyFAQManager' , 'save_category_fields' ) , 10 , 2 );

/**custom tables**/
register_activation_hook( __FILE__, array( 'MyFAQManager', 'install' ) );

add_action( 'admin_menu', array( 'MyFAQManager', 'admin_menu' ) );

/** includes **/
include_once ("faqs.php");


include_once ("widget.php");

