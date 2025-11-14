<?php
/**
 * Clase para hardening de WordPress
 * Oculta información sensible y deshabilita funciones peligrosas
 *
 * @package Kit_Security
 * @author Roberto Berná Larrosa
 */

// Si se accede directamente, salir
if (!defined('ABSPATH')) {
    exit;
}

class Kit_Security_WP_Hardening {
    
    /**
     * Inicializar funcionalidad
     */
    public static function init() {
        // Ocultar versión de WordPress
        add_filter('the_generator', '__return_empty_string');
        remove_action('wp_head', 'wp_generator');
        
        // Remover versión de los scripts y estilos
        add_filter('style_loader_src', array(__CLASS__, 'remove_version_from_assets'), 9999);
        add_filter('script_loader_src', array(__CLASS__, 'remove_version_from_assets'), 9999);
        
        // Deshabilitar el editor de archivos
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
        
        // Remover meta tags innecesarios
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
        
        // Deshabilitar REST API para usuarios no autenticados (opcional)
        add_filter('rest_authentication_errors', array(__CLASS__, 'restrict_rest_api'));
        
        // Remover información de usuario en comentarios
        add_filter('comment_form_default_fields', array(__CLASS__, 'remove_website_field'));
        
        // Proteger archivos sensibles
        add_action('init', array(__CLASS__, 'protect_sensitive_files'));
        
        // Deshabilitar mensajes de error específicos en login
        add_filter('login_errors', array(__CLASS__, 'generic_login_error'));
        
        // Remover jQuery migrate si no es necesario
        add_action('wp_default_scripts', array(__CLASS__, 'remove_jquery_migrate'));
        
        // Deshabilitar emojis (opcional, mejora rendimiento)
        add_action('init', array(__CLASS__, 'disable_emojis'));
        
        // Proteger contra escaneo de usuarios
        add_action('template_redirect', array(__CLASS__, 'prevent_user_enumeration'));
    }
    
    /**
     * Remover versión de CSS y JS
     */
    public static function remove_version_from_assets($src) {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }
    
    /**
     * Restringir REST API a usuarios autenticados
     */
    public static function restrict_rest_api($result) {
        if (!empty($result)) {
            return $result;
        }
        
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_not_logged_in',
                __('Debes estar autenticado para acceder a la API REST.', 'kit-security'),
                array('status' => 401)
            );
        }
        
        return $result;
    }
    
    /**
     * Remover campo de website en comentarios
     */
    public static function remove_website_field($fields) {
        if (isset($fields['url'])) {
            unset($fields['url']);
        }
        return $fields;
    }
    
    /**
     * Proteger archivos sensibles mediante .htaccess
     */
    public static function protect_sensitive_files() {
        // Solo en Apache
        if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === false) {
            return;
        }
        
        $htaccess_file = ABSPATH . '.htaccess';
        
        // Verificar si existe y es escribible
        if (!file_exists($htaccess_file) || !is_writable($htaccess_file)) {
            return;
        }
        
        $rules = "\n# BEGIN Kit Security - Protect Files\n";
        $rules .= "<FilesMatch \"^(wp-config\\.php|error_log|debug\\.log)\">\n";
        $rules .= "    Order allow,deny\n";
        $rules .= "    Deny from all\n";
        $rules .= "</FilesMatch>\n";
        $rules .= "# END Kit Security - Protect Files\n";
        
        $htaccess_content = file_get_contents($htaccess_file);
        
        // Solo añadir si no existe ya
        if (strpos($htaccess_content, '# BEGIN Kit Security - Protect Files') === false) {
            file_put_contents($htaccess_file, $rules . $htaccess_content);
        }
    }
    
    /**
     * Mensaje de error genérico en login
     */
    public static function generic_login_error($error) {
        return __('Error: Credenciales incorrectas.', 'kit-security');
    }
    
    /**
     * Remover jQuery Migrate
     */
    public static function remove_jquery_migrate($scripts) {
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];
            
            if ($script->deps) {
                $script->deps = array_diff($script->deps, array('jquery-migrate'));
            }
        }
    }
    
    /**
     * Deshabilitar emojis
     */
    public static function disable_emojis() {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        
        add_filter('tiny_mce_plugins', array(__CLASS__, 'disable_emojis_tinymce'));
        add_filter('wp_resource_hints', array(__CLASS__, 'disable_emojis_dns_prefetch'), 10, 2);
    }
    
    /**
     * Filtro para TinyMCE
     */
    public static function disable_emojis_tinymce($plugins) {
        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        }
        return array();
    }
    
    /**
     * Remover DNS prefetch de emojis
     */
    public static function disable_emojis_dns_prefetch($urls, $relation_type) {
        if ('dns-prefetch' === $relation_type) {
            $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/');
            $urls = array_diff($urls, array($emoji_svg_url));
        }
        return $urls;
    }
    
    /**
     * Prevenir enumeración de usuarios
     */
    public static function prevent_user_enumeration() {
        // Bloquear ?author=1 y similares
        if (is_admin()) {
            return;
        }
        
        if (isset($_GET['author']) && is_numeric($_GET['author'])) {
            wp_die(__('Acceso no permitido.', 'kit-security'), 403);
        }
        
        // También bloquear en REST API
        if (preg_match('/wp-json\/wp\/v2\/users/i', $_SERVER['REQUEST_URI'])) {
            if (!is_user_logged_in()) {
                wp_die(__('Acceso no permitido.', 'kit-security'), 403);
            }
        }
    }
}