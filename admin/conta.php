<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$me = admin_head('Equipe e senha', 'conta');
$users = db()->query('SELECT id, nome, email, ultimo_login FROM usuarios ORDER BY nome')->fetchAll();
?>
  <div class="adm">
    <aside id="adm-side"></aside>
    <div class="adm-main">
      <header id="adm-top"></header>
      <main class="adm-content">
        <div class="adm-grid" style="grid-template-columns:1fr 340px">
          <div class="adm-card">
            <table class="table">
              <thead><tr><th>Acesso</th><th>Último login</th><th style="text-align:right">Ações</th></tr></thead>
              <tbody>
              <?php foreach ($users as $u): ?>
                <tr data-id="<?= (int) $u['id'] ?>">
                  <td><span class="t-main"><?= h($u['nome']) ?><?= (int) $u['id'] === $me['id'] ? ' (você)' : '' ?></span><span class="t-sub"><?= h($u['email']) ?></span></td>
                  <td data-l="Último login"><?= $u['ultimo_login'] ? h(date('d/m/Y H:i', strtotime($u['ultimo_login']))) : 'Nunca' ?></td>
                  <td class="actions-cell"><?php if ((int) $u['id'] !== $me['id']): ?><button class="icon-btn danger" data-del title="Remover acesso" aria-label="Remover acesso"><?= ic('trash-2') ?></button><?php endif; ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <aside>
            <form class="adm-panel" id="f-senha" novalidate>
              <h2><?= ic('key-round') ?>Trocar minha senha</h2>
              <div class="field"><label for="atual">Senha atual</label><input class="input" id="atual" type="password" autocomplete="current-password"></div>
              <div class="field"><label for="nova">Nova senha (mínimo 8)</label><input class="input" id="nova" type="password" minlength="8" autocomplete="new-password"></div>
              <button class="btn btn-primary btn-block" type="submit">Salvar senha</button>
            </form>
            <form class="adm-panel" id="f-user" novalidate>
              <h2><?= ic('user-plus') ?>Novo acesso da equipe</h2>
              <div class="field"><label for="u-nome">Nome</label><input class="input" id="u-nome"></div>
              <div class="field"><label for="u-email">E-mail</label><input class="input" id="u-email" type="email"></div>
              <div class="field"><label for="u-senha">Senha inicial (mínimo 8)</label><input class="input" id="u-senha" type="text" autocomplete="off"></div>
              <button class="btn btn-outline btn-block" type="submit">Criar acesso</button>
            </form>
          </aside>
        </div>
      </main>
    </div>
  </div>
  <?php admin_scripts(); ?>
  <script>
    (function () {
      const $ = id => document.getElementById(id), A = PP.Admin;
      const call = (action, data) => PP.api('/admin/api', Object.assign({ action }, data));
      $('f-senha').addEventListener('submit', async e => {
        e.preventDefault();
        const ok = await A.busy(e.target.querySelector('button'), () => call('conta.senha', { atual: $('atual').value, nova: $('nova').value }));
        if (ok) { PP.toast('Senha trocada'); e.target.reset(); }
      });
      $('f-user').addEventListener('submit', async e => {
        e.preventDefault();
        const ok = await A.busy(e.target.querySelector('button'), () => call('usuario.adicionar', { nome: $('u-nome').value, email: $('u-email').value, senha: $('u-senha').value }));
        if (ok) { PP.toast('Acesso criado'); setTimeout(() => location.reload(), 700); }
      });
      document.addEventListener('click', async e => {
        const b = e.target.closest('[data-del]'); if (!b) return;
        if (!confirm('Remover esse acesso?')) return;
        const ok = await A.busy(b, () => call('usuario.remover', { id: Number(b.closest('tr').dataset.id) }));
        if (ok) b.closest('tr').remove();
      });
    })();
  </script>
</body>
</html>
