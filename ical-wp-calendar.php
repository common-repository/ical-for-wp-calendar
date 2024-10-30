<?php
/*
Plugin Name: iCal for WP Calendar
Plugin URI: http://wordpress.org/extend/plugins/ical-for-wp-calendar/
Description: Creates an iCal feed for WP Calendar (http://www.faebusoft.ch/webentwicklung/wpcalendar/) at http://your-web-address/?wp-calendar-ical. Based on Gary King's iCal Posts (http://www.kinggary.com/archives/build-an-ical-feed-from-your-wordpress-posts-plugin) and modifications by Jerome (http://capacity.electronest.com/ical-for-ec-event-calendar/).
Version: 1.5.1
Author: Robert Kleinschmager
Author URI: http://kleinschmager.net

---------------------------------------------------------------------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You can see a copy of GPL at <http://www.gnu.org/licenses/>
---------------------------------------------------------------------
*/


include_once(dirname(__FILE__) . "/ical-wp-calendar-admin.php");

add_action('plugins_loaded','icwpc_check_requirements');

add_action("admin_menu", "ical_wp_calendar_option_menu_init");
add_filter('query_vars', "ical_wp_calendar_filter_query_vars");

define("ICAL_WP_CALENDAR_QUERY_VARIABLE", "wp-calendar-ical");
define("ICAL_WP_CALENDAR_QUERY_PARAMETER_CATEGORY", "category");
define("ICAL_WP_CALENDAR_QUERY_PARAMETER_EVENTID", "event_id");


function ical_wp_calendar_filter_query_vars($vars)
{
	if(isset($_GET[ICAL_WP_CALENDAR_QUERY_VARIABLE]))
    	iCalFeed();
    	
	return $vars;
}

function ical_wp_calendar_getIcalUrl()
{
	return get_bloginfo('home') . "/?" . ICAL_WP_CALENDAR_QUERY_VARIABLE;
}



function stripTextFields($text)
{
	$retText = str_replace(",", "\,", $text);
	$retText = str_replace("\\", "\\\\", $retText);
	$retText = str_replace(";", "\;", $retText);
	$retText = str_replace("\N", "\\N", $retText);
	$retText = str_replace("\n", "\\n", $retText);
	
	$retText = strip_tags($retText, "<br><BR>");
	
	// try to keep line breaks out of HTML texts
	$retText = str_replace("<br>", "\n", $retText);
	$retText = str_replace("<br/>", "\n", $retText);
	$retText = str_replace("<BR>", "\n", $retText);
	$retText = str_replace("<BR/>", "\n", $retText);

	return $retText;	
}

function iCalFeed()
{
	global $wpdb;

	if (isset($_GET["debug"]))
	{
		define("DEBUG", true);
	}

	if (isset($_GET[ICAL_WP_CALENDAR_QUERY_PARAMETER_CATEGORY]))
	{
		$category_parameter = $_GET[ICAL_WP_CALENDAR_QUERY_PARAMETER_CATEGORY];
	}

	if (isset($_GET[ICAL_WP_CALENDAR_QUERY_PARAMETER_EVENTID]))
	{
		$single_event_parameter = $_GET[ICAL_WP_CALENDAR_QUERY_PARAMETER_EVENTID];
	}
	
	if ( !function_exists('fse_get_events') )
	{
		echo "Could not create iCal file, as plugin WP Calendar is not active. Contact your wordpress administrator.";
		exit;
	}
	else 
	{
		$args = array();
		
		$options = get_option("ical-wp-calendar-options");

		if (isset($single_event_parameter))
		{
			/* query for a single event */
			$singleEvent = fse_get_event($single_event_parameter);
			if ($singleEvent) 
			{
				$fsEvents = array();
				array_push($fsEvents, $singleEvent);
			} 
		} 
		else 
		{
			/* query for multiple event */
			if (!is_null($options) && !is_null($options['ical-wp-calendar-history-length-months']))
			{
				$historyLengthMonths = $options['ical-wp-calendar-history-length-months'];			
				$limitDate = strtotime("-" . $historyLengthMonths . " month");
				
				$args['datefrom'] = $limitDate;
				// $args["dateto"] = ... does not need to be defined, so all events in the future will be queried
				$args['datemode'] = 1; // == FSE_DATE_MODE_ALL
				$args['number'] = 0; // == FSE_DATE_MODE_ALL			
			}

			if (isset($category_parameter)) 
			{
				$args['categories'] = array($category_parameter);
			}
			
			// query the events from WP Calendar
			$fsEvents = fse_get_events ($args);	
		}

		
		
		$outputBody = "";
		$blogURL = get_bloginfo('home');		
		$blogName = get_bloginfo('name');
		
		foreach ($fsEvents as $event)
		{		

			$eventStartString = getEventStartDateString($event);
			$eventEndString = getEventEndDateString($event);	
			
		
			$summary = stripTextFields($event->subject);
			$description = stripTextFields($event->description);
			
			$location = $event->location;
			
			// if event has an attached post, than add the postlink here
			if ($post->postid > 0)
			{
				$link=get_permalink($post->postid);
			}
			
			$uid = $event->eventid . "@" . $blogURL;
			
			
			// assemble the body of an event
			$outputBody .= "BEGIN:VEVENT\r\n";
			$outputBody .= "UID:" . $uid . "\r\n";
			if (!empty($link))
			{	
				$outputBody .="URL:" . $link . "\r\n";
			}
			$outputBody .= "DTSTART;" . $eventStartString . "\r\n";
			$outputBody .= "DTEND;" . $eventEndString . "\r\n";
			if (!empty($location))
			{
				$outputBody .= "LOCATION:" . $location . "\r\n";
			}
			$outputBody .= "SUMMARY:" . $summary . "\r\n";
			
			if (empty($link))
			{
				// no link out
				$outputBody .= "DESCRIPTION:" . $description . "\r\n";
			}
			else
			{
				// has link out
				$outputBody .= "DESCRIPTION;ALTREP=\"" . $link . "\":";
				$outputBody .= $description . "\r\n";
			}
			
			$outputBody .= "END:VEVENT\r\n";
		}	
		

		if (!defined('DEBUG'))
		{
			$name=preg_replace('/([\\,;])/','\\\\$1',get_bloginfo_rss('name'));
  			$filename=preg_replace('/[^0-9a-zA-Z]/','',$name).'.ics';

	  		header("Content-Type: text/calendar; charset=" . get_option('blog_charset'));
	  		//header("Content-Disposition: inline; filename=$filename");
	  		header("Content-Disposition: attachment; filename=$filename");
			//header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	  		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	  		header('Cache-Control: no-cache, must-revalidate, max-age=0');
	  		header('Pragma: no-cache');
	
		}
	
		$content = "BEGIN:VCALENDAR\r\n";
		$content .= "VERSION:2.0\r\n";
		$content .= "PRODID:-//" . $blogName . "//NONSGML v1.0//EN\r\n";
		$content .= "X-WR-CALNAME:" . $blogName . "\r\n";
		$content .= "X-ORIGINAL-URL:" . $blogURL . "\r\n";
		$content .= "X-WR-CALDESC:Events for " . $blogName . "\r\n";
		$content .= "CALSCALE:GREGORIAN\r\n";
		//$content .= "METHOD:PUBLISH\r\n";
		$content .= $outputBody;
		$content .= "END:VCALENDAR";
	
		echo $content;
	
		if (defined('DEBUG'))
		{
			echo "\n" . $queryEvents . "\n";	
			echo $eventStart . "\n";
		}
	
		exit;
		
	}//end else if function exists fse_get_events
}

function getEventStartDateString($event) {

	if (version_compare(getFsCalendarVersion(), '1.5', '<')) {
		//its wp_calendar 1.4.x
		$timestamp = $event->tsfrom;

		// utc correction, if time matters
		if ($event->allday == false) {
			$timestamp = datetimeToUTC_PHP52($timestamp);
		}
	} else {		
		if ($event->allday == false) { 
			$timestamp = convertToTimestampWithTimezoneCorrection($event->from);
		} else {
			$timestamp = convertToTimestampWithoutTimezoneCorrection($event->from);
		}
	}

	return convertUtcTimestampToRfc5545String($timestamp, $event->allday == false);
}

function getEventEndDateString($event) {

	if (version_compare(getFsCalendarVersion(), '1.5', '<')) {
		//its wp_calendar 1.4.x
		$timestamp = $event->tsto;

		// utc correction, if time matters
		if ($event->allday == false) {
			$timestamp = datetimeToUTC_PHP52($timestamp);
		}
	} else {		
		if ($event->allday == false) { 
			$timestamp = convertToTimestampWithTimezoneCorrection($event->to);
		} else {
			$timestamp = convertToTimestampWithoutTimezoneCorrection($event->to);
		}
	}

	return convertUtcTimestampToRfc5545String($timestamp, $event->allday == false);
}

function convertToTimestampWithTimezoneCorrection($formattedDateString) {

	// as $formattedDateString is in the wordpress timezone, but we need to UTC timestamp ...
	$gofs = get_option( 'gmt_offset' ); // get WordPress offset in hours
	$oldDefaultTimezone = date_default_timezone_get(); // get current PHP timezone
	date_default_timezone_set('Etc/GMT'.(($gofs < 0)?'+':'').-$gofs); // set the PHP timezone to match WordPress
	// parse date with wordpress timezon ein mind.		
	$timeStamp = convertToTimestampWithoutTimezoneCorrection($formattedDateString);

	date_default_timezone_set($oldDefaultTimezone); // set the PHP timezone back the way it
	
	return $timeStamp;
}

function convertToTimestampWithoutTimezoneCorrection($formattedDateString) {
	return strtotime($formattedDateString);
}

function datetimeToUTC_PHP53($date)
{
	$currentGmtOffset = get_option("gmt_offset");
	$dateObject = new DateTime();
	$dateObject->setTimestamp($date);
	
	if ($currentGmtOffset > 0) $dateObject->modify( "-". abs($currentGmtOffset) . "hours");
	if ($currentGmtOffset < 0) $dateObject->modify( "+". abs($currentGmtOffset) . "hours");
	
	return $dateObject->getTimestamp();
}

function datetimeToUTC_PHP52($timestamp)
{
	$currentGmtOffset = intval(get_option('gmt_offset')) * 3600;	
	return $timestamp - $currentGmtOffset;
}

function convertUtcTimestampToRfc5545String($timestamp, $useTime)
{
	if ($useTime == TRUE) {
		$ret = "VALUE=DATE-TIME:" . date("Ymd\THis", $timestamp);
		$ret .= "Z";
		return $ret;
	} else {
		return "VALUE=DATE:" . date("Ymd", $timestamp);
	}
}


function getFsCalendarVersion() {
	global $fsCalendar;
	return fsCalendar::$plugin_vers;
}


function icwpc_check_requirements() {
	//This works only if WP Calendar is installed (and activated) too - activation is not checked here (waiting for the WP dependency mechanism :)
	if ( !function_exists('fse_get_events') ) {
		add_action(
			'admin_notices', 
			create_function(
				'', 
				'echo \'<div id="message" class="error"><p>Plugin <strong>iCal for WP Calendar</strong> is activated but <a href="http://wordpress.org/extend/plugins/wp-calendar/">WP Calendar</a> is not installed. iCal for WP Calendar will not produce any iCal feed.</p></div>\';'
			)
		);
		return false;
	}
	
	return true;
}
?>