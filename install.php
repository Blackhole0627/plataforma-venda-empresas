<?php
/*
 * One-time setup: creates the tables, loads the 27 capitals and the first team login.
 * Locks itself once a user exists. Delete this file after installing if you like.
 */
require __DIR__ . '/inc/bootstrap.php';

/* ---------- server and config checks ---------- */
$bytes = function (string $v): int {
    $n = (int) $v;
    $u = strtolower(substr(trim($v), -1));
    return $n * ($u === 'g' ? 1073741824 : ($u === 'm' ? 1048576 : ($u === 'k' ? 1024 : 1)));
};
$checks = [];
$add = function (string $nivel, string $texto) use (&$checks) { $checks[] = [$nivel, $texto]; };

if (PHP_VERSION_ID < 80000) $add('erro', 'PHP ' . PHP_VERSION . '. O site precisa de PHP 8.0 ou mais novo.');
if (!extension_loaded('pdo_mysql')) $add('erro', 'Falta a extensão pdo_mysql. Ative no painel da hospedagem.');
if (!is_file(ROOT . '/.htaccess')) $add('erro', 'Falta o arquivo .htaccess na raiz. Ele fica oculto em alguns programas de FTP.');
if (!is_file(ROOT . '/uploads/.htaccess')) $add('erro', 'Falta o arquivo uploads/.htaccess. Ele bloqueia scripts na pasta de fotos.');
if (!is_dir(ROOT . '/uploads') || !is_writable(ROOT . '/uploads')) $add('erro', 'A pasta uploads não aceita gravação. Ajuste a permissão para 755.');
if (!function_exists('imagecreatefromstring')) $add('aviso', 'Falta a extensão GD. As fotos serão salvas sem tratamento. Ative no painel da hospedagem.');
if ($bytes((string) ini_get('post_max_size')) < 8 * 1048576) $add('aviso', 'post_max_size está em ' . ini_get('post_max_size') . '. O ideal é 8M ou mais para o envio de 15 fotos.');
if ((int) ini_get('max_file_uploads') < 15) $add('aviso', 'max_file_uploads está em ' . ini_get('max_file_uploads') . '. Precisa ser 15 ou mais.');

$host = strtolower((string) parse_url((string) cfg('site_url', ''), PHP_URL_HOST));
$dominio = preg_replace('/^www\./', '', $host);
if ($host !== strtolower(explode(':', (string) ($_SERVER['HTTP_HOST'] ?? ''))[0])) $add('aviso', 'site_url em config.php (' . cfg('site_url') . ') não bate com o endereço aberto agora.');
if (!str_starts_with((string) cfg('site_url', ''), 'https://')) $add('aviso', 'site_url ainda não usa https.');
if (cfg('debug')) $add('aviso', 'debug está ligado em config.php. Desligue antes de publicar.');
if (cfg('whatsapp') === '5511999999999') $add('aviso', 'O WhatsApp em config.php ainda é o de exemplo.');
if (str_contains((string) cfg('email', ''), 'empresasavenda.com.br') && !str_contains($dominio, 'empresasavenda')) $add('aviso', 'O e-mail de contato em config.php ainda é o de exemplo.');
if (cfg('notificar_email', '') === '') $add('aviso', 'notificar_email está vazio. A equipe não vai receber aviso de cadastro novo.');
if (!str_ends_with((string) cfg('email_remetente', ''), '@' . $dominio)) $add('aviso', 'email_remetente não usa o domínio do site. Os avisos podem cair no spam ou nem sair.');
if (in_array('Mariana Costa', array_column(equipe(), 'nome'), true)) $add('aviso', 'A equipe em config.php ainda é a de exemplo.');

$c = cfg('db');
try {
    new PDO('mysql:host=' . $c['host'] . ';port=' . ($c['port'] ?? 3306) . ';dbname=' . $c['name'] . ';charset=utf8mb4', $c['user'], $c['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Throwable $e) {
    $cod = (int) $e->getCode();
    error_log('install DB: ' . $e->getMessage());
    $add('erro', [1045 => 'O banco recusou o usuário ou a senha de config.php.', 1044 => 'O usuário do banco não tem permissão nesse banco. Dê todos os privilégios no painel.',
                  1049 => 'O banco "' . $c['name'] . '" não existe. Crie no painel e confira o nome em config.php.', 2002 => 'Não consegui achar o servidor do banco. Confira o host em config.php.'][$cod]
        ?? 'Não consegui conectar no banco (código ' . $cod . '). Confira os dados em config.php.');
}
$bloqueado = (bool) array_filter($checks, fn($k) => $k[0] === 'erro');

$pdo = $bloqueado ? null : db();
$tem = fn($t) => (bool) $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t))->fetchColumn();
$instalado = $pdo && $tem('usuarios') && (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() > 0;

$erro = '';
$ok = false;
$emailTeste = null;
if (!$instalado && !$bloqueado && is_post()) {
    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    if (mb_strlen($nome) < 2) $erro = 'Digite seu nome.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erro = 'E-mail inválido.';
    elseif (strlen($senha) < 8) $erro = 'A senha precisa de pelo menos 8 caracteres.';
    else {
        $sql = file_get_contents(__DIR__ . '/inc/schema.sql');
        foreach (array_filter(array_map('trim', explode(';', preg_replace('/^--.*$/m', '', $sql)))) as $q) $pdo->exec($q);

        $st = $pdo->prepare('INSERT IGNORE INTO cidades (slug, nome, uf, capital, ativa, criado_em) VALUES (?, ?, ?, 1, 1, ?)');
        foreach (CAPITAIS as [$n, $uf]) $st->execute([slugify($n) . '-' . strtolower($uf), $n, $uf, now()]);

        $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash, criado_em) VALUES (?, ?, ?, ?)')
            ->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), now()]);

        if (!empty($_POST['exemplos']) && !(int) $pdo->query('SELECT COUNT(*) FROM anuncios')->fetchColumn()) {
            require __DIR__ . '/inc/seed.php';
            foreach (SEED_ANUNCIOS as $r) {
                [$seg, $cid, $bairro, $preco, $fat, $func, $fund, $area, $alug, $marg, $folha, $est, $cnpj, $marca, $dest, $dias, $hor, $desc] = $r;
                [$a] = anuncio_salvar(['segmento' => $seg, 'cidade' => $cid, 'bairro' => $bairro, 'preco' => $preco, 'faturamento' => $fat,
                    'funcionarios' => $func, 'fundacao' => $fund, 'area' => $area, 'aluguel' => $alug, 'margem' => $marg, 'folha' => $folha,
                    'estoque' => $est, 'cnpj' => $cnpj, 'marca' => $marca, 'destaque' => $dest, 'horario' => $hor, 'descricao' => $desc,
                    'fotos' => seg_fotos($seg), 'status' => 'publicado']);
                $quando = date('Y-m-d H:i:s', time() - $dias * 86400);
                $pdo->prepare('UPDATE anuncios SET publicado_em = ?, criado_em = ? WHERE id = ?')->execute([$quando, $quando, $a['id']]);
            }
        }
        foreach (['uploads/cadastros', 'uploads/anuncios'] as $d) if (!is_dir(__DIR__ . "/$d")) @mkdir(__DIR__ . "/$d", 0755, true);
        if (cfg('notificar_email', '') !== '') $emailTeste = notify('Teste de e-mail · ' . cfg('marca'), 'Os avisos de cadastro novo e de mensagem do site vão chegar neste e-mail.');
        $ok = true;
    }
}
header('X-Robots-Tag: noindex');
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Instalação · <?= h(cfg('marca')) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('/assets/css/style.css') ?>">
  <style>body{display:grid;place-items:center;min-height:100vh;padding:20px}.form-card{max-width:560px;width:100%}.chk{list-style:none;margin:0 0 20px;padding:0;display:grid;gap:8px;font-size:.92rem}.chk li{padding:10px 14px;border-radius:10px;background:var(--warn-50);color:var(--warn)}.chk li.erro{background:var(--danger-50);color:var(--danger);font-weight:600}</style>
</head>
<body>
  <div class="form-card">
    <div style="margin-bottom:22px"><?= logo() ?></div>
<?php if ($instalado): ?>
    <h1 style="font-size:1.5rem">Já está instalado</h1>
    <p class="muted">O banco já tem usuário da equipe. Pode apagar o arquivo install.php do servidor.</p>
    <a class="btn btn-primary" href="/admin/login">Ir pro login</a>
<?php elseif ($ok): ?>
    <h1 style="font-size:1.5rem">Pronto!</h1>
    <p class="muted">Tabelas criadas, 27 capitais cadastradas e seu acesso criado. Por segurança, apague o arquivo install.php do servidor.</p>
    <?php if ($emailTeste === true): ?><div class="note" style="margin-bottom:16px"><span>Mandei um e-mail de teste pra <?= h(cfg('notificar_email')) ?>. Se não chegar em 5 minutos, olha o spam.</span></div>
    <?php elseif ($emailTeste === false): ?><div class="note warn" style="margin-bottom:16px"><span>O servidor não conseguiu mandar o e-mail de teste. Confira email_remetente em config.php e o e-mail no painel da hospedagem.</span></div><?php endif; ?>
    <?php if ($checks): ?><ul class="chk"><?php foreach ($checks as [$nivel, $texto]): ?><li class="<?= h($nivel) ?>"><?= h($texto) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <a class="btn btn-primary" href="/admin/login">Entrar na área da equipe</a>
<?php else: ?>
    <h1 style="font-size:1.5rem">Instalação</h1>
    <p class="muted" style="margin-bottom:20px">Cria as tabelas no banco e o primeiro acesso da equipe.</p>
    <?php if ($checks): ?><ul class="chk"><?php foreach ($checks as [$nivel, $texto]): ?><li class="<?= h($nivel) ?>"><?= h($texto) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($erro): ?><div class="note warn" style="margin-bottom:16px"><span><?= h($erro) ?></span></div><?php endif; ?>
    <?php if ($bloqueado): ?><p class="muted">Corrija os itens em vermelho e recarregue esta página.</p>
    <?php else: ?><form method="post">
      <div class="field"><label for="nome">Seu nome</label><input class="input" id="nome" name="nome" required value="<?= h($_POST['nome'] ?? '') ?>"></div>
      <div class="field"><label for="email">E-mail de acesso</label><input class="input" id="email" name="email" type="email" required value="<?= h($_POST['email'] ?? '') ?>"></div>
      <div class="field"><label for="senha">Senha (mínimo 8)</label><input class="input" id="senha" name="senha" type="password" minlength="8" required></div>
      <label class="check" style="margin-bottom:20px"><input type="checkbox" name="exemplos" value="1"><span>Carregar 20 anúncios de exemplo (dá pra excluir depois)</span></label>
      <button class="btn btn-primary btn-block" type="submit">Instalar</button>
    </form><?php endif; ?>
<?php endif; ?>
  </div>
</body>
</html>
