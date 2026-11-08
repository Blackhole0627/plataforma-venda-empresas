<?php
require __DIR__ . '/inc/bootstrap.php';

const MAXV = 3500000;
const PER_PAGE = 9;

/* /comprar-empresa?cidade=x  ->  /comprar-empresa/x */
if (!empty($_GET['cidade']) && empty($_GET['cidade_path'])) {
    $qs = $_GET;
    $c = (string) $qs['cidade'];
    unset($qs['cidade']);
    redirect(cidade_url(slugify($c)) . ($qs ? '?' . http_build_query($qs) : ''), 301);
}
$cidadeSlug = (string) ($_GET['cidade_path'] ?? '');
$cid = null;
if ($cidadeSlug !== '') {
    $cid = cidade($cidadeSlug);
    if (!$cid || !$cid['ativa']) not_found();
}

$all = anuncios_publicados();
$segs = array_values(array_filter(explode(',', (string) ($_GET['segmento'] ?? '')), 'seg_exists'));
$min = max(0, (int) ($_GET['min'] ?? 0));
$max = (int) ($_GET['max'] ?? 0) ?: MAXV;
$ordem = (string) ($_GET['ordem'] ?? 'recentes');
$page = max(1, (int) ($_GET['pagina'] ?? 1));
$q = trim((string) ($_GET['q'] ?? ''));
if (preg_match('/^(?:pp)?\s*(\d{3,})$/i', $q, $m)) {
    $porCodigo = anuncio_por_codigo('PP' . $m[1]);
    if ($porCodigo && $porCodigo['status'] === 'publicado' && (cidade($porCodigo['cidade'])['ativa'] ?? false)) redirect(anuncio_url($porCodigo));
}
$qn = str_replace('-', ' ', slugify($q));

$list = array_values(array_filter($all, function ($a) use ($cidadeSlug, $segs, $min, $max, $qn) {
    if ($cidadeSlug && $a['cidade'] !== $cidadeSlug) return false;
    if ($segs && !in_array($a['segmento'], $segs, true)) return false;
    if ($a['preco'] < $min || ($max < MAXV && $a['preco'] > $max)) return false;
    if ($qn !== '') {
        $hay = str_replace('-', ' ', slugify(implode(' ', [$a['codigo'], $a['bairro'], seg($a['segmento'])['nome'], cidade_label($a['cidade']), $a['descricao']])));
        if (!str_contains($hay, $qn)) return false;
    }
    return true;
}));
$sorters = [
    'menor' => fn($x, $y) => $x['preco'] <=> $y['preco'],
    'maior' => fn($x, $y) => $y['preco'] <=> $x['preco'],
    'faturamento' => fn($x, $y) => $y['faturamento'] <=> $x['faturamento'],
    'recentes' => fn($x, $y) => $y['data'] <=> $x['data'],
];
usort($list, $sorters[$ordem] ?? $sorters['recentes']);
$pages = max(1, (int) ceil(count($list) / PER_PAGE));
$page = min($page, $pages);
$slice = array_slice($list, ($page - 1) * PER_PAGE, PER_PAGE);

$s1 = count($segs) === 1 ? seg($segs[0]) : null;
$where = $cid ? ' em ' . $cid['nome'] . '/' . $cid['uf'] : '';
$titulo = ($s1 ? $s1['nome'] : 'Empresas') . ' à venda' . $where;
$sub = $cid ? 'Negócios à venda' . $where . ' com faturamento, estrutura e valores. O nome das empresas fica em sigilo.'
    : 'Negócios prontos para operar. Filtre por segmento, cidade e faixa de valor.';
$filtrado = $q !== '' || $page > 1 || count($segs) > 1 || $min || $max < MAXV;
$canonical = ($cid ? cidade_url($cid['slug']) : '/comprar-empresa') . ($s1 ? '?segmento=' . $s1['slug'] : '');

$crumbs = [['Início', '/'], ['Comprar empresa', '/comprar-empresa']];
if ($cid) $crumbs[] = [$cid['nome'] . '/' . $cid['uf'], cidade_url($cid['slug'])];
$ld = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => []];
foreach ($crumbs as $i => [$n, $u]) $ld['itemListElement'][] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $n, 'item' => site_url($u)];
$itemList = ['@context' => 'https://schema.org', '@type' => 'ItemList',
    'itemListElement' => array_map(fn($a, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => site_url(anuncio_url($a))], $slice, array_keys($slice))];

page_head([
    'title' => ($titulo === 'Empresas à venda' ? 'Comprar empresa' : $titulo) . ' · ' . cfg('marca'),
    'description' => ($s1 ? $s1['nome'] . ' à venda' : 'Empresas à venda') . $where . '. Veja valores, faturamento e estrutura de cada negócio e fale com nossa equipe pelo WhatsApp.',
    'canonical' => $canonical,
    'page' => 'comprar',
    'robots' => $filtrado ? 'noindex, follow' : null,
    'jsonld' => [$ld, $itemList],
]);
site_header('comprar');
?>
  <main>
    <section class="page-head">
      <div class="container">
        <ol class="breadcrumb" id="crumbs"><li><a href="/">Início</a></li><li><a href="/comprar-empresa">Comprar empresa</a></li><?php if ($cid): ?><li><?= h($cid['nome'] . '/' . $cid['uf']) ?></li><?php endif; ?></ol>
        <h1 id="page-title"><?= h($titulo) ?></h1>
        <p id="page-sub"><?= h($sub) ?></p>
      </div>
    </section>

    <div class="container list-layout">
      <!-- FILTERS -->
      <aside class="filters" id="filters" aria-label="Filtros">
        <h3>Filtrar anúncios
          <button class="btn btn-ghost btn-sm filters-close" id="filters-close" aria-label="Fechar filtros"><i data-lucide="x"></i></button>
        </h3>

        <div class="filter-block">
          <label class="label" for="f-q">Buscar</label>
          <div class="input-icon"><i data-lucide="search"></i><input class="input" id="f-q" type="search" placeholder="Código, bairro, palavra..."></div>
        </div>

        <div class="filter-block">
          <label class="label" for="f-cidade">Cidade</label>
          <select class="select" id="f-cidade"><option value="">Todas as cidades</option></select>
        </div>

        <div class="filter-block">
          <span class="label">Faixa de valor</span>
          <div class="range-wrap">
            <div class="track"></div><div class="fill" id="r-fill"></div>
            <input type="range" id="r-min" min="0" max="3500000" step="10000" value="0" aria-label="Valor mínimo">
            <input type="range" id="r-max" min="0" max="3500000" step="10000" value="3500000" aria-label="Valor máximo">
          </div>
          <div class="range-vals">
            <input class="input" id="v-min" data-mask="money" placeholder="Mínimo" aria-label="Valor mínimo">
            <input class="input" id="v-max" data-mask="money" placeholder="Máximo" aria-label="Valor máximo">
          </div>
        </div>

        <div class="filter-block">
          <span class="label">Segmento</span>
          <div class="seg-list" id="f-seg"></div>
        </div>

        <div class="filter-block">
          <button class="btn btn-outline btn-block" id="f-clear"><i data-lucide="rotate-ccw"></i> Limpar filtros</button>
        </div>
      </aside>

      <!-- RESULTS -->
      <section>
        <div class="list-toolbar">
          <span class="count" id="count">Exibindo <strong><?= count($list) ?></strong> <?= count($list) === 1 ? 'empresa' : 'empresas' ?></span>
          <div style="display:flex;gap:10px">
            <button class="btn btn-outline btn-sm filters-open" id="filters-open"><i data-lucide="sliders-horizontal"></i> Filtros</button>
            <select class="select" id="f-ordem" aria-label="Ordenar">
              <option value="recentes">Mais recentes</option>
              <option value="menor">Menor valor</option>
              <option value="maior">Maior valor</option>
              <option value="faturamento">Maior faturamento</option>
            </select>
          </div>
        </div>
        <div class="active-filters" id="active-filters"></div>
        <div class="cards-grid" id="results"><?= implode('', array_map('card', $slice)) ?></div>
        <nav class="pagination" id="pagination" aria-label="Páginas"><?php if ($pages > 1) for ($i = 1; $i <= $pages; $i++): $qs = $_GET; unset($qs['cidade_path']); $qs['pagina'] = $i; ?><a href="?<?= h(http_build_query($qs)) ?>"<?= $i === $page ? ' class="active"' : '' ?>><?= $i ?></a><?php endfor; ?></nav>
        <div class="seo-text" id="seo-text"></div>
      </section>
    </div>
  </main>

<?php site_footer(); page_scripts(['anuncios' => $all]); ?>
  <script>
    (function () {
      const { Store, SEGMENTOS, esc, money } = PP;
      const PER_PAGE = 9, MAXV = 3500000;
      const all = Store.anuncios();
      const qs = new URLSearchParams(location.search);

      // city from friendly URL: /comprar-empresa/curitiba-pr
      const pathCity = (location.pathname.match(/^\/comprar-empresa\/([a-z0-9-]+)\/?$/) || [])[1];

      const state = {
        q: qs.get('q') || '',
        cidade: pathCity || qs.get('cidade') || '',
        segs: (qs.get('segmento') || '').split(',').filter(Boolean),
        min: Number(qs.get('min')) || 0,
        max: Number(qs.get('max')) || MAXV,
        ordem: qs.get('ordem') || 'recentes',
        page: Number(qs.get('pagina')) || 1
      };

      const $ = id => document.getElementById(id);
      const elQ = $('f-q'), elCid = $('f-cidade'), elOrd = $('f-ordem');
      const rMin = $('r-min'), rMax = $('r-max'), vMin = $('v-min'), vMax = $('v-max');

      // cities
      Store.cidades(true).slice().sort((a, b) => a.nome.localeCompare(b.nome)).forEach(c =>
        elCid.insertAdjacentHTML('beforeend', '<option value="' + c.slug + '">' + esc(c.nome) + '/' + c.uf + '</option>'));

      // segments with counts
      $('f-seg').innerHTML = SEGMENTOS.map(s => {
        const n = all.filter(a => a.segmento === s.slug).length;
        return '<label class="seg-opt"><input type="checkbox" value="' + s.slug + '">' + esc(s.nome) + '<em>' + n + '</em></label>';
      }).join('');

      function syncInputs() {
        elQ.value = state.q; elCid.value = state.cidade; elOrd.value = state.ordem;
        rMin.value = state.min; rMax.value = state.max;
        vMin.value = state.min ? 'R$ ' + state.min.toLocaleString('pt-BR') : '';
        vMax.value = state.max < MAXV ? 'R$ ' + state.max.toLocaleString('pt-BR') : '';
        document.querySelectorAll('#f-seg input').forEach(i => { i.checked = state.segs.includes(i.value); });
        paintRange();
      }
      function paintRange() {
        const a = (rMin.value / MAXV) * 100, b = (rMax.value / MAXV) * 100;
        $('r-fill').style.left = a + '%'; $('r-fill').style.width = (b - a) + '%';
      }

      function filtered() {
        const q = PP.slugify(state.q).replace(/-/g, ' ');
        let l = all.filter(a =>
          (!state.cidade || a.cidade === state.cidade) &&
          (!state.segs.length || state.segs.includes(a.segmento)) &&
          a.preco >= state.min && (state.max >= MAXV || a.preco <= state.max) &&
          (!q || PP.slugify([a.codigo, a.bairro, PP.seg(a.segmento).nome, PP.cidadeLabel(a.cidade), a.descricao].join(' ')).replace(/-/g, ' ').includes(q))
        );
        const sorters = {
          recentes: (x, y) => y.data - x.data,
          menor: (x, y) => x.preco - y.preco,
          maior: (x, y) => y.preco - x.preco,
          faturamento: (x, y) => y.faturamento - x.faturamento
        };
        return l.sort(sorters[state.ordem] || sorters.recentes);
      }

      function updateUrl() {
        const p = new URLSearchParams();
        if (state.q) p.set('q', state.q);
        if (state.cidade) p.set('cidade', state.cidade);
        if (state.segs.length) p.set('segmento', state.segs.join(','));
        if (state.min) p.set('min', state.min);
        if (state.max < MAXV) p.set('max', state.max);
        if (state.ordem !== 'recentes') p.set('ordem', state.ordem);
        if (state.page > 1) p.set('pagina', state.page);
        p.delete('cidade');
        const base = state.cidade ? PP.cidadeUrl(state.cidade) : '/comprar-empresa';
        history.replaceState(null, '', base + (p.toString() ? '?' + p : ''));
      }

      function headings() {
        const c = state.cidade ? PP.cidade(state.cidade) : null;
        const s = state.segs.length === 1 ? PP.seg(state.segs[0]) : null;
        const where = c ? ' em ' + c.nome + '/' + c.uf : '';
        const what = s ? s.nome : 'Empresas';
        const title = what + ' à venda' + where;
        $('page-title').textContent = title;
        $('page-sub').textContent = c
          ? 'Negócios à venda' + where + ' com faturamento, estrutura e valores. O nome das empresas fica em sigilo.'
          : 'Negócios prontos para operar. Filtre por segmento, cidade e faixa de valor.';
        PP.setMeta((title === 'Empresas à venda' ? 'Comprar empresa' : title) + ' · ' + PP.CONFIG.marca,
          (s ? s.nome + ' à venda' : 'Empresas à venda') + where + '. Veja valores, faturamento e estrutura de cada negócio e fale com nossa equipe pelo WhatsApp.');

        let crumbs = '<li><a href="/">Início</a></li><li><a href="/comprar-empresa">Comprar empresa</a></li>';
        if (c) crumbs += '<li>' + esc(c.nome) + '/' + c.uf + '</li>';
        $('crumbs').innerHTML = crumbs;

        $('seo-text').innerHTML = c
          ? '<h2>Comprar empresa' + where + '</h2><p>Procurando um negócio pronto' + where + '? Aqui você encontra empresas à venda de vários segmentos, com dados de faturamento, número de funcionários, aluguel e estrutura. Todos os anúncios passam pela nossa equipe e o nome da empresa só é revelado para compradores interessados.</p>'
          : '<h2>Como comprar uma empresa com segurança</h2><p>Todos os anúncios passam pela nossa equipe antes de ir pro ar. O nome da empresa fica em sigilo e você recebe os detalhes completos quando fala com um consultor. Use os filtros pra achar negócios por cidade, segmento e valor.</p>';
      }

      function chips() {
        const out = [];
        if (state.q) out.push(['q', '“' + state.q + '”']);
        if (state.cidade) out.push(['cidade', PP.cidadeLabel(state.cidade)]);
        state.segs.forEach(s => out.push(['seg:' + s, PP.seg(s).nome]));
        if (state.min || state.max < MAXV) out.push(['valor', PP.moneyShort(state.min) + ' a ' + (state.max < MAXV ? PP.moneyShort(state.max) : 'sem limite')]);
        $('active-filters').innerHTML = out.map(([k, l]) => '<button class="pill-filter" data-k="' + k + '">' + esc(l) + PP.ic('x') + '</button>').join('');
      }

      function render() {
        const list = filtered();
        const pages = Math.max(1, Math.ceil(list.length / PER_PAGE));
        if (state.page > pages) state.page = pages;
        const slice = list.slice((state.page - 1) * PER_PAGE, state.page * PER_PAGE);

        $('count').innerHTML = 'Exibindo <strong>' + list.length + '</strong> ' + (list.length === 1 ? 'empresa' : 'empresas');
        $('results').innerHTML = slice.length ? slice.map(PP.card).join('') :
          '<div class="empty" style="grid-column:1/-1"><div class="empty-icon">' + PP.ic('search-x') + '</div><h3>Nenhuma empresa encontrada</h3><p class="muted">Tente mudar os filtros ou cadastre seu interesse com a nossa equipe.</p><a class="btn btn-primary" href="' + PP.whatsappUrl('Olá! Procuro uma empresa e não achei no site.') + '" target="_blank" rel="noopener">' + PP.ic('message-circle') + 'Falar com a equipe</a></div>';

        let pg = '';
        if (pages > 1) {
          pg += '<button data-p="' + (state.page - 1) + '" ' + (state.page === 1 ? 'disabled' : '') + ' aria-label="Anterior">' + PP.ic('chevron-left') + '</button>';
          for (let i = 1; i <= pages; i++) pg += '<button data-p="' + i + '" class="' + (i === state.page ? 'active' : '') + '">' + i + '</button>';
          pg += '<button data-p="' + (state.page + 1) + '" ' + (state.page === pages ? 'disabled' : '') + ' aria-label="Próxima">' + PP.ic('chevron-right') + '</button>';
        }
        $('pagination').innerHTML = pg;

        headings(); chips(); updateUrl(); PP.icons();
      }

      function change(patch) { Object.assign(state, patch, { page: 1 }); syncInputs(); render(); }

      // events
      let t;
      elQ.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => change({ q: elQ.value.trim() }), 250); });
      elCid.addEventListener('change', () => change({ cidade: elCid.value }));
      elOrd.addEventListener('change', () => change({ ordem: elOrd.value }));
      $('f-seg').addEventListener('change', () => change({ segs: [...document.querySelectorAll('#f-seg input:checked')].map(i => i.value) }));
      [rMin, rMax].forEach(r => r.addEventListener('input', () => {
        if (Number(rMin.value) > Number(rMax.value) - 10000) { if (r === rMin) rMin.value = Number(rMax.value) - 10000; else rMax.value = Number(rMin.value) + 10000; }
        paintRange();
        vMin.value = Number(rMin.value) ? 'R$ ' + Number(rMin.value).toLocaleString('pt-BR') : '';
        vMax.value = Number(rMax.value) < MAXV ? 'R$ ' + Number(rMax.value).toLocaleString('pt-BR') : '';
      }));
      [rMin, rMax].forEach(r => r.addEventListener('change', () => change({ min: Number(rMin.value), max: Number(rMax.value) })));
      [vMin, vMax].forEach(v => v.addEventListener('change', () => {
        const mn = PP.unmoney(vMin.value), mx = PP.unmoney(vMax.value) || MAXV;
        change({ min: Math.min(mn, MAXV), max: Math.min(Math.max(mx, mn), MAXV) });
      }));
      $('f-clear').addEventListener('click', () => change({ q: '', cidade: '', segs: [], min: 0, max: MAXV, ordem: 'recentes' }));
      $('active-filters').addEventListener('click', e => {
        const b = e.target.closest('[data-k]'); if (!b) return;
        const k = b.dataset.k;
        if (k === 'q') change({ q: '' });
        else if (k === 'cidade') change({ cidade: '' });
        else if (k === 'valor') change({ min: 0, max: MAXV });
        else if (k.startsWith('seg:')) change({ segs: state.segs.filter(s => s !== k.slice(4)) });
      });
      $('pagination').addEventListener('click', e => {
        const b = e.target.closest('[data-p]'); if (!b || b.disabled) return;
        state.page = Number(b.dataset.p); render();
        window.scrollTo({ top: document.querySelector('.list-layout').offsetTop - 80, behavior: 'smooth' });
      });
      $('filters-open').addEventListener('click', () => document.body.classList.add('filters-show'));
      $('filters-close').addEventListener('click', () => document.body.classList.remove('filters-show'));
      document.addEventListener('click', e => {
        if (document.body.classList.contains('filters-show') && !e.target.closest('#filters') && !e.target.closest('#filters-open'))
          document.body.classList.remove('filters-show');
      });

      PP.bindMasks();
      syncInputs(); render();
    })();
  </script>
</body>
</html>
