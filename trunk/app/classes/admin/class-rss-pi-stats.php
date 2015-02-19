<?php
	
/*
 * Calculates and shows graphical stats
 *
 * @author Pramod Jodhani<mrpramodjodhani@gmail.com>
 */

if(!class_exists("Rss_pi_stats")) {

	class Rss_pi_stats {


		function show_charts() {

			$feeds = get_option("rss_pi_feeds" , array());

			$oldest_post = $this->get_the_oldest_post();			
			$newest_post = $this->get_the_newest_post();			
			
			$start_time = "";
			$end_time 	= "";
			if(	
				isset($_POST["rss_filter_stats"]) && 
				isset($_POST["rss_from_date"]) && 
				isset($_POST["rss_till_date"]) ) {
				
				$start_time = strtotime($_POST["rss_from_date"]);
				$end_time 	= strtotime($_POST["rss_till_date"]);

			}
			else {
				$start_time = get_the_time( "U", $oldest_post );		
				$end_time 	= get_the_time( "U", $newest_post );		
			}

			$pie_feeds_data 	= $this->get_pie_chart_data_between($start_time , $end_time);
			$line_feeds_data 	= $this->get_line_chart_data_between($start_time , $end_time);
			$bar_feeds_data 	= $this->get_bar_chart_data_between($start_time , $end_time);

			?>
			    <script type="text/javascript"
		          src="https://www.google.com/jsapi?autoload={
		            'modules':[{
		              'name':'visualization',
		              'version':'1.1',
		              'packages':['corechart' , 'bar']
		            }]
		          }"></script>
				<script type="text/javascript">
			      //google.load("visualization", "1.1", {packages:["bar"]});
			      google.setOnLoadCallback(drawChart);

			      function drawChart() {

			      	<?php 
				      	if( isset($feeds["feeds"]) && is_array($feeds["feeds"]) ) {


					      	$this->draw_line_charts_js($line_feeds_data , $feeds ); 
					      	$this->draw_pie_chart_js($pie_feeds_data , $feeds ); 
					      	$this->draw_bar_chart_js($bar_feeds_data , $feeds ); 

				      	}
			      	?>


			      }

			      </script>
				  
 				  <?php
	 				if( isset($feeds["feeds"]) && is_array($feeds["feeds"]) ) {
		 				$this->show_date_pickers();
					    echo '<div class="rss_pi_stat_div" id="rsspi_chart_line" style=""></div>';
					    echo '<div class="rss_pi_stat_div" id="rsspi_chart_pie"  style=""></div>';
					    echo '<div class="rss_pi_stat_div" id="rsspi_chart_bar"  style=""></div>';
				   	}
				   	else {
				   		echo "<div class='rss_no_data_available'>No data avaibale to be shown.</div>";
				   	}
 				  ?>
			<?php


		}

		/*
		* Prints the line chart between two dates
		* param $start_time : timestamp 
		* param $end_time : timestamp 
		*/
		function get_line_chart_data_between($start_time, $end_time ) {
			
			$dates = $this->get_all_dates_between($start_time, $end_time );
			
			$feeds = get_option("rss_pi_feeds" , array());

			$data = array();
			
			if(isset($feeds["feeds"]) && is_array($feeds["feeds"])) {

				foreach($feeds["feeds"] as $feed) {
					$feedname = $feed["name"];
					$feedurl = $feed["url"];
					foreach($dates as $date) {

						$key = date("d-m-Y", $date);
						$data[$key][$feedname] = $this->get_feedcount_for($feedurl , $date );

					}
				}
			
			}


			return $data;

		}

		function get_feedcount_for($feedurl , $date) {

			$year 	= date("Y", $date);
			$month 	= date("m", $date);
			$day 	= date("d", $date);

			$parse 	= parse_url($feedurl);
			$url 	= $parse['host'];

			$args = array(
							"date_query" => array( 
													array(
														"year" 	=> $year,
														"month" => $month,
														"day" 	=> $day,
														),
													'inclusive' => true,
													),
							'meta_query' 		=> array(
														array(
															'key'     => "rss_pi_source_url",
															'value'   => $url,
															'compare' => 'LIKE',
														),
													),
							'posts_per_page' => -1,
						);

			$posts = get_posts($args);

			return count($posts);


		}

		function get_all_dates_between($strDateFrom,$strDateTo)
		{	

		    $aryRange=array();

		    if ($strDateTo>=$strDateFrom)
		    {
		        $aryRange[] = $strDateFrom; // first entry
		        
		        while ($strDateFrom < $strDateTo)
		        {
		            $strDateFrom+=86400; // add 24 hours
		            $aryRange[] = $strDateFrom;
		        }
		    }

		    return $aryRange;
		}


		function get_the_oldest_post() {

			$args = array(
						"posts_per_page" 	=> 1,
						"order"				=> "ASC",
						"orderby"			=> "date", 
						"meta_key"			=> "rss_pi_source_url",
						);

			$post = get_posts($args);

			return $post[0];
			
		}

		
		function get_the_newest_post() {

			$args = array(
						"posts_per_page" 	=> 1,
						"order"				=> "DESC",
						"orderby"			=> "date", 
						"meta_key"			=> "rss_pi_source_url",
						);

			$post = get_posts($args);

			return $post[0];
			
		}


		function draw_line_charts_js( $feeds_data , $feeds ) {
			?>
				var data_line_chart = google.visualization.arrayToDataTable([
				<?php

					$feednames = "";

					foreach($feeds["feeds"] as $feed ) {
						$feednames .= "'".$feed["name"]."', ";
					}

					//Generating the following:
			        //['Year', 'Sales', 'Expenses'],
					echo "[ 'Date' , $feednames  ], \n";

			        //Generating the following:
			        //['2004',  1000,      400],
					foreach($feeds_data as $date=>$data) {
						echo "[ '$date' ,";
						
						$i = 1;
						foreach($data as $d) {
							
							echo "".$d.",";

							//if last data
							if(count($data) == $i) {
								echo "], \n";
							}
							$i++;

						} //foreach $data
					
					} //foreach $feeds_data
					
		        ?>
			       
			
	        ]);

	        var options_line_chart = {
	          title: 'Feeds Imported',
	          curveType: 'none',
	          legend: { position: 'bottom' }
	        };

	    	var chart = new google.visualization.LineChart(document.getElementById('rsspi_chart_line'));

			chart.draw(data_line_chart, options_line_chart);
	        <?php

		} //draw_line_charts_js

		
		function draw_pie_chart_js($pie_feeds_data , $feeds) {
			
			//$data_pie = get_pie_data();
			?>
			var data_pie_chart = google.visualization.arrayToDataTable([ 
				<?php 
					 echo "['Feed', 'Posts imported'], \n";

					foreach($pie_feeds_data as $feed=>$import_count) {
						echo "['".$feed."' , $import_count ], \n";
					}
				?>
			]);

			 var options_pie_chart = {
					          title: 'Feeds Share',
					          is3D: true,
					        };
	
			var chart = new google.visualization.PieChart(document.getElementById('rsspi_chart_pie'));
        	chart.draw(data_pie_chart, options_pie_chart);

			<?php

		} // draw_pie_chart_js
	


		function get_pie_chart_data_between($start_time , $end_time) {

			$feeds = get_option("rss_pi_feeds" , array());

			$s_year 	= date("Y", $start_time);
			$s_month 	= date("m", $start_time);
			$s_day 		= date("d", $start_time);			

			$e_year 	= date("Y", $end_time);
			$e_month 	= date("m", $end_time);
			$e_day 		= date("d", $end_time);	

			$data 		= array();
			
			//pre($feeds); exit;
			if(isset($feeds["feeds"]) && is_array($feeds["feeds"])) {

				foreach($feeds["feeds"] as $feed) {
					
					$data[$feed["name"]] = 0; 
					
					$domain = $this->get_domain($feed["url"]);
					

					$args = array(
									"date_query" => array( 
															"after" => array(
																			"year" 	=> $s_year,
																			"month" => $s_month,
																			"day" 	=> $s_day,
																			),														
															"before" => array(
																			"year" 	=> $e_year,
																			"month" => $e_month,
																			"day" 	=> $e_day,
																			),
															'inclusive' => true,

															),
									'meta_query' 		=> array(
																array(
																	'key'     => "rss_pi_source_url",
																	'value'   => $domain,
																	'compare' => 'LIKE',
																),
															),

									'posts_per_page' => -1,
								);

					$posts = get_posts($args);
					$data[$feed["name"]] = count($posts);
				}
			}

			return $data;		 

		} // get_pie_chart_data_between

		function get_domain($url) {
			$parse 	= parse_url($url);
			$url 	= $parse['host'];
			return $url; 
		}

		function get_bar_chart_data_between($start_time , $end_time) {

			$feeds = get_option("rss_pi_feeds" , array());

			$data = array(); 
			
			$dates = $this->get_all_dates_between($start_time , $end_time);

			foreach($dates as $date) {

				$year 	= date("Y", $date);
				$month 	= date("m", $date);
				$day 	= date("d", $date);	

				$args = array(
								"date_query" => array( 
														array(
																"year" 	=> $year,
																"month" => $month,
																"day" 	=> $day,
																),														
														'inclusive' => true,

														),

								"meta_key" 		 => "rss_pi_source_url",
								"posts_per_page" => -1,
							);

				$posts = get_posts($args);

				$date = date("d-m-Y", $date);

				$data[$date] = count($posts);


			}

			return $data;
		}


		function draw_bar_chart_js($bar_feeds_data , $feeds ) {
			?>
			var data_bar = google.visualization.arrayToDataTable([
				['Date', 'Posts Imported' ],
				<?php
					foreach($bar_feeds_data as $date=>$count) {
						echo "['".$date."', $count], \n";
					}
				?>
			]);

	        var bar_options = {
	          chart: {
	            title: 'Total posts imported everyday',
	            subtitle: '',
	          },
	          bars: 'vertical' // Required for Material Bar Charts.
	        };

	        var bar_chart = new google.charts.Bar(document.getElementById('rsspi_chart_bar'));

	        bar_chart.draw(data_bar, bar_options);

			<?php
		}

		function show_date_pickers() {
			?>
			<div class="rss_pi_stats_date">
				<div class="rss_filter_heading">Filter results:</div>
				<hr>
				<label>From: <input type="text" id="from_date" name="rss_from_date" value="<?php echo  (isset($_POST["rss_from_date"]))? $_POST["rss_from_date"] : "";  ?>" /> </label> 
				<label>Till: <input type="text" id="till_date" name="rss_till_date" value="<?php echo (isset($_POST["rss_till_date"]))? $_POST["rss_till_date"] : "";  ?>" /> </label> 
				<input type="submit" name="rss_filter_stats" class="button button-primary button-large " value="Filter">
				<br>
			</div>
			<?php
		}
	} // CLass Rss_pi_stats
}


?>