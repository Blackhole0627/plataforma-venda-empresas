<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$me = admin_head('Equipe, valores e senha', 'conta');
$users = db()->query('SELECT id, nome, email, ultimo_login FROM usuarios ORDER BY nome')->fetchAll();
$eq = equipe();
?>
  <div class="adm">
    <aside id="adm-side"></aside>
    <div class="adm-main">
      <header id="adm-top"></header>
      <main class="adm-content">
        <div class="adm-grid" style="grid-template-columns:1fr 340px">
          <div>
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

          <?php if ($eq): ?>
          <form class="adm-panel" id="f-equipe" novalidate style="margin-top:18px">
            <h2><?= ic('id-card') ?>Consultor no site</h2>
            <p class="muted" style="font-size:.88rem">Nome, cargo e foto que aparecem na home, no contato e no cadastro. Use uma foto de rosto, ela fica redonda no site.</p>
            <?php foreach ($eq as $i => $p): ?>
            <div class="eq-row" data-i="<?= $i ?>" data-foto="<?= h($p['foto'] ?? '') ?>">
              <div class="eq-foto"><?= avatar($p, 200, 'eq-av') ?>
                <label class="btn btn-outline btn-sm"><?= ic('camera') ?>Trocar foto<input type="file" accept="image/*" class="sr-only" data-foto-input></label>
              </div>
              <div class="eq-fields">
                <div class="field"><label>Nome</label><input class="input" data-k="nome" value="<?= h($p['nome']) ?>" maxlength="60"></div>
                <div class="field"><label>Cargo</label><input class="input" data-k="cargo" value="<?= h($p['cargo']) ?>" maxlength="80"></div>
              </div>
            </div>
            <?php endforeach; ?>
            <button class="btn btn-primary" type="submit">Salvar consultor</button>
          </form>
          <?php endif; ?>
          </div>

          <aside>
            <form class="adm-panel" id="f-valores" novalidate>
              <h2><?= ic('badge-dollar-sign') ?>Valores do site</h2>
              <p class="muted" style="font-size:.88rem">Aparecem na página Vender empresa. Mudou aqui, muda no site na hora.</p>
              <div class="field"><label for="taxa">Taxa de publicação (R$)</label><input class="input" id="taxa" inputmode="numeric" value="<?= h((string) ajuste('taxa_publicacao')) ?>"></div>
              <div class="field"><label for="comissao">Comissão sobre a venda (%)</label><input class="input" id="comissao" inputmode="decimal" value="<?= h(str_replace('.', ',', (string) ajuste('comissao'))) ?>"></div>
              <button class="btn btn-primary btn-block" type="submit">Salvar valores</button>
            </form>
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
      /* consultant photo: crop to a centered square (a bit toward the top, where the face usually is) */
      function quadrada(file) {
        return new Promise((ok, fail) => {
          const img = new Image(), url = URL.createObjectURL(file);
          img.onload = () => {
            const s = Math.min(img.width, img.height), x = (img.width - s) / 2, y = img.height > img.width ? (img.height - s) * 0.2 : 0;
            const c = document.createElement('canvas'); c.width = c.height = Math.min(s, 600);
            c.getContext('2d').drawImage(img, x, y, s, s, 0, 0, c.width, c.height);
            URL.revokeObjectURL(url); c.toBlob(b => b ? ok(b) : fail(new Error('foto')), 'image/jpeg', 0.88);
          };
          img.onerror = () => fail(new Error('Não consegui abrir essa foto.'));
          img.src = url;
        });
      }
      const fe = $('f-equipe');
      if (fe) {
        fe.addEventListener('change', async e => {
          const inp = e.target.closest('[data-foto-input]'); if (!inp || !inp.files[0]) return;
          const row = inp.closest('.eq-row'), lbl = inp.closest('label');
          lbl.classList.add('loading');
          try {
            const path = await PP.Store.uploadFoto(await quadrada(inp.files[0]), 'equipe');
            row.dataset.foto = path;
            const old = row.querySelector('.eq-av'), img = document.createElement('img');
            img.className = 'eq-av'; img.src = path; img.alt = ''; old.replaceWith(img);
            PP.toast('Foto pronta. Clique em Salvar consultor');
          } catch (err) { PP.toast(err.message || 'Não deu pra enviar a foto', 'error'); }
          lbl.classList.remove('loading'); inp.value = '';
        });
        fe.addEventListener('submit', async e => {
          e.preventDefault();
          const equipe = {};
          fe.querySelectorAll('.eq-row').forEach(r => {
            equipe[r.dataset.i] = { nome: r.querySelector('[data-k=nome]').value, cargo: r.querySelector('[data-k=cargo]').value, foto: r.dataset.foto || '' };
          });
          const ok = await A.busy(fe.querySelector('button[type=submit]'), () => call('equipe.salvar', { equipe }));
          if (ok) PP.toast('Consultor salvo');
        });
      }
      $('f-valores').addEventListener('submit', async e => {
        e.preventDefault();
        const ok = await A.busy(e.target.querySelector('button'), () => call('ajustes.salvar', { taxa_publicacao: $('taxa').value, comissao: $('comissao').value }));
        if (ok) PP.toast('Valores salvos');
      });
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
