<?php

require_once __DIR__ . '/../config/app.php';

function renderTurnstileWidget(): void
{
    ?>
    <div
        class="cf-turnstile"
        data-sitekey="<?php echo e(TURNSTILE_SITE_KEY); ?>"
        data-theme="light"
    ></div>

    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php
}

function verifyTurnstileToken(): bool
{
    $token = $_POST['cf-turnstile-response'] ?? '';

    if ($token === '') {
        return false;
    }

    $data = http_build_query([
        'secret' => TURNSTILE_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $data,
            'timeout' => 8
        ]
    ];

    $context = stream_context_create($options);

    $response = file_get_contents(
        'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        false,
        $context
    );

    if ($response === false) {
        return false;
    }

    $result = json_decode($response, true);

    return isset($result['success']) && $result['success'] === true;
}