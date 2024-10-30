=== iCal for WP Calendar ===
Contributors: barclay_reg
Tags: ical, wp calendar, ical feed, feed, icalendar, calendar, events, event, category
Requires at least: 3.0
Tested up to: 4.6.1
Stable tag: 1.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An extension for the Wordpress plugin WP Calendar, which generates iCal / RFC5545 / RFC2445 conform files.

== Description ==

Creates an iCal feed with [WP Calendar](http://wordpress.org/extend/plugins/wp-calendar/) events. 
The generated file contains iCal / RFC5545 / RFC2445 conform data, which can be imported in several Calendar applications like Outlook, iCal, Google Calendar.

WP Calendar 1.5.x and also older versions (like 1.4.x) are supported.

Based on [iCal for Events Calendar](http://wordpress.org/extend/plugins/ical-for-events-calendar/). 

Feed will be at http://your-web-address/?wp-calendar-ical

== Installation ==

1. Unzip in your plugins directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. add the link to your template or use following code to generate the URL: `echo ical_wp_calendar_getIcalUrl();` 

== Frequently Asked Questions ==

= Where is the feed located? =

At 

* http://your-web-address/?wp-calendar-ical OR
* http://your-web-address/?wp-calendar-ical&category=4 OR
* http://your-web-address/?wp-calendar-ical&event_id=25


= How can I add the iCal URL to my website =

Place the following code to your theme 
`echo ical_wp_calendar_getIcalUrl();`
or use a widget like the [Text Widget](http://en.support.wordpress.com/widgets/text-widget/)
to your sidebar and place a code like

`<ul class="feed-list">
  <li><a href="http://your-web-address/?wp-calendar-ical">All Events (iCal)</a></li>
</ul>`

= How can the list of feeds be limited? =

The list can be limited in two ways. By a single event id (like given by the placeholder \{event_id\} ) or by adding 'category=<id of category>' to the feed url, you can limit to events, which are asssigned to the (post) category. The feed url will then look like

http://your-web-address/?wp-calendar-ical&category=1
http://your-web-address/?wp-calendar-ical&event_id=4

== Changelog ==

= Version 1.5.1 - 07.10.2013 =
* add query parameter 'event_id' to select single events into an ical feed

= Version 1.5.0 - 16.06.2013 =
* added compatibilty for WP Calendar 1.5.x
* link of ical feed is printed in the options page

= Version 1.1.0 - 05.01.2012 =
* added check if WP Calendar is activated
* added query parameter 'category' to limit events in the feed

= Version 1.0.3 - 30.08.2011 = 
* BUGFIX: querying posts and pages works again - no normal content could be displayed up to now

= Version 1.0.2 - 30.08.2011 = 
* overwrite default amount of printed events: always print all events, ignoring the setting in WP Calendar

= Version 1.0.1 - 30.08.2011 = 
* added backward compatibility to PHP version 4.2 (== minimum requirement for WP < 3.2) in UTC time calculation

= Version 1.0.0 - 24.08.2011 = 
* generate valid iCal file with events from WP Calendar, incl. location, URL to a connected post (if available) and allDay events
* admin page for the number of past months to include
* debug flag for easy debugging of the resulting iCal file in the browser itself (file can also be validated using http://icalvalid.cloudapp.net/)

== ToDo ==

* Add option, to choose the datemode, when queried the events
* add option, to choose the time method in iCal: UTC (default now), floating or th timezome, which is specified in wordpress itself 
 