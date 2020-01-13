# PHP classes mapper

**required version of PHP >= 7.0**

Version 1.0.0

### Code usage

Download code, open terminal and set current working directory to **usage** folder. I prefer to use PHP CLI before deploying to production server. In order to get desired result you should change index.php file to work with your project. Standing on usage folder execute `php -f index.php`. Get result in separate file.

### Step by step:
1. cd your-project-page/php-class-mapper/usage
2. `php -f index.php`
3. use result to apply it in your autoloader function


### Result example:
```
<?php return array (
  'super\space\ClassTest' => '/var/www/my-files/php-class-mapper/usage/test/Test2.txt',
  'super\space\AbstractClassTest' => '/var/www/my-files/php-class-mapper/usage/test/Test2.txt',
  'super\space\InterfaceTest' => '/var/www/my-files/php-class-mapper/usage/test/Test2.txt',
  'super\space\TraitTest' => '/var/www/my-files/php-class-mapper/usage/test/Test2.txt',
  'super\ClassTest' => '/var/www/my-files/php-class-mapper/usage/test/Test2.txt',
  'super\__func' => '/var/www/my-files/php-class-mapper/usage/test/Test2.txt',
  'Test4' => '/var/www/my-files/php-class-mapper/usage/test/b/Test4.php',
  'Test3' => '/var/www/my-files/php-class-mapper/usage/test/a/a1/Test3.php',
  'Test' => '/var/www/my-files/php-class-mapper/usage/test/Test.php',
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
  @type bool $parse_flat switches parse logic from recursive to flat fetch in the folder
  @type array $excluded_paths these entire paths will be excluded from parsing
  @type array $excluded_folders files in this folders will be excluded from parsing
  @type array $excluded_files file paths that will be omitted during the parsing
  @type array $file_extensions file with this extensions will be parsed, using this option don't forget to add 'php' - default: array( 'php' )
}
```

### Advanced example:
```
require_once '../src/class-classes-mapper.php';

$paths = array(
  __DIR__ . '/test',
);

$options = array(
  'parse_flat'       => true,
  'file_extensions'  => array( 'txt', 'php' ),
  'excluded_paths'   => array(
    '/var/www/my-files/php-class-mapper/test/a',
  ),
  'excluded_folders' => array(
    '/var/www/my-files/php-class-mapper/test/',
  ),
  'excluded_files'   => array(
    '/var/www/my-files/php-class-mapper/test/a/a1/Test3.php'
  )
);

$mapper = new cm\Classes_Mapper( $paths, $options );
$mapper->process()->export_result_in_file( __DIR__ . '/exported-map.php' );
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
Copyright (c) 2020 Aleksey Sirochenko
https://github.com/alex-2077/

MIT License https://opensource.org/licenses/MIT
