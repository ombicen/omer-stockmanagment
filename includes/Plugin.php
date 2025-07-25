<?php

namespace MadeByHypeStockmanagment;

if (! defined('ABSPATH')) {
    exit;
}

class Plugin
{
    private $admin_page;
    private $data_manager;
    private $ui_manager;
    private $assets_manager;
    private $ajax_handler;
    private $plugin_file;

    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
        $this->load_dependencies();
    }

    public function run()
    {
        add_action('init', [$this, 'init_plugin']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // Declare HPOS compatibility
        add_action('before_woocommerce_init', [$this, 'declare_hpos_compatibility']);
    }

    private function load_dependencies()
    {
        // Load all modular components
        require_once plugin_dir_path(__FILE__) . 'Admin/AdminPage.php';
        require_once plugin_dir_path(__FILE__) . 'Admin/AjaxHandler.php';
        require_once plugin_dir_path(__FILE__) . 'Data/DataManager.php';
        require_once plugin_dir_path(__FILE__) . 'UI/UIManager.php';
        require_once plugin_dir_path(__FILE__) . 'Assets/AssetsManager.php';

        $this->data_manager = new Data\DataManager();
        $this->ui_manager = new UI\UIManager();
        $this->assets_manager = new Assets\AssetsManager();
        $this->admin_page = new Admin\AdminPage();
        $this->ajax_handler = new Admin\AjaxHandler();

        // Set dependencies
        $this->admin_page->set_dependencies($this->data_manager, $this->ui_manager);
    }

    public function init_plugin()
    {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }

        // Initialize components
        $this->admin_page->init();
        $this->data_manager->init();
        $this->ui_manager->init();
        $this->assets_manager->init();
        $this->ajax_handler->init();
    }

    /**
     * Add custom admin menu page
     */
    public function add_admin_menu()
    {
        $this->admin_page->add_admin_menu();
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook)
    {
        $this->assets_manager->enqueue_admin_scripts($hook, $this->plugin_file);
    }

    /**
     * Get data manager instance
     */
    public function get_data_manager()
    {
        return $this->data_manager;
    }

    /**
     * Get UI manager instance
     */
    public function get_ui_manager()
    {
        return $this->ui_manager;
    }

    /**
     * Declare HPOS (High-Performance Order Storage) compatibility
     */
    public function declare_hpos_compatibility()
    {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                $this->plugin_file,
                true
            );
        }
    }

    /**
     * Display notice if WooCommerce is not active
     */
    public function woocommerce_missing_notice()
    {
?>
        <div class="notice notice-error">
            <p><?php _e('MadeByHype Stock Management requires WooCommerce to be installed and activated.', 'madebyhype-stockmanagment'); ?>
            </p>
        </div>
<?php
    }
}
