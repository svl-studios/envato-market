<?php

date_default_timezone_set( 'UTC' );

$output = shell_exec( 'git log -1' );
echo shell_exec( 'git checkout -f main' );

$count_file  = dirname( __FILE__ ) . '/pages.txt';

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

$key    = $argv[1];
$token  = new Herbert\Envato\Auth\Token( $key );
$client = new Herbert\EnvatoClient($token);
$list   = $client->catalog->collection(['id' => 4201392 ,'page' => 1]);
$count  = $list->results['pagination']['pages'];

echo "Saving Page Count File\n\n";
file_put_contents( $count_file, $list->results['pagination']['pages'] );

echo "Saving Redux Item Caches\n\n";
for ( $i = 1; $i <= $count; $i ++ ) {
	$data  = $client->catalog->collection(['id' => 4201392 ,'page' => $i]);
	$json  = json_encode( $data->results );
	$cache = dirname( __FILE__ ) . '/envato-market-' . $i . '.json';
	$x = file_put_contents( $cache, $json );
}
