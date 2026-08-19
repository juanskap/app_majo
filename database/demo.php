<?php
/**
 * SIGEP - Datos de demostración
 * Crea proyectos, documentos, observaciones, actividades, plazos,
 * eventos de calendario y notificaciones de ejemplo para mostrar la app "viva".
 *
 * Uso: php database/demo.php
 * Se puede ejecutar varias veces: salta lo que ya existe.
 */

require_once dirname(__DIR__) . '/config/config.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "== SIGEP - Datos de demostración ==\n\n";

// ---------- Referencias ----------
$estudiante = $pdo->query("SELECT u.id AS uid, e.id AS est, e.codigo FROM estudiantes e JOIN usuarios u ON u.id = e.usuario_id WHERE e.codigo = '2025-0001'")->fetch();
if (!$estudiante) {
    echo "[ERROR] No existe el estudiante con código 2025-0001. Crea a María José (estudiante@sigep.edu.ec) antes de ejecutar.\n";
    exit(1);
}
$docente = $pdo->query("SELECT u.id AS uid, d.id AS doc FROM docentes d JOIN usuarios u ON u.id = d.usuario_id WHERE u.email = 'docente@sigep.edu.ec'")->fetch();
if (!$docente) {
    echo "[ERROR] No existe el docente docente@sigep.edu.ec.\n";
    exit(1);
}

$tipo = fn (string $n) => (int) $pdo->query("SELECT id FROM tipos_proyecto WHERE nombre = '$n'")->fetchColumn();
$tipoTitulacion = $tipo('Titulación');
$tipoVinculacion = $tipo('Vinculación');
$tipoPis = $tipo('PIS');
$etapasDe = fn (int $t) => $pdo->query("SELECT id, nombre, orden FROM etapas WHERE tipo_proyecto_id = $t ORDER BY orden")->fetchAll();

function proyectoExiste(PDO $pdo, string $codigo): bool {
    return (bool) $pdo->query("SELECT 1 FROM proyectos WHERE codigo = '$codigo'")->fetchColumn();
}

function crearArchivo(string $dir, string $nombre, string $contenido): void {
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    if (!file_exists($dir . '/' . $nombre)) {
        file_put_contents($dir . '/' . $nombre, $contenido);
    }
}
function insertDoc(PDO $pdo, int $proyecto, ?int $etapa, string $tipo, string $nombre, string $ruta, int $version, int $por, string $estado, string $creado): void {
    $pdo->prepare("INSERT INTO documentos (proyecto_id, etapa_id, tipo, nombre_original, ruta, version, subido_por, estado, creado_en)
                   VALUES (?,?,?,?,?,?,?,?,?)")->execute([$proyecto, $etapa, $tipo, $nombre, $ruta, $version, $por, $estado, $creado]);
}

// ---------- Proyecto 1: Titulación (en revisión, ~56%) ----------
if (!proyectoExiste($pdo, 'PRJ-2026-0001')) {
    $etapas = $etapasDe($tipoTitulacion);
    $pId = (int) $pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM proyectos")->fetchColumn();
    $pdo->prepare("INSERT INTO proyectos (id, codigo, nombre, descripcion, tipo_proyecto_id, estudiante_id, fecha_creacion, fecha_inicio, fecha_limite, estado, etapa_actual_id, porcentaje_avance)
                   VALUES (?, 'PRJ-2026-0001', ?, ?, ?, ?, '2026-03-03 09:00:00', '2026-03-10', '2026-11-30', 'en_revision', ?, 55.56)")
        ->execute([$pId, 'Sistema web para la gestión de prácticas preprofesionales', 'Desarrollo de una plataforma web que automatiza el registro y seguimiento de las prácticas preprofesionales de la facultad.', $tipoTitulacion, $estudiante['est'], $etapas[2]['id']]);
    $pdo->prepare("INSERT INTO asignaciones (proyecto_id, docente_id, fecha_asignacion, estado) VALUES (?, ?, '2026-03-04 10:00:00', 'activa')")->execute([$pId, $docente['doc']]);

    // Etapa 1 (Perfil) aprobada -> final archivado
    $r1 = 'p' . $pId . '_e' . $etapas[0]['id'] . '_v1_demo01.txt';
    crearArchivo(UPLOAD_DOCUMENTOS, $r1, "Perfil del proyecto - Sistema de prácticas preprofesionales.");
    insertDoc($pdo, $pId, (int) $etapas[0]['id'], 'trabajo', 'perfil_proyecto.txt', $r1, 1, $estudiante['uid'], 'aprobado', '2026-03-10 11:00:00');
    $rf = 'final_p' . $pId . '_e' . $etapas[0]['id'] . '_demo01.txt';
    crearArchivo(UPLOAD_FINALES, $rf, "PERFIL FINAL aprobado.");
    insertDoc($pdo, $pId, (int) $etapas[0]['id'], 'final', 'perfil_proyecto_final.txt', $rf, 1, $docente['uid'], 'final', '2026-04-02 10:00:00');

    // Etapa 2 (Capítulo I) en revisión
    $r2 = 'p' . $pId . '_e' . $etapas[1]['id'] . '_v1_demo01.txt';
    crearArchivo(UPLOAD_DOCUMENTOS, $r2, "Capitulo I - Marco teorico.");
    insertDoc($pdo, $pId, (int) $etapas[1]['id'], 'trabajo', 'capitulo_I.txt', $r2, 1, $estudiante['uid'], 'en_revision', '2026-04-15 09:00:00');

    // Etapa 3 (Capítulo II) con observación y respuesta
    $r3 = 'p' . $pId . '_e' . $etapas[2]['id'] . '_v1_demo01.txt';
    crearArchivo(UPLOAD_DOCUMENTOS, $r3, "Capitulo II - Analisis y diseno.");
    insertDoc($pdo, $pId, (int) $etapas[2]['id'], 'trabajo', 'capitulo_II.txt', $r3, 1, $estudiante['uid'], 'con_observaciones', '2026-05-20 09:00:00');

    // Observación en Capítulo II con respuesta
    $doc3 = $pdo->query("SELECT id FROM documentos WHERE proyecto_id = $pId AND etapa_id = {$etapas[2]['id']} AND tipo = 'trabajo'")->fetchColumn();
    $pdo->prepare("INSERT INTO observaciones (documento_id, proyecto_id, usuario_id, texto_seleccionado, comentario, estado) VALUES (?,?,?,?,?, 'pendiente')")
        ->execute([(int) $doc3, $pId, $docente['uid'], 'la arquitectura se apoya en PHP con patrón MVC', 'La arquitectura descrita no detalla cómo se resolverá la concurrencia de usuarios en el módulo de prácticas. Amplía esa sección.']);
    $obsId = (int) $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO respuestas_observaciones (observacion_id, usuario_id, mensaje, tipo) VALUES (?,?,?,'correccion')")
        ->execute([$obsId, $estudiante['uid'], 'Corregido: se incluyó el manejo de sesiones y bloqueo optimista en el esquema de la base de datos.']);
    $pdo->prepare("UPDATE observaciones SET estado = 'en_correccion' WHERE id = ?")->execute([$obsId]);

    echo "[OK] Proyecto 1 (Titulación) creado\n";
}

// ---------- Proyecto 2: Vinculación (con observaciones, ~28%) ----------
if (!proyectoExiste($pdo, 'PRJ-2026-0002')) {
    $etapas = $etapasDe($tipoVinculacion);
    $pId = (int) $pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM proyectos")->fetchColumn();
    $pdo->prepare("INSERT INTO proyectos (id, codigo, nombre, descripcion, tipo_proyecto_id, estudiante_id, fecha_creacion, fecha_inicio, fecha_limite, estado, etapa_actual_id, porcentaje_avance)
                   VALUES (?, 'PRJ-2026-0002', ?, ?, ?, ?, '2026-04-12 09:00:00', '2026-04-20', '2026-10-15', 'con_observaciones', ?, 28.57)")
        ->execute([$pId, 'Jornadas de alfabetización digital para adultos mayores', 'Talleres de alfabetización digital dirigidos a adultos mayores del sector urbano, en el marco de vinculación con la sociedad.', $tipoVinculacion, $estudiante['est'], $etapas[1]['id']]);
    $pdo->prepare("INSERT INTO asignaciones (proyecto_id, docente_id, fecha_asignacion, estado) VALUES (?, ?, '2026-04-15 10:00:00', 'activa')")->execute([$pId, $docente['doc']]);

    $r1 = 'p' . $pId . '_e' . $etapas[0]['id'] . '_v1_demo02.txt';
    crearArchivo(UPLOAD_DOCUMENTOS, $r1, "Perfil del proyecto de vinculacion.");
    insertDoc($pdo, $pId, (int) $etapas[0]['id'], 'trabajo', 'perfil_vinculacion.txt', $r1, 1, $estudiante['uid'], 'enviado', '2026-04-25 09:00:00');

    $r2 = 'p' . $pId . '_e' . $etapas[1]['id'] . '_v1_demo02.txt';
    crearArchivo(UPLOAD_DOCUMENTOS, $r2, "Diagnostico - Encuesta aplicada a 120 adultos mayores.");
    insertDoc($pdo, $pId, (int) $etapas[1]['id'], 'trabajo', 'diagnostico.txt', $r2, 2, $estudiante['uid'], 'en_correccion', '2026-05-18 09:00:00');

    $doc2 = $pdo->query("SELECT id FROM documentos WHERE proyecto_id = $pId AND etapa_id = {$etapas[1]['id']} AND tipo = 'trabajo'")->fetchColumn();
    $pdo->prepare("INSERT INTO observaciones (documento_id, proyecto_id, usuario_id, texto_seleccionado, comentario, estado) VALUES (?,?,?,?,?, 'en_correccion')")
        ->execute([(int) $doc2, $pId, $docente['uid'], 'los adultos mayores prefieren sesiones presenciales', 'La muestra indica que los adultos mayores prefieren las sesiones presenciales. Ajusta la modalidad propuesta en la metodología y actualiza el cronograma.']);
    $obsId = (int) $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO respuestas_observaciones (observacion_id, usuario_id, mensaje, tipo) VALUES (?,?,?, 'correccion')")
        ->execute([$obsId, $estudiante['uid'], 'Corregido: la metodología ahora contempla 8 sesiones presenciales semanales y se actualizó el cronograma.']);

    echo "[OK] Proyecto 2 (Vinculación) creado\n";
}

// ---------- Proyecto 3: PIS (borrador, sin tutor) ----------
if (!proyectoExiste($pdo, 'PRJ-2026-0003')) {
    $etapas = $etapasDe($tipoPis);
    $pId = (int) $pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM proyectos")->fetchColumn();
    $pdo->prepare("INSERT INTO proyectos (id, codigo, nombre, descripcion, tipo_proyecto_id, estudiante_id, fecha_creacion, estado, etapa_actual_id, porcentaje_avance)
                   VALUES (?, 'PRJ-2026-0003', ?, ?, ?, ?, '2026-06-30 09:00:00', 'borrador', ?, 0.00)")
        ->execute([$pId, 'Diagnóstico de contaminación acústica en el centro urbano', 'Medición y análisis de niveles de ruido ambiental en la zona céntrica de la ciudad.', $tipoPis, $estudiante['est'], $etapas[0]['id']]);
    echo "[OK] Proyecto 3 (PIS) creado\n";
}

// ---------- Actividades y plazos (Proyecto 1) ----------
$p1 = $pdo->query("SELECT id FROM proyectos WHERE codigo = 'PRJ-2026-0001'")->fetchColumn();
if ($p1 && (int) $pdo->query("SELECT COUNT(*) FROM actividades WHERE proyecto_id = $p1")->fetchColumn() === 0) {
    $insAct = $pdo->prepare("INSERT INTO actividades (proyecto_id, etapa_id, tipo, descripcion, responsable_id, fecha_inicio, fecha_limite, estado) VALUES (?, NULL, ?, ?, ?, ?, ?, ?)");
    $insAct->execute([$p1, 'entrega', 'Elaborar el perfil del proyecto', $estudiante['uid'], '2026-03-15', '2026-04-05', 'completada']);
    $insAct->execute([$p1, 'revision', 'Revisar el Capítulo I por el tutor', $docente['uid'], '2026-04-16', '2026-04-30', 'en_curso']);
    $insAct->execute([$p1, 'entrega', 'Entregar el Capítulo III', $estudiante['uid'], '2026-07-01', date('Y-m-d', strtotime('+12 days')), 'pendiente']);
    $insAct->execute([$p1, 'correccion', 'Corregir observaciones del Capítulo II', $estudiante['uid'], '2026-06-01', '2026-06-10', 'vencida']);

    $insPlazo = $pdo->prepare("INSERT INTO plazos (proyecto_id, actividad_id, descripcion, fecha_inicio, fecha_limite, estado) VALUES (?, NULL, ?, ?, ?, ?)");
    $insPlazo->execute([$p1, 'Entrega del perfil final', '2026-03-15', '2026-04-02', 'completado']);
    $insPlazo->execute([$p1, 'Entrega del Capítulo III', '2026-07-01', date('Y-m-d', strtotime('+12 days')), 'activo']);
    echo "[OK] Actividades y plazos del Proyecto 1\n";
}

// ---------- Calendario ----------
$cal = [
    ['PRJ-2026-0001', 'Entrega del Capítulo III', date('Y-m-d H:i:s', strtotime('+10 days 15:00')), 'entrega', 'pendiente'],
    ['PRJ-2026-0001', 'Revisión del Capítulo I', date('Y-m-d H:i:s', strtotime('-2 days 10:00')), 'revision', 'vencido'],
    ['PRJ-2026-0004', '', '', '', ''],
    ['PRJ-2026-0002', 'Sesión presencial de alfabetización digital', date('Y-m-d H:i:s', strtotime('+5 days 09:00')), 'otro', 'pendiente'],
    ['PRJ-2026-0002', 'Revisión del diagnóstico', date('Y-m-d H:i:s', strtotime('-10 days 17:00')), 'revision', 'completado'],
];
foreach ($cal as $ev) {
    if ($ev[1] === '') continue;
    $pId = $pdo->query("SELECT id FROM proyectos WHERE codigo = '{$ev[0]}'")->fetchColumn();
    if ($pId && (int) $pdo->query("SELECT COUNT(*) FROM calendario WHERE proyecto_id = $pId AND titulo = '" . $ev[1] . "'")->fetchColumn() === 0) {
        $pdo->prepare("INSERT INTO calendario (proyecto_id, usuario_id, titulo, fecha_evento, tipo, estado) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$pId, $docente['uid'], $ev[1], $ev[2], $ev[3], $ev[4]]);
    }
}
echo "[OK] Eventos de calendario\n";

// ---------- Notificaciones ----------
$notis = [
    [$estudiante['uid'], 'Etapa aprobada', 'Su etapa "Perfil" fue aprobada y su documento final se archivó. Avance: 56%.', 'aprobacion'],
    [$docente['uid'], 'Documento subido', 'Se subió el documento de la etapa "Capítulo II" (v1).', 'documento'],
    [$estudiante['uid'], 'Nueva observación', 'El tutor observó el documento "diagnostico.txt".', 'observacion'],
];
$stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?,?,?,?)");
foreach ($notis as $nt) {
    $cnt = (int) $pdo->query("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = {$nt[0]} AND titulo = '{$nt[1]}'")->fetchColumn();
    if ($cnt === 0) $stmt->execute($nt);
}
echo "[OK] Notificaciones\n";

echo "\n== Demostración creada. Revisa http://localhost/app_majo/guia_academica/public/dashboard ==\n";