<?php
/* Team area API. POST JSON {action, ...} or multipart for photo upload. Needs login + CSRF header. */
require dirname(__DIR__) . '/inc/bootstrap.php';

if (!is_post()) json_error('Método inválido.', 405);
$user = require_login(true);
if (!csrf_ok($_SERVER['HTTP_X_CSRF'] ?? null)) json_error('Sessão expirada. Recarregue a página.', 419);

$isForm = str_starts_with((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'multipart/form-data');
$d = $isForm ? $_POST : json_body();
$action = (string) ($d['action'] ?? '');

switch ($action) {
    /* ---------- anúncios ---------- */
    case 'anuncio.salvar':
        [$a, $err] = anuncio_salvar((array) ($d['anuncio'] ?? []));
        if ($err) json_error($err, 422);
        json_out(['anuncio' => $a]);

    case 'anuncio.alterar':
        $a = anuncio_patch((int) ($d['id'] ?? 0), (array) ($d['patch'] ?? []));
        if (!$a) json_error('Anúncio não encontrado.', 404);
        json_out(['anuncio' => $a]);

    case 'anuncio.excluir':
        anuncio_excluir((int) ($d['id'] ?? 0));
        json_out(['ok' => true]);

    case 'foto.enviar':
        $files = arquivos('foto');
        $p = $files ? salvar_foto($files[0], 'anuncios/' . date('Y-m')) : null;
        if (!$p) json_error('Não deu pra salvar essa foto. Use JPG, PNG ou WEBP até 10 MB.', 422);
        json_out(['path' => $p]);

    /* ---------- cadastros ---------- */
    case 'cadastro.status':
        $c = cadastro_status((int) ($d['id'] ?? 0), (string) ($d['status'] ?? ''));
        if (!$c) json_error('Cadastro não encontrado.', 404);
        json_out(['cadastro' => $c]);

    /* ---------- cidades ---------- */
    case 'cidade.adicionar':
        $nome = mb_substr(trim((string) ($d['nome'] ?? '')), 0, 120);
        $uf = strtoupper((string) ($d['uf'] ?? ''));
        if (mb_strlen($nome) < 2 || !in_array($uf, UFS, true)) json_error('Digite o nome e escolha o estado.', 422);
        $nome = mb_convert_case($nome, MB_CASE_TITLE);
        $nome = preg_replace_callback('/\b(De|Da|Do|Das|Dos|E)\b/u', fn($m) => mb_strtolower($m[0]), $nome);
        $slug = slugify($nome) . '-' . strtolower($uf);
        if (cidade($slug)) json_error('Essa cidade já está cadastrada.', 422);
        db()->prepare('INSERT INTO cidades (slug, nome, uf, capital, ativa, criado_em) VALUES (?, ?, ?, 0, 1, ?)')->execute([$slug, $nome, $uf, now()]);
        json_out(['cidade' => cidade($slug)]);

    case 'cidade.alternar':
        $c = cidade((string) ($d['slug'] ?? ''));
        if (!$c) json_error('Cidade não encontrada.', 404);
        db()->prepare('UPDATE cidades SET ativa = ? WHERE slug = ?')->execute([$c['ativa'] ? 0 : 1, $c['slug']]);
        json_out(['cidade' => cidade($c['slug'])]);

    case 'cidade.remover':
        $c = cidade((string) ($d['slug'] ?? ''));
        if (!$c) json_error('Cidade não encontrada.', 404);
        if ($c['capital']) json_error('Capitais não podem ser removidas. Oculte se precisar.', 422);
        $st = db()->prepare('SELECT COUNT(*) FROM anuncios WHERE cidade = ?');
        $st->execute([$c['slug']]);
        if ((int) $st->fetchColumn()) json_error('Essa cidade tem anúncios. Oculte em vez de remover.', 422);
        db()->prepare('DELETE FROM cidades WHERE slug = ?')->execute([$c['slug']]);
        json_out(['ok' => true]);

    /* ---------- conta ---------- */
    case 'conta.senha':
        $st = db()->prepare('SELECT senha_hash FROM usuarios WHERE id = ?');
        $st->execute([$user['id']]);
        if (!password_verify((string) ($d['atual'] ?? ''), (string) $st->fetchColumn())) json_error('Senha atual errada.', 422);
        $nova = (string) ($d['nova'] ?? '');
        if (strlen($nova) < 8) json_error('A nova senha precisa de pelo menos 8 caracteres.', 422);
        db()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([password_hash($nova, PASSWORD_DEFAULT), $user['id']]);
        json_out(['ok' => true]);

    case 'usuario.adicionar':
        $nome = mb_substr(trim((string) ($d['nome'] ?? '')), 0, 120);
        $email = strtolower(trim((string) ($d['email'] ?? '')));
        $senha = (string) ($d['senha'] ?? '');
        if (mb_strlen($nome) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 8) json_error('Preencha nome, e-mail e senha (mínimo 8).', 422);
        $st = db()->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        if ((int) $st->fetchColumn()) json_error('Já existe acesso com esse e-mail.', 422);
        db()->prepare('INSERT INTO usuarios (nome, email, senha_hash, criado_em) VALUES (?, ?, ?, ?)')->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), now()]);
        json_out(['ok' => true]);

    case 'usuario.remover':
        $id = (int) ($d['id'] ?? 0);
        if ($id === (int) $user['id']) json_error('Você não pode remover o seu próprio acesso.', 422);
        db()->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
        json_out(['ok' => true]);
}
json_error('Ação desconhecida.', 400);
