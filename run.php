<?php

date_default_timezone_set( 'UTC' );

$output = shell_exec( 'git log -1' );
echo shell_exec( 'git checkout -f master' );

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
	$cache = dirname( __FILE__ ) . 'envato-market-' . $i . '.json';

	file_put_contents( $cache, $json );
}

return;

$gFileminPHP       = dirname( __FILE__ ) . '/googlefonts.php';

$fonts             = array();

$arrContextOptions = array(
	'ssl' => array(
		'verify_peer'      => false,
		'verify_peer_name' => false,
	),
);

$key               = $argv[1];
$result            = json_decode( file_get_contents( "https://www.googleapis.com/webfonts/v1/webfonts?key={$key}", false, stream_context_create( $arrContextOptions ) ) );
$cd                = date( 'Y-m-d h:i:s:a' );

foreach ( $result->items as $font ) {
	$fonts[ $font->family ] = array(
		'variants' => getVariants( $font->variants ),
		'subsets'  => getSubsets( $font->subsets ),
	);
}

ksort( $fonts );
$data = json_encode( $fonts );

echo "Saving JSON File\n\n";
file_put_contents( $gFile, $data );

echo "Saving PHP\n\n";
$code = <<<PHP
<?php
// Last Updated : $cd
defined( 'ABSPATH' ) || exit; 
return json_decode( '$data', true );
PHP;
file_put_contents( $gFileminPHP, $code );
