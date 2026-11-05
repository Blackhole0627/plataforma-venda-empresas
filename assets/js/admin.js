/* ==========================================================================
   Área interna: login, sidebar, topbar, status helpers
   (front-end only for now, the real login will be PHP session + password_hash)
   ========================================================================== */

(function () {
  'use strict';
  const PP = window.PP;
  const AUTH_KEY = 'pp_admin_session';

  function getSession() { try { return JSON.parse(localStorage.getItem(AUTH_KEY)); } catch (e) { return null; } }
  function login(email) { try { localStorage.setItem(AUTH_KEY, JSON.stringify({ email, at: Date.now() })); } catch (e) {} }
  function logout() { try { localStorage.removeItem(AUTH_KEY); } catch (e) {} location.href = '/admin/login.html'; }

  const isLogin = /\/admin\/login(\.html)?$/.test(location.pathname);
  if (!isLogin && !getSession()) {
    location.replace('/admin/login.html?next=' + encodeURIComponent(location.pathname + location.search));
  }

  const STATUS = {
    novo: { label: 'Novo', icon: 'inbox' },
    analise: { label: 'Em análise', icon: 'search-check' },
    publicado: { label: 'Publicado', icon: 'check-circle' },
    recusado: { label: 'Recusado', icon: 'x-circle' },
    pausado: { label: 'Pausado', icon: 'pause-circle' },
    rascunho: { label: 'Rascunho', icon: 'file-pen' }
  };
  function statusPill(s) { return '<span class="status st-' + s + '">' + (STATUS[s] ? STATUS[s].label : s) + '</span>'; }

  function shell() {
    const side = document.getElementById('adm-side');
    const top = document.getElementById('adm-top');
    if (!side || !top) return;
    const page = document.body.dataset.page;
    const novos = PP.Store.cadastros().filter(c => c.status === 'novo').length;
    const user = PP.EQUIPE[3];
    const link = (href, icon, label, key, extra) =>
      '<a href="' + href + '" class="' + (page === key ? 'active' : '') + '">' + PP.ic(icon) + label + (extra || '') + '</a>';

    side.className = 'adm-side';
    side.innerHTML = PP.logo(true) +
      '<div class="side-label">Gestão</div><nav class="adm-nav">' +
        link('/admin/index.html', 'inbox', 'Cadastros recebidos', 'cadastros', novos ? '<span class="count">' + novos + '</span>' : '') +
        link('/admin/anuncios.html', 'megaphone', 'Anúncios', 'anuncios') +
        link('/admin/cidades.html', 'map-pin', 'Cidades', 'cidades') +
      '</nav>' +
      '<div class="side-bottom">' +
        '<div class="side-help"><strong>Como funciona</strong>O cadastro chega aqui, a equipe confere, cria o anúncio e publica no site.</div>' +
        '<nav class="adm-nav">' +
          '<a href="/index.html" target="_blank">' + PP.ic('external-link') + 'Ver site</a>' +
          '<button id="reset-demo">' + PP.ic('rotate-ccw') + 'Restaurar dados de exemplo</button>' +
          '<button id="logout">' + PP.ic('log-out') + 'Sair</button>' +
        '</nav>' +
      '</div>';

    top.className = 'adm-top';
    top.innerHTML =
      '<div style="display:flex;align-items:center;gap:10px"><button class="adm-menu" id="adm-menu" aria-label="Menu">' + PP.ic('menu') + '</button><h1>' + (document.body.dataset.title || '') + '</h1></div>' +
      '<div class="adm-user"><div style="text-align:right"><strong>' + PP.esc(user.nome) + '</strong><span>Equipe</span></div><img src="' + PP.photoUrl(user.foto, 100) + '" alt="' + PP.esc(user.nome) + '"></div>';

    document.getElementById('logout').onclick = logout;
    document.getElementById('reset-demo').onclick = () => {
      if (confirm('Restaurar os dados de exemplo? Os cadastros e anúncios criados neste navegador serão apagados.')) { PP.Store.reset(); location.reload(); }
    };
    document.getElementById('adm-menu').onclick = e => { e.stopPropagation(); document.body.classList.toggle('side-open'); };
    document.addEventListener('click', e => { if (document.body.classList.contains('side-open') && !e.target.closest('.adm-side')) document.body.classList.remove('side-open'); });
  }

  /* plain-text summary for the team to paste anywhere */
  function cadastroTexto(c) {
    const m = PP.money;
    return [
      'Protocolo: ' + c.protocolo,
      'Responsável: ' + c.responsavel.nome + ' | ' + c.responsavel.whatsapp + ' | ' + c.responsavel.email,
      'Empresa: ' + c.empresa.nome + (c.empresa.cnpj ? ' | CNPJ ' + c.empresa.cnpj : ''),
      'Segmento: ' + PP.seg(c.segmento).nome,
      'Cidade: ' + (c.cidade ? PP.cidadeLabel(c.cidade) : c.cidadeOutra) + ' | Bairro: ' + c.bairro,
      'Fundação: ' + c.fundacao + ' | Funcionários: ' + c.funcionarios + ' | Área: ' + c.area + ' m²',
      'Horário: ' + (c.horario || '-'),
      'Valor pedido: ' + m(c.preco) + ' | Faturamento/mês: ' + m(c.faturamento) + ' | Margem: ' + (c.margem || '-') + '%',
      'Folha: ' + m(c.folha) + ' | Aluguel: ' + m(c.aluguel) + ' | Estoque: ' + m(c.estoque),
      'Venda com CNPJ: ' + c.cnpj + ' | Marca inclusa: ' + (c.marca ? 'Sim' : 'Não'),
      'Motivo: ' + (c.motivo || '-'),
      'Descrição: ' + c.descricao,
      'Fotos: ' + (c.fotoTipo === 'ilustrativas' ? 'ilustrativas' : c.fotos.length + ' foto(s) enviadas')
    ].join('\n');
  }

  Object.assign(PP, { Admin: { login, logout, getSession, STATUS, statusPill, cadastroTexto } });
  document.addEventListener('DOMContentLoaded', () => { shell(); PP.icons(); });
})();
