<?php
/*
  Plugin Name: PrePost SEO 
  Plugin URI: http://www.prepostseo.com/
  Description: Best plugin to check post seo before its published. it checks Plagiarized Content, Keyword Density, Links Count, Broken Links, Images Tages, etc. <a href="https://www.prepostseo.com" target="_blank">View full features list</a>. if you have any issue regarding plugin please <a href="https://www.prepostseo.com/contact" target="_blank">contact us</a> and we will try to solve your issue ASAP.Tools used in this plugin are <a href="https://www.prepostseo.com/plagiarism-checker" target="_blank">plagiarism checker</a>, <a href="https://www.prepostseo.com/website-seo-score-checker" target="_blank">seo score checker</a>, <a href="https://www.prepostseo.com/grammar-checker" target="_blank">grammar checker</a>, keyword density checker and <a href="https://www.prepostseo.com/broken-links-checker" target="_blank">broken links checker</a>.
  Version: 3.0
  Author: Ahmad Sattar
  Author URI: http://www.prepostseo.com/
  License: GPLv3+
*/

/*
Copyright (C) 2015 Ahmad Sattar, prepostseo.com (me AT prepostseo.com) 

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'PPS_ACTION_SITE' ) )
	define("PPS_ACTION_SITE", "https://www.prepostseo.com/");

if ( ! defined( 'PPS_VERSION' ) )
	define("PPS_VERSION", "1.5");

if(strlen(@get_option('prepostseo_acckey')) > 10)
	define("PPS_APIKEY", get_option('prepostseo_acckey'));


class PPS_WP_AhmadSeo{
	
	function __construct() {
		 add_action( 'admin_menu', array( $this, 'pps_wpa_add_menus' ) );
	}

	function  pps_wpa_add_menus()
	{
		 add_menu_page( 'PrePost SEO', 'PrePost SEO', 'manage_options', 'prepost-seo', array(
                          __CLASS__,
                         'pps_wpa_files_path'
                        ), plugins_url('imgs/logo.png', __FILE__),'14.6');

	}
	
	
	public static function pps_wpa_files_path()
	{
		include('settingPage.php');
	}
	
	
	 /*
     * Actions perform on activation of plugin
     */
    public static function pps_wpa_install() {
	
    	
		if(strlen(@get_option('prepostseo_acckey')) < 10)
		{
			@add_option('prepostseo_acckey', "");
			@update_option('prepostseo_acckey', "");
		}
		@add_option('prepostseo_version', PPS_VERSION);
		@update_option('prepostseo_version', PPS_VERSION);
		
		@add_option('prepostseo_action_site', PPS_ACTION_SITE);
		@update_option('prepostseo_action_site', PPS_ACTION_SITE);
		
		
	}
	
}
new PPS_WP_AhmadSeo();



function ravs_content_div( $content ){
	return '<prepostseo>'.$content.'</prepostseo>';
}
add_action('the_content','ravs_content_div');


function pps_main_actions()
{
	include_once("actions.php");
}
add_action( 'admin_init', 'pps_main_actions' );


function pps_wp_admin_style() {
	wp_register_style( 'pps_main_css', plugin_dir_url(__FILE__) . 'pps_style.min.css', false, '1.5' );
	wp_enqueue_style( 'pps_main_css' );
	wp_register_style( 'pps_setting_css', plugin_dir_url(__FILE__) . 'css/settings.css', false, '1.5' );
	wp_enqueue_style( 'pps_setting_css' );
}
add_action( 'admin_enqueue_scripts', 'pps_wp_admin_style' );


function pps_pre_post_seo_top() {

	global $current_screen;
	 if($current_screen->is_block_editor){
		wp_enqueue_script( 
			'pps_sidebar_block_meta', 
			plugin_dir_url(__FILE__) . 'js/pps_meta_block_sidebar.js', 
			array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components' ),
			false,
			true
		);
	 }

	wp_enqueue_script('jquery');
	wp_enqueue_script( 'pps_stopwords', plugin_dir_url(__FILE__) . 'js/stopwords.js', array('jquery'));
	wp_enqueue_script( 'pps_main_fn', plugin_dir_url(__FILE__) . 'js/fn.new.js?v=1.5', array('jquery'));

	if(strlen(@get_option('prepostseo_acckey')) < 10)
	{
		add_action( 'admin_notices', 'ppsNotification' );
	}
}
add_action('admin_head', 'pps_pre_post_seo_top');


function pps_add_html_bottom() {
    echo '<form action="'.PPS_ACTION_SITE.'grammar-checker" id="gDForm" method="post" target="_blank"></form>';
}
add_action( 'admin_footer', 'pps_add_html_bottom' );



function ppsNotification()
{
	if(strlen(@get_option('prepostseo_acckey')) < 10)
	{
		$msg = 'Prepost SEO Plugin installed successfully, To activate this plugin please enter API KEY in the <a href="'.admin_url().'admin.php?page=prepost-seo">Prepost SEO Setting Page</a> '; 
?>
	<div class="notice notice-warning is-dismissible">
		<p><?php _e( $msg, 'sample-text-domain' ); ?></p>
	</div>
<?php 
	}
}



add_action( 'admin_menu', 'pps_create_metabox_seo' );
register_activation_hook( __FILE__, array( 'PPS_WP_AhmadSeo', 'pps_wpa_install' ) );

function pps_create_metabox_seo()
{
	$post_types = get_post_types();
	foreach($post_types as $type){
		add_meta_box( 'pps-meta-box', '<b>PrePost SEO</b> : Seo status of this post', 'pps_seobox_design', $type, 'normal', 'high' );
	}
}




function pps_seobox_design()
{
	if(@$_GET['action'] == 'edit' and !empty(@$_GET['post']))
	{
		$ppsPostMeta = get_metadata('post', $_GET['post'], "pps_post_meta", true);
		if(!empty($ppsPostMeta))
		{
			$decoded = json_decode($ppsPostMeta);
			$ppsLastPlag = $decoded->plag;
			$ppsLastDate = date("d M, Y", $decoded->time);
			if($ppsLastPlag > 40)
			{
				$ppsLastPlag = $ppsLastPlag.'% (<span class="red">warning</span>)';
			} else {
				$ppsLastPlag = $ppsLastPlag.'% <span class="green">Passed</span>';
			}
			
			if($decoded->plag == 0 && $decoded->plag != null){
				$un = 100;
			}else{
				$un = 100-$decoded->plag;
			}
		}
	}
?>

<div id="sba_results" class="wrap pps-metabox-content">

	<span id="currentLang" style="display:none;"><?php echo get_locale(); ?></span>
	<span id="pluginDir" style="display:none;"><?php echo plugin_dir_url(__FILE__); ?></span>
	<span id="ppsMainAccKey" style="display:none;"><?php echo @get_option("prepostseo_acckey"); ?></span>
	<span id="ppsPluginVersion" style="display:none;"><?php echo @get_option("prepostseo_version"); ?></span>
	<span id="ppsAdminURL" style="display:none;"><?php echo get_admin_url(); ?></span>
	<?php if(!empty($ppsPostMeta)): ?>
	<span id="ppsLastPlag" style="display:none;"><?php echo @$ppsLastPlag; ?></span>
	<span id="ppsLastDate" style="display:none;"><?php echo @$ppsLastDate; ?></span>
	<?php endif; ?>
	<?php if(isset($_GET['post']) && !empty($_GET['post'])){ ?>
	<span id="ppsPostID" style="display:none;"><?php echo @$_GET['post']; ?></span>
	<?php } ?>

  	<span class="currentStatus">
        <img src="<?php echo plugin_dir_url(__FILE__); ?>imgs/loading3.gif" id="statusImg" />
        <span id="cStats"></span>
    </span>
    <span id="alerts"></span>
    <span id="pluginStatus"></span>


	<span id="contentDetails" style="display:none;">	

		<div class="nav-tab-wrapper pps-tab-wrap">
			<a class="nav-tab tablinks nav-tab-active" onclick="readtabs(event, 'pps-seo', 'pps-tabcontent', 'tablinks' ,'nav-tab-active')"><span class="dashicons dashicons-chart-pie seo-tab-icon" ></span> SEO</a>
			<a class="nav-tab tablinks" onclick="readtabs(event, 'pps-readability', 'pps-tabcontent', 'tablinks' ,'nav-tab-active')"><span class="dashicons dashicons-smiley readability-tab-icon" ></span>  Readability</a>
			<a class="nav-tab tablinks" onclick="readtabs(event, 'pps-plagiarism-report', 'pps-tabcontent', 'tablinks','nav-tab-active')"></span> <span class="dashicons dashicons-media-text plagiarism-tab-icon"></span>  Plagiarism Report</a>
		</div>

		<div id="pps-seo" class="pps-tabcontent" style="display: inline-block;">

			<span class="sec_heading">SEO Score</span>

			<span class="row pps-p16">
				<table>
					<tr>
						<td width="800" valign="top">

							<span style="margin-top:30px; float:left;">
								<span class="bar_btn">Passed:</span>
								<span class="outer_bar">
									<span class="inner_green" id="greenBar" start="0" style="width:0%;"></span>
								</span>
							</span>
							<br><br>
							<span style="margin-top:17px; float:left;">
								<span class="bar_btn">To Improve:</span>
								<span class="outer_bar">
									<span class="inner_yellow" id="yellowBar" start="0" style="width:0%;"></span>
								</span>
							</span>
							<br><br>
							<span style="margin-top:17px; float:left;">
								<span class="bar_btn">Error:</span>
								<span class="outer_bar">
									<span class="inner_red" id="redBar" start="0" style="width:0%;"></span>
								</span>
							</span>
						</td>
						<td width="200">
							<div id="pbar" class="progress-pie-chart" data-percent="0">
								<div class="ppc-progress">
									<div class="ppc-progress-fill" style="transform: rotate(0deg);"></div>
								</div>
								<div class="ppc-percents">
									<div class="pcc-percents-wrapper">
										<span class="score">0</span>

									</div>
								</div>
							</div>
						</td>
					</tr>
				</table>
			</span>

			<div class="pps-acc-content-wrapper">
				<div class="pps-acc-collapsible-container">

					<h2 class="pps-collapsible-header ">
						<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-search"></span>  STATUS <span class="toggleable-container-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
					</h2>

					<div class="pps-acc-content">
						<span class="content_staus_box tabsContent" id="contentStatus" style="display:none; width:100%;"></span>
					</div>
				</div>
			</div>

			<div class="pps-acc-content-wrapper">
				<div class="pps-acc-collapsible-container">

					<h2 class="pps-collapsible-header ">
						<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-chart-bar"></span> Improvement <span class="toggleable-container-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
					</h2>

					<div class="pps-acc-content">
						<span id="suggestions">
							<span class="improvements"></span>
						</span>
					</div>
				</div>
			</div>
			

			<div class="pps-acc-content-wrapper">
				<div class="pps-acc-collapsible-container">

					<h2 class="pps-collapsible-header ">
						<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-share"></span>  LINKS STATUS <span class="toggleable-container-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
					</h2>

					<div class="pps-acc-content">
						<span class="content_staus_box tabsContent" id="linksStatus" style="display:none;  width:100%;"></span>
					</div>
				</div>
			</div>

			<div class="pps-acc-content-wrapper">
				<div class="pps-acc-collapsible-container">

					<h2 class="pps-collapsible-header ">
						<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-editor-spellcheck"></span>  GRAMMAR CHECKER <span class="toggleable-container-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
					</h2>

					<div class="pps-acc-content">
						<span class="content_staus_box tabsContent" id="grammarStatus" style="display:none;  width:100%;"></span>
					</div>
				</div>
			</div>


			

		</div>

		<div id="pps-readability" class="pps-tabcontent">
			<span class="content_staus_box tabsContent" id="densityStatus" style="display:none;  width:100%;">
				
					<!-- TOP WORD DENSITY -->
					<div class="pps-acc-content-wrapper" >
						<div class="pps-acc-collapsible-container">

							<h2 class="pps-collapsible-header active">
								<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-search"></span>   TOP WORD DENSITY <span class="toggleable-container-icon dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span></button>
							</h2>

							<div class="pps-acc-content active" style="max-height: 300px">
								<div id="densityResults"></div>
								<table id="densityTable" class="table table-bordered" width="100%">
									<tbody>
										<tr >
											<td style="display: none;">
												<strong>TOP:</strong> <input class="topwords" id="number_of_top_keywords_value"
													value="3" size="4" type="number"
													style="width:57px;">
											</td>
											<td>
												<div class="tab" style="padding:4px 0px;display: flex;">
													<button class="_prepost_read_tablinks  "
														onclick="readtabs(event, 'keyword_density-tab-1', '_prepost_read_tabcontent', '_prepost_read_tablinks')">1 Word</button>
													<button class="_prepost_read_tablinks  "
														onclick="readtabs(event, 'keyword_density-tab-2', '_prepost_read_tabcontent', '_prepost_read_tablinks')">2
														Word</button>
													<button class="_prepost_read_tablinks  active"  id="_prepost_defaultOpenr"
														onclick="readtabs(event, 'keyword_density-tab-3', '_prepost_read_tabcontent', '_prepost_read_tablinks')">3
														Word</button>
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="2">
					
												<div class="keyword_density_content">
													<div id="keyword_density-tab-1"
														class="_prepost_read_tabcontent col-md-12 pn" style="display: none;">
														<div id="keyword_density-tab-1-table">
															<div class="col-md-12 w_tbody text-left">
																<div class="col-md-6 col-xs-6 br-right p15"></div>
																<div class="col-md-6 col-xs-6 p15"></div>
															</div>
															<div class="col-md-12 w_tbody text-left">
																<div class="col-md-6 col-xs-6 br-right p15"></div>
																<div class="col-md-6 col-xs-6 p15"></div>
															</div>
															<div class="clear"></div>
														</div>
													</div>
													<div id="keyword_density-tab-2"
														class="_prepost_read_tabcontent col-md-12 pn" style="display: none;">
														<div id="keyword_density-tab-2-table">
															<div class="col-md-12 w_tbody text-left">
																<div class="col-md-6 col-xs-8 br-right p15"></div>
																<div class="col-md-6 col-xs-4 p15"></div>
															</div>
															<div class="clear"></div>
														</div>
													</div>
													<div id="keyword_density-tab-3"
														class="_prepost_read_tabcontent col-md-12 pn" style="display: block;">
														<div id="keyword_density-tab-3-table">
															<div class="clear"></div>
														</div>
													</div>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div id="readabilityResults"></div>

					<!-- Grade Levels & Readability Index -->
					<div class="pps-acc-content-wrapper">
						<div class="pps-acc-collapsible-container">

							<h2 class="pps-collapsible-header ">
								<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-chart-bar"></span>  Grade Levels & Readability Index <span class="toggleable-container-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
							</h2>

							<div class="pps-acc-content" id="readingLevelsTable">
								<!-- <table class="table table-bordered densityStatsTable">
									<tbody>
										<tr>
											<td  width="30%">Flesch Kincaid Reading Ease</td>
											<td id="flesch_reading_ease_score"  width="30%" class="text-center"></td>
										</tr>
										<tr>
											<td >Flesch Kincaid Grade Level</td>
											<td id="flesch_kincaid_grade_level"  class="text-center"></td>
										</tr>
										<tr>
											<td>Coleman Liau Index</td>
											<td id="coleman_liau_index"  class="text-center"></td>
										</tr>
										<tr>
											<td >Automated Readability Index</td>
											<td id="automated_readability_index"  class="text-center"></td>
										</tr>
						
										<tr >
											<td ><strong>Average Index</strong></td>
											<td id="avarage_index"  class="text-center"></td>
										</tr>
									</tbody>
								</table> -->

								<table class="table table-bordered densityStatsTable">
									<tbody>
										<tr style="font-weight:700;background: #fcf8e3;">
											<td width="15%">Score</td>
											<td width="15%">School Level</td>
											<td width="15%">Ease Level</td>
											<td width="55%">Details</td>
										</tr>
										<tr data-pps-ease="100.0-90.0">
											<td>100.0 — 90.0</td>
											<td>5th grade</td>
											<td>Very Easy</td>
											<td>Its very easy to read. An average 11-year-old student can easily understand it</td>
										</tr>
										<tr data-pps-ease="90.0-80.0">
											<td>90.0 — 80.0</td>
											<td>6th grade</td>
											<td>Easy</td>
											<td>Easy to read, Conversational English for consumers.</td>
										</tr>
										<tr data-pps-ease="80.0-70.0">
											<td>80.0 — 70.0</td>
											<td>7th grade</td>
											<td>Fair Easy</td>
											<td>Fair Easy to read and undertand english.</td>
										</tr>
										<tr data-pps-ease="70.0-60.0">
											<td>70.0 — 60.0</td>
											<td>8th &amp; 9th grade</td>
											<td>Plain English</td>
											<td>Easily understood by 13 to 15-year-old students. Difficult for students younger than 13 Years </td>
										</tr>
										<tr data-pps-ease="60.0-50.0">
											<td>60.0 — 50.0</td>
											<td>10th to 12th grade</td>
											<td>Fairly Difficult</td>
											<td>Fairly Difficult to read.</td>
										</tr>
										<tr data-pps-ease="50.0-30.0">
											<td>50.0 — 30.0</td>
											<td>College</td>
											<td>Difficult</td>
											<td>Difficult to read. Best understood by College students.</td>
										</tr>
										<tr data-pps-ease="3.0-0.0">
											<td>30.0 — 0.0</td>
											<td>College graduate</td>
											<td>Very Difficult</td>
											<td>Very difficult to read. Best understood by university graduates.</td>
										</tr>
									</tbody>
								</table>
							</div>

						</div>
					</div>

					<!-- CONTENT STATS  -->
					<div class="pps-acc-content-wrapper">
						<div class="pps-acc-collapsible-container">

							<h2 class="pps-collapsible-header ">
								<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-list-view"></span>  CONTENT STATS <span class="toggleable-container-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
							</h2>

							<div class="pps-acc-content" id="textStatisticTable">
								<table class="table table-bordered densityStatsTable">
									<tbody>
										<tr>
											<td width="35%">Total Words</td>
											<td id="stat_t_w"></td>

											<td width="25%">Characters per Word</td>
											<td id="avg_c_p_w"></td>
										</tr>
										<tr>
											<td>Keywords (stop words removed)</td>
											<td  id="stat_k_s_w_r"></td>

											<td>Words per Sentence</td>
											<td  id="avg_w_p_s"></td>
										</tr>
										<tr>
											<td>Keywords / Total Words Ratio</td>
											<td  id="stat_k_t_w_r"></td>

											<td>Syllables per Word</td>
											<td  id="avg_s_p_w"></td>
										</tr>
										<tr>
											<td>Unique Words</td>
											<td  id="stat_u_w"></td>

											<td>Syllables per Sentence</td>
											<td  id="avg_s_p_s"></td>
										</tr>
										<tr>
											<td>Unique Keywords</td>
											<td  id="stat_u_k"></td>
											<td></td>
											<td></td>
										</tr>
										<tr>
											<td>Total Characters (without spaces)</td>
											<td id="stat_t_c_w_s"></td>
											<td></td>
											<td></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>


					<!-- AVERAGE  -->
					<!-- <div class="pps-acc-content-wrapper">
						<div class="pps-acc-collapsible-container">

							<h2 class="pps-collapsible-header ">
								<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-chart-area"></span>  AVERAGE <span class="toggleable-container-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
							</h2>

							<div class="pps-acc-content" id="agvTable">
								<table class="table table-bordered densityStatsTable">
									<tbody>
										<tr>
											<td width="50%">Characters per Word</td>
											<td id="avg_c_p_w"></td>
										</tr>
										<tr>
											<td>Words per Sentence</td>
											<td  id="avg_w_p_s"></td>
										</tr>
										<tr>
											<td>Syllables per Word</td>
											<td  id="avg_s_p_w"></td>
										</tr>
										<tr>
											<td>Syllables per Sentence</td>
											<td  id="avg_s_p_s"></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div> -->
					
					<!-- READING TIME -->
					<div class="pps-acc-content-wrapper">
						<div class="pps-acc-collapsible-container">

							<h2 class="pps-collapsible-header ">
								<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-clipboard"></span>  READING TIME & LENGTH<span class="toggleable-container-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
							</h2>

							<div class="pps-acc-content" id="readingTable">
								<table class="table table-bordered densityStatsTable">
									<tbody>
									
										<tr>
											<td width="25%">Estimated Reading Time</td>
											<td id="estimate_reading_time"></td>
											<td width="25%">Total Sentences</td>
											<td id="length_total_sentences"></span></td>
										</tr>
										<tr>
											<td>Estimated Speaking Time</td>
											<td id="estimate_speaking_time"></td>
											<td>Syllables</td>
											<td id="length_syllables"></span></td>
										</tr>
										<tr>
											<td colspan="4">Longest Sentence</td>
										</tr>
										<tr>
											<td colspan="4" id="length_longest_sentence"></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<!-- Content Length Statistics -->
					<!-- <div class="pps-acc-content-wrapper">
						<div class="pps-acc-collapsible-container">

							<h2 class="pps-collapsible-header ">
								<button  type="button" class="pps-toggleable-container-action" ><span class="dashicons dashicons-text-page"></span> Content Length Statistics <span class="toggleable-container-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
							</h2>

							<div class="pps-acc-content" id="lengthTable">
								<table class="table table-bordered densityStatsTable">
									<tbody>
										<tr>
											<td width="50%">Total Sentences</td>
											<td id="length_total_sentences"></span></td>
										</tr>
										<tr>
											<td>Syllables</td>
											<td id="length_syllables"></span></td>
										</tr>
										<tr>
											<td colspan="2">Longest Sentence</td>
										</tr>
										<tr>
											<td colspan="2" id="length_longest_sentence"></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div> -->
					
			</span>
		</div>


		<div id="pps-plagiarism-report" class="pps-tabcontent pps-p16">

			<span id="plagalerts"></span>
			<div class="progress-palg-bar" style="text-align:center;">
				<img src="<?php echo plugin_dir_url(__FILE__); ?>imgs/loading3.gif" id="loadGif" class="loadImg">
				<strong id="checkStatus">Checking Content...</strong>
				<br>
				<span class="resultBar">
					<span class="showBar" id="totalBar" style="width:0%;">
					</span>
					<span class="showText"><span id="totalCount">0</span>%</span>
				</span>
			</div>

			<span id="plagResult" class="tabsContent" style="display:none;">
				
				<div class="col-md-7 overallBox mp10 clear">
					<div class="col-md-12 noP">
						<div class="col-md-5 col-xs-12 noP">
							<div class="col-xs-5 noP">
								<svg class="percentBarTotal" style="margin-top:-30px;" version="1.1" id="Layer_1"
									xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
									y="0px" viewBox="0 0 1020 750" xml:space="preserve">
									<path class="st0b"
										d="M82.9,641.79c0-230.36,186.74-417.1,417.1-417.1s417.1,186.74,417.1,417.1"></path>
									<path class="st0-similar"
										d="M82.9,641.79c0-230.36,186.74-417.1,417.1-417.1s417.1,186.74,417.1,417.1"></path>
									<path class="st0"
										d="M82.9,641.79c0-230.36,186.74-417.1,417.1-417.1s417.1,186.74,417.1,417.1"></path>
									<text class="innerfont plagCount" x="50%" y="85%" text-anchor="middle"
										font-size="150">0%</text>
									<text class="lowtext " x="8%" y="98%" text-anchor="middle" font-size="70">0%</text>
									<text class="lowtext " x="50%" y="98%" text-anchor="middle"
										font-size="70">Plagiarized</text>
									<text class="lowtext " x="91%" y="98%" text-anchor="middle" font-size="70">100%</text>
								</svg>
							</div>
						</div>
						<div class="col-md-7 noP">
							<div class="col-md-12 noP  clear">
								<div class="col-xs-6 noP">
									<span style="font-size:24px;color:#ED3949;"><strong id="plagCount"
											class="plagCount">0%</strong></span>
									<br>Plagiarized<span class="hidden-xs"> Content</span>
								</div>
								<div class="col-xs-6 noP">
									<span style="font-size:24px;color:#42BB00;"><strong id="uniqueCount"
											class="uniqueCount">0%</strong></span>
									<br>Unique<span class="hidden-xs"> Content</span>
								</div>
							</div>
							<div class="col-md-12 noP plagoptions clear">
								<div class="col-xs-6 switches noP">
									<label style="cursor:no-drop;">
										<input type="checkbox" class="ios-switch green" checked="" disabled="">
										<div>
											<div></div>
										</div>
										<span class="switchVal">— <span class="exactTotal">0%</span></span>
										Exact Phrase Match
									</label>
								</div>
								<div class="col-xs-6 switches noP">
									<label>
										<input type="checkbox" class="ios-switch switchSimilar" checked>
										<div>
											<div></div>
										</div>
										<span class="switchVal">— <span class="inexactTotal">0%</span></span>
										Paraphrased Match
									</label>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="resultsBars"></div>
				<div class="plaglimitresultsBtn"></div>
				<div class="plaglimitresultsBars" style="display:none"></div>
				<div class="palOverallResult">
					<div class="_prepost_tab">
						<button class="_prepost_tablinks" onclick="preosttabs(event, 'prepost_doc_view')"
							id="_prepost_defaultOpen">Document View</button>
						<button class="_prepost_tablinks" onclick="preosttabs(event, 'prepost_sources')">Matched
							Sources</button>
						<div class="text-right" id="reportBox" style="display:none;">
							<span class="button button-primary button-large " id="genrateReport"><i class="fa fa-file-text"></i> Download Plagiarism
								Report</span>
						</div>
					</div>

					<!-- Tab content -->


					<div id="prepost_sources" class="_prepost_tabcontent">
						<table class="table table-bordered match_table" style="">
							<thead>
								<tr class="bg-warning">
									<td width="85%">Sources</td>
									<td width="15%" align="center" id="simi">Similarity</td>
								</tr>
							</thead>
							<tbody>

							</tbody>
						</table>
					</div>

					<div id="prepost_doc_view" class="_prepost_tabcontent">
						<div id="docView">

						</div>
					</div>
				</div>

			</span>
		</div>
		
	</span> <!-- #contentDetails -->

</div>

<?php	
}

function PPS_get_post_meta($post_ID) {
    $postMeta = get_post_meta($post_ID,"pps_post_meta",true);
		return $postMeta;
		//var_dump(unserialize($postMeta));
			
}
// ADD NEW COLUMN
function PPS_columns_head($defaults) {
    $defaults['pps'] = 'Prepostseo';
    return $defaults;
}
 
// SHOW THE FEATURED IMAGE
function PPS_columns_content($column_name, $post_ID) {
    if ($column_name == 'pps') {
        $content = PPS_get_post_meta($post_ID);
		$decoded = json_decode($content);
        if (!empty($decoded)) {
			if($decoded->plag >= 40){
				$pl = '<b style="color:red">'.$decoded->plag.'%</b>';
			}else{
				$pl = '<b style="color:green">'.$decoded->plag.'%</b>';
			}
			if($decoded->plag == 0 && $decoded->plag != null){
				$un = 100;
			}else{
				$un = 100-$decoded->plag;
			}
			if($decoded->score >= 70){
				$sc = '<b style="color:green">'.$decoded->score.'</b>';
			}elseif($decoded->score >= 60){
				$sc = '<b style="color:orange">'.$decoded->score.'</b>';
			}else{
				$sc = '<b style="color:red">'.$decoded->score.'</b>';
			}
			echo 'Plagiarism: '.$pl.' <br> (<span class="details_single_pps" style="display:none;"><input type="hidden" name="ignore" id="rIgnore" value="'.$decoded->ignoreUrl.'" /><input type="hidden" name="unique" id="rUnique" value="'.$un.'" /><input type="hidden" name="plagiarized" id="rPlag" value="'.$decoded->plag.'" /><textarea name="content" id="rContent">'.$decoded->content.'</textarea><textarea name="doc_view" id="rView">'.$decoded->docView.'</textarea></span><span class="genrateReportAllPost"><b>Plagiarism Report</b></span>)<br>SEO Score: <b>'.$sc.'</b>/<sub>100</sub>';
        }
        else {
            // NO FEATURED IMAGE, SHOW THE DEFAULT ONE
            echo 'Not Analyze Yet!';
        }
    }
}
add_filter('manage_posts_columns', 'PPS_columns_head');
add_filter('manage_pages_columns', 'PPS_columns_head');
add_action('manage_posts_custom_column', 'PPS_columns_content', 10, 2);
add_action('manage_pages_custom_column', 'PPS_columns_content', 10, 2);