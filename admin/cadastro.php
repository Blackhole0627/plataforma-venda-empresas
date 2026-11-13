<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$c = cadastro((int) ($_GET['id'] ?? 0));
admin_head($c ? 'Cadastro ' . $c['protocolo'] : 'Cadastro', 'cadastros');
?>
  <div class="adm">
    <aside id="adm-side"></aside>
    <div class="adm-main">
      <header id="adm-top"></header>
      <main class="adm-content" id="content"></main>
    </div>
  </div>

  <?php admin_scripts(['cadastro' => $c, 'anuncio' => ($c && $c['anuncioId']) ? anuncio($c['anuncioId']) : null]); ?>
  <script>
    (function () {
      const { Store, esc, money, ic } = PP, A = PP.Admin;
      const $ = id => document.getElementById(id);
      const id = PP.param('id');

      function render() {
        const c = Store.cadastro(id);
        if (!c) { $('content').innerHTML = '<div class="adm-empty">' + ic('search-x') + '<p>Cadastro não encontrado.</p><a class="btn btn-primary" href="/admin/">Voltar</a></div>'; PP.icons(); return; }
        const mo = v => v == null ? '' : money(v); // left blank by the seller -> "-"
        const kv = (l, v, wide) => '<div' + (wide ? ' class="wide"' : '') + '><span>' + l + '</span><strong>' + esc(v === '' || v == null ? '-' : v) + '</strong></div>';
        const phone = c.responsavel.whatsapp.replace(/\D/g, '');
        // Instagram and site come from the seller, so only http(s) links are made clickable
        const link = (l, v) => {
          if (!v) return kv(l, '');
          let u = v.trim();
          if (l === 'Instagram' && !/^https?:/i.test(u)) u = 'https://instagram.com/' + u.replace(/^@/, '').replace(/^(www\.)?instagram\.com\//i, '');
          else if (!/^https?:\/\//i.test(u)) u = 'https://' + u;
          if (!/^https?:\/\/[^\s"'<>]+$/i.test(u)) return kv(l, v);
          return '<div><span>' + l + '</span><strong><a href="' + esc(u) + '" target="_blank" rel="noopener nofollow">' + esc(v) + '</a></strong></div>';
        };
        const anuncio = c.anuncioId ? Store.anuncio(c.anuncioId) : null;

        $('content').innerHTML =
          '<div class="adm-head"><div>' +
            '<a class="back" href="/admin/">' + ic('arrow-left') + 'Cadastros</a>' +
            '<h2>' + esc(c.empresa.nome) + '</h2>' +
            '<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">' + A.statusPill(c.status) +
              '<span class="muted" style="font-size:.88rem">' + esc(c.protocolo) + ' · recebido em ' + new Date(c.data).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' }) + '</span></div>' +
          '</div>' +
          '<div style="display:flex;gap:8px;flex-wrap:wrap"><button class="btn btn-outline btn-sm" id="copy">' + ic('copy') + 'Copiar dados</button>' +
          '<button class="btn btn-outline btn-sm" onclick="window.print()">' + ic('printer') + 'Imprimir</button></div></div>' +

          '<div class="adm-grid"><div>' +
            '<section class="adm-panel"><h2>' + ic('building-2') + 'Empresa</h2><div class="kv">' +
              kv('Nome', c.empresa.nome) + kv('Razão social', c.empresa.razaoSocial) + kv('CNPJ', c.empresa.cnpj || 'Não informado') + kv('Segmento', PP.seg(c.segmento).nome) +
              kv('Cidade', c.cidade ? PP.cidadeLabel(c.cidade) : c.cidadeOutra + ' (não cadastrada)') + kv('Bairro', c.bairro) +
              kv('Endereço', A.enderecoTexto(c), true) + kv('Fundação', c.fundacao) +
              kv('Funcionários', c.funcionarios) + kv('Área', c.area ? c.area + ' m²' : '') + kv('Horário', c.horario) +
              kv('Imóvel', A.imovelTexto(c.imovel)) + kv('Energia solar', c.energiaSolar ? 'Sim' : 'Não') +
              link('Instagram', c.empresa.instagram) + link('Site', c.empresa.site) +
              kv('Descrição', c.descricao, true) +
            '</div></section>' +
            '<section class="adm-panel"><h2>' + ic('bar-chart-3') + 'Valores</h2><div class="kv">' +
              kv('Valor pedido', money(c.preco)) + kv('Faturamento/mês', money(c.faturamento)) + kv('Margem', c.margem == null ? '' : c.margem + '%') +
              kv('Folha', mo(c.folha)) + kv('Aluguel + cond.', mo(c.aluguel)) + kv('Estoque', mo(c.estoque)) +
              kv('Equipamentos, móveis e instalações', mo(c.bens)) + kv('Aceita proposta', c.aceitaProposta ? 'Sim' : 'Não') +
              kv('Venda com CNPJ', c.cnpj) + kv('Marca inclusa', c.marca ? 'Sim' : 'Não') +
              kv('Aceitou a comissão', c.aceiteComissao ? 'Sim' : 'Não') +
              kv('Motivo da venda (interno)', c.motivo, true) +
            '</div></section>' +
            '<section class="adm-panel"><h2>' + ic('images') + 'Fotos <span class="muted" style="font-weight:500;font-size:.88rem">(' + (c.fotoTipo === 'ilustrativas' ? 'pediu fotos ilustrativas' : c.fotos.length + ' enviadas') + ')</span></h2>' +
              (c.fotos.length ? '<div class="adm-photos">' + c.fotos.map((f, i) =>
                '<a href="' + PP.photoUrl(f, 1600) + '" target="_blank" rel="noopener" download="' + esc(c.protocolo) + '-foto-' + (i + 1) + '.jpg"><img src="' + PP.photoUrl(f, 300) + '" alt="Foto ' + (i + 1) + '"><span class="dl">' + ic('download') + '</span></a>').join('') + '</div>'
                : '<p class="muted" style="margin:0">' + 'Sem fotos. Usar fotos ilustrativas do segmento.' + '</p>') +
            '</section>' +
          '</div>' +

          '<aside>' +
            '<section class="adm-panel side-actions"><h2>' + ic('zap') + 'Próximo passo</h2>' +
              (anuncio
                ? '<div class="note" style="margin-bottom:14px">' + ic('check-circle') + '<span>Anúncio <strong>' + esc(anuncio.codigo) + '</strong> criado a partir deste cadastro.</span></div>' +
                  '<a class="btn btn-primary" href="/admin/anuncio-editar?id=' + anuncio.id + '">' + ic('pencil') + 'Editar anúncio</a>' +
                  '<a class="btn btn-outline" href="' + PP.anuncioUrl(anuncio) + (anuncio.status === 'publicado' ? '' : '?preview=1') + '" target="_blank">' + ic('external-link') + 'Ver no site</a>'
                : (c.status !== 'recusado' ? '<a class="btn btn-primary" href="/admin/anuncio-editar?from=' + c.id + '">' + ic('megaphone') + 'Criar anúncio com esses dados</a>' : '') +
                  (c.status === 'novo' ? '<button class="btn btn-outline" data-st="analise">' + ic('search-check') + 'Marcar em análise</button>' : '') +
                  (c.status !== 'recusado' ? '<button class="btn btn-danger" data-st="recusado">' + ic('x-circle') + 'Recusar</button>' : '<button class="btn btn-outline" data-st="analise">' + ic('rotate-ccw') + 'Voltar pra análise</button>')) +
            '</section>' +
            '<section class="adm-panel"><h2>' + ic('user') + 'Responsável</h2>' +
              '<div class="contact-line">' + ic('user') + '<strong>' + esc(c.responsavel.nome) + '</strong></div>' +
              '<div class="contact-line">' + ic('message-circle') + '<a href="https://wa.me/55' + phone + '?text=' + encodeURIComponent('Olá ' + c.responsavel.nome.split(' ')[0] + '! Recebemos o cadastro da sua empresa (protocolo ' + c.protocolo + ').') + '" target="_blank" rel="noopener">' + esc(c.responsavel.whatsapp) + '</a></div>' +
              '<div class="contact-line">' + ic('mail') + '<a href="mailto:' + esc(c.responsavel.email) + '">' + esc(c.responsavel.email) + '</a></div>' +
            '</section>' +
            '<section class="adm-panel"><h2>' + ic('history') + 'Histórico</h2><ul class="history">' +
              '<li><div>Cadastro recebido pelo site<span>' + new Date(c.data).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' }) + '</span></div></li>' +
              (c.analiseEm ? '<li><div>Marcado em análise<span>' + new Date(c.analiseEm).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' }) + '</span></div></li>' : '') +
              (c.publicadoEm || anuncio ? '<li><div>Anúncio criado' + (anuncio ? ' (' + esc(anuncio.codigo) + ')' : '') + '<span>' + new Date(c.publicadoEm || (anuncio && anuncio.data) || c.data).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' }) + '</span></div></li>' : '') +
              (c.status === 'recusado' ? '<li><div>Cadastro recusado</div></li>' : '') +
            '</ul></section>' +
          '</aside></div>';
        PP.icons();

        $('copy').onclick = async () => {
          try { await navigator.clipboard.writeText(A.cadastroTexto(c)); PP.toast('Dados copiados'); } catch (e) { PP.toast('Não deu pra copiar', 'error'); }
        };
        document.querySelectorAll('[data-st]').forEach(b => b.onclick = async () => {
          const st = b.dataset.st;
          if (st === 'recusado' && !confirm('Recusar esse cadastro?')) return;
          const r = await A.busy(b, () => Store.setCadastroStatus(c.id, st));
          if (r) { PP.toast('Status atualizado: ' + A.STATUS[st].label); render(); }
        });
      }
      document.addEventListener('DOMContentLoaded', render);
    })();
  </script>
</body>
</html>
