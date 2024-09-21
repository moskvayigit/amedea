<?php
/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.0.0
 */
class kiss_amedea__verifycode {
 
    const LICENCE_CALL_URL = "https://api.krasotaiskusstva.com/";

    private $licenceActivate_error;
    private $licenceDeactivate_error;

    public function __construct() {

        $this->licenceActivate_error = $this->licenceActivate();
        
        $this->LicenceHTMLForm();
    }

    public function LicenceHTMLForm(){
		echo '<div class="wrap">';
	?>
        <h2><?php echo esc_html__('License Activation Form for Amedea', 'amedea'); ?></h2>
        <?php
        $token = get_option('__token__amedea');
        $isActivated = get_option('__is_activated__amedea');

        if ($token && $isActivated) {
            ?>
            <?php
            if ($this->licenceDeactivate_error) {?>
                <p class="licence_error"><?php echo esc_html( $this->licenceDeactivate_error );?></p>
            <?php }?>
            <p><?php echo esc_html__('Thanks for the verification. You can enjoy using the plugin now...', 'amedea');?></p>
            <!-- deactivation form starts here -->
			<!-- deactivation form ends here -->
			
            <?php
		echo '<br/><br/><a href="https://krasotaiskusstva.com/wordpress/?bundlelink=amedea" target="_blank"><img class="ki-img" style="max-width:480px;" src="https://krasotaiskusstva.com/wordpress/?bundle=amedea" alt=""></a>';
		echo '</div>';
        }else{ ?>
            <?php
            if ($this->licenceActivate_error) {?>
                <p class="licence_error"><?php echo esc_html( $this->licenceActivate_error );?></p>
            <?php }?>
            <form method="post">
				<label for="username"><?php echo esc_html__( 'Envata Username :', 'amedea' ); ?></label><br/>
                <input type="text" style="width:100%;max-width:480px;" id="username" name="username" placeholder="Envato Username"><br/>
				<label for="email"><?php echo esc_html__( 'Email :', 'amedea' ); ?></label><br/>
                <input type="text" style="width:100%;max-width:480px;" id="email" name="email" placeholder="Your Email"><br/>
				<label for="purchase_code"><?php echo esc_html__( 'Purchase code :', 'amedea' ); ?></label><br/>
                <input type="text" style="width:100%;max-width:480px;" id="purchase_code" name="purchase_code" placeholder="Example: 1e71cs5f-13d9-41e8-a140-2cff01d96afb"><br/>
				<a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank"><?php echo esc_html__('Where can I get my purchase code?', 'amedea');?></a>
                
                <?php wp_nonce_field( 'submit_activate' ); ?>
                <?php submit_button( esc_html__( 'Activate', 'amedea' ), 'danger', 'submit_activate' ); ?>
            </form>
            <?php
		echo '<br/><br/><h4>' . esc_html__( 'You may like this, too :', 'amedea' ) . '</h4><a href="https://krasotaiskusstva.com/wordpress/?link=amedea" target="_blank"><img class="ki-img" style="max-width:480px;" src="https://krasotaiskusstva.com/wordpress/?image=amedea" alt=""></a>';
		echo '</div>';
		}
    }

    private function licenceActivate(){
		
        if ( ! isset( $_POST['submit_activate'] ) ) {
            return;
        }
		
		if ( ! isset( $_POST['email'] ) || empty($_POST['email']) ) {
            return 'Please Enter Your Email.';
        }
		
		if ( ! isset( $_POST['username'] ) || empty($_POST['username']) ) {
            return 'Please Enter Your Envato Username.';
        }
		
        if ( ! isset( $_POST['purchase_code'] ) || empty($_POST['purchase_code']) ) {
            return 'Please Enter Purchase Code.';
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'submit_activate' ) ) {
            wp_die( 'ERROR:1' );
        }
    
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'ERROR:2' );
        }
    
		$email = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
		$username = isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '';
        $purchase_code = isset( $_POST['purchase_code'] ) ? sanitize_text_field( $_POST['purchase_code'] ) : '';

        if ($username && $email && $purchase_code) {
            $url = self::LICENCE_CALL_URL."/wp-json/licenseenvato/v1/active";
            $domain = $this->domain();
            $response = $this->apicall($url, $purchase_code, $email, $domain);
            $date = json_decode($response);

            $token = isset( $date->token ) ? $date->token : '';
            if ($token) {
                update_option('__is_activated__amedea', true);
                update_option('__token__amedea', $token);
                update_option('__email__amedea', $email);
				update_option('__username__amedea', $username);
                update_option('__purchase_code__amedea', $purchase_code);
            }else{
                $statusCode = isset( $date->code ) ? $date->code : '';
                $statusMessage = isset( $date->message ) ? $date->message : '';
                if ($statusCode) {
                    return $statusMessage;
                }
            }
        }
    }

    private function domain() {
        $domain = get_option( 'siteurl' );
		$domain = str_replace( 'http://', '', $domain );
        $domain = str_replace( 'https://', '', $domain );
		$domain = str_replace( 'www.', '', $domain );
        return urlencode( $domain );
    }

    private function apicall($url, $purchase_code, $email, $domain = null){

        if ($domain) {
            $body = array(
                'code' => $purchase_code,
                'domain' => $domain,
				'email' => $email,
            );
        }else{
            $body = array(
                'token' => $purchase_code,
            );
        }
        
        $headers = array(
            'Content-Type' => 'application/json'
        );
        $response = wp_remote_post( $url , array(
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($body)
        ) );

        return $response['body'];
    }
}


?>
