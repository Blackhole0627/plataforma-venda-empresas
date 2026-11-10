/* ==========================================================================
   Área interna: sidebar, topbar, status helpers.
   Login is checked by PHP before the page is sent.
   ========================================================================== */

(function () {
  'use strict';
  const PP = window.PP, B = PP.BOOT;

  const STATUS = {
    novo: { label: 'Novo', icon: 'inbox' },
    analise: { label: 'Em análise', icon: 'search-check' },
    publicado: { label: 'Publicado', icon: 'check-circle' },
    recusado: { label: 'Recusado', icon: 'x-circle' },
    pausado: { label: 'Pausado', icon: 'pause-circle' },
    rascunho: { label: 'Rascunho', icon: 'file-pen' }
  };
  function statusPill(s) { return '<span class="status st-' + s + '">' + (STATUS[s] ? STATUS[s].label : s) + '</span>'; }

  function initials(n) { return String(n || '?').trim().split(/\s+/).map(p => p[0]).slice(0, 2).join('').toUpperCase(); }

  function shell() {
    const side = document.getElementById('adm-side');
    const top = document.getElementById('adm-top');
    if (!side || !top) return;
    const page = document.body.dataset.page;
    const novos = B.novos || 0;
    const user = B.user || { nome: '' };
    const link = (href, icon, label, key, extra) =>
      '<a href="' + href + '" class="' + (page === key ? 'active' : '') + '">' + PP.ic(icon) + label + (extra || '') + '</a>';

    side.className = 'adm-side';
    side.innerHTML = PP.logo(true) +
      '<div class="side-label">Gestão</div><nav class="adm-nav">' +
        link('/admin/', 'inbox', 'Cadastros recebidos', 'cadastros', novos ? '<span class="count">' + novos + '</span>' : '') +
        link('/admin/anuncios', 'megaphone', 'Anúncios', 'anuncios') +
        link('/admin/cidades', 'map-pin', 'Cidades', 'cidades') +
        link('/admin/mensagens', 'mail', 'Mensagens do site', 'mensagens') +
      '</nav>' +
      '<div class="side-bottom">' +
        '<div class="side-help"><strong>Como funciona</strong>O cadastro chega aqui, a equipe confere, cria o anúncio e publica no site.</div>' +
        '<nav class="adm-nav">' +
          link('/admin/conta', 'user-cog', 'Equipe, valores e senha', 'conta') +
          '<a href="/" target="_blank">' + PP.ic('external-link') + 'Ver site</a>' +
          '<a href="/admin/logout?t=' + encodeURIComponent(B.csrf || '') + '">' + PP.ic('log-out') + 'Sair</a>' +
        '</nav>' +
      '</div>';

    top.className = 'adm-top';
    top.innerHTML =
      '<div style="display:flex;align-items:center;gap:10px"><button class="adm-menu" id="adm-menu" aria-label="Menu">' + PP.ic('menu') + '</button><h1>' + PP.esc(document.body.dataset.title || '') + '</h1></div>' +
      '<div class="adm-user"><div style="text-align:right"><strong>' + PP.esc(user.nome) + '</strong><span>' + PP.esc(user.email || 'Equipe') + '</span></div><span class="adm-initials">' + PP.esc(initials(user.nome)) + '</span></div>';

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
      'Razão social: ' + (c.empresa.razaoSocial || '-'),
      'Endereço: ' + enderecoTexto(c),
      'Instagram: ' + (c.empresa.instagram || '-') + ' | Site: ' + (c.empresa.site || '-'),
      'Segmento: ' + PP.seg(c.segmento).nome,
      'Cidade: ' + (c.cidade ? PP.cidadeLabel(c.cidade) : c.cidadeOutra) + ' | Bairro: ' + c.bairro,
      'Fundação: ' + c.fundacao + ' | Funcionários: ' + c.funcionarios + ' | Área: ' + c.area + ' m²',
      'Horário: ' + (c.horario || '-'),
      'Valor pedido: ' + m(c.preco) + ' | Faturamento/mês: ' + m(c.faturamento) + ' | Margem: ' + (c.margem || '-') + '%',
      'Folha: ' + m(c.folha) + ' | Aluguel: ' + m(c.aluguel) + ' | Estoque: ' + m(c.estoque),
      'Equipamentos, móveis e instalações: ' + m(c.bens) + ' | Aceita proposta: ' + (c.aceitaProposta ? 'Sim' : 'Não'),
      'Imóvel: ' + imovelTexto(c.imovel) + ' | Energia solar: ' + (c.energiaSolar ? 'Sim' : 'Não') + ' | Aceitou a comissão: ' + (c.aceiteComissao ? 'Sim' : 'Não'),
      'Venda com CNPJ: ' + c.cnpj + ' | Marca inclusa: ' + (c.marca ? 'Sim' : 'Não'),
      'Motivo: ' + (c.motivo || '-'),
      'Descrição: ' + c.descricao,
      'Fotos: ' + (c.fotoTipo === 'ilustrativas' ? 'ilustrativas' : c.fotos.length + ' foto(s) enviadas')
    ].join('\n');
  }

  function enderecoTexto(c) {
    const e = c.endereco || {};
    if (!e.rua) return '-';
    return e.rua + ', ' + e.numero + (e.complemento ? ' ' + e.complemento : '') + ' · ' + c.bairro + ' · CEP ' + e.cep;
  }
  function imovelTexto(v) { return v === 'proprio' ? 'Próprio' : v === 'alugado' ? 'Alugado' : '-'; }

  /* runs an async action with the button disabled; shows the error as a toast */
  async function busy(btn, fn) {
    if (btn) btn.disabled = true;
    try { return await fn(); }
    catch (e) { PP.toast(e.message, 'error'); return null; }
    finally { if (btn) btn.disabled = false; }
  }

  Object.assign(PP, { Admin: { STATUS, statusPill, cadastroTexto, enderecoTexto, imovelTexto, busy } });
  document.addEventListener('DOMContentLoaded', () => { shell(); PP.icons(); });
})();
