<?php
/*
 * One-time setup: creates the tables, loads the 27 capitals and the first team login.
 * Locks itself once a user exists. Delete this file after installing if you like.
 */
require __DIR__ . '/inc/bootstrap.php';

$pdo = db();
$tem = fn($t) => (bool) $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t))->fetchColumn();
$instalado = $tem('usuarios') && (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() > 0;

$erro = '';
$ok = false;
if (!$instalado && is_post()) {
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
  <style>body{display:grid;place-items:center;min-height:100vh;padding:20px}.form-card{max-width:460px;width:100%}</style>
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
    <a class="btn btn-primary" href="/admin/login">Entrar na área da equipe</a>
<?php else: ?>
    <h1 style="font-size:1.5rem">Instalação</h1>
    <p class="muted" style="margin-bottom:20px">Cria as tabelas no banco <strong><?= h(cfg('db')['name']) ?></strong> e o primeiro acesso da equipe.</p>
    <?php if ($erro): ?><div class="note warn" style="margin-bottom:16px"><span><?= h($erro) ?></span></div><?php endif; ?>
    <form method="post">
      <div class="field"><label for="nome">Seu nome</label><input class="input" id="nome" name="nome" required value="<?= h($_POST['nome'] ?? '') ?>"></div>
      <div class="field"><label for="email">E-mail de acesso</label><input class="input" id="email" name="email" type="email" required value="<?= h($_POST['email'] ?? '') ?>"></div>
      <div class="field"><label for="senha">Senha (mínimo 8)</label><input class="input" id="senha" name="senha" type="password" minlength="8" required></div>
      <label class="check" style="margin-bottom:20px"><input type="checkbox" name="exemplos" value="1"><span>Carregar 20 anúncios de exemplo (dá pra excluir depois)</span></label>
      <button class="btn btn-primary btn-block" type="submit">Instalar</button>
    </form>
<?php endif; ?>
  </div>
</body>
</html>
