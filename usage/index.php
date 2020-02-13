<?php

require_once '../src/class-classes-mapper.php';

$paths = array(
	__DIR__ . '/test',
);

$options = array(
	'parse_flat'         => true,
	'file_extensions'    => array( 'txt', 'php' ),
	'excluded_paths'     => array(
		'/var/www/my-files/php-class-mapper/test/a',
	),
	'excluded_folders'   => array(
		'/var/www/my-files/php-class-mapper/test/',
	),
	'excluded_files'     => array(
		'/var/www/my-files/php-class-mapper/test/a/a1/Test3.php'
	),
	'map_as_relative_to' => __DIR__.'/../..',
);

$mapper      = new cm\Classes_Mapper( $paths, $options );
$classes_map = $mapper->process()->get_result_as_array();

$mapper->export_result_in_file( __DIR__ . '/exported-map.php' );
$mapper->export_result_in_json_file( __DIR__ . '/exported-map.json' );