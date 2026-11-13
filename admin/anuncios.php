<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
admin_head('Anúncios', 'anuncios');
?>
  <div class="adm">
    <aside id="adm-side"></aside>
    <div class="adm-main">
      <header id="adm-top"></header>
      <main class="adm-content">
        <div class="adm-card">
          <div class="adm-toolbar">
            <div class="tabs" id="tabs"></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1;justify-content:flex-end">
              <div class="input-icon"><i data-lucide="search"></i><input class="input" id="q" type="search" placeholder="Buscar por código, cidade, segmento..."></div>
              <a class="btn btn-primary" href="/admin/anuncio-editar"><i data-lucide="plus"></i> Novo anúncio</a>
            </div>
          </div>
          <table class="table">
            <thead><tr><th>Anúncio</th><th>Valor</th><th>Status</th><th>Destaque</th><th>Publicado</th><th style="text-align:right">Ações</th></tr></thead>
            <tbody id="rows"></tbody>
          </table>
        </div>
      </main>
    </div>
  </div>

  <?php admin_scripts(['anuncios' => anuncios_todos()]); ?>
  <script>
    (function () {
      const { Store, esc, money, ic } = PP, A = PP.Admin;
      const $ = id => document.getElementById(id);
      let filtro = 'todos', q = '';

      function render() {
        const all = Store.anuncios();
        const n = s => all.filter(a => a.status === s).length;
        $('tabs').innerHTML = [['todos', 'Todos (' + all.length + ')'], ['publicado', 'Publicados (' + n('publicado') + ')'], ['pausado', 'Pausados (' + n('pausado') + ')'], ['rascunho', 'Rascunhos (' + n('rascunho') + ')']]
          .map(([k, l]) => '<button data-f="' + k + '" class="' + (filtro === k ? 'active' : '') + '">' + l + '</button>').join('');
        const term = PP.slugify(q);
        const list = all.filter(a => (filtro === 'todos' || a.status === filtro) &&
          (!term || PP.slugify([a.codigo, a.titulo, PP.seg(a.segmento).nome, PP.cidadeLabel(a.cidade), a.bairro].join(' ')).includes(term)));

        $('rows').innerHTML = list.length ? list.map(a => {
          const s = PP.seg(a.segmento);
          const foto = PP.photoUrl(a.fotos[0] || s.fotos[0], 200);
          const viewUrl = PP.anuncioUrl(a) + (a.status === 'publicado' ? '' : '?preview=1');
          return '<tr data-id="' + a.id + '">' +
            '<td><div class="t-flex"><img class="t-thumb" src="' + foto + '" alt=""><div><span class="t-main">' + esc(a.titulo || s.nome) + ' em ' + esc(PP.cidadeLabel(a.cidade)) + '</span><span class="t-sub">' + esc(a.codigo) + ' · ' + (a.titulo ? esc(s.nome) + ' · ' : '') + esc(a.bairro) + '</span></div></div></td>' +
            '<td data-l="Valor"><strong>' + money(a.preco) + '</strong></td>' +
            '<td>' + A.statusPill(a.status) + '</td>' +
            '<td data-l="Destaque"><button class="icon-btn' + (a.destaque ? ' on' : '') + '" data-act="destaque" title="Destaque na home" aria-label="Destaque">' + ic('star') + '</button></td>' +
            '<td data-l="Publicado">' + PP.dateBR(a.data) + '</td>' +
            '<td class="actions-cell">' +
              '<a class="icon-btn" href="' + viewUrl + '" target="_blank" title="Ver no site" aria-label="Ver no site">' + ic('eye') + '</a>' +
              '<a class="icon-btn" href="/admin/anuncio-editar?id=' + a.id + '" title="Editar" aria-label="Editar">' + ic('pencil') + '</a>' +
              '<button class="icon-btn" data-act="toggle" title="' + (a.status === 'publicado' ? 'Pausar' : 'Publicar') + '" aria-label="Pausar ou publicar">' + ic(a.status === 'publicado' ? 'pause' : 'play') + '</button>' +
              '<button class="icon-btn danger" data-act="del" title="Excluir" aria-label="Excluir">' + ic('trash-2') + '</button>' +
            '</td></tr>';
        }).join('') : '<tr><td colspan="6"><div class="adm-empty">' + ic('megaphone') + '<p>Nenhum anúncio aqui.</p></div></td></tr>';
        PP.icons();
      }

      document.addEventListener('click', async e => {
        const f = e.target.closest('[data-f]');
        if (f) { filtro = f.dataset.f; render(); return; }
        const b = e.target.closest('[data-act]'); if (!b) return;
        const a = Store.anuncio(b.closest('tr').dataset.id);
        let ok = null;
        if (b.dataset.act === 'destaque') {
          ok = await A.busy(b, () => Store.patchAnuncio(a.id, { destaque: !a.destaque }));
          if (ok) PP.toast(ok.destaque ? 'Marcado como destaque' : 'Destaque removido');
        }
        if (b.dataset.act === 'toggle') {
          const st = a.status === 'publicado' ? 'pausado' : 'publicado';
          ok = await A.busy(b, () => Store.patchAnuncio(a.id, { status: st }));
          if (ok) PP.toast(st === 'publicado' ? 'Anúncio publicado' : 'Anúncio pausado');
        }
        if (b.dataset.act === 'del') {
          if (!confirm('Excluir o anúncio ' + a.codigo + '? Isso não tem volta.')) return;
          ok = await A.busy(b, () => Store.removeAnuncio(a.id).then(() => true));
          if (ok) PP.toast('Anúncio excluído');
        }
        render();
      });
      $('q').addEventListener('input', e => { q = e.target.value; render(); });
      render();
    })();
  </script>
</body>
</html>
