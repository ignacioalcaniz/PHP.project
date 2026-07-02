<?php

require_once __DIR__ . '/../config/database.php';

function getClientIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function clearOldLoginAttempts(): void
{
    $pdo = conectarDB();

    $sql = "
        DELETE FROM login_attempts
        WHERE attempted_at < (NOW() - INTERVAL 7 DAY)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
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

    if (!$success) {
        evaluateSuspiciousIp($ip);
    }
}

function isLoginBlocked(string $email): bool
{
    $pdo = conectarDB();

    $ip = getClientIp();

    $sqlBlockedIp = "
        SELECT id
        FROM suspicious_ips
        WHERE ip_address = :ip
        AND blocked_until IS NOT NULL
        AND blocked_until > NOW()
        LIMIT 1
    ";

    $stmtBlockedIp = $pdo->prepare($sqlBlockedIp);
    $stmtBlockedIp->bindParam(':ip', $ip);
    $stmtBlockedIp->execute();

    if ($stmtBlockedIp->fetch()) {
        return true;
    }

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

function evaluateSuspiciousIp(string $ip): void
{
    $pdo = conectarDB();

    $sql = "
        SELECT COUNT(*) AS failed_attempts
        FROM login_attempts
        WHERE ip_address = :ip
        AND success = 0
        AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ip', $ip);
    $stmt->execute();

    $result = $stmt->fetch();
    $failedAttempts = (int)$result['failed_attempts'];

    if ($failedAttempts < 5) {
        return;
    }

    $blockedUntil = date('Y-m-d H:i:s', time() + (15 * 60));

    $sqlExisting = "
        SELECT id
        FROM suspicious_ips
        WHERE ip_address = :ip
        LIMIT 1
    ";

    $stmtExisting = $pdo->prepare($sqlExisting);
    $stmtExisting->bindParam(':ip', $ip);
    $stmtExisting->execute();

    $existing = $stmtExisting->fetch();

    if ($existing) {
        $sqlUpdate = "
            UPDATE suspicious_ips
            SET
                reason = :reason,
                attempts = :attempts,
                blocked_until = :blocked_until
            WHERE ip_address = :ip
        ";

        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $reason = 'Múltiples intentos fallidos de login';

        $stmtUpdate->bindParam(':reason', $reason);
        $stmtUpdate->bindParam(':attempts', $failedAttempts, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':blocked_until', $blockedUntil);
        $stmtUpdate->bindParam(':ip', $ip);
        $stmtUpdate->execute();

        return;
    }

    $sqlInsert = "
        INSERT INTO suspicious_ips (
            ip_address,
            reason,
            attempts,
            blocked_until
        )
        VALUES (
            :ip,
            :reason,
            :attempts,
            :blocked_until
        )
    ";

    $stmtInsert = $pdo->prepare($sqlInsert);
    $reason = 'Múltiples intentos fallidos de login';

    $stmtInsert->bindParam(':ip', $ip);
    $stmtInsert->bindParam(':reason', $reason);
    $stmtInsert->bindParam(':attempts', $failedAttempts, PDO::PARAM_INT);
    $stmtInsert->bindParam(':blocked_until', $blockedUntil);
    $stmtInsert->execute();
}

function unblockSuspiciousIp(int $id): void
{
    $pdo = conectarDB();

    $sql = "
        UPDATE suspicious_ips
        SET blocked_until = NULL
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}