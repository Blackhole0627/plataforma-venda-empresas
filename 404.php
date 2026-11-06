<?php
if (!defined('ROOT')) require __DIR__ . '/inc/bootstrap.php';
http_response_code(404);
page_head(['title' => 'Página não encontrada · ' . cfg('marca'), 'robots' => 'noindex, follow']);
site_header('');
?>
  <main class="container" style="padding:80px 20px">
    <div class="empty">
      <div class="empty-icon"><i data-lucide="map"></i></div>
      <h1 style="font-size:2rem">Página não encontrada</h1>
      <p class="muted">O link pode estar errado ou o anúncio saiu do ar.</p>
      <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
        <a class="btn btn-primary" href="/comprar-empresa">Ver empresas à venda</a>
        <a class="btn btn-outline" href="/">Ir pro início</a>
      </div>
    </div>
  </main>
  <?php site_footer(); page_scripts(); ?>
</body>
</html>
