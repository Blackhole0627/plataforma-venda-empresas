<?php
require __DIR__ . '/inc/bootstrap.php';

$anuncios = anuncios_publicados();
$porSeg = array_count_values(array_column($anuncios, 'segmento'));
$porCid = array_count_values(array_column($anuncios, 'cidade'));

$segs = array_values(array_filter(SEGMENTOS, fn($s) => $s['slug'] !== 'outros'));
usort($segs, fn($a, $b) => ($porSeg[$b['slug']] ?? 0) <=> ($porSeg[$a['slug']] ?? 0));
$segs = array_slice($segs, 0, 8);

$dest = array_merge(array_filter($anuncios, fn($a) => $a['destaque']), array_filter($anuncios, fn($a) => !$a['destaque']));
$dest = array_slice($dest, 0, 6);

$cids = cidades(true);
usort($cids, fn($a, $b) => (($porCid[$b['slug']] ?? 0) <=> ($porCid[$a['slug']] ?? 0)) ?: strcmp(slugify($a['nome']), slugify($b['nome'])));

$marca = cfg('marca');
page_head([
    'title' => $marca . ' · ' . cfg('slogan'),
    'description' => 'Compre ou venda sua empresa com sigilo. Anúncios de academias, restaurantes, farmácias, mercados e muito mais nas capitais do Brasil.',
    'canonical' => '/',
    'page' => 'home',
    'jsonld' => [
        ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $marca, 'url' => site_url('/'),
         'logo' => site_url('/assets/img/favicon.svg'), 'email' => cfg('email'),
         'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => '+' . cfg('whatsapp'), 'contactType' => 'customer service', 'areaServed' => 'BR', 'availableLanguage' => 'Portuguese']],
        ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => $marca, 'url' => site_url('/'),
         'potentialAction' => ['@type' => 'SearchAction', 'target' => site_url('/comprar-empresa?q={q}'), 'query-input' => 'required name=q']],
    ],
]);
site_header('home');
?>
  <main>
    <section class="hero">
      <div class="hero-bg" style="background-image:url('<?= photo_url(IMAGENS['heroHome'], 1800) ?>')"></div>
      <div class="container hero-inner">
        <span class="eyebrow"><?= ic('shield-check') ?> Anúncios confidenciais nas capitais do Brasil</span>
        <h1>Compre ou venda sua empresa com <em>sigilo e segurança</em></h1>
        <p class="lead">Negócios prontos para começar a faturar no dia seguinte. O nome da empresa fica guardado e só quem tem interesse de verdade fala com a nossa equipe.</p>

        <form class="search-box" id="search-form" action="/comprar-empresa">
          <div class="sb-field">
            <label for="sb-seg">Segmento</label>
            <select id="sb-seg" name="segmento"><option value="">Todos os segmentos</option>
              <?php foreach (SEGMENTOS as $s): ?><option value="<?= $s['slug'] ?>"><?= h($s['nome']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="sb-field">
            <label for="sb-cidade">Cidade</label>
            <select id="sb-cidade" name="cidade"><option value="">Todas as cidades</option>
              <?php foreach (cidades(true) as $c): ?><option value="<?= $c['slug'] ?>"><?= h($c['nome']) ?>/<?= $c['uf'] ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="sb-field">
            <label for="sb-valor">Investimento</label>
            <select id="sb-valor" name="valor">
              <option value="">Qualquer valor</option>
              <option value="0-300000">Até R$ 300 mil</option>
              <option value="300000-700000">R$ 300 mil a R$ 700 mil</option>
              <option value="700000-1500000">R$ 700 mil a R$ 1,5 mi</option>
              <option value="1500000-">Acima de R$ 1,5 mi</option>
            </select>
          </div>
          <button class="btn btn-primary btn-lg" type="submit"><?= ic('search') ?> Buscar</button>
        </form>

        <div class="hero-trust">
          <span><?= ic('lock') ?> Nome da empresa em sigilo</span>
          <span><?= ic('user-check') ?> Compradores filtrados pela equipe</span>
          <span><?= ic('message-circle') ?> Atendimento direto no WhatsApp</span>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <div>
            <span class="kicker">Segmentos</span>
            <h2>Encontre o negócio certo pra você</h2>
            <p>Escolha um segmento e veja as empresas à venda agora.</p>
          </div>
          <a class="btn btn-outline" href="/comprar-empresa">Ver todos os anúncios <?= ic('arrow-right') ?></a>
        </div>
        <div class="seg-grid">
          <?php foreach ($segs as $s): $n = $porSeg[$s['slug']] ?? 0; ?>
          <a class="seg-tile" href="/comprar-empresa?segmento=<?= $s['slug'] ?>">
            <img src="<?= photo_url($s['fotos'][0], 600) ?>" alt="<?= h($s['nome']) ?> à venda" loading="lazy">
            <div><strong><span class="seg-icon"><?= ic($s['icon']) ?></span><?= h($s['nome']) ?></strong>
            <small><?= $n ?> <?= $n === 1 ? 'empresa à venda' : 'empresas à venda' ?></small></div></a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

<?php if ($dest): ?>
    <section class="section section-alt">
      <div class="container">
        <div class="section-head">
          <div>
            <span class="kicker">Destaques</span>
            <h2>Empresas à venda em destaque</h2>
            <p>Negócios revisados pela nossa equipe, com dados de faturamento e estrutura.</p>
          </div>
          <a class="btn btn-outline" href="/comprar-empresa">Ver mais empresas <?= ic('arrow-right') ?></a>
        </div>
        <div class="cards-grid"><?= implode('', array_map('card', $dest)) ?></div>
      </div>
    </section>
<?php endif; ?>

    <section class="section">
      <div class="container">
        <div class="section-head center">
          <span class="kicker">Como funciona</span>
          <h2>Simples pra quem vende e pra quem compra</h2>
        </div>
        <div class="how-grid">
          <div class="how-card dark">
            <span class="how-tag"><?= ic('store') ?> Quero vender</span>
            <h3>Anuncie sua empresa sem se expor</h3>
            <ol class="how-steps">
              <li><span class="n">1</span><div><strong>Faça o cadastro</strong><p>Preencha os dados do negócio e mande as fotos. Leva uns 5 minutos.</p></div></li>
              <li><span class="n">2</span><div><strong>Nossa equipe monta o anúncio</strong><p>Revisamos tudo e publicamos sem mostrar o nome da empresa.</p></div></li>
              <li><span class="n">3</span><div><strong>Receba só contatos sérios</strong><p>A gente filtra os interessados antes de passar pra você.</p></div></li>
            </ol>
            <a class="btn btn-accent" href="/cadastro">Anunciar minha empresa <?= ic('arrow-right') ?></a>
          </div>
          <div class="how-card">
            <span class="how-tag"><?= ic('search') ?> Quero comprar</span>
            <h3>Encontre um negócio pronto pra operar</h3>
            <ol class="how-steps">
              <li><span class="n">1</span><div><strong>Busque por cidade e segmento</strong><p>Filtre por valor, cidade e tipo de negócio.</p></div></li>
              <li><span class="n">2</span><div><strong>Veja os números</strong><p>Faturamento, funcionários, aluguel e estrutura em cada anúncio.</p></div></li>
              <li><span class="n">3</span><div><strong>Fale com a equipe</strong><p>Chame no WhatsApp com o código do anúncio e a gente te atende.</p></div></li>
            </ol>
            <a class="btn btn-primary" href="/comprar-empresa">Ver empresas à venda <?= ic('arrow-right') ?></a>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <div class="section-head">
          <div>
            <span class="kicker">Cidades</span>
            <h2>Empresas à venda nas capitais</h2>
            <p>Começamos pelas capitais e estamos chegando em novas cidades.</p>
          </div>
        </div>
        <div class="city-grid">
          <?php foreach ($cids as $c): ?><a class="city-chip" href="<?= cidade_url($c['slug']) ?>"><span><?= ic('map-pin') . h($c['nome']) ?>/<?= $c['uf'] ?></span><em><?= $porCid[$c['slug']] ?? 0 ?></em></a><?php endforeach; ?>
        </div>
      </div>
    </section>

<?php if (equipe()): ?>
    <section class="section">
      <div class="container">
        <div class="section-head center">
          <span class="kicker">Atendimento</span>
          <h2>Gente de verdade cuidando do seu negócio</h2>
          <p>Cada região tem um consultor que acompanha o anúncio do começo ao fim.</p>
        </div>
        <div class="team-grid">
          <?php foreach (equipe() as $p): ?><div class="team-card"><img src="<?= h(photo_url($p['foto'], 240)) ?>" alt="<?= h($p['nome']) ?>" loading="lazy"><strong><?= h($p['nome']) ?></strong><span><?= h($p['cargo']) ?></span></div><?php endforeach; ?>
        </div>
      </div>
    </section>
<?php endif; ?>

    <section class="section" style="padding-top:0">
      <div class="container">
        <div class="cta-band">
          <div class="bg" style="background-image:url('<?= photo_url(IMAGENS['ctaVender'], 1400) ?>')"></div>
          <div>
            <h2>Quer vender sua empresa?</h2>
            <p>Faça o cadastro. Nossa equipe analisa e entra em contato com você pra montar o anúncio.</p>
          </div>
          <div class="actions">
            <a class="btn btn-white btn-lg" href="/cadastro">Cadastrar empresa</a>
            <a class="btn btn-wa btn-lg" href="<?= h(whatsapp_url('Olá! Quero vender minha empresa.')) ?>" target="_blank" rel="noopener"><?= ic('message-circle') ?> WhatsApp</a>
          </div>
        </div>
      </div>
    </section>
  </main>

<?php site_footer(); page_scripts(); ?>
  <script>
    document.getElementById('search-form').addEventListener('submit', function (e) {
      e.preventDefault();
      var f = new FormData(e.target), p = new URLSearchParams(), cidade = f.get('cidade');
      if (f.get('segmento')) p.set('segmento', f.get('segmento'));
      var v = f.get('valor');
      if (v) { var r = v.split('-'); if (r[0] && r[0] !== '0') p.set('min', r[0]); if (r[1]) p.set('max', r[1]); }
      location.href = '/comprar-empresa' + (cidade ? '/' + cidade : '') + (p.toString() ? '?' + p : '');
    });
  </script>
</body>
</html>
