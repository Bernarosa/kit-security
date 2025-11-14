<?php
/**
 * Clase para seguridad de login
 * Bloquea intentos de login fallidos por IP
 *
 * @package Kit_Security
 * @author Roberto Berná Larrosa
 */

// Si se accede directamente, salir
if (!defined('ABSPATH')) {
    exit;
}

class Kit_Security_Login {
    
    /**
     * Inicializar funcionalidad
     */
    public static function init() {
        // Hook para verificar intentos antes del login
        add_filter('authenticate', array(__CLASS__, 'check_login_attempts'), 30, 3);
        
        // Hook para registrar intentos fallidos
        add_action('wp_login_failed', array(__CLASS__, 'log_failed_attempt'));
        
        // Limpiar intentos antiguos periódicamente
        add_action('init', array(__CLASS__, 'schedule_cleanup'));
    }
    
    /**
     * Verificar si la IP está bloqueada
     */
    public static function check_login_attempts($user, $username, $password) {
        // Si ya hay un error, devolver ese error
        if (is_wp_error($user)) {
            return $user;
        }
        
        // Si no hay username, continuar
        if (empty($username)) {
            return $user;
        }
        
        $ip_address = self::get_user_ip();
        
        // Verificar si la IP está en whitelist
        if (self::is_ip_whitelisted($ip_address)) {
            return $user;
        }
        
        // Verificar si la IP está bloqueada
        if (self::is_ip_blocked($ip_address)) {
            $blocked_until = self::get_blocked_until($ip_address);
            $time_remaining = human_time_diff(current_time('timestamp'), strtotime($blocked_until));
            
            return new WP_Error(
                'ip_blocked',
                sprintf(
                    __('Tu IP ha sido bloqueada temporalmente debido a múltiples intentos fallidos. Inténtalo de nuevo en %s.', 'kit-security'),
                    $time_remaining
                )
            );
        }
        
        // Verificar número de intentos
        $attempts = self::get_login_attempts($ip_address);
        $options = get_option('kit_security_options', array());
        $max_attempts = isset($options['login_max_attempts']) ? intval($options['login_max_attempts']) : 3;
        
        if ($attempts >= $max_attempts) {
            self::block_ip($ip_address);
            
            // Enviar notificación por email
            self::send_block_notification($ip_address, $username);
            
            return new WP_Error(
                'too_many_attempts',
                __('Demasiados intentos fallidos. Tu IP ha sido bloqueada temporalmente.', 'kit-security')
            );
        }
        
        return $user;
    }
    
    /**
     * Registrar intento fallido
     */
    public static function log_failed_attempt($username) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kit_security_log';
        $ip_address = self::get_user_ip();
        
        // Verificar si la IP está en whitelist
        if (self::is_ip_whitelisted($ip_address)) {
            return;
        }
        
        // Insertar en la base de datos
        $wpdb->insert(
            $table_name,
            array(
                'ip_address' => $ip_address,
                'username' => sanitize_text_field($username),
                'attempt_time' => current_time('mysql')
            ),
            array('%s', '%s', '%s')
        );
    }
    
    /**
     * Obtener número de intentos de una IP
     */
    private static function get_login_attempts($ip_address) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kit_security_log';
        $options = get_option('kit_security_options', array());
        $lockout_duration = isset($options['login_lockout_duration']) ? intval($options['login_lockout_duration']) : 15;
        
        // Contar intentos en el período de tiempo
        $time_limit = date('Y-m-d H:i:s', strtotime("-{$lockout_duration} minutes"));
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
            WHERE ip_address = %s 
            AND attempt_time > %s 
            AND (blocked_until IS NULL OR blocked_until < NOW())",
            $ip_address,
            $time_limit
        ));
        
        return intval($count);
    }
    
    /**
     * Verificar si una IP está bloqueada
     */
    private static function is_ip_blocked($ip_address) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kit_security_log';
        
        $blocked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
            WHERE ip_address = %s 
            AND blocked_until > NOW()
            LIMIT 1",
            $ip_address
        ));
        
        return intval($blocked) > 0;
    }
    
    /**
     * Obtener hasta cuándo está bloqueada una IP
     */
    private static function get_blocked_until($ip_address) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kit_security_log';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT blocked_until FROM $table_name 
            WHERE ip_address = %s 
            AND blocked_until > NOW()
            ORDER BY blocked_until DESC
            LIMIT 1",
            $ip_address
        ));
    }
    
    /**
     * Bloquear una IP
     */
    private static function block_ip($ip_address) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kit_security_log';
        $options = get_option('kit_security_options', array());
        $lockout_duration = isset($options['login_lockout_duration']) ? intval($options['login_lockout_duration']) : 15;
        
        $blocked_until = date('Y-m-d H:i:s', strtotime("+{$lockout_duration} minutes"));
        
        // Actualizar todos los registros recientes de esta IP
        $wpdb->update(
            $table_name,
            array('blocked_until' => $blocked_until),
            array('ip_address' => $ip_address),
            array('%s'),
            array('%s')
        );
    }
    
    /**
     * Verificar si la IP está en whitelist
     */
    private static function is_ip_whitelisted($ip_address) {
        $options = get_option('kit_security_options', array());
        $whitelist = isset($options['ip_whitelist']) ? $options['ip_whitelist'] : '';
        
        if (empty($whitelist)) {
            return false;
        }
        
        // Convertir string a array
        $whitelist_ips = array_map('trim', explode("\n", $whitelist));
        
        return in_array($ip_address, $whitelist_ips);
    }
    
    /**
     * Obtener IP del usuario
     */
    private static function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Enviar notificación de bloqueo por email
     */
    private static function send_block_notification($ip_address, $username) {
        $options = get_option('kit_security_options', array());
        $email = isset($options['login_notification_email']) ? $options['login_notification_email'] : get_option('admin_email');
        
        if (empty($email)) {
            return;
        }
        
        $subject = sprintf(__('[%s] IP bloqueada por intentos fallidos de login', 'kit-security'), get_bloginfo('name'));
        
        $message = sprintf(
            __("Se ha bloqueado una IP por múltiples intentos fallidos de login.\n\nDetalles:\nIP: %s\nUsuario intentado: %s\nFecha: %s\nSitio: %s", 'kit-security'),
            $ip_address,
            $username,
            current_time('mysql'),
            get_bloginfo('url')
        );
        
        wp_mail($email, $subject, $message);
    }
    
    /**
     * Programar limpieza de registros antiguos
     */
    public static function schedule_cleanup() {
        if (!wp_next_scheduled('kit_security_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'kit_security_cleanup_logs');
        }
        
        add_action('kit_security_cleanup_logs', array(__CLASS__, 'cleanup_old_logs'));
    }
    
    /**
     * Limpiar registros antiguos (más de 30 días)
     */
    public static function cleanup_old_logs() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kit_security_log';
        
        $wpdb->query(
            "DELETE FROM $table_name 
            WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
    }
}