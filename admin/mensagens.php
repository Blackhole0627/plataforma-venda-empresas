<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
admin_head('Mensagens do site', 'mensagens');
$msgs = db()->query('SELECT * FROM contatos ORDER BY criado_em DESC LIMIT 300')->fetchAll();
?>
  <div class="adm">
    <aside id="adm-side"></aside>
    <div class="adm-main">
      <header id="adm-top"></header>
      <main class="adm-content">
        <div class="adm-card">
          <div class="adm-toolbar"><span class="muted" style="font-size:.9rem">Mensagens enviadas pela página de contato (últimas 300).</span></div>
          <table class="table">
            <thead><tr><th>Recebida</th><th>Nome / contato</th><th>Assunto</th><th>Mensagem</th><th></th></tr></thead>
            <tbody>
            <?php if (!$msgs): ?>
              <tr><td colspan="5"><div class="adm-empty"><?= ic('inbox') ?><p>Nenhuma mensagem ainda.</p></div></td></tr>
            <?php endif; foreach ($msgs as $m): $tel = preg_replace('/\D/', '', $m['whatsapp']); ?>
              <tr>
                <td data-l="Recebida" style="white-space:nowrap"><?= h(date('d/m/Y H:i', strtotime($m['criado_em']))) ?></td>
                <td><span class="t-main"><?= h($m['nome']) ?></span><span class="t-sub"><?= h($m['whatsapp']) ?> · <?= h($m['email']) ?></span></td>
                <td data-l="Assunto"><?= h($m['assunto']) ?><?= $m['anuncio'] ? '<br><span class="t-sub">Anúncio ' . h($m['anuncio']) . '</span>' : '' ?></td>
                <td data-l="Mensagem" style="max-width:420px;white-space:pre-line"><?= h($m['mensagem']) ?></td>
                <td class="actions-cell">
                  <a class="icon-btn" href="https://wa.me/55<?= h($tel) ?>?text=<?= rawurlencode('Olá ' . explode(' ', $m['nome'])[0] . '! Recebemos sua mensagem pelo site.') ?>" target="_blank" rel="noopener" title="WhatsApp" aria-label="WhatsApp"><?= ic('message-circle') ?></a>
                  <a class="icon-btn" href="mailto:<?= h($m['email']) ?>" title="E-mail" aria-label="E-mail"><?= ic('mail') ?></a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </main>
    </div>
  </div>
  <?php admin_scripts(); ?>
</body>
</html>
