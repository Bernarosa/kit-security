<?php
/**
 * Clase para añadir cabeceras de seguridad HTTP
 * Protege contra ataques XSS, clickjacking, etc.
 *
 * @package Kit_Security
 * @author Roberto Berná Larrosa
 */

// Si se accede directamente, salir
if (!defined('ABSPATH')) {
    exit;
}

class Kit_Security_Headers {
    
    /**
     * Inicializar funcionalidad
     */
    public static function init() {
        // Añadir headers de seguridad
        add_action('send_headers', array(__CLASS__, 'add_security_headers'));
        
        // También añadir en el hook wp_headers para asegurar
        add_filter('wp_headers', array(__CLASS__, 'filter_wp_headers'));
    }
    
    /**
     * Añadir headers de seguridad
     */
    public static function add_security_headers() {
        // X-Frame-Options: Previene clickjacking
        if (!headers_sent()) {
            header('X-Frame-Options: SAMEORIGIN');
            
            // X-Content-Type-Options: Previene MIME type sniffing
            header('X-Content-Type-Options: nosniff');
            
            // X-XSS-Protection: Protección contra XSS (navegadores antiguos)
            header('X-XSS-Protection: 1; mode=block');
            
            // Referrer-Policy: Controla qué información se envía en el referrer
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            // Permissions-Policy: Controla qué features del navegador se pueden usar
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
            
            // Content-Security-Policy: Política de seguridad de contenido (básica)
            // Nota: Puede causar problemas con algunos plugins, por eso es básica
            header("Content-Security-Policy: frame-ancestors 'self'");
            
            // Strict-Transport-Security: Forzar HTTPS (solo si el sitio usa HTTPS)
            if (is_ssl()) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            }
        }
    }
    
    /**
     * Filtrar headers de WordPress
     */
    public static function filter_wp_headers($headers) {
        // X-Frame-Options
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        
        // X-Content-Type-Options
        $headers['X-Content-Type-Options'] = 'nosniff';
        
        // X-XSS-Protection
        $headers['X-XSS-Protection'] = '1; mode=block';
        
        // Referrer-Policy
        $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
        
        // Permissions-Policy
        $headers['Permissions-Policy'] = 'geolocation=(), microphone=(), camera=()';
        
        // Content-Security-Policy
        $headers['Content-Security-Policy'] = "frame-ancestors 'self'";
        
        // Strict-Transport-Security (solo HTTPS)
        if (is_ssl()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }
        
        return $headers;
    }
}