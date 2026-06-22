<?php

require_once __DIR__ . '/../config/database.php';

function getAdminIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function createAdminLog(
    int $adminId,
    string $accion,
    string $entidad,
    ?int $entidadId = null,
    ?string $descripcion = null
): void {
    $pdo = conectarDB();
    $ip = getAdminIp();

    $sql = "
        INSERT INTO admin_logs (
            admin_id,
            accion,
            entidad,
            entidad_id,
            descripcion,
            ip_address
        )
        VALUES (
            :admin_id,
            :accion,
            :entidad,
            :entidad_id,
            :descripcion,
            :ip
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':admin_id', $adminId, PDO::PARAM_INT);
    $stmt->bindValue(':accion', $accion);
    $stmt->bindValue(':entidad', $entidad);
    $stmt->bindValue(':entidad_id', $entidadId, $entidadId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':descripcion', $descripcion, $descripcion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':ip', $ip);

    $stmt->execute();
}