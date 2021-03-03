# My favorite usage
```
<?php

require_once 'class-classes-mapper.php';

$paths = array(
	__DIR__ . '/..',
);

$options = array(
	'parse_flat'         => false,
	'file_extensions'    => array( 'php' ),
	'excluded_paths'     => array(
		__DIR__ . '/../libs',
	),
	'excluded_folders'   => array(),
	'excluded_files'     => array(),
	'map_as_absolute_to' => __DIR__.'/../../',
);

$mapper = new cm\Classes_Mapper( $paths, $options );
$mapper->process()->export_result_in_file( __DIR__ . '/exported-map.php' );
```

# PHP classes mapper

**required version of PHP >= 7.0**

Version 1.2.2

### Code usage

Download code, open terminal and set current working directory to **usage** folder. I prefer to use PHP CLI before deploying to production server. In order to get desired result you should change index.php file to work with your project. Standing on usage folder execute `php -f index.php`. Get result in separate file.

### Step by step:
1. cd your-project-page/php-class-mapper/usage
2. `php -f index.php`
3. use result to apply it in your autoloader function

### Step by step:
create an option to set relative path of files to desired folder

### Result example:
```
<?php return array (
  'super\space\ClassTest' => './php-classes-mapper/usage/test/Test2.php',
  'super\space\AbstractClassTest' => './php-classes-mapper/usage/test/Test2.php',
  'super\space\InterfaceTest' => './php-classes-mapper/usage/test/Test2.php',
  'super\space\TraitTest' => './php-classes-mapper/usage/test/Test2.php',
  'super\ClassTest' => './php-classes-mapper/usage/test/Test2.php',
  'Test' => './php-classes-mapper/usage/test/Test.php',
);
```
### Simple example

```
$mapper = new cm\Classes_Mapper( $paths );
$mapper->process()->export_result_in_file( __DIR__ . '/exported-map.php' );
```

### Available options:
```
@param array $paths where to parse
 @param array $options {

@param array $paths where to parse
@param array $options {
  @type bool $parse_flat              switches parse logic from recursive to flat fetch in the folder
  
  @type array $excluded_paths         these entire paths will be excluded from parsing
  
  @type array $excluded_folders       files in this folders will be excluded from parsing
  
  @type array $excluded_files         file paths that will be omitted during the parsing
  
  @type array $file_extensions        file with this extensions will be parsed, using this option 
                                      don't forget to add 'php' - default: array( 'php' )
  
  @type string $map_as_relative_to    path to the folder from which create a relative path to files. With a help of this
                                      option you can map your classes on localhost environment and upload map to 
                                      production without remapping on production. Priority over $map_as_absolute_to. 
                                      Choose only on settings $map_as_relative_to or $map_as_absolute_to.
  
  @type string $map_as_absolute_to    path to the folder from which to create an absolute path to files.
}
```

### Advanced example:
```
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
```

Feel free to point on any issue. I will fix it in no time.
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
Copyright (c) Alex Sirochenko
https://github.com/alex-sirochenko/

MIT License https://opensource.org/licenses/MIT
