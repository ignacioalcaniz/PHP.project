<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';

function rememberCookieName(): string
{
    return 'CAVA_NOBLE_REMEMBER';
}

function rememberCookieSecure(): bool
{
    return !isLocalEnvironment() && isHttpsRequest();
}

function createRememberToken(int $usuarioId): void
{
    $pdo = conectarDB();

    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));

    $cookieValue = $selector . ':' . $validator;
    $validatorHash = password_hash($validator, PASSWORD_DEFAULT);

    $expiresAt = date('Y-m-d H:i:s', time() + (86400 * 30));

    $sqlDelete = "
        DELETE FROM remember_tokens
        WHERE usuario_id = :usuario_id
    ";

    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmtDelete->execute();

    $sqlInsert = "
        INSERT INTO remember_tokens (
            usuario_id,
            selector,
            token_hash,
            expires_at
        )
        VALUES (
            :usuario_id,
            :selector,
            :token_hash,
            :expires_at
        )
    ";

    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmtInsert->bindParam(':selector', $selector);
    $stmtInsert->bindParam(':token_hash', $validatorHash);
    $stmtInsert->bindParam(':expires_at', $expiresAt);
    $stmtInsert->execute();

    setcookie(
        rememberCookieName(),
        $cookieValue,
        [
            'expires' => time() + (86400 * 30),
            'path' => '/proyecto_cava_Noble/',
            'domain' => '',
            'secure' => rememberCookieSecure(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

function attemptRememberLogin(): void
{
    if (isset($_SESSION['usuario_id'])) {
        return;
    }

    if (empty($_COOKIE[rememberCookieName()])) {
        return;
    }

    $cookieValue = $_COOKIE[rememberCookieName()];
    $parts = explode(':', $cookieValue);

    if (count($parts) !== 2) {
        clearRememberCookie();
        return;
    }

    [$selector, $validator] = $parts;

    if (
        !ctype_xdigit($selector) ||
        !ctype_xdigit($validator) ||
        strlen($selector) !== 32 ||
        strlen($validator) !== 64
    ) {
        clearRememberCookie();
        return;
    }

    $pdo = conectarDB();

    $sql = "
        SELECT
            rt.id,
            rt.usuario_id,
            rt.token_hash,
            rt.expires_at,
            u.nombre,
            u.email,
            u.rol
        FROM remember_tokens rt
        INNER JOIN usuarios u ON rt.usuario_id = u.id
        WHERE rt.selector = :selector
        AND rt.expires_at > NOW()
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':selector', $selector);
    $stmt->execute();

    $tokenRecord = $stmt->fetch();

    if (!$tokenRecord) {
        clearRememberCookie();
        return;
    }

    if (!password_verify($validator, $tokenRecord['token_hash'])) {
        deleteRememberTokenById((int)$tokenRecord['id']);
        clearRememberCookie();
        return;
    }

    session_regenerate_id(true);

    $_SESSION['usuario_id'] = (int)$tokenRecord['usuario_id'];
    $_SESSION['usuario_nombre'] = $tokenRecord['nombre'];
    $_SESSION['usuario_email'] = $tokenRecord['email'];
    $_SESSION['usuario_rol'] = $tokenRecord['rol'] ?? 'cliente';
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();

    deleteRememberTokenById((int)$tokenRecord['id']);
    createRememberToken((int)$tokenRecord['usuario_id']);
}

function deleteRememberTokenById(int $tokenId): void
{
    $pdo = conectarDB();

    $sql = "
        DELETE FROM remember_tokens
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $tokenId, PDO::PARAM_INT);
    $stmt->execute();
}

function deleteCurrentRememberToken(): void
{
    if (empty($_COOKIE[rememberCookieName()])) {
        return;
    }

    $parts = explode(':', $_COOKIE[rememberCookieName()]);

    if (count($parts) !== 2) {
        return;
    }

    [$selector] = $parts;

    if (!ctype_xdigit($selector)) {
        return;
    }

    $pdo = conectarDB();

    $sql = "
        DELETE FROM remember_tokens
        WHERE selector = :selector
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':selector', $selector);
    $stmt->execute();
}

function deleteExpiredRememberTokens(): void
{
    $pdo = conectarDB();

    $sql = "
        DELETE FROM remember_tokens
        WHERE expires_at <= NOW()
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

function clearRememberCookie(): void
{
    setcookie(
        rememberCookieName(),
        '',
        [
            'expires' => time() - 3600,
            'path' => '/proyecto_cava_Noble/',
            'domain' => '',
            'secure' => rememberCookieSecure(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}