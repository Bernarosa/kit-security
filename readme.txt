=== Kit Security ===
Contributors: robertoberna
Tags: security, login, firewall, hardening, xmlrpc
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin de seguridad esencial para WordPress con funciones personalizables desde el panel de administración.

== Description ==

Kit Security es un plugin completo de seguridad para WordPress que te permite proteger tu sitio web de las amenazas más comunes. Todas las funciones son completamente configurables desde un panel de administración intuitivo.

= Características principales =

* **Desactivar comentarios**: Elimina completamente los comentarios de tu sitio
* **Bloqueo de intentos de login**: Protege contra ataques de fuerza bruta
* **URL de login personalizada**: Cambia wp-login.php por una URL personalizada
* **Deshabilitar XML-RPC**: Previene ataques a través de XML-RPC
* **Hardening de WordPress**: Oculta información sensible del sistema
* **Cabeceras de seguridad**: Añade headers HTTP de seguridad
* **Registro de intentos**: Visualiza todos los intentos de login fallidos
* **Whitelist de IPs**: Excluye IPs de confianza del bloqueo

= Funciones de seguridad de login =

* Bloqueo automático de IPs después de X intentos fallidos
* Duración de bloqueo configurable (en minutos)
* Notificaciones por email cuando se bloquea una IP
* Whitelist de IPs que nunca serán bloqueadas
* Registro completo de intentos fallidos con fecha y hora

= Funciones de hardening =

* Oculta la versión de WordPress
* Deshabilita el editor de archivos del administrador
* Restringe REST API a usuarios autenticados
* Protege archivos sensibles (wp-config.php)
* Previene enumeración de usuarios
* Mensajes de error genéricos en login
* Deshabilita emojis de WordPress (mejora rendimiento)
* Elimina meta tags innecesarios

= Cabeceras de seguridad =

* X-Frame-Options: Previene clickjacking
* X-Content-Type-Options: Previene MIME sniffing
* X-XSS-Protection: Protección contra XSS
* Referrer-Policy: Controla información del referrer
* Permissions-Policy: Controla features del navegador
* Content-Security-Policy: Política básica de seguridad
* Strict-Transport-Security: Fuerza HTTPS (si está disponible)

== Installation ==

1. Sube la carpeta `kit-security` al directorio `/wp-content/plugins/`
2. Activa el plugin a través del menú 'Plugins' en WordPress
3. Ve a 'Kit Security' en el menú lateral para configurar las opciones
4. Activa las funciones de seguridad que necesites

== Frequently Asked Questions ==

= ¿Es compatible con otros plugins de seguridad? =

Sí, Kit Security está diseñado para ser compatible con la mayoría de plugins de seguridad. Sin embargo, te recomendamos desactivar funciones duplicadas en otros plugins para evitar conflictos.

= ¿Qué pasa si olvido mi URL de login personalizada? =

Si olvidas tu URL de login, puedes acceder por FTP y desactivar el plugin desde la carpeta `/wp-content/plugins/`. También puedes editar las opciones directamente en la base de datos.

= ¿El plugin afecta al rendimiento del sitio? =

No, Kit Security está optimizado para tener un impacto mínimo en el rendimiento. De hecho, algunas funciones como deshabilitar emojis pueden mejorar la velocidad de carga.

= ¿Necesito conocimientos técnicos para usar el plugin? =

No, el plugin está diseñado para ser fácil de usar. Todas las opciones tienen descripciones claras y valores por defecto seguros.

= ¿Puedo ver quién intenta acceder a mi sitio? =

Sí, en la pestaña "Logs" puedes ver todos los intentos de login fallidos con la IP, usuario intentado, fecha y estado de bloqueo.

= ¿Las notificaciones por email son obligatorias? =

No, puedes dejar el campo de email vacío si no quieres recibir notificaciones. Por defecto usa el email del administrador.

= ¿Qué IPs debería añadir a la whitelist? =

Te recomendamos añadir tu IP personal, la IP de tu oficina, o cualquier IP desde la que accedas regularmente al sitio.

== Screenshots ==

1. Panel principal con opciones generales
2. Configuración de seguridad de login
3. Opciones de hardening de WordPress
4. Registro de intentos fallidos y IPs bloqueadas

== Changelog ==

= 1.0.2 =
* Fix: Corregido problema al desactivar opciones (checkboxes desmarcados ahora se guardan correctamente)

= 1.0.1 =
* Fix: Corregido problema donde al guardar una pestaña se desactivaban las opciones de otras pestañas

= 1.0.0 =
* Lanzamiento inicial
* Desactivar comentarios
* Bloqueo de intentos de login
* URL de login personalizada
* Deshabilitar XML-RPC
* Hardening de WordPress
* Cabeceras de seguridad HTTP
* Registro de logs
* Whitelist de IPs

== Upgrade Notice ==

= 1.0.0 =
Primera versión del plugin.

== Configuración recomendada ==

Para una protección óptima, te recomendamos:

1. Activar el bloqueo de intentos de login (3 intentos, 15 minutos de bloqueo)
2. Configurar una URL de login personalizada
3. Deshabilitar XML-RPC si no usas apps móviles de WordPress
4. Activar hardening de WordPress
5. Activar cabeceras de seguridad
6. Añadir tu IP a la whitelist

== Soporte ==

Para soporte, reportar bugs o solicitar nuevas funciones:

* GitHub: https://github.com/Bernarosa/kit-security
* Email: info@robertoberna.es

== Créditos ==

Desarrollado por Roberto Berná Larrosa

== Licencia ==

Este plugin es software libre bajo licencia GPL v2 or later.