<?php
/**
 * Clase para el panel de administración
 * Gestiona la interfaz de configuración del plugin
 *
 * @package Kit_Security
 * @author Roberto Berná Larrosa
 */

// Si se accede directamente, salir
if (!defined('ABSPATH')) {
    exit;
}

class Kit_Security_Admin_Panel {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Añadir menú de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Registrar settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Cargar assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX para limpiar logs
        add_action('wp_ajax_kit_security_clear_logs', array($this, 'ajax_clear_logs'));
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Kit Security', 'kit-security'),
            __('Kit Security', 'kit-security'),
            'manage_options',
            'kit-security',
            array($this, 'render_admin_page'),
            'dashicons-shield',
            80
        );
    }
    
    /**
     * Registrar settings
     */
    public function register_settings() {
        register_setting('kit_security_options', 'kit_security_options', array($this, 'sanitize_options'));
    }
    
    /**
     * Cargar assets del admin
     */
    public function enqueue_admin_assets($hook) {
        // Solo cargar en nuestra página
        if ($hook !== 'toplevel_page_kit-security') {
            return;
        }
        
        wp_enqueue_style(
            'kit-security-admin',
            KIT_SECURITY_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            KIT_SECURITY_VERSION
        );
        
        wp_enqueue_script(
            'kit-security-admin',
            KIT_SECURITY_PLUGIN_URL . 'admin/js/admin-script.js',
            array('jquery'),
            KIT_SECURITY_VERSION,
            true
        );
        
        // Pasar datos a JavaScript
        wp_localize_script('kit-security-admin', 'kitSecurityAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kit_security_nonce')
        ));
    }
    
    /**
     * Renderizar página de administración
     */
    public function render_admin_page() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para acceder a esta página.', 'kit-security'));
        }
        
        // Obtener opciones actuales
        $options = get_option('kit_security_options', array());
        
        // Obtener tab activa
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        ?>
        <div class="wrap kit-security-wrap">
            <h1>
                <span class="dashicons dashicons-shield"></span>
                <?php echo esc_html__('Kit Security', 'kit-security'); ?>
            </h1>
            
            <p class="kit-security-subtitle">
                <?php echo esc_html__('Plugin de seguridad esencial para WordPress', 'kit-security'); ?>
            </p>
            
            <?php settings_errors(); ?>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=kit-security&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('General', 'kit-security'); ?>
                </a>
                <a href="?page=kit-security&tab=login" class="nav-tab <?php echo $active_tab === 'login' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Login', 'kit-security'); ?>
                </a>
                <a href="?page=kit-security&tab=hardening" class="nav-tab <?php echo $active_tab === 'hardening' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Hardening', 'kit-security'); ?>
                </a>
                <a href="?page=kit-security&tab=logs" class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Logs', 'kit-security'); ?>
                </a>
            </nav>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('kit_security_options');
                
                // Renderizar tab activa
                switch ($active_tab) {
                    case 'login':
                        $this->render_login_tab($options);
                        break;
                    case 'hardening':
                        $this->render_hardening_tab($options);
                        break;
                    case 'logs':
                        $this->render_logs_tab();
                        break;
                    default:
                        $this->render_general_tab($options);
                        break;
                }
                
                // Botón de guardar solo si no es la tab de logs
                if ($active_tab !== 'logs') {
                    submit_button(__('Guardar cambios', 'kit-security'));
                }
                ?>
            </form>
            
            <div class="kit-security-footer">
                <p>
                    <?php 
                    echo sprintf(
                        __('Desarrollado por %s | Versión %s', 'kit-security'),
                        '<a href="https://github.com/Bernarosa" target="_blank">Roberto Berná Larrosa</a>',
                        KIT_SECURITY_VERSION
                    ); 
                    ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizar tab General
     */
    private function render_general_tab($options) {
        ?>
        <div class="kit-security-tab-content">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="disable_comments">
                            <?php esc_html_e('Desactivar comentarios', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="kit-security-switch">
                            <input type="checkbox" 
                                   id="disable_comments" 
                                   name="kit_security_options[disable_comments]" 
                                   value="1" 
                                   <?php checked(isset($options['disable_comments']) ? $options['disable_comments'] : 0, 1); ?>>
                            <span class="kit-security-slider"></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Desactiva completamente los comentarios en todo el sitio.', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="disable_xmlrpc">
                            <?php esc_html_e('Deshabilitar XML-RPC', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="kit-security-switch">
                            <input type="checkbox" 
                                   id="disable_xmlrpc" 
                                   name="kit_security_options[disable_xmlrpc]" 
                                   value="1" 
                                   <?php checked(isset($options['disable_xmlrpc']) ? $options['disable_xmlrpc'] : 1, 1); ?>>
                            <span class="kit-security-slider"></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Desactiva XML-RPC para prevenir ataques de fuerza bruta.', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Renderizar tab Login
     */
    private function render_login_tab($options) {
        ?>
        <div class="kit-security-tab-content">
            <h2><?php esc_html_e('Seguridad de Login', 'kit-security'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="enable_login_security">
                            <?php esc_html_e('Activar bloqueo de intentos', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="kit-security-switch">
                            <input type="checkbox" 
                                   id="enable_login_security" 
                                   name="kit_security_options[enable_login_security]" 
                                   value="1" 
                                   <?php checked(isset($options['enable_login_security']) ? $options['enable_login_security'] : 1, 1); ?>>
                            <span class="kit-security-slider"></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Bloquea IPs después de varios intentos fallidos.', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="login_max_attempts">
                            <?php esc_html_e('Intentos máximos', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" 
                               id="login_max_attempts" 
                               name="kit_security_options[login_max_attempts]" 
                               value="<?php echo esc_attr(isset($options['login_max_attempts']) ? $options['login_max_attempts'] : 3); ?>" 
                               min="1" 
                               max="10" 
                               class="small-text">
                        <p class="description">
                            <?php esc_html_e('Número de intentos fallidos antes de bloquear la IP.', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="login_lockout_duration">
                            <?php esc_html_e('Duración del bloqueo (minutos)', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" 
                               id="login_lockout_duration" 
                               name="kit_security_options[login_lockout_duration]" 
                               value="<?php echo esc_attr(isset($options['login_lockout_duration']) ? $options['login_lockout_duration'] : 15); ?>" 
                               min="5" 
                               max="1440" 
                               class="small-text">
                        <p class="description">
                            <?php esc_html_e('Tiempo que permanecerá bloqueada la IP (en minutos).', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="login_notification_email">
                            <?php esc_html_e('Email de notificación', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="email" 
                               id="login_notification_email" 
                               name="kit_security_options[login_notification_email]" 
                               value="<?php echo esc_attr(isset($options['login_notification_email']) ? $options['login_notification_email'] : get_option('admin_email')); ?>" 
                               class="regular-text">
                        <p class="description">
                            <?php esc_html_e('Recibirás un email cuando se bloquee una IP.', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ip_whitelist">
                            <?php esc_html_e('IPs en Whitelist', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="ip_whitelist" 
                                  name="kit_security_options[ip_whitelist]" 
                                  rows="5" 
                                  class="large-text code"><?php echo esc_textarea(isset($options['ip_whitelist']) ? $options['ip_whitelist'] : ''); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('IPs que nunca serán bloqueadas (una por línea).', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <hr>
            
            <h2><?php esc_html_e('URL de Login Personalizada', 'kit-security'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="enable_custom_login_url">
                            <?php esc_html_e('Activar URL personalizada', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="kit-security-switch">
                            <input type="checkbox" 
                                   id="enable_custom_login_url" 
                                   name="kit_security_options[enable_custom_login_url]" 
                                   value="1" 
                                   <?php checked(isset($options['enable_custom_login_url']) ? $options['enable_custom_login_url'] : 0, 1); ?>>
                            <span class="kit-security-slider"></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Cambia la URL de wp-login.php por una personalizada.', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="custom_login_slug">
                            <?php esc_html_e('Slug de login', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <code><?php echo esc_url(home_url('/')); ?></code>
                        <input type="text" 
                               id="custom_login_slug" 
                               name="kit_security_options[custom_login_slug]" 
                               value="<?php echo esc_attr(isset($options['custom_login_slug']) ? $options['custom_login_slug'] : 'acceso'); ?>" 
                               class="regular-text">
                        <p class="description">
                            <?php esc_html_e('Ejemplo: Si pones "acceso", tu login será tudominio.com/acceso', 'kit-security'); ?>
                        </p>
                        <?php if (isset($options['enable_custom_login_url']) && $options['enable_custom_login_url']) : ?>
                            <p class="description">
                                <strong><?php esc_html_e('Tu URL de login actual:', 'kit-security'); ?></strong>
                                <a href="<?php echo esc_url(home_url($options['custom_login_slug'])); ?>" target="_blank">
                                    <?php echo esc_url(home_url($options['custom_login_slug'])); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Renderizar tab Hardening
     */
    private function render_hardening_tab($options) {
        ?>
        <div class="kit-security-tab-content">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="enable_wp_hardening">
                            <?php esc_html_e('Activar hardening de WordPress', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="kit-security-switch">
                            <input type="checkbox" 
                                   id="enable_wp_hardening" 
                                   name="kit_security_options[enable_wp_hardening]" 
                                   value="1" 
                                   <?php checked(isset($options['enable_wp_hardening']) ? $options['enable_wp_hardening'] : 1, 1); ?>>
                            <span class="kit-security-slider"></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Oculta versión de WP, deshabilita editor de archivos, previene enumeración de usuarios, etc.', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="enable_security_headers">
                            <?php esc_html_e('Activar cabeceras de seguridad', 'kit-security'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="kit-security-switch">
                            <input type="checkbox" 
                                   id="enable_security_headers" 
                                   name="kit_security_options[enable_security_headers]" 
                                   value="1" 
                                   <?php checked(isset($options['enable_security_headers']) ? $options['enable_security_headers'] : 1, 1); ?>>
                            <span class="kit-security-slider"></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Añade cabeceras HTTP de seguridad (X-Frame-Options, X-XSS-Protection, etc.).', 'kit-security'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <div class="kit-security-info-box">
                <h3><?php esc_html_e('¿Qué incluye el hardening?', 'kit-security'); ?></h3>
                <ul>
                    <li><?php esc_html_e('✓ Oculta la versión de WordPress', 'kit-security'); ?></li>
                    <li><?php esc_html_e('✓ Deshabilita el editor de archivos del admin', 'kit-security'); ?></li>
                    <li><?php esc_html_e('✓ Restringe REST API a usuarios autenticados', 'kit-security'); ?></li>
                    <li><?php esc_html_e('✓ Protege archivos sensibles (wp-config.php)', 'kit-security'); ?></li>
                    <li><?php esc_html_e('✓ Previene enumeración de usuarios', 'kit-security'); ?></li>
                    <li><?php esc_html_e('✓ Mensajes de error genéricos en login', 'kit-security'); ?></li>
                    <li><?php esc_html_e('✓ Deshabilita emojis de WordPress', 'kit-security'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizar tab Logs
     */
    private function render_logs_tab() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kit_security_log';
        
        // Obtener logs recientes
        $logs = $wpdb->get_results(
            "SELECT * FROM $table_name 
            ORDER BY attempt_time DESC 
            LIMIT 100"
        );
        
        ?>
        <div class="kit-security-tab-content">
            <div class="kit-security-logs-header">
                <h2><?php esc_html_e('Registro de Intentos Fallidos', 'kit-security'); ?></h2>
                <button type="button" id="kit-security-clear-logs" class="button button-secondary">
                    <?php esc_html_e('Limpiar logs', 'kit-security'); ?>
                </button>
            </div>
            
            <?php if (empty($logs)) : ?>
                <p><?php esc_html_e('No hay intentos fallidos registrados.', 'kit-security'); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('IP', 'kit-security'); ?></th>
                            <th><?php esc_html_e('Usuario', 'kit-security'); ?></th>
                            <th><?php esc_html_e('Fecha', 'kit-security'); ?></th>
                            <th><?php esc_html_e('Bloqueado hasta', 'kit-security'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log) : ?>
                            <tr>
                                <td><code><?php echo esc_html($log->ip_address); ?></code></td>
                                <td><?php echo esc_html($log->username); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->attempt_time))); ?></td>
                                <td>
                                    <?php 
                                    if ($log->blocked_until && strtotime($log->blocked_until) > current_time('timestamp')) {
                                        echo '<span class="kit-security-blocked">' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->blocked_until))) . '</span>';
                                    } else {
                                        echo '<span class="kit-security-not-blocked">' . esc_html__('No bloqueado', 'kit-security') . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Sanitizar opciones
     */
   public function sanitize_options($input) {
    // Obtener las opciones actuales para mantener los valores que no se envían
    $old_options = get_option('kit_security_options', array());
    $sanitized = $old_options; // Partir de las opciones existentes
    
    // Actualizar solo los valores que vienen en el input
    
    // Checkboxes - solo actualizar si existen en el input
    if (isset($input['disable_comments'])) {
        $sanitized['disable_comments'] = 1;
    } elseif (array_key_exists('disable_comments', $input)) {
        $sanitized['disable_comments'] = 0;
    }
    
    if (isset($input['enable_login_security'])) {
        $sanitized['enable_login_security'] = 1;
    } elseif (array_key_exists('enable_login_security', $input)) {
        $sanitized['enable_login_security'] = 0;
    }
    
    if (isset($input['enable_custom_login_url'])) {
        $sanitized['enable_custom_login_url'] = 1;
    } elseif (array_key_exists('enable_custom_login_url', $input)) {
        $sanitized['enable_custom_login_url'] = 0;
    }
    
    if (isset($input['disable_xmlrpc'])) {
        $sanitized['disable_xmlrpc'] = 1;
    } elseif (array_key_exists('disable_xmlrpc', $input)) {
        $sanitized['disable_xmlrpc'] = 0;
    }
    
    if (isset($input['enable_wp_hardening'])) {
        $sanitized['enable_wp_hardening'] = 1;
    } elseif (array_key_exists('enable_wp_hardening', $input)) {
        $sanitized['enable_wp_hardening'] = 0;
    }
    
    if (isset($input['enable_security_headers'])) {
        $sanitized['enable_security_headers'] = 1;
    } elseif (array_key_exists('enable_security_headers', $input)) {
        $sanitized['enable_security_headers'] = 0;
    }
    
    // Números - solo actualizar si vienen en el input
    if (isset($input['login_max_attempts'])) {
        $sanitized['login_max_attempts'] = absint($input['login_max_attempts']);
    }
    
    if (isset($input['login_lockout_duration'])) {
        $sanitized['login_lockout_duration'] = absint($input['login_lockout_duration']);
    }
    
    // Email - solo actualizar si viene en el input
    if (isset($input['login_notification_email'])) {
        $sanitized['login_notification_email'] = sanitize_email($input['login_notification_email']);
    }
    
    // Texto - solo actualizar si viene en el input
    if (isset($input['custom_login_slug'])) {
        $sanitized['custom_login_slug'] = sanitize_title($input['custom_login_slug']);
    }
    
    if (isset($input['ip_whitelist'])) {
        $sanitized['ip_whitelist'] = sanitize_textarea_field($input['ip_whitelist']);
    }
    
    // Si se cambió el slug de login, flush rewrite rules
    if (isset($input['custom_login_slug']) && 
        isset($old_options['custom_login_slug']) && 
        $old_options['custom_login_slug'] !== $sanitized['custom_login_slug']) {
        flush_rewrite_rules();
    }
    
    return $sanitized;
}
    
    /**
     * AJAX para limpiar logs
     */
    public function ajax_clear_logs() {
        check_ajax_referer('kit_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos.', 'kit-security')));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'kit_security_log';
        $wpdb->query("TRUNCATE TABLE $table_name");
        
        wp_send_json_success(array('message' => __('Logs limpiados correctamente.', 'kit-security')));
    }
}

// Inicializar el panel de admin
new Kit_Security_Admin_Panel();