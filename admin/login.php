<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
session_open();
header('X-Robots-Tag: noindex, nofollow');

$next = (string) ($_GET['next'] ?? $_POST['next'] ?? '');
if (!preg_match('#^/admin/[a-z0-9\-/?=&%._]*$#i', $next)) $next = '/admin/';
if (current_user()) redirect($next);

$erro = '';
$email = '';
if (is_post()) {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');
    $falhas = $_SESSION['falhas'] ?? 0;
    if (!csrf_ok($_POST['t'] ?? null)) {
        $erro = 'A página ficou muito tempo aberta. Tenta de novo.';
    } else {
        if ($falhas >= 3) sleep(min(5, $falhas - 2)); // slows down guessing
        $st = db()->prepare('SELECT * FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($senha, $u['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION = ['user' => ['id' => (int) $u['id'], 'nome' => $u['nome'], 'email' => $u['email']]];
            db()->prepare('UPDATE usuarios SET ultimo_login = ? WHERE id = ?')->execute([now(), $u['id']]);
            if (password_needs_rehash($u['senha_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([password_hash($senha, PASSWORD_DEFAULT), $u['id']]);
            }
            redirect($next);
        }
        $_SESSION['falhas'] = $falhas + 1;
        $erro = 'E-mail ou senha errados.';
    }
}

page_head(['title' => 'Entrar · Área da equipe', 'admin' => true, 'robots' => 'noindex, nofollow', 'body_class' => 'admin']);
?>
  <div class="login">
    <div class="login-art" style="background-image:url('<?= photo_url(IMAGENS['login'], 1400) ?>')">
      <div><?= logo(true) ?></div>
      <div>
        <h2>Área da equipe</h2>
        <p>Aqui chegam os cadastros das empresas. Vocês conferem, montam o anúncio e publicam no site.</p>
        <div class="avatar-stack" style="margin-top:22px"><?php foreach (equipe() as $p): ?><img src="<?= h(photo_url($p['foto'], 100)) ?>" alt="<?= h($p['nome']) ?>"><?php endforeach; ?></div>
      </div>
    </div>
    <div class="login-form">
      <form method="post" action="/admin/login" id="form" novalidate>
        <div style="margin-bottom:28px"><?= logo() ?></div>
        <h1>Entrar</h1>
        <p class="muted">Use seu e-mail e senha da equipe.</p>
        <?php if ($erro): ?><div class="note warn" style="margin-bottom:18px"><?= ic('alert-circle') ?><span><?= h($erro) ?></span></div><?php endif; ?>
        <input type="hidden" name="t" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= h($next) ?>">
        <div class="field"><label for="email">E-mail</label>
          <div class="input-icon"><?= ic('mail') ?><input class="input" id="email" name="email" type="email" autocomplete="username" required value="<?= h($email) ?>"<?= $email ? '' : ' autofocus' ?>></div>
          <span class="error">Digite seu e-mail.</span></div>
        <div class="field"><label for="senha">Senha</label>
          <div class="input-icon"><?= ic('lock') ?><input class="input" id="senha" name="senha" type="password" autocomplete="current-password" required<?= $email ? ' autofocus' : '' ?>></div>
          <span class="error">Digite sua senha.</span></div>
        <button class="btn btn-primary btn-lg btn-block" type="submit">Entrar <?= ic('arrow-right') ?></button>
        <p style="text-align:center;margin-top:20px"><a href="/"><?= ic('arrow-left') ?> Voltar pro site</a></p>
      </form>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () { if (window.lucide) lucide.createIcons(); });
    document.getElementById('form').addEventListener('submit', function (e) {
      var em = document.getElementById('email'), se = document.getElementById('senha');
      var okE = /\S+@\S+\.\S+/.test(em.value), okS = se.value.length > 0;
      em.closest('.field').classList.toggle('invalid', !okE);
      se.closest('.field').classList.toggle('invalid', !okS);
      if (!okE || !okS) e.preventDefault();
    });
  </script>
</body>
</html>
