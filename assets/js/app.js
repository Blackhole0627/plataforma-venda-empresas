/* ==========================================================================
   Shared UI: config, formatting, header/footer, cards, toast
   ========================================================================== */

(function () {
  'use strict';
  const PP = window.PP;

  /* Brand placeholder. Swap name/contacts here when the client sends them. */
  const CONFIG = {
    marca: 'PassaPonto',
    marcaA: 'Passa', marcaB: 'Ponto',
    slogan: 'Compra e venda de empresas com sigilo',
    whatsapp: '5511999999999',
    whatsappLabel: '(11) 99999-9999',
    email: 'contato@passaponto.com.br',
    instagram: '@passaponto'
  };

  /* friendly URLs on the live server (Netlify rewrites), query strings locally */
  const PRETTY = !['localhost', '127.0.0.1', ''].includes(location.hostname);

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
  function anuncioUrl(a) { return PRETTY ? '/empresa-a-venda/' + anuncioSlug(a) : '/anuncio.html?id=' + a.id; }
  function cidadeUrl(slug) { return PRETTY ? '/comprar-empresa/' + slug : '/comprar-empresa.html?cidade=' + slug; }
  function segmentoUrl(slug) { return '/comprar-empresa.html?segmento=' + slug; }
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

  /* ---------- header / footer ---------- */
  function logo(light) {
    return '<a class="logo' + (light ? ' logo-light' : '') + '" href="/index.html" aria-label="' + CONFIG.marca + ' início">' +
      '<span class="logo-mark">' + ic('handshake') + '</span>' +
      '<span class="logo-text">' + CONFIG.marcaA + '<b>' + CONFIG.marcaB + '</b></span></a>';
  }

  function header() {
    const el = document.getElementById('site-header');
    if (!el) return;
    const page = document.body.dataset.page || '';
    const link = (href, label, key) => '<a href="' + href + '"' + (page === key ? ' class="active" aria-current="page"' : '') + '>' + label + '</a>';
    el.className = 'site-header';
    el.innerHTML =
      '<div class="container header-inner">' + logo() +
        '<nav class="main-nav" id="main-nav" aria-label="Principal">' +
          link('/index.html', 'Início', 'home') +
          link('/comprar-empresa.html', 'Comprar empresa', 'comprar') +
          link('/vender-empresa.html', 'Vender empresa', 'vender') +
          link('/contato.html', 'Contato', 'contato') +
          '<a class="btn btn-primary nav-cta" href="/cadastro.html">' + ic('plus') + 'Anunciar minha empresa</a>' +
        '</nav>' +
        '<button class="nav-toggle" id="nav-toggle" aria-label="Abrir menu" aria-expanded="false">' + ic('menu') + '</button>' +
      '</div>';
    const t = document.getElementById('nav-toggle');
    t.addEventListener('click', () => {
      const open = document.body.classList.toggle('nav-open');
      t.setAttribute('aria-expanded', open);
    });
    const onScroll = () => el.classList.toggle('scrolled', window.scrollY > 8);
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  }

  function footer() {
    const el = document.getElementById('site-footer');
    if (!el) return;
    const caps = PP.Store.cidades(true).filter(c => c.capital).slice(0, 12);
    el.className = 'site-footer';
    el.innerHTML =
      '<div class="container footer-grid">' +
        '<div class="footer-brand">' + logo(true) +
          '<p>Plataforma para comprar e vender empresas com sigilo. Seu negócio anunciado sem expor o nome, e só compradores interessados de verdade chegam até você.</p>' +
          '<div class="footer-social">' +
            '<a href="' + whatsappUrl('Olá! Vim pelo site.') + '" target="_blank" rel="noopener" aria-label="WhatsApp">' + ic('message-circle') + '</a>' +
            '<a href="#" aria-label="Instagram">' + ic('instagram') + '</a>' +
            '<a href="mailto:' + CONFIG.email + '" aria-label="E-mail">' + ic('mail') + '</a>' +
          '</div>' +
        '</div>' +
        '<div><h4>Navegação</h4><ul>' +
          '<li><a href="/comprar-empresa.html">Comprar empresa</a></li>' +
          '<li><a href="/vender-empresa.html">Vender empresa</a></li>' +
          '<li><a href="/cadastro.html">Anunciar minha empresa</a></li>' +
          '<li><a href="/contato.html">Contato</a></li>' +
          '<li><a href="/admin/login.html">Área da equipe</a></li>' +
        '</ul></div>' +
        '<div class="footer-cities"><h4>Empresas à venda por cidade</h4><ul>' +
          caps.map(c => '<li><a href="' + cidadeUrl(c.slug) + '">' + esc(c.nome) + '/' + c.uf + '</a></li>').join('') +
        '</ul></div>' +
        '<div><h4>Atendimento</h4><ul class="footer-contact">' +
          '<li>' + ic('message-circle') + CONFIG.whatsappLabel + '</li>' +
          '<li>' + ic('mail') + CONFIG.email + '</li>' +
          '<li>' + ic('clock') + 'Seg a sex, 9h às 18h</li>' +
        '</ul></div>' +
      '</div>' +
      '<div class="container footer-bottom"><span>© ' + new Date().getFullYear() + ' ' + CONFIG.marca + '. Todos os direitos reservados.</span>' +
        '<span><a href="#">Política de privacidade</a> · <a href="#">Termos de uso</a></span></div>';

    // floating WhatsApp button
    if (!document.querySelector('.wa-float')) {
      const wa = document.createElement('a');
      wa.className = 'wa-float'; wa.target = '_blank'; wa.rel = 'noopener';
      wa.href = whatsappUrl('Olá! Vim pelo site e quero mais informações.');
      wa.setAttribute('aria-label', 'Falar no WhatsApp');
      wa.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.79-1.47-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.27.49 1.7.63.72.23 1.37.2 1.88.12.58-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.8h-.01a9.8 9.8 0 0 1-5-1.37l-.36-.21-3.72.98 1-3.63-.24-.37a9.8 9.8 0 0 1-1.5-5.22c0-5.42 4.41-9.83 9.84-9.83a9.77 9.77 0 0 1 9.82 9.84c0 5.42-4.41 9.83-9.83 9.83zm8.37-18.2A11.76 11.76 0 0 0 12.05 0C5.5 0 .16 5.33.16 11.89c0 2.1.55 4.14 1.59 5.94L.06 24l6.3-1.65a11.87 11.87 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.9 0-3.17-1.24-6.16-3.48-8.4z"/></svg>';
      document.body.appendChild(wa);
    }
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

  Object.assign(PP, {
    CONFIG, PRETTY, money, moneyShort, num, dateBR, timeAgo, esc, param, seg, cidade, cidadeLabel,
    anuncioSlug, anuncioUrl, cidadeUrl, segmentoUrl, whatsappUrl, icons, ic, card, toast, setMeta, jsonLd,
    masks, bindMasks, unmoney, resizeImage, logo
  });

  document.addEventListener('DOMContentLoaded', () => { header(); footer(); icons(); });
})();
