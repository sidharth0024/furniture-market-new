<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Turn a string into a URL-safe slug. */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'ascii//TRANSLIT', $text) ?: $text;
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text ?: 'n-a';
}

/** Generate (once per session) and return the CSRF token. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Validate a submitted CSRF token. */
function csrf_verify(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/** Pull a value back out of "old" flashed input (after a failed validation), else fallback. */
function old(string $key, $fallback = '')
{
    return $_SESSION['old_input'][$key] ?? $fallback;
}

/** Get a flashed validation error for a field, or empty string. */
function form_error(string $key): string
{
    return $_SESSION['form_errors'][$key] ?? '';
}

function has_form_error(string $key): bool
{
    return !empty($_SESSION['form_errors'][$key]);
}

/** Store errors + old input in session and redirect back to the form. */
function fail_validation(array $errors, array $oldInput, ?int $editId = null): void
{
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input']   = $oldInput;
    $location = 'products_forms.php' . ($editId ? "?id={$editId}" : '');
    header("Location: {$location}");
    exit;
}

/** Clear flashed validation state (call at top of the form page after reading it). */
function clear_flash(): array
{
    $errors = $_SESSION['form_errors'] ?? [];
    $old    = $_SESSION['old_input'] ?? [];
    unset($_SESSION['form_errors'], $_SESSION['old_input']);
    return [$errors, $old];
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function get_flash(string $type): ?string
{
    $msg = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $msg;
}

function e(?string $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
