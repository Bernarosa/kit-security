# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.0.2] - 2024-11-14

### Corregido
- Problema al guardar opciones por pestañas (ahora funciona correctamente activar/desactivar en cualquier pestaña)

## [1.0.1] - 2024-11-14

## [1.0.0] - 2024-XX-XX

### Añadido
- Sistema de bloqueo de intentos de login por IP
- URL de login personalizada con redirección 404
- Desactivación completa de comentarios
- Deshabilitación de XML-RPC
- Hardening de WordPress (ocultar versión, deshabilitar editor, etc.)
- Cabeceras de seguridad HTTP
- Panel de administración con pestañas
- Sistema de logs de intentos fallidos
- Whitelist de IPs
- Notificaciones por email de bloqueos
- Validación de formularios con JavaScript
- Limpieza de logs vía AJAX
- Documentación completa

### Seguridad
- Protección contra ataques de fuerza bruta
- Prevención de clickjacking
- Protección XSS
- Prevención de enumeración de usuarios
- Protección de archivos sensibles

## [Unreleased]

### Planificado
- Soporte para reCAPTCHA
- Escaneo de malware
- Backup automático de configuración
- Exportar/importar configuración
- Más opciones de notificaciones (Slack, Telegram)
- Dashboard con estadísticas de seguridad
- Integración con servicios de reputación de IPs
- Modo de mantenimiento
- Protección contra hotlinking

---

[1.0.0]: https://github.com/Bernarosa/kit-security/releases/tag/v1.0.0