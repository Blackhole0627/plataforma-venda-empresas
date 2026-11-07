# Plataforma de Compra e Venda de Empresas

Classifieds platform for buying and selling businesses with confidentiality.
Sellers submit their company, the team reviews it in a private area and publishes an anonymous listing (no company name), and buyers browse by city, segment and price.

**Stack:** PHP 8 + MySQL/MariaDB, plain HTML/CSS/JS, no framework and no build step. Runs on any common shared hosting (cPanel, Hostinger, HostGator, Locaweb) with Apache.

## What's inside

| Public | Team area (`/admin`) |
|---|---|
| `/` home: search, segments, featured, capitals, team | `/admin/login` login (sessions + `password_hash`) |
| `/comprar-empresa` listings, filters, price slider, sort, pagination | `/admin/` submissions inbox with status |
| `/comprar-empresa/<cidade-uf>` one page per city | `/admin/cadastro?id=` submission detail, photos, copy data |
| `/empresa-a-venda/<segmento>-em-<cidade-uf>-<codigo>` listing page | `/admin/anuncio-editar` create listing from a submission, live preview |
| `/vender-empresa` how selling works, FAQ | `/admin/anuncios` publish, pause, featured, delete |
| `/cadastro` 4-step seller form, up to 15 photos | `/admin/cidades` 27 capitals + team adds cities |
| `/contato` contact form | `/admin/mensagens` contact form messages |
| `/sitemap.xml`, `/robots.txt` generated automatically | `/admin/conta` team logins and password |

SEO: friendly URLs, unique title/description per page, canonical, Open Graph, JSON-LD (Organization, Product/Offer, BreadcrumbList, ItemList, FAQPage), listing and home pages rendered on the server, automatic sitemap.

Security: PDO prepared statements, CSRF on every team action, session cookie `HttpOnly` + `SameSite`, login throttling, uploads validated and re-encoded (GD), no script execution in `/uploads`, `/inc` and `config.php` blocked, honeypot + per-IP limit on public forms.

## Run locally

Needs PHP 8.0+ (with `pdo_mysql`, ideally `gd`) and MySQL/MariaDB. XAMPP works.

```bash
# 1. database
mysql -uroot -e "CREATE DATABASE passaponto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 2. config
cp config.sample.php config.php      # fill in db user/password

# 3. server
php -S localhost:8000 router.php     # router.php mimics the .htaccess rules

# 4. open http://localhost:8000/install.php
#    creates the tables, the 27 capitals and your login
#    (tick "anúncios de exemplo" to load 20 sample listings)
```

## Deploy (shared hosting with cPanel)

For launch day, follow [docs/LAUNCH.md](docs/LAUNCH.md). It adds the client checklist, the release zip and `tools/smoke_test.py`. The team guide is [docs/guia-da-equipe.md](docs/guia-da-equipe.md).

1. Create a MySQL database and user in cPanel, give the user all privileges on that database.
2. Upload every file in this repo to `public_html` (FTP, File Manager or cPanel Git). Keep `.htaccess`.
3. Copy `config.sample.php` to `config.php` and fill in:
   - `db` with the cPanel database, user and password
   - `site_url` with the real domain, for example `https://www.dominio.com.br`
   - brand, WhatsApp, e-mail and `notificar_email` (team inbox for new submissions)
4. Open `https://www.dominio.com.br/install.php`, create the first login, then **delete `install.php`**.
5. When SSL is active, uncomment the two HTTPS lines at the top of `.htaccess`.
6. When the client approves, set `'indexar' => true` in `config.php` and send `/sitemap.xml` to Google Search Console.

`/uploads` must be writable by PHP (755 is usually enough on cPanel).

## Structure

```
index.php, comprar-empresa.php, anuncio.php, vender-empresa.php, cadastro.php, contato.php, 404.php
sitemap.php, robots.php      served as /sitemap.xml and /robots.txt
api/cadastro.php             seller form submit (multipart, photos)
api/contato.php              contact form submit
admin/                       team area pages + admin/api.php (JSON, CSRF)
inc/bootstrap.php            config, PDO, session, helpers
inc/repo.php                 database functions, photo upload
inc/view.php                 <head>, header, footer, listing card, JS boot data
inc/data.php                 segments, states, capitals, site images
inc/schema.sql               tables (install.php runs it)
assets/js/data.js            front-end store (reads server data, saves through the API)
assets/js/app.js             formatting, masks, card, toast
assets/js/admin.js           team area sidebar/topbar
assets/css/                  style.css (public, brand colors in :root), admin.css
uploads/                     photos (not in git)
install.php                  one-time setup
router.php                   local dev server only
```

## Swap the brand

Name, WhatsApp, e-mail, hours and the team cards are in `config.php`. The logo files are `assets/img/logo.png` (light backgrounds), `logo-light.png` (dark backgrounds) and `favicon.png`. Colors are in `assets/css/style.css` (`:root`). Client photos are in `assets/img/site/`. The hero, banners and other page images are set in `inc/data.php` (`IMAGENS`).

## Front-end prototype

The first clickable version (front-end only, browser storage) is kept in the git tag `v0.1-frontend`.
