<?php

function subirImagenProducto(array $archivo): string
{
    if (
        !isset($archivo['error']) ||
        $archivo['error'] !== UPLOAD_ERR_OK
    ) {
        throw new Exception('Debe seleccionar una imagen.');
    }

    $extensionesPermitidas = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    $extension = strtolower(
        pathinfo(
            $archivo['name'],
            PATHINFO_EXTENSION
        )
    );

    if (!in_array($extension, $extensionesPermitidas)) {
        throw new Exception(
            'Formato inválido. Solo JPG, PNG y WEBP.'
        );
    }

    $tamanoMaximo = 5 * 1024 * 1024;

    if ($archivo['size'] > $tamanoMaximo) {
        throw new Exception(
            'La imagen supera los 5 MB.'
        );
    }

    $nombreArchivo =
        uniqid('vino_', true) .
        '.' .
        $extension;

    $rutaFisica =
        dirname(__DIR__) .
        '/assets/uploads/products/' .
        $nombreArchivo;

    if (
        !move_uploaded_file(
            $archivo['tmp_name'],
            $rutaFisica
        )
    ) {
        throw new Exception(
            'No se pudo guardar la imagen.'
        );
    }

    return
        '/proyecto_cava_Noble/assets/uploads/products/' .
        $nombreArchivo;
}