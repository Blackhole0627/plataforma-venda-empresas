<?php
require __DIR__ . '/inc/bootstrap.php';

$taxa = money((float) ajuste('taxa_publicacao'));
$com = pct(ajuste('comissao'));
$faq = [
    ['Quanto custa para anunciar?', 'São dois valores, e a parte maior só é paga se a sua empresa for vendida. 1) Taxa de publicação de ' . $taxa . ', paga uma única vez, só depois de você aprovar o anúncio. Inclui a revisão e a montagem do anúncio e a sugestão de faixa de valor, e o anúncio fica no ar até a venda. 2) Comissão de ' . $com . ' sobre o valor da venda, paga somente no fechamento.'],
    ['O nome da minha empresa vai aparecer no site?', 'Não. O anúncio mostra o segmento, a cidade e faixas de números, e você escolhe o nível de detalhe. O nome e o endereço só são passados a compradores aprovados pela equipe depois de assinarem um termo de confidencialidade.'],
    ['Como vocês escolhem os compradores?', 'A equipe conversa com cada interessado antes de qualquer contato com você, para entender o perfil e o interesse real na compra.'],
    ['E se o interessado for meu concorrente?', 'Seus dados só são liberados depois que o comprador é aprovado pela equipe e assina o termo de confidencialidade. Você também pode indicar empresas ou pessoas que não devem receber as suas informações.'],
    ['A comissão vale se eu fechar direto com o comprador?', 'Sim. A comissão é devida quando a venda é fechada com qualquer comprador apresentado pela ' . cfg('marca') . ', mesmo que as partes concluam a negociação diretamente, dentro do prazo previsto no contrato.'],
    ['Vocês me ajudam a definir o preço?', 'Sim. A equipe analisa o faturamento e a estrutura do negócio e sugere uma faixa de valor. O preço final é sempre decisão sua. Preço acima do mercado costuma alongar o prazo, e a equipe te explica isso na conversa.'],
    ['Posso pausar ou retirar o anúncio? Existe fidelidade?', 'Você pode pausar ou retirar o anúncio quando quiser, sem nenhuma multa.'],
];
page_head([
    'title' => 'Vender empresa com sigilo · ' . cfg('marca'),
    'description' => 'Venda sua empresa sem expor o seu negócio. Anúncio sem o nome da empresa, compradores qualificados pela equipe e acompanhamento até a assinatura.',
    'canonical' => '/vender-empresa',
    'image' => photo_url(IMAGENS['heroVender'], 1200),
    'page' => 'vender',
    'jsonld' => [['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($q) => ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], $faq)]],
]);
site_header('vender');
$waDuvidas = whatsapp_url('Olá! Quero vender minha empresa e tenho algumas dúvidas.');
?>

  <main>
    <section class="hero">
      <div class="hero-bg" style="background-image:url('<?= photo_url(IMAGENS['heroVender'], 1800) ?>')"></div>
      <div class="container hero-inner">
        <span class="eyebrow"><?= ic('lock') ?> Venda com sigilo</span>
        <h1>Venda sua empresa <em>sem expor o seu negócio</em></h1>
        <p class="lead">Seu anúncio vai ao ar sem o nome da empresa. Nossa equipe qualifica os interessados e só libera os seus dados a compradores aprovados.</p>
        <div class="hero-actions">
          <a class="btn btn-accent btn-lg" href="/cadastro">Cadastrar minha empresa <?= ic('arrow-right') ?></a>
          <a class="btn btn-wa btn-lg" href="<?= h($waDuvidas) ?>" target="_blank" rel="noopener"><?= ic('message-circle') ?> Falar com a equipe no WhatsApp</a>
        </div>
        <p class="hero-note">Cadastro grátis · Leva uns 5 minutos · Resposta em até 1 dia útil</p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head center">
          <span class="kicker">Passo a passo</span>
          <h2>Como funciona a venda</h2>
          <p>Do cadastro à assinatura, nossa equipe acompanha cada etapa, com sigilo do começo ao fim.</p>
        </div>
        <div class="timeline">
          <div class="tl-step"><span class="tl-icon"><?= ic('clipboard-list') ?></span><h3>Cadastro <small>leva apenas 5 minutos</small></h3><p>Você preenche os dados do negócio, como segmento, cidade, faturamento e equipe, e envia fotos se quiser. O nome da empresa não aparece no anúncio.</p></div>
          <div class="tl-step"><span class="tl-icon"><?= ic('search-check') ?></span><h3>Análise <small>retorno em até 1 dia útil</small></h3><p>A equipe confere as informações, conversa com você e sugere uma faixa de valor com base nos números. Antes de publicar, você recebe as condições do anúncio.</p></div>
          <div class="tl-step"><span class="tl-icon"><?= ic('megaphone') ?></span><h3>Anúncio</h3><p>Publicamos sem o nome da empresa, com código próprio. Você aprova o texto e as fotos antes de ir ao ar.</p></div>
          <div class="tl-step"><span class="tl-icon"><?= ic('user-check') ?></span><h3>Interessados</h3><p>Qualificamos cada interessado. Seus dados só são liberados após termo de confidencialidade, e as conversas e visitas são marcadas apenas com quem foi aprovado pela equipe.</p></div>
          <div class="tl-step"><span class="tl-icon"><?= ic('handshake') ?></span><h3>Fechamento</h3><p>Acompanhamos a negociação, com proposta, análise de documentos e contrato, até a assinatura.</p></div>
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container split">
        <div>
          <span class="kicker">Sigilo</span>
          <h2>Você decide quem sabe que sua empresa está à venda</h2>
          <p class="muted">Vender uma empresa mexe com muita gente: funcionários, clientes, fornecedores e concorrentes. Por isso o anúncio não mostra o nome do negócio, e você escolhe o nível de detalhe que aparece.</p>
          <ul class="value-list">
            <li><?= ic('lock') ?><span>Nome e endereço ficam apenas com a nossa equipe. Só são liberados a compradores aprovados, após termo de confidencialidade.</span></li>
            <li><?= ic('image') ?><span>Fotos reais ou ilustrativas, sem fachada, logo ou nome.</span></li>
            <li><?= ic('calendar-check') ?><span>Visitas só com compradores aprovados, combinadas com discrição.</span></li>
            <li><?= ic('circle-check') ?><span>Você aprova o anúncio antes de ir ao ar e pode pausá-lo quando quiser.</span></li>
          </ul>
          <div class="note" style="margin-bottom:24px"><?= ic('info') ?><span>Quanto mais genérico o anúncio, maior a proteção. Você escolhe o nível de detalhe.</span></div>
          <a class="btn btn-primary btn-lg" href="/cadastro">Começar cadastro</a>
        </div>
        <img class="rounded" src="<?= photo_url(IMAGENS['sigilo'], 900) ?>" alt="Reunião de negociação de venda de empresa" loading="lazy">
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head center">
          <span class="kicker">Fotos do anúncio</span>
          <h2>Você escolhe como seu negócio aparece</h2>
          <p>Foto ajuda o comprador a se interessar, mas também pode revelar o local. Por isso você decide o nível de exposição.</p>
        </div>
        <div class="photo-options">
          <div class="photo-opt"><img src="<?= photo_url(IMAGENS['fotoIlus'], 800) ?>" alt="Exemplo com fotos ilustrativas" loading="lazy"><div class="po-body"><h3>Fotos ilustrativas</h3><p class="muted" style="margin:0">Usamos imagens do segmento, marcadas como “imagem ilustrativa”. O seu espaço não aparece no anúncio. As fotos reais podem ser enviadas depois, só a compradores aprovados, após o termo de confidencialidade.</p></div></div>
          <div class="photo-opt"><img src="<?= photo_url(IMAGENS['fotoReal'], 800) ?>" alt="Exemplo com fotos reais" loading="lazy"><div class="po-body"><h3>Fotos reais, sem identificação</h3><p class="muted" style="margin:0">Mostram o espaço de verdade e passam mais confiança ao comprador. Nossa equipe revisa cada foto e retira o que possa identificar o negócio antes de publicar.</p></div></div>
        </div>
        <div class="note photo-tip"><?= ic('camera') ?><span><strong>Antes de fotografar.</strong> Evite fachada, letreiro, logo, uniformes, produtos com marca, pessoas e a vista da janela. Prefira luz natural e ambiente organizado, de 6 a 10 fotos (até 15). Você vê e aprova as fotos antes de o anúncio ir ao ar.</span></div>
      </div>
    </section>

    <section class="section section-alt" id="investimento">
      <div class="container split">
        <div>
          <span class="kicker">Investimento</span>
          <h2>Quanto custa vender com a gente</h2>
          <p class="muted">O cadastro é grátis. São dois valores, e a parte maior só é paga se a sua empresa for vendida.</p>
          <div class="price-cards">
            <div class="price-card"><small>Taxa de publicação</small><strong><?= $taxa ?></strong><span>Paga uma única vez, só depois de você aprovar o anúncio. Inclui a revisão e a montagem do anúncio e a sugestão de faixa de valor.</span></div>
            <div class="price-card"><small>Comissão</small><strong><?= $com ?></strong><span>Sobre o valor da venda, paga somente no fechamento.</span></div>
          </div>
          <div class="hero-actions" style="margin-top:22px">
            <a class="btn btn-primary btn-lg" href="/cadastro">Cadastrar minha empresa</a>
            <a class="btn btn-outline btn-lg" href="<?= h($waDuvidas) ?>" target="_blank" rel="noopener"><?= ic('message-circle') ?>Tirar dúvidas</a>
          </div>
        </div>
        <img class="rounded" src="/assets/img/site/investimento.jpg" alt="Proposta de serviço sendo conferida com calculadora ao lado" loading="lazy">
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head center">
          <span class="kicker">Dúvidas</span>
          <h2>Perguntas frequentes</h2>
        </div>
        <div class="faq">
          <?php foreach ($faq as $i => [$q, $r]): ?>
          <details<?= $i === 0 ? ' open' : '' ?>><summary><?= h($q) ?></summary><?php if ($i === 0): ?><p>São dois valores, e a parte maior só é paga se a sua empresa for vendida.</p><ol class="faq-list"><li><strong>Taxa de publicação de <?= $taxa ?></strong>, paga uma única vez, só depois de você aprovar o anúncio. Inclui a revisão e a montagem do anúncio e a sugestão de faixa de valor, e o anúncio fica no ar até a venda.</li><li><strong>Comissão de <?= $com ?></strong> sobre o valor da venda, paga somente no fechamento.</li></ol><?php else: ?><p><?= h($r) ?></p><?php endif; ?></details>
          <?php endforeach; ?>
        </div>
        <div class="faq-cta">
          <a class="btn btn-primary btn-lg" href="/cadastro">Cadastrar minha empresa</a>
          <a class="btn btn-wa btn-lg" href="<?= h($waDuvidas) ?>" target="_blank" rel="noopener"><?= ic('message-circle') ?> WhatsApp</a>
        </div>
      </div>
    </section>
  </main>

  <?php site_footer(); page_scripts(); ?>
</body>
</html>
