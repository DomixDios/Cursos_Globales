-- ============================================================
-- Cursos Globales - Esquema de Base de Datos SQLite
-- ============================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre_completo TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    rol TEXT NOT NULL DEFAULT 'estudiante'
        CHECK(rol IN ('admin', 'moderador', 'profesor', 'estudiante')),
    avatar TEXT,
    bio TEXT,
    activo INTEGER NOT NULL DEFAULT 1,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    descripcion TEXT,
    icono TEXT,
    activo INTEGER NOT NULL DEFAULT 1,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cursos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    profesor_id INTEGER NOT NULL,
    categoria_id INTEGER,
    titulo TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    descripcion TEXT,
    descripcion_corta TEXT,
    miniatura TEXT,
    precio REAL NOT NULL DEFAULT 0,
    nivel TEXT DEFAULT 'principiante'
        CHECK(nivel IN ('principiante', 'intermedio', 'avanzado', 'todos')),
    estado TEXT DEFAULT 'borrador'
        CHECK(estado IN ('borrador', 'pendiente', 'aprobado', 'rechazado', 'publicado')),
    destacado INTEGER DEFAULT 0,
    motivo_rechazo TEXT,
    aprobado_por INTEGER,
    aprobado_en DATETIME,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (aprobado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS modulos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    curso_id INTEGER NOT NULL,
    titulo TEXT NOT NULL,
    descripcion TEXT,
    orden INTEGER NOT NULL DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS clases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    modulo_id INTEGER NOT NULL,
    titulo TEXT NOT NULL,
    descripcion TEXT,
    url_video TEXT,
    duracion INTEGER DEFAULT 0,
    tipo_contenido TEXT DEFAULT 'video'
        CHECK(tipo_contenido IN ('video', 'articulo', 'cuestionario', 'recurso')),
    texto_contenido TEXT,
    orden INTEGER NOT NULL DEFAULT 0,
    gratuito INTEGER DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS inscripciones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    curso_id INTEGER NOT NULL,
    progreso REAL DEFAULT 0,
    inscrito_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    completado_en DATETIME,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    UNIQUE(usuario_id, curso_id)
);

CREATE TABLE IF NOT EXISTS progreso_clases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    inscripcion_id INTEGER NOT NULL,
    clase_id INTEGER NOT NULL,
    completado INTEGER DEFAULT 0,
    completado_en DATETIME,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id) ON DELETE CASCADE,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE,
    UNIQUE(inscripcion_id, clase_id)
);

CREATE TABLE IF NOT EXISTS resenas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    curso_id INTEGER NOT NULL,
    puntuacion INTEGER NOT NULL CHECK(puntuacion >= 1 AND puntuacion <= 5),
    comentario TEXT,
    aprobado INTEGER DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    UNIQUE(usuario_id, curso_id)
);

CREATE TABLE IF NOT EXISTS comentarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    curso_id INTEGER NOT NULL,
    clase_id INTEGER,
    padre_id INTEGER,
    contenido TEXT NOT NULL,
    aprobado INTEGER DEFAULT 1,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE SET NULL,
    FOREIGN KEY (padre_id) REFERENCES comentarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pagos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    inscripcion_id INTEGER NOT NULL UNIQUE,
    monto REAL NOT NULL,
    ganancias_profesor REAL NOT NULL,
    comision REAL NOT NULL DEFAULT 0,
    metodo_pago TEXT DEFAULT 'transferencia',
    estado TEXT DEFAULT 'completado'
        CHECK(estado IN ('pendiente', 'completado', 'reembolsado')),
    pagado_profesor INTEGER DEFAULT 0,
    pagado_en DATETIME,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_cursos_profesor   ON cursos(profesor_id);
CREATE INDEX IF NOT EXISTS idx_cursos_estado     ON cursos(estado);
CREATE INDEX IF NOT EXISTS idx_cursos_categoria  ON cursos(categoria_id);
CREATE INDEX IF NOT EXISTS idx_modulos_curso     ON modulos(curso_id);
CREATE INDEX IF NOT EXISTS idx_clases_modulo     ON clases(modulo_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_usuario  ON inscripciones(usuario_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_curso ON inscripciones(curso_id);
CREATE INDEX IF NOT EXISTS idx_progreso_clases_inscripcion ON progreso_clases(inscripcion_id);
CREATE INDEX IF NOT EXISTS idx_resenas_curso     ON resenas(curso_id);
CREATE INDEX IF NOT EXISTS idx_comentarios_curso ON comentarios(curso_id);
CREATE INDEX IF NOT EXISTS idx_comentarios_clase  ON comentarios(clase_id);
CREATE INDEX IF NOT EXISTS idx_pagos_inscripcion ON pagos(inscripcion_id);
