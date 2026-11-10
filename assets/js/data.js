/* ==========================================================================
   Data layer
   Reads what the server printed in window.PP_BOOT and saves through the API
   (/admin/api for the team area, /api/* for public forms).
   ========================================================================== */

(function () {
  'use strict';
  const B = window.PP_BOOT || {};

  function slugify(str) {
    return String(str || '')
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }

  function photoUrl(p, w) {
    if (!p) return '';
    const id = p.replace(/^u:/, '');
    if (id.startsWith('data:') || id.startsWith('http') || id.startsWith('/') || id.startsWith('blob:')) return id;
    return 'https://images.unsplash.com/photo-' + id + '?auto=format&fit=crop&w=' + (w || 800) + '&q=70';
  }

  const SEGMENTOS = B.segmentos || [];
  function segFotos(seg) {
    const s = SEGMENTOS.find(x => x.slug === seg) || SEGMENTOS[SEGMENTOS.length - 1];
    return (s ? s.fotos : []).map(id => 'u:' + id);
  }

  /* ---------- API ---------- */
  async function api(url, payload, opts) {
    const isForm = payload instanceof FormData;
    const headers = { 'Accept': 'application/json' };
    if (!isForm) headers['Content-Type'] = 'application/json';
    if (B.csrf) headers['X-CSRF'] = B.csrf;
    let res, data;
    try {
      res = await fetch(url, Object.assign({ method: 'POST', headers, body: isForm ? payload : JSON.stringify(payload || {}), credentials: 'same-origin' }, opts));
      data = await res.json();
    } catch (e) {
      throw new Error('Sem conexão com o servidor. Tenta de novo.');
    }
    if (res.status === 401) { location.href = '/admin/login?next=' + encodeURIComponent(location.pathname + location.search); }
    if (!res.ok || data.error) throw new Error(data.error || 'Algo deu errado. Tenta de novo.');
    return data;
  }
  const admin = (action, payload) => api('/admin/api', Object.assign({ action }, payload || {}));

  /* ---------- store (in-memory copy of what the server sent) ---------- */
  const cidades = B.cidades || [];
  const anuncios = B.anuncios || (B.anuncio ? [B.anuncio] : []);
  const cadastros = B.cadastros || (B.cadastro ? [B.cadastro] : []);
  const upsert = (list, item) => { const i = list.findIndex(x => x.id === item.id); if (i >= 0) list[i] = item; else list.unshift(item); return item; };

  const Store = {
    cidades(onlyActive) { return onlyActive ? cidades.filter(c => c.ativa) : cidades; },
    cidade(slug) { return cidades.find(c => c.slug === slug); },
    anuncios() { return anuncios; },
    anuncio(id) { return anuncios.find(a => a.id === Number(id)); },
    cadastros() { return cadastros; },
    cadastro(id) { return cadastros.find(c => c.id === Number(id)); },

    /* team area writes (return promises) */
    async saveAnuncio(a) { const r = await admin('anuncio.salvar', { anuncio: a }); return upsert(anuncios, r.anuncio); },
    async patchAnuncio(id, patch) { const r = await admin('anuncio.alterar', { id, patch }); return upsert(anuncios, r.anuncio); },
    async removeAnuncio(id) { await admin('anuncio.excluir', { id }); const i = anuncios.findIndex(a => a.id === Number(id)); if (i >= 0) anuncios.splice(i, 1); },
    async setCadastroStatus(id, status) { const r = await admin('cadastro.status', { id, status }); return upsert(cadastros, r.cadastro); },
    async addCidade(nome, uf) { const r = await admin('cidade.adicionar', { nome, uf }); cidades.push(r.cidade); return r.cidade; },
    async toggleCidade(slug) { const r = await admin('cidade.alternar', { slug }); Object.assign(Store.cidade(slug), r.cidade); return r.cidade; },
    async setInstagram(slug, instagram) { const r = await admin('cidade.instagram', { slug, instagram }); Object.assign(Store.cidade(slug), r.cidade); return r.cidade; },
    async removeCidade(slug) { await admin('cidade.remover', { slug }); const i = cidades.findIndex(c => c.slug === slug); if (i >= 0) cidades.splice(i, 1); },
    async uploadFoto(blob) { const f = new FormData(); f.append('action', 'foto.enviar'); f.append('foto', blob, 'foto.jpg'); return (await api('/admin/api', f)).path; }
  };

  window.PP = Object.assign(window.PP || {}, {
    BOOT: B, slugify, photoUrl, SEGMENTOS, UFS: B.ufs || [], IMAGENS: B.imagens || {}, EQUIPE: B.equipe || [],
    Store, segFotos, api
  });
})();
