<?php
declare(strict_types=1);

require_once __DIR__ . '/Session.php';
Session::start();
require_once __DIR__ . '/database.php';

function csrf_token(): string
{
    return Session::csrfToken();
}

function verify_csrf_token(): bool
{
    return Session::verifyCsrfToken();
}

function current_user(): ?array
{
    return Session::currentUser();
}

function require_login(): void
{
    Session::requireLogin('../auth/login.php');
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}