<?php
/* /sitemap.xml, rebuilt on every request from the database */
require __DIR__ . '/inc/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');

$urls = [['/', null, '1.0'], ['/comprar-empresa', null, '0.9'], ['/vender-empresa', null, '0.7'], ['/cadastro', null, '0.6'], ['/contato', null, '0.4']];
foreach (cidades(true) as $c) $urls[] = [cidade_url($c['slug']), null, '0.8'];
foreach (anuncios_publicados() as $a) $urls[] = [anuncio_url($a), $a['atualizado'] ?: $a['data'], '0.8'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$u, $mod, $pri]) {
    echo '  <url><loc>' . h(site_url($u)) . '</loc>' . ($mod ? '<lastmod>' . date('Y-m-d', intdiv($mod, 1000)) . '</lastmod>' : '') . '<priority>' . $pri . "</priority></url>\n";
}
echo '</urlset>';
