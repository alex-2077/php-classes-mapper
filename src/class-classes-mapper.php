<?php


namespace cm;

class Classes_Mapper {
	//options set through the constructor
	protected $paths = array();
	protected $parse_flat = false;
	protected $excluded_paths = array();
	protected $excluded_folders = array();
	protected $excluded_files = array();
	protected $file_extensions = array();
	protected $excluded_file_extensions = array();
	//options end
	protected $file_extensions_regex = '';
	protected $map_as_relative_to = '';
	protected $classes_map = array();

	/**
	 * Classes_Mapper constructor.
	 *
	 * @param array $paths where to parse
	 *
	 * @param array $options {
	 *
	 * @type bool $parse_flat switches parse logic from recursive to flat fetch in the folder
	 * @type array $excluded_paths these entire paths will be excluded from parsing
	 * @type array $excluded_folders files in this folders will be excluded from parsing
	 * @type array $excluded_files file paths that will be omitted during the parsing
	 * @type array $file_extensions file with this extensions will be parsed, using this option don't forget to add 'php' - default: array( 'php' )
	 * @type string $map_as_relative_to - path to the folder from which create a relative path to files. With a help of this
	 * option you can map your classes on localhost environment and upload map to production without remapping on production.
	 * }
	 *
	 */
	function __construct( array $paths, array $options = array() ) {
		$this->paths = $paths;

		if ( ! empty( $options ) ) {
			$this->set_options( $options );
		}
	}

	protected function set_options( array $options ): void {
		$options_keys = array(
			'parse_flat',
			'excluded_paths',
			'excluded_folders',
			'excluded_files',
			'file_extensions',
			'map_as_relative_to',
		);

		foreach ( $options_keys as $option_key ) {
			if ( isset( $options[ $option_key ] ) ) {
				if ( is_array( $options[ $option_key ] ) ) {
					$this->$option_key = array_filter( $options[ $option_key ], 'is_string' );
				} else {
					$this->$option_key = $options[ $option_key ];
				}
			}
		}

		$this->set_file_extensions_regex();
		$this->excluded_paths   = $this->array_realpath( $this->excluded_paths );
		$this->excluded_folders = $this->array_realpath( $this->excluded_folders );
		$this->excluded_files   = $this->array_realpath( $this->excluded_files );
	}

	protected function set_file_extensions_regex(): void {
		$extensions = 'php';
		$regex      = "/[a-z0-9_-]+\.#exts#$/i";

		if ( ! empty( $this->file_extensions ) ) {
			$extensions = '(' . implode( '|', array_map( 'trim', $this->file_extensions ) ) . ')';
		}

		$this->file_extensions_regex = str_replace( '#exts#', $extensions, $regex );
	}

	protected function array_realpath( array $paths ) {
		$real_paths = array();
		foreach ( $paths as $path ) {
			$path = realpath( $path );
			if ( ! empty( $path ) ) {
				$real_paths[] = $path;
			}
		}

		return $real_paths;
	}

	function process(): Classes_Mapper {
		foreach ( $this->paths as $path ) {
			if ( empty( $this->parse_flat ) ) {
				$iterator = new \RecursiveDirectoryIterator( $path, \FilesystemIterator::SKIP_DOTS );
				$iterator = new \RecursiveIteratorIterator( $iterator );
			} else {
				$iterator = new \DirectoryIterator( $path );
			}

			/**
			 * @var $file_info \SplFileInfo
			 */
			foreach ( $iterator as $file_info ) {
				if ( ! $this->file_has_valid_extension( $file_info->getRealPath() ) ||
				     $this->in_excluded_path( $file_info->getRealPath() ) ||
				     $this->in_excluded_folder( $file_info->getRealPath() ) ||
				     $this->is_excluded_file( $file_info->getRealPath() )
				) {
					continue;
				}

				$file_data = file_get_contents( $file_info->getRealPath() );

				if ( empty( $file_data ) ) {
					continue;
				}

				$tokens   = token_get_all( $file_data, TOKEN_PARSE );
				$entities = $this->parse_tokens( $tokens, $file_data );

				if ( ! empty( $this->map_as_relative_to ) ) {
					$file_path = $this->get_relative_path( $this->map_as_relative_to, $file_info->getRealPath() );
				} else {
					$file_path = $file_info->getRealPath();
				}

				$classes_map       = array_combine( $entities, array_fill( 0, count( $entities ), $file_path ) );
				$this->classes_map = array_merge( $this->classes_map, $classes_map );
			}
		}

		return $this;
	}

	protected function file_has_valid_extension( string $file_path ): bool {
		return (bool) preg_match( $this->file_extensions_regex, $file_path );
	}

	protected function in_excluded_path( $file_path ): bool {
		$dirname = pathinfo( $file_path, PATHINFO_DIRNAME );
		foreach ( $this->excluded_paths as $excluded_path ) {
			if ( strpos( $dirname, $excluded_path ) !== false ) {
				return true;
			}
		}

		return false;
	}

	protected function in_excluded_folder( $file_path ): bool {
		$dirname = pathinfo( $file_path, PATHINFO_DIRNAME );
		foreach ( $this->excluded_folders as $excluded_path ) {
			if ( $dirname === rtrim( $excluded_path, DIRECTORY_SEPARATOR ) ) {
				return true;
			}
		}

		return false;
	}

	protected function is_excluded_file( $file_path ): bool {
		foreach ( $this->excluded_files as $excluded_path ) {
			if ( rtrim( $file_path, DIRECTORY_SEPARATOR ) === rtrim( $excluded_path, DIRECTORY_SEPARATOR ) ) {
				return true;
			}
		}

		return false;
	}

	protected function parse_tokens( array $tokens, string $file_data ): array {
		$data              = array();
		$current_namespace = '';

		for ( $i = 0; $i < count( $tokens ); $i ++ ) {
			$value = null;
			$token = $tokens[ $i ];
			if ( is_array( $token ) ) {
				$token_type = $token[0];

				switch ( $token_type ) {
					case T_NAMESPACE:
						$i ++;
						$current_namespace = $this->parse_token( $tokens, $i, 'parse_namespace' );
						break;
					case T_CLASS:
					case T_INTERFACE:
					case T_TRAIT:
						$i ++;
						$value = $this->parse_token( $tokens, $i, 'parse_entity' );
						break;
					case T_FUNCTION:
						$i ++;
						$value = $this->parse_token( $tokens, $i, 'parse_entity' );
						if ( $this->is_function_method( $value, $file_data ) ) {
							$value = null;
						}
						break;
					case T_CONSTANT_ENCAPSED_STRING:
						$i ++;
						$value = $this->parse_token( $tokens, $i, 'parse_constant' );
						break;
					default;

						break;
				}
				if ( ! empty( $value ) ) {
					$data[] = $this->concat_namespace_with_entity( $current_namespace, $value );
				}
			}
		}

		return $data;
	}

	protected function parse_token( array $tokens, &$i, $callback ): string {
		$value = '';

		for ( ; $i < count( $tokens ); $i ++ ) {
			$result = call_user_func( array( $this, $callback ), $tokens[ $i ][0] );
			if ( $result === true ) {
				$value .= $tokens[ $i ][1];
			} elseif ( $result === false ) {
				break;
			} elseif ( $result === null ) {
				continue;
			}
		}

		return $value;
	}

	protected function concat_namespace_with_entity( $namespace, $entity ): string {
		return ltrim( $namespace . '\\' . $entity, '\\' );
	}

	protected function parse_namespace( $token_type ): ?bool {
		if ( $token_type === ';' ) {
			return false;
		}
		if ( in_array( $token_type, array( T_STRING, T_NS_SEPARATOR ), true ) ) {
			return true;
		}

		return null;
	}

	protected function parse_entity( $token_type ): ?bool {
		if ( $token_type === T_WHITESPACE ) {
			return null;
		}
		if ( $token_type === T_STRING ) {
			return true;
		}

		return false;
	}

	protected function parse_constant( $token_type ): ?bool {
		if ( $token_type === T_WHITESPACE || $token_type == '=' ) {
			return null;
		}
		if ( in_array( $token_type, array( T_STRING, T_DNUMBER, T_LNUMBER ) ) ) {
			return true;
		}

		return false;
	}

	protected function is_function_method( string $func_name, string $file_data ): bool {
		return (bool) preg_match( "/(?:class|interface|trait).*?\{.*?{$func_name}.*?\}.*}/ms", $file_data );
	}

	protected function get_relative_path( $from_path, $to_path ) {
		$from = realpath( $from_path );
		$to   = realpath( $to_path );
		if ( empty( $from ) ) {
			throw new \InvalidArgumentException( "Folder {$from_path} does not exist" );
		}
		if ( empty( $to ) ) {
			throw new \InvalidArgumentException( "Folder {$to_path} does not exist" );
		}

		$from_parts = explode( DIRECTORY_SEPARATOR, trim( $from, DIRECTORY_SEPARATOR ) );
		$to_parts   = explode( DIRECTORY_SEPARATOR, trim( $to, DIRECTORY_SEPARATOR ) );

		while ( true ) {
			if ( isset( $from_parts[0] ) &&
			     isset( $to_parts[0] ) &&
			     $from_parts[0] === $to_parts[0] ) {
				array_splice( $from_parts, 0, 1 );
				array_splice( $to_parts, 0, 1 );
			} else {
				break;
			}
		}

		$from_relative_parts = array_filter( explode( '|', rtrim( str_repeat( '..|', count( $from_parts ) ), '|' ) ) );

		$new_parts         = array_merge( array(), $from_relative_parts, $to_parts );
		$new_relative_path = '.' . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $new_parts );

		return $new_relative_path;
	}

	function get_result_as_array(): array {
		return $this->classes_map;
	}

	function get_result_as_json(): string {
		return json_encode( $this->classes_map );
	}

	function export_result_in_file( $file_path ): bool {
		try {
			$file_data = sprintf( '<?php return %s;', var_export( $this->classes_map, true ) );
			$file_data = str_replace( '\\\\', '\\', $file_data );
			file_put_contents( $file_path, $file_data );

			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	function export_result_in_json_file( $file_path ): bool {
		try {
			file_put_contents( $file_path, json_encode( $this->classes_map ) );

			return true;
		} catch ( \Exception $e ) {

			return false;
		}
	}
}