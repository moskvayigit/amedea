<?php
namespace Amedea;

/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.0.0
 */
class Amedea {

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var Plugin The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * widget_scripts
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function amedea__widget_scripts() {

		wp_enqueue_script('jquery');
		
		wp_register_script('anime', 'https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.2/anime.min.js', array(), true, true);
		wp_register_script('eventemitter', 'https://cdnjs.cloudflare.com/ajax/libs/EventEmitter/1.1.3/EventEmitter.min.js', array(), true, true);
		wp_register_script('flip', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/Flip.min.js', array(), true, true);
		wp_register_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), true, true);
		wp_register_script('lenis', 'https://unpkg.com/lenis@1.1.5/dist/lenis.min.js', array(), true, true);
		wp_register_script('locomotive', 'https://cdn.jsdelivr.net/npm/locomotive-scroll@4.1.4/dist/locomotive-scroll.min.js', array(), true, true);
		wp_register_script('observer', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/Observer.min.js', array(), true, true);
		wp_register_script('scrolltrigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', array(), true, true);
		wp_register_script('splitting', 'https://unpkg.com/splitting/dist/splitting.min.js', array(), true, true);
		wp_register_script('split-type', 'https://unpkg.com/split-type@0.3.4/umd/index.min.js', array(), true, true);
		wp_register_script('tweenmax', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.3/TweenMax.min.js', array(), true, true);
		
		//Register Libraries
		foreach (glob(__DIR__ . '/assets/lib/*.js') as $filename) {  
			wp_register_script(basename($filename, '.js'), plugins_url('/assets/lib/'.basename($filename),__FILE__),  array(), true, true);
		}
			
		// Register jQuery  
		foreach (glob(__DIR__ . '/assets/js/*.js') as $filename) {  
			wp_register_script('amedea-'.basename($filename, '.js'), plugins_url('/assets/js/'.basename($filename),__FILE__),  array(), true, true);
		}
		
	}
	
	/**
	 * widget_styles
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function amedea__widget_styles(){

		wp_register_style('amedea',plugins_url('/assets/style.css',__FILE__));
		wp_enqueue_style( 'amedea' );
		
		foreach (glob(__DIR__ . '/assets/css/*.css') as $filename) {  
	
			wp_register_style(basename($filename, '.css'),plugins_url('/assets/css/'.basename($filename),__FILE__));
		}
		
	}	

	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function amedea__include_widgets_files() {
		
		foreach (glob(__DIR__ . '/widgets/*/*.php') as $filename) {
			require_once $filename;
		}
	
	}
	
	
	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function amedea__register_widgets() {
		// Its is now safe to include Widgets files
		$this->amedea__include_widgets_files();
		
		// Get the namespace  
		$namespace = __NAMESPACE__ . '\\Widgets\\amedea__';  

		// Register widgets  
		foreach (glob(__DIR__ . '/widgets/*/*.php') as $filename) {  
			$className = str_replace("-","_",$namespace . basename($filename, '.php'));
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new $className());  
		}
		
	}
	
	//category registered
	public function amedea__add_elementor_categories( $elements_manager ) {



		$elements_manager->add_category(
			'amedea-category',
			[
				'title' => esc_html__( 'Amedea', 'amedea' ),
				'icon' => 'fa fa-plug',
			]
		);
		
		$elements_manager->add_category(
			'amedea-sa-category',
			[
				'title' => esc_html__( 'Amedea SA', 'amedea' ),
				'icon' => 'fa fa-plug',
			]
		);
	}

	/**
	 *  Plugin class constructor
	 *
	 * Register plugin action hooks and filters
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		// Enqueue widget styles
        add_action( 'elementor/frontend/after_register_styles', [ $this, 'amedea__widget_styles' ] , 100 );
        add_action( 'admin_enqueue_scripts', [ $this, 'amedea__widget_styles' ] , 100 );

		// Enqueue widget scripts
        add_action( 'elementor/frontend/after_register_scripts', [ $this, 'amedea__widget_scripts' ], 100 );

		// Register widgets
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'amedea__register_widgets' ] );

		// Category registered
		add_action( 'elementor/elements/categories_registered',  [ $this,'amedea__add_elementor_categories' ]);

	}
}

// Instantiate Plugin Class
Amedea::instance();
