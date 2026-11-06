<?php
require __DIR__ . '/inc/bootstrap.php';
session_if_cookie();

/* /empresa-a-venda/academia-em-curitiba-pr-pp1101 -> code is the last part */
$slug = (string) ($_GET['slug'] ?? '');
$codigo = $slug !== '' ? substr($slug, strrpos($slug, '-') + 1) : (string) ($_GET['codigo'] ?? '');
$a = preg_match('/^pp\d+$/i', $codigo) ? anuncio_por_codigo($codigo) : null;

$equipeLogada = current_user() !== null;
$cid = $a ? cidade($a['cidade']) : null;
$visivel = $a && $a['status'] === 'publicado' && $cid && $cid['ativa'];
if (!$a || (!$visivel && !$equipeLogada)) not_found();

/* old or wrong slug -> canonical URL */
$url = anuncio_url($a);
if ($visivel && $slug !== anuncio_slug($a)) redirect($url, 301);

$s = seg($a['segmento']);
$where = $cid ? $cid['nome'] . '/' . $cid['uf'] : $a['cidade'];
$title = $s['nome'] . ' à venda em ' . $where;
$fotos = $a['fotos'] ?: seg_fotos($a['segmento']);
$consultor = consultor_para($cid['uf'] ?? '');
$waText = 'Olá! Tenho interesse no anúncio ' . $a['codigo'] . ' (' . $s['nome'] . ' em ' . $where . ', ' . money($a['preco']) . '). Pode me passar mais informações?';
$anos = $a['fundacao'] ? (int) date('Y') - $a['fundacao'] : null;
$bairroWhere = ($a['bairro'] ? $a['bairro'] . ', ' : '') . $where;

$similares = array_slice(array_values(array_filter(anuncios_publicados(),
    fn($x) => $x['id'] !== $a['id'] && ($x['segmento'] === $a['segmento'] || $x['cidade'] === $a['cidade']))), 0, 3);

$pageUrl = site_url($url);
page_head([
    'title' => $title . ' · ' . money($a['preco']) . ' · ' . cfg('marca'),
    'description' => $s['nome'] . ' à venda em ' . $bairroWhere . ' por ' . money($a['preco']) . '. Faturamento médio de ' . money($a['faturamento']) . '/mês, ' . $a['funcionarios'] . ' funcionários. Código ' . $a['codigo'] . '.',
    'canonical' => $url,
    'image' => photo_url($fotos[0], 1200),
    'page' => 'comprar',
    'robots' => $visivel ? null : 'noindex, nofollow',
    'jsonld' => [
        ['@context' => 'https://schema.org', '@type' => 'Product', 'name' => $title, 'sku' => $a['codigo'], 'category' => $s['nome'],
         'description' => $a['descricao'], 'image' => array_map(fn($f) => str_starts_with(photo_url($f), '/') ? site_url($f) : photo_url($f, 1200), array_slice($fotos, 0, 5)),
         'offers' => ['@type' => 'Offer', 'price' => $a['preco'], 'priceCurrency' => 'BRL', 'availability' => 'https://schema.org/InStock', 'url' => $pageUrl,
                      'areaServed' => ['@type' => 'City', 'name' => $cid['nome'] ?? '']]],
        ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => site_url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Comprar empresa', 'item' => site_url('/comprar-empresa')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $where, 'item' => site_url(cidade_url($a['cidade']))],
            ['@type' => 'ListItem', 'position' => 4, 'name' => $title, 'item' => $pageUrl],
        ]],
    ],
]);
site_header('comprar');

$item = function ($icon, $label, $value) {
    if ($value === '' || $value === null) return '';
    return '<div class="data-item"><span class="di-icon">' . ic($icon) . '</span><div><small>' . $label . '</small><strong>' . h($value) . '</strong></div></div>';
};
$gal = array_slice($fotos, 0, 3);
?>
  <main id="main" class="container">
<?php if (!$visivel): ?>
    <div class="note warn" style="margin-top:20px"><?= ic('eye') ?><span>Pré-visualização da equipe. Esse anúncio não está publicado (status: <?= h($a['status']) ?>).</span></div>
<?php endif; ?>
    <div class="detail-head">
      <ol class="breadcrumb dark"><li><a href="/">Início</a></li><li><a href="/comprar-empresa">Comprar empresa</a></li><li><a href="<?= cidade_url($a['cidade']) ?>"><?= h($where) ?></a></li><li><?= h($s['nome']) ?></li></ol>
      <div class="detail-title"><div>
        <div class="detail-meta"><span class="tag green"><?= ic($s['icon']) . h($s['nome']) ?></span><span class="tag"><?= ic('hash') ?>Cód. <?= h($a['codigo']) ?></span><span class="tag"><?= ic('calendar') ?>Publicado em <?= date_br($a['data']) ?></span><?= $a['destaque'] ? '<span class="tag">' . ic('star') . 'Destaque</span>' : '' ?></div>
        <h1><?= h($title) ?></h1>
        <div class="detail-meta"><?= ic('map-pin') . h($bairroWhere) ?></div>
      </div>
      <button class="btn btn-outline" id="share"><?= ic('share-2') ?>Compartilhar</button></div>
      <div class="gallery<?= count($gal) === 1 ? ' one' : '' ?>">
        <?php foreach ($gal as $i => $f): ?><button class="<?= $i === 0 ? 'g-main' : '' ?>" data-i="<?= $i ?>" aria-label="Ver foto <?= $i + 1 ?>"><img src="<?= h(photo_url($f, $i === 0 ? 1200 : 700)) ?>" alt="<?= h($title) ?> foto <?= $i + 1 ?>"<?= $i ? ' loading="lazy"' : '' ?>><?php
          if ($i === 0 && $a['fotoTipo'] === 'ilustrativas') echo '<span class="g-note">' . ic('info') . 'Fotos ilustrativas</span>';
          if ($i === 2 && count($fotos) > 3) { $n = count($fotos) - 3; echo '<span class="g-more">+' . $n . ($n === 1 ? ' foto' : ' fotos') . '</span>'; }
        ?></button><?php endforeach; ?>
      </div>
    </div>

    <div class="detail-layout"><div>
      <section class="panel"><h2><?= ic('file-text') ?>Sobre o negócio</h2><p style="margin:0;white-space:pre-line"><?= h($a['descricao']) ?></p></section>
      <section class="panel"><h2><?= ic('building-2') ?>Estrutura e operação</h2><div class="data-grid">
        <?= $a['fundacao'] ? $item('calendar-check', 'Fundação', $a['fundacao'] . ' (' . $anos . ($anos === 1 ? ' ano' : ' anos') . ')') : '' ?>
        <?= $item('users', 'Funcionários', $a['funcionarios']) ?>
        <?= $a['area'] ? $item('ruler', 'Área', num($a['area']) . ' m²') : '' ?>
        <?= $item('clock', 'Funcionamento', $a['horario']) ?>
        <?= $item('file-badge', 'Venda com CNPJ', $a['cnpj']) ?>
        <?= $item('badge-check', 'Marca inclusa', $a['marca'] ? 'Sim' : 'Não') ?>
      </div></section>
      <section class="panel"><h2><?= ic('bar-chart-3') ?>Números do negócio</h2><div class="data-grid">
        <?= $item('trending-up', 'Faturamento médio/mês', money($a['faturamento'])) ?>
        <?= $item('percent', 'Margem de lucro', $a['margem'] ? rtrim(rtrim(number_format($a['margem'], 1, ',', ''), '0'), ',') . '%' : 'A apurar') ?>
        <?= $item('wallet', 'Folha de pagamento', money($a['folha'])) ?>
        <?= $item('home', 'Aluguel + condomínio', $a['aluguel'] ? money($a['aluguel']) : 'Imóvel próprio') ?>
        <?= $item('package', 'Estoque atual', $a['estoque'] ? money($a['estoque']) : 'Sem estoque') ?>
        <?= $item('tag', 'Valor pedido', money($a['preco'])) ?>
      </div></section>
      <div class="note"><?= ic('shield-check') ?><span><strong>Por que o nome da empresa não aparece?</strong><br>Pra proteger o vendedor, clientes e funcionários. O nome e o endereço são passados pela nossa equipe só pra compradores interessados de verdade.</span></div>
    </div>

    <aside><div class="side-card">
      <span class="price-label">Valor pedido</span><div class="price"><?= money($a['preco']) ?></div>
      <a class="btn btn-wa btn-lg" href="<?= h(whatsapp_url($waText)) ?>" target="_blank" rel="noopener"><?= ic('message-circle') ?>Tenho interesse</a>
      <a class="btn btn-outline" href="/contato?anuncio=<?= rawurlencode($a['codigo']) ?>" rel="nofollow"><?= ic('mail') ?>Enviar mensagem</a>
      <ul class="side-list">
        <li><span>Código</span><strong><?= h($a['codigo']) ?></strong></li>
        <li><span>Segmento</span><strong><?= h($s['nome']) ?></strong></li>
        <li><span>Local</span><strong><?= h($bairroWhere) ?></strong></li>
        <li><span>Faturamento/mês</span><strong><?= money($a['faturamento']) ?></strong></li>
      </ul>
      <?php if ($consultor): ?><div class="consultant"><img class="avatar" src="<?= h(photo_url($consultor['foto'], 120)) ?>" alt="<?= h($consultor['nome']) ?>" loading="lazy"><div><strong><?= h($consultor['nome']) ?></strong><span><?= h($consultor['cargo']) ?></span></div></div><?php endif; ?>
    </div></aside></div>

<?php if ($similares): ?>
    <section style="padding-bottom:70px"><div class="section-head"><div><span class="kicker">Veja também</span><h2>Negócios parecidos</h2></div><a class="btn btn-outline" href="<?= cidade_url($a['cidade']) ?>">Mais empresas em <?= h($cid['nome'] ?? '') ?> <?= ic('arrow-right') ?></a></div>
      <div class="cards-grid"><?= implode('', array_map('card', $similares)) ?></div></section>
<?php endif; ?>
  </main>

  <div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Fotos do anúncio">
    <button class="lb-close" id="lb-close" aria-label="Fechar"><?= ic('x') ?></button>
    <button class="lb-prev" id="lb-prev" aria-label="Foto anterior"><?= ic('chevron-left') ?></button>
    <img id="lb-img" alt="">
    <button class="lb-next" id="lb-next" aria-label="Próxima foto"><?= ic('chevron-right') ?></button>
    <span class="lb-count" id="lb-count"></span>
  </div>

<?php site_footer(); page_scripts(['fotos' => array_map(fn($f) => photo_url($f, 1600), $fotos), 'titulo' => $title, 'url' => $pageUrl]); ?>
  <script>
    (function () {
      const B = PP.BOOT, fotos = B.fotos, title = B.titulo;
      document.getElementById('share').addEventListener('click', async () => {
        if (navigator.share) { try { await navigator.share({ title, url: B.url }); } catch (e) {} return; }
        try { await navigator.clipboard.writeText(B.url); PP.toast('Link copiado'); } catch (e) { PP.toast(B.url); }
      });
      const lb = document.getElementById('lightbox'), lbImg = document.getElementById('lb-img');
      let cur = 0;
      function show(i) { cur = (i + fotos.length) % fotos.length; lbImg.src = fotos[cur]; lbImg.alt = title + ' foto ' + (cur + 1); document.getElementById('lb-count').textContent = (cur + 1) + ' / ' + fotos.length; }
      document.querySelectorAll('.gallery button').forEach(b => b.addEventListener('click', () => { show(Number(b.dataset.i)); lb.classList.add('open'); }));
      document.getElementById('lb-close').onclick = () => lb.classList.remove('open');
      document.getElementById('lb-prev').onclick = () => show(cur - 1);
      document.getElementById('lb-next').onclick = () => show(cur + 1);
      lb.addEventListener('click', e => { if (e.target === lb) lb.classList.remove('open'); });
      document.addEventListener('keydown', e => {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape') lb.classList.remove('open');
        if (e.key === 'ArrowLeft') show(cur - 1);
        if (e.key === 'ArrowRight') show(cur + 1);
      });
    })();
  </script>
</body>
</html>
