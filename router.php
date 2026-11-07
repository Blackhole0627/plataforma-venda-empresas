<?php
/*
 * Local development only:  php -S localhost:8000 router.php
 * Mirrors the .htaccess rules (Apache is used in production).
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = __DIR__;

if (preg_match('#^/(inc|docs|tools|\.git)(/|$)#', $path) || preg_match('#(config(\.sample)?\.php|router\.php|\.sql|\.md)$#', $path)
    || (str_starts_with($path, '/uploads/') && preg_match('#\.(php\d?|phtml|phar|html?|svg|js)$#i', $path))) {
    http_response_code(403);
    exit('Forbidden');
}

$run = function (string $file, array $get = []) use ($root) {
    $_GET = $get + $_GET;
    $_SERVER['SCRIPT_NAME'] = '/' . $file;
    chdir(dirname("$root/$file"));
    require "$root/$file";
    exit;
};

if (preg_match('#^/empresa-a-venda/([a-z0-9-]+)/?$#', $path, $m)) $run('anuncio.php', ['slug' => $m[1]]);
if (preg_match('#^/comprar-empresa/([a-z0-9-]+)/?$#', $path, $m)) $run('comprar-empresa.php', ['cidade_path' => $m[1]]);
if ($path === '/sitemap.xml') $run('sitemap.php');
if ($path === '/robots.txt') $run('robots.php');

$file = $root . $path;
if (is_dir($file) && is_file(rtrim($file, '/') . '/index.php')) $run(ltrim(rtrim($path, '/') . '/index.php', '/'));
if (is_file($file)) {
    if (str_ends_with($file, '.php')) $run(ltrim($path, '/'));
    return false; // static file
}
if (is_file(rtrim($file, '/') . '.php')) $run(ltrim(rtrim($path, '/'), '/') . '.php');

$run('404.php');
