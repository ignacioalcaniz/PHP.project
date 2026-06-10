<?php

function e(mixed $value): string
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function formatPrice(float|int|string $price): string
{
    return '$' . number_format(
        (float)$price,
        0,
        ',',
        '.'
    );
}

function stockLabel(float|int|string $stock): string
{
    return (int)$stock > 0 ? 'Disponible' : 'Agotado';
}