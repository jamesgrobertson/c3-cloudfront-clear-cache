<?php

$c3_admin = CloudFront_Clear_Cache_Admin::get_instance();
$c3_admin->add_hook();

class CloudFront_Clear_Cache_Admin {
	private static $instance;

	private static $text_domain;

	const MENU_ID = 'c3-admin-menu';
	const MESSAGE_KEY = 'c3-admin-errors';
	const FLUSH_CACHE = 'c3-flush-cache';

	private function __construct() {}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	public function add_hook() {
		self::$text_domain = CloudFront_Clear_Cache::text_domain();
		add_action( 'admin_init',    array( $this, 'c3_admin_init' ) );
		add_action( 'admin_notices', array( $this, 'c3_admin_notices' ) );
		add_action( 'c3_add_setting_before', array( $this, 'c3_manual_flush' ) );
	}

	public function c3_manual_flush() {
	}

	public function c3_admin_menu() {
    <table class="widefat form-table">
      <tbody>
<?php foreach ( $c3_settings_keys as $key => $title ) : ?>
        <tr>
          <th>　<?php echo esc_html( $title );?></th>
          <td>
						<?php $name = "{$option_name}[{$key}]";?>
            <input
              name="<?php echo esc_attr( $name );?>"
              type="text"
              id='<?php echo esc_attr( $key );?>'
              value="<?php echo esc_attr( $c3_settings[ $key ] );?>"
              class="regular-text code"
            >
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
</div>
<?php
	}

	public function c3_admin_init() {
		$option_name = CloudFront_Clear_Cache::OPTION_NAME;
		$nonce_key = CloudFront_Clear_Cache::OPTION_NAME;
		if ( isset ( $_POST[ self::MENU_ID ] ) && $_POST[ self::MENU_ID ] ) {
			if ( check_admin_referer( $nonce_key , self::MENU_ID ) ) {
				update_option( CloudFront_Clear_Cache::OPTION_NAME, $_POST[ $option_name ] );
			} else {
				update_option( CloudFront_Clear_Cache::OPTION_NAME, '' );
			}
			wp_safe_redirect( menu_page_url( self::MENU_ID , false ) );
		}

		if ( isset ( $_POST[ self::FLUSH_CACHE ] ) && $_POST[ self::FLUSH_CACHE ] ) {
			$c3 = CloudFront_Clear_Cache::get_instance();
			add_filter( 'c3_invalidation_flag' , array( $this , 'force_invalidation') );
			$c3->c3_invalidation();
		}

		load_plugin_textdomain( self::$text_domain );

	}

	public function force_invalidation( $flag ) {
		return false;
	}

	public function c3_admin_notices(){
		$messages = get_transient( self::MESSAGE_KEY );
		if ( ! $messages ) {
			return;
		}
?>
    <div class="updated">
      <ul>
        <?php foreach ( $messages as $message ) : ?>
          <li><?php echo esc_html( $message );?></li>
        <?php endforeach;?>
      </ul>
    </div>
<?php
	}
}
