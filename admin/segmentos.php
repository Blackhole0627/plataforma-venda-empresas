<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
admin_head('Segmentos', 'segmentos');
$uso = [];
foreach (db()->query('SELECT segmento, COUNT(*) n FROM anuncios GROUP BY segmento')->fetchAll() as $r) $uso[$r['segmento']] = (int) $r['n'];
$fixos = [];
foreach (SEGMENTOS as $s) $fixos[$s['grupo']][] = $s['nome'];
?>
  <div class="adm">
    <aside id="adm-side"></aside>
    <div class="adm-main">
      <header id="adm-top"></header>
      <main class="adm-content">
        <div class="adm-grid">
          <div>
            <div class="adm-card">
              <div class="adm-toolbar"><h2 style="margin:0;font-size:1.05rem">Segmentos adicionados pela equipe</h2></div>
              <table class="table">
                <thead><tr><th>Segmento</th><th>Grupo</th><th>Anúncios</th><th style="text-align:right">Ações</th></tr></thead>
                <tbody id="rows"></tbody>
              </table>
            </div>
            <details class="adm-panel seg-fixos">
              <summary><strong>Ver os <?= count(SEGMENTOS) ?> segmentos que já vêm no site</strong><span class="muted">Confira aqui antes de adicionar, pra não repetir</span></summary>
              <?php foreach ($fixos as $g => $nomes): ?>
              <h3><?= h($g) ?></h3><p class="muted"><?= h(implode(', ', $nomes)) ?></p>
              <?php endforeach; ?>
            </details>
          </div>

          <aside>
            <form class="adm-panel" id="form" novalidate>
              <h2 id="f-title"><i data-lucide="plus-circle"></i>Adicionar segmento</h2>
              <p class="muted" style="font-size:.88rem">Aparece na lista de segmentos do site, nos filtros, no cadastro do vendedor e na criação de anúncios.</p>
              <div class="field"><label for="nome">Nome do segmento</label><input class="input" id="nome" maxlength="40" placeholder="Ex.: Adega e bistrô"><span class="error" id="nome-err">Digite o nome do segmento.</span></div>
              <div class="field"><label for="desc">Descrição curta <span class="muted" style="font-weight:500">(opcional)</span></label><input class="input" id="desc" maxlength="60" placeholder="Ex.: vinhos, petiscos, música ao vivo"><small>Aparece embaixo do nome na lista.</small></div>
              <div class="field"><label for="grupo">Grupo</label><select class="select" id="grupo"></select></div>
              <div class="field"><label>Ícone</label><div class="icon-pick" id="icones" role="radiogroup" aria-label="Ícone"></div></div>
              <div class="seg-prev" id="prev"></div>
              <button class="btn btn-primary btn-block" type="submit" id="salvar"><i data-lucide="plus"></i>Adicionar segmento</button>
              <button class="btn btn-ghost btn-block" type="button" id="cancelar" hidden>Cancelar</button>
            </form>
            <div class="adm-panel">
              <h2><i data-lucide="image"></i>Fotos</h2>
              <p class="muted" style="font-size:.88rem;margin:0">Anúncio sem foto num segmento novo usa as fotos ilustrativas de Outros. Pra ter fotos próprias, envie as fotos no anúncio.</p>
            </div>
          </aside>
        </div>
      </main>
    </div>
  </div>

  <?php admin_scripts(['segExtra' => segmentos_extra(), 'grupos' => array_keys($fixos), 'icones' => SEG_ICONES, 'uso' => (object) $uso]); ?>
  <script>
    (function () {
      const { esc, ic } = PP, A = PP.Admin, B = PP.BOOT;
      const $ = id => document.getElementById(id);
      let lista = B.segExtra || [], editando = null, icone = 'store';

      $('grupo').innerHTML = B.grupos.map(g => '<option>' + esc(g) + '</option>').join('');
      $('grupo').value = 'Outros';
      $('icones').innerHTML = B.icones.map(n => '<button type="button" role="radio" data-icon="' + n + '" aria-label="' + n + '">' + ic(n) + '</button>').join('');

      function marcaIcone(n) {
        icone = n;
        $('icones').querySelectorAll('button').forEach(b => { const on = b.dataset.icon === n; b.classList.toggle('on', on); b.setAttribute('aria-checked', on); });
        preview();
      }
      function preview() {
        const n = $('nome').value.trim() || 'Nome do segmento', d = $('desc').value.trim();
        $('prev').innerHTML = '<span class="label">Prévia</span><div class="sheet-links"><a>' + ic(icone) + '<span>' + esc(n) + (d ? '<small>' + esc(d) + '</small>' : '') + '</span></a></div>';
        PP.icons();
      }

      function render() {
        $('rows').innerHTML = lista.map(s => {
          const n = Number(B.uso[s.slug] || 0);
          return '<tr data-slug="' + s.slug + '">' +
            '<td><div class="t-flex"><span class="seg-ic">' + ic(s.icon) + '</span><div><span class="t-main">' + esc(s.nome) + '</span>' + (s.desc ? '<span class="t-sub">' + esc(s.desc) + '</span>' : '') + '</div></div></td>' +
            '<td data-l="Grupo">' + esc(s.grupo) + '</td>' +
            '<td data-l="Anúncios"><a href="/comprar-empresa?segmento=' + s.slug + '" target="_blank">' + n + '</a></td>' +
            '<td class="actions-cell"><button class="icon-btn" data-act="edit" title="Editar" aria-label="Editar">' + ic('pencil') + '</button>' +
            '<button class="icon-btn danger" data-act="del" title="Remover" aria-label="Remover">' + ic('trash-2') + '</button></td></tr>';
        }).join('') || '<tr><td colspan="4"><div class="adm-empty">Nenhum segmento adicionado ainda. Use o formulário Adicionar segmento.</div></td></tr>';
        PP.icons();
      }

      function modo(s) {
        editando = s ? s.slug : null;
        $('nome').value = s ? s.nome : ''; $('desc').value = s ? s.desc : ''; $('grupo').value = s ? s.grupo : 'Outros';
        $('f-title').innerHTML = ic(s ? 'pencil' : 'plus-circle') + (s ? 'Editar segmento' : 'Adicionar segmento');
        $('salvar').innerHTML = ic(s ? 'save' : 'plus') + (s ? 'Salvar alterações' : 'Adicionar segmento');
        $('cancelar').hidden = !s;
        $('nome').closest('.field').classList.remove('invalid');
        marcaIcone(s ? s.icon : 'store');
      }

      $('icones').addEventListener('click', e => { const b = e.target.closest('[data-icon]'); if (b) marcaIcone(b.dataset.icon); });
      $('nome').addEventListener('input', preview); $('desc').addEventListener('input', preview);
      $('cancelar').onclick = () => modo(null);

      $('form').addEventListener('submit', async e => {
        e.preventDefault();
        const nome = $('nome').value.trim();
        $('nome-err').textContent = 'Digite o nome do segmento.';
        $('nome').closest('.field').classList.toggle('invalid', nome.length < 2);
        if (nome.length < 2) return;
        let r;
        try { r = await PP.api('/admin/api', { action: 'segmento.salvar', slug: editando, nome, desc: $('desc').value.trim(), grupo: $('grupo').value, icon: icone }); }
        catch (err) { $('nome-err').textContent = err.message; $('nome').closest('.field').classList.add('invalid'); return; }
        PP.toast(editando ? 'Segmento salvo' : r.segmento.nome + ' adicionado. Já aparece no site.');
        lista = r.segmentos; modo(null); render();
      });

      $('rows').addEventListener('click', async e => {
        const b = e.target.closest('[data-act]'); if (!b) return;
        const s = lista.find(x => x.slug === b.closest('tr').dataset.slug);
        if (b.dataset.act === 'edit') { modo(s); $('form').scrollIntoView({ behavior: 'smooth', block: 'start' }); return; }
        if (!confirm('Remover o segmento ' + s.nome + '?')) return;
        const r = await A.busy(b, () => PP.api('/admin/api', { action: 'segmento.remover', slug: s.slug }));
        if (!r) return;
        lista = r.segmentos; if (editando === s.slug) modo(null);
        PP.toast('Segmento removido'); render();
      });

      modo(null); render();
    })();
  </script>
</body>
</html>
