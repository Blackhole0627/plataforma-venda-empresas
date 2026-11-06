<?php
/* POST multipart: dados (JSON), fotos[] (images), site (honeypot) */
require dirname(__DIR__) . '/inc/bootstrap.php';

if (!is_post()) json_error('Método inválido.', 405);
if (!empty($_POST['site'])) json_out(['cadastro' => ['protocolo' => 'CAD-0000-0000', 'responsavel' => ['whatsapp' => '']]]); // bot

$d = json_decode((string) ($_POST['dados'] ?? ''), true);
if (!is_array($d)) json_error('Dados inválidos. Recarregue a página e tente de novo.');

/* max 5 submissions per IP per hour */
$st = db()->prepare('SELECT COUNT(*) FROM cadastros WHERE ip = ? AND criado_em > ?');
$st->execute([client_ip(), date('Y-m-d H:i:s', time() - 3600)]);
if ((int) $st->fetchColumn() >= 5) json_error('Muitos cadastros seguidos. Tenta de novo daqui a pouco ou chama a gente no WhatsApp.', 429);

$str = fn($v, $max = 160) => mb_substr(trim((string) ($v ?? '')), 0, $max);
$int = fn($v) => max(0, (int) ($v ?? 0));

$r = [
    'resp_nome' => $str($d['responsavel']['nome'] ?? '', 120),
    'resp_email' => strtolower($str($d['responsavel']['email'] ?? '', 190)),
    'resp_whatsapp' => $str($d['responsavel']['whatsapp'] ?? '', 30),
    'empresa_nome' => $str($d['empresa']['nome'] ?? '', 160),
    'empresa_cnpj' => $str($d['empresa']['cnpj'] ?? '', 20),
    'segmento' => (string) ($d['segmento'] ?? ''),
    'cidade' => (string) ($d['cidade'] ?? ''),
    'cidade_outra' => $str($d['cidadeOutra'] ?? '', 120),
    'bairro' => $str($d['bairro'] ?? '', 120),
    'fundacao' => (int) ($d['fundacao'] ?? 0),
    'funcionarios' => $int($d['funcionarios'] ?? 0),
    'area' => $int($d['area'] ?? 0),
    'horario' => $str($d['horario'] ?? ''),
    'descricao' => $str($d['descricao'] ?? '', 1200),
    'preco' => $int($d['preco'] ?? 0),
    'faturamento' => $int($d['faturamento'] ?? 0),
    'margem' => max(0, min(100, (float) ($d['margem'] ?? 0))),
    'folha' => $int($d['folha'] ?? 0),
    'aluguel' => $int($d['aluguel'] ?? 0),
    'estoque' => $int($d['estoque'] ?? 0),
    'venda_cnpj' => in_array($d['cnpj'] ?? '', VENDA_CNPJ, true) ? $d['cnpj'] : 'Indiferente',
    'marca' => !empty($d['marca']) ? 1 : 0,
    'motivo' => $str($d['motivo'] ?? '', 2000),
    'foto_tipo' => ($d['fotoTipo'] ?? '') === 'ilustrativas' ? 'ilustrativas' : 'reais',
];

$erros = [];
if (mb_strlen($r['resp_nome']) < 5 || !str_contains($r['resp_nome'], ' ')) $erros[] = 'nome completo';
if (!filter_var($r['resp_email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'e-mail';
if (strlen(preg_replace('/\D/', '', $r['resp_whatsapp'])) < 10) $erros[] = 'WhatsApp';
if (mb_strlen($r['empresa_nome']) < 2) $erros[] = 'nome da empresa';
if ($r['empresa_cnpj'] !== '' && strlen(preg_replace('/\D/', '', $r['empresa_cnpj'])) !== 14) $erros[] = 'CNPJ';
if (!seg_exists($r['segmento'])) $erros[] = 'segmento';
if ($r['cidade'] !== '') {
    $c = cidade($r['cidade']);
    if (!$c || !$c['ativa']) $erros[] = 'cidade';
    $r['cidade_outra'] = '';
} elseif (mb_strlen($r['cidade_outra']) < 3) $erros[] = 'cidade';
if ($r['cidade'] === '') $r['cidade'] = null;
if (mb_strlen($r['bairro']) < 2) $erros[] = 'bairro';
if ($r['fundacao'] < 1900 || $r['fundacao'] > (int) date('Y')) $erros[] = 'ano de fundação';
if (mb_strlen($r['descricao']) < 30) $erros[] = 'descrição';
if ($r['preco'] <= 0) $erros[] = 'valor pedido';
if ($r['faturamento'] <= 0) $erros[] = 'faturamento';
if ($erros) json_error('Confira: ' . implode(', ', $erros) . '.', 422);

$files = array_slice(arquivos('fotos'), 0, 15);
if ($r['foto_tipo'] === 'reais' && !$files) json_error('Mande pelo menos 1 foto ou escolha fotos ilustrativas.', 422);

$pdo = db();
$r['status'] = 'novo';
$r['fotos'] = '[]';
$r['ip'] = client_ip();
$r['criado_em'] = now();
$cols = implode(', ', array_keys($r));
$pdo->prepare("INSERT INTO cadastros ($cols) VALUES (:" . implode(', :', array_keys($r)) . ')')->execute($r);
$id = (int) $pdo->lastInsertId();
$protocolo = 'CAD-' . date('ym') . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);

$fotos = [];
if ($r['foto_tipo'] === 'reais') {
    foreach ($files as $f) {
        $p = salvar_foto($f, 'cadastros/' . strtolower($protocolo));
        if ($p) $fotos[] = $p;
    }
}
$pdo->prepare('UPDATE cadastros SET protocolo = ?, fotos = ? WHERE id = ?')
    ->execute([$protocolo, json_encode($fotos, JSON_UNESCAPED_SLASHES), $id]);

notify(
    'Novo cadastro ' . $protocolo . ' · ' . $r['empresa_nome'],
    "Chegou um cadastro novo pelo site.\n\n" .
    "Protocolo: $protocolo\nEmpresa: {$r['empresa_nome']}\nSegmento: " . seg($r['segmento'])['nome'] .
    "\nCidade: " . ($r['cidade'] ? cidade_label($r['cidade']) : $r['cidade_outra']) .
    "\nValor pedido: " . money($r['preco']) .
    "\nResponsável: {$r['resp_nome']} · {$r['resp_whatsapp']} · {$r['resp_email']}\nFotos: " . count($fotos) .
    "\n\nAbrir: " . site_url('/admin/cadastro?id=' . $id)
);

json_out(['cadastro' => ['protocolo' => $protocolo, 'responsavel' => ['whatsapp' => $r['resp_whatsapp']], 'fotos' => count($fotos)]]);
