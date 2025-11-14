<?php
/**
 * Plugin Name: Kit Security
 * Plugin URI: https://github.com/Bernarosa/kit-security
 * Description: Plugin de seguridad esencial para WordPress con funciones personalizables desde el panel de administración.
 * Version: 1.0.0
 * Author: Roberto Berná Larrosa
 * Author URI: https://github.com/Bernarosa
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kit-security
 * Domain Path: /languages
 */

// Si se accede directamente, salir
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('KIT_SECURITY_VERSION', '1.0.0');
define('KIT_SECURITY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KIT_SECURITY_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Clase principal del plugin
 */
class Kit_Security {
    
    /**
     * Instancia única del plugin
     */
    private static $instance = null;
    
    /**
     * Obtener instancia única
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Cargar dependencias
     */
    private function load_dependencies() {
        // Cargar clases de funcionalidades
        require_once KIT_SECURITY_PLUGIN_DIR . 'includes/class-comments.php';
        require_once KIT_SECURITY_PLUGIN_DIR . 'includes/class-login-security.php';
        require_once KIT_SECURITY_PLUGIN_DIR . 'includes/class-custom-login-url.php';
        require_once KIT_SECURITY_PLUGIN_DIR . 'includes/class-xmlrpc.php';
        require_once KIT_SECURITY_PLUGIN_DIR . 'includes/class-wp-hardening.php';
        require_once KIT_SECURITY_PLUGIN_DIR . 'includes/class-security-headers.php';
        
        // Cargar panel de administración
        if (is_admin()) {
            require_once KIT_SECURITY_PLUGIN_DIR . 'admin/class-admin-panel.php';
        }
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Activación del plugin
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Desactivación del plugin
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Inicializar funcionalidades
        add_action('plugins_loaded', array($this, 'init_features'));
    }
    
    /**
     * Inicializar funcionalidades según configuración
     */
    public function init_features() {
        $options = get_option('kit_security_options', array());
        
        // Desactivar comentarios
        if (!empty($options['disable_comments'])) {
            Kit_Security_Comments::init();
        }
        
        // Seguridad de login
        if (!empty($options['enable_login_security'])) {
            Kit_Security_Login::init();
        }
        
        // URL de login personalizada
        if (!empty($options['enable_custom_login_url'])) {
            Kit_Security_Custom_Login_URL::init();
        }
        
        // Deshabilitar XML-RPC
        if (!empty($options['disable_xmlrpc'])) {
            Kit_Security_XMLRPC::init();
        }
        
        // Hardening de WordPress
        if (!empty($options['enable_wp_hardening'])) {
            Kit_Security_WP_Hardening::init();
        }
        
        // Cabeceras de seguridad
        if (!empty($options['enable_security_headers'])) {
            Kit_Security_Headers::init();
        }
    }
    
    /**
     * Activación del plugin
     */
    public function activate() {
        // Crear opciones por defecto
        $default_options = array(
            'disable_comments' => 0,
            'enable_login_security' => 1,
            'login_max_attempts' => 3,
            'login_lockout_duration' => 15,
            'login_notification_email' => get_option('admin_email'),
            'enable_custom_login_url' => 0,
            'custom_login_slug' => 'acceso',
            'disable_xmlrpc' => 1,
            'enable_wp_hardening' => 1,
            'enable_security_headers' => 1,
            'ip_whitelist' => ''
        );
        
        add_option('kit_security_options', $default_options);
        
        // Crear tabla para log de intentos fallidos
        $this->create_log_table();
        
        flush_rewrite_rules();
    }
    
    /**
     * Desactivación del plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Crear tabla para log de intentos
     */
    private function create_log_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kit_security_log';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ip_address varchar(100) NOT NULL,
            username varchar(100) NOT NULL,
            attempt_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            blocked_until datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY ip_address (ip_address)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Inicializar el plugin
function kit_security_init() {
    return Kit_Security::get_instance();
}

// Ejecutar
kit_security_init();