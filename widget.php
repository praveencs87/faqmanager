<?php
$ui_themes = array(
	'black-tie' => "Blank Tie",
	'blitzer' => "Blitzer",
	'cupertino' => "Cupertino",
	'dark-hive' => "Dark Hive",
	'dot-luv' => "Dot Luv",
	'eggplant' => "Eggplant",
	'excite-bike' => "Excite Bike",
	'flick' => "Flick",
	'hot-sneaks' => "Hot Sneaks",
	'humanity' => "Humanity",
	'le-frog' => "Le Frog",
	'mint-choc' => "Mint Choc",
	'overcast' => "Overcast",
	'pepper-grinder' => "Pepper Grinder",
	'redmond' => "Redmond",
	'smoothness' => "Smoothness",
	'south-street' => "South Street",
	'start' => "Start",
	'sunny' => "Sunny",
	'swanky-purse' => "Swanky Purse",
	'trontastic' => "Trontastic",
	'ui-darkness' => "UI Darkness",
	'ui-lightness' => "UI Lightness",
	'vader' => "Veder"
);


class FAQ_Widget extends WP_Widget {
    function FAQ_Widget() {
global $auth;
		if($auth==1) {	
        $widget_ops = array( 'classname' => 'faq_widget', 'description' => 'A widget that displays the FAQs according to Post/Page/Category or Primary ' );  
        $control_ops = array( 'width' => 400, 'height' => 450, 'id_base' => 'faq_widget-widget' );  
        $this->WP_Widget( 'faq_widget-widget', 'FAQ Widget', $widget_ops, $control_ops );  
    }
	}
	
	function widget( $args, $instance ) {
		extract($instance);
		extract( $args );
		$unique = rand(1, 99999);
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_style( 'faq-jquery-ui', "http://code.jquery.com/ui/1.10.3/themes/{$theme}/jquery-ui.css", array(), date("Ymdhms") );
		/***jscroll***/
		//wp_enqueue_style( 'faq-jscroll-css', MYFAQ_URL . '/jscroll/jquery.jscrollpane.css', array(), date("Ymdhms") );
		//wp_register_script( 'jquery-jscrollpane', MYFAQ_URL . '/jscroll/jquery.jscrollpane.min.js' );
		//wp_register_script( 'jquery-mousewheel', MYFAQ_URL . '/jscroll/jquery.mousewheel.js' );
		//wp_enqueue_script( 'jquery-jscrollpane' );
		//wp_enqueue_script( 'jquery-mousewheel' );
		
		
		$own_FAQs = $show_faq = FALSE;
		if ( is_single() || is_page() || is_category() ) { // post / page comes / category
			if ( is_single() || is_page() ) {
				global $post;
				
				$enabled_faq = get_post_meta($post->ID, '_enable_faq', TRUE);
				if ( $enabled_faq && $enabled_faq == 1 ) {
					$show_faq = TRUE;
				} else {
					return;
				}
				
				
				$faqs = get_post_meta($post->ID, "faqs", TRUE);
				
				
				$total = $faqs && isset($faqs['a']) && count($faqs['a']) > 0 ? count($faqs['a']) : 0;
				if ( $total ) {
					// post / page have its own FAQs
					$own_FAQs = TRUE;
				} else if ( is_single() ) {
					// post / page does not have its own FAQs, check if it is post then its category FAQs  
					$category = get_the_category();
					$faqs = get_option( 'category_' . $category[0]->term_id );
					
					if ( isset($faqs['enabled']) && $faqs['enabled'] == 1 ){
						$show_faq = TRUE;
					} else {
						return;
					}
					
					$total = $faqs && isset($faqs['a']) && count($faqs['a']) > 0 ? count($faqs['a']) : 0;
					if ( $total ) {
						// category FAQs for post
						$own_FAQs = TRUE; 
					}
				}
			} else if ( is_category() ) {
				$cat_id = get_cat_id( single_cat_title( "", FALSE ) );
				$faqs = get_option( 'category_' . $cat_id );
				if ( isset($faqs['enabled']) && $faqs['enabled'] == 1 ){
					$show_faq = TRUE;
				} else {
					return;
				}
					
				$total = $faqs && isset($faqs['a']) && count($faqs['a']) > 0 ? count($faqs['a']) : 0;
				if ( $total ) {
					// category have its own FAQs
					$own_FAQs = TRUE; 
				}
			}
		} else {
			$show_faq = TRUE;
		}
		if ( $show_faq ) {
			echo $before_widget;
			if ( isset($title) && !empty($title) ) { echo $before_title . $title . $after_title; }
		} else {
			return;
		}
		
		if ( $own_FAQs ) {
			$unique = rand(1, 99999);
			$accordion = "";
			for($i = 0; $i < $total; $i++) {
				if ($faqs['q'][$i] != "" && $faqs['a'][$i] != "" ) {
					$accordion .= "<h3 class='question-heading'>{$faqs['q'][$i]}</h3>";
					$accordion .= "<div class='answer-contents'>{$faqs['a'][$i]}</div>";
				}
			}
			if ( $accordion != "" ) {
				?>
				<div id='faq-accordion-<?php echo $unique;?>'><?php echo $accordion;?></div>
				<script type='text/javascript'>
					jQuery(document).ready(function($){
						$("#faq-accordion-<?php echo $unique;?>").accordion({
							heightStyle: "content",
				            autoHeight: true,
				        	clearStyle: true,
				        	collapsible: true,
			        		active: false
						});
					});
				</script>
				<?php
			} else { // single page and category does not have any FAQs, load primary faqs
				widget_primary_faqs_func($total_faqs);
			}
		} else {
			widget_primary_faqs_func($total_faqs);
		}
		
		if ( isset( $custom_css ) ) :
		?>
		<style type='text/css'>
			<?php echo $custom_css;?>
		</style>
		<?php
		endif; 
		if ( $show_faq ) {
			echo $after_widget;
		} else {
			return;
		}
		 
	}
	
	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
	
	function form( $instance ) {
	global $auth;
		if($auth==1) {
		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] :  NULL;
		$total_faqs = isset( $instance[ 'total_faqs' ] ) ? $instance[ 'total_faqs' ] :  5;
		$theme = isset( $instance[ 'theme' ] ) ? $instance[ 'theme' ] :  "black-tie";
		$custom_css = isset( $instance[ 'custom_css' ] ) ? $instance[ 'custom_css' ] :  NULL;
		global $ui_themes;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'total_faqs' ); ?>">Total Number of FAQs:</label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'total_faqs' ); ?>" name="<?php echo $this->get_field_name( 'total_faqs' ); ?>" type="text" value="<?php echo esc_attr( $total_faqs ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'theme' ); ?>">jQuery Accordion Theme:</label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'theme' ); ?>" name="<?php echo $this->get_field_name( 'theme' ); ?>">
			<?php
			foreach($ui_themes as $ui_theme => $name) {
				$sel = $ui_theme == $theme ? "selected='selected'" : NULL;
				echo "<option value='{$ui_theme}' {$sel}>{$name}</option>";
			}
			?>
		</select> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'custom_css' ); ?>">Custom CSS:</label> 
			<textarea class="widefat" style="height:200px;" id="<?php echo $this->get_field_id( 'custom_css' ); ?>" name="<?php echo $this->get_field_name( 'custom_css' ); ?>"><?php echo esc_attr( $custom_css ); ?></textarea>
			<strong>
				Custom CSS structure: 
<code>
<pre>
<?php echo $auth;?>
h3.question-heading {
	font-family: Tahoma;
	font-size: 14px; 
	color: #B52700;
}

div.answer-contents {
	font-family: Tahoma;
	font-size: 14px;
	font-weight: normal;
	color: #B35B22;
}
</pre>
</code>
			</strong>
		</p>
		<?php 
		
	}
	}
} 
function register_faq_widget() {  
	global $auth;
		if($auth==1) {
    register_widget( 'FAQ_Widget' );
}	
}  

add_action( 'widgets_init', 'register_faq_widget' ); 

function widget_primary_faqs_func($limit) {
	global $wpdb;
	$sql = "SELECT * FROM " . MYFAQ_TABLE . " ORDER BY rand() LIMIT {$limit}";
	$faqs = $wpdb->get_results($sql);
	$accordion = "";
	if ( $faqs ) {
		$unique = rand(1, 99999);
		foreach($faqs as $faq) {
			$accordion .= "<h3 class='question-heading'>". stripslashes($faq->question)."</h3>";
			$accordion .= "<div class='answer-contents'>". stripslashes($faq->answer)."</div>";
		}
		if ( $accordion != "" ) {
			?>
			<div id='faq-accordion-<?php echo $unique;?>'><?php echo $accordion;?></div>
			<script type='text/javascript'>
				jQuery(document).ready(function($){
					$("#faq-accordion-<?php echo $unique;?>").accordion({
						heightStyle: "content",
			            autoHeight: true,
			        	clearStyle: true,
			        	collapsible: true,
			        	active: false
					});
				});
			</script>
			<?php
		}
	}
}
//add_action( "widget_primary_faq", "widget_primary_faqs_func");