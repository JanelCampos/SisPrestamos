<?php

use App\Auth;

function config(?string $key = null, $default = null)
{
    static $config;

    if ($config === null) {
        $config = require __DIR__ . '/Config.php';
    }

    if ($key === null) {
        return $config;
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function app_url(string $path = ''): string
{
    $baseUrl = rtrim(base_url(), '/');

    if ($path === '') {
        return $baseUrl;
    }

    return $baseUrl . '/' . ltrim($path, '/');
}

function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $baseUrl = rtrim(base_url(), '/');

    if ($baseUrl !== '' && strpos($uri, $baseUrl) === 0) {
        $uri = substr($uri, strlen($baseUrl));
    }

    $path = trim($uri, '/');

    return $path === '' ? '/' : '/' . $path;
}

function base_url(): string
{
    $configured = trim((string) config('app.base_url', ''));
    if ($configured !== '') {
        return '/' . trim($configured, '/');
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    if (basename($scriptName) !== 'index.php') {
        return '';
    }

    $directory = str_replace('/index.php', '', $scriptName);
    $directory = rtrim($directory, '/');

    return $directory === '/' ? '' : $directory;
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function is_post(): bool
{
    return request_method() === 'POST';
}

function input(string $key, $default = null)
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;

    if (is_string($value)) {
        return trim($value);
    }

    return $value;
}

function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function with_old_input(array $payload): void
{
    $_SESSION['_old'] = $payload;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][$type][] = $message;
}

function pull_flash(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);

    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if (!is_post()) {
        return;
    }

    $token = $_POST['_token'] ?? '';

    if (!hash_equals(csrf_token(), $token)) {
        abort(419, 'El token CSRF es invalido o expiro.');
    }
}

function redirect_to(string $path): void
{
    header('Location: ' . app_url($path));
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($value): string
{
    return config('app.currency', '$') . ' ' . number_format((float) $value, 2, '.', ',');
}

function format_date(?string $date, string $format = 'd/m/Y'): string
{
    if (!$date) {
        return '-';
    }

    return date($format, strtotime($date));
}

function is_active_route(string $path): bool
{
    return current_path() === $path;
}

function auth_user(): ?array
{
    return Auth::user();
}

function abort(int $statusCode, string $message): void
{
    http_response_code($statusCode);
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Error</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body class="bg-light"><div class="container py-5">';
    echo '<div class="alert alert-danger shadow-sm"><h1 class="h4 mb-3">Error ' . e((string) $statusCode) . '</h1><p class="mb-0">' . e($message) . '</p></div>';
    echo '</div></body></html>';
    exit;
}

function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $flashMessages = pull_flash();
    $user = auth_user();
    $viewFile = __DIR__ . '/../views/' . $view . '.php';

    if (!file_exists($viewFile)) {
        abort(500, 'La vista solicitada no existe.');
    }

    require __DIR__ . '/../views/layout.php';
}

function require_auth(array $roles = []): void
{
    if (!Auth::check()) {
        flash('danger', 'Inicia sesion para continuar.');
        redirect_to('login');
    }

    if ($roles !== [] && !Auth::hasAnyRole($roles)) {
        abort(403, 'No tienes permisos para acceder a este recurso.');
    }
}

function redirect_back(string $fallback = 'dashboard'): void
{
    $target = $_SERVER['HTTP_REFERER'] ?? app_url($fallback);
    header('Location: ' . $target);
    exit;
}

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
