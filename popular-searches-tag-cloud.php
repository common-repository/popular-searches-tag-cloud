<?php
/*
Plugin Name: Popular searches tag cloud
Plugin URI: http://www.josellinares.com/wordpress-plugins/
Description: Create a Tag Cloud with the searches your users are performing on your internal search engine. IMPORTANT! To make it work you need to install and activate first the <a href="http://wordpress.org/extend/plugins/search-meter/" target="_blank">Search Meter</a> plugin.
Version: 1.0
Author: Jose Llinares
Author URI: http://www.josellinares.com
*/

//activate plugin WP function

//Checking search meter dependencies
global $wpdb, $table_prefix;
$sql = "SHOW TABLES LIKE '{$table_prefix}searchmeter_recent'";
$results = $wpdb->get_results($sql);
if (!$wpdb->num_rows )
{
	die( '<p>This plugin will not work unless <a href="http://wordpress.org/extend/plugins/search-meter/" target="_blank">Search Meter plugin</a> is installed and activated.</p>' );
}


register_activation_hook( __FILE__, 'initializeSearchTagCloud');

//activat plugin WP function
register_deactivation_hook( __FILE__, 'deactivateSearchTagCloud');

//set initial values when the plugin is activated
function initializeSearchTagCloud()
{
	$search_tag_cloud=new searchTagCloud();
	$search_tag_cloud->initializeSearchTagCloud();
}

//delete DB options when the plugin is activated
function deactivateSearchTagCloud() {
	delete_option("searchTagCloudOption");
}

class searchTagCloud
{
	public $widgetText;
	public $numberSearches;
	public $max_size;
	public $min_size;
	public $days_to_display;
	var $error;

	//constuctor function
	function __construct()
	{
		$this->min_size=12;
		$this->max_size=32;
		$this->widgetText = 'What people is searching?';
		$this->total_tags=10;
		$this->show_author_credit=0;
		$this->days_to_display=30;
		//the size of the tag cloud is missed
	}
	
	//initialize options
	//size of the smallest tag
	//maximum size of the biggest tag
	//Personalized text for the tag cloud
	//how many links to display
	function initializeSearchTagCloud()
	{
		global $wpdb, $table_prefix;
		$wpdb->query("ALTER TABLE `{$table_prefix}searchmeter_recent` ADD COLUMN visible INT( 1 ) NOT NULL DEFAULT '1'");
		
		$initializeOptions = array(
		"min_size"      => $this->min_size,
		"max_size"     => $this->max_size,
		"total_tags"     => $this->total_tags,
		"widgetText" => $this->widgetText,
		"days_to_display" => $this->days_to_display,
		"show_author_credit" => $this->show_author_credit,
		);
		add_option("searchTagCloudOption", $initializeOptions, '', 'yes');	
		//select recent searched terms
	}	

	//get DB options for the Search Tag Cloud
	function getSearchTagCloudOptions()
	{
		$myOptions = get_option('searchTagCloudOption');
		$this->min_size=$myOptions['min_size'];
		$this->max_size=$myOptions['max_size'];
		$this->widgetText=$myOptions['widgetText'];
		$this->total_tags=$myOptions['total_tags'];	
		$this->days_to_display=$myOptions['days_to_display'];	
		$this->show_author_credit=$myOptions['show_author_credit'];
	}

	//set Search Tag Cloud class values
	function setSearchTagCloudValues($min_size,$max_size,$total_tags,$widgetText,$show_author_credit,$days_to_display)
	{
		$this->min_size=$min_size;
		$this->max_size=$max_size;
		$this->widgetText=$widgetText;
		$this->total_tags=$total_tags;	
		$this->show_author_credit=$show_author_credit;
		$this->days_to_display=$days_to_display;
	}
	
	//update Search Tag Cloud class values and DB options
	function updateSearchTagCloud($array_post)
	{		
		global $wpdb, $table_prefix;
		
		$this->setSearchTagCloudValues($array_post['min_size'],$array_post['max_size'],$array_post['total_tags'],$array_post['widgetText'],$array_post['show_author_credit'],$array_post['days_to_display']);
		
		//set the new options in the database
		update_option("searchTagCloudOption", $array_post, '', 'yes');
		
		//I set all to visible
		$wpdb->query("UPDATE `{$table_prefix}searchmeter_recent` SET visible=1");
			
		if(is_array($array_post['checkbox_visible']))
		{
			foreach($array_post['checkbox_visible'] as $index=>$value)
				$wpdb->query("UPDATE `{$table_prefix}searchmeter_recent` SET visible=0 WHERE terms = '{$value}'");	
		}
		return __("Options Saved Correctly");
	}

	//Function to select from search meter tables in the database the most common searches. 
	function select_searches_for_tagcloud() 
	{
		// List the most recent successful searches.
		global $wpdb, $table_prefix;
		$this->getSearchTagCloudOptions();
		$count = intval($this->total_tags);
		
		//first I need to know how many invisible tags we have
		
		//select terms, COUNT( * ) AS total FROM `wp_searchmeter_recent` WHERE datetime>='2010-07-05' GROUP BY `terms` LIMIT 0,15
		
		//$datebeginning = date()-días
		$datebeginning  = date('Y-m-d', mktime(0, 0, 0, date("m"),date("d")-$this->days_to_display, date("Y")));

		$tags = $wpdb->get_results(
		//select recent searched terms
			" SELECT terms, visible, COUNT( * ) AS total
				FROM `{$table_prefix}searchmeter_recent`
				WHERE datetime>='{$datebeginning}' AND
				hits>0 AND
				visible=1
				GROUP BY `terms`
				LIMIT {$count}");
		
		return $tags;	
	}
	
	function selectPopularSearchesforAdmin()
	{
		// List the most recent successful searches.
		global $wpdb, $table_prefix;
		$this->getSearchTagCloudOptions();
		$count = intval($this->total_tags);
		
		//first I need to know how many invisible tags we have
		
		//select terms, COUNT( * ) AS total FROM `wp_searchmeter_recent` WHERE datetime>='2010-07-05' GROUP BY `terms` LIMIT 0,15
		
		//$datebeginning = date()-días
		$datebeginning  = date('Y-m-d', mktime(0, 0, 0, date("m"),date("d")-$this->days_to_display, date("Y")));
				
		$invisible_tags = $wpdb->get_results(
			" SELECT terms, COUNT( * ) AS total
				FROM `{$table_prefix}searchmeter_recent`
				WHERE visible=1 AND
				`datetime`>='{$datebeginning}' AND
				hits>0
				GROUP BY `terms`
				LIMIT {$count}");
		
		// I have to show the tags plus the invisible ones
		$count = $count + count($invisible_tags);

		$tags = $wpdb->get_results(
		//select recent searched terms
			" SELECT terms, visible, COUNT( * ) AS total
				FROM `{$table_prefix}searchmeter_recent`
				WHERE datetime>='{$datebeginning}' AND
				hits>0
				GROUP BY `terms`
				LIMIT {$count}");
		
		return $tags;		
	}	

	//function that creates the tag cloud and prints it.
	function popular_searches_tag_cloud($args)
	{
			$results=$this->select_searches_for_tagcloud();
			
			if(count($results)>0)
			{
				foreach($results as $index)
				{
					$array_end[$index->terms]=$index->total;
				}
				
				arsort($array_end);
	
				// largest and smallest array values
				$max_qty = max(array_values($array_end));
				$min_qty = min(array_values($array_end));
			   
				// find the range of values
				$spread = $max_qty - $min_qty;
				if ($spread == 0) { // we don't want to divide by zero
						$spread = 1;
				}
			   
				// set the font-size increment
				$step = ($this->max_size - $this->min_size) / ($spread);
			   
				//set the counter for the loop at 0
				$counter=0;
			   
				// loop through the tag array
				if(count($array_end)>0)
				{
					$html='<div class="search-tag-cloud">';
					$html.='<h2>'.$this->widgetText.'</h2>';
					foreach ($array_end as $key => $value) 
					{
						if($counter<=$this->total_tags)
						{
							$counter++;
							// calculate font-size
							// find the $value in excess of $min_qty
							// multiply by the font-size increment ($size)
							// and add the $min_size set above
							$size = round($this->min_size + (($value - $min_qty) * $step));
				   
							$html.= '<a href="?s=' . $key . '" style="font-size: ' . $size . 'px" 
							title="' . $key . '">' . $key . '</a> ';
						}
						else
							break;		
					}
					
					if($this->show_author_credit==1)
						$html.='<div id="search-tag-cloud"><p style="text-align:right"><small>WP plugin by <a href="http://www.josellinares.com/tag/marketing-online/" title="Marketing Online" target="_blank">Marketing Online</a></small></p></div>';
						
					$html.='</div>';
					echo $html;
				}
			}
	}
}
/*end class--------------------------------*/


//setting the admin page
//create admin->settings page
//create Settings Section to configure plugin values
if (is_admin() ){ // admin actions
	add_action('set_twitter_keyword_values','set_twitter_keyword');
	add_action('admin_menu','admin_setSearchTagCloud');
	add_action('admin_init','searchTagCloudSettings' );
} else {
  // non-admin enqueues, actions, and filters
}

//adding the page in the admin section
function admin_setSearchTagCloud() {
	add_options_page('Popular Searches Tag Cloud Options', 'Popular Searches Tag Cloud', 8,__FILE__, 'searchTagCloudOptions');
}

//register form fields
function searchTagCloudSettings() { // whitelist options
	register_setting('search-tag-cloud-options', 'widgetText', 'wp_filter_nohtml_kses');
	register_setting('search-tag-cloud-options', 'max_size', 'checkValueisInt');
	register_setting('search-tag-cloud-options', 'min_size', 'checkValueisInt');
	register_setting('search-tag-cloud-options', 'total_tags', 'checkValueisInt');
	register_setting('search-tag-cloud-options', 'checkbox_visible');
	register_setting('search-tag-cloud-options', 'show_author_credit', 'checkValueisInt');
	register_setting('search-tag-cloud-options', 'days_to_display', 'checkValueisInt');
}

function searchTagCloudOptions() 
{	
	$html= '<div class="wrap">';
	$html= '<form method="post">';
	settings_fields('search-tag-cloud-options');
	$html.= '<h2>'. __("Popular Searches Tag Cloud Options: Manage Options").'</h2>';
	if($_POST['type-submit']=='Y')
	{
		$message=updateSearchTagCloudForm($_POST);
		if($message!='')
			$html.= '<div class="error"><p><strong>'.$message.'</strong></p></div>';
		else
			$html.= '<div class="updated"><p><strong>'.__("Options Saved").'</strong></p></div>';
		$myOptions=get_option('searchTagCloudOption');
	}
	else
		$myOptions=get_option('searchTagCloudOption');
	
	$html.= '<label for="newpost-edited-text">'.__('Set the header for the Popular Searches Tag Cloud to be visible: ').'</label>';
	$html.= '<input type="text" name="widgetText" size="40" maxlength="150" value="'.$myOptions['widgetText'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">'.__('Size of the biggest tag: ').'</label>';
	$html.= '<input type="text" name="max_size" size="10" maxlength="3" value="'.$myOptions['max_size'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">'.__('Size of the smallest tag: ').'</label>';
	$html.= '<input type="text" name="min_size" size="10" maxlength="3" value="'.$myOptions['min_size'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">'.__('Number of searches to display: ').'</label>';
	$html.= '<input type="text" name="total_tags" size="10" maxlength="3" value="'.$myOptions['total_tags'].'" /><br /><br />';
	$html.= '<label for="newpost-edited-text">'.__('You want to show searches from the last : ').'</label>';
	$html.= '<input type="text" name="days_to_display" size="10" maxlength="3" value="'.$myOptions['days_to_display'].'" /> days<br /><br />';
	$html.=getMostPopularSearchesAdmin($results, 15, false);
	$html.= '<br /><br /><label for="newpost-edited-text">'.__('Display developer credits in the Widget: ').'</label>';
	$html.= '<input type="checkbox" name="show_author_credit" value="1" ';
	
	if ($myOptions['show_author_credit']==1) 
			$html.='checked';
	
	$html.='/><br /><br />';	
	$html.= '<input type="hidden" name="type-submit" value="Y">';
	$html.= '<br><input type="submit" class="button-primary" value="'.__('Save Options').'" />';
	
	//here I need the list of all searches, order by total
	$html.= '</form>';
	$html.= '</div>';
	
	echo $html;
}

//function to show common searches to edit in the admin page. Completes the admin form.
function getMostPopularSearchesAdmin(){

	$searchcloud=new searchTagCloud();
	$results=$searchcloud->selectPopularSearchesforAdmin();
	
	if (count($results)) {
		$html='<table cellpadding="3" cellspacing="2">';
		$html.='<tbody>';
		$html.='<tr class="alternate"><th class="left">Term</th><th>Set not Visible</th>';
		if ($do_include_successes) {
			$html.='<th>Results</th>';
		}
		$html.='</tr>';
		$class= '';
		$counter=0;
		foreach ($results as $result) {
			$html.='<tr class="'.$class.'">';
			$html.='<td><a href="'.get_bloginfo('wpurl').'/wp-admin/edit.php?s='.urlencode($result->terms).'&submit=Search">'.htmlspecialchars($result->terms).'</a></td>';
	        $html.='<td align="center"><input type="checkbox" name="checkbox_visible['.$counter.']" value="'.$result->terms.'" ';
			
			if ($result->visible==0) 
				$html.='checked';
			$html.='/>';			
			$html.='</td>';
			$html.='</tr>';
			
			$class = ($class == '' ? 'alternate' : '');
			
			$counter++;
		}
		
		$html.='</tbody>';
		$html.='</table>';
   
		
	} else {
		$html.='<p>No searches recorded for this period.</p>';
	}
	return $html;
}

//This functions checks the data send by the form and calls the update the option.
function updateSearchTagCloudForm($array)
{
	$message='';
	$search_tag_cloud=new searchTagCloud();
	
	//check values before inserting into DB
	$message=checkNumbers($array['max_size']);
	$message.=checkNumbers($array['min_size']);
	$message.=checkNumbers($array['total_tags']);
	$message.=checkSearchCloudWidgetText($array['widgetText']);
	$message.=checkNumbers($array['days_to_display']);
	
	if($array['show_author_credit'])
		$message.=checkNumbers($array['show_author_credit']);
	
	if($message!='')
		return $message;	
	
	if($message=='')	
	{	
		$search_tag_cloud->updateSearchTagCloud($array);
	}
}

//checking function for the form fields functions in the admin page
function checkNumbers($number)
{	
	if(!intval( $number ))
		return __("The field maximum size and minimum size have to be numeric<br />");
	elseif($number>100)
		return __("Maximum size and minimum size have to be smaller than 100<br />");
	else
		return "";
}

//checking function for the form fields functions in the admin page
function checkSearchCloudWidgetText($widgetText)
{
	if(strlen($widgetText)>150)
	{
		return __("You are not allowed to include more than 150 characters in the Widget Text<br />");
	}	
	return "";
}

//Widgetizing the plugin functions
function setSearchTagCloudPlugin()
{
  register_sidebar_widget(__('Popular Searches Tag Cloud'), 'callSearchTagCloud'); 
  register_widget_control(__('Popular Searches Tag Cloud'), 'callSearchTagCloud', 200, 200 );
}

add_action("plugins_loaded", "setSearchTagCloudPlugin");

//function to initailize the class. Called from sidebar.php, it checks dependencies from the search meter plugin.
function callSearchTagCloud()
{
	global $wpdb, $table_prefix;
	
	$sql = "SHOW TABLES LIKE '{$table_prefix}searchmeter_recent'";
	$results = $wpdb->get_results( $sql );
	if ( ! $wpdb->num_rows )
	{
		die( '<p>This plugin will not work unless <a href="http://wordpress.org/extend/plugins/search-meter/" target="_blank">Search Meter plugin</a> is installed and activated. -- widget</p>' );
	}
	else
	{
		$search_tag_cloud=new searchTagCloud();
		$search_tag_cloud->initializeSearchTagCloud();
	}
	$searchcloud=new searchTagCloud();
	$searchcloud->popular_searches_tag_cloud($tags,$args);	
}

function setSearchTagCloudControl()
{
  echo '<p><label for="myHelloWorld-WidgetTitle">To configure options go to "Settings > Popular Searches Tag Cloud" in this admin panel</label></p>';
}
?>
