<?php

function __naswp_echo( string $msg, int $lineBreaks = 1 ) {
	echo $msg . str_repeat( PHP_EOL, $lineBreaks );
	flush();
}

function __naswp_check( bool $exp, string $msg ) {
	if ( !$exp ) {
		__naswp_echo( $msg );
		exit( 1 );
	}
}

function __naswp_check_eq( int $value, int $shouldBe, string $valName ) {
	$msg = "$valName should be $shouldBe, is $value.";
	__naswp_check( $value === $shouldBe, $msg );
}

function __naswp_check_view( array $view, int $views, int $lastUpdate, string $viewName ) {
	__naswp_check( count( $view ) === 2 && isset( $view['views'] ) && isset( $view['lastUpdate'] ), "$viewName: structure doesn't match." );
	__naswp_check( $view['views'] === $views, "$viewName: views should be $views, is " . $view['views'] );
	__naswp_check( $view['lastUpdate'] === $lastUpdate, "$viewName: lastUpdate should be " . date( 'c', $lastUpdate ) . ", is " . date( 'c', $view['lastUpdate'] ) );
}