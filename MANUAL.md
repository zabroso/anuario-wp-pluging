# Manual de uso — Plugin Anuario Alumni
**Universidad Técnica Federico Santa María**

Este manual describe paso a paso cómo utilizar cada sección del plugin de gestión de alumni desde el panel de administración de WordPress.

---

## Índice

1. [Anuario Alumni — Gestión de estudiantes](#1-anuario-alumni--gestión-de-estudiantes)
2. [Comentarios — Gestión de comentarios](#2-comentarios--gestión-de-comentarios)
3. [Programas Académicos — Gestión de programas](#3-programas-académicos--gestión-de-programas)
4. [Carga masiva — Importación de datos](#4-carga-masiva--importación-de-datos)

---

## 1. Anuario Alumni — Gestión de estudiantes

Esta es la vista principal del plugin. Desde aquí se puede ver, buscar, crear, editar y eliminar alumni.

### Cómo acceder
En el menú lateral de WordPress, hacer clic en **Anuario Alumni**.

---

### Ver y buscar alumni

1. Al ingresar se muestra una tabla con todos los alumni registrados (20 por página).
2. Para buscar, utilizar la barra de filtros en la parte superior:
   - **Campo de texto**: busca por nombre o cargo.
   - **Campo RUT**: busca por RUT del alumni.
   - **Egreso desde / hasta**: filtra por rango de año de egreso. El año "desde" no puede ser mayor que el año "hasta".
   - **Nivel**: filtra por nivel de cargo (1 al 10).
3. Hacer clic en **Filtrar** para aplicar los filtros.
4. Para quitar los filtros, hacer clic en **Limpiar filtros**.
5. Si hay más de 20 resultados, usar los botones de paginación al final de la tabla.

---

### Ver el detalle de un alumni

1. En la columna **Acciones** de la tabla, hacer clic en **Ver**.
2. Se abrirá una vista de detalle con:
   - **Izquierda**: todos los datos del alumni (RUT, cargo, empresa, LinkedIn, nivel, año de egreso, fecha de nacimiento, autorización pública).
   - **Derecha arriba**: comentarios registrados para ese alumni.
   - **Derecha abajo**: programas académicos del alumni.
3. Desde esta vista se puede acceder directamente a **Editar** el alumni o gestionar sus comentarios y programas mediante los botones correspondientes.

---

### Crear un alumni

1. Hacer clic en el botón **Crear Alumni** (junto al título de la página).
2. Completar el formulario:
   - **Nombre** es el único campo obligatorio.
   - Los campos opcionales (cargo, empresa, LinkedIn, foto, nivel, año de egreso) se habilitan marcando la casilla **Posee?** junto a cada uno.
3. Al finalizar, marcar la casilla de **confirmación** al pie del formulario.
4. Hacer clic en **Crear Alumni**.

> El alumni quedará registrado y el formulario mostrará un mensaje de confirmación.

---

### Editar un alumni

1. En la columna **Acciones** de la tabla, hacer clic en **Editar**.
2. Modificar los campos deseados en el formulario.
3. Marcar la casilla de **confirmación**.
4. Hacer clic en **Actualizar Alumni**.

---

### Eliminar un alumni

**Eliminar uno:**
1. En la columna **Acciones**, hacer clic en **Eliminar**.
2. Confirmar la acción en el diálogo que aparece.

**Eliminar varios a la vez:**
1. Marcar las casillas de los alumni que se desea eliminar. Se puede usar la casilla del encabezado para seleccionar todos.
2. Hacer clic en el botón **Eliminar seleccionados**.
3. Confirmar la acción en el diálogo que aparece.

> ⚠️ La eliminación de un alumni es permanente y no se puede deshacer.

---

## 2. Comentarios — Gestión de comentarios

Desde esta vista se pueden ver, crear y eliminar comentarios asociados a los alumni.

### Cómo acceder
- Desde el menú lateral: **Anuario Alumni → Comentarios**.
- Directamente desde la tabla de alumni: hacer clic en **Comentarios** en la columna de acciones de un alumni específico. La vista se abrirá filtrada para ese alumno.

---

### Ver comentarios

1. La tabla muestra todos los comentarios registrados con el nombre del alumni, su RUT, el texto del comentario y la fecha.
2. Para filtrar por alumni, usar el selector desplegable en la parte superior y hacer clic en **Filtrar**.
3. Para ver todos los comentarios nuevamente, hacer clic en **Limpiar filtro**.

---

### Crear un comentario

1. En la parte superior de la página se encuentra el formulario **Agregar comentario**.
2. Escribir en el campo de búsqueda para encontrar al alumni por nombre o RUT. El selector se filtra automáticamente mientras se escribe.
3. Seleccionar el alumni en el desplegable.
4. Escribir el comentario en el campo de texto.
5. Marcar la casilla de **confirmación**.
6. Hacer clic en **Guardar comentario**.

---

### Eliminar un comentario

**Eliminar uno:**
1. En la columna **Acciones** de la tabla, hacer clic en **Eliminar**.
2. Confirmar la acción en el diálogo que aparece.

**Eliminar varios a la vez:**
1. Marcar las casillas de los comentarios que se desea eliminar. Se puede usar la casilla del encabezado para seleccionar todos.
2. Hacer clic en el botón **Eliminar seleccionados**.
3. Se abrirá un modal indicando cuántos comentarios se eliminarán. Hacer clic en **Sí, eliminar** para confirmar, o en **Cancelar** para volver atrás.

> ⚠️ La eliminación de comentarios es permanente y no se puede deshacer.

---

## 3. Programas Académicos — Gestión de programas

Desde esta vista se pueden ver, crear y eliminar los programas académicos cursados por cada alumni.

### Cómo acceder
- Desde el menú lateral: **Anuario Alumni → Programas**.
- Desde la vista de detalle de un alumni, hacer clic en **Gestionar** dentro del bloque de programas.

---

### Ver programas

1. La tabla muestra todos los programas registrados con el nombre del alumni, su RUT, el nombre del programa, el nivel académico y el campus.
2. Para filtrar por alumni, usar el selector y hacer clic en **Filtrar**.
3. Para ver todos los programas nuevamente, hacer clic en **Limpiar filtro**.

---

### Crear un programa

1. En la parte superior de la página se encuentra el formulario **Agregar programa**.
2. Buscar el alumni por nombre o RUT usando el campo de búsqueda. El selector se filtra automáticamente.
3. Seleccionar el alumni en el desplegable.
4. Ingresar el **nombre del programa** (por ejemplo: *Ingeniería Civil Informática*).
5. Seleccionar el **nivel académico**: Pregrado, Diplomado, Magíster o Doctorado.
6. Opcionalmente, ingresar el **campus** donde se cursó el programa.
7. Marcar la casilla de **confirmación**.
8. Hacer clic en **Guardar programa**.

---

### Eliminar un programa

**Eliminar uno:**
1. En la columna **Acciones** de la tabla, hacer clic en **Eliminar**.
2. Confirmar la acción en el diálogo que aparece.

**Eliminar varios a la vez:**
1. Marcar las casillas de los programas que se desea eliminar.
2. Hacer clic en el botón **Eliminar seleccionados**.
3. Confirmar en el modal que aparece.

> ⚠️ La eliminación de programas es permanente y no se puede deshacer.

---

## 4. Carga masiva — Importación de datos

La carga masiva permite importar grandes volúmenes de datos desde archivos CSV. Está disponible para alumni, comentarios y programas.

### Cómo acceder
Desde el menú lateral: **Anuario Alumni → Carga masiva**.

---

### Descargar respaldo antes de importar

Antes de realizar cualquier importación, se recomienda descargar un respaldo del estado actual:

1. En la sección **Descargar respaldo**, hacer clic en el botón correspondiente:
   - **Descargar respaldo CSV (Alumni)**
   - **Descargar respaldo CSV (Comentarios)**
   - **Descargar respaldo CSV (Programas)**
2. El archivo se descargará automáticamente y puede reimportarse en cualquier momento.

---

### Descargar plantilla de ejemplo

En la columna derecha de la página se muestran los bloques de referencia con el formato exacto requerido por cada CSV. Cada bloque incluye un botón para descargar la plantilla:

- **Descargar plantilla Alumni**
- **Descargar plantilla Comentarios**
- **Descargar plantilla Programas**

Las plantillas contienen los encabezados correctos y una fila de ejemplo lista para reemplazar con datos reales.

---

### Importar alumni

El archivo CSV debe tener exactamente estas columnas en este orden:

| Columna | Descripción | Ejemplo |
|---|---|---|
| `nombre` | Nombre completo | Juan Pérez |
| `rut` | RUT con formato | 12.345.678-9 |
| `fecha_nacimiento` | Fecha en formato YYYY-MM-DD | 1992-03-15 |
| `cargo_actual` | Cargo profesional actual | Ingeniero de Software |
| `empresa` | Empresa donde trabaja | Google |
| `perfil_linkedin` | URL del perfil de LinkedIn | https://linkedin.com/in/... |
| `nivel_cargo` | Número del 1 al 10 | 5 |
| `ano_egreso` | Año de egreso | 2020 |
| `link_foto` | URL de foto de perfil (puede ir vacío) | https://... |
| `autorizacion_publica` | 1 para sí, 0 para no | 1 |

**Pasos:**
1. En la sección **Importar Alumni**, seleccionar el archivo CSV.
2. Elegir el modo:
   - **Añadir registros**: agrega los alumni del CSV sin borrar los existentes.
   - **Reemplazar todos los datos**: elimina todos los alumni actuales antes de importar. Requiere marcar 3 casillas de confirmación.
3. Hacer clic en **Ejecutar carga de Alumni**.
4. Se mostrará un mensaje indicando cuántos alumni fueron importados.

---

### Importar comentarios

El archivo CSV debe tener exactamente estas columnas en este orden:

| Columna | Descripción | Ejemplo |
|---|---|---|
| `rut` | RUT del alumni al que pertenece el comentario | 12.345.678-9 |
| `comentario` | Texto del comentario | Excelente profesional... |

> El RUT debe coincidir exactamente con el de un alumni ya registrado. Las filas con RUT no encontrado serán omitidas.

**Pasos:**
1. En la sección **Importar Comentarios**, seleccionar el archivo CSV.
2. Elegir el modo (Añadir o Reemplazar).
3. Hacer clic en **Ejecutar carga de Comentarios**.
4. Se mostrará un mensaje indicando cuántos comentarios fueron importados y cuántos fueron omitidos.

---

### Importar programas

El archivo CSV debe tener exactamente estas columnas en este orden:

| Columna | Descripción | Ejemplo |
|---|---|---|
| `rut` | RUT del alumni | 12.345.678-9 |
| `nombre` | Nombre del programa | Ingeniería Civil Informática |
| `nivel_academico` | Nivel del programa | pregrado |
| `campus` | Campus donde se cursó (puede ir vacío) | Casa Central Valparaíso |

> Los valores válidos para `nivel_academico` son exactamente: `pregrado`, `diplomado`, `magister`, `doctorado`. Las filas con un valor distinto serán omitidas.

**Pasos:**
1. En la sección **Importar Programas**, seleccionar el archivo CSV.
2. Elegir el modo (Añadir o Reemplazar).
3. Hacer clic en **Ejecutar carga de Programas**.
4. Se mostrará un mensaje indicando cuántos programas fueron importados y cuántos fueron omitidos.

---

## Notas generales

- Todos los formularios de creación y edición requieren marcar una casilla de confirmación antes de guardar. Esto previene envíos accidentales.
- Las eliminaciones masivas muestran siempre un modal de confirmación indicando la cantidad de registros afectados.
- El modo **Reemplazar** en la carga masiva elimina permanentemente todos los datos existentes de esa tabla antes de importar. Se recomienda siempre descargar un respaldo antes de usarlo.
- Un alumni puede tener múltiples comentarios y múltiples programas académicos asociados.
