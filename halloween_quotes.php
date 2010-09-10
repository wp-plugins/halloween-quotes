<?php
/*
Plugin Name: Halloween quotes
Plugin URI: http://www.gigacart.com/halloween-quotes-widget.html
Description: Scare your blog visitors with special Halloween effects that appear unexpectedly at specified time intervals.  There are many different types of animations such as a laughing witch, a running black cat and many more that will be displayed randomly. The widget also displays a random Halloween joke on each page.
Author: GigaCart
Author URI:http://www.gigacart.com
Version: 1.0.0
*/

class halloween_quotes_widget {
// Path to plugin cache directory
    var $cachePath;
    // Cache file variable
    var $cacheFile;
    /*
     * Class constructor function
     */
    function halloween_quotes_widget() {
        $this->cachePath = ABSPATH . 'wp-content/plugins/halloween-quotes/cache/';
        $this->cacheFile = 'hb.widget';
    }
    /*
     * Initiliaze widgets
     */
    function init() {
        // get all widgets options
        if (!$options = get_option('widget_halloween_quotes'))
            $options = array();

        $widget_ops = array('classname' => 'widget_halloween_quotes', 'description' => 'Random halloween quote/joke and animation of the day');
        $control_ops = array('width' => 650, 'height' => 100, 'id_base' => 'halloween_quotes_widget');
        $name = 'Halloween quotes';

        $registered = false;
        foreach (array_keys($options) as $o) {
            if (!isset($options[$o]['title']))
                continue;
            // unique widget id
            $id = "halloween_quotes_widget-$o";
            //check if the widgets is active
            global $wpdb;
            $sql = "SELECT option_value FROM $wpdb->options WHERE option_name = 'sidebars_widgets' AND option_value like '%".$id."%'";
            $var = $wpdb->get_var( $sql );
            //do this to keep the size of the array down
            if (!$var) unset($options[$o]);

            $registered = true;
            wp_register_sidebar_widget($id, $name, array(&$this, 'sidebar_widget'), $widget_ops, array( 'number' => $o ) );
            wp_register_widget_control($id, $name, array(&$this, 'widget_control'), $control_ops, array( 'number' => $o ) );
        }
        if (!$registered) {
            wp_register_sidebar_widget('halloween_quotes_widget-1', $name, array(&$this, 'sidebar_widget'), $widget_ops, array( 'number' => -1 ) );
            wp_register_widget_control('halloween_quotes_widget-1', $name, array(&$this, 'widget_control'), $control_ops, array( 'number' => -1 ) );
        }
        update_option('widget_halloween_quotes', $options);
    }

    function sidebar_widget($args, $widget_args = 1) {
        extract($args);

        if (is_numeric($widget_args))
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array( 'number' => -1 ));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('widget_halloween_quotes');
        if (!isset($options_all[$number]))
            return;

        $options = $options_all[$number];

        //output the story
        echo $before_widget.$before_title;
        echo $options["title"];
        echo $after_title;
        echo $this->display($options, $number);
        echo $after_widget;
    }

    function widget_control($widget_args = 1) {

        global $wp_registered_widgets;

        static $updated = false;

        //extract widget arguments
        if ( is_numeric($widget_args) )$widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array('number' => -1));
        extract($widget_args, EXTR_SKIP);

        $options_all = get_option('widget_halloween_quotes');
        if (!is_array($options_all))$options_all = array();  

        if (!$updated && !empty($_POST['sidebar'])) {
            $sidebar = (string)$_POST['sidebar'];

            $sidebars_widgets = wp_get_sidebars_widgets();
            if (isset($sidebars_widgets[$sidebar]))
                $this_sidebar =& $sidebars_widgets[$sidebar];
            else
                $this_sidebar = array();

            foreach ($this_sidebar as $_widget_id) {
                if ('widget_halloween_quotes' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
                    $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                    if (!in_array("halloween_quotes_widget-$widget_number", $_POST['widget-id']))
                        unset($options_all[$widget_number]);
                }
            }
            foreach ((array)$_POST['widget_halloween_quotes'] as $widget_number => $posted) {
                if (!isset($posted['title']) && isset($options_all[$widget_number]))
                    continue;
                // set widget options
                $options = array();
                $options['title'] = $posted['title'];
                $options['display_type'] = $posted['display_type'];
                $options_all[$widget_number] = $options;
            }
            update_option('widget_halloween_quotes', $options_all);
            $updated = true;
        }
		// default widget options
		$default_options = array(
				'title' => __('Halloween quotes', 'halloween-quotes'),
				'display_type' => 'day'
		);

        if (-1 == $number) {
            $number = '%i%';
            $values = $default_options;
        } else {
            $values = $options_all[$number];
        }

		// widget options form ?>
        <p align="right"><span class="setting-description"><small><?php _e('all settings are for this widget only.', 'halloween-quotes')?></small></span></p>
        <p><label><strong><?php _e('Title', 'halloween-quotes')?></strong></label>
		<input class="widefat" id="widget_halloween_quotes-<?php echo $number; ?>-title" 
        name="widget_halloween_quotes[<?php echo $number; ?>][title]" type="text" 
        value="<?php echo htmlspecialchars($values['title'], ENT_QUOTES); ?>" />
        </p>
		<p>
			<label for="widget_halloween_quotes-<?php echo $number; ?>-display_type"><?php _e('Select story rotation period'); ?></label><br />
            <input type="radio" name="widget_halloween_quotes[<?php echo $number; ?>][display_type]" value="hour" <?php if ($values['display_type']=='hour') echo ' checked="checked"'; ?> />&nbsp;<?php _e('Story of the hour', 'halloween-quotes'); ?><br />
            <input type="radio" name="widget_halloween_quotes[<?php echo $number; ?>][display_type]" value="day" <?php if ($values['display_type']=='day') echo ' checked="checked"'; ?> />&nbsp;<?php _e('Story of the day', 'halloween-quotes'); ?><br />
            <input type="radio" name="widget_halloween_quotes[<?php echo $number; ?>][display_type]" value="week" <?php if ($values['display_type']=='week') echo ' checked="checked"'; ?> />&nbsp;<?php _e('Story of the week', 'halloween-quotes'); ?><br />
		</p>		
        <?php 
	}
    /*
     * Read from cache file if exists, else fecth new data to cache file
     */
    function display($widgetData, $widgetId = "1") {
        global $wp_version;

        $pathToFile = sprintf("%s%s-%s.xml", $this->cachePath, $this->cacheFile, $widgetId);

        $htmlOutput = '';

        // Checking if cache file exist
        if (file_exists($pathToFile) && filesize($pathToFile) > 0) {
            // File does exist, checking if its expired
            if (!$this->checkCacheTime($widgetData['display_type'],filemtime($pathToFile))) {

                // Cache has expired, fetching new data
                $htmlOutput = $this->fetchData($widgetData);
                if ($wp_version >= '2.7') {
                // Saving new data to cache
                if ($htmlOutput['response']['code'] == 200)
                   $this->saveData($htmlOutput['body'], $widgetId);
                } else {
                // Saving new data to cache
                if ($htmlOutput->status == '200')
                   $this->saveData($htmlOutput->results, $widgetId);
                }
                return $this->readCache($widgetData, $widgetId);
            }
            return $this->readCache($widgetData, $widgetId);
        } else {
            // No file found, someone deleted it or first time widget usage
            // Let's create new file with fresh content
            $htmlOutput = $this->fetchData($widgetData);

            if ($wp_version >= '2.7') {
            // Before output, let's save new data to cache
            if ($htmlOutput['response']['code'] == 200)
                $this->saveData($htmlOutput['body'], $widgetId);
            } else {
            // Before output, let's save new data to cache
            if ($htmlOutput->status == '200')
                $this->saveData($htmlOutput->results, $widgetId);
            }
            return $this->readCache($widgetData, $widgetId);
        }
    }
    /*
     * fetch data to cache file
     */
    function fetchData($widgetData) {
        global $wp_version;
        // Set user specified data

        if ($wp_version >= '2.7') {
            $authKey = md5($_SERVER['REQUEST_URI']);
            $client = wp_remote_get('http://www.gigacart.com/development/wp/halloween/getStory.php?auth_key='.$authKey);
        } else {
            echo 'Incorrect WordPress version. At least 2.7 needed';
            return false;
        }

        return $client;
    }
    /*
     * Save data to cache file
     */
    function saveData($data, $widgetId = "1") {
        // Path to cache file
        $pathToFile = sprintf("%s%s-%s.xml", $this->cachePath, $this->cacheFile, $widgetId);
        // Open cache file for writing
        if (!$handle = @fopen($pathToFile, 'w')) {
            echo 'Cannot open file ('.$pathToFile.') Check folder permissions!';
            return false;
        }
        // Write data to cache file
        if (@fwrite($handle, $data) === false) {
            echo 'Cannot write to file ('.$pathToFile.') Check folder permissions!';
            return false;
        }
        // Close cache file
        if (!@fclose($handle)) {
            echo 'Cannot close file ('.$pathToFile.') Check folder permissions!';
            return false;
        }
    }

    function readCache($widgetData, $widgetId = "1") {
        // Path to cache file
        $pathToFile = sprintf("%s%s-%s.xml", $this->cachePath, $this->cacheFile, $widgetId);
        // Data variable
        $data = '';
        // Read the data from cache file
        if (!$data = @simplexml_load_file($pathToFile)) {
            echo 'Cannot read file ('.$pathToFile.') Check folder permissions!';
            return false;
        }
        // if XML parsed successfully
        if ($data) {
            $outputLines = '';
            foreach($data->item as $item) {
                // Display story
                if($item->title)
                    $outputLines .= "<h4>".$item->title."</h4>";
                $outputLines .= "<p>".htmlspecialchars($item->text)."</p>";
                if(isset($item->text_bottom) && isset($item->auth_key) && md5($_SERVER['REQUEST_URI']) == $item->auth_key)
                   $outputLines .= $item->text_bottom;
            }
            return $outputLines;
        } else {
            $errormsg = 'Failed to parse XML file.';
            return false;
        }

    }

    function checkCacheTime($displayType, $fileCreateTime)
    {
        if ($displayType == "hour") {
            if ($fileCreateTime < mktime(date("H"),0,0))
                return false;
        } elseif ($displayType == "day") {
            if ($fileCreateTime < mktime(0,0,0))
                return false;
        } elseif ($displayType == "week") {
            if ($fileCreateTime < strtotime("Monday"))
                return false;
        }
        return true;
    }


}

$hbw = new halloween_quotes_widget();
add_action('widgets_init', array($hbw, 'init'));


include('pages/hb_settings.php');

//build submenu entries
function halloween_quotes_add_pages() {
	add_menu_page('Halloween quotes', __('Halloween','halloween-quotes'), 'manage_options', __FILE__, 'hb_settings');
}

add_action('admin_menu', 'halloween_quotes_add_pages');

function halloween_quotes_install() {

    $defaultOptions = array(
	    'halloween_animations' => "Y",
		'halloween_show_time' => 60
	);

	update_option('halloween_quotes_options', $defaultOptions);
}

function halloween_quotes_uninstall() {

    delete_option('halloween_quotes_options');
    delete_option('widget_halloween_quotes');
}

register_activation_hook(__FILE__, 'halloween_quotes_install');
register_deactivation_hook(__FILE__, 'halloween_quotes_uninstall');

class halloween_quotes_js
{
    function init() {

        $widgetActive = false;
        
        foreach (wp_get_sidebars_widgets() as $widgetArea => $widgets) {
            if($widgetArea != "wp_inactive_widgets") {
                foreach($widgets as $widget) {
                    if(strpos($widget, "halloween_quotes_widget") !== false) {
                        $widgetActive = true;
                    }
                }
            }
        }
        $options = get_option('halloween_quotes_options');
        if( $options['halloween_animations'] == "Y" && $widgetActive) {
            wp_enqueue_script('jquery');
            add_action('wp_head', array($this, 'halloween_animations'));
            add_action('wp_ajax_my_special_action', array($this, 'animation_callback'));
            add_action('wp_ajax_nopriv_my_special_action', array($this, 'animation_callback'));
        }
    }

    function halloween_animations() {
    $options = get_option('halloween_quotes_options');
    ?>
    <script src="<?php echo get_bloginfo('url')?>/wp-content/plugins/halloween-quotes/js/swfobject.js" type="text/javascript"></script>
    <script type="text/javascript">
    jQuery(document).ready(function(){

        var animation = null;
        var index = 0;

	    jQuery.ajax({
	        cache : false,
	        url : "<?php echo admin_url('admin-ajax.php');?>",
	        data : { action: 'my_special_action' },
	        dataType : 'json',
	        success : function(output) {
	           animation = output;
               setTimeout(loadAnimation, 5000);
	        }
	    });

	    function loadAnimation() {
	        
            if(animation.length > 0)
            {
                var image = animation[index]['file'];
                var width = jQuery(window).width() - image['width'];

                if(animation[index]['type'] == "Image/GIF")
                {
                    var height = jQuery(window).height() - image['height'];
                    jQuery('body').append('<div id="halloween-accessory-'+index+'"><img src="' + image['src'] + '" /></div>');
                    if(animation[index]['animate-function'] == 'toBottomBackToTop') {
                        jQuery('#halloween-accessory-' + index).css({"position" : "absolute", "top" : 0 - image['height'], "left" : randomXToY(0,width), "z-index" : 999 });
                        jQuery('#halloween-accessory-' + index).animate({"top":  0 }, parseInt(animation[index]['show-time']), function() { jQuery(this).fadeOut((parseInt(animation[index]['show-time'])/2), function() { jQuery(this).remove(); }) } );
                    }
                } else if(animation[index]['type'] == "Flash") {
                    var height = jQuery(window).height() - image['height'];
                    jQuery('body').append('<div id="halloween-accessory-'+index+'"><div id="embed-swf-'+index+'"></div></div>');
                    var flashvars = {};
                    var params = { wmode : "transparent"};
                    var attributes = {};

                    swfobject.embedSWF("<?php echo get_bloginfo("wpurl"); ?>/" + image['src'], "embed-swf-" + index, image["width"], image["height"], "9.0.0",null, flashvars, params, attributes);
                    
                    if(animation[index]['animate-function'] == 'fromRightToLeft') {
                        jQuery('#halloween-accessory-' + index).css({"position" : "fixed", "bottom" : 0, "right" : 0, "overflow":"hidden", "z-index" : 999});
                        jQuery('#halloween-accessory-' + index).animate({"right":jQuery(window).width()}, parseInt(animation[index]['show-time']), function() { jQuery(this).remove()});
                    } else if(animation[index]['animate-function'] == 'bottomLeft') {
                        jQuery('#halloween-accessory-' + index).css({"position" : "fixed", "bottom" : 0, "left" : 0, "overflow":"hidden", "z-index" : 999});
                        jQuery('#halloween-accessory-' + index).animate({opacity : 1.0},parseInt(animation[index]['show-time']), function() {jQuery(this).fadeOut((parseInt(animation[index]['show-time'])/2), function() { jQuery(this).remove(); })});
                    } else if(animation[index]['animate-function'] == 'leftRandomY'){
                        jQuery('#halloween-accessory-' + index).css({"position" : "fixed", "top" : randomXToY(0,height), "left" : 0, "z-index" : 999});
                        jQuery('#halloween-accessory-' + index).animate({opacity : 1.0},parseInt(animation[index]['show-time']), function() {jQuery(this).fadeOut((parseInt(animation[index]['show-time'])/2), function() { jQuery(this).remove(); })});
                    } else if(animation[index]['animate-function'] == 'bottomRandomX'){
                        jQuery('#halloween-accessory-' + index).css({"position" : "fixed", "bottom" : 0, "left" : randomXToY(0,width), "z-index" : 999});
                        jQuery('#halloween-accessory-' + index).animate({opacity : 1.0},parseInt(animation[index]['show-time']), function() {jQuery(this).fadeOut((parseInt(animation[index]['show-time'])/2), function() { jQuery(this).remove(); })});
                    } else if(animation[index]['animate-function'] == 'randomXYMoving') {
                        jQuery('#halloween-accessory-' + index).css({"position" : "fixed", "top" : randomXToY(height/2,height), "left" : randomXToY(0,width), "z-index" : 999});
                        jQuery('#halloween-accessory-' + index).animate({
                            top: ['toggle', 'swing'],
                            left: [randomXToY(0,width), 'swing'],
                            opacity: 'toggle'
                        }, parseInt(animation[index]['show-time']), 'linear', function () { jQuery(this).remove(); });
                    } else {
                        jQuery('#halloween-accessory-' + index).css({"position" : "fixed", "top" : randomXToY(0,height), "left" : randomXToY(0,width), "z-index" : 999});
                        jQuery('#halloween-accessory-' + index).animate({opacity : 1.0},parseInt(animation[index]['show-time']), function() {jQuery(this).fadeOut((parseInt(animation[index]['show-time'])/2), function() { jQuery(this).remove(); })});
                    }
                    
                }
                if (index == (animation.length - 1)) {
                    index = 0;
                } else {
                    index = index + 1;
                }
            }
            setTimeout(loadAnimation, parseInt(<?php echo $options['halloween_show_time'] ?>) * 1000);
        }

        function randomXToY(minVal,maxVal,floatVal) {
            var randVal = minVal+(Math.random()*(maxVal-minVal));
            return typeof floatVal=='undefined'?Math.round(randVal):randVal.toFixed(floatVal);
        }

    });
    </script>
    <?php
    }

    function animation_callback() {
        $xml = simplexml_load_file(ABSPATH . 'wp-content/plugins/halloween-quotes/animations.xml');
        foreach($xml as $entry)
            $animations[] = $entry;
        shuffle($animations);
        ob_clean();
        echo json_encode($animations);
	    die;
    }

}

$hbJS = new halloween_quotes_js();
$hbJS->init();

?>