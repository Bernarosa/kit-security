<?php
/**
 * Clase para deshabilitar XML-RPC
 * Previene ataques de fuerza bruta a través de XML-RPC
 *
 * @package Kit_Security
 * @author Roberto Berná Larrosa
 */

// Si se accede directamente, salir
if (!defined('ABSPATH')) {
    exit;
}

class Kit_Security_XMLRPC {
    
    /**
     * Inicializar funcionalidad
     */
    public static function init() {
        // Deshabilitar XML-RPC completamente
        add_filter('xmlrpc_enabled', '__return_false');
        
        // Remover el link de RSD (Really Simple Discovery)
        remove_action('wp_head', 'rsd_link');
        
        // Remover el header X-Pingback
        add_filter('wp_headers', array(__CLASS__, 'remove_pingback_header'));
        
        // Bloquear acceso al archivo xmlrpc.php
        add_action('init', array(__CLASS__, 'block_xmlrpc_access'));
        
        // Deshabilitar métodos específicos si XML-RPC se usa
        add_filter('xmlrpc_methods', array(__CLASS__, 'remove_xmlrpc_methods'));
    }
    
    /**
     * Remover header X-Pingback
     */
    public static function remove_pingback_header($headers) {
        if (isset($headers['X-Pingback'])) {
            unset($headers['X-Pingback']);
        }
        return $headers;
    }
    
    /**
     * Bloquear acceso directo a xmlrpc.php
     */
    public static function block_xmlrpc_access() {
        // Si están intentando acceder a xmlrpc.php
        if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
            // Devolver error 403
            header('HTTP/1.1 403 Forbidden');
            die('XML-RPC services are disabled on this site.');
        }
    }
    
    /**
     * Remover métodos peligrosos de XML-RPC
     * Por si acaso XML-RPC se necesita para algo específico
     */
    public static function remove_xmlrpc_methods($methods) {
        // Métodos que se usan comúnmente en ataques de fuerza bruta
        unset($methods['system.multicall']);
        unset($methods['system.listMethods']);
        unset($methods['system.getCapabilities']);
        
        return $methods;
    }
}