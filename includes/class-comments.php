<?php
/**
 * Clase para desactivar comentarios
 *
 * @package Kit_Security
 * @author Roberto Berná Larrosa
 */

// Si se accede directamente, salir
if (!defined('ABSPATH')) {
    exit;
}

class Kit_Security_Comments {
    
    /**
     * Inicializar funcionalidad
     */
    public static function init() {
        // Desactivar soporte de comentarios y trackbacks
        add_action('admin_init', array(__CLASS__, 'disable_comments_admin'));
        
        // Cerrar comentarios en el frontend
        add_filter('comments_open', array(__CLASS__, 'disable_comments_status'), 20, 2);
        add_filter('pings_open', array(__CLASS__, 'disable_comments_status'), 20, 2);
        
        // Ocultar comentarios existentes
        add_filter('comments_array', array(__CLASS__, 'disable_comments_hide_existing'), 10, 2);
        
        // Remover del admin bar
        add_action('admin_bar_menu', array(__CLASS__, 'remove_comments_admin_bar'), 999);
        
        // Remover del menú de administración
        add_action('admin_menu', array(__CLASS__, 'remove_comments_admin_menu'));
        
        // Remover del dashboard
        add_action('wp_dashboard_setup', array(__CLASS__, 'remove_comments_dashboard'));
        
        // Remover meta boxes
        add_action('admin_init', array(__CLASS__, 'remove_comments_meta_boxes'));
        
        // Redireccionar páginas de comentarios
        add_action('admin_init', array(__CLASS__, 'redirect_comments_page'));
    }
    
    /**
     * Desactivar comentarios en admin
     */
    public static function disable_comments_admin() {
        // Actualizar opciones por defecto
        update_option('default_comment_status', 'closed');
        update_option('default_ping_status', 'closed');
    }
    
    /**
     * Cerrar comentarios y trackbacks
     */
    public static function disable_comments_status() {
        return false;
    }
    
    /**
     * Ocultar comentarios existentes
     */
    public static function disable_comments_hide_existing($comments) {
        return array();
    }
    
    /**
     * Remover del admin bar
     */
    public static function remove_comments_admin_bar($wp_admin_bar) {
        $wp_admin_bar->remove_node('comments');
    }
    
    /**
     * Remover del menú de administración
     */
    public static function remove_comments_admin_menu() {
        remove_menu_page('edit-comments.php');
        
        // Remover de todos los post types
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }
    
    /**
     * Remover del dashboard
     */
    public static function remove_comments_dashboard() {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }
    
    /**
     * Remover meta boxes de comentarios
     */
    public static function remove_comments_meta_boxes() {
        foreach (get_post_types() as $post_type) {
            remove_meta_box('commentstatusdiv', $post_type, 'normal');
            remove_meta_box('commentsdiv', $post_type, 'normal');
            remove_meta_box('trackbacksdiv', $post_type, 'normal');
        }
    }
    
    /**
     * Redireccionar si intentan acceder a página de comentarios
     */
    public static function redirect_comments_page() {
        global $pagenow;
        
        if ($pagenow === 'edit-comments.php' || $pagenow === 'options-discussion.php') {
            wp_redirect(admin_url());
            exit;
        }
    }
}