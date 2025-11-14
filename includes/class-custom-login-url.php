<?php
/**
 * Clase para URL de login personalizada
 * Cambia la URL de wp-login.php y bloquea acceso directo
 *
 * @package Kit_Security
 * @author Roberto Berná Larrosa
 */

// Si se accede directamente, salir
if (!defined('ABSPATH')) {
    exit;
}

class Kit_Security_Custom_Login_URL {
    
    private static $new_login_slug = '';
    
    /**
     * Inicializar funcionalidad
     */
    public static function init() {
        $options = get_option('kit_security_options', array());
        self::$new_login_slug = isset($options['custom_login_slug']) ? sanitize_title($options['custom_login_slug']) : 'acceso';
        
        // Hooks principales
        add_action('plugins_loaded', array(__CLASS__, 'plugins_loaded'), 2);
        add_action('wp_loaded', array(__CLASS__, 'wp_loaded'));
        add_filter('site_url', array(__CLASS__, 'site_url'), 10, 4);
        add_filter('network_site_url', array(__CLASS__, 'network_site_url'), 10, 3);
        add_filter('wp_redirect', array(__CLASS__, 'wp_redirect'), 10, 2);
        add_filter('register', array(__CLASS__, 'register_link'));
        add_filter('lostpassword_url', array(__CLASS__, 'lostpassword_url'), 10, 2);
        
        // Remover acciones de wp-login.php
        remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);
    }
    
    /**
     * Hook plugins_loaded
     */
    public static function plugins_loaded() {
        global $pagenow;
        
        $request = parse_url($_SERVER['REQUEST_URI']);
        $path = untrailingslashit($request['path']);
        $slug = self::$new_login_slug;
        
        // Si estamos en la URL personalizada
        if (strpos($path, '/' . $slug) !== false || 
            (isset($_GET['action']) && strpos($path, '/' . $slug) !== false)) {
            
            $pagenow = 'wp-login.php';
            $_SERVER['SCRIPT_NAME'] = '/' . $slug;
            
        }
    }
    
    /**
     * Hook wp_loaded
     */
    public static function wp_loaded() {
        global $pagenow;
        
        $request = parse_url($_SERVER['REQUEST_URI']);
        $path = untrailingslashit($request['path']);
        $slug = self::$new_login_slug;
        
        // Si intentan acceder a wp-login.php o wp-admin directamente
        if (is_admin() && !is_user_logged_in() && !defined('DOING_AJAX') && 
            $pagenow !== 'admin-post.php' && $pagenow !== 'admin-ajax.php') {
            
            // Permitir si vienen de la URL personalizada
            if (strpos($path, '/' . $slug) === false) {
                self::return_404();
            }
        }
        
        // Bloquear wp-login.php directamente
        if ($pagenow === 'wp-login.php' && strpos($path, '/' . $slug) === false) {
            
            // Permitir AJAX y post requests específicos
            if (!defined('DOING_AJAX') && 
                !isset($_POST['log']) && 
                !isset($_GET['action']) || 
                (isset($_GET['action']) && !in_array($_GET['action'], array('postpass', 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register')))) {
                self::return_404();
            }
        }
        
        // Redirigir si están en la URL personalizada
        if (strpos($path, '/' . $slug) !== false) {
            
            // Cargar wp-login.php
            require_once ABSPATH . 'wp-login.php';
            die();
        }
    }
    
    /**
     * Filtrar site_url
     */
    public static function site_url($url, $path, $scheme, $blog_id) {
        return self::filter_login_url($url, $scheme);
    }
    
    /**
     * Filtrar network_site_url
     */
    public static function network_site_url($url, $path, $scheme) {
        return self::filter_login_url($url, $scheme);
    }
    
    /**
     * Filtrar URLs de login
     */
    private static function filter_login_url($url, $scheme = null) {
        if (strpos($url, 'wp-login.php') !== false) {
            $slug = self::$new_login_slug;
            $url = str_replace('wp-login.php', $slug, $url);
        }
        
        return $url;
    }
    
    /**
     * Filtrar redirecciones
     */
    public static function wp_redirect($location, $status) {
        if (strpos($location, 'wp-login.php') !== false) {
            $slug = self::$new_login_slug;
            $location = str_replace('wp-login.php', $slug, $location);
        }
        
        return $location;
    }
    
    /**
     * Filtrar link de registro
     */
    public static function register_link($link) {
        $slug = self::$new_login_slug;
        return str_replace('wp-login.php', $slug, $link);
    }
    
    /**
     * Filtrar URL de recuperar contraseña
     */
    public static function lostpassword_url($url, $redirect) {
        $slug = self::$new_login_slug;
        return str_replace('wp-login.php', $slug, $url);
    }
    
    /**
     * Devolver 404
     */
    private static function return_404() {
        global $wp_query;
        
        status_header(404);
        $wp_query->set_404();
        
        // Cargar template 404
        if (file_exists(get_template_directory() . '/404.php')) {
            include(get_template_directory() . '/404.php');
        } else {
            // 404 básico si no existe template
            echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head>';
            echo '<body><h1>404 Not Found</h1><p>The page you requested could not be found.</p></body></html>';
        }
        
        die();
    }
    
    /**
     * Obtener la URL de login personalizada
     */
    public static function get_login_url() {
        $slug = self::$new_login_slug;
        return home_url($slug);
    }
}