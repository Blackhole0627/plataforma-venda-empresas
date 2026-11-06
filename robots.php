<?php
require __DIR__ . '/inc/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
if (!cfg('indexar')) {
    echo "User-agent: *\nDisallow: /\n";
    exit;
}
echo "User-agent: *\nDisallow: /admin/\nDisallow: /api/\nDisallow: /install.php\n\nSitemap: " . site_url('/sitemap.xml') . "\n";
