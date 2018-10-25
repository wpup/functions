<?php

namespace WPUP\Functions;

use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class REST_API {

    /**
     * REST API namespace.
     *
     * @var string
     */
    protected $namespace = 'functions/v1';

    /**
     * REST API construct.
     */
    public function __construct() {
		add_action( 'rest_api_init', [$this, 'rest_api_init'] );
        add_action( 'rest_api_init', [$this, 'register_routes'] );
    }

    /**
     * REST API init function.
     */
    public function rest_api_init() {
    }

    /**
     * Register REST API routes.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/(?P<name>[\w]+)', [
            [
				'methods'  => [
                    WP_REST_Server::CREATABLE,
                    WP_REST_Server::DELETABLE,
                    WP_REST_Server::EDITABLE,
                    WP_REST_Server::READABLE,
                ],
				'callback' => [$this, 'call_function']
            ]
        ] );
    }

    /**
     * Call function file.
     *
     * @param  WP_REST_Request $reqest
     *
     * @return mixed
     */
    public function call_function( WP_REST_Request $request ) {
        $name = $request['name'];
        $file = $this->find_file( $name );

        /**
         * Modify file path.
         *
         * @param  string $file
         * @param  string $name
         *
         * @return string
         */
        $file = apply_filters( 'functions_file', $file, $name );

        if ( empty( $file ) ) {
            return new WP_Error( 'functions_invalid_handler', __( 'The handler for the function is invalid', 'wp-functions' ), ['status' => 500] );
        }

        require $file;

        $handler = $this->find_handler( $file );

        /**
         * Modify handler function.
         *
         * @param  string $handler
         * @param  string $name
         *
         * @return string
         */
        $handler = apply_filters( 'functions_handler', $handler, $name );

        if ( ! is_callable( $handler ) ) {
            return new WP_Error( 'functions_invalid_handler', __( 'The handler for the function is invalid', 'wp-functions' ), ['status' => 500] );
        }

        $res = call_user_func( $handler, $request );

        /**
         * Modify function response.
         *
         * @param  mixed $res
         * @param  string $name
         *
         * @return mixed
         *
         */
        $res = apply_filters( 'functions_response', $res, $name );

        return $res;
    }

    /**
     * Find namespace name and/or class name from file.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function find_class_name( $file ) {
        if ( ! file_exists( $file ) ) {
            return '';
        }

        $content         = file_get_contents( $file );
        $tokens          = token_get_all( $content );
        $class_name      = '';
        $namespace_name  = '';
        $i               = 0;
        $len             = count( $tokens );

        for ( ; $i < $len; $i++ ) {
            if ( $tokens[$i][0] === T_NAMESPACE ) {
                for ( $j = $i + 1; $j < $len; $j++ ) {
                    if ( $tokens[$j][0] === T_STRING ) {
                        $namespace_name .= '\\' . $tokens[$j][1];
                    } else if ( $tokens[$j] === '{' || $tokens[$j] === ';' ) {
                        break;
                    }
                }
            }
            if ( $tokens[$i][0] === T_CLASS ) {
                for ( $j = $i + 1; $j < $len; $j++ ) {
                    if ( $tokens[$j] === '{' ) {
                        $class_name = $tokens[$i + 2][1];
                    }
                }
            }
        }

        if ( empty( $class_name ) ) {
            return $namespace_name;
        }

        if ( empty( $namespace_name ) ) {
            return $class_name;
        }

        return $namespace_name . '\\' . $class_name;
    }

    /**
     * Find function file.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function find_file( $name ) {
        $dirs = [
            get_template_directory() . '/functions/'
        ];

        /**
         * Modify functions directories.
         *
         * @param  array $dirs
         *
         * @return array
         */
        $dirs = apply_filters( 'functions_directories', $dirs );

        if ( ! is_array( $dirs ) ) {
            return '';
        }

        foreach ( $dirs as $dir ) {
            if ( ! is_string( $dir ) ) {
                continue;
            }

            $dir  = rtrim( $dir, '/' ) . '/';
            $file = sprintf( '%s%s.php', $dir, $name );

            if ( file_exists( $file ) ) {
                return $file;
            }
        }

        return '';
    }

    /**
     * Find handler name.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function find_handler( $file ) {
        $class_name = $this->find_class_name( $file );

        if ( empty( $class_name ) ) {
            return 'handler';
        }

        if ( method_exists( $class_name, 'handler' ) ) {
            return $class_name . '::handler';
        }

        return $class_name . '\\handler';
    }
}
