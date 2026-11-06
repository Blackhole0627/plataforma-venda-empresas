<?php
/* POST JSON: nome, whatsapp, email, assunto, mensagem, anuncio, site (honeypot) */
require dirname(__DIR__) . '/inc/bootstrap.php';

if (!is_post()) json_error('Método inválido.', 405);
$d = json_body();
if (!empty($d['site'])) json_out(['ok' => true]); // bot

$st = db()->prepare('SELECT COUNT(*) FROM contatos WHERE ip = ? AND criado_em > ?');
$st->execute([client_ip(), date('Y-m-d H:i:s', time() - 3600)]);
if ((int) $st->fetchColumn() >= 10) json_error('Muitas mensagens seguidas. Chama a gente no WhatsApp.', 429);

$r = [
    'nome' => mb_substr(trim((string) ($d['nome'] ?? '')), 0, 120),
    'email' => strtolower(mb_substr(trim((string) ($d['email'] ?? '')), 0, 190)),
    'whatsapp' => mb_substr(trim((string) ($d['whatsapp'] ?? '')), 0, 30),
    'assunto' => mb_substr(trim((string) ($d['assunto'] ?? '')), 0, 80),
    'mensagem' => mb_substr(trim((string) ($d['mensagem'] ?? '')), 0, 3000),
    'anuncio' => preg_match('/^PP\d{1,8}$/i', (string) ($d['anuncio'] ?? '')) ? strtoupper($d['anuncio']) : '',
];
if (mb_strlen($r['nome']) < 2 || !filter_var($r['email'], FILTER_VALIDATE_EMAIL)
    || strlen(preg_replace('/\D/', '', $r['whatsapp'])) < 10 || mb_strlen($r['mensagem']) < 5) {
    json_error('Confira os campos e tente de novo.', 422);
}
$r['ip'] = client_ip();
$r['criado_em'] = now();
db()->prepare('INSERT INTO contatos (nome, email, whatsapp, assunto, mensagem, anuncio, ip, criado_em)
               VALUES (:nome, :email, :whatsapp, :assunto, :mensagem, :anuncio, :ip, :criado_em)')->execute($r);

notify(
    'Mensagem do site · ' . $r['assunto'],
    "Nome: {$r['nome']}\nWhatsApp: {$r['whatsapp']}\nE-mail: {$r['email']}\nAssunto: {$r['assunto']}" .
    ($r['anuncio'] ? "\nAnúncio: {$r['anuncio']}" : '') . "\n\n{$r['mensagem']}\n\nVer todas: " . site_url('/admin/mensagens')
);
json_out(['ok' => true]);
