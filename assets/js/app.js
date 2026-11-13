/* ==========================================================================
   Shared UI: config, formatting, header/footer, cards, toast
   ========================================================================== */

(function () {
  'use strict';
  const PP = window.PP;

  /* brand + contacts come from config.php through the page */
  const CONFIG = (PP.BOOT && PP.BOOT.config) || { marca: '', whatsapp: '', whatsappLabel: '', email: '' };
  const PRETTY = true;

  /* ---------- formatting ---------- */
  const brl = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 });
  function money(v) { return brl.format(Number(v) || 0); }
  function moneyShort(v) {
    v = Number(v) || 0;
    if (v >= 1e6) return 'R$ ' + (v / 1e6).toLocaleString('pt-BR', { maximumFractionDigits: 1 }) + ' mi';
    if (v >= 1e3) return 'R$ ' + Math.round(v / 1e3) + ' mil';
    return money(v);
  }
  function num(v) { return (Number(v) || 0).toLocaleString('pt-BR'); }
  function dateBR(ts) { return new Date(ts).toLocaleDateString('pt-BR'); }
  function timeAgo(ts) {
    const m = Math.round((Date.now() - ts) / 60000);
    if (m < 60) return 'há ' + Math.max(1, m) + ' min';
    const h = Math.round(m / 60);
    if (h < 24) return 'há ' + h + 'h';
    const d = Math.round(h / 24);
    return d === 1 ? 'ontem' : 'há ' + d + ' dias';
  }
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }
  function param(name) { return new URLSearchParams(location.search).get(name); }

  /* ---------- lookups ---------- */
  function seg(slug) { return PP.SEGMENTOS.find(s => s.slug === slug) || PP.SEGMENTOS[PP.SEGMENTOS.length - 1]; }
  function cidade(slug) { return PP.Store.cidade(slug) || { nome: slug, uf: '', slug }; }
  function cidadeLabel(slug) { const c = cidade(slug); return c.nome + (c.uf ? '/' + c.uf : ''); }

  /* ---------- URLs ---------- */
  function anuncioSlug(a) { return seg(a.segmento).slug + '-em-' + a.cidade + '-' + a.codigo.toLowerCase(); }
  function anuncioUrl(a) { return '/empresa-a-venda/' + anuncioSlug(a); }
  function cidadeUrl(slug) { return '/comprar-empresa/' + slug; }
  function segmentoUrl(slug) { return '/comprar-empresa?segmento=' + slug; }
  function whatsappUrl(text) { return 'https://wa.me/' + CONFIG.whatsapp + '?text=' + encodeURIComponent(text || ''); }

  /* ---------- icons ---------- */
  function icons() { if (window.lucide) window.lucide.createIcons(); }
  function ic(name, cls) { return '<i data-lucide="' + name + '"' + (cls ? ' class="' + cls + '"' : '') + '></i>'; }

  /* ---------- listing card ---------- */
  /* the team's own title, or "Restaurante à venda" (same as anuncio_titulo() in PHP) */
  function anuncioTitulo(a) { return (a.titulo || '').trim() || seg(a.segmento).nome + ' à venda'; }

  function card(a) {
    const s = seg(a.segmento);
    const foto = PP.photoUrl(a.fotos[0] || ('u:' + s.fotos[0]), 640);
    const titulo = anuncioTitulo(a);
    return '' +
      '<article class="card-anuncio">' +
        '<a class="card-media" href="' + anuncioUrl(a) + '" aria-label="' + esc(titulo + ' em ' + cidadeLabel(a.cidade)) + '">' +
          '<img src="' + foto + '" alt="' + esc(titulo + ' em ' + cidadeLabel(a.cidade)) + '" loading="lazy">' +
          '<span class="badge-seg">' + ic(s.icon) + esc(s.nome) + '</span>' +
          (a.destaque ? '<span class="badge-destaque">' + ic('star') + 'Destaque</span>' : '') +
        '</a>' +
        '<div class="card-body">' +
          '<div class="card-top"><button type="button" class="card-code" data-copy="' + esc(a.codigo) + '" title="Copiar código">Cód. ' + esc(a.codigo) + ic('copy') + '</button><span class="card-date">' + timeAgo(a.data) + '</span></div>' +
          '<h3 class="card-title"><a href="' + anuncioUrl(a) + '">' + esc(titulo) + '</a></h3>' +
          '<p class="card-loc">' + ic('map-pin') + esc(a.bairro ? a.bairro + ', ' : '') + esc(cidadeLabel(a.cidade)) + '</p>' +
          '<div class="card-stats">' +
            '<span class="cs-fat">' + ic('trending-up') + 'Faturamento (mês) <strong>' + (a.faturamento == null ? 'Sob consulta' : moneyShort(a.faturamento)) + '</strong></span>' +
            (a.funcionarios == null ? '' : '<span title="Funcionários">' + ic('users') + a.funcionarios + ' func.</span>') +
            (a.area ? '<span title="Área">' + ic('ruler') + num(a.area) + ' m²</span>' : '') +
          '</div>' +
          '<div class="card-foot"><span class="card-price">' + money(a.preco) + '</span>' +
          '<a class="card-link" href="' + anuncioUrl(a) + '">Ver detalhes ' + ic('arrow-right') + '</a></div>' +
        '</div>' +
      '</article>';
  }

  /* ---------- header (rendered by PHP, JS only adds behaviour) ---------- */
  function logo(light) {
    return '<a class="logo" href="/" aria-label="' + esc(CONFIG.marca) + ' início"><img src="/assets/img/' + (light ? 'logo-light' : 'logo') + '.png" alt="' + esc(CONFIG.marca) + '" width="662" height="160"></a>';
  }

  function header() {
    const el = document.getElementById('site-header');
    const t = document.getElementById('nav-toggle');
    if (!el || !t) return;
    t.addEventListener('click', () => {
      const open = document.body.classList.toggle('nav-open');
      t.setAttribute('aria-expanded', open);
    });
    const onScroll = () => el.classList.toggle('scrolled', window.scrollY > 8);
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  }

  /* ---------- toast ---------- */
  function toast(msg, type) {
    let box = document.getElementById('toast-box');
    if (!box) { box = document.createElement('div'); box.id = 'toast-box'; box.setAttribute('role', 'status'); document.body.appendChild(box); }
    const t = document.createElement('div');
    t.className = 'toast ' + (type || 'ok');
    t.innerHTML = ic(type === 'error' ? 'alert-circle' : 'check-circle') + '<span>' + esc(msg) + '</span>';
    box.appendChild(t); icons();
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3200);
  }

  /* ---------- SEO helpers ---------- */
  function setMeta(title, description) {
    document.title = title;
    let m = document.querySelector('meta[name="description"]');
    if (!m) { m = document.createElement('meta'); m.name = 'description'; document.head.appendChild(m); }
    m.content = description;
  }
  function jsonLd(obj) {
    const s = document.createElement('script');
    s.type = 'application/ld+json'; s.textContent = JSON.stringify(obj);
    document.head.appendChild(s);
  }

  /* ---------- form masks ---------- */
  const masks = {
    phone(v) {
      v = v.replace(/\D/g, '').slice(0, 11);
      if (v.length > 10) return v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
      if (v.length > 6) return v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
      if (v.length > 2) return v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
      return v.replace(/(\d{0,2})/, '($1');
    },
    cnpj(v) {
      v = v.replace(/\D/g, '').slice(0, 14);
      return v.replace(/^(\d{2})(\d)/, '$1.$2').replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1/$2').replace(/(\d{4})(\d)/, '$1-$2');
    },
    cep(v) {
      v = v.replace(/\D/g, '').slice(0, 8);
      return v.length > 5 ? v.slice(0, 5) + '-' + v.slice(5) : v;
    },
    money(v) {
      v = v.replace(/\D/g, '').replace(/^0+/, '').slice(0, 11);
      return v ? 'R$ ' + Number(v).toLocaleString('pt-BR') : '';
    }
  };
  function bindMasks(root) {
    (root || document).querySelectorAll('[data-mask]').forEach(inp => {
      inp.addEventListener('input', () => {
        const fn = masks[inp.dataset.mask];
        if (fn && inp.value) inp.value = fn(inp.value);
      });
    });
  }
  function unmoney(v) { return Number(String(v || '').replace(/\D/g, '')) || 0; }

  /* ---------- image resize (upload) ---------- */
  function resizeImage(file, max, quality) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => {
        const img = new Image();
        img.onload = () => {
          const scale = Math.min(1, max / Math.max(img.width, img.height));
          const c = document.createElement('canvas');
          c.width = Math.round(img.width * scale); c.height = Math.round(img.height * scale);
          c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
          resolve(c.toDataURL('image/jpeg', quality || 0.72));
        };
        img.onerror = reject; img.src = reader.result;
      };
      reader.onerror = reject; reader.readAsDataURL(file);
    });
  }

  function dataUrlToBlob(u) {
    const [head, b64] = u.split(',');
    const bin = atob(b64), arr = new Uint8Array(bin.length);
    for (let i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
    return new Blob([arr], { type: (head.match(/data:(.*?);/) || [])[1] || 'image/jpeg' });
  }

  /* ---------- copy listing code ---------- */
  function copyText(t) {
    if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(t);
    const ta = document.createElement('textarea');
    ta.value = t; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); } finally { ta.remove(); }
    return Promise.resolve();
  }
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-copy]'); if (!b) return;
    e.preventDefault();
    copyText(b.dataset.copy).then(() => toast('Código ' + b.dataset.copy + ' copiado'), () => toast('Não deu pra copiar. O código é ' + b.dataset.copy, 'error'));
    track('copiar_codigo', { codigo: b.dataset.copy });
  });

  /* ---------- analytics (Meta Pixel / GA), only after the visitor accepts cookies ---------- */
  const TRACK = window.PP_TRACK || null;
  function consent() { try { return localStorage.getItem('pp-cookies'); } catch (e) { return null; } }
  function loadTracking() {
    if (!TRACK || window.__ppTrack) return;
    window.__ppTrack = true;
    if (TRACK.ga) {
      const s = document.createElement('script'); s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id=' + TRACK.ga; document.head.appendChild(s);
      window.dataLayer = window.dataLayer || [];
      window.gtag = function () { window.dataLayer.push(arguments); };
      window.gtag('js', new Date()); window.gtag('config', TRACK.ga);
    }
    if (TRACK.pixel) {
      /* standard Meta Pixel loader */
      !function (f, b, e, v, n, t, s) { if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); }; if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0; t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s); }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
      window.fbq('init', TRACK.pixel); window.fbq('track', 'PageView');
    }
  }
  function track(name, params) {
    if (window.gtag) window.gtag('event', name, params || {});
    if (window.fbq) window.fbq('trackCustom', name, params || {});
  }
  function cookieBar() {
    if (!TRACK) return;
    const c = consent();
    if (c === 'ok') return loadTracking();
    if (c === 'no') return;
    const bar = document.createElement('div');
    bar.className = 'cookie-bar'; bar.setAttribute('role', 'dialog'); bar.setAttribute('aria-label', 'Cookies');
    bar.innerHTML = '<p>Usamos cookies pra entender como o site é usado e melhorar os anúncios. Você pode aceitar ou recusar.</p>' +
      '<div><button class="btn btn-outline btn-sm" data-c="no">Recusar</button><button class="btn btn-primary btn-sm" data-c="ok">Aceitar</button></div>';
    document.body.appendChild(bar);
    bar.addEventListener('click', e => {
      const b = e.target.closest('[data-c]'); if (!b) return;
      try { localStorage.setItem('pp-cookies', b.dataset.c); } catch (err) { /* private mode */ }
      bar.remove();
      if (b.dataset.c === 'ok') loadTracking();
    });
  }

  /* ---------- tabs (data-tabs="group"); every group on the page follows the same choice ---------- */
  function setTab(value, fromClick) {
    document.querySelectorAll('[data-tab]').forEach(t => {
      const on = t.dataset.tab === value;
      t.classList.toggle('active', on); t.setAttribute('aria-selected', on);
    });
    document.querySelectorAll('[data-pane]').forEach(p => { p.hidden = p.dataset.pane !== value; });
    if (fromClick) {
      track('aba_' + value, {});
      const u = new URL(location.href);
      if (value === 'vender') u.searchParams.set('aba', 'vender'); else u.searchParams.delete('aba');
      history.replaceState(null, '', u.pathname + u.search + u.hash);
    }
  }
  function tabs() {
    if (!document.querySelector('[data-tab]')) return;
    document.addEventListener('click', e => { const t = e.target.closest('[data-tab]'); if (t) setTab(t.dataset.tab, true); });
    if (param('aba') === 'vender') setTab('vender');
  }

  /* ---------- bottom sheet / modal (data-sheet-open="id") ---------- */
  function sheets() {
    let last = null;
    const close = s => { s.classList.remove('open'); s.setAttribute('aria-hidden', 'true'); document.body.classList.remove('sheet-open'); if (last) last.focus(); };
    document.addEventListener('click', e => {
      const o = e.target.closest('[data-sheet-open]');
      if (o) {
        const s = document.getElementById(o.dataset.sheetOpen); if (!s) return;
        last = o; s.classList.add('open'); s.setAttribute('aria-hidden', 'false'); document.body.classList.add('sheet-open');
        const f = s.querySelector('input'); if (f && window.matchMedia('(min-width: 641px)').matches) setTimeout(() => f.focus(), 50);
        return;
      }
      const s = e.target.closest('.sheet');
      if (s && (e.target.closest('[data-sheet-close]') || e.target === s)) close(s);
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.sheet.open').forEach(close); });
    document.querySelectorAll('[data-sheet-filter]').forEach(inp => inp.addEventListener('input', () => {
      const q = PP.slugify(inp.value).replace(/-/g, ' ');
      const box = document.getElementById(inp.dataset.sheetFilter);
      box.querySelectorAll('[data-k]').forEach(a => { a.hidden = q && !a.dataset.k.includes(q); });
      box.querySelectorAll('.sheet-group').forEach(g => { g.hidden = !g.querySelector('[data-k]:not([hidden])'); });
      const none = box.querySelector('.sheet-none'); if (none) none.hidden = !!box.querySelector('[data-k]:not([hidden])');
    }));
  }

  /* ---------- horizontal carousels with arrows (data-carousel) ---------- */
  function carousels() {
    document.querySelectorAll('[data-carousel]').forEach(wrap => {
      const rail = wrap.querySelector(".carousel-track"); if (!rail) return;
      const prev = wrap.querySelector('[data-dir="-1"]'), next = wrap.querySelector('[data-dir="1"]');
      const step = () => { const c = rail.querySelector(':scope > *'); return c ? c.getBoundingClientRect().width + 20 : 300; };
      const upd = () => {
        if (prev) prev.disabled = rail.scrollLeft < 8;
        if (next) next.disabled = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 8;
      };
      [prev, next].forEach(b => b && b.addEventListener('click', () => rail.scrollBy({ left: Number(b.dataset.dir) * step(), behavior: 'smooth' })));
      rail.addEventListener("scroll", upd, { passive: true }); window.addEventListener('resize', upd); upd();
    });
  }

  Object.assign(PP, {
    CONFIG, PRETTY, money, moneyShort, num, dateBR, timeAgo, esc, param, seg, cidade, cidadeLabel,
    anuncioSlug, anuncioUrl, anuncioTitulo, cidadeUrl, segmentoUrl, whatsappUrl, icons, ic, card, toast, setMeta, jsonLd,
    masks, bindMasks, unmoney, resizeImage, dataUrlToBlob, logo, track, setTab, copyText
  });

  document.addEventListener('DOMContentLoaded', () => { header(); tabs(); sheets(); carousels(); cookieBar(); icons(); });
})();
