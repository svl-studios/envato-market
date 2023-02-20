<?php

// Set timezone to UTC.
date_default_timezone_set( 'UTC' );

// Set GitHub to main.
$output = shell_exec( 'git log -1' );
echo shell_exec( 'git checkout -f main' );

// Requite composer autoload.
require_once dirname( __FILE__ ) . '/vendor/autoload.php';

// Get secret envato token.
$key    = $argv[1];

// Init Envato API.
$token  = new Herbert\Envato\Auth\Token( $key );

// Set Envato client.
$client = new Herbert\EnvatoClient($token);

// Get first page of collection.
$list   = $client->catalog->collection(['id' => 4201392 ,'page' => 1]);

// Determine page count of collection.
$count  = $list->results['pagination']['pages'];

// Enumerate collection pages.
echo "Saving Redux Item Caches\n\n";

$all = array();
for ( $i = 1; $i <= $count; $i ++ ) {

	// Get associative array data from specified page.
	$data  = $client->catalog->collection(['id' => 4201392 ,'page' => $i]);

	// Get raw Envato data.
	$results = $data->results;

	// Push it to array.
	$all[$i] = $results;

	// Encode the array to JSON.
	$json  = json_encode( $results );

	// Save JSON to file.
	$cache = dirname( __FILE__ ) . '/envato-market-' . $i . '.json';

	// Write the file.
	$x = file_put_contents( $cache, $json );
}

// Create stats array.
$stats = array(
	'pages'   => $count,
	'revenue' => 0,
	'items'   => $list->results['collection']['item_count'],
	'author'  => array(),
	'sales'   => 0,
);

// Set  locale to US.
setlocale( LC_MONETARY, 'en_US' );

// Enumerate collection data.
foreach ( $all as $data ) {
	$items = $data['items'];

	// Enumerate individual items in collection.
	foreach ( $items as $k => $item ) {

		// Count authors.
		if ( ! isset( $stats['authors'][ $item['author_username'] ] ) ) {
			$stats['author'][ $item['author_username'] ] = 1;
		} else {
			$stats['author'][ $item['author_username'] ] ++;
		}

		// Format cost of item.
		$cost = $item['price_cents'] / 100;

		// Count revenue and sales.
		$stats['revenue'] += ( $item['number_of_sales'] * $cost );
		$stats['sales']   += $item['number_of_sales'];
	}
}

// Format total revenue.
$stats['revenue'] = '$' . number_format( $stats['revenue'] );

// Count total authors.
$stats['authors'] = count( $stats['author'] );

// Unset author array.
unset( $stats['author'] );

// Convert array to JSON.
$json = json_encode( $stats );

// Write stats.json
$stats_file = dirname( __FILE__ ) . '/stats.json';

echo "Saving Current Stats\n\n";
file_put_contents( $stats_file, $json );
