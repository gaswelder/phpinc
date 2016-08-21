<?php
/*
 * Replaces all "require" commands with the contents of the corresponding
 * files and prints the result to STDOUT.
 */

date_default_timezone_set( "UTC" );
main( $argv );

function usage() {
	fwrite(STDERR, "Usage: phpinc [-l <include search path>]... <main PHP file>\n");
	return 1;
}

function main( $args )
{
	array_shift( $args );
	$search_paths = array();

	while( !empty( $args ) && $args[0][0] == '-' ) {
		$arg = array_shift( $args );
		switch( $arg ) {
			case "-l":
				$arg = array_shift( $args );
				if( !$arg ) {
					return usage();
				}
				$search_paths[] = $arg;
				break;
			default:
				return usage();
		}
	}

	$search_paths[] = '.';

	if( count( $args ) != 1 ) {
		return usage();
	}

	$path = realpath( $args[0] );
	if( !$path ) {
		fwrite( STDERR, "Can't find file '$args[0]'\n" );
		exit(1);
	}

	fwrite( STDOUT, "<?php\n\n" );
	fwrite( STDOUT, "/*
 * Compilation time: ".date( "r" ) . "
 */
" );
	compile( $path, $search_paths );
	fwrite( STDOUT, "\n\n?>\n" );
	return 0;
}

function compile( $path, $search_paths )
{
	echo "?>\n";
	$lines = file( $path );
	foreach( $lines as $line )
	{
		$req = require_path( $line );
		if( !$req ) {
			echo $line;
			continue;
		}

		$req_path = find_file( $req, $search_paths, $path );
		if( !$req_path ) {
			fwrite( STDERR, "Could not find file '$req'\n" );
			echo $line;
			continue;
		}

		compile( $req_path, $search_paths );
	}
	echo "<?php\n";
}

function find_file( $name, $search_paths, $except ) {
	foreach( $search_paths as $dir ) {
		$path = realpath( "$dir/$name" );
		if( $path && $path != $except ) return $path;
	}
	return null;
}

function require_path( $line )
{
	$line = trim( $line );
	if( strpos( $line, "require " ) !== 0 ) return null;
	if( substr( $line, -1 ) != ";" ) return null;
	$line = substr( $line, strlen( "require " ), -1 );
	if( strlen( $line ) < 2 ) return null;
	$q = $line[0];
	if( $q != $line[strlen($line)-1] || ($q != '"' && $q != "'") ) {
		return null;
	}
	return substr( $line, 1, -1 );
}
?>
