<?php 

//settings page
function hb_settings() {
	
	global $wpdb;

	//check whether user can manage options
	if( !current_user_can('manage_options') ) die('Access Denied');	

	//decode and intercept
	foreach($_POST as $key => $val) {
		$_POST[$key] = stripslashes(utf8_encode($val));
	}
	
	//handle the post event
	if(!empty($_POST['do'])) {

		//create array of values *ALL VALUES MUST BE INCLUDED HERE
		$showTime = ($_POST['show_time'] >= 15 && is_numeric($_POST['show_time']))?$_POST['show_time']:30;
		$halloweenOptions = array(
		   'halloween_animations' => $_POST['animations'],
		   'halloween_show_time' => $showTime
		);

		//update options
		$update_halloween_options = update_option('halloween_quotes_options', $halloweenOptions);			
		
		if ($update_halloween_options) {
            //positive feedback
             ?><div id="message" class="updated fade below-h2"><p>
            <?php _e('<strong>Options saved...</strong> ','halloween-quotes'); ?></p></div><?php 

		} else {
		
			//negative feedback		
			?><div id="message" class="error fade below-h2"><p>
            <?php _e('<strong>The options could not be saved</strong>. Either the operation went wrong, or you didn\'t make any changes.</strong> ','halloween-quotes'); 
			?></p></div><?php 
		}			
		
	}	
	
	//get the options
	$halloweenOptions = array();
	$halloweenOptions = get_option('halloween_quotes_options');
	
	$displayAnimations = $halloweenOptions['halloween_animations'];
	$showTime = $halloweenOptions['halloween_show_time'];
	
	if ( $displayAnimations == 'Y' ) $halloweenAnimationsSelected = 'checked';	
	
    //the options form
    ?>
	<form name="frm_options" method="post" action="<?php echo ($_SERVER['REQUEST_URI']); ?>">
	<?php //AJAX loader ?>
    <p><h3 style="line-height:.1em"><?php _e('Animations settings','halloween-quotes') ?></h3></p>
    <table class="form-table">  
    <tr valign="top"><th scope="row"><?php _e('Enable/Disable Animations','halloween-quotes') ?></th>    
        <td colspan="2">
            <input type="checkbox" name="animations" value="Y" <?php echo ($halloweenAnimationsSelected); ?> />
            <span class="setting-description"><?php _e('<br/>Needs at least one active module widget to display animations ','halloween-quotes') ?></span>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><?php _e('Animation appear time','halloween-quotes') ?></th>
        <td colspan="2">
            <input type="text" size="50" name="show_time" value="<?php echo (utf8_decode(htmlspecialchars($showTime))); ?>" class="regular-text"  id="ajaxinput2" />
            <span class="setting-description"><?php _e('<br/>Minimal time is 15 seconds','halloween-quotes') ?></span>
        </td>
    </tr>   
	</table>
    <br/>
	<div class="submit">
    <input type="hidden" name="do" value="Update" />
    <input type="submit" value="<?php _e('Update settings','halloween-quotes') ?> &raquo;" />
    </div>
    </form><?php
	
}

?>