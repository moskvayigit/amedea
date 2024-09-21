<?php
// Take over the update check
add_filter( 'pre_set_site_transient_update_plugins', 'amedea__check_for_plugin_update' );

function amedea__check_for_plugin_update( $checked_data ) {
    $api_url     = 'https://amedea.pro/api';
    $plugin_slug = 'amedea';

	if(get_option('__amedea__token')){ $token = get_option('__amedea__token'); } else { $token = "NULL"; }
	if(get_option('__amedea__purchasecode')){ $purchase_code = get_option('__amedea__purchasecode'); } else { $purchase_code = "NULL"; }
	if(get_option('__amedea__username')){ $username = get_option('__amedea__username'); } else { $username = "NULL"; }
	if(get_option('__amedea__email')){ $email = get_option('__amedea__email'); } else { $email = get_option('admin_email'); }
	
	if (empty($checked_data->checked)) {
		if (empty($transient->checked)) {
			return $checked_data;
		} else {
       $checked_data = $transient;
		}
	}
	
	if($plugin_version){ $plugin_version = $checked_data->checked[ $plugin_slug . '/' . $plugin_slug . '.php' ]; } else { $plugin_version = AMEDEA__VERSION; }
	
    $request_args = [
        'slug'    => $plugin_slug,
        'version' => $checked_data->checked[ $plugin_slug . '/' . $plugin_slug . '.php' ],
		
		'token'  => $token,
		'purchase_code' => $purchase_code,
		'username' => $username,
		'email' => $email,
		'website' => get_bloginfo( 'url' ),
    ];

    $request_string = amedea__prepare_request( 'basic_check', $request_args );

    // Start checking for an update
    $raw_response = wp_remote_post( $api_url, $request_string );

    if ( ! is_wp_error( $raw_response ) && ( (int) $raw_response['response']['code'] === 200 ) ) {
        $response = unserialize( $raw_response['body'] );
    }

    if ( is_object( $response ) && ! empty( $response ) ) { // Feed the update data into WP updater
        $checked_data->response[ $plugin_slug . '/' . $plugin_slug . '.php' ] = $response;
    }

    return $checked_data;
}

// Take over the Plugin info screen
add_filter( 'plugins_api', 'amedea__plugin_api_call', 10, 3 );

function amedea__plugin_api_call( $def, $action, $args ) {
    $api_url     = 'https://amedea.pro/api';
    $plugin_slug = 'amedea';

    // Do nothing if this is not about getting plugin information
    if ( $action !== 'plugin_information' ) {
        return false;
    }

    if ( (string) $args->slug !== (string) $plugin_slug ) {
        // Conserve the value of previous filter of plugins list in alternate API
        return $def;
    }

    // Get the current version
    $plugin_info     = get_site_transient( 'update_plugins' );
    $current_version = $plugin_info->checked[ $plugin_slug . '/' . $plugin_slug . '.php' ];
	
	if(get_option('__amedea__token')){ $token = get_option('__amedea__token'); } else { $token = "NULL"; }
	if(get_option('__amedea__purchasecode')){ $purchase_code = get_option('__amedea__purchasecode'); } else { $purchase_code = "NULL"; }
	if(get_option('__amedea__username')){ $username = get_option('__amedea__username'); } else { $username = "NULL"; }
	if(get_option('__amedea__email')){ $email = get_option('__amedea__email'); } else { $email = get_option('admin_email'); }
	$args->version  	   = $current_version;
	$args->token   		   = $token;
    $args->purchase_code   = $purchase_code;
	$args->email		   = $email;
	$args->username	 	   = $username;
	$args->website		   = get_bloginfo( 'url' );
    
    $request_string = amedea__prepare_request( $action, $args );

    $request = wp_remote_post( $api_url, $request_string );

    if ( is_wp_error( $request ) ) {
        $res = new WP_Error( 'plugins_api_failed', esc_html__( 'An Unexpected HTTP Error occurred during the API request. Please, refresh the page and try again.' ), $request->get_error_message() );
    } else {
        $res = unserialize( $request['body'] );

        if ( $res === false ) {
            $res = new WP_Error( 'plugins_api_failed', esc_html__( 'An unknown error occurred' ), $request['body'] );
        }
    }

    return $res;
}

function amedea__prepare_request( $action, $args ) {
    global $wp_version;

    return [
        'body'       => [
            'action'  => $action,
            'request' => serialize( $args ),
            'api-key' => md5( get_bloginfo( 'url' ) ),
        ],
        'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
    ];
}
