<?php

require_once __DIR__ . '/../config/database.php';

function getClientIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function isLoginBlocked(string $email): bool
{
    $pdo = conectarDB();

    $ip = getClientIp();

    $sql = "
        SELECT COUNT(*) AS attempts
        FROM login_attempts
        WHERE email = :email
        AND ip_address = :ip
        AND success = 0
        AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':ip', $ip);
    $stmt->execute();

    $result = $stmt->fetch();

    return (int)$result['attempts'] >= 5;
}

function registerLoginAttempt(string $email, bool $success): void
{
    $pdo = conectarDB();

    $ip = getClientIp();
    $successValue = $success ? 1 : 0;

    $sql = "
        INSERT INTO login_attempts (
            email,
            ip_address,
            success
        )
        VALUES (
            :email,
            :ip,
            :success
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':ip', $ip);
    $stmt->bindParam(':success', $successValue, PDO::PARAM_INT);
    $stmt->execute();
}

function clearOldLoginAttempts(): void
{
    $pdo = conectarDB();

    $sql = "
        DELETE FROM login_attempts
        WHERE attempted_at < (NOW() - INTERVAL 1 DAY)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}