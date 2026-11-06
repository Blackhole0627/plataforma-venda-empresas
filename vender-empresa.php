<?php
require __DIR__ . '/inc/bootstrap.php';
const FAQ = [
    ['Quanto custa anunciar?', 'O cadastro é grátis. Depois da análise, a equipe te passa as condições do anúncio antes de publicar.'],
    ['O nome da minha empresa vai aparecer no site?', 'Não. O anúncio mostra o segmento, o bairro, a cidade e os números. O nome só é passado pra compradores aprovados pela equipe.'],
    ['Quanto tempo leva pra publicar?', 'Depois do cadastro, a equipe entra em contato em até 1 dia útil. Com tudo certo, o anúncio vai pro ar no mesmo dia.'],
    ['Quantas fotos posso mandar?', 'Até 15 fotos. Se preferir, a gente usa fotos ilustrativas do seu segmento.'],
    ['Vocês ajudam a definir o preço?', 'Sim. Se você não tiver certeza do valor, a equipe conversa com você com base no faturamento e na estrutura do negócio.'],
];
page_head([
    'title' => 'Vender empresa com sigilo · ' . cfg('marca'),
    'description' => 'Quer vender sua empresa? Anuncie com sigilo, sem expor o nome do negócio, e receba só contatos de compradores filtrados pela nossa equipe.',
    'canonical' => '/vender-empresa',
    'image' => photo_url(IMAGENS['heroVender'], 1200),
    'page' => 'vender',
    'jsonld' => [['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($q) => ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], FAQ)]],
]);
site_header('vender');
?>

  <main>
    <section class="hero">
      <div class="hero-bg" style="background-image:url('<?= photo_url(IMAGENS['heroVender'], 1800) ?>')"></div>
      <div class="container hero-inner">
        <span class="eyebrow"><i data-lucide="lock"></i> Venda com sigilo total</span>
        <h1>Venda sua empresa <em>sem expor o seu negócio</em></h1>
        <p class="lead">Seu anúncio vai pro ar sem o nome da empresa. A nossa equipe filtra os interessados e só passa pra você quem tem perfil e dinheiro pra comprar.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <a class="btn btn-accent btn-lg" href="/cadastro">Cadastrar minha empresa <i data-lucide="arrow-right"></i></a>
          <a class="btn btn-wa btn-lg" href="<?= h(whatsapp_url('Olá! Quero vender minha empresa e tenho algumas dúvidas.')) ?>" target="_blank" rel="noopener"><i data-lucide="message-circle"></i> Tirar dúvidas</a>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head center">
          <span class="kicker">Passo a passo</span>
          <h2>Como funciona a venda</h2>
          <p>Do cadastro até a assinatura, você tem alguém acompanhando cada etapa.</p>
        </div>
        <div class="timeline">
          <div class="tl-step"><span class="tl-icon"><i data-lucide="clipboard-list"></i></span><h3>Cadastro</h3><p>Você preenche os dados da empresa e manda as fotos pelo site.</p></div>
          <div class="tl-step"><span class="tl-icon"><i data-lucide="search-check"></i></span><h3>Análise</h3><p>A equipe confere as informações e tira as dúvidas com você.</p></div>
          <div class="tl-step"><span class="tl-icon"><i data-lucide="megaphone"></i></span><h3>Anúncio</h3><p>Publicamos o anúncio sem o nome da empresa, com código próprio.</p></div>
          <div class="tl-step"><span class="tl-icon"><i data-lucide="user-check"></i></span><h3>Interessados</h3><p>Filtramos os compradores e marcamos visita só com quem é sério.</p></div>
          <div class="tl-step"><span class="tl-icon"><i data-lucide="handshake"></i></span><h3>Fechamento</h3><p>Acompanhamos a negociação até a assinatura do contrato.</p></div>
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container split">
        <div>
          <span class="kicker">Sigilo</span>
          <h2>Seu cliente e seu funcionário não ficam sabendo</h2>
          <p class="muted">Vender uma empresa mexe com muita gente. Por isso o anúncio mostra só o segmento, o bairro, a cidade e os números do negócio.</p>
          <ul class="next-steps" style="margin:22px 0 28px">
            <li><span class="n"><i data-lucide="check" style="width:15px;height:15px"></i></span><span>Nome e endereço ficam guardados com a equipe</span></li>
            <li><span class="n"><i data-lucide="check" style="width:15px;height:15px"></i></span><span>Você escolhe fotos reais ou fotos ilustrativas</span></li>
            <li><span class="n"><i data-lucide="check" style="width:15px;height:15px"></i></span><span>Visitas só com compradores aprovados</span></li>
            <li><span class="n"><i data-lucide="check" style="width:15px;height:15px"></i></span><span>Atendimento direto pelo WhatsApp</span></li>
          </ul>
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
        </div>
        <div class="photo-options">
          <div class="photo-opt"><img src="<?= photo_url(IMAGENS['fotoReal'], 800) ?>" alt="Exemplo com fotos reais" loading="lazy"><div class="po-body"><h3>Fotos reais</h3><p class="muted" style="margin:0">Mostra o espaço de verdade e atrai mais interessados. Pode mandar até 15 fotos, de preferência bem iluminadas e sem fachada.</p></div></div>
          <div class="photo-opt"><img src="<?= photo_url(IMAGENS['fotoIlus'], 800) ?>" alt="Exemplo com fotos ilustrativas" loading="lazy"><div class="po-body"><h3>Fotos ilustrativas</h3><p class="muted" style="margin:0">Usamos imagens do segmento. Ninguém reconhece o local e o sigilo fica total.</p></div></div>
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <div class="section-head center">
          <span class="kicker">Dúvidas</span>
          <h2>Perguntas frequentes</h2>
        </div>
        <div class="faq">
          <details open><summary>Quanto custa anunciar?</summary><p>O cadastro é grátis. Depois da análise, a equipe te passa as condições do anúncio antes de publicar.</p></details>
          <details><summary>O nome da minha empresa vai aparecer no site?</summary><p>Não. O anúncio mostra o segmento, o bairro, a cidade e os números. O nome só é passado pra compradores aprovados pela equipe.</p></details>
          <details><summary>Quanto tempo leva pra publicar?</summary><p>Depois do cadastro, a equipe entra em contato em até 1 dia útil. Com tudo certo, o anúncio vai pro ar no mesmo dia.</p></details>
          <details><summary>Quantas fotos posso mandar?</summary><p>Até 15 fotos. Se preferir, a gente usa fotos ilustrativas do seu segmento.</p></details>
          <details><summary>Vocês ajudam a definir o preço?</summary><p>Sim. Se você não tiver certeza do valor, a equipe conversa com você com base no faturamento e na estrutura do negócio.</p></details>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="cta-band">
          <div class="bg" style="background-image:url('<?= photo_url(IMAGENS['ctaVender'], 1400) ?>')"></div>
          <div><h2>Pronto pra vender?</h2><p>O cadastro leva uns 5 minutos e é grátis.</p></div>
          <div class="actions"><a class="btn btn-white btn-lg" href="/cadastro">Cadastrar empresa</a></div>
        </div>
      </div>
    </section>
  </main>

  <?php site_footer(); page_scripts(); ?>
</body>
</html>
