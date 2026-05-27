<?php

function formatPrice($price) {
    return '$' . number_format((float)$price, 0, ',', '.');
}

function stockLabel($stock) {
    return (int)$stock > 0 ? 'Disponible' : 'Agotado';
}