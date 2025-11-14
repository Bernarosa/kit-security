/**
 * JavaScript para el panel de administración de Kit Security
 * 
 * @package Kit_Security
 * @author Roberto Berná Larrosa
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Limpiar logs con AJAX
         */
        $('#kit-security-clear-logs').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var originalText = $button.text();
            
            // Confirmar acción
            if (!confirm('¿Estás seguro de que quieres limpiar todos los logs? Esta acción no se puede deshacer.')) {
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            $button.prop('disabled', true).text('Limpiando...');
            
            // Realizar petición AJAX
            $.ajax({
                url: kitSecurityAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'kit_security_clear_logs',
                    nonce: kitSecurityAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Mostrar mensaje de éxito
                        showNotice('success', response.data.message);
                        
                        // Recargar la página después de 1 segundo
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotice('error', response.data.message);
                        $button.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    showNotice('error', 'Error al limpiar los logs. Por favor, inténtalo de nuevo.');
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });
        
        /**
         * Mostrar/ocultar campos según checkboxes
         */
        
        // Login security fields
        $('#enable_login_security').on('change', function() {
            var $relatedFields = $('#login_max_attempts, #login_lockout_duration, #login_notification_email, #ip_whitelist')
                .closest('tr');
            
            if ($(this).is(':checked')) {
                $relatedFields.fadeIn();
            } else {
                $relatedFields.fadeOut();
            }
        }).trigger('change');
        
        // Custom login URL fields
        $('#enable_custom_login_url').on('change', function() {
            var $relatedFields = $('#custom_login_slug').closest('tr');
            
            if ($(this).is(':checked')) {
                $relatedFields.fadeIn();
            } else {
                $relatedFields.fadeOut();
            }
        }).trigger('change');
        
        /**
         * Validación del formulario
         */
        $('form').on('submit', function(e) {
            var isValid = true;
            var errorMessages = [];
            
            // Validar intentos máximos
            var maxAttempts = parseInt($('#login_max_attempts').val());
            if (maxAttempts < 1 || maxAttempts > 10) {
                isValid = false;
                errorMessages.push('Los intentos máximos deben estar entre 1 y 10.');
            }
            
            // Validar duración de bloqueo
            var lockoutDuration = parseInt($('#login_lockout_duration').val());
            if (lockoutDuration < 5 || lockoutDuration > 1440) {
                isValid = false;
                errorMessages.push('La duración del bloqueo debe estar entre 5 y 1440 minutos.');
            }
            
            // Validar email
            var email = $('#login_notification_email').val();
            if (email && !isValidEmail(email)) {
                isValid = false;
                errorMessages.push('El email de notificación no es válido.');
            }
            
            // Validar slug de login
            var loginSlug = $('#custom_login_slug').val();
            if ($('#enable_custom_login_url').is(':checked')) {
                if (!loginSlug || loginSlug.length < 3) {
                    isValid = false;
                    errorMessages.push('El slug de login debe tener al menos 3 caracteres.');
                }
                
                // Verificar que no sea un slug reservado
                var reservedSlugs = ['wp-admin', 'wp-login', 'admin', 'login', 'administrator', 'wp-content'];
                if (reservedSlugs.indexOf(loginSlug) !== -1) {
                    isValid = false;
                    errorMessages.push('El slug "' + loginSlug + '" está reservado. Por favor, elige otro.');
                }
            }
            
            // Validar IPs en whitelist
            var whitelist = $('#ip_whitelist').val().trim();
            if (whitelist) {
                var ips = whitelist.split('\n');
                for (var i = 0; i < ips.length; i++) {
                    var ip = ips[i].trim();
                    if (ip && !isValidIP(ip)) {
                        isValid = false;
                        errorMessages.push('La IP "' + ip + '" no es válida.');
                        break;
                    }
                }
            }
            
            // Mostrar errores si los hay
            if (!isValid) {
                e.preventDefault();
                showNotice('error', errorMessages.join('<br>'));
                $('html, body').animate({ scrollTop: 0 }, 500);
            }
        });
        
        /**
         * Copiar URL de login al portapapeles
         */
        $(document).on('click', '.kit-security-copy-login-url', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            
            // Crear elemento temporal
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(url).select();
            document.execCommand('copy');
            $temp.remove();
            
            // Mostrar feedback
            showNotice('success', 'URL copiada al portapapeles');
        });
        
        /**
         * Confirmación al cambiar URL de login
         */
        $('#enable_custom_login_url').on('change', function() {
            if ($(this).is(':checked')) {
                var message = '⚠️ IMPORTANTE: Al activar esta función, tu URL de login cambiará.\n\n';
                message += 'Asegúrate de guardar la nueva URL en un lugar seguro.\n';
                message += 'Si olvidas la URL, no podrás acceder al panel de administración.\n\n';
                message += '¿Deseas continuar?';
                
                if (!confirm(message)) {
                    $(this).prop('checked', false).trigger('change');
                }
            }
        });
        
        /**
         * Highlight de la fila actual al hacer hover en la tabla de logs
         */
        $('.wp-list-table tbody tr').hover(
            function() {
                $(this).css('background-color', '#f6f7f7');
            },
            function() {
                $(this).css('background-color', '');
            }
        );
        
        /**
         * Auto-refresh de logs cada 30 segundos si estamos en la tab de logs
         */
        if (window.location.href.indexOf('tab=logs') !== -1) {
            setInterval(function() {
                // Solo si no hay notificaciones activas
                if ($('.notice').length === 0) {
                    location.reload();
                }
            }, 30000); // 30 segundos
        }
        
    }); // End document ready
    
    /**
     * Funciones auxiliares
     */
    
    /**
     * Validar formato de email
     */
    function isValidEmail(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    /**
     * Validar formato de IP
     */
    function isValidIP(ip) {
        // IPv4
        var ipv4Regex = /^(\d{1,3}\.){3}\d{1,3}$/;
        if (ipv4Regex.test(ip)) {
            var parts = ip.split('.');
            for (var i = 0; i < parts.length; i++) {
                var num = parseInt(parts[i]);
                if (num < 0 || num > 255) {
                    return false;
                }
            }
            return true;
        }
        
        // IPv6 (validación básica)
        var ipv6Regex = /^([0-9a-fA-F]{0,4}:){7}[0-9a-fA-F]{0,4}$/;
        return ipv6Regex.test(ip);
    }
    
    /**
     * Mostrar notificación
     */
    function showNotice(type, message) {
        // Remover notificaciones existentes
        $('.kit-security-notice').remove();
        
        var noticeClass = 'notice notice-' + type + ' is-dismissible kit-security-notice';
        var $notice = $('<div class="' + noticeClass + '"><p>' + message + '</p></div>');
        
        // Insertar después del título
        $('.kit-security-wrap h1').after($notice);
        
        // Hacer que sea dismissible
        $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Descartar este aviso.</span></button>');
        
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        });
        
        // Auto-cerrar después de 5 segundos (solo para success)
        if (type === 'success') {
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    }
    
})(jQuery);