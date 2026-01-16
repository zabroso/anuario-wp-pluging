# Anuario

Plugin WordPress para gestión de alumni.

## Funcionalidades
- CRUD de Alumni
- Búsqueda por nombre
- API REST pública
- Carga masiva CSV
  - Añadir datos
  - Reescribir base de datos
- Exportación CSV (respaldo)

## Estructura de datos
- nombre
- cargo_actual
- redes_sociales (JSON)
- nivel_exito
- fecha_egreso
- foto

## API
GET /wp-json/anuario/v1/alumni
