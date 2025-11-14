# 🛡️ Kit Security

![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL%20v2-green.svg)

Plugin de seguridad esencial para WordPress con funciones personalizables desde el panel de administración.

## 📋 Características

### 🔐 Seguridad de Login
- ✅ Bloqueo automático de IPs después de intentos fallidos
- ✅ Duración de bloqueo configurable
- ✅ Notificaciones por email
- ✅ Whitelist de IPs de confianza
- ✅ URL de login personalizada (oculta wp-login.php)
- ✅ Registro completo de intentos fallidos

### 🔒 Hardening de WordPress
- ✅ Oculta versión de WordPress
- ✅ Deshabilita editor de archivos
- ✅ Restringe REST API
- ✅ Protege archivos sensibles
- ✅ Previene enumeración de usuarios
- ✅ Mensajes de error genéricos
- ✅ Deshabilita emojis (mejora rendimiento)

### 🚫 Protecciones adicionales
- ✅ Desactivar comentarios completamente
- ✅ Deshabilitar XML-RPC
- ✅ Cabeceras de seguridad HTTP (X-Frame-Options, CSP, HSTS, etc.)

## 📦 Instalación

### Instalación manual

1. Descarga el plugin o clona el repositorio:
```bash
git clone https://github.com/Bernarosa/kit-security.git
```

2. Sube la carpeta `kit-security` a `/wp-content/plugins/`

3. Activa el plugin desde el panel de WordPress

4. Ve a **Kit Security** en el menú lateral para configurar

### Instalación desde WordPress

_(Próximamente disponible en el repositorio oficial de WordPress)_

## 🚀 Uso

### Configuración básica

1. **General**: Activa/desactiva comentarios y XML-RPC
2. **Login**: Configura bloqueo de intentos y URL personalizada
3. **Hardening**: Activa protecciones de WordPress
4. **Logs**: Visualiza intentos fallidos y IPs bloqueadas

### Configuración recomendada
```
✓ Bloqueo de intentos: 3 intentos, 15 minutos
✓ URL de login personalizada: activa (usa un slug único)
✓ XML-RPC: deshabilitado (si no usas apps móviles)
✓ Hardening: activado
✓ Cabeceras de seguridad: activadas
✓ Añade tu IP a la whitelist
```

## 📸 Capturas de pantalla

_(Próximamente)_

## 🔧 Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- MySQL 5.6 o superior

## 📝 Changelog

### [1.0.1] - 2024-11-14
- 🐛 **Fix**: Corregido problema donde al guardar una pestaña se desactivaban las opciones de otras pestañas

### [1.0.0] - 2024-11-14
- 🎉 Lanzamiento inicial
- Desactivar comentarios
- Bloqueo de intentos de login
- URL de login personalizada
- Deshabilitar XML-RPC
- Hardening de WordPress
- Cabeceras de seguridad
- Sistema de logs

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia GPL v2 o posterior. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 👨‍💻 Autor

**Roberto Berná Larrosa**

- GitHub: [@Bernarosa](https://github.com/Bernarosa)
- Email: info@robertoberna.es

## 🙏 Agradecimientos

- Comunidad de WordPress
- Contribuidores de seguridad

## ⚠️ Advertencia

**IMPORTANTE**: Si activas la URL de login personalizada, asegúrate de guardar la nueva URL en un lugar seguro. Si la olvidas, necesitarás acceder por FTP para desactivar el plugin.

## 📞 Soporte

- Issues: [GitHub Issues](https://github.com/Bernarosa/kit-security/issues)
- Email: info@robertoberna.es

---

Hecho con ❤️ por Roberto Berná Larrosa