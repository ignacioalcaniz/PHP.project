<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final readonly class AuditService
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function log(
        int $adminId,
        string $action,
        string $entity,
        ?int $entityId = null,
        ?string $description = null
    ): void {
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
                :ip_address
            )
        ";

        $statement = $this->pdo->prepare(
            $sql
        );

        $statement->bindValue(
            ':admin_id',
            $adminId,
            PDO::PARAM_INT
        );

        $statement->bindValue(
            ':accion',
            $action,
            PDO::PARAM_STR
        );

        $statement->bindValue(
            ':entidad',
            $entity,
            PDO::PARAM_STR
        );

        $statement->bindValue(
            ':entidad_id',
            $entityId,
            $entityId === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_INT
        );

        $statement->bindValue(
            ':descripcion',
            $description,
            $description === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );

        $statement->bindValue(
            ':ip_address',
            $this->clientIp(),
            PDO::PARAM_STR
        );

        $statement->execute();
    }

    private function clientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP
        ) !== false
            ? $ip
            : '0.0.0.0';
    }
}