<?php
/* Fixed lists: segments, states, capitals and site images. */

/*
 * Segments in the order of Tiago's list (Nov 11). 'chip' = shows in the home chips (20, Outros last).
 * 'grupo' = heading in the full list and in the selects. 'outros' must stay LAST: seg() falls back to it.
 */
const SEGMENTOS = [
    ['slug' => 'restaurante', 'nome' => 'Restaurante', 'icon' => 'utensils', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1517248135467-4c7edcad34c4', '1414235077428-338989a2e8c0', '1559339352-11d035aa65de']],
    ['slug' => 'lanchonete', 'nome' => 'Lanchonete / Hamburgueria', 'icon' => 'sandwich', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1550547660-d9450f859349', '1776829399747-9c77bdc60ab7', '1758578484459-677bfbbd00f7']],
    ['slug' => 'pizzaria', 'nome' => 'Pizzaria', 'icon' => 'pizza', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1513104890138-7c749659a591', '1414235077428-338989a2e8c0', '1517248135467-4c7edcad34c4']],
    ['slug' => 'padaria', 'nome' => 'Padaria e confeitaria', 'icon' => 'croissant', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['/assets/img/site/cafeteria-padaria.jpg', '1509440159596-0249088772ff', '1555507036-ab1f4038808a']],
    ['slug' => 'cafeteria', 'nome' => 'Cafeteria', 'icon' => 'coffee', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['/assets/img/site/cafeteria-padaria.jpg', '1501339847302-ac426a4a7cbb', '1554118811-1e0d58224f24', '1445116572660-236099ec97a0']],
    ['slug' => 'bar', 'nome' => 'Bar e pub', 'icon' => 'beer', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1779372178480-28385582bf53', '1675419092955-3dfff1351e01']],
    ['slug' => 'casa-de-festas', 'nome' => 'Casa de festas', 'icon' => 'party-popper', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1755704282977-340323fa52df', '1768776183877-e8f3dfc91f40', '1605644656444-ff452c73e2fe']],
    ['slug' => 'mercado', 'nome' => 'Mercado e supermercado', 'icon' => 'shopping-cart', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1604719312566-8912e9227c6a', '1542838132-92c53300491e']],
    ['slug' => 'clinica', 'nome' => 'Clínica', 'desc' => 'médica, odontológica, estética', 'icon' => 'stethoscope', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1519494026892-80bbd2d6fd0d', '1629909614456-6b1c5c94cecc', '1581982231900-6a1a46b744c9']],
    ['slug' => 'farmacia', 'nome' => 'Farmácia', 'icon' => 'pill', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1576602976047-174e57a47881', '1587854692152-cbe660dbde88']],
    ['slug' => 'pet-shop', 'nome' => 'Pet shop', 'icon' => 'paw-print', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1548199973-03cce0bbc87b', '1583337130417-3346a1be7dee']],
    ['slug' => 'salao-de-beleza', 'nome' => 'Salão e barbearia', 'icon' => 'scissors', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1560066984-138dadb4c035', '1522337360788-8b13dee7a37e', '1781455793310-8427c96454c7']],
    ['slug' => 'loja-de-roupas', 'nome' => 'Loja de roupas', 'icon' => 'shirt', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1441986300917-64674bd600d8', '1567401893414-76b7b1e5a7a5']],
    ['slug' => 'hospedagem', 'nome' => 'Hospedagem', 'desc' => 'hotel, pousada, hostel, motel', 'icon' => 'bed-double', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1566073771259-6a8506099945', '1582719478250-c89cae4dc85b', '1781572725438-ac8c41476752', '1782890790046-2aefb0b503cb']],
    ['slug' => 'escola', 'nome' => 'Escola e cursos', 'desc' => 'inclui educação infantil e idiomas', 'icon' => 'graduation-cap', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1580582932707-520aed937b7b']],
    ['slug' => 'lavanderia', 'nome' => 'Lavanderia', 'icon' => 'washing-machine', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1545173168-9f1947eebb7f']],
    ['slug' => 'automotivo', 'nome' => 'Automotivo', 'desc' => 'auto center, oficina, lava-jato, revenda de veículos', 'icon' => 'car', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1487754180451-c456f719a1fc', '1615906655593-ad0386982a0f', '1767681092416-bccf9410bda4']],
    ['slug' => 'industria', 'nome' => 'Indústria', 'icon' => 'factory', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1581091226825-a6a2a5aee158']],
    ['slug' => 'tecnologia', 'nome' => 'E-commerce e tecnologia', 'icon' => 'laptop', 'grupo' => 'Mais procurados', 'chip' => true,
     'fotos' => ['1580674285054-bed31e145f59', '1449247666642-264389f5f5b1', '1606857521015-7f9fcf423740']],
    ['slug' => 'acaiteria', 'nome' => 'Açaiteria / Sorveteria', 'icon' => 'ice-cream-cone', 'grupo' => 'Alimentação', 'chip' => false,
     'fotos' => ['1567206563064-6f60f40a2b57', '1560801619-01d71da0f70c']],
    ['slug' => 'acougue', 'nome' => 'Açougue / Casa de carnes', 'icon' => 'beef', 'grupo' => 'Alimentação', 'chip' => false,
     'fotos' => ['1606677661991-446cea8ee182', '1625643269470-5d3e7b69fa34', '1767491654834-13bf58ed9522']],
    ['slug' => 'emporio', 'nome' => 'Empório / Adega', 'icon' => 'wine', 'grupo' => 'Alimentação', 'chip' => false,
     'fotos' => ['1767465837851-d438c6ea474b', '1773196729399-a0914f1582e5', '1516594915697-87eb3b1c14ea']],
    ['slug' => 'conveniencia', 'nome' => 'Loja de conveniência', 'icon' => 'store', 'grupo' => 'Alimentação', 'chip' => false,
     'fotos' => ['1604719312566-8912e9227c6a', '1759395061464-5d4ad4f864fa', '1769485016826-a7d5bfe50119']],
    ['slug' => 'distribuidora', 'nome' => 'Distribuidora de alimentos', 'icon' => 'truck', 'grupo' => 'Alimentação', 'chip' => false,
     'fotos' => ['1761472651462-c2a019e76f4b', '1651525669944-00de65d3b8a5']],
    ['slug' => 'fabrica-de-alimentos', 'nome' => 'Fábrica de alimentos', 'desc' => 'inclui beneficiamento de carnes', 'icon' => 'factory', 'grupo' => 'Alimentação', 'chip' => false,
     'fotos' => ['1779517935494-2fa87fd7178c', '1779517226178-cedb23334a2a', '1652211955967-99c892925469']],
    ['slug' => 'delivery', 'nome' => 'Delivery / Dark kitchen', 'icon' => 'bike', 'grupo' => 'Alimentação', 'chip' => false,
     'fotos' => ['1589109807644-924edf14ee09', '1771574207826-02f0d845e3a7', '1771360963016-1408c2de12c4']],
    ['slug' => 'mercado-autonomo', 'nome' => 'Mercado autônomo', 'icon' => 'scan-barcode', 'grupo' => 'Alimentação', 'chip' => false,
     'fotos' => ['1760273464017-4bb7dfa42d91', '1773713612984-fcc59de6fffd', '1689770767185-a9950b3b24da']],
    ['slug' => 'academia', 'nome' => 'Academia', 'icon' => 'dumbbell', 'grupo' => 'Saúde e beleza', 'chip' => false,
     'fotos' => ['1534438327276-14e5300c3a48', '1540497077202-7c8a3999166f', '1517836357463-d25dfeac3438', '1571902943202-507ec2618e8f']],
    ['slug' => 'veterinaria', 'nome' => 'Clínica veterinária', 'icon' => 'heart-pulse', 'grupo' => 'Saúde e beleza', 'chip' => false,
     'fotos' => ['1770836037275-38b44e4b101f', '1770836037289-e00e5f351d11', '1551076805-e1869033e561']],
    ['slug' => 'otica', 'nome' => 'Óptica', 'icon' => 'glasses', 'grupo' => 'Saúde e beleza', 'chip' => false,
     'fotos' => ['1556306510-31ca015374b0', '1615468822882-4828d2602857', '1534078477103-9f6a18b3a5e2']],
    ['slug' => 'suplementos', 'nome' => 'Fábrica de suplementos', 'icon' => 'flask-conical', 'grupo' => 'Saúde e beleza', 'chip' => false,
     'fotos' => ['1631549916768-4119b2e5f926', '1763747958224-7726941b0b15', '1664956618021-73c47736845e']],
    ['slug' => 'cosmeticos', 'nome' => 'Cosméticos e perfumaria', 'desc' => 'inclui quiosque de maquiagem', 'icon' => 'sparkles', 'grupo' => 'Saúde e beleza', 'chip' => false,
     'fotos' => ['1567585902972-53bd0b897bf3', '1691187861257-a56c4aa2d7fb']],
    ['slug' => 'pilates', 'nome' => 'Studio de pilates / Fisioterapia', 'icon' => 'activity', 'grupo' => 'Saúde e beleza', 'chip' => false,
     'fotos' => ['1783841193384-4269c68aa050', '1771270786606-f5a0e57db762', '1579454566790-f9e5697ddf36']],
    ['slug' => 'material-de-construcao', 'nome' => 'Ferragem / Material de construção', 'icon' => 'hammer', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1765744893064-dce3184289ef', '1631856954655-966f97d809de', '1774273177331-4c19e9912071']],
    ['slug' => 'papelaria', 'nome' => 'Papelaria / Livraria', 'icon' => 'book-open', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1778585914908-c968c0a7dacd', '1774070231417-4bf4d2025878', '1457369804613-52c61a468e7d']],
    ['slug' => 'floricultura', 'nome' => 'Floricultura', 'icon' => 'flower', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1775743093104-d8e53e5ed250', '1762424361024-766d1a44f54e', '1589244159943-460088ed5c92']],
    ['slug' => 'celulares', 'nome' => 'Celulares e acessórios', 'icon' => 'smartphone', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1768137348738-ce11911f54f6', '1758467700917-3517eb11ec8c', '1556742111-a301076d9d18']],
    ['slug' => 'moveis', 'nome' => 'Móveis e decoração', 'icon' => 'sofa', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1760611656148-063d3b9a8dbc', '1773486767681-e78834b44798', '1634712282287-14ed57b9cc89']],
    ['slug' => 'calcados', 'nome' => 'Calçados', 'icon' => 'footprints', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1633464129147-777bdcc97c1d', '1760302318631-a8d342cd4951', '1768225286074-9039931bb9d3']],
    ['slug' => 'artigos-esportivos', 'nome' => 'Artigos esportivos', 'icon' => 'medal', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1441986300917-64674bd600d8', '1596274646574-266971f0dfad']],
    ['slug' => 'artigos-de-festas', 'nome' => 'Artigos de festas', 'icon' => 'gift', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1757381133477-306ae6ccc640', '1550850395-c17a8e90ad0a', '1530103862676-de8c9debad1d']],
    ['slug' => 'produtos-naturais', 'nome' => 'Produtos naturais', 'icon' => 'leaf', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1545186182-9faaf78480b8', '1516594798947-e65505dbb29d', '1659283506371-d5c45ab0f4c6']],
    ['slug' => 'joalheria', 'nome' => 'Joalheria', 'icon' => 'gem', 'grupo' => 'Varejo', 'chip' => false,
     'fotos' => ['1764512680324-048f158cab2b', '1775021723698-b9afeaa084d1']],
    ['slug' => 'autopecas', 'nome' => 'Autopeças', 'icon' => 'cog', 'grupo' => 'Automotivo', 'chip' => false,
     'fotos' => ['1779263569890-ce0389630b10', '1759419281419-b04552b2691a', '1486262715619-67b85e0b08d3']],
    ['slug' => 'posto', 'nome' => 'Posto de combustível', 'icon' => 'fuel', 'grupo' => 'Automotivo', 'chip' => false,
     'fotos' => ['1768784210790-7630d85c059a', '1644246905181-c3753e9a82bd', '1758944966741-fc79410be873']],
    ['slug' => 'guincho', 'nome' => 'Guincho / Reboque', 'icon' => 'truck', 'grupo' => 'Automotivo', 'chip' => false,
     'fotos' => ['1730514784243-f0e7f09c9f50', '1761917904658-2a9ecb84a169', '1686966933735-305bd8fe0a77']],
    ['slug' => 'imobiliaria', 'nome' => 'Imobiliária', 'icon' => 'house', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1783667440357-bf89d00c2077', '1758448721149-aa0ce8e1b2c9']],
    ['slug' => 'agencia-de-viagens', 'nome' => 'Agência de viagens', 'icon' => 'plane', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1500835556837-99ac94a94552', '1768639527374-5a0071b66fd7', '1783443162577-ffb10e6ebed7']],
    ['slug' => 'coworking', 'nome' => 'Coworking', 'icon' => 'users', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1772723822651-d2161754d61b', '1527192491265-7e15c55b1ed2']],
    ['slug' => 'prestadora-de-servicos', 'nome' => 'Prestadora de serviços', 'icon' => 'clipboard-check', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1740657254989-42fe9c3b8cce', '1781637590564-01c65dbf2039', '1686178827149-6d55c72d81df']],
    ['slug' => 'informatica', 'nome' => 'Informática', 'icon' => 'monitor', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1785947058973-43d5e9c64ef7', '1788025487883-c59e1f8edcaa', '1547394765-185e1e68f34e']],
    ['slug' => 'transportadora', 'nome' => 'Transportadora / Logística', 'icon' => 'truck', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1782948603191-065fb15e2e8e', '1587293852726-70cdb56c2866', '1586528116311-ad8dd3c8310d']],
    ['slug' => 'construtora', 'nome' => 'Construtora / Engenharia', 'icon' => 'hard-hat', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1541888946425-d81bb19240f5', '1774931363306-5a896cd4dd02', '1587582423116-ec07293f0395']],
    ['slug' => 'agencia-de-marketing', 'nome' => 'Agência de marketing', 'icon' => 'megaphone', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1666698809123-44e998e93f23', '1726594696307-5c582ba81e0e']],
    ['slug' => 'software', 'nome' => 'Software / SaaS / Startup', 'icon' => 'code', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1758873272412-7166447a94ae', '1606857521015-7f9fcf423740', '1758873272414-c0bf30332738']],
    ['slug' => 'grafica', 'nome' => 'Gráfica / Comunicação visual', 'icon' => 'printer', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1503694978374-8a2fa686963a', '1773525912476-213bff96b8a4', '1773525912431-ee1eff5e1ab9']],
    ['slug' => 'energia-solar', 'nome' => 'Energia solar', 'icon' => 'sun', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1509391366360-2e959784a276', '1768839722298-0491a671634d', '1724041875334-0a6397111c7e']],
    ['slug' => 'contabilidade', 'nome' => 'Contabilidade / Escritório de serviços', 'icon' => 'calculator', 'grupo' => 'Serviços', 'chip' => false,
     'fotos' => ['1772588627757-32365608b421', '1762427354051-a9bdb181ae3b', '1761914410572-02614b575847']],
    ['slug' => 'entretenimento', 'nome' => 'Entretenimento e lazer', 'icon' => 'gamepad-2', 'grupo' => 'Outros', 'chip' => false,
     'fotos' => ['1763819527452-f01bfb155013', '1538511059256-46e76f13f071', '1763819527303-54f4dee70709']],
    ['slug' => 'loterica', 'nome' => 'Lotérica', 'icon' => 'ticket', 'grupo' => 'Outros', 'chip' => false,
     'fotos' => ['1715520928476-cd350276d96e', '1578575436955-ef29da568c6c']],
    ['slug' => 'outros', 'nome' => 'Outros', 'icon' => 'briefcase', 'grupo' => 'Outros', 'chip' => true,
     'fotos' => ['1497366216548-37526070297c', '1497215728101-856f4ea42174']],
];

const UFS = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA',
    'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

const CAPITAIS = [
    ['Rio Branco', 'AC'], ['Maceió', 'AL'], ['Macapá', 'AP'], ['Manaus', 'AM'],
    ['Salvador', 'BA'], ['Fortaleza', 'CE'], ['Brasília', 'DF'], ['Vitória', 'ES'],
    ['Goiânia', 'GO'], ['São Luís', 'MA'], ['Cuiabá', 'MT'], ['Campo Grande', 'MS'],
    ['Belo Horizonte', 'MG'], ['Belém', 'PA'], ['João Pessoa', 'PB'], ['Curitiba', 'PR'],
    ['Recife', 'PE'], ['Teresina', 'PI'], ['Rio de Janeiro', 'RJ'], ['Natal', 'RN'],
    ['Porto Alegre', 'RS'], ['Porto Velho', 'RO'], ['Boa Vista', 'RR'], ['Florianópolis', 'SC'],
    ['São Paulo', 'SP'], ['Aracaju', 'SE'], ['Palmas', 'TO'],
];

const IMAGENS = [
    'heroHome' => '/assets/img/site/banner-home.jpg',
    'heroVender' => '1556761175-5973dc0f32e7',
    'ctaVender' => '1600880292203-757bb62b4baf',
    'login' => '1497215728101-856f4ea42174',
    'sigilo' => '/assets/img/site/sigilo.jpg',
    'fotoReal' => '1517248135467-4c7edcad34c4',
    'fotoIlus' => '/assets/img/site/cafeteria-padaria.jpg',
];

const STATUS_CADASTRO = ['novo', 'analise', 'publicado', 'recusado'];
const STATUS_ANUNCIO = ['rascunho', 'publicado', 'pausado'];
const VENDA_CNPJ = ['Sim', 'Não', 'Indiferente'];

function seg(?string $slug): array
{
    foreach (SEGMENTOS as $s) {
        if ($s['slug'] === $slug) return $s;
    }
    return SEGMENTOS[count(SEGMENTOS) - 1];
}

function seg_exists(?string $slug): bool
{
    foreach (SEGMENTOS as $s) {
        if ($s['slug'] === $slug) return true;
    }
    return false;
}

/* "Clínica (médica, odontológica, estética)" for lists and selects; cards and titles use just 'nome' */
function seg_label(array $s): string
{
    return $s['nome'] . (!empty($s['desc']) ? ' (' . $s['desc'] . ')' : '');
}

/* ['Mais procurados' => [...], 'Alimentação' => [...], ..., 'Outros' => [..., outros]] */
function seg_grupos(): array
{
    $g = [];
    foreach (SEGMENTOS as $s) $g[$s['grupo']][] = $s;
    return $g;
}

function seg_options(string $sel = ''): string
{
    $o = '';
    foreach (seg_grupos() as $g => $list) {
        $o .= '<optgroup label="' . h($g) . '">';
        foreach ($list as $s) $o .= '<option value="' . $s['slug'] . '"' . ($s['slug'] === $sel ? ' selected' : '') . '>' . h(seg_label($s)) . '</option>';
        $o .= '</optgroup>';
    }
    return $o;
}

function seg_fotos(?string $slug): array
{
    return array_map(fn($id) => 'u:' . $id, seg($slug)['fotos']);
}
