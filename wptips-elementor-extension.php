<?php
/**
 * Plugin Name: WPTips Elementor Extension
 * Description: Custom Elementor extension.
 * Plugin URI:  https://wptips.dev/
 * Version:     1.0.0
 * Author:      Durgesh Tayade
 * Author URI:  https://durgeshtayade.com/
 * Text Domain: elementor-test-extension
 */
namespace WPTips_Elementor;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Main Elementor Test Extension Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
final class WPTips_Elementor_Extension
{

    /**
     * Plugin Version
     *
     * @since 1.0.0
     *
     * @var string The plugin version.
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     *
     * @since 1.0.0
     *
     * @var string Minimum Elementor version required to run the plugin.
     */
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

    /**
     * Minimum PHP Version
     *
     * @since 1.0.0
     *
     * @var string Minimum PHP version required to run the plugin.
     */
    const MINIMUM_PHP_VERSION = '7.0';

    /**
     * Instance
     *
     * @since 1.0.0
     *
     * @access private
     * @static
     *
     * @var WPTips_Elementor_Extension The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.0.0
     *
     * @access public
     * @static
     *
     * @return WPTips_Elementor_Extension An instance of the class.
     */
    public static function instance()
    {

        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function __construct()
    {

        add_action('init', [$this, 'i18n']);
        add_action('plugins_loaded', [$this, 'init']);

    }

    /**
     * Load Textdomain
     *
     * Load plugin localization files.
     *
     * Fired by `init` action hook.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function i18n()
    {

        load_plugin_textdomain('elementor-test-extension');

    }

    /**
     * Initialize the plugin
     *
     * Load the plugin only after Elementor (and other plugins) are loaded.
     * Checks for basic plugin requirements, if one check fail don't continue,
     * if all check have passed load the files required to run the plugin.
     *
     * Fired by `plugins_loaded` action hook.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function init()
    {

        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        //Register Widget Category
        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories']);


        // Add Plugin actions
        add_action('elementor/widgets/widgets_registered', [$this, 'init_widgets'], 20);
        //add_action('elementor/controls/controls_registered', [$this, 'init_controls']);
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have Elementor installed or activated.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function admin_notice_missing_main_plugin()
    {

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'elementor-test-extension'),
            '<strong>' . esc_html__('Elementor Test Extension', 'elementor-test-extension') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'elementor-test-extension') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);

    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required Elementor version.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function admin_notice_minimum_elementor_version()
    {

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-test-extension'),
            '<strong>' . esc_html__('Elementor Test Extension', 'elementor-test-extension') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'elementor-test-extension') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);

    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function admin_notice_minimum_php_version()
    {

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-test-extension'),
            '<strong>' . esc_html__('Elementor Test Extension', 'elementor-test-extension') . '</strong>',
            '<strong>' . esc_html__('PHP', 'elementor-test-extension') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);

    }

    /**
     * Create Category
     *
     * @param [type] $elements_manager
     * @return void
     */
    function add_elementor_widget_categories($elements_manager)
    {

        $elements_manager->add_category(
            'wptips-elementor',
            [
                'title' => __('WPTips Elemetor Addons', 'elementor-test-extension'),
                'icon' => 'fa fa-plug',
            ]
        );
    }


    /**
     * Init Widgets
     *
     * Include widgets files and register them
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function init_widgets($wm)
    {

        // Include Widget files
        require_once __DIR__ . '/widgets/image-box.php';
        require_once __DIR__ . '/widgets/test-widget.php';

        // Register widget
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\Widget_WPTips_Image_Box());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\Elementor_oEmbed_Widget());

    }

    /**
     * Init Controls
     *
     * Include controls files and register them
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function init_controls()
    {

        // Include Control files
        require_once __DIR__ . '/controls/test-control.php';

        // Register control
        \Elementor\Plugin::$instance->controls_manager->register_control('control-type-', new \Test_Control());

    }

}

WPTips_Elementor_Extension::instance();
