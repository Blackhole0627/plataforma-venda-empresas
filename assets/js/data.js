/* ==========================================================================
   Data layer (front-end prototype)
   Everything here will move to MySQL + PHP. For now it lives in
   localStorage so the full flow works in the browser:
   cadastro -> área interna -> anúncio publicado.
   ========================================================================== */

(function () {
  'use strict';

  const DB_KEY = 'pp_db_v1';
  const DAY = 86400000;

  /* ---------- helpers ---------- */
  function slugify(str) {
    return String(str || '')
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }

  function photoUrl(p, w) {
    if (!p) return '';
    const id = p.replace(/^u:/, '');
    if (id.startsWith('data:') || id.startsWith('http') || id.startsWith('/')) return id;
    return 'https://images.unsplash.com/photo-' + id + '?auto=format&fit=crop&w=' + (w || 800) + '&q=70';
  }

  /* ---------- capitais (27) ---------- */
  const CAPITAIS = [
    ['Rio Branco', 'AC'], ['Maceió', 'AL'], ['Macapá', 'AP'], ['Manaus', 'AM'],
    ['Salvador', 'BA'], ['Fortaleza', 'CE'], ['Brasília', 'DF'], ['Vitória', 'ES'],
    ['Goiânia', 'GO'], ['São Luís', 'MA'], ['Cuiabá', 'MT'], ['Campo Grande', 'MS'],
    ['Belo Horizonte', 'MG'], ['Belém', 'PA'], ['João Pessoa', 'PB'], ['Curitiba', 'PR'],
    ['Recife', 'PE'], ['Teresina', 'PI'], ['Rio de Janeiro', 'RJ'], ['Natal', 'RN'],
    ['Porto Alegre', 'RS'], ['Porto Velho', 'RO'], ['Boa Vista', 'RR'], ['Florianópolis', 'SC'],
    ['São Paulo', 'SP'], ['Aracaju', 'SE'], ['Palmas', 'TO']
  ].map(([nome, uf]) => ({
    slug: slugify(nome) + '-' + uf.toLowerCase(),
    nome, uf, capital: true, ativa: true
  }));

  const UFS = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA',
    'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

  /* ---------- segmentos ---------- */
  const SEGMENTOS = [
    { slug: 'academia', nome: 'Academia', icon: 'dumbbell',
      fotos: ['1534438327276-14e5300c3a48', '1540497077202-7c8a3999166f', '1517836357463-d25dfeac3438', '1571902943202-507ec2618e8f'] },
    { slug: 'restaurante', nome: 'Restaurante', icon: 'utensils',
      fotos: ['1517248135467-4c7edcad34c4', '1414235077428-338989a2e8c0', '1559339352-11d035aa65de'] },
    { slug: 'cafeteria', nome: 'Cafeteria', icon: 'coffee',
      fotos: ['/assets/img/site/cafeteria-padaria.jpg', '1501339847302-ac426a4a7cbb', '1554118811-1e0d58224f24', '1445116572660-236099ec97a0'] },
    { slug: 'pizzaria', nome: 'Pizzaria', icon: 'pizza',
      fotos: ['1513104890138-7c749659a591', '1414235077428-338989a2e8c0', '1517248135467-4c7edcad34c4'] },
    { slug: 'farmacia', nome: 'Farmácia', icon: 'pill',
      fotos: ['1576602976047-174e57a47881', '1587854692152-cbe660dbde88'] },
    { slug: 'pet-shop', nome: 'Pet shop', icon: 'paw-print',
      fotos: ['1548199973-03cce0bbc87b', '1583337130417-3346a1be7dee'] },
    { slug: 'salao-de-beleza', nome: 'Salão de beleza', icon: 'scissors',
      fotos: ['1560066984-138dadb4c035', '1522337360788-8b13dee7a37e'] },
    { slug: 'padaria', nome: 'Padaria', icon: 'croissant',
      fotos: ['/assets/img/site/cafeteria-padaria.jpg', '1509440159596-0249088772ff', '1555507036-ab1f4038808a'] },
    { slug: 'mercado', nome: 'Mercado', icon: 'shopping-cart',
      fotos: ['1604719312566-8912e9227c6a', '1542838132-92c53300491e'] },
    { slug: 'loja-de-roupas', nome: 'Loja de roupas', icon: 'shirt',
      fotos: ['1441986300917-64674bd600d8', '1567401893414-76b7b1e5a7a5'] },
    { slug: 'pousada', nome: 'Pousada e hotel', icon: 'bed-double',
      fotos: ['1566073771259-6a8506099945', '1582719478250-c89cae4dc85b'] },
    { slug: 'escola', nome: 'Escola e cursos', icon: 'graduation-cap',
      fotos: ['1580582932707-520aed937b7b'] },
    { slug: 'lavanderia', nome: 'Lavanderia', icon: 'washing-machine',
      fotos: ['1545173168-9f1947eebb7f'] },
    { slug: 'oficina', nome: 'Oficina mecânica', icon: 'wrench',
      fotos: ['1487754180451-c456f719a1fc'] },
    { slug: 'industria', nome: 'Indústria', icon: 'factory',
      fotos: ['1581091226825-a6a2a5aee158'] },
    { slug: 'outros', nome: 'Outros', icon: 'briefcase',
      fotos: ['1497366216548-37526070297c', '1497215728101-856f4ea42174'] }
  ];

  /* ---------- imagens institucionais ---------- */
  const IMAGENS = {
    heroHome: '/assets/img/site/banner-home.jpg',
    heroVender: '1556761175-5973dc0f32e7',    // reunião
    ctaVender: '1600880292203-757bb62b4baf',  // comemoração
    login: '1497215728101-856f4ea42174',      // escritório
    contato: '1497366216548-37526070297c'
  };

  const EQUIPE = [
    { nome: 'Mariana Costa', cargo: 'Consultora · Sul e Sudeste', foto: '1573496359142-b8d87734a5a2' },
    { nome: 'Rafael Mendes', cargo: 'Consultor · Nordeste', foto: '1507003211169-0a1dd7228f2d' },
    { nome: 'Juliana Alves', cargo: 'Consultora · Centro-Oeste e Norte', foto: '1494790108377-be9c29b29330' },
    { nome: 'Carlos Ribeiro', cargo: 'Análise de cadastros', foto: '1560250097-0b93528c311a' }
  ];

  /* ---------- anúncios de exemplo ---------- */
  // [seg, cidade, bairro, preco, faturamento, funcionarios, fundacao, area, aluguel, margem, folha, estoque, cnpj, marca, destaque, diasAtras, horario, descricao]
  const SEED_ANUNCIOS = [
    ['academia', 'curitiba-pr', 'Batel', 850000, 95000, 8, 2018, 520, 18000, 22, 21000, 0, 'Indiferente', true, true, 1,
      'Seg a sex das 6h às 23h, sáb das 8h às 14h',
      'Academia completa com cerca de 650 alunos ativos, área de musculação, sala de bike e sala de lutas. Equipamentos de linha profissional, todos inclusos na venda. Ponto com grande fluxo e estacionamento conveniado ao lado.'],
    ['restaurante', 'sao-paulo-sp', 'Pinheiros', 1200000, 180000, 14, 2015, 280, 22000, 18, 42000, 35000, 'Sim', true, true, 2,
      'Ter a dom, almoço e jantar',
      'Restaurante de cozinha contemporânea com 90 lugares, cozinha industrial completa e delivery ativo nos principais aplicativos. Equipe treinada que pode continuar com o novo dono. Clientela fiel e boas avaliações.'],
    ['cafeteria', 'florianopolis-sc', 'Centro', 320000, 48000, 4, 2019, 90, 6500, 20, 9500, 12000, 'Indiferente', true, false, 3,
      'Seg a sáb das 7h às 19h',
      'Cafeteria charmosa perto de escritórios e faculdades. Máquina de espresso profissional, vitrine refrigerada e mobiliário novo. Movimento forte no café da manhã e no fim da tarde.'],
    ['pizzaria', 'porto-alegre-rs', 'Moinhos de Vento', 450000, 70000, 7, 2016, 160, 9800, 17, 16000, 15000, 'Sim', true, true, 4,
      'Todos os dias das 18h às 23h30',
      'Pizzaria com forno a lenha, salão para 50 pessoas e delivery próprio com 3 motoboys. Cardápio consolidado e receitas repassadas ao comprador. Ótima reputação no bairro.'],
    ['farmacia', 'belo-horizonte-mg', 'Savassi', 980000, 210000, 9, 2012, 140, 12000, 12, 28000, 380000, 'Sim', true, false, 5,
      'Seg a sáb das 7h às 22h, dom das 8h às 18h',
      'Farmácia com manipulação e perfumaria, ponto de esquina com alto fluxo. Estoque alto incluso, convênios ativos com empresas da região e sistema de gestão completo.'],
    ['pet-shop', 'rio-de-janeiro-rj', 'Barra da Tijuca', 380000, 65000, 6, 2020, 180, 11000, 19, 14000, 40000, 'Indiferente', true, true, 6,
      'Seg a sáb das 8h às 19h',
      'Pet shop com banho e tosa, loja de rações e acessórios e consultório veterinário. Carteira com mais de 1.200 clientes cadastrados e serviço de leva e traz.'],
    ['salao-de-beleza', 'salvador-ba', 'Pituba', 210000, 42000, 6, 2017, 110, 5500, 25, 9000, 8000, 'Não', false, false, 7,
      'Ter a sáb das 9h às 20h',
      'Salão com 6 estações de corte, 3 lavatórios, sala de estética e manicure. Ambiente reformado recentemente e agenda cheia nos fins de semana.'],
    ['padaria', 'goiania-go', 'Setor Bueno', 690000, 130000, 12, 2010, 250, 9000, 15, 30000, 25000, 'Sim', true, false, 8,
      'Todos os dias das 6h às 21h',
      'Padaria e confeitaria com produção própria, forno turbo, câmara fria e área de café com 30 lugares. Encomendas para festas e empresas garantem receita extra.'],
    ['mercado', 'recife-pe', 'Boa Viagem', 1500000, 320000, 18, 2008, 600, 20000, 9, 52000, 420000, 'Sim', true, true, 9,
      'Seg a sáb das 7h às 22h, dom das 7h às 14h',
      'Mercado de bairro com açougue, hortifruti e padaria. 4 caixas, câmaras frias e estacionamento próprio. Clientela fixa há mais de 15 anos.'],
    ['loja-de-roupas', 'fortaleza-ce', 'Aldeota', 260000, 55000, 4, 2019, 85, 7500, 23, 9000, 120000, 'Indiferente', false, false, 10,
      'Seg a sáb das 9h às 19h',
      'Loja de moda feminina com marca própria, Instagram com 40 mil seguidores e vendas online ativas. Estoque da coleção atual incluso.'],
    ['pousada', 'florianopolis-sc', 'Jurerê', 2400000, 160000, 10, 2014, 1200, 15000, 28, 26000, 0, 'Sim', true, true, 11,
      'Funcionamento o ano todo',
      'Pousada com 18 suítes, piscina, café da manhã e a 200 metros da praia. Alta ocupação no verão e bom movimento o ano todo com eventos e turismo de negócios.'],
    ['escola', 'brasilia-df', 'Asa Sul', 720000, 110000, 15, 2013, 400, 16000, 21, 38000, 0, 'Sim', true, false, 12,
      'Seg a sex das 7h às 22h, sáb das 8h às 13h',
      'Escola de idiomas com 9 salas equipadas, cerca de 380 alunos matriculados e material didático próprio. Equipe de professores fixa.'],
    ['lavanderia', 'curitiba-pr', 'Água Verde', 180000, 32000, 3, 2021, 70, 4200, 26, 6000, 3000, 'Indiferente', false, false, 13,
      'Seg a sáb das 8h às 20h',
      'Lavanderia com máquinas industriais novas, contratos com 2 hotéis e serviço de coleta e entrega. Operação simples e enxuta.'],
    ['oficina', 'campo-grande-ms', 'Centro', 290000, 60000, 5, 2011, 300, 6000, 24, 12000, 30000, 'Indiferente', false, false, 14,
      'Seg a sex das 8h às 18h, sáb das 8h às 12h',
      'Oficina mecânica com 4 elevadores, scanner automotivo e ferramental completo. Parcerias com seguradoras e frotas de empresas.'],
    ['industria', 'manaus-am', 'Distrito Industrial', 3200000, 450000, 32, 2005, 2000, 25000, 14, 98000, 600000, 'Sim', true, true, 15,
      'Seg a sex, 2 turnos',
      'Indústria de embalagens plásticas com maquinário revisado, licenças em dia e carteira de clientes recorrentes no polo industrial. Venda com estrutura e equipe técnica.'],
    ['academia', 'sao-paulo-sp', 'Moema', 1100000, 140000, 11, 2016, 700, 26000, 20, 30000, 0, 'Sim', true, false, 16,
      'Seg a sex das 5h30 às 23h, sáb e dom das 8h às 16h',
      'Academia de alto padrão com piscina, área funcional e cerca de 900 alunos. Ar condicionado em todos os ambientes e equipamentos importados.'],
    ['cafeteria', 'rio-de-janeiro-rj', 'Botafogo', 240000, 38000, 3, 2021, 60, 7000, 18, 7500, 6000, 'Indiferente', false, false, 18,
      'Seg a dom das 8h às 20h',
      'Café com produção própria de doces e salgados, ponto em rua movimentada e delivery ativo. Ideal para quem quer começar com operação pronta.'],
    ['restaurante', 'belem-pa', 'Umarizal', 560000, 90000, 9, 2014, 200, 8000, 19, 20000, 18000, 'Sim', true, false, 20,
      'Seg a sáb, almoço e jantar',
      'Restaurante de comida regional com 70 lugares, cozinha completa e forte movimento no almoço executivo. Receitas e fornecedores repassados.'],
    ['pizzaria', 'joao-pessoa-pb', 'Manaíra', 260000, 45000, 5, 2018, 120, 4500, 21, 10000, 6000, 'Indiferente', true, false, 22,
      'Ter a dom das 18h às 23h',
      'Pizzaria com forte delivery, forno elétrico de esteira e salão pequeno. Marca conhecida no bairro e cardápio enxuto.'],
    ['farmacia', 'natal-rn', 'Ponta Negra', 520000, 120000, 6, 2015, 110, 8500, 13, 15000, 210000, 'Sim', true, false, 25,
      'Todos os dias das 7h às 23h',
      'Farmácia em região turística com grande fluxo o ano todo. Estoque incluso, entrega em domicílio e convênios ativos.']
  ];

  function segFotos(seg) {
    const s = SEGMENTOS.find(x => x.slug === seg);
    return (s ? s.fotos : SEGMENTOS[SEGMENTOS.length - 1].fotos).map(id => 'u:' + id);
  }

  function buildSeed() {
    const now = Date.now();
    const anuncios = SEED_ANUNCIOS.map((r, i) => {
      const codigo = 'PP' + (1101 + i);
      return {
        id: i + 1, codigo, status: 'publicado',
        segmento: r[0], cidade: r[1], bairro: r[2], preco: r[3], faturamento: r[4],
        funcionarios: r[5], fundacao: r[6], area: r[7], aluguel: r[8], margem: r[9],
        folha: r[10], estoque: r[11], cnpj: r[12], marca: r[13], destaque: r[14],
        data: now - r[15] * DAY, horario: r[16], descricao: r[17],
        fotos: segFotos(r[0]), fotoTipo: 'ilustrativas', cadastroId: null
      };
    });

    const cadastros = [
      { id: 1, protocolo: 'CAD-2611-0041', status: 'novo', data: now - 2 * 3600000,
        responsavel: { nome: 'Fernanda Lima', email: 'fernanda.lima@email.com', whatsapp: '(41) 99812-3344' },
        empresa: { nome: 'Studio Fit Bem Estar', cnpj: '32.415.778/0001-09' },
        segmento: 'academia', cidade: 'curitiba-pr', bairro: 'Cabral', fundacao: 2019, funcionarios: 5, area: 320,
        horario: 'Seg a sex das 6h às 22h', preco: 420000, faturamento: 52000, margem: 20, folha: 12000, aluguel: 9000, estoque: 0,
        cnpj: 'Indiferente', marca: true, motivo: 'Mudança de cidade por motivo familiar.',
        descricao: 'Academia de bairro com 380 alunos, musculação e aulas coletivas. Equipamentos com 3 anos de uso.',
        fotoTipo: 'reais', fotos: segFotos('academia').slice(0, 3) },
      { id: 2, protocolo: 'CAD-2611-0040', status: 'novo', data: now - 5 * 3600000,
        responsavel: { nome: 'Marcos Antônio Souza', email: 'marcos.souza@email.com', whatsapp: '(71) 98877-1020' },
        empresa: { nome: 'Sabor da Bahia Restaurante', cnpj: '18.220.334/0001-55' },
        segmento: 'restaurante', cidade: 'salvador-ba', bairro: 'Rio Vermelho', fundacao: 2013, funcionarios: 11, area: 230,
        horario: 'Ter a dom, almoço e jantar', preco: 780000, faturamento: 125000, margem: 17, folha: 26000, aluguel: 12000, estoque: 20000,
        cnpj: 'Sim', marca: true, motivo: 'Sócios vão se dedicar a outro negócio.',
        descricao: 'Restaurante de frutos do mar com vista para o mar, 80 lugares e varanda.',
        fotoTipo: 'reais', fotos: segFotos('restaurante') },
      { id: 3, protocolo: 'CAD-2611-0039', status: 'novo', data: now - 1 * DAY,
        responsavel: { nome: 'Patrícia Gomes', email: 'patricia.g@email.com', whatsapp: '(31) 99700-4512' },
        empresa: { nome: 'Pet Amigo BH', cnpj: '' },
        segmento: 'pet-shop', cidade: 'belo-horizonte-mg', bairro: 'Funcionários', fundacao: 2020, funcionarios: 4, area: 120,
        horario: 'Seg a sáb das 8h às 18h', preco: 230000, faturamento: 38000, margem: 22, folha: 8000, aluguel: 6000, estoque: 25000,
        cnpj: 'Indiferente', marca: false, motivo: 'Vou abrir outro negócio em outro estado.',
        descricao: 'Pet shop com banho e tosa e loja. Clientela fiel no bairro.',
        fotoTipo: 'ilustrativas', fotos: [] },
      { id: 4, protocolo: 'CAD-2610-0037', status: 'analise', data: now - 3 * DAY,
        responsavel: { nome: 'Roberto Nunes', email: 'roberto.nunes@email.com', whatsapp: '(85) 98111-2233' },
        empresa: { nome: 'Panificadora Pão Nosso', cnpj: '07.991.220/0001-71' },
        segmento: 'padaria', cidade: 'fortaleza-ce', bairro: 'Meireles', fundacao: 2009, funcionarios: 13, area: 280,
        horario: 'Todos os dias das 6h às 21h', preco: 650000, faturamento: 140000, margem: 14, folha: 32000, aluguel: 10000, estoque: 18000,
        cnpj: 'Sim', marca: true, motivo: 'Aposentadoria.',
        descricao: 'Padaria tradicional com confeitaria e lanchonete.',
        fotoTipo: 'reais', fotos: segFotos('padaria') },
      { id: 5, protocolo: 'CAD-2610-0031', status: 'publicado', data: now - 4 * DAY, anuncioId: 1,
        responsavel: { nome: 'André Martins', email: 'andre.martins@email.com', whatsapp: '(41) 99654-7788' },
        empresa: { nome: 'Iron Gym Batel', cnpj: '25.664.101/0001-30' },
        segmento: 'academia', cidade: 'curitiba-pr', bairro: 'Batel', fundacao: 2018, funcionarios: 8, area: 520,
        horario: 'Seg a sex das 6h às 23h, sáb das 8h às 14h', preco: 850000, faturamento: 95000, margem: 22, folha: 21000, aluguel: 18000, estoque: 0,
        cnpj: 'Indiferente', marca: true, motivo: 'Foco em outra unidade.',
        descricao: 'Academia completa com musculação, bike e lutas.',
        fotoTipo: 'reais', fotos: segFotos('academia') },
      { id: 6, protocolo: 'CAD-2610-0028', status: 'recusado', data: now - 6 * DAY,
        responsavel: { nome: 'Lucas Pereira', email: 'lucas.p@email.com', whatsapp: '(11) 97000-1122' },
        empresa: { nome: 'LP Importados', cnpj: '' },
        segmento: 'outros', cidade: 'sao-paulo-sp', bairro: 'Brás', fundacao: 2023, funcionarios: 1, area: 20,
        horario: '', preco: 50000, faturamento: 0, margem: 0, folha: 0, aluguel: 0, estoque: 0,
        cnpj: 'Não', marca: false, motivo: '',
        descricao: 'Dados incompletos.', fotoTipo: 'ilustrativas', fotos: [] }
    ];

    return {
      version: 1,
      cidades: CAPITAIS.map(c => Object.assign({}, c)),
      anuncios, cadastros,
      seq: { anuncio: anuncios.length + 1, cadastro: 7, codigo: 1101 + anuncios.length, protocolo: 42 }
    };
  }

  /* ---------- store ---------- */
  let db = null;
  function load() {
    if (db) return db;
    try {
      const raw = localStorage.getItem(DB_KEY);
      if (raw) { db = JSON.parse(raw); return db; }
    } catch (e) { /* storage blocked */ }
    db = buildSeed();
    persist();
    return db;
  }
  function persist() {
    try { localStorage.setItem(DB_KEY, JSON.stringify(db)); return true; }
    catch (e) { return false; }
  }
  // Browser storage is small (~5 MB). If it fills up, quietly drop uploaded
  // photos from the oldest records first, then trim the current one.
  const isUpload = f => typeof f === 'string' && f.indexOf('data:') === 0;
  function persistFree(keep) {
    if (persist()) return true;
    const old = db.cadastros.concat(db.anuncios)
      .filter(r => r !== keep && (r.fotos || []).some(isUpload))
      .sort((a, b) => a.data - b.data);
    for (const r of old) {
      r.fotos = r.fotos.filter(f => !isUpload(f));
      if (!r.fotos.length && r.codigo) r.fotos = segFotos(r.segmento);
      if (persist()) return true;
    }
    while (keep && keep.fotos && keep.fotos.length > 1) {
      keep.fotos.pop();
      if (persist()) return true;
    }
    return persist();
  }

  const Store = {
    reset() { try { localStorage.removeItem(DB_KEY); } catch (e) {} db = null; return load(); },

    /* cidades */
    cidades(onlyActive) { const l = load().cidades; return onlyActive ? l.filter(c => c.ativa) : l; },
    cidade(slug) { return load().cidades.find(c => c.slug === slug); },
    addCidade(nome, uf) {
      const slug = slugify(nome) + '-' + uf.toLowerCase();
      if (this.cidade(slug)) return { error: 'Essa cidade já está cadastrada.' };
      const c = { slug, nome: nome.trim(), uf, capital: false, ativa: true };
      load().cidades.push(c); persist(); return c;
    },
    toggleCidade(slug) { const c = this.cidade(slug); if (c) { c.ativa = !c.ativa; persist(); } return c; },
    removeCidade(slug) {
      const d = load(); d.cidades = d.cidades.filter(c => c.slug !== slug || c.capital); persist();
    },

    /* anúncios */
    anuncios(opts) {
      opts = opts || {};
      let l = load().anuncios.slice();
      if (!opts.all) l = l.filter(a => a.status === 'publicado');
      return l.sort((a, b) => b.data - a.data);
    },
    anuncio(id) { return load().anuncios.find(a => a.id === Number(id)); },
    anuncioByCodigo(codigo) { return load().anuncios.find(a => a.codigo.toLowerCase() === String(codigo).toLowerCase()); },
    saveAnuncio(a) {
      const d = load();
      if (a.id) {
        const i = d.anuncios.findIndex(x => x.id === a.id);
        a = d.anuncios[i] = Object.assign(d.anuncios[i], a);
      } else {
        a.id = d.seq.anuncio++;
        a.codigo = 'PP' + (d.seq.codigo++);
        a.data = Date.now();
        d.anuncios.push(a);
      }
      return persistFree(a) ? a : { error: 'Não deu pra salvar. Tenta de novo.' };
    },
    removeAnuncio(id) { const d = load(); d.anuncios = d.anuncios.filter(a => a.id !== Number(id)); persist(); },

    /* cadastros */
    cadastros() { return load().cadastros.slice().sort((a, b) => b.data - a.data); },
    cadastro(id) { return load().cadastros.find(c => c.id === Number(id)); },
    addCadastro(c) {
      const d = load();
      c.id = d.seq.cadastro++;
      const dt = new Date();
      c.protocolo = 'CAD-' + String(dt.getFullYear()).slice(2) + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(d.seq.protocolo++).padStart(4, '0');
      c.status = 'novo';
      c.data = Date.now();
      d.cadastros.push(c);
      persistFree(c);
      return c;
    },
    setCadastroStatus(id, status, extra) {
      const c = this.cadastro(id);
      if (c) { c.status = status; Object.assign(c, extra || {}); persist(); }
      return c;
    }
  };

  window.PP = Object.assign(window.PP || {}, {
    slugify, photoUrl, SEGMENTOS, UFS, IMAGENS, EQUIPE, Store, segFotos
  });
})();
