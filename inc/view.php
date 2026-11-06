<?php
/* Shared HTML: <head>, header, footer, listing card, JS boot data. */

function ic(string $name, string $cls = ''): string
{
    return '<i data-lucide="' . $name . '"' . ($cls ? ' class="' . $cls . '"' : '') . '></i>';
}

function asset(string $path): string
{
    $f = ROOT . $path;
    return $path . (is_file($f) ? '?v=' . filemtime($f) : '');
}

/*
 * $m: title, description, canonical (path), image, page (nav key), robots, admin (bool),
 *     jsonld (array of objects), body_class, title_raw (skip brand suffix)
 */
function page_head(array $m): void
{
    $marca = cfg('marca');
    $title = $m['title'] ?? $marca;
    $desc = $m['description'] ?? cfg('slogan');
    $admin = !empty($m['admin']);
    $robots = $m['robots'] ?? ((cfg('indexar') && !$admin) ? 'index, follow' : 'noindex, nofollow');
    $canonical = isset($m['canonical']) ? site_url($m['canonical']) : null;
    $image = $m['image'] ?? photo_url(IMAGENS['heroHome'], 1200);
    if (str_starts_with($image, '/')) $image = site_url($image);
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>
  <meta name="description" content="<?= h($desc) ?>">
  <meta name="robots" content="<?= h($robots) ?>">
<?php if ($canonical): ?>
  <link rel="canonical" href="<?= h($canonical) ?>">
  <meta property="og:url" content="<?= h($canonical) ?>">
<?php endif; ?>
<?php if (!$admin): ?>
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= h($marca) ?>">
  <meta property="og:locale" content="pt_BR">
  <meta property="og:title" content="<?= h($title) ?>">
  <meta property="og:description" content="<?= h($desc) ?>">
  <meta property="og:image" content="<?= h($image) ?>">
  <meta name="twitter:card" content="summary_large_image">
<?php endif; ?>
  <meta name="theme-color" content="#0d7a5a">
  <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('/assets/css/style.css') ?>">
<?php if ($admin): ?>
  <link rel="stylesheet" href="<?= asset('/assets/css/admin.css') ?>">
<?php endif; ?>
  <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js" defer></script>
<?php foreach ($m['jsonld'] ?? [] as $ld): ?>
  <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
</head>
<body<?= isset($m['page']) ? ' data-page="' . h($m['page']) . '"' : '' ?><?= isset($m['body_class']) ? ' class="' . h($m['body_class']) . '"' : '' ?><?= isset($m['admin_title']) ? ' data-title="' . h($m['admin_title']) . '"' : '' ?>>
<?php
}

function logo(bool $light = false): string
{
    return '<a class="logo' . ($light ? ' logo-light' : '') . '" href="/" aria-label="' . h(cfg('marca')) . ' início">' .
        '<span class="logo-mark">' . ic('handshake') . '</span>' .
        '<span class="logo-text">' . h(cfg('marca_a')) . '<b>' . h(cfg('marca_b')) . '</b></span></a>';
}

function site_header(string $page = ''): void
{
    $link = fn($href, $label, $key) => '<a href="' . $href . '"' . ($page === $key ? ' class="active" aria-current="page"' : '') . '>' . $label . '</a>';
    echo '<header id="site-header" class="site-header"><div class="container header-inner">' . logo() .
        '<nav class="main-nav" id="main-nav" aria-label="Principal">' .
        $link('/', 'Início', 'home') .
        $link('/comprar-empresa', 'Comprar empresa', 'comprar') .
        $link('/vender-empresa', 'Vender empresa', 'vender') .
        $link('/contato', 'Contato', 'contato') .
        '<a class="btn btn-primary nav-cta" href="/cadastro">' . ic('plus') . 'Anunciar minha empresa</a>' .
        '</nav>' .
        '<button class="nav-toggle" id="nav-toggle" aria-label="Abrir menu" aria-expanded="false">' . ic('menu') . '</button>' .
        '</div></header>';
}

function site_footer(): void
{
    $caps = array_slice(array_values(array_filter(cidades(true), fn($c) => $c['capital'])), 0, 12);
    $insta = (string) cfg('instagram', '');
    ?>
<footer id="site-footer" class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand"><?= logo(true) ?>
      <p>Plataforma para comprar e vender empresas com sigilo. Seu negócio anunciado sem expor o nome, e só compradores interessados de verdade chegam até você.</p>
      <div class="footer-social">
        <a href="<?= h(whatsapp_url('Olá! Vim pelo site.')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?= ic('message-circle') ?></a>
        <?php if ($insta): ?><a href="<?= h($insta) ?>" target="_blank" rel="noopener" aria-label="Instagram"><?= ic('instagram') ?></a><?php endif; ?>
        <a href="mailto:<?= h(cfg('email')) ?>" aria-label="E-mail"><?= ic('mail') ?></a>
      </div>
    </div>
    <div><h4>Navegação</h4><ul>
      <li><a href="/comprar-empresa">Comprar empresa</a></li>
      <li><a href="/vender-empresa">Vender empresa</a></li>
      <li><a href="/cadastro">Anunciar minha empresa</a></li>
      <li><a href="/contato">Contato</a></li>
      <li><a href="/admin/" rel="nofollow">Área da equipe</a></li>
    </ul></div>
    <div class="footer-cities"><h4>Empresas à venda por cidade</h4><ul>
      <?php foreach ($caps as $c): ?><li><a href="<?= cidade_url($c['slug']) ?>"><?= h($c['nome']) ?>/<?= h($c['uf']) ?></a></li><?php endforeach; ?>
    </ul></div>
    <div><h4>Atendimento</h4><ul class="footer-contact">
      <li><?= ic('message-circle') . h(cfg('whatsapp_label')) ?></li>
      <li><?= ic('mail') . h(cfg('email')) ?></li>
      <li><?= ic('clock') . h(cfg('horario')) ?></li>
    </ul></div>
  </div>
  <div class="container footer-bottom"><span>© <?= date('Y') ?> <?= h(cfg('marca')) ?>. Todos os direitos reservados.</span></div>
</footer>
<a class="wa-float" href="<?= h(whatsapp_url('Olá! Vim pelo site e quero mais informações.')) ?>" target="_blank" rel="noopener" aria-label="Falar no WhatsApp"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.79-1.47-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.27.49 1.7.63.72.23 1.37.2 1.88.12.58-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.8h-.01a9.8 9.8 0 0 1-5-1.37l-.36-.21-3.72.98 1-3.63-.24-.37a9.8 9.8 0 0 1-1.5-5.22c0-5.42 4.41-9.83 9.84-9.83a9.77 9.77 0 0 1 9.82 9.84c0 5.42-4.41 9.83-9.83 9.83zm8.37-18.2A11.76 11.76 0 0 0 12.05 0C5.5 0 .16 5.33.16 11.89c0 2.1.55 4.14 1.59 5.94L.06 24l6.3-1.65a11.87 11.87 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.9 0-3.17-1.24-6.16-3.48-8.4z"/></svg></a>
<?php
}

/* same markup as PP.card() in app.js */
function card(array $a): string
{
    $s = seg($a['segmento']);
    $where = cidade_label($a['cidade']);
    $url = anuncio_url($a);
    $foto = photo_url($a['fotos'][0] ?? ('u:' . $s['fotos'][0]), 640);
    return '<article class="card-anuncio">' .
        '<a class="card-media" href="' . $url . '" aria-label="' . h($s['nome'] . ' à venda em ' . $where) . '">' .
        '<img src="' . h($foto) . '" alt="' . h($s['nome'] . ' à venda em ' . $where) . '" loading="lazy">' .
        '<span class="badge-seg">' . ic($s['icon']) . h($s['nome']) . '</span>' .
        ($a['destaque'] ? '<span class="badge-destaque">' . ic('star') . 'Destaque</span>' : '') .
        '</a>' .
        '<div class="card-body">' .
        '<div class="card-top"><span class="card-code">Cód. ' . h($a['codigo']) . '</span><span class="card-date">' . time_ago($a['data']) . '</span></div>' .
        '<h3 class="card-title"><a href="' . $url . '">' . h($s['nome']) . ' à venda</a></h3>' .
        '<p class="card-loc">' . ic('map-pin') . h(($a['bairro'] ? $a['bairro'] . ', ' : '') . $where) . '</p>' .
        '<div class="card-stats">' .
        '<span title="Faturamento médio mensal">' . ic('trending-up') . money_short($a['faturamento']) . '/mês</span>' .
        '<span title="Funcionários">' . ic('users') . (int) $a['funcionarios'] . ' func.</span>' .
        '<span title="Área">' . ic('ruler') . num($a['area']) . ' m²</span>' .
        '</div>' .
        '<div class="card-foot"><span class="card-price">' . money($a['preco']) . '</span>' .
        '<a class="card-link" href="' . $url . '">Ver detalhes ' . ic('arrow-right') . '</a></div>' .
        '</div></article>';
}

function equipe(): array
{
    return cfg('equipe', []);
}

function consultor_para(string $uf): ?array
{
    $eq = equipe();
    foreach ($eq as $p) {
        if (in_array($uf, $p['ufs'] ?? [], true)) return $p;
    }
    return $eq[0] ?? null;
}

function config_js(): array
{
    return [
        'marca' => cfg('marca'), 'marcaA' => cfg('marca_a'), 'marcaB' => cfg('marca_b'), 'slogan' => cfg('slogan'),
        'whatsapp' => cfg('whatsapp'), 'whatsappLabel' => cfg('whatsapp_label'), 'email' => cfg('email'),
        'instagram' => cfg('instagram'), 'horario' => cfg('horario'),
    ];
}

/* prints window.PP_BOOT + core scripts. $data is merged into the boot object. */
function page_scripts(array $data = [], bool $admin = false): void
{
    $boot = array_merge([
        'config' => config_js(),
        'segmentos' => SEGMENTOS,
        'ufs' => UFS,
        'imagens' => IMAGENS,
        'equipe' => array_map(fn($p) => ['nome' => $p['nome'], 'cargo' => $p['cargo'], 'foto' => $p['foto']], equipe()),
        'cidades' => cidades(!$admin),
    ], $data);
    echo '<script>window.PP_BOOT = ' . json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . ';</script>' . "\n";
    echo '<script src="' . asset('/assets/js/data.js') . '"></script>' . "\n";
    echo '<script src="' . asset('/assets/js/app.js') . '"></script>' . "\n";
    if ($admin) echo '<script src="' . asset('/assets/js/admin.js') . '"></script>' . "\n";
}

function not_found(): void
{
    http_response_code(404);
    require ROOT . '/404.php';
    exit;
}

/* ---------- team area ---------- */
function admin_head(string $title, string $page): array
{
    $u = require_login();
    page_head(['title' => $title . ' · Área da equipe', 'admin' => true, 'robots' => 'noindex, nofollow',
        'page' => $page, 'body_class' => 'admin', 'admin_title' => $title]);
    return $u;
}

function admin_scripts(array $data = []): void
{
    $u = current_user();
    page_scripts(array_merge([
        'user' => ['nome' => $u['nome'], 'email' => $u['email']],
        'csrf' => csrf_token(),
        'novos' => cadastros_novos(),
    ], $data), true);
}
