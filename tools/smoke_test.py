#!/usr/bin/env python3
"""Launch check. Run from any computer, no setup needed:

    python tools/smoke_test.py https://www.dominio.com.br

Checks pages, friendly URLs, blocked files and SEO files. Exit code 1 if anything fails.
"""
import re
import sys
import urllib.error
import urllib.parse
import urllib.request


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


OPENER = urllib.request.build_opener(NoRedirect)
BASE = (sys.argv[1] if len(sys.argv) > 1 else '').rstrip('/')
if not BASE.startswith('http'):
    sys.exit('Usage: python tools/smoke_test.py https://www.dominio.com.br')

PAGES = ['/', '/comprar-empresa', '/vender-empresa', '/cadastro', '/contato', '/admin/login',
         '/robots.txt', '/sitemap.xml', '/assets/img/logo.png', '/assets/img/site/banner-home.jpg', '/assets/css/style.css']
BLOCKED = ['/config.php', '/config.sample.php', '/inc/bootstrap.php', '/inc/schema.sql', '/.git/config', '/.htaccess',
           '/router.php', '/README.md', '/docs/LAUNCH.md', '/tools/smoke_test.py', '/uploads/']
BAD_TEXT = ['Fatal error', 'Parse error', 'Warning: ', 'Notice: ', 'Deprecated: ', 'Stack trace', 'PassaPonto', 'localhost']
failures = warnings = 0


def get(path, url=None):
    req = urllib.request.Request(url or BASE + path, headers={'User-Agent': 'launch-smoke-test'})
    try:
        with OPENER.open(req, timeout=30) as r:
            return r.status, r.read(), dict(r.headers)
    except urllib.error.HTTPError as e:
        return e.code, e.read(), dict(e.headers)


def report(ok, text, warn=False):
    global failures, warnings
    if ok:
        print('  ok    ', text)
    elif warn:
        warnings += 1
        print('  WARN  ', text)
    else:
        failures += 1
        print('  FAIL  ', text)


print('Pages and friendly URLs')
home = b''
for path in PAGES:
    code, body, _ = get(path)
    report(code == 200, f'{path} -> {code}')
    if path.endswith(('.png', '.jpg', '.css')):
        continue
    text = body.decode('utf-8', 'replace')
    for bad in BAD_TEXT:
        report(bad not in text, f'{path} contains "{bad}"') if bad in text else None
    if path == '/':
        home = text

print('Files that must stay private')
for path in BLOCKED:
    code, body, _ = get(path)
    report(code in (403, 404), f'{path} -> {code}')
    report(b'<?php' not in body, f'{path} leaked source code') if b'<?php' in body else None

print('Other behaviour')
code, body, _ = get('/pagina-que-nao-existe')
report(code == 404 and b'<html' in body.lower(), f'missing page -> {code}')
code, _, h = get('/admin/')
report(code in (301, 302) and 'login' in h.get('Location', h.get('location', '')), f'team area without login -> {code}')
code, _, _ = get('/admin/api')
report(code in (401, 403, 405), f'team API without login -> {code}')
code, _, _ = get('/install.php')
report(code in (403, 404), f'install.php deleted -> {code}')
if BASE.startswith('https://'):
    code, _, h = get('', 'http://' + BASE[len('https://'):] + '/')
    report(code in (301, 302, 308) and h.get('Location', h.get('location', '')).startswith('https://'), f'http redirects to https -> {code}')
else:
    report(False, 'site is not on https yet', warn=True)

print('SEO')
canon = re.search(r'rel="canonical" href="([^"]+)"', home)
report(bool(canon) and canon.group(1).rstrip('/') == BASE, f'home canonical -> {canon.group(1) if canon else None}')
report('application/ld+json' in home, 'home has structured data')
_, body, _ = get('/sitemap.xml')
locs = re.findall(r'<loc>([^<]+)</loc>', body.decode('utf-8', 'replace'))
report(bool(locs) and all(u.startswith(BASE) for u in locs), f'sitemap has {len(locs)} URLs on this domain')
for url in locs[:40]:
    path = urllib.parse.urlparse(url).path or '/'
    code, _, _ = get(path)
    report(code == 200, f'sitemap link {path} -> {code}') if code != 200 else None
indexed = 'noindex' not in home
_, robots, _ = get('/robots.txt')
report(indexed, 'site is open to Google (indexar is true)' if indexed else 'site is hidden from Google (indexar is false). Fine during review, turn on to finish', warn=True)
if indexed:
    report(b'Disallow: /\n' not in robots.replace(b'\r', b''), 'robots.txt allows the site')

print(f'\n{failures} failed, {warnings} warnings')
sys.exit(1 if failures else 0)
