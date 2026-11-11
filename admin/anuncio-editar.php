<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$editId = (int) ($_GET['id'] ?? 0);
$fromId = (int) ($_GET['from'] ?? 0);
$anuncio = $editId ? anuncio($editId) : null;
$cad = $fromId ? cadastro($fromId) : (($anuncio && $anuncio['cadastroId']) ? cadastro($anuncio['cadastroId']) : null);
if (($editId && !$anuncio) || ($fromId && !$cad)) { require_login(); redirect('/admin/anuncios'); }
admin_head($anuncio ? 'Anúncio ' . $anuncio['codigo'] : 'Novo anúncio', 'anuncios');
?>
  <div class="adm">
    <aside id="adm-side"></aside>
    <div class="adm-main">
      <header id="adm-top"></header>
      <main class="adm-content">
        <div class="adm-head">
          <div>
            <a class="back" href="/admin/anuncios"><i data-lucide="arrow-left"></i>Anúncios</a>
            <h2 id="ed-title">Novo anúncio</h2>
            <div id="ed-sub" class="muted" style="font-size:.9rem"></div>
          </div>
        </div>
        <div id="from-note"></div>

        <form id="form" novalidate class="editor-grid">
          <div>
            <section class="adm-panel"><h2><i data-lucide="map-pin"></i>Segmento e local</h2>
              <div class="grid-3">
                <div class="field"><label for="segmento">Segmento</label><select class="select" id="segmento"><option value="">Escolha</option></select><span class="error">Escolha o segmento.</span></div>
                <div class="field"><label for="cidade">Cidade</label><select class="select" id="cidade"><option value="">Escolha</option></select><span class="error">Escolha a cidade.</span></div>
                <div class="field"><label for="bairro">Bairro</label><input class="input" id="bairro"><span class="error">Digite o bairro.</span></div>
              </div>
            </section>

            <section class="adm-panel"><h2><i data-lucide="file-text"></i>Texto do anúncio</h2>
              <div class="note warn" style="margin-bottom:16px"><i data-lucide="eye-off"></i><span>Não coloque o nome da empresa, endereço ou qualquer coisa que identifique o negócio.</span></div>
              <div class="field"><label for="descricao">Descrição</label><textarea class="textarea" id="descricao" style="min-height:140px"></textarea><small><span id="desc-count">0</span> caracteres · mínimo 30</small><span class="error">Escreva pelo menos 30 caracteres.</span></div>
            </section>

            <section class="adm-panel"><h2><i data-lucide="building-2"></i>Estrutura</h2>
              <div class="grid-3">
                <div class="field"><label for="fundacao">Ano de fundação</label><input class="input" id="fundacao" type="number"></div>
                <div class="field"><label for="funcionarios">Funcionários</label><input class="input" id="funcionarios" type="number" min="0"></div>
                <div class="field"><label for="area">Área (m²)</label><input class="input" id="area" type="number" min="0"></div>
              </div>
              <div class="grid-2">
                <div class="field"><label for="horario">Horário</label><input class="input" id="horario"></div>
                <div class="field"><label for="cnpj">Venda com CNPJ</label><select class="select" id="cnpj"><option>Indiferente</option><option>Sim</option><option>Não</option></select></div>
              </div>
              <label class="switch"><span><strong>Marca inclusa na venda</strong></span><input type="checkbox" id="marca"></label>
            </section>

            <section class="adm-panel"><h2><i data-lucide="bar-chart-3"></i>Valores</h2>
              <div class="grid-3">
                <div class="field"><label for="preco">Valor pedido</label><input class="input" id="preco" data-mask="money"><span class="error">Informe o valor.</span></div>
                <div class="field"><label for="faturamento">Faturamento/mês</label><input class="input" id="faturamento" data-mask="money"></div>
                <div class="field"><label for="margem">Margem (%)</label><input class="input" id="margem" type="number" min="0" max="100"></div>
              </div>
              <div class="grid-3">
                <div class="field"><label for="folha">Folha</label><input class="input" id="folha" data-mask="money"></div>
                <div class="field"><label for="aluguel">Aluguel + cond.</label><input class="input" id="aluguel" data-mask="money"></div>
                <div class="field"><label for="estoque">Estoque</label><input class="input" id="estoque" data-mask="money"></div>
              </div>
              <div class="grid-2">
                <div class="field"><label for="bens">Valor estimado dos bens incluídos</label><input class="input" id="bens" data-mask="money"></div>
              </div>
              <label class="switch"><span><strong>Aceita proposta</strong></span><input type="checkbox" id="aceitaProposta"></label>
            </section>

            <section class="adm-panel"><h2><i data-lucide="images"></i>Fotos <span class="muted" id="foto-count" style="font-weight:500;font-size:.88rem"></span></h2>
              <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
                <button type="button" class="btn btn-outline btn-sm" id="use-stock"><i data-lucide="image"></i>Usar fotos ilustrativas do segmento</button>
                <button type="button" class="btn btn-ghost btn-sm" id="clear-photos"><i data-lucide="trash-2"></i>Limpar</button>
              </div>
              <div class="ed-photos" id="photos"></div>
              <input type="file" id="file" accept="image/*" multiple class="sr-only">
              <small class="muted" style="display:block;margin-top:10px">A primeira foto é a capa. Clique na estrela pra trocar a capa.</small>
            </section>

            <section class="adm-panel"><h2><i data-lucide="settings-2"></i>Publicação</h2>
              <label class="switch"><span><strong>Destaque</strong><br><small class="muted">Aparece na página inicial</small></span><input type="checkbox" id="destaque"></label>
            </section>

            <div class="sticky-actions">
              <button type="button" class="btn btn-outline" id="save-draft"><i data-lucide="save"></i>Salvar rascunho</button>
              <button type="submit" class="btn btn-primary" id="publish"><i data-lucide="send"></i>Publicar no site</button>
            </div>
          </div>

          <aside class="preview-sticky">
            <span class="label" style="display:block;margin-bottom:10px">Prévia do card</span>
            <div id="preview"></div>
            <div class="seo-snippet">
              <div class="ss-label"><i data-lucide="search"></i>Como aparece no Google</div>
              <div class="ss-url" id="ss-url"></div>
              <div class="ss-title" id="ss-title"></div>
              <div class="ss-desc" id="ss-desc"></div>
            </div>
          </aside>
        </form>
      </main>
    </div>
  </div>

  <?php admin_scripts(['anuncio' => $anuncio, 'cadastro' => $cad]); ?>
  <script>
    (function () {
      const { Store, SEGMENTOS, esc, money, ic } = PP;
      const $ = id => document.getElementById(id);
      const MAX = 15;
      const editId = PP.param('id'), fromId = PP.param('from');
      let anuncio = editId ? Store.anuncio(editId) : null;
      const cad = fromId ? Store.cadastro(fromId) : (anuncio && anuncio.cadastroId ? Store.cadastro(anuncio.cadastroId) : null);
      let fotos = [];

      $('segmento').insertAdjacentHTML('beforeend', PP.segOptions());
      Store.cidades(true).forEach(c =>
        $('cidade').insertAdjacentHTML('beforeend', '<option value="' + c.slug + '">' + esc(c.nome) + '/' + c.uf + '</option>'));

      const moneyIn = v => v ? 'R$ ' + Number(v).toLocaleString('pt-BR') : '';
      function fill(src) {
        $('segmento').value = src.segmento || ''; $('cidade').value = src.cidade || ''; $('bairro').value = src.bairro || '';
        $('descricao').value = src.descricao || ''; $('fundacao').value = src.fundacao || ''; $('funcionarios').value = src.funcionarios ?? '';
        $('area').value = src.area || ''; $('horario').value = src.horario || ''; $('cnpj').value = src.cnpj || 'Indiferente';
        $('marca').checked = !!src.marca; $('preco').value = moneyIn(src.preco); $('faturamento').value = moneyIn(src.faturamento);
        $('margem').value = src.margem || ''; $('folha').value = moneyIn(src.folha); $('aluguel').value = moneyIn(src.aluguel);
        $('estoque').value = moneyIn(src.estoque); $('destaque').checked = !!src.destaque;
        $('bens').value = moneyIn(src.bens); $('aceitaProposta').checked = !!src.aceitaProposta;
        fotos = (src.fotos || []).slice();
        if (!fotos.length && src.segmento && src.fotoTipo === 'ilustrativas') fotos = PP.segFotos(src.segmento);
      }

      if (anuncio) {
        fill(anuncio);
        $('ed-title').textContent = 'Editar anúncio ' + anuncio.codigo;
        $('ed-sub').innerHTML = PP.Admin.statusPill(anuncio.status);
        $('publish').innerHTML = ic('send') + (anuncio.status === 'publicado' ? 'Salvar e manter publicado' : 'Publicar no site');
      } else if (cad) {
        fill(cad);
        $('ed-title').textContent = 'Criar anúncio';
      }
      if (cad) {
        $('from-note').innerHTML = '<div class="note info" style="margin-bottom:20px">' + ic('link') + '<span>Dados puxados do cadastro <a href="/admin/cadastro?id=' + cad.id + '"><strong>' + esc(cad.protocolo) + '</strong></a> · ' + esc(cad.empresa.nome) + ' (' + esc(cad.responsavel.nome) + '). Confira e ajuste o texto antes de publicar. O nome da empresa não vai pro anúncio.</span></div>' +
          (!cad.cidade && cad.cidadeOutra ? '<div class="note warn" style="margin-bottom:20px">' + ic('map-pin') + '<span>A cidade <strong>' + esc(cad.cidadeOutra) + '</strong> ainda não está cadastrada. <a href="/admin/cidades">Cadastre aqui</a> e depois escolha ela no campo cidade.</span></div>' : '');
      }
      $('desc-count').textContent = $('descricao').value.length;
      PP.bindMasks();

      /* ---------- photos ---------- */
      function renderPhotos() {
        $('photos').innerHTML = fotos.map((f, i) =>
          '<div class="ed-photo' + (i === 0 ? ' cover' : '') + '"><img src="' + PP.photoUrl(f, 300) + '" alt="Foto ' + (i + 1) + '">' +
          (i === 0 ? '<span class="pt-cover">CAPA</span>' : '') +
          '<div class="ep-actions">' + (i ? '<button type="button" data-cover="' + i + '" title="Usar como capa" aria-label="Usar como capa">' + ic('star') + '</button>' : '') +
          '<button type="button" data-rm="' + i + '" title="Remover" aria-label="Remover">' + ic('x') + '</button></div></div>').join('') +
          (fotos.length < MAX ? '<label class="ed-add" for="file">' + ic('image-plus') + '<span>Adicionar</span></label>' : '');
        $('foto-count').textContent = '(' + fotos.length + ' de ' + MAX + ')';
        preview(); PP.icons();
      }
      $('photos').addEventListener('click', e => {
        const c = e.target.closest('[data-cover]'), r = e.target.closest('[data-rm]');
        if (c) { const i = Number(c.dataset.cover); fotos.unshift(fotos.splice(i, 1)[0]); renderPhotos(); }
        if (r) { fotos.splice(Number(r.dataset.rm), 1); renderPhotos(); }
      });
      $('file').addEventListener('change', async e => {
        const files = [...e.target.files].slice(0, MAX - fotos.length);
        e.target.value = '';
        for (const f of files) {
          try {
            const small = await PP.resizeImage(f, 1600, 0.82);
            fotos.push(await Store.uploadFoto(PP.dataUrlToBlob(small)));
            renderPhotos();
          } catch (err) { PP.toast(err.message || 'Não deu pra enviar a foto', 'error'); }
        }
      });
      $('use-stock').onclick = () => {
        if (!$('segmento').value) return PP.toast('Escolha o segmento primeiro', 'error');
        fotos = PP.segFotos($('segmento').value); renderPhotos();
      };
      $('clear-photos').onclick = () => { fotos = []; renderPhotos(); };

      /* ---------- live preview ---------- */
      function current() {
        return {
          segmento: $('segmento').value || 'outros', cidade: $('cidade').value, bairro: $('bairro').value.trim(),
          descricao: $('descricao').value.trim(), fundacao: Number($('fundacao').value) || new Date().getFullYear(),
          funcionarios: Number($('funcionarios').value) || 0, area: Number($('area').value) || 0, horario: $('horario').value.trim(),
          cnpj: $('cnpj').value, marca: $('marca').checked, preco: PP.unmoney($('preco').value), faturamento: PP.unmoney($('faturamento').value),
          margem: Number($('margem').value) || 0, folha: PP.unmoney($('folha').value), aluguel: PP.unmoney($('aluguel').value),
          estoque: PP.unmoney($('estoque').value), destaque: $('destaque').checked, fotos: fotos.slice(),
          bens: PP.unmoney($('bens').value), aceitaProposta: $('aceitaProposta').checked,
          fotoTipo: fotos.length && fotos.every(f => f.startsWith('u:')) ? 'ilustrativas' : 'reais'
        };
      }
      function preview() {
        const d = current();
        const fake = Object.assign({ id: anuncio ? anuncio.id : 0, codigo: anuncio ? anuncio.codigo : 'PP····', data: Date.now(), status: 'rascunho' }, d);
        if (!fake.cidade) fake.cidade = 'cidade';
        $('preview').innerHTML = PP.card(fake);
        const s = PP.seg(d.segmento), where = d.cidade ? PP.cidadeLabel(d.cidade) : 'Cidade';
        const slug = s.slug + '-em-' + (d.cidade || 'cidade') + '-' + (anuncio ? anuncio.codigo.toLowerCase() : 'pp0000');
        $('ss-url').textContent = location.host + ' › empresa-a-venda › ' + slug;
        $('ss-title').textContent = s.nome + ' à venda em ' + where + ' · ' + money(d.preco);
        $('ss-desc').textContent = s.nome + ' à venda em ' + (d.bairro ? d.bairro + ', ' : '') + where + ' por ' + money(d.preco) + '. Faturamento médio de ' + money(d.faturamento) + '/mês, ' + d.funcionarios + ' funcionários.';
        $('preview').querySelectorAll('a').forEach(a => a.addEventListener('click', e => e.preventDefault()));
        PP.icons();
      }
      $('form').addEventListener('input', e => { if (e.target.id === 'descricao') $('desc-count').textContent = e.target.value.length; preview(); });
      $('form').addEventListener('change', preview);

      /* ---------- save ---------- */
      const v = (id, ok) => { $(id).closest('.field').classList.toggle('invalid', !ok); return ok; };
      async function save(status, btn) {
        const d = current();
        const ok = [v('segmento', !!$('segmento').value), v('cidade', !!$('cidade').value), v('bairro', !!d.bairro),
          v('descricao', d.descricao.length >= 30), v('preco', d.preco > 0)].every(Boolean);
        if (!ok) { PP.toast('Confira os campos marcados', 'error'); document.querySelector('.field.invalid').scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
        if (!d.fotos.length) d.fotos = PP.segFotos(d.segmento);
        d.status = status;
        if (anuncio) d.id = anuncio.id;
        if (cad) d.cadastroId = cad.id;
        const saved = await PP.Admin.busy(btn, () => Store.saveAnuncio(d));
        if (!saved) return;
        PP.toast(status === 'publicado' ? 'Anúncio ' + saved.codigo + ' publicado no site' : 'Rascunho salvo');
        setTimeout(() => { location.href = '/admin/anuncios'; }, 900);
      }
      $('form').addEventListener('submit', e => { e.preventDefault(); save('publicado', $('publish')); });
      $('save-draft').onclick = () => save(anuncio && anuncio.status === 'pausado' ? 'pausado' : 'rascunho', $('save-draft'));

      renderPhotos();
    })();
  </script>
</body>
</html>
