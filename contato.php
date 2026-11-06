<?php
require __DIR__ . '/inc/bootstrap.php';
page_head([
    'title' => 'Contato · ' . cfg('marca'),
    'description' => 'Fale com a equipe pelo WhatsApp, e-mail ou formulário. Atendimento ' . cfg('horario') . '.',
    'canonical' => '/contato',
    'page' => 'contato',
    'robots' => isset($_GET['anuncio']) ? 'noindex, follow' : null,
]);
site_header('contato');
?>

  <main>
    <section class="page-head">
      <div class="container">
        <ol class="breadcrumb"><li><a href="/">Início</a></li><li>Contato</li></ol>
        <h1>Fale com a gente</h1>
        <p>Quer comprar, vender ou tirar uma dúvida? Chama no WhatsApp ou manda uma mensagem.</p>
      </div>
    </section>

    <div class="container form-layout" style="grid-template-columns:1fr 380px">
      <div class="form-card">
        <form id="form" novalidate>
          <h2 style="font-size:1.4rem">Envie uma mensagem</h2>
          <p class="muted" style="margin-bottom:22px">Respondemos em até 1 dia útil.</p>
          <div class="grid-2">
            <div class="field"><label for="nome">Nome</label><input class="input" id="nome" autocomplete="name" placeholder="Seu nome"><span class="error">Digite seu nome.</span></div>
            <div class="field"><label for="tel">WhatsApp</label><input class="input" id="tel" data-mask="phone" inputmode="tel" placeholder="(11) 99999-9999"><span class="error">Digite um WhatsApp com DDD.</span></div>
          </div>
          <div class="grid-2">
            <div class="field"><label for="email">E-mail</label><input class="input" id="email" type="email" autocomplete="email" placeholder="voce@email.com"><span class="error">Digite um e-mail válido.</span></div>
            <div class="field"><label for="assunto">Assunto</label>
              <select class="select" id="assunto">
                <option>Quero comprar uma empresa</option>
                <option>Quero vender minha empresa</option>
                <option>Dúvida sobre um anúncio</option>
                <option>Outro assunto</option>
              </select></div>
          </div>
          <div class="field"><label for="msg">Mensagem</label><textarea class="textarea" id="msg" maxlength="3000" placeholder="Escreva aqui"></textarea>
            <input type="text" id="site" name="site" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true"><span class="error">Escreva sua mensagem.</span></div>
          <button class="btn btn-primary btn-lg" type="submit"><i data-lucide="send"></i> Enviar mensagem</button>
        </form>
        <div class="success hidden" id="success">
          <div class="ok-icon"><i data-lucide="check"></i></div>
          <h2>Mensagem enviada!</h2>
          <p class="muted">Obrigado pelo contato. Logo a gente te responde.</p>
          <a class="btn btn-primary" href="/comprar-empresa">Ver empresas à venda</a>
        </div>
      </div>

      <aside class="contact-cards">
        <a class="contact-card" href="<?= h(whatsapp_url('Olá! Vim pela página de contato.')) ?>" target="_blank" rel="noopener"><span class="cc-icon wa"><i data-lucide="message-circle"></i></span><div><small>WhatsApp</small><strong><?= h(cfg('whatsapp_label')) ?></strong></div></a>
        <a class="contact-card" href="mailto:<?= h(cfg('email')) ?>"><span class="cc-icon"><i data-lucide="mail"></i></span><div><small>E-mail</small><strong><?= h(cfg('email')) ?></strong></div></a>
        <div class="contact-card"><span class="cc-icon"><i data-lucide="clock"></i></span><div><small>Atendimento</small><strong><?= h(cfg('horario')) ?></strong></div></div>
        <div class="contact-card" style="flex-direction:column;align-items:flex-start;gap:12px">
          <small class="muted" style="font-weight:600">Nossos consultores</small>
          <div class="avatar-stack"><?php foreach (equipe() as $p): ?><img src="<?= h(photo_url($p['foto'], 100)) ?>" alt="<?= h($p['nome']) ?>" title="<?= h($p['nome']) ?>"><?php endforeach; ?></div>
          <span class="muted" style="font-size:.9rem">Um consultor da sua região vai te atender.</span>
        </div>
      </aside>
    </div>
  </main>

  <?php site_footer(); page_scripts(); ?>
  <script>
    (function () {
      const $ = id => document.getElementById(id);

      const cod = PP.param('anuncio');
      if (cod) { $('assunto').value = 'Dúvida sobre um anúncio'; $('msg').value = 'Olá! Tenho interesse no anúncio ' + cod + '. '; }

      PP.bindMasks();
      const v = (id, ok) => { $(id).closest('.field').classList.toggle('invalid', !ok); return ok; };
      $('form').addEventListener('submit', async e => {
        e.preventDefault();
        const ok = [
          v('nome', $('nome').value.trim().length >= 2),
          v('tel', $('tel').value.replace(/\D/g, '').length >= 10),
          v('email', /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test($('email').value.trim())),
          v('msg', $('msg').value.trim().length >= 5)
        ].every(Boolean);
        if (!ok) return PP.toast('Confira os campos marcados', 'error');
        const btn = $('form').querySelector('button[type=submit]');
        btn.disabled = true;
        try {
          await PP.api('/api/contato', { nome: $('nome').value.trim(), whatsapp: $('tel').value.trim(), email: $('email').value.trim(),
            assunto: $('assunto').value, mensagem: $('msg').value.trim(), anuncio: cod || '', site: $('site').value });
          $('form').classList.add('hidden'); $('success').classList.remove('hidden'); PP.icons();
        } catch (err) { PP.toast(err.message, 'error'); btn.disabled = false; }
      });
    })();
  </script>
</body>
</html>
