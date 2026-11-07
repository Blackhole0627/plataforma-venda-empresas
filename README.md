# Plataforma de Compra e Venda de Empresas

Classifieds platform for buying and selling businesses with confidentiality.
Sellers submit their company, the team reviews it in an internal area and publishes an anonymous listing (no company name), and buyers browse by city, segment and price.

This repo holds the front-end (HTML, CSS, JavaScript). The full flow already works in the browser with `localStorage`, so the layout and UX can be reviewed before the PHP + MySQL back-end is plugged in.

## Pages

| Public | Internal area (`/admin`) |
|---|---|
| `index.html` home (search, segments, featured, capitals) | `login.html` team login |
| `comprar-empresa.html` listings, filters, price slider, sort, pagination, city pages | `index.html` submissions inbox with status |
| `anuncio.html` listing detail, gallery, WhatsApp CTA, JSON-LD | `cadastro.html` submission detail, copy data, status |
| `vender-empresa.html` how selling works, FAQ | `anuncios.html` listings (publish, pause, featured, delete) |
| `cadastro.html` 4-step seller form, masks, validation, up to 15 photos | `anuncio-editar.html` create listing from a submission, live card + Google preview |
| `contato.html` contact | `cidades.html` 27 capitals + team adds new cities |

Test login: any e-mail + password (prefilled).

## Run locally

Paths are root-relative, so use a local server (not double-click):

```bash
python -m http.server 8080
# open http://localhost:8080
```

On localhost the links use query strings (`anuncio.html?id=1`). On the live server they switch to friendly URLs:

- `/empresa-a-venda/academia-em-curitiba-pr-pp1101`
- `/comprar-empresa/curitiba-pr`

## Deploy (Netlify)

Static site, no build step. Publish directory is the repo root. `_redirects` handles the friendly URLs.

## Structure

```
assets/
  css/style.css     public site styles (brand colors in :root)
  css/admin.css     internal area styles
  js/data.js        capitals, segments, sample data, storage layer
  js/app.js         config (brand, WhatsApp), header/footer, cards, helpers
  js/admin.js       login guard, sidebar, status helpers
admin/              internal area pages
_redirects          Netlify friendly URLs
```

## Swap the brand

`Empresas à Venda` is a placeholder. Change it in `assets/js/app.js` (`CONFIG`) and the page titles, and the colors in `assets/css/style.css` (`:root`).

## Next (back-end)

- PHP + MySQL: tables `cidades`, `segmentos`, `cadastros`, `cadastro_fotos`, `anuncios`, `anuncio_fotos`, `usuarios`
- Replace `PP.Store` calls with PHP endpoints / server-rendered pages
- Real login (sessions + `password_hash`), image upload to disk with resize
- `.htaccess` rewrites for the same friendly URLs, automatic `sitemap.xml`
- Remove the `noindex` meta tags when going live
