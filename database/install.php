<?php
/**
 * SIGEP - Instalador
 * Crea la base de datos, las tablas y los datos iniciales.
 *
 * Uso: php database/install.php
 */

require_once dirname(__DIR__) . '/config/config.php';

echo "=============================================\n";
echo "  SIGEP - Instalación de base de datos\n";
echo "=============================================\n\n";

// 1. Conectar a MySQL sin base de datos
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[OK] Conexión a MySQL\n";
} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar a MySQL: " . $e->getMessage() . "\n";
    echo "Verifica que MySQL esté iniciado en el panel de XAMPP.\n";
    exit(1);
}

// 2. Crear la base de datos
$pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `" . DB_NAME . "`");
echo "[OK] Base de datos '" . DB_NAME . "' creada/verificada\n";

// 3. Ejecutar el esquema
$schema = file_get_contents(__DIR__ . '/schema.sql');
if ($schema === false) {
    echo "[ERROR] No se encontró el archivo schema.sql\n";
    exit(1);
}

// Eliminar líneas de comentario para poder dividir por punto y coma
$cleanLines = [];
foreach (preg_split('/\R/', $schema) as $line) {
    $t = trim($line);
    if ($t === '' || str_starts_with($t, '--')) {
        continue;
    }
    $cleanLines[] = $line;
}
$statements = array_filter(
    array_map('trim', explode(';', implode("\n", $cleanLines))),
    fn ($s) => $s !== ''
);

foreach ($statements as $sql) {
    $pdo->exec($sql);
}
echo "[OK] " . count($statements) . " sentencias SQL ejecutadas\n";

// 4. Datos iniciales
seed($pdo);

echo "\n=============================================\n";
echo "  Instalación completada correctamente\n";
echo "=============================================\n";

/**
 * Inserta los datos iniciales si las tablas están vacías.
 */
function seed(PDO $pdo): void
{
    // Roles
    $roles = [
        ['admin', 'Administrador del sistema'],
        ['estudiante', 'Estudiante'],
        ['docente', 'Docente / Tutor'],
    ];
    $count = (int) $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?)");
        foreach ($roles as $r) {
            $stmt->execute($r);
        }
        echo "[OK] Roles creados (admin, estudiante, docente)\n";
    } else {
        echo "[INFO] Roles ya existían\n";
    }

    // Usuario administrador por defecto
    $exists = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE email = 'admin@sigep.edu.ec'")->fetchColumn();
    if ((int) $exists === 0) {
        $rolId = $pdo->query("SELECT id FROM roles WHERE nombre = 'admin'")->fetchColumn();
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (rol_id, nombres, apellidos, email, password, estado)
             VALUES (?, ?, ?, ?, ?, 'activo')"
        );
        $stmt->execute([
            $rolId,
            'Administrador',
            'del Sistema',
            'admin@sigep.edu.ec',
            password_hash('Admin123', PASSWORD_BCRYPT),
        ]);
        echo "[OK] Usuario administrador creado: admin@sigep.edu.ec / Admin123\n";
        echo "     ¡Cámbialo después del primer inicio de sesión!\n";
    } else {
        echo "[INFO] El administrador ya existía\n";
    }

    // Tipos de proyecto y etapas
    $tipos = [
        'Titulación' => [
            'Perfil', 'Capítulo I', 'Capítulo II', 'Capítulo III', 'Capítulo IV',
            'Conclusiones', 'Recomendaciones', 'Bibliografía', 'Anexos',
        ],
        'Vinculación' => [
            'Perfil', 'Diagnóstico', 'Planificación', 'Ejecución',
            'Resultados', 'Conclusiones', 'Anexos',
        ],
        'PIS' => [
            'Diagnóstico', 'Planificación', 'Desarrollo', 'Resultados', 'Conclusiones', 'Anexos',
        ],
    ];

    $count = (int) $pdo->query("SELECT COUNT(*) FROM tipos_proyecto")->fetchColumn();
    if ($count === 0) {
        $stmtTipo = $pdo->prepare("INSERT INTO tipos_proyecto (nombre, descripcion) VALUES (?, ?)");
        $stmtEtapa = $pdo->prepare(
            "INSERT INTO etapas (tipo_proyecto_id, nombre, orden) VALUES (?, ?, ?)"
        );
        foreach ($tipos as $nombre => $etapas) {
            $stmtTipo->execute([$nombre, 'Proyectos de ' . $nombre]);
            $tipoId = (int) $pdo->lastInsertId();
            foreach ($etapas as $i => $etapa) {
                $stmtEtapa->execute([$tipoId, $etapa, $i + 1]);
            }
            echo "[OK] Tipo de proyecto '{$nombre}' con " . count($etapas) . " etapas\n";
        }
    } else {
        echo "[INFO] Los tipos de proyecto ya existían\n";
    }
}
