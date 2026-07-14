<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Ejecución exclusiva por consola
|--------------------------------------------------------------------------
*/

if (PHP_SAPI !== 'cli') {
    http_response_code(403);

    exit(
        'Este script solamente puede ejecutarse desde la terminal.'
    );
}

/*
|--------------------------------------------------------------------------
| Variables del administrador
|--------------------------------------------------------------------------
*/

$nombre = trim((string)env('ADMIN_NAME', 'Administrador'));
$apellido = trim((string)env('ADMIN_LASTNAME', 'Cava Noble'));
$username = trim((string)env('ADMIN_USERNAME', ''));
$dni = trim((string)env('ADMIN_DNI', ''));
$email = strtolower(
    trim((string)env('ADMIN_EMAIL', ''))
);
$passwordPlano = (string)env('ADMIN_PASSWORD', '');
$rol = 'admin';

if (
    $nombre === '' ||
    $apellido === '' ||
    $username === '' ||
    $dni === '' ||
    $email === '' ||
    $passwordPlano === ''
) {
    fwrite(
        STDERR,
        "Faltan variables ADMIN_* en el archivo .env.\n"
    );

    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(
        STDERR,
        "ADMIN_EMAIL no contiene un email válido.\n"
    );

    exit(1);
}

if (mb_strlen($passwordPlano) < 8) {
    fwrite(
        STDERR,
        "ADMIN_PASSWORD debe tener al menos 8 caracteres.\n"
    );

    exit(1);
}

$passwordHash = password_hash(
    $passwordPlano,
    PASSWORD_DEFAULT
);

if ($passwordHash === false) {
    fwrite(
        STDERR,
        "No se pudo generar el hash de contraseña.\n"
    );

    exit(1);
}

$pdo = conectarDB();

try {
    $pdo->beginTransaction();

    $sqlBuscar = "
        SELECT id
        FROM usuarios
        WHERE
            username = :username
            OR dni = :dni
            OR email = :email
        LIMIT 1
        FOR UPDATE
    ";

    $stmtBuscar = $pdo->prepare($sqlBuscar);

    $stmtBuscar->execute([
        ':username' => $username,
        ':dni' => $dni,
        ':email' => $email
    ]);

    $usuario = $stmtBuscar->fetch();

    if ($usuario) {
        $usuarioId = (int)$usuario['id'];

        $sqlActualizar = "
            UPDATE usuarios
            SET
                nombre = :nombre,
                apellido = :apellido,
                username = :username,
                dni = :dni,
                email = :email,
                password = :password,
                rol = :rol
            WHERE id = :id
        ";

        $stmtActualizar = $pdo->prepare($sqlActualizar);

        $stmtActualizar->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':username' => $username,
            ':dni' => $dni,
            ':email' => $email,
            ':password' => $passwordHash,
            ':rol' => $rol,
            ':id' => $usuarioId
        ]);

        $resultado = 'Administrador actualizado correctamente.';
    } else {
        $sqlInsertar = "
            INSERT INTO usuarios (
                nombre,
                apellido,
                username,
                dni,
                email,
                password,
                rol
            )
            VALUES (
                :nombre,
                :apellido,
                :username,
                :dni,
                :email,
                :password,
                :rol
            )
        ";

        $stmtInsertar = $pdo->prepare($sqlInsertar);

        $stmtInsertar->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':username' => $username,
            ':dni' => $dni,
            ':email' => $email,
            ':password' => $passwordHash,
            ':rol' => $rol
        ]);

        $resultado = 'Administrador creado correctamente.';
    }

    $pdo->commit();

    echo $resultado . PHP_EOL;
    echo 'Usuario: ' . $username . PHP_EOL;
    echo 'DNI: ' . $dni . PHP_EOL;
    echo 'Email: ' . $email . PHP_EOL;
    echo 'Rol: ' . $rol . PHP_EOL;

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(
        STDERR,
        'Error al configurar el administrador: ' .
        $exception->getMessage() .
        PHP_EOL
    );

    exit(1);
}