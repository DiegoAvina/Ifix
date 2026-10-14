# Catálogo Lunar e importación de Mercado Libre

## Auditoría del entorno

- Instalación existente: Laravel 12.62.0, PHP 8.3.30 y Lunar Core/Panel 1.3.0. No se reinstalaron ni actualizaron dependencias.
- Lunar ya tiene MXN predeterminado con 2 decimales, el canal `Webstore`, español (`es`) y una zona fiscal predeterminada.
- El seeder de zona fiscal asocia países mediante el modelo oficial de Lunar; tras ejecutarlo, México quedó asociado a la zona predeterminada. No se añadió una tasa fiscal: requiere validación contable.
- El almacenamiento de Media Library usa el disco `public`; el disco predeterminado de Laravel es local. No se descargan imágenes del marketplace.
- La telemetría de Lunar está desactivada. No se encontró una excepción Carbon activa; el log conserva errores históricos de tabla `cache` ausente, pero esa tabla existe y las pruebas de `SafeTelemetryService` pasan.
- La base existente contenía cuatro registros del seeder de demostración publicados, sin datos comerciales verificados. Una migración los pone en borrador sin borrar productos ni alterar SKU, precios o existencias. El seeder normal dejó de volver a crearlos o actualizarlos. Se pueden ejecutar explícitamente en pruebas, no como catálogo real.
- Las páginas y el API público de Mercado Libre consultados respondieron HTTP 403. No se intentó sortear el bloqueo. Por ello, no se importó ni se afirma como verificado ningún producto real.

## Preparar la base

Aplicar las migraciones aditivas y sembrar la configuración comercial reutilizable:

```bash
php artisan migrate
php artisan db:seed --class=DatabaseSeeder
```

La configuración reutiliza los registros existentes y prepara idioma, canal, moneda, grupo de colecciones, tipo de producto y marca IFIX solo cuando hacen falta. No configura tasas fiscales, impuestos, pagos ni tarifas de envío por conjetura.

## Flujo de importación

La plantilla vacía y versionada está en `database/imports/mercadolibre-catalog-template.csv`. Cópiala a `storage/app/imports/mercadolibre-catalog.csv` para completar los datos localmente. Cada fila representa una publicación/SKU estable, y `category_path` permite jerarquías separadas por `>`. Los precios observados de Mercado Libre y los precios comerciales propios de iFix tienen campos distintos. El comando no consulta URLs, no descarga imágenes y no convierte el precio de marketplace en precio de venta.

1. Completar el CSV únicamente con información comprobada en una fuente permitida.
2. Marcar `data_verified`, `stock_verified` e `image_rights_verified` solo después de revisar esos datos. El inventario y el precio propios de iFix son independientes de los observados.
3. Importar al registro de revisión, de forma idempotente:

   ```bash
   php artisan ifix:catalog:import storage/app/imports/mercadolibre-catalog.csv
   ```

4. Cuando estén definidos el precio propio, el inventario verificado y los datos de producto, generar el producto Lunar inicialmente como borrador:

   ```bash
   php artisan ifix:catalog:import storage/app/imports/mercadolibre-catalog.csv --publish
   ```

5. Cargar en el panel una imagen con derechos de uso confirmados y volver a ejecutar el comando con `--publish`. Solo entonces puede pasar a publicado. El SKU ya existente se omite para evitar duplicados y nunca se sobrescriben el precio o inventario de un producto Lunar vinculado.

El reporte de consola muestra registros creados, actualizados, omitidos y fallidos, además de productos Lunar creados y publicados. Los motivos de revisión quedan en `ifix_catalog_imports`. Las imágenes de referencia solo se registran; no se solicitan al servidor externo.

## Estado del catálogo y pendientes

- No hay publicaciones importadas porque Mercado Libre bloqueó el acceso automatizado y no se proporcionó una exportación verificable.
- La plantilla se deja sin filas para no inventar productos, SKU, precios, cantidades ni imágenes.
- Los cuatro productos de demostración existentes se conservan como borradores. Su precio e inventario siguen intactos y no deben tratarse como datos oficiales.
- No se añadieron tasas de IVA ni otras reglas fiscales. Confirmar configuración con el responsable contable.
- Revisar credenciales y webhook de Mercado Pago, reglas de envío, costos y cobertura antes de habilitar ventas reales.
