<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
admin_head('Cadastros recebidos', 'cadastros');
?>
  <div class="adm">
    <aside id="adm-side"></aside>
    <div class="adm-main">
      <header id="adm-top"></header>
      <main class="adm-content">
        <div class="stats" id="stats"></div>
        <div class="adm-card">
          <div class="adm-toolbar">
            <div class="tabs" id="tabs"></div>
            <div class="input-icon"><i data-lucide="search"></i><input class="input" id="q" type="search" placeholder="Buscar por nome, empresa, protocolo..."></div>
          </div>
          <table class="table">
            <thead><tr><th>Protocolo</th><th>Empresa / responsável</th><th>Segmento</th><th>Cidade</th><th>Valor</th><th>Recebido</th><th>Status</th><th></th></tr></thead>
            <tbody id="rows"></tbody>
          </table>
        </div>
      </main>
    </div>
  </div>

  <?php admin_scripts(['cadastros' => cadastros()]); ?>
  <script>
    (function () {
      const { Store, esc, money } = PP, A = PP.Admin;
      const $ = id => document.getElementById(id);
      let filtro = PP.param('status') || 'todos', q = '';
      const ORDER = ['novo', 'analise', 'publicado', 'recusado'];

      function render() {
        const all = Store.cadastros();
        const n = s => all.filter(c => c.status === s).length;
        $('stats').innerHTML = ORDER.map(s =>
          '<button class="stat' + (filtro === s ? ' active' : '') + '" data-s="' + s + '"><span class="s-icon s-' + s + '">' + PP.ic(A.STATUS[s].icon) + '</span><div><strong>' + n(s) + '</strong><span>' + A.STATUS[s].label + '</span></div></button>').join('');
        $('tabs').innerHTML = [['todos', 'Todos (' + all.length + ')']].concat(ORDER.map(s => [s, A.STATUS[s].label]))
          .map(([k, l]) => '<button data-s="' + k + '" class="' + (filtro === k ? 'active' : '') + '">' + l + '</button>').join('');

        const term = PP.slugify(q);
        const list = all.filter(c => (filtro === 'todos' || c.status === filtro) &&
          (!term || PP.slugify([c.protocolo, c.empresa.nome, c.responsavel.nome, c.responsavel.email, c.bairro].join(' ')).includes(term)));

        $('rows').innerHTML = list.length ? list.map(c =>
          '<tr class="clickable' + (c.status === 'novo' ? ' row-new' : '') + '" data-id="' + c.id + '">' +
            '<td data-l="Protocolo"><span class="t-main" style="font-family:ui-monospace,Consolas,monospace;font-size:.82rem;white-space:nowrap">' + esc(c.protocolo) + '</span></td>' +
            '<td><span class="t-main">' + esc(c.empresa.nome) + '</span><span class="t-sub">' + esc(c.responsavel.nome) + ' · ' + esc(c.responsavel.whatsapp) + '</span></td>' +
            '<td data-l="Segmento">' + esc(PP.seg(c.segmento).nome) + '</td>' +
            '<td data-l="Cidade">' + esc(c.cidade ? PP.cidadeLabel(c.cidade) : c.cidadeOutra + ' (nova)') + '</td>' +
            '<td data-l="Valor"><strong>' + money(c.preco) + '</strong></td>' +
            '<td data-l="Recebido"><span title="' + PP.dateBR(c.data) + '">' + PP.timeAgo(c.data) + '</span></td>' +
            '<td>' + A.statusPill(c.status) + '</td>' +
            '<td class="actions-cell"><a class="btn btn-outline btn-sm" href="/admin/cadastro?id=' + c.id + '">Abrir</a></td>' +
          '</tr>').join('') :
          '<tr><td colspan="8"><div class="adm-empty">' + PP.ic('inbox') + '<p>Nenhum cadastro aqui.</p></div></td></tr>';
        PP.icons();
      }

      document.addEventListener('click', e => {
        const b = e.target.closest('[data-s]');
        if (b) { filtro = (filtro === b.dataset.s && b.classList.contains('stat')) ? 'todos' : b.dataset.s; render(); return; }
        const tr = e.target.closest('tr[data-id]');
        if (tr && !e.target.closest('a')) location.href = '/admin/cadastro?id=' + tr.dataset.id;
      });
      $('q').addEventListener('input', e => { q = e.target.value; render(); });
      render();
    })();
  </script>
</body>
</html>
