<?php
require __DIR__ . '/inc/bootstrap.php';

$anuncios = anuncios_publicados();

$dest = array_merge(array_filter($anuncios, fn($a) => $a['destaque']), array_filter($anuncios, fn($a) => !$a['destaque']));
$dest = array_slice($dest, 0, 8);

/* cities: three quick picks + every active city grouped by region for the "ver todas" sheet */
const REGIOES = [
    'Sudeste' => ['SP', 'RJ', 'MG', 'ES'],
    'Sul' => ['PR', 'SC', 'RS'],
    'Centro-Oeste' => ['DF', 'GO', 'MT', 'MS'],
    'Nordeste' => ['BA', 'PE', 'CE', 'MA', 'PB', 'RN', 'AL', 'SE', 'PI'],
    'Norte' => ['AM', 'PA', 'AC', 'RO', 'RR', 'AP', 'TO'],
];
$cids = cidades(true);
$porRegiao = [];
foreach (REGIOES as $r => $ufs) {
    $l = array_values(array_filter($cids, fn($c) => in_array($c['uf'], $ufs, true)));
    usort($l, fn($a, $b) => ($b['capital'] <=> $a['capital']) ?: strcmp(slugify($a['nome']), slugify($b['nome'])));
    if ($l) $porRegiao[$r] = $l;
}
$principais = array_values(array_filter(array_map('cidade', ['sao-paulo-sp', 'brasilia-df', 'campo-grande-ms']), fn($c) => $c && $c['ativa']));

$marca = cfg('marca');
$insta = (string) cfg('instagram', '');
$wa = whatsapp_url('Olá! Quero vender minha empresa.');
page_head([
    'title' => $marca . ' · ' . cfg('slogan'),
    'description' => 'Compre ou venda sua empresa com sigilo. Empresas em operação à venda, com faturamento e estrutura, e compradores qualificados pela nossa equipe.',
    'canonical' => '/',
    'page' => 'home',
    'jsonld' => [
        array_filter(['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $marca, 'url' => site_url('/'),
         'logo' => site_url('/assets/img/logo.png'), 'email' => cfg('email'), 'sameAs' => $insta ? [$insta] : null,
         'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => '+' . cfg('whatsapp'), 'contactType' => 'customer service', 'areaServed' => 'BR', 'availableLanguage' => 'Portuguese']]),
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
        <h1>Compra e venda de empresas, com <em>sigilo em toda a negociação</em></h1>

        <div class="tabs-big" role="tablist" aria-label="O que você quer fazer">
          <button type="button" role="tab" class="active" aria-selected="true" data-tab="comprar"><?= ic('search') ?>Quero comprar um negócio</button>
          <button type="button" role="tab" aria-selected="false" data-tab="vender"><?= ic('store') ?>Quero vender minha empresa</button>
        </div>

        <div data-pane="comprar">
          <p class="lead">Encontre empresas lucrativas e em operação para assumir. Dados financeiros claros e sigilo garantido.</p>
          <form class="search-box" id="search-form" action="/comprar-empresa">
            <div class="sb-field">
              <label for="sb-cidade">Cidade</label>
              <select id="sb-cidade" name="cidade"><option value="">Todas</option>
                <?php foreach ($cids as $c): ?><option value="<?= $c['slug'] ?>"><?= h($c['nome']) ?>/<?= $c['uf'] ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="sb-field">
              <label for="sb-seg">Segmento</label>
              <select id="sb-seg" name="segmento"><option value="">Todos</option><?= seg_options() ?></select>
            </div>
            <div class="sb-field">
              <label for="sb-valor">Valor</label>
              <select id="sb-valor" name="valor">
                <option value="">Qualquer valor</option>
                <option value="0-300000">Até R$ 300 mil</option>
                <option value="300000-700000">R$ 300 mil a R$ 700 mil</option>
                <option value="700000-1500000">R$ 700 mil a R$ 1,5 mi</option>
                <option value="1500000-">Acima de R$ 1,5 mi</option>
              </select>
            </div>
            <div class="sb-field">
              <label for="sb-codigo">Código da empresa</label>
              <input id="sb-codigo" name="codigo" placeholder="Ex.: PP1101" autocomplete="off" spellcheck="false">
            </div>
            <button class="btn btn-primary btn-lg" type="submit"><?= ic('search') ?> Buscar</button>
          </form>
        </div>

        <div data-pane="vender" hidden>
          <p class="lead">Venda sua empresa sem que funcionários, clientes ou concorrentes fiquem sabendo. Sigilo em toda a negociação e compradores filtrados.</p>
          <div class="hero-sell">
            <a class="btn btn-accent btn-xl" href="/cadastro"><?= ic('lock') ?>Cadastrar minha empresa com sigilo</a>
            <a class="hero-sell-alt" href="<?= h($wa) ?>" target="_blank" rel="noopener"><?= ic('message-circle') ?>ou fale com nossa equipe</a>
          </div>
        </div>

        <div class="hero-trust">
          <span><?= ic('lock') ?> Nome da empresa em sigilo</span>
          <span><?= ic('user-check') ?> Compradores filtrados pela equipe</span>
          <span><?= ic('message-circle') ?> Atendimento direto no WhatsApp</span>
        </div>
      </div>
    </section>

<?php if ($dest): ?>
    <section class="section">
      <div class="container" data-carousel>
        <div class="section-head">
          <div>
            <span class="kicker">Destaques</span>
            <h2>Empresas em operação prontas para assumir</h2>
            <p>Negócios com faturamento ativo, estrutura montada e sigilo preservado.</p>
          </div>
          <div class="carousel-nav">
            <button type="button" class="icon-round" data-dir="-1" aria-label="Anterior"><?= ic('chevron-left') ?></button>
            <button type="button" class="icon-round" data-dir="1" aria-label="Próximo"><?= ic('chevron-right') ?></button>
            <a class="btn btn-outline" href="/comprar-empresa">Ver todas <?= ic('arrow-right') ?></a>
          </div>
        </div>
        <div class="carousel-track"><?= implode('', array_map('card', $dest)) ?></div>
      </div>
    </section>
<?php endif; ?>

    <section class="section section-tight section-alt">
      <div class="container">
        <div class="section-head">
          <div>
            <span class="kicker">Segmentos</span>
            <h2>Encontre por segmento</h2>
          </div>
        </div>
        <div class="chip-rail" aria-label="Segmentos">
          <a class="seg-chip active" href="/comprar-empresa"><?= ic('layout-grid') ?>Todos</a>
          <?php foreach (segmentos() as $s): if (empty($s['chip'])) continue; ?><a class="seg-chip" href="/comprar-empresa?segmento=<?= $s['slug'] ?>"><?= ic($s['icon']) . h($s['nome']) ?></a><?php endforeach; ?>
          <button type="button" class="seg-chip seg-chip-all" data-sheet-open="sheet-segmentos"><?= ic('list') ?>Ver todos os segmentos</button>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head center">
          <span class="kicker">Como funciona</span>
          <h2>Simples pra quem vende e pra quem compra</h2>
        </div>
        <div class="tabs-pill" role="tablist" aria-label="Como funciona">
          <button type="button" role="tab" class="active" aria-selected="true" data-tab="comprar">Quero comprar</button>
          <button type="button" role="tab" aria-selected="false" data-tab="vender">Quero vender</button>
        </div>
        <div class="how-card how-single" data-pane="comprar">
          <ol class="how-steps how-steps-4">
            <li><span class="n">1</span><div><strong>Busque por cidade, segmento e valor</strong><p>Veja só os negócios que cabem no seu bolso e no seu perfil.</p></div></li>
            <li><span class="n">2</span><div><strong>Analise os números</strong><p>Faturamento, funcionários, aluguel e estrutura em cada anúncio.</p></div></li>
            <li><span class="n">3</span><div><strong>Fale com a equipe pelo WhatsApp</strong><p>Informe o código do anúncio. Os dados completos são liberados após uma conversa e o termo de confidencialidade.</p></div></li>
            <li><span class="n">4</span><div><strong>Negocie com acompanhamento</strong><p>A equipe te ajuda até o acordo e a assinatura.</p></div></li>
          </ol>
          <a class="btn btn-primary" href="/comprar-empresa">Ver empresas à venda <?= ic('arrow-right') ?></a>
        </div>
        <div class="how-card how-single dark" data-pane="vender" hidden>
          <ol class="how-steps how-steps-4">
            <li><span class="n">1</span><div><strong>Cadastre sua empresa em 5 minutos</strong><p>Preencha os dados e mande as fotos. O nome da empresa não aparece no anúncio.</p></div></li>
            <li><span class="n">2</span><div><strong>Nossa equipe monta o anúncio</strong><p>Revisamos os números e publicamos sem identificar o seu negócio.</p></div></li>
            <li><span class="n">3</span><div><strong>Receba compradores qualificados</strong><p>Filtramos os interessados antes de passar o contato pra você.</p></div></li>
            <li><span class="n">4</span><div><strong>Negocie e feche com acompanhamento</strong><p>A equipe faz a ponte e acompanha até a assinatura.</p></div></li>
          </ol>
          <a class="btn btn-accent" href="/cadastro">Cadastrar minha empresa <?= ic('arrow-right') ?></a>
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <div class="section-head">
          <div>
            <span class="kicker">Cidades</span>
            <h2>Onde você quer investir?</h2>
          </div>
        </div>
        <div class="city-picks">
          <?php foreach ($principais as $c): ?><a class="city-chip" href="<?= cidade_url($c['slug']) ?>"><span><?= ic('map-pin') . h($c['nome']) ?></span></a><?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline btn-block city-all" data-sheet-open="sheet-cidades"><?= ic('plus') ?>Ver todas as cidades<?= ic('chevron-down') ?></button>
        <div class="help-card">
          <div><strong>Não encontrou sua cidade?</strong><p>Nossa equipe pode te ajudar com buscas personalizadas.</p></div>
          <a class="btn btn-primary" href="<?= h(whatsapp_url('Olá! Não encontrei minha cidade no site. Podem me ajudar a buscar uma empresa?')) ?>" target="_blank" rel="noopener"><?= ic('message-circle') ?>Fale com nossa equipe</a>
        </div>
      </div>
    </section>

    <section class="section" id="quem-somos">
      <div class="container split">
        <img class="rounded" src="/assets/img/site/quem-somos.jpg" alt="Sala de reunião do escritório" loading="lazy">
        <div>
          <span class="kicker">Quem somos</span>
          <h2>Transparência para quem compra. Sigilo para quem vende.</h2>
          <p class="muted">Há mais de 2 anos, a I9 desenvolve projetos e negócios. Nesse caminho, aprendemos o que torna uma empresa valiosa, bem estruturada e atraente para quem quer investir. A <?= h($marca) ?> nasceu dessa experiência: uma assessoria que conecta donos de empresas a compradores sérios, com discrição do começo ao fim.</p>
          <p class="muted">Quem vende mantém o nome da empresa protegido. Quem compra vê números claros, como faturamento, estrutura e custos, antes de qualquer conversa.</p>
          <ul class="value-list">
            <li><?= ic('lock') ?><span><strong>Sigilo na negociação.</strong> O nome da empresa não aparece no anúncio, e os dados só são liberados mediante termo de confidencialidade.</span></li>
            <li><?= ic('scale') ?><span><strong>Avaliação realista.</strong> Estimamos o valor da sua empresa com base nos números, para anunciar por um preço que atrai os compradores certos.</span></li>
            <li><?= ic('user-check') ?><span><strong>Compradores qualificados.</strong> A nossa equipe conversa com cada interessado antes de qualquer contato.</span></li>
            <li><?= ic('handshake') ?><span><strong>Acompanhamento consultivo.</strong> Da primeira conversa até a assinatura do contrato.</span></li>
          </ul>
          <?php foreach (equipe() as $p): ?><div class="consultant"><?= avatar($p) ?><div><strong><?= h($p['nome']) ?></strong><span><?= h($p['cargo']) ?></span></div></div><?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section section-alt" id="servicos">
      <div class="container">
        <div class="section-head center">
          <span class="kicker">Nosso trabalho</span>
          <h2>Como conduzimos a venda da sua empresa</h2>
          <p>Da avaliação à assinatura, com sigilo em cada etapa.</p>
        </div>
        <div class="svc-grid svc-4">
          <article class="svc-card">
            <img src="/assets/img/site/investimento.jpg" alt="Análise dos números de uma empresa com calculadora" width="1000" height="667" loading="lazy">
            <div class="svc-body"><span class="svc-n">1</span><h3>Avaliação do negócio</h3><p class="muted">Analisamos faturamento, lucro, estrutura e mercado para chegar a uma faixa de valor realista.</p><p class="svc-get"><?= ic('check') ?>Você recebe a análise e a faixa de valor sugerida.</p></div>
          </article>
          <article class="svc-card">
            <img src="/assets/img/site/prospeccao.jpg" alt="Consultora atendendo um comprador pelo computador" width="1000" height="667" loading="lazy">
            <div class="svc-body"><span class="svc-n">2</span><h3>Anúncio e prospecção de compradores</h3><p class="muted">Montamos o anúncio sem identificar a sua empresa e buscamos compradores com perfil para o seu tipo de negócio. Os dados só são liberados mediante termo de confidencialidade.</p></div>
          </article>
          <article class="svc-card">
            <img src="/assets/img/site/intermediacao.jpg" alt="Consultora conversando com o dono de uma empresa" width="1000" height="667" loading="lazy">
            <div class="svc-body"><span class="svc-n">3</span><h3>Intermediação e negociação</h3><p class="muted">Qualificamos os interessados, conduzimos as conversas sobre proposta, prazo e condições, e apoiamos a análise de documentos pedida pelo comprador.</p></div>
          </article>
          <article class="svc-card">
            <img src="/assets/img/site/fechamento.jpg" alt="Empresário assinando o contrato de venda" width="1000" height="667" loading="lazy">
            <div class="svc-body"><span class="svc-n">4</span><h3>Fechamento</h3><p class="muted">Acompanhamos tudo até a assinatura do contrato.</p></div>
          </article>
        </div>
        <div class="svc-cta">
          <a class="btn btn-primary btn-lg" href="/cadastro">Quero vender minha empresa</a>
          <a class="link-arrow" href="/vender-empresa#investimento">Quanto custa vender com a gente <?= ic('arrow-right') ?></a>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="cta-band">
          <div class="bg" style="background-image:url('<?= photo_url(IMAGENS['ctaVender'], 1400) ?>')"></div>
          <div>
            <h2>Quer vender sua empresa?</h2>
            <p>Faça o cadastro. Nossa equipe analisa e entra em contato com você pra montar o anúncio.</p>
          </div>
          <div class="actions">
            <a class="btn btn-white btn-lg" href="/cadastro">Cadastrar empresa</a>
            <a class="btn btn-wa btn-lg" href="<?= h($wa) ?>" target="_blank" rel="noopener"><?= ic('message-circle') ?> WhatsApp</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <div class="sheet" id="sheet-cidades" aria-hidden="true">
    <div class="sheet-box" role="dialog" aria-modal="true" aria-labelledby="sheet-cid-title">
      <div class="sheet-head"><h2 id="sheet-cid-title">Selecione a cidade</h2><button type="button" class="icon-round" data-sheet-close aria-label="Fechar"><?= ic('x') ?></button></div>
      <div class="input-icon"><?= ic('search') ?><input class="input" type="search" placeholder="Digite a cidade ou estado..." data-sheet-filter="sheet-cid-list" aria-label="Buscar cidade"></div>
      <div class="sheet-body" id="sheet-cid-list">
        <?php foreach ($porRegiao as $r => $l): ?>
        <div class="sheet-group"><h3><?= h($r) ?></h3><div class="sheet-links">
          <?php foreach ($l as $c): ?><a href="<?= cidade_url($c['slug']) ?>" data-k="<?= h(str_replace('-', ' ', slugify($c['nome'] . ' ' . $c['uf'] . ' ' . $r))) ?>"><?= ic('map-pin') . h($c['nome']) ?>/<?= $c['uf'] ?></a><?php endforeach; ?>
        </div></div>
        <?php endforeach; ?>
        <p class="sheet-none muted" hidden>Nenhuma cidade com esse nome. <a href="<?= h(whatsapp_url('Olá! Não encontrei minha cidade no site.')) ?>" target="_blank" rel="noopener">Fale com a equipe</a>.</p>
      </div>
    </div>
  </div>

  <div class="sheet" id="sheet-segmentos" aria-hidden="true">
    <div class="sheet-box" role="dialog" aria-modal="true" aria-labelledby="sheet-seg-title">
      <div class="sheet-head"><h2 id="sheet-seg-title">Todos os segmentos</h2><button type="button" class="icon-round" data-sheet-close aria-label="Fechar"><?= ic('x') ?></button></div>
      <div class="input-icon"><?= ic('search') ?><input class="input" type="search" placeholder="Digite o segmento..." data-sheet-filter="sheet-seg-list" aria-label="Buscar segmento"></div>
      <div class="sheet-body" id="sheet-seg-list">
        <?php foreach (seg_grupos() as $g => $l): ?>
        <div class="sheet-group"><h3><?= h($g) ?></h3><div class="sheet-links">
          <?php foreach ($l as $s): ?><a href="/comprar-empresa?segmento=<?= $s['slug'] ?>" data-k="<?= h(str_replace('-', ' ', slugify(seg_label($s) . ' ' . $g))) ?>"><?= ic($s['icon']) ?><span><?= h($s['nome']) ?><?php if (!empty($s['desc'])): ?><small><?= h($s['desc']) ?></small><?php endif; ?></span></a><?php endforeach; ?>
        </div></div>
        <?php endforeach; ?>
        <p class="sheet-none muted" hidden>Nenhum segmento com esse nome. <a href="<?= h(whatsapp_url('Olá! Não encontrei o segmento da minha empresa no site.')) ?>" target="_blank" rel="noopener">Fale com a equipe</a>.</p>
      </div>
    </div>
  </div>

<?php site_footer(); page_scripts(); ?>
  <script>
    document.getElementById('search-form').addEventListener('submit', function (e) {
      e.preventDefault();
      var f = new FormData(e.target), p = new URLSearchParams(), cidade = f.get('cidade');
      var cod = String(f.get('codigo') || '').trim();
      if (cod) { location.href = '/comprar-empresa?q=' + encodeURIComponent(cod); return; }
      if (f.get('segmento')) p.set('segmento', f.get('segmento'));
      var v = f.get('valor');
      if (v) { var r = v.split('-'); if (r[0] && r[0] !== '0') p.set('min', r[0]); if (r[1]) p.set('max', r[1]); }
      location.href = '/comprar-empresa' + (cidade ? '/' + cidade : '') + (p.toString() ? '?' + p : '');
    });
  </script>
</body>
</html>
