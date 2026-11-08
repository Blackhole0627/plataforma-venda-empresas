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
  function card(a) {
    const s = seg(a.segmento);
    const foto = PP.photoUrl(a.fotos[0] || ('u:' + s.fotos[0]), 640);
    return '' +
      '<article class="card-anuncio">' +
        '<a class="card-media" href="' + anuncioUrl(a) + '" aria-label="' + esc(s.nome + ' à venda em ' + cidadeLabel(a.cidade)) + '">' +
          '<img src="' + foto + '" alt="' + esc(s.nome) + ' à venda em ' + esc(cidadeLabel(a.cidade)) + '" loading="lazy">' +
          '<span class="badge-seg">' + ic(s.icon) + esc(s.nome) + '</span>' +
          (a.destaque ? '<span class="badge-destaque">' + ic('star') + 'Destaque</span>' : '') +
        '</a>' +
        '<div class="card-body">' +
          '<div class="card-top"><span class="card-code">Cód. ' + esc(a.codigo) + '</span><span class="card-date">' + timeAgo(a.data) + '</span></div>' +
          '<h3 class="card-title"><a href="' + anuncioUrl(a) + '">' + esc(s.nome) + ' à venda</a></h3>' +
          '<p class="card-loc">' + ic('map-pin') + esc(a.bairro ? a.bairro + ', ' : '') + esc(cidadeLabel(a.cidade)) + '</p>' +
          '<div class="card-stats">' +
            '<span title="Faturamento médio mensal">' + ic('trending-up') + moneyShort(a.faturamento) + '/mês</span>' +
            '<span title="Funcionários">' + ic('users') + a.funcionarios + ' func.</span>' +
            '<span title="Área">' + ic('ruler') + num(a.area) + ' m²</span>' +
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

  Object.assign(PP, {
    CONFIG, PRETTY, money, moneyShort, num, dateBR, timeAgo, esc, param, seg, cidade, cidadeLabel,
    anuncioSlug, anuncioUrl, cidadeUrl, segmentoUrl, whatsappUrl, icons, ic, card, toast, setMeta, jsonLd,
    masks, bindMasks, unmoney, resizeImage, dataUrlToBlob, logo
  });

  document.addEventListener('DOMContentLoaded', () => { header(); icons(); });
})();
