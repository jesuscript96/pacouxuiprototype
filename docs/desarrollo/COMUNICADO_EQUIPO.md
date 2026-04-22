# 📢 COMUNICADO: Simplificación del desarrollo local

**Para:** Rafa, Adrián, César (equipo tecben-core)  
**Fecha:** Marzo 2026  

---

## 🎉 Buenas noticias: simplificación del desarrollo local

Después de revisar las prácticas recomendadas por la comunidad Filament, hemos decidido **simplificar la forma en que trabajamos localmente**.

### ✅ Lo que cambia

**A partir de ahora, para desarrollo diario solo necesitas:**

```bash
php artisan serve
# o con Sail: ./vendor/bin/sail up
```

Y ambos paneles estarán disponibles en:

- **Admin:** http://localhost:8000/admin  
- **Cliente:** http://localhost:8000/cliente  

**APP_MODULE** ya no es necesario en desarrollo. Déjalo vacío en tu `.env` (o no lo definas).

### 🔒 La seguridad sigue igual

- Usuarios tipo `user` solo pueden acceder a `/admin`.
- Usuarios tipo `admin` solo pueden acceder a `/cliente`.
- Si intentan cambiar la URL manualmente, recibirán **403** o serán redirigidos.
- `canAccessPanel`, middlewares y políticas funcionan igual.

### 🧪 Para pruebas de QA (simular producción)

Hemos creado scripts opcionales en `scripts/`:

- **Mac/Linux:** `./scripts/serve-qa.sh`
- **Windows:** `.\scripts\serve-qa.ps1`

### 📚 Documentación actualizada

Toda la información está en **docs/desarrollo/README.md**.

---

## ❓ Preguntas frecuentes

**¿Puedo seguir usando APP_MODULE si quiero?**  
Sí, pero ya no es necesario. Si lo dejas vacío, todo funciona con un solo servidor.

**¿Esto afecta a producción?**  
No. En producción/CI seguiremos usando APP_MODULE con servidores separados.

**¿Qué pasa si quiero probar el comportamiento con dos servidores?**  
Usa los scripts de QA en `scripts/` (ver scripts/README.md).

---

¡A desarrollar sin fricción! 🚀  

Cualquier duda, avísenme.
