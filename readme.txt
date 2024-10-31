=== Search Tag Cloud ===
Contributors: Jose Llinares
Tags: search, popular searches, widget, tag cloud, SEO
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 2.7.2

Show a Tag Cloud displaying the most popular searches performed by your users in your blog.

== Description ==

Help your users at the same time you improve your SEO.

Make your users find the interesting content in your blog by showing what the peoples is searching in your blog.

Improve at the same time the indexability of your site giving to Search Engines the words the users are demanding in your site and increase your rankings and number of pages indexed.

== Installation ==

To make this plugin work you need to install first the Search Meter plugin (which is great). You can find, download and install Search Meter directly from the **Plugins** section in WordPress http://wordpress.org/extend/plugins/search-meter/

Once you have it installed download, install and activate Popular Searches Tag Cloud Plugin. You can find Popular Searches Tag Cloud directly from the **Plugins** section in WordPress or in http://www.josellinares.com/wordpress-plugins/

Remember you can set up the options at Admin -> Settings -> Popular Searches Tag Cloud.

If you just have installed the plugin it is normal you don't see the tag cloud as there are no searches... you can perform some relevant searches your own to start.

This plugin won't work if you have changed the wordpress default search system.

= Manage Popular Searches Tag Cloud =

You can personalize in the admin section;

The text that displays above the tag.

The minimum and maximum sizes of the tags.

Number of total searches you want to display in the tag cloud.

The searches you want to display in the tag cloud.

To display the author's credit.

= Widget: Popular Searches Tag Cloud =

The Popular Searches Tag Cloud widget displays the most common searches on your blog for the time you set. The searches are hyperlinked to the actual search results; readers can click the search term to show the results for that search. You can configure the title of the widget, the searches you want to display and maximum and minimum size of the tag cloud. At the same time you can edit which searches you want to hide in case they are not relevant. (you can perform some searches your own in order to have some data at the beginning)

To add these widget to your sidebar, login to WordPress Admin -> Appearance -> Widgets. Then drag the appropriate widget to the sidebar of your choice, and set the title and the number of searches to display.

Only searches with results will be displayed.

= Template Tags =

If you are using an older version of WordPress or an old theme, you may not be able to use the widgets. In any case, you can always use the Search Tag Cloud editing your template and adding this line where you want the tag cloud to appear (you can perform some searches your own in order to have some data at the beginning).

//function to initailize the class. Called from sidebar.php
function callSearchTagCloud()
{
	$searchcloud=new searchTagCloud();
	$searchcloud->popular_searches_tag_cloud($tags,$args);	
}

== Changelog ==

= 1.0 =
* Initial public release
