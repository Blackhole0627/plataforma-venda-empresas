<?php
/* Loaded by every page: config, database, session, helpers. */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

if (!is_file(ROOT . '/config.php')) {
    http_response_code(500);
    exit('Falta o arquivo config.php. Copie config.sample.php para config.php e preencha os dados do banco.');
}
$GLOBALS['CFG'] = require ROOT . '/config.php';

date_default_timezone_set(cfg('timezone', 'America/Sao_Paulo'));
mb_internal_encoding('UTF-8');
if (cfg('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

require __DIR__ . '/data.php';
require __DIR__ . '/repo.php';
require __DIR__ . '/view.php';

/* ---------- config ---------- */
function cfg(string $key, $default = null)
{
    return $GLOBALS['CFG'][$key] ?? $default;
}

function site_url(string $path = ''): string
{
    return rtrim((string) cfg('site_url', ''), '/') . $path;
}

/* ---------- database ---------- */
function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = cfg('db');
    $dsn = 'mysql:host=' . $c['host'] . ';port=' . ($c['port'] ?? 3306) . ';dbname=' . $c['name'] . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('DB: ' . $e->getMessage());
        exit(cfg('debug') ? 'Erro no banco: ' . $e->getMessage() : 'Site em manutenção. Tente de novo em alguns minutos.');
    }
    return $pdo;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function ms(?string $datetime): ?int
{
    return $datetime ? strtotime($datetime) * 1000 : null;
}

/* ---------- text ---------- */
function h($s): string
{
    return htmlspecialchars((string) ($s ?? ''), ENT_QUOTES, 'UTF-8');
}

function slugify(string $s): string
{
    $map = ['á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n'];
    $s = strtr(mb_strtolower($s), $map);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

function money(?float $v): string
{
    return "R$\u{00A0}" . number_format((float) $v, 0, ',', '.');
}

function money_short(?float $v): string
{
    $v = (float) $v;
    if ($v >= 1e6) return 'R$ ' . rtrim(rtrim(number_format($v / 1e6, 1, ',', '.'), '0'), ',') . ' mi';
    if ($v >= 1e3) return 'R$ ' . round($v / 1e3) . ' mil';
    return money($v);
}

function num($v): string
{
    return number_format((float) $v, 0, ',', '.');
}

function date_br(?int $ms): string
{
    return $ms ? date('d/m/Y', intdiv($ms, 1000)) : '';
}

function time_ago(?int $ms): string
{
    $m = (int) round((time() - intdiv((int) $ms, 1000)) / 60);
    if ($m < 60) return 'há ' . max(1, $m) . ' min';
    $hh = (int) round($m / 60);
    if ($hh < 24) return 'há ' . $hh . 'h';
    $d = (int) round($hh / 24);
    return $d === 1 ? 'ontem' : 'há ' . $d . ' dias';
}

function photo_url(string $p, int $w = 800): string
{
    if ($p === '') return '';
    $id = preg_replace('/^u:/', '', $p);
    if (str_starts_with($id, '/') || str_starts_with($id, 'http')) return $id;
    return 'https://images.unsplash.com/photo-' . $id . '?auto=format&fit=crop&w=' . $w . '&q=70';
}

/* ---------- URLs ---------- */
function anuncio_slug(array $a): string
{
    return seg($a['segmento'])['slug'] . '-em-' . $a['cidade'] . '-' . strtolower($a['codigo']);
}

function anuncio_url(array $a): string
{
    return '/empresa-a-venda/' . anuncio_slug($a);
}

function cidade_url(string $slug): string
{
    return '/comprar-empresa/' . $slug;
}

function whatsapp_url(string $text = ''): string
{
    return 'https://wa.me/' . cfg('whatsapp') . '?text=' . rawurlencode($text);
}

function redirect(string $to, int $code = 302): void
{
    header('Location: ' . $to, true, $code);
    exit;
}

/* ---------- requests ---------- */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $msg, int $code = 400): void
{
    json_out(['error' => $msg], $code);
}

function json_body(): array
{
    $d = json_decode(file_get_contents('php://input') ?: '', true);
    return is_array($d) ? $d : [];
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/* ---------- session / auth ---------- */
function session_open(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('pp_sess');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/* public pages only touch the session when the team is logged in (keeps pages cache friendly) */
function session_if_cookie(): void
{
    if (isset($_COOKIE['pp_sess'])) session_open();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(bool $api = false): array
{
    session_open();
    $u = current_user();
    if (!$u) {
        if ($api) json_error('Sessão expirada. Entre de novo.', 401);
        redirect('/admin/login?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/admin/'));
    }
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    return $u;
}

function csrf_token(): string
{
    session_open();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_ok(?string $t): bool
{
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

/* ---------- e-mail notice (best effort) ---------- */
function notify(string $subject, string $body): void
{
    $to = (string) cfg('notificar_email', '');
    if ($to === '') return;
    $from = (string) cfg('email_remetente', 'no-reply@localhost');
    $headers = 'From: ' . cfg('marca') . ' <' . $from . ">\r\n" .
        "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit";
    $subj = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    if (!@mail($to, $subj, $body, $headers)) error_log('mail() failed: ' . $subject);
}
