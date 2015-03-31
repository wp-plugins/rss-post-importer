<?php

// This include gives us all the WordPress functionality

$options = get_option('rss_pi_feeds', array());

function array2csv(array &$array) {

	if (count($array) == 0) {
		return null;
	}
	ob_start();
	$df = fopen("php://output", 'w');
	$arrayhead = array_keys(reset($array));
	$exclude_data = array('id', 'category_id', 'tags_id', 'strip_html');
	$arrayhead = array_diff($arrayhead, array('id', 'category_id', 'tags_id', 'strip_html'));

	fputcsv($df, $arrayhead);

	foreach ($array as $row) {
		unset($row['id']);
		unset($row['category_id']);
		unset($row['tags_id']);
		unset($row['strip_html']);
		fputcsv($df, $row);
	}
	fclose($df);

	return ob_get_clean();
}

function download_send_headers($filename) {

	// disable caching
	$now = gmdate("D, d M Y H:i:s");
	header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	header("Last-Modified: {$now} GMT");

	// force download  
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");

	// disposition / encoding on response body
	header("Content-Disposition: attachment;filename={$filename}");
	header("Content-Transfer-Encoding: binary");
}

if (isset($_POST['csv_download'])) {

	download_send_headers("data_export_" . date("Y-m-d") . ".csv");
	echo array2csv($options['feeds']);
	die();
}
/* echo "<pre>";

  print_r($options);
  exit; */
?>