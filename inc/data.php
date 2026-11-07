<?php
/* Fixed lists: segments, states, capitals and site images. */

const SEGMENTOS = [
    ['slug' => 'academia', 'nome' => 'Academia', 'icon' => 'dumbbell',
     'fotos' => ['1534438327276-14e5300c3a48', '1540497077202-7c8a3999166f', '1517836357463-d25dfeac3438', '1571902943202-507ec2618e8f']],
    ['slug' => 'restaurante', 'nome' => 'Restaurante', 'icon' => 'utensils',
     'fotos' => ['1517248135467-4c7edcad34c4', '1414235077428-338989a2e8c0', '1559339352-11d035aa65de']],
    ['slug' => 'cafeteria', 'nome' => 'Cafeteria', 'icon' => 'coffee',
     'fotos' => ['/assets/img/site/cafeteria-padaria.jpg', '1501339847302-ac426a4a7cbb', '1554118811-1e0d58224f24', '1445116572660-236099ec97a0']],
    ['slug' => 'pizzaria', 'nome' => 'Pizzaria', 'icon' => 'pizza',
     'fotos' => ['1513104890138-7c749659a591', '1414235077428-338989a2e8c0', '1517248135467-4c7edcad34c4']],
    ['slug' => 'farmacia', 'nome' => 'Farmácia', 'icon' => 'pill',
     'fotos' => ['1576602976047-174e57a47881', '1587854692152-cbe660dbde88']],
    ['slug' => 'pet-shop', 'nome' => 'Pet shop', 'icon' => 'paw-print',
     'fotos' => ['1548199973-03cce0bbc87b', '1583337130417-3346a1be7dee']],
    ['slug' => 'salao-de-beleza', 'nome' => 'Salão de beleza', 'icon' => 'scissors',
     'fotos' => ['1560066984-138dadb4c035', '1522337360788-8b13dee7a37e']],
    ['slug' => 'padaria', 'nome' => 'Padaria', 'icon' => 'croissant',
     'fotos' => ['/assets/img/site/cafeteria-padaria.jpg', '1509440159596-0249088772ff', '1555507036-ab1f4038808a']],
    ['slug' => 'mercado', 'nome' => 'Mercado', 'icon' => 'shopping-cart',
     'fotos' => ['1604719312566-8912e9227c6a', '1542838132-92c53300491e']],
    ['slug' => 'loja-de-roupas', 'nome' => 'Loja de roupas', 'icon' => 'shirt',
     'fotos' => ['1441986300917-64674bd600d8', '1567401893414-76b7b1e5a7a5']],
    ['slug' => 'pousada', 'nome' => 'Pousada e hotel', 'icon' => 'bed-double',
     'fotos' => ['1566073771259-6a8506099945', '1582719478250-c89cae4dc85b']],
    ['slug' => 'escola', 'nome' => 'Escola e cursos', 'icon' => 'graduation-cap',
     'fotos' => ['1580582932707-520aed937b7b']],
    ['slug' => 'lavanderia', 'nome' => 'Lavanderia', 'icon' => 'washing-machine',
     'fotos' => ['1545173168-9f1947eebb7f']],
    ['slug' => 'oficina', 'nome' => 'Oficina mecânica', 'icon' => 'wrench',
     'fotos' => ['1487754180451-c456f719a1fc']],
    ['slug' => 'industria', 'nome' => 'Indústria', 'icon' => 'factory',
     'fotos' => ['1581091226825-a6a2a5aee158']],
    ['slug' => 'outros', 'nome' => 'Outros', 'icon' => 'briefcase',
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

function seg_fotos(?string $slug): array
{
    return array_map(fn($id) => 'u:' . $id, seg($slug)['fotos']);
}
