<?php /** @noinspection ForgottenDebugOutputInspection */
echo '<h1>Parse books.xsl</h1><pre>';

require_once __DIR__.'/../src/SimpleXLS.php';

if ( $xls = SimpleXLS::parse('SAEOI051.xls') ) {
	print_r( $xls->rows([1]) );
} else {
	echo SimpleXLS::parseError();
}
echo '<pre>';
?>