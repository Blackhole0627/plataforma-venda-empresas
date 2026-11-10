<?php
require __DIR__ . '/inc/bootstrap.php';
$analista = null;
foreach (equipe() as $p) if (!empty($p['cadastros'])) $analista = $p;
page_head([
    'title' => 'Cadastrar empresa para venda · ' . cfg('marca'),
    'description' => 'Cadastre sua empresa para venda. Rápido e com sigilo: o nome do negócio não aparece no anúncio.',
    'canonical' => '/cadastro',
    'page' => 'cadastro',
]);
site_header('cadastro');
?>

  <main>
    <section class="page-head">
      <div class="container">
        <ol class="breadcrumb"><li><a href="/">Início</a></li><li><a href="/vender-empresa">Vender empresa</a></li><li>Cadastro</li></ol>
        <h1>Cadastre sua empresa</h1>
        <p>Preencha os dados abaixo. A nossa equipe analisa e entra em contato pra montar o anúncio. Leva uns 5 minutos.</p>
      </div>
    </section>

    <div class="container form-layout">
      <div class="form-card">
        <form id="form" novalidate>
          <ol class="stepper" id="stepper">
            <li class="current"><span>1. Seus dados</span></li>
            <li><span>2. A empresa</span></li>
            <li><span>3. Valores</span></li>
            <li><span>4. Fotos e envio</span></li>
          </ol>

          <!-- STEP 1 -->
          <div class="step-pane active" data-step="1">
            <h2>Seus dados</h2>
            <p class="muted">Só a nossa equipe vê essas informações.</p>
            <div class="field"><label for="nome">Nome completo</label>
              <div class="input-icon"><i data-lucide="user"></i><input class="input" id="nome" name="nome" autocomplete="name" placeholder="Seu nome"></div>
              <span class="error">Digite seu nome completo.</span></div>
            <div class="grid-2">
              <div class="field"><label for="email">E-mail</label>
                <div class="input-icon"><i data-lucide="mail"></i><input class="input" id="email" name="email" type="email" autocomplete="email" placeholder="voce@email.com"></div>
                <span class="error">Digite um e-mail válido.</span></div>
              <div class="field"><label for="whatsapp">WhatsApp</label>
                <div class="input-icon"><i data-lucide="phone"></i><input class="input" id="whatsapp" name="whatsapp" data-mask="phone" inputmode="tel" autocomplete="tel" placeholder="(11) 99999-9999"></div>
                <span class="error">Digite um WhatsApp com DDD.</span></div>
            </div>
          </div>

          <!-- STEP 2 -->
          <div class="step-pane" data-step="2">
            <h2>Sobre a empresa</h2>
            <p class="muted">O nome da empresa e o endereço não aparecem no anúncio. No site só vai o bairro e a cidade.</p>
            <div class="grid-2">
              <div class="field"><label for="empresa">Nome da empresa</label>
                <input class="input" id="empresa" name="empresa" placeholder="Nome fantasia">
                <span class="error">Digite o nome da empresa.</span></div>
              <div class="field"><label for="razaoSocial">Razão social</label>
                <input class="input" id="razaoSocial" name="razaoSocial" placeholder="Como está no CNPJ">
                <span class="error">Digite a razão social.</span></div>
            </div>
            <div class="grid-2">
              <div class="field"><label for="cnpj">CNPJ <small>(opcional)</small></label>
                <input class="input" id="cnpj" name="cnpj" data-mask="cnpj" inputmode="numeric" placeholder="00.000.000/0000-00">
                <span class="error">CNPJ incompleto.</span></div>
              <div class="field"><label for="segmento">Segmento</label>
                <select class="select" id="segmento" name="segmento"><option value="">Escolha</option></select>
                <span class="error">Escolha o segmento.</span></div>
            </div>

            <h3 class="form-sub">Endereço</h3>
            <div class="grid-2">
              <div class="field"><label for="cep">CEP</label>
                <input class="input" id="cep" name="cep" data-mask="cep" inputmode="numeric" autocomplete="postal-code" placeholder="00000-000">
                <span class="error">Digite o CEP.</span></div>
              <div class="field"><label for="cidade">Cidade</label>
                <select class="select" id="cidade" name="cidade"><option value="">Escolha</option></select>
                <span class="error">Escolha a cidade.</span></div>
            </div>
            <div class="field hidden" id="outra-wrap"><label for="cidadeOutra">Qual cidade?</label>
              <input class="input" id="cidadeOutra" name="cidadeOutra" placeholder="Cidade/UF">
              <span class="error">Digite a cidade.</span></div>
            <div class="grid-2">
              <div class="field"><label for="rua">Rua</label>
                <input class="input" id="rua" name="rua" autocomplete="address-line1" placeholder="Rua, avenida...">
                <span class="error">Digite a rua.</span></div>
              <div class="field"><label for="bairro">Bairro</label>
                <input class="input" id="bairro" name="bairro" placeholder="Bairro">
                <span class="error">Digite o bairro.</span></div>
            </div>
            <div class="grid-2">
              <div class="field"><label for="numero">Número</label>
                <input class="input" id="numero" name="numero" placeholder="123">
                <span class="error">Digite o número.</span></div>
              <div class="field"><label for="complemento">Complemento <small>(opcional)</small></label>
                <input class="input" id="complemento" name="complemento" autocomplete="address-line2" placeholder="Sala, loja, bloco..."></div>
            </div>

            <h3 class="form-sub">Estrutura</h3>
            <div class="grid-3">
              <div class="field"><label for="fundacao">Ano de fundação</label>
                <input class="input" id="fundacao" name="fundacao" type="number" inputmode="numeric" placeholder="2015">
                <span class="error">Ano inválido.</span></div>
              <div class="field"><label for="funcionarios">Funcionários</label>
                <input class="input" id="funcionarios" name="funcionarios" type="number" min="0" inputmode="numeric" placeholder="0">
                <span class="error">Informe o número.</span></div>
            </div>
            <div class="grid-2">
              <div class="field"><label for="area">Área (m²)</label>
                <input class="input" id="area" name="area" type="number" min="0" inputmode="numeric" placeholder="120"></div>
              <div class="field"><label for="horario">Horário de funcionamento</label>
                <input class="input" id="horario" name="horario" placeholder="Seg a sex das 8h às 18h"></div>
            </div>
            <div class="grid-2">
              <div class="field" id="imovel-field"><span class="label">O imóvel é</span>
                <div class="choice-group">
                  <label class="choice"><input type="radio" name="imovel" value="proprio"><span>Próprio</span></label>
                  <label class="choice"><input type="radio" name="imovel" value="alugado"><span>Alugado</span></label>
                </div>
                <span class="error">Escolha uma opção.</span></div>
              <div class="field"><span class="label">Tem energia solar?</span>
                <div class="choice-group">
                  <label class="choice"><input type="radio" name="energiaSolar" value="1"><span>Sim</span></label>
                  <label class="choice"><input type="radio" name="energiaSolar" value="0" checked><span>Não</span></label>
                </div></div>
            </div>
            <div class="grid-2">
              <div class="field"><label for="instagram">Instagram <small>(opcional)</small></label>
                <input class="input" id="instagram" name="instagram" placeholder="@suaempresa"></div>
              <div class="field"><label for="siteEmpresa">Site da empresa <small>(opcional)</small></label>
                <input class="input" id="siteEmpresa" name="siteEmpresa" inputmode="url" placeholder="www.suaempresa.com.br"></div>
            </div>
            <div class="field"><label for="descricao">Conte sobre o negócio</label>
              <textarea class="textarea" id="descricao" name="descricao" maxlength="1200" placeholder="O que a empresa faz, estrutura, clientes, diferenciais, o que vai junto na venda..."></textarea>
              <small><span id="desc-count">0</span>/1200 · mínimo de 30 caracteres</small>
              <span class="error">Escreva pelo menos 30 caracteres.</span></div>
          </div>

          <!-- STEP 3 -->
          <div class="step-pane" data-step="3">
            <h2>Valores e números</h2>
            <p class="muted">Esses números ajudam o comprador a decidir. Se não souber exato, coloque uma média.</p>
            <div class="grid-2">
              <div class="field"><label for="preco">Valor pedido</label>
                <input class="input" id="preco" name="preco" data-mask="money" inputmode="numeric" placeholder="R$ 0">
                <span class="error">Informe o valor pedido.</span></div>
              <div class="field"><label for="faturamento">Faturamento médio mensal</label>
                <input class="input" id="faturamento" name="faturamento" data-mask="money" inputmode="numeric" placeholder="R$ 0">
                <span class="error">Informe o faturamento.</span></div>
            </div>
            <div class="grid-3">
              <div class="field"><label for="margem">Margem de lucro (%)</label>
                <input class="input" id="margem" name="margem" type="number" min="0" max="100" inputmode="numeric" placeholder="20"></div>
              <div class="field"><label for="folha">Folha de pagamento</label>
                <input class="input" id="folha" name="folha" data-mask="money" inputmode="numeric" placeholder="R$ 0"></div>
              <div class="field"><label for="aluguel">Aluguel + condomínio</label>
                <input class="input" id="aluguel" name="aluguel" data-mask="money" inputmode="numeric" placeholder="R$ 0"></div>
            </div>
            <div class="grid-2">
              <div class="field"><label for="estoque">Valor do estoque</label>
                <input class="input" id="estoque" name="estoque" data-mask="money" inputmode="numeric" placeholder="R$ 0"></div>
              <div class="field"><label for="bens">Valor dos equipamentos, móveis e instalações</label>
                <input class="input" id="bens" name="bens" data-mask="money" inputmode="numeric" placeholder="R$ 0"></div>
            </div>
            <div class="grid-2">
              <div class="field"><span class="label">Aceita proposta?</span>
                <div class="choice-group">
                  <label class="choice"><input type="radio" name="aceitaProposta" value="1" checked><span>Sim</span></label>
                  <label class="choice"><input type="radio" name="aceitaProposta" value="0"><span>Não</span></label>
                </div></div>
              <div class="field"><span class="label">Venda com CNPJ?</span>
                <div class="choice-group">
                  <label class="choice"><input type="radio" name="cnpjVenda" value="Sim"><span>Sim</span></label>
                  <label class="choice"><input type="radio" name="cnpjVenda" value="Não"><span>Não</span></label>
                  <label class="choice"><input type="radio" name="cnpjVenda" value="Indiferente" checked><span>Tanto faz</span></label>
                </div></div>
            </div>
            <div class="field"><span class="label">A marca vai junto na venda?</span>
              <div class="choice-group">
                <label class="choice"><input type="radio" name="marca" value="1" checked><span>Sim</span></label>
                <label class="choice"><input type="radio" name="marca" value="0"><span>Não</span></label>
              </div></div>
            <div class="field"><label for="motivo">Motivo da venda <small>(só a equipe vê)</small></label>
              <textarea class="textarea" id="motivo" name="motivo" style="min-height:80px" placeholder="Ex.: mudança de cidade, aposentadoria, outro negócio..."></textarea></div>
          </div>

          <!-- STEP 4 -->
          <div class="step-pane" data-step="4">
            <h2>Fotos e envio</h2>
            <p class="muted">Escolha como quer as fotos no anúncio.</p>
            <div class="field">
              <div class="choice-group">
                <label class="choice"><input type="radio" name="fotoTipo" value="reais" checked><span><i data-lucide="camera" style="width:16px;height:16px"></i>Fotos reais</span></label>
                <label class="choice"><input type="radio" name="fotoTipo" value="ilustrativas"><span><i data-lucide="image" style="width:16px;height:16px"></i>Fotos ilustrativas</span></label>
              </div>
            </div>
            <div id="upload-area">
              <label class="dropzone" id="dropzone" for="file">
                <span class="dz-icon"><i data-lucide="image-plus"></i></span>
                <strong>Arraste as fotos aqui ou clique pra escolher</strong>
                <span class="muted">Até 15 fotos · JPG ou PNG · a primeira vira a capa</span>
              </label>
              <input type="file" id="file" accept="image/*" multiple class="sr-only">
              <div class="photo-grid" id="photo-grid"></div>
              <div class="photo-count"><span id="photo-count">0 de 15 fotos</span><span>Dica: fotos na horizontal e bem iluminadas</span></div>
              <div class="field" id="foto-field"><span class="error">Mande pelo menos 1 foto ou escolha fotos ilustrativas.</span></div>
            </div>
            <div class="note info hidden" id="ilus-note"><i data-lucide="info"></i><span>Beleza. A nossa equipe escolhe fotos do seu segmento pra montar o anúncio.</span></div>

            <h3 style="margin-top:26px">Confira seus dados</h3>
            <div class="review-list" id="review"></div>
            <div class="field" id="consent-field">
              <label class="check"><input type="checkbox" id="consent"><span>Autorizo a equipe a usar esses dados pra montar o anúncio e entrar em contato comigo. Li a <a href="#">política de privacidade</a>.</span></label>
              <span class="error">Marque a autorização pra continuar.</span>
            </div>
            <div class="field" id="comissao-field">
              <label class="check"><input type="checkbox" id="comissao"><span>Estou de acordo com a comissão da equipe se a empresa for vendida.</span></label>
              <span class="error">Marque o aceite da comissão pra continuar.</span>
            </div>
          </div>

          <input type="text" name="site" id="site" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true">
          <div class="form-actions" id="actions">
            <button type="button" class="btn btn-ghost" id="prev"><i data-lucide="arrow-left"></i> Voltar</button>
            <button type="button" class="btn btn-primary" id="next">Continuar <i data-lucide="arrow-right"></i></button>
          </div>
        </form>

        <div class="success hidden" id="success"></div>
      </div>

      <aside class="aside-card">
        <h3>Seus dados estão seguros</h3>
        <ul>
          <li><i data-lucide="lock"></i><span>O nome da empresa não aparece no anúncio.</span></li>
          <li><i data-lucide="user-check"></i><span>A equipe revisa tudo antes de publicar.</span></li>
          <li><i data-lucide="phone-call"></i><span>A gente te chama no WhatsApp em até 1 dia útil.</span></li>
          <li><i data-lucide="badge-check"></i><span>Cadastro sem compromisso.</span></li>
        </ul>
        <?php if ($analista): ?><div class="consultant"><?= avatar($analista) ?><div><strong><?= h($analista['nome']) ?></strong><span>Vai analisar seu cadastro</span></div></div><?php endif; ?>
      </aside>
    </div>
  </main>

  <?php site_footer(); page_scripts(); ?>
  <script>
    (function () {
      const { Store, SEGMENTOS, esc } = PP;
      const MAX_FOTOS = 15;
      const $ = id => document.getElementById(id);
      const form = $('form');
      let step = 1;
      const fotos = []; // dataURLs

      // selects
      SEGMENTOS.forEach(s => $('segmento').insertAdjacentHTML('beforeend', '<option value="' + s.slug + '">' + esc(s.nome) + '</option>'));
      Store.cidades(true).forEach(c =>
        $('cidade').insertAdjacentHTML('beforeend', '<option value="' + c.slug + '">' + esc(c.nome) + '/' + c.uf + '</option>'));
      $('cidade').insertAdjacentHTML('beforeend', '<option value="outra">Minha cidade não está na lista</option>');
      $('cidade').addEventListener('change', () => $('outra-wrap').classList.toggle('hidden', $('cidade').value !== 'outra'));


      $('descricao').addEventListener('input', () => { $('desc-count').textContent = $('descricao').value.length; });

      // CEP fills street, bairro and city when they are still empty
      let lastCep = '';
      $('cep').addEventListener('input', async () => {
        const d = $('cep').value.replace(/\D/g, '');
        if (d.length !== 8 || d === lastCep) return;
        lastCep = d;
        try {
          const r = await (await fetch('https://viacep.com.br/ws/' + d + '/json/')).json();
          if (r.erro) return;
          if (!val('rua') && r.logradouro) $('rua').value = r.logradouro;
          if (!val('bairro') && r.bairro) $('bairro').value = r.bairro;
          const c = Store.cidades(true).find(x => x.uf === r.uf && PP.slugify(x.nome) === PP.slugify(r.localidade || ''));
          if (c && !$('cidade').value) { $('cidade').value = c.slug; $('outra-wrap').classList.add('hidden'); }
          $('numero').focus();
        } catch (e) { /* offline or blocked, the person types it */ }
      });
      PP.bindMasks(form);

      /* ---------- validation ---------- */
      const v = (id, ok) => { const f = $(id).closest('.field'); f.classList.toggle('invalid', !ok); return ok; };
      const val = id => $(id).value.trim();
      const rules = {
        1: () => [
          v('nome', val('nome').split(/\s+/).length >= 2 && val('nome').length >= 5),
          v('email', /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val('email'))),
          v('whatsapp', val('whatsapp').replace(/\D/g, '').length >= 10)
        ],
        2: () => {
          const y = Number(val('fundacao'));
          return [
            v('empresa', val('empresa').length >= 2),
            v('razaoSocial', val('razaoSocial').length >= 2),
            v('cnpj', !val('cnpj') || val('cnpj').replace(/\D/g, '').length === 14),
            v('segmento', !!val('segmento')),
            v('cep', val('cep').replace(/\D/g, '').length === 8),
            v('cidade', !!val('cidade')),
            $('cidade').value !== 'outra' ? v('cidadeOutra', true) : v('cidadeOutra', val('cidadeOutra').length >= 3),
            v('rua', val('rua').length >= 3),
            v('numero', val('numero').length >= 1),
            v('bairro', val('bairro').length >= 2),
            (() => { const ok = !!form.imovel.value; $('imovel-field').classList.toggle('invalid', !ok); return ok; })(),
            v('fundacao', y >= 1900 && y <= new Date().getFullYear()),
            v('funcionarios', val('funcionarios') !== '' && Number(val('funcionarios')) >= 0),
            v('descricao', val('descricao').length >= 30)
          ];
        },
        3: () => [
          v('preco', PP.unmoney(val('preco')) > 0),
          v('faturamento', PP.unmoney(val('faturamento')) > 0)
        ],
        4: () => {
          const tipo = form.fotoTipo.value;
          $('foto-field').classList.toggle('invalid', tipo === 'reais' && !fotos.length);
          return [tipo === 'ilustrativas' || fotos.length > 0, v('consent', $('consent').checked), v('comissao', $('comissao').checked)];
        }
      };
      // live re-validate after first error
      form.addEventListener('input', e => {
        const f = e.target.closest('.field');
        if (f && f.classList.contains('invalid')) rules[step]();
      });

      /* ---------- steps ---------- */
      function go(n) {
        step = n;
        document.querySelectorAll('.step-pane').forEach(p => p.classList.toggle('active', Number(p.dataset.step) === n));
        document.querySelectorAll('#stepper li').forEach((li, i) => {
          li.classList.toggle('current', i + 1 === n); li.classList.toggle('done', i + 1 < n);
        });
        $('prev').style.visibility = n === 1 ? 'hidden' : 'visible';
        $('next').innerHTML = n === 4 ? PP.ic('send') + ' Enviar cadastro' : 'Continuar ' + PP.ic('arrow-right');
        if (n === 4) review();
        PP.icons();
        window.scrollTo({ top: form.closest('.form-card').offsetTop - 90, behavior: 'smooth' });
      }
      $('prev').addEventListener('click', () => go(Math.max(1, step - 1)));
      $('next').addEventListener('click', () => {
        const ok = rules[step]().every(Boolean);
        if (!ok) {
          const first = document.querySelector('.step-pane.active .field.invalid');
          if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
          PP.toast('Confira os campos marcados', 'error');
          return;
        }
        if (step < 4) go(step + 1); else submit();
      });
      go(1);

      /* ---------- photos ---------- */
      function renderPhotos() {
        $('photo-grid').innerHTML = fotos.map((f, i) =>
          '<div class="photo-thumb' + (f ? '' : ' loading') + '">' + (f ? '<img src="' + f + '" alt="Foto ' + (i + 1) + '">' : '') +
          (i === 0 && f ? '<span class="pt-cover">CAPA</span>' : '') +
          (f ? '<button type="button" class="pt-remove" data-i="' + i + '" aria-label="Remover foto">' + PP.ic('x') + '</button>' : '') + '</div>').join('');
        $('photo-count').textContent = fotos.filter(Boolean).length + ' de ' + MAX_FOTOS + ' fotos';
        PP.icons();
      }
      async function addFiles(list) {
        const files = [...list].filter(f => f.type.startsWith('image/'));
        const room = MAX_FOTOS - fotos.length;
        if (files.length > room) PP.toast('Máximo de ' + MAX_FOTOS + ' fotos. Algumas ficaram de fora.', 'error');
        for (const file of files.slice(0, room)) {
          const i = fotos.push(null) - 1; renderPhotos();
          try { fotos[i] = await PP.resizeImage(file, 1400, 0.78); } catch (e) { fotos.splice(i, 1); }
          renderPhotos();
        }
        $('foto-field').classList.remove('invalid');
      }
      $('file').addEventListener('change', e => { addFiles(e.target.files); e.target.value = ''; });
      const dz = $('dropzone');
      ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('drag'); }));
      ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('drag'); }));
      dz.addEventListener('drop', e => addFiles(e.dataTransfer.files));
      $('photo-grid').addEventListener('click', e => {
        const b = e.target.closest('.pt-remove'); if (!b) return;
        fotos.splice(Number(b.dataset.i), 1); renderPhotos();
      });
      form.addEventListener('change', e => {
        if (e.target.name !== 'fotoTipo') return;
        const ilus = form.fotoTipo.value === 'ilustrativas';
        $('upload-area').classList.toggle('hidden', ilus);
        $('ilus-note').classList.toggle('hidden', !ilus);
      });

      /* ---------- review + submit ---------- */
      function data() {
        const cid = $('cidade').value;
        return {
          responsavel: { nome: val('nome'), email: val('email'), whatsapp: val('whatsapp') },
          empresa: { nome: val('empresa'), cnpj: val('cnpj'), razaoSocial: val('razaoSocial'), instagram: val('instagram'), site: val('siteEmpresa') },
          endereco: { cep: val('cep'), rua: val('rua'), numero: val('numero'), complemento: val('complemento') },
          imovel: form.imovel.value, energiaSolar: form.energiaSolar.value === '1',
          bens: PP.unmoney(val('bens')), aceitaProposta: form.aceitaProposta.value === '1', aceiteComissao: $('comissao').checked,
          segmento: val('segmento'),
          cidade: cid === 'outra' ? '' : cid, cidadeOutra: cid === 'outra' ? val('cidadeOutra') : '',
          bairro: val('bairro'), fundacao: Number(val('fundacao')), funcionarios: Number(val('funcionarios')),
          area: Number(val('area')) || 0, horario: val('horario'), descricao: val('descricao'),
          preco: PP.unmoney(val('preco')), faturamento: PP.unmoney(val('faturamento')),
          margem: Number(val('margem')) || 0, folha: PP.unmoney(val('folha')), aluguel: PP.unmoney(val('aluguel')),
          estoque: PP.unmoney(val('estoque')), cnpj: form.cnpjVenda.value, marca: form.marca.value === '1',
          motivo: val('motivo'), fotoTipo: form.fotoTipo.value,
          fotos: form.fotoTipo.value === 'reais' ? fotos.filter(Boolean) : []
        };
      }
      function review() {
        const d = data();
        const row = (l, x) => '<div><span>' + l + '</span><strong>' + esc(x || '-') + '</strong></div>';
        $('review').innerHTML =
          row('Responsável', d.responsavel.nome) + row('WhatsApp', d.responsavel.whatsapp) +
          row('Empresa', d.empresa.nome) + row('Segmento', d.segmento ? PP.seg(d.segmento).nome : '') +
          row('Cidade', d.cidade ? PP.cidadeLabel(d.cidade) : d.cidadeOutra) + row('Bairro', d.bairro) +
          row('Endereço', d.endereco.rua + ', ' + d.endereco.numero + (d.endereco.complemento ? ' ' + d.endereco.complemento : '')) +
          row('Valor pedido', PP.money(d.preco)) + row('Faturamento/mês', PP.money(d.faturamento));
      }
      async function submit() {
        const btn = $('next');
        btn.disabled = true; $('prev').disabled = true;
        btn.innerHTML = PP.ic('loader') + ' Enviando...'; PP.icons();
        const d = data();
        const fd = new FormData();
        fd.append('dados', JSON.stringify(Object.assign({}, d, { fotos: undefined })));
        fd.append('site', $('site').value);
        d.fotos.forEach((f, i) => fd.append('fotos[]', PP.dataUrlToBlob(f), 'foto-' + (i + 1) + '.jpg'));
        let c;
        try {
          c = (await PP.api('/api/cadastro', fd)).cadastro;
        } catch (err) {
          PP.toast(err.message, 'error');
          btn.disabled = false; $('prev').disabled = false;
          btn.innerHTML = PP.ic('send') + ' Enviar cadastro'; PP.icons();
          return;
        }
        form.classList.add('hidden');
        $('success').classList.remove('hidden');
        $('success').innerHTML =
          '<div class="ok-icon">' + PP.ic('check') + '</div>' +
          '<h2>Cadastro enviado!</h2><p class="muted">Guarde o número do seu protocolo.</p>' +
          '<div class="protocol">' + esc(c.protocolo) + '</div>' +
          '<ol class="next-steps">' +
            '<li><span class="n">1</span><span>A nossa equipe recebe seu cadastro agora mesmo.</span></li>' +
            '<li><span class="n">2</span><span>Em até 1 dia útil a gente te chama no WhatsApp ' + esc(c.responsavel.whatsapp) + '.</span></li>' +
            '<li><span class="n">3</span><span>Com tudo certo, seu anúncio vai pro ar sem o nome da empresa.</span></li>' +
          '</ol>' +
          '<div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">' +
            '<a class="btn btn-primary" href="/comprar-empresa">Ver anúncios</a>' +
            '<a class="btn btn-outline" href="/">Voltar pro início</a>' +
          '</div>';
        PP.icons();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    })();
  </script>
</body>
</html>
