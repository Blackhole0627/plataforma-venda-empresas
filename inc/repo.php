<?php
/* Database access. Rows are mapped to the same shape the front-end JS uses. */

/* ---------- cidades ---------- */
function cidade_js(array $r): array
{
    return ['slug' => $r['slug'], 'nome' => $r['nome'], 'uf' => $r['uf'],
        'capital' => (bool) $r['capital'], 'ativa' => (bool) $r['ativa'], 'instagram' => (string) ($r['instagram'] ?? '')];
}

/* "@conta", "conta" or an instagram.com link -> "conta". Empty string if it doesn't look like a handle. */
function instagram_handle(string $v): string
{
    $v = trim($v);
    if (preg_match('~instagram\.com/([A-Za-z0-9._]+)~i', $v, $m)) $v = $m[1];
    $v = ltrim($v, '@');
    return preg_match('/^[A-Za-z0-9._]{1,30}$/', $v) ? $v : '';
}

/* the city's own account if the team set one, else the main account from config. null when neither exists. */
function instagram_da_cidade(?array $c): ?array
{
    $h = $c['instagram'] ?? '';
    if ($h !== '') return ['url' => 'https://www.instagram.com/' . $h . '/', 'handle' => '@' . $h, 'cidade' => true];
    $main = (string) cfg('instagram', '');
    if ($main === '') return null;
    $mh = instagram_handle($main);
    return ['url' => $main, 'handle' => $mh ? '@' . $mh : 'Instagram', 'cidade' => false];
}

/* ---------- ajustes (values the team edits in Equipe, valores e senha) ---------- */
const AJUSTES = [
    'taxa_publicacao' => 350,
    'comissao' => 8,
];

function ajuste(string $k)
{
    static $vals = null;
    if ($vals === null) {
        $vals = [];
        try {
            foreach (db()->query('SELECT chave, valor FROM ajustes')->fetchAll() as $r) $vals[$r['chave']] = $r['valor'];
        } catch (Throwable $e) { /* table not created yet: defaults */ }
    }
    $v = $vals[$k] ?? cfg($k, AJUSTES[$k] ?? null);
    return is_numeric($v) ? $v + 0 : $v;
}

function ajuste_salvar(string $k, $v): void
{
    db()->prepare('INSERT INTO ajustes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)')->execute([$k, (string) $v]);
}

/* 8 -> "8%", 7.5 -> "7,5%" */
function pct($v): string
{
    return str_replace('.', ',', (string) ($v + 0)) . '%';
}

function cidades(bool $onlyActive = false): array
{
    static $cache = [];
    $k = (int) $onlyActive;
    if (!isset($cache[$k])) {
        $sql = 'SELECT * FROM cidades' . ($onlyActive ? ' WHERE ativa = 1' : '') . ' ORDER BY nome';
        $cache[$k] = array_map('cidade_js', db()->query($sql)->fetchAll());
    }
    return $cache[$k];
}

function cidade(string $slug): ?array
{
    $st = db()->prepare('SELECT * FROM cidades WHERE slug = ?');
    $st->execute([$slug]);
    $r = $st->fetch();
    return $r ? cidade_js($r) : null;
}

function cidade_label(string $slug): string
{
    foreach (cidades() as $c) {
        if ($c['slug'] === $slug) return $c['nome'] . '/' . $c['uf'];
    }
    return $slug;
}

/* ---------- anúncios ---------- */
const ANUNCIO_FIELDS = ['segmento', 'cidade', 'bairro', 'descricao', 'fundacao', 'funcionarios', 'area', 'horario',
    'venda_cnpj', 'marca', 'preco', 'faturamento', 'margem', 'folha', 'aluguel', 'estoque', 'bens', 'aceita_proposta', 'destaque', 'foto_tipo', 'fotos'];

function anuncio_js(array $r): array
{
    return [
        'id' => (int) $r['id'], 'codigo' => $r['codigo'], 'status' => $r['status'],
        'segmento' => $r['segmento'], 'cidade' => $r['cidade'], 'bairro' => $r['bairro'],
        'descricao' => $r['descricao'], 'fundacao' => (int) $r['fundacao'], 'funcionarios' => (int) $r['funcionarios'],
        'area' => (int) $r['area'], 'horario' => $r['horario'], 'cnpj' => $r['venda_cnpj'], 'marca' => (bool) $r['marca'],
        'preco' => (int) $r['preco'], 'faturamento' => (int) $r['faturamento'], 'margem' => (float) $r['margem'],
        'folha' => (int) $r['folha'], 'aluguel' => (int) $r['aluguel'], 'estoque' => (int) $r['estoque'],
        'bens' => (int) $r['bens'], 'aceitaProposta' => (bool) $r['aceita_proposta'],
        'destaque' => (bool) $r['destaque'], 'fotoTipo' => $r['foto_tipo'],
        'fotos' => json_decode($r['fotos'] ?: '[]', true) ?: [],
        'cadastroId' => $r['cadastro_id'] ? (int) $r['cadastro_id'] : null,
        'data' => ms($r['publicado_em'] ?: $r['criado_em']),
        'atualizado' => ms($r['atualizado_em']),
    ];
}

function anuncios_publicados(): array
{
    $sql = "SELECT a.* FROM anuncios a JOIN cidades c ON c.slug = a.cidade AND c.ativa = 1
            WHERE a.status = 'publicado' ORDER BY a.publicado_em DESC, a.id DESC";
    return array_map('anuncio_js', db()->query($sql)->fetchAll());
}

function anuncios_todos(): array
{
    return array_map('anuncio_js', db()->query('SELECT * FROM anuncios ORDER BY id DESC')->fetchAll());
}

function anuncio(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM anuncios WHERE id = ?');
    $st->execute([$id]);
    $r = $st->fetch();
    return $r ? anuncio_js($r) : null;
}

function anuncio_por_codigo(string $codigo): ?array
{
    $st = db()->prepare('SELECT * FROM anuncios WHERE codigo = ?');
    $st->execute([strtoupper($codigo)]);
    $r = $st->fetch();
    return $r ? anuncio_js($r) : null;
}

/* validates + saves; returns [anuncio, null] or [null, error] */
function anuncio_salvar(array $d): array
{
    $id = (int) ($d['id'] ?? 0);
    $old = $id ? anuncio($id) : null;
    if ($id && !$old) return [null, 'Anúncio não encontrado.'];

    $status = in_array($d['status'] ?? '', STATUS_ANUNCIO, true) ? $d['status'] : 'rascunho';
    $row = [
        'segmento' => seg_exists($d['segmento'] ?? '') ? $d['segmento'] : '',
        'cidade' => (string) ($d['cidade'] ?? ''),
        'bairro' => mb_substr(trim((string) ($d['bairro'] ?? '')), 0, 120),
        'descricao' => mb_substr(trim((string) ($d['descricao'] ?? '')), 0, 3000),
        'fundacao' => max(0, min((int) date('Y'), (int) ($d['fundacao'] ?? 0))) ?: null,
        'funcionarios' => max(0, (int) ($d['funcionarios'] ?? 0)),
        'area' => max(0, (int) ($d['area'] ?? 0)),
        'horario' => mb_substr(trim((string) ($d['horario'] ?? '')), 0, 160),
        'venda_cnpj' => in_array($d['cnpj'] ?? '', VENDA_CNPJ, true) ? $d['cnpj'] : 'Indiferente',
        'marca' => !empty($d['marca']) ? 1 : 0,
        'preco' => max(0, (int) ($d['preco'] ?? 0)),
        'faturamento' => max(0, (int) ($d['faturamento'] ?? 0)),
        'margem' => max(0, min(100, (float) ($d['margem'] ?? 0))),
        'folha' => max(0, (int) ($d['folha'] ?? 0)),
        'aluguel' => max(0, (int) ($d['aluguel'] ?? 0)),
        'estoque' => max(0, (int) ($d['estoque'] ?? 0)),
        'bens' => max(0, (int) ($d['bens'] ?? 0)),
        'aceita_proposta' => !empty($d['aceitaProposta']) ? 1 : 0,
        'destaque' => !empty($d['destaque']) ? 1 : 0,
        'fotos' => json_encode(fotos_limpas($d['fotos'] ?? []), JSON_UNESCAPED_SLASHES),
    ];
    $fotos = json_decode($row['fotos'], true);
    if (!$fotos) {
        $fotos = seg_fotos($row['segmento']);
        $row['fotos'] = json_encode($fotos, JSON_UNESCAPED_SLASHES);
    }
    $row['foto_tipo'] = array_filter($fotos, fn($f) => !str_starts_with($f, 'u:')) ? 'reais' : 'ilustrativas';

    if (!$row['segmento']) return [null, 'Escolha o segmento.'];
    if (!cidade($row['cidade'])) return [null, 'Escolha uma cidade cadastrada.'];
    if (mb_strlen($row['bairro']) < 2) return [null, 'Digite o bairro.'];
    if (mb_strlen($row['descricao']) < 30) return [null, 'A descrição precisa de pelo menos 30 caracteres.'];
    if ($row['preco'] <= 0) return [null, 'Informe o valor pedido.'];

    $cadastroId = (int) ($d['cadastroId'] ?? 0) ?: ($old['cadastroId'] ?? null);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $row['status'] = $status;
        $row['atualizado_em'] = now();

        if ($old) {
            $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($row)));
            $st = $pdo->prepare("UPDATE anuncios SET $sets WHERE id = :id");
            $st->execute($row + ['id' => $id]);
        } else {
            $row['cadastro_id'] = $cadastroId ?: null;
            $row['criado_em'] = now();
            $cols = implode(', ', array_keys($row));
            $vals = ':' . implode(', :', array_keys($row));
            $pdo->prepare("INSERT INTO anuncios ($cols) VALUES ($vals)")->execute($row);
            $id = (int) $pdo->lastInsertId();
            $pdo->prepare('UPDATE anuncios SET codigo = ? WHERE id = ?')->execute(['PP' . (1100 + $id), $id]);
        }
        if ($status === 'publicado') {
            $pdo->prepare('UPDATE anuncios SET publicado_em = COALESCE(publicado_em, ?) WHERE id = ?')->execute([now(), $id]);
        }

        if ($cadastroId) {
            $novo = $status === 'publicado' ? 'publicado' : 'analise';
            $pdo->prepare('UPDATE cadastros SET status = ?, anuncio_id = ?, publicado_em = COALESCE(publicado_em, ?) WHERE id = ?')
                ->execute([$novo, $id, $status === 'publicado' ? now() : null, $cadastroId]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    return [anuncio($id), null];
}

function anuncio_patch(int $id, array $d): ?array
{
    $a = anuncio($id);
    if (!$a) return null;
    if (array_key_exists('destaque', $d)) {
        db()->prepare('UPDATE anuncios SET destaque = ?, atualizado_em = ? WHERE id = ?')->execute([$d['destaque'] ? 1 : 0, now(), $id]);
    }
    if (isset($d['status']) && in_array($d['status'], STATUS_ANUNCIO, true)) {
        db()->prepare('UPDATE anuncios SET status = ?, atualizado_em = ?, publicado_em = COALESCE(publicado_em, ?) WHERE id = ?')
            ->execute([$d['status'], now(), $d['status'] === 'publicado' ? now() : null, $id]);
    }
    return anuncio($id);
}

function anuncio_excluir(int $id): void
{
    db()->prepare("UPDATE cadastros SET anuncio_id = NULL, status = IF(status = 'publicado', 'analise', status) WHERE anuncio_id = ?")->execute([$id]);
    db()->prepare('DELETE FROM anuncios WHERE id = ?')->execute([$id]);
}

/* ---------- cadastros ---------- */
function cadastro_js(array $r): array
{
    return [
        'id' => (int) $r['id'], 'protocolo' => $r['protocolo'], 'status' => $r['status'],
        'responsavel' => ['nome' => $r['resp_nome'], 'email' => $r['resp_email'], 'whatsapp' => $r['resp_whatsapp']],
        'empresa' => ['nome' => $r['empresa_nome'], 'cnpj' => $r['empresa_cnpj'], 'razaoSocial' => $r['razao_social'],
            'instagram' => $r['instagram'], 'site' => $r['site_empresa']],
        'endereco' => ['cep' => $r['cep'], 'rua' => $r['rua'], 'numero' => $r['numero'], 'complemento' => $r['complemento']],
        'segmento' => $r['segmento'], 'cidade' => $r['cidade'] ?: '', 'cidadeOutra' => $r['cidade_outra'] ?: '',
        'bairro' => $r['bairro'], 'fundacao' => (int) $r['fundacao'], 'funcionarios' => (int) $r['funcionarios'],
        'area' => (int) $r['area'], 'horario' => $r['horario'], 'descricao' => $r['descricao'],
        'preco' => (int) $r['preco'], 'faturamento' => (int) $r['faturamento'], 'margem' => (float) $r['margem'],
        'folha' => (int) $r['folha'], 'aluguel' => (int) $r['aluguel'], 'estoque' => (int) $r['estoque'],
        'bens' => (int) $r['bens'], 'energiaSolar' => (bool) $r['energia_solar'], 'imovel' => $r['imovel'],
        'aceitaProposta' => (bool) $r['aceita_proposta'], 'aceiteComissao' => (bool) $r['aceite_comissao'],
        'cnpj' => $r['venda_cnpj'], 'marca' => (bool) $r['marca'], 'motivo' => $r['motivo'],
        'fotoTipo' => $r['foto_tipo'], 'fotos' => json_decode($r['fotos'] ?: '[]', true) ?: [],
        'anuncioId' => $r['anuncio_id'] ? (int) $r['anuncio_id'] : null,
        'data' => ms($r['criado_em']), 'analiseEm' => ms($r['analise_em']), 'publicadoEm' => ms($r['publicado_em']),
    ];
}

function cadastros(): array
{
    return array_map('cadastro_js', db()->query('SELECT * FROM cadastros ORDER BY criado_em DESC, id DESC')->fetchAll());
}

function cadastro(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM cadastros WHERE id = ?');
    $st->execute([$id]);
    $r = $st->fetch();
    return $r ? cadastro_js($r) : null;
}

function cadastros_novos(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM cadastros WHERE status = 'novo'")->fetchColumn();
}

function cadastro_status(int $id, string $status): ?array
{
    if (!in_array($status, STATUS_CADASTRO, true)) return null;
    db()->prepare('UPDATE cadastros SET status = ? WHERE id = ?')->execute([$status, $id]);
    if ($status === 'analise') db()->prepare('UPDATE cadastros SET analise_em = COALESCE(analise_em, ?) WHERE id = ?')->execute([now(), $id]);
    return cadastro($id);
}

/* ---------- photos ---------- */

/* keeps only stock ids (u:...) and files we stored ourselves */
function fotos_limpas($list): array
{
    $out = [];
    foreach ((array) $list as $f) {
        if (!is_string($f)) continue;
        if (preg_match('#^u:([0-9a-f-]{8,40}|/assets/img/site/[a-z0-9-]+\.jpg)$#', $f) || preg_match('#^/uploads/[a-z0-9/_-]+\.(jpg|png|webp)$#', $f)) $out[] = $f;
        if (count($out) >= 15) break;
    }
    return array_values(array_unique($out));
}

/*
 * Saves one uploaded image under /uploads/<dir>/ and returns its public path.
 * Re-encodes with GD when available (drops EXIF and anything hidden in the file).
 */
function salvar_foto(array $file, string $dir): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) return null;
    if ($file['size'] > 10 * 1024 * 1024) return null;
    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) return null;

    $dir = trim(preg_replace('#[^a-z0-9/_-]#', '', strtolower($dir)), '/');
    $abs = ROOT . '/uploads/' . $dir;
    if (!is_dir($abs) && !mkdir($abs, 0755, true)) return null;
    $name = bin2hex(random_bytes(8));

    if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
        $img = @imagecreatefromstring(file_get_contents($file['tmp_name']));
        if ($img) {
            $w = imagesx($img);
            $hh = imagesy($img);
            $max = 1600;
            if (max($w, $hh) > $max) {
                $r = $max / max($w, $hh);
                $img = imagescale($img, (int) round($w * $r), (int) round($hh * $r));
            }
            $ok = imagejpeg($img, "$abs/$name.jpg", 80);
            imagedestroy($img);
            return $ok ? "/uploads/$dir/$name.jpg" : null;
        }
    }
    $ext = $types[$info[2]];
    return move_uploaded_file($file['tmp_name'], "$abs/$name.$ext") ? "/uploads/$dir/$name.$ext" : null;
}

/* normalizes $_FILES['x'][] into a list */
function arquivos(string $key): array
{
    if (empty($_FILES[$key])) return [];
    $f = $_FILES[$key];
    if (!is_array($f['name'])) return [$f];
    $out = [];
    foreach ($f['name'] as $i => $n) {
        $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}
