<?php
/*
Plugin Name: Wezar.de Deals Stream
Plugin URI: https://www.wezar.de/plugin
Description: Zeigt die neuesten Gutscheine von Wezar.de in einem Widget an.
Version: 1.0
Author: Maximilian Scherer
Author URI: https://www.wezar.de/imprint
License: GPL2
*/
?>
<?php
class wp_wezar_plugin extends WP_Widget {
	private $api_type;
	private $identifier;
	private $basedomain;
	private $api_url;
	private $urlgetcontents;
	private $urlgetcontents_https;
	private $maxVouchers;
	private $defaultVouchers;
	private $widgetName;
	
   public function __construct() {
   	$this->api_type = 'wp-plugin';
	$this->identifier = 'w_c8af943';
	$this->basedomain = 'www.wezar.de';
	$this->widgetName = 'Wezar.de Deals';
	$this->api_url = '/wp-content/plugins/wezar/external/api.php';
	$this->urlgetcontents = 'http://'.$this->basedomain.$this->api_url;
	$this->urlgetcontents_https = 'https://'.$this->basedomain.$this->api_url;
	$this->maxVoucherCount = 10;
	$this->defaultVoucherCount = 5;
	   
	parent::WP_Widget(false, $name = __($this->widgetName, 'wp_widget_plugin') );
    }

	// widget form creation
	function form($instance) {
		// Check values
    	  $title = empty($instance['title']) ? $this->widgetName : esc_attr($instance['title']);
    	  $vouchersCount = empty($instance['vouchersCount']) ? $this->defaultVoucherCount : esc_attr($instance['vouchersCount']);	
    	  $checkbox = empty($instance['checkbox']) ? 0 : esc_attr($instance['checkbox']);
	?>

	<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('vouchersCount'); ?>"><?php _e('Anzahl an Gutscheinen (Max. 10):', 'wp_widget_plugin'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('vouchersCount'); ?>" name="<?php echo $this->get_field_name('vouchersCount'); ?>" type="text" value="<?php echo $vouchersCount; ?>" />
	</p>
	
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'checkbox' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'checkbox' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $checkbox ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'checkbox' ) ); ?>"><?php _e( 'Links zu den Details auf Wezar.de erlauben', 'wp_widget_plugin' ); ?></label>
	</p>

	<?php
	}
		
	// update widget
	function update($new_instance, $old_instance) {
      	$instance = $old_instance;
      	// Fields
      	$instance['title'] = strip_tags($new_instance['title']);
        if($new_instance['vouchersCount'] <= $this->maxVoucherCount){
         $instance['vouchersCount'] = strip_tags($new_instance['vouchersCount']);
        }
    	$instance['checkbox'] = strip_tags($new_instance['checkbox']);
    	return $instance;
	}

	// display widget
	function widget($args, $instance) {
	    extract( $args );
   		$title = apply_filters('widget_title', $instance['title']);
   		$text = $instance['vouchersCount'];
   		$checkbox = empty($instance['checkbox']) ? 0 : esc_attr($instance['checkbox']);
   		   		
   		echo $before_widget;
   		// Display the widget
   		echo '<div class="widget-text wp_widget_plugin_box">';
   		
   		// Check if title is set
   		if ( $title ) {
     		 echo $before_title . $title . $after_title;
   		}

   		$response = $this->connectgetStream($instance['vouchersCount']);
   		if(empty($response)){
   		 echo 'Deals konnten nicht geladen werden';
   		}else{
   		 $decoded = json_decode($response);
   		 echo '<ul>';
   		 foreach($decoded as $obj){
   		 	if( $checkbox AND $checkbox == '1' ){
   		 		echo '<li><a href="'.$obj->guid.'" target="_blank">'.$obj->post_title.'</a></li>';
   		 	}
   		 	else{
   		 		echo '<li>'.$obj->post_title.'</li>';
   		 	}
   		 }
   		 //print_r($decoded);
   		}
   		echo '</ul>';
   		echo '</div>';
   		echo $after_widget;
	}
	
	function connectgetStream($count){
		try{
			return file_get_contents($this->getStreamUrl($count));
		}catch (Exception $e) {
    		echo __('Error', 'wp_widget_plugin'),  $e->getMessage(), "\n";
		}	
	}
	
	function getStreamUrl($count){
		$url = $this->urlgetcontents.'?type='.$this->api_type.'&identifier='.$this->identifier.'&count='.$count;
		return $url;
	}
	
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("wp_wezar_plugin");'));
?>