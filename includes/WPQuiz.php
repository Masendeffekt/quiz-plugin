<?php
/**
 * Class WPQuiz
 *
 * @package WPQuiz
 */

namespace WPQuiz;

use Automatic_Upgrader_Skin;
use Plugin_Upgrader;
use stdClass;
use WPQuiz\Admin\Admin;
use WPQuiz\Lockers\InitLockers;
use WPQuiz\Modules\PlayerTracking\PlayerTracking;
use WPQuiz\Modules\Stats\Stats;
use WPQuiz\Modules\Subscription\Subscription;
use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\PlayDataTracking\PlayDataTracking;
use WPQuiz\Processes\ImportProcess;
use WPQuiz\Shortcodes\WPQuizListingShortcode;
use WPQuiz\Shortcodes\WPQuizProShortcode;

/**
 * Class WPQuiz
 */
class WPQuiz {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'wp-quiz/v2';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '2.1.11';

	/**
	 * Plugin url.
	 *
	 * @var string
	 */
	private $plugin_url = '';

	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	private $plugin_dir = '';

	/**
	 * Class instance.
	 *
	 * @var WPQuiz
	 */
	protected static $_instance;

	/**
	 * Import background process.
	 *
	 * @var ImportProcess
	 */
	public $import_process;

	/**
	 * Module manager.
	 *
	 * @var ModuleManager
	 */
	public $module_manager;

	/**
	 * Gets class instance.
	 *
	 * @return WPQuiz
	 */
	public static function get_instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
			self::$_instance->setup();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'wp-quiz-pro' ), esc_html( $this->version ) );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'wp-quiz-pro' ), esc_html( $this->version ) );
	}

	/**
	 * Class constructor.
	 */
	protected function __construct() {}

	/**
	 * Setups plugin.
	 */
	public function setup() {
		$quiz_type_classes = $this->get_quiz_type_classes();
		foreach ( $quiz_type_classes as $quiz_type_class ) {
			QuizTypeManager::add( new $quiz_type_class() );
		}

		$class_names = $this->get_force_action_classes();
		foreach ( $class_names as $class_name ) {
			ForceActions\Manager::add( new $class_name() );
		}

		$this->register_shortcodes();

		( new PostTypeQuiz() )->init();
		( new Assets() )->init();
		( new GDPR() )->init();
		( new ShowAnsweredQuiz() )->init();
		( new SendMails() )->init();

		$play_data_tracking = new PlayDataTracking();
		$play_data_tracking->init();

		// REST init.
		new REST\REST();
		new REST\Quiz();
		new REST\Admin();

		if ( is_admin() ) {
			( new Admin() )->init();
		}

		if ( $this->mts_activated() ) {
			$this->module_manager = new ModuleManager();
			$this->module_manager->add( new Stats() );
			$this->module_manager->add( new PlayerTracking() );
			$this->module_manager->add( new Subscription() );
		}

		$this->hooks();
	}

	/**
	 * Adds hooks.
	 */
	protected function hooks() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ), 1 );
		add_action( 'init', array( $this, 'embed_output' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Activates plugin.
	 */
	public function activate() {
		$post_types = new PostTypeQuiz();
		$post_types->register();
		flush_rewrite_rules();

		$wq_upload_dir = wp_upload_dir();
		wp_mkdir_p( $wq_upload_dir['basedir'] . '/wp_quiz-import/' );
		wp_mkdir_p( $wq_upload_dir['basedir'] . '/wp_quiz-result-images/' );

		// phpcs:disable
		chmod( $wq_upload_dir['basedir'], 0755 );
		chmod( $wq_upload_dir['basedir'] . '/wp_quiz-import/', 0755 );
		chmod( $wq_upload_dir['basedir'] . '/wp_quiz-result-images/', 0755 );
		// phpcs:enable

		if ( ! $this->mts_activated() ) {
			$this->activate_updater_plugin();
		}
	}

	/**
	 * Checks if activated MTS account.
	 *
	 * @return bool
	 */
	public function mts_activated() {
		return class_exists( 'mts_connection' ) && defined( 'MTS_CONNECT_ACTIVE' ) && MTS_CONNECT_ACTIVE;
	}

	/**
	 * Activates updater plugin.
	 */
	protected function activate_updater_plugin() {
		$plugin_path = 'mythemeshop-connect/mythemeshop-connect.php';

		// Check if plugin exists.
		$plugins = get_plugins();
		if ( ! array_key_exists( $plugin_path, $plugins ) ) {
			// Auto-install it.
			include_once ABSPATH . 'wp-admin/includes/misc.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$skin        = new Automatic_Upgrader_Skin();
			$upgrader    = new Plugin_Upgrader( $skin );
			$plugin_file = 'https://mythemeshop.com/mythemeshop-connect.zip';
			$upgrader->install( $plugin_file );
		} else {
			// Plugin is already installed, check version.
			$ver = isset( $plugins[ $plugin_path ]['Version'] ) ? $plugins[ $plugin_path ]['Version'] : '1.0';
			if ( version_compare( $ver, '1.4' ) === -1 ) {
				// Update if < 1.4.
				include_once ABSPATH . 'wp-admin/includes/misc.php';
				include_once ABSPATH . 'wp-admin/includes/file.php';
				include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				$skin     = new Automatic_Upgrader_Skin();
				$upgrader = new Plugin_Upgrader( $skin );

				add_filter( 'pre_site_transient_update_plugins', array( $this, 'inject_connect_repo' ), 10, 2 );
				$upgrader->upgrade( $plugin_path );
				remove_filter( 'pre_site_transient_update_plugins', array( $this, 'inject_connect_repo' ) );
			}
		}

		activate_plugin( $plugin_path );
	}


	/**
	 * Injects connect repo.
	 *
	 * @param object $pre       Pre object.
	 * @param array  $transient Transient.
	 * @return object
	 */
	public function inject_connect_repo( $pre, $transient ) {
		$plugin_file = 'https://mythemeshop.com/mythemeshop-connect.zip';

		$return = new stdClass();

		$return->response                                                         = array();
		$return->response['mythemeshop-connect/mythemeshop-connect.php']          = new stdClass();
		$return->response['mythemeshop-connect/mythemeshop-connect.php']->package = $plugin_file;

		return $return;
	}


	/**
	 * Deactivates plugin.
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Loads plugin textdomain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-quiz-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Gets quiz type classes.
	 *
	 * @return array
	 */
	protected function get_quiz_type_classes() {
		/**
		 * Allows adding new quiz types.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes An array of quiz type classes.
		 */
		return apply_filters(
			'wp_quiz_type_classes',
			array(
				'\\WPQuiz\\QuizTypes\\Trivia',
				'\\WPQuiz\\QuizTypes\\Personality',
				'\\WPQuiz\\QuizTypes\\Swiper',
				'\\WPQuiz\\QuizTypes\\Flip',
				'\\WPQuiz\\QuizTypes\\FBQuiz',
				'\\WPQuiz\\QuizTypes\\ListQuiz',
			)
		);
	}

	/**
	 * Gets force action classes.
	 *
	 * @return array
	 */
	protected function get_force_action_classes() {
		/**
		 * Allows adding new force action.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes An array of force action classes.
		 */
		return apply_filters(
			'wp_quiz_force_action_classes',
			array(
				'\\WPQuiz\\ForceActions\\Subscribe',
				'\\WPQuiz\\ForceActions\\FBShare',
				'\\WPQuiz\\ForceActions\\Login',
				'\\WPQuiz\\ForceActions\\Redirect',
				'\\WPQuiz\\ForceActions\\Pay',
			)
		);
	}

	/**
	 * Registers shortcodes.
	 */
	protected function register_shortcodes() {
		( new WPQuizProShortcode() )->register();
		( new WPQuizListingShortcode() )->register();
	}

	/**
	 * Does something when all plugins are loaded.
	 */
	public function plugins_loaded() {
		$install = new Install();
		$install->install();
		$this->import_process = new ImportProcess();
	}

	/**
	 * Prints something inside <head> tag.
	 */
	public function wp_head() {
		if ( is_singular( PostTypeQuiz::get_name() ) ) {
			$quiz_id = get_queried_object_id();
			$quiz    = PostTypeQuiz::get_quiz( $quiz_id );
			$quiz->get_quiz_type()->wp_head( $quiz );
		} elseif ( ! empty( $_GET['wqtid'] ) ) { // WPCS: csrf ok.
			$play_data = PlayData::get( intval( $_GET['wqtid'] ) ); // WPCS: csrf ok.
			if ( $play_data ) {
				$quiz = PostTypeQuiz::get_quiz( $play_data->quiz_id );
				$quiz->get_quiz_type()->wp_head( $quiz );
			}
		}

		// phpcs:disable
		if ( Helper::get_option( 'fb_app_id' ) ) : ?>
			<script>
				// Allow detecting when fb api is loaded.
				function Deferred() {
					var self = this;
					this.promise = new Promise( function( resolve, reject ) {
						self.reject  = reject;
						self.resolve = resolve;
					});
				}
				window.fbLoaded = new Deferred();

				window.fbAsyncInit = function() {
					FB.init({
						appId            : '<?php echo esc_js( Helper::get_option( 'fb_app_id' ) ); ?>',
						autoLogAppEvents : true,
						xfbml            : true,
						version          : 'v3.0'
					});

					window.fbLoaded.resolve();
				};

				(function(d, s, id){
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) {return;}
					js = d.createElement(s); js.id = id;
					js.src = "https://connect.facebook.net/en_US/sdk.js";
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));
			</script>
			<?php
		endif;

		if ( Helper::get_option( 'ga_tracking_id' ) && 'on' !== Helper::get_option( 'ga_no_print_code' ) ) :
			?>
			<!-- Google Analytics -->
			<script>
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

				ga('create', '<?php echo esc_js( Helper::get_option( 'ga_tracking_id' ) ); ?>', 'auto');
				ga('send', 'pageview');
			</script>
			<!-- End Google Analytics -->
			<?php
		endif;
		// phpcs:enable
	}

	/**
	 * Prints embed output on embed page.
	 */
	public function embed_output() {
		if ( ! Helper::is_embed() ) {
			return;
		}
		$quiz = PostTypeQuiz::get_quiz( $_GET['wp_quiz_id'] ); // phpcs:ignore
		if ( ! $quiz ) {
			return;
		}
		Template::load_template( 'embed.php', compact( 'quiz' ) );
		die();
	}

	/**
	 * Get the plugin dir.
	 *
	 * @return string
	 */
	public function plugin_dir() {

		if ( ! $this->plugin_dir ) {
			$this->plugin_dir = trailingslashit( plugin_dir_path( WP_QUIZ_FILE ) );
		}

		return $this->plugin_dir;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {

		if ( ! $this->plugin_url ) {
			$this->plugin_url = trailingslashit( plugin_dir_url( WP_QUIZ_FILE ) );
		}

		return $this->plugin_url;
	}

	/**
	 * Get plugin includes directory.
	 *
	 * @return string
	 */
	public function includes_dir() {
		return $this->plugin_dir() . 'includes/';
	}

	/**
	 * Get plugin templates directory.
	 *
	 * @return string
	 */
	public function templates_dir() {
		return $this->plugin_dir() . 'templates/';
	}

	/**
	 * Get plugin libraries directory.
	 *
	 * @return string
	 */
	public function libraries_dir() {
		return $this->includes_dir() . 'libs/';
	}

	/**
	 * Get assets url.
	 *
	 * @return string
	 */
	public function assets() {
		return $this->plugin_url() . 'assets/frontend/';
	}

	/**
	 * Get admin assets url.
	 *
	 * @return string
	 */
	public function admin_assets() {
		return $this->plugin_url() . 'assets/admin/';
	}

	/**
	 * Get plugin admin directory.
	 *
	 * @return string
	 */
	public function admin_dir() {
		return $this->plugin_dir() . 'includes/Admin/';
	}
}
