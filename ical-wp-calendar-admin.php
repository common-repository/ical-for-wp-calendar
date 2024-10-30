<?php

include_once(dirname(__FILE__) . "/ical-wp-calendar.php");

/*
 * Wordpress admin option menu for iCal for Events Calendar plugin.
 */

function ical_wp_calendar_option_menu_init()
{
	add_options_page(
		'iCal for WP Calendar Settings',
		'iCal for WP Calendar',
		'manage_options', 'ical-wp-calenar-option', 'ical_wp_calendar_option_menu_page');

    //hock for settings registry
	add_action('admin_init', 'ical_wp_calenar_register_settings');
}


// register the necessary settings and sections
function ical_wp_calenar_register_settings()
{
	add_settings_section('main-section', 'Main Settings', 'ical_wp_calendar_main_section_description', 'ical-wp-calenar-option-main-page');	
	add_settings_field('ical-wp-calendar-history-length-months', 'History Length in Months', 'ical_wp_calenar_history_length_months_input', 'ical-wp-calenar-option-main-page', 'main-section');

	register_setting('ical-wp-calendar-options-group', 'ical-wp-calendar-options');
}


// callback, to write the description for the main section
function ical_wp_calendar_main_section_description() 
{
echo '<p>General settings for the iCal WP Calendar Plugin</p>';
}

// callback, to write the input box for the option history_length_months
function ical_wp_calenar_history_length_months_input()
{
$options = get_option('ical-wp-calendar-options');
echo "<input id='ical-wp-calendar-history-length-months' name='ical-wp-calendar-options[ical-wp-calendar-history-length-months]' class='setting-description description' type='text' value='{$options['ical-wp-calendar-history-length-months']}' />
<p class='setting-description description'>
	The number of past months to include in the iCalendar file.
	Leave blank to include all past events.
</p>";
}



// simple option page, with one section
function ical_wp_calendar_option_menu_page()
{
?>
<div class="wrap">
	<h2>iCal for WP Calendar</h2>

	<p>... creates an iCal feed with <a href="http://wordpress.org/extend/plugins/wp-calendar/">WP Calendar</a> events. Your events will be available under the following URL:</p>
	<p style="padding-left: 15px;"><strong><?php echo ical_wp_calendar_getIcalUrl(); ?></strong></p>
	
	<form action="options.php" method="post">
		<?php settings_fields('ical-wp-calendar-options-group'); ?>
		<?php do_settings_sections('ical-wp-calenar-option-main-page'); ?>

		<p class="submit"><input class="button-primary" name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
	</form>
</div>
<?php
}

// not used currently
function ical_ec_action_links($links)
{ 
	// Add a link to this plugin's settings page
	$settings_link = '<a href="options-general.php?page=ical-ec-admin.php">';
	$settings_link .= __('Settings') . '</a>';
	array_unshift($links, $settings_link);
	return $links;
}

?>