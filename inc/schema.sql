-- MySQL 5.7+ / MariaDB 10.3+. install.php runs this automatically.

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  ultimo_login DATETIME NULL,
  criado_em DATETIME NOT NULL,
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cidades (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(140) NOT NULL,
  nome VARCHAR(120) NOT NULL,
  uf CHAR(2) NOT NULL,
  capital TINYINT(1) NOT NULL DEFAULT 0,
  ativa TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL,
  UNIQUE KEY uq_cidades_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cadastros (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  protocolo VARCHAR(20) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'novo',
  resp_nome VARCHAR(120) NOT NULL,
  resp_email VARCHAR(190) NOT NULL,
  resp_whatsapp VARCHAR(30) NOT NULL,
  empresa_nome VARCHAR(160) NOT NULL,
  empresa_cnpj VARCHAR(20) NOT NULL DEFAULT '',
  segmento VARCHAR(40) NOT NULL,
  cidade VARCHAR(140) NULL,
  cidade_outra VARCHAR(120) NOT NULL DEFAULT '',
  bairro VARCHAR(120) NOT NULL DEFAULT '',
  fundacao SMALLINT UNSIGNED NULL,
  funcionarios INT UNSIGNED NOT NULL DEFAULT 0,
  area INT UNSIGNED NOT NULL DEFAULT 0,
  horario VARCHAR(160) NOT NULL DEFAULT '',
  descricao TEXT NOT NULL,
  preco BIGINT UNSIGNED NOT NULL DEFAULT 0,
  faturamento BIGINT UNSIGNED NOT NULL DEFAULT 0,
  margem DECIMAL(5,2) NOT NULL DEFAULT 0,
  folha BIGINT UNSIGNED NOT NULL DEFAULT 0,
  aluguel BIGINT UNSIGNED NOT NULL DEFAULT 0,
  estoque BIGINT UNSIGNED NOT NULL DEFAULT 0,
  venda_cnpj VARCHAR(20) NOT NULL DEFAULT 'Indiferente',
  marca TINYINT(1) NOT NULL DEFAULT 0,
  motivo TEXT NULL,
  foto_tipo VARCHAR(20) NOT NULL DEFAULT 'reais',
  fotos TEXT NULL,
  anuncio_id INT UNSIGNED NULL,
  analise_em DATETIME NULL,
  publicado_em DATETIME NULL,
  ip VARCHAR(45) NOT NULL DEFAULT '',
  criado_em DATETIME NOT NULL,
  UNIQUE KEY uq_cadastros_protocolo (protocolo),
  KEY idx_cadastros_status (status),
  KEY idx_cadastros_ip (ip, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anuncios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'rascunho',
  segmento VARCHAR(40) NOT NULL,
  cidade VARCHAR(140) NOT NULL,
  bairro VARCHAR(120) NOT NULL DEFAULT '',
  descricao TEXT NOT NULL,
  fundacao SMALLINT UNSIGNED NULL,
  funcionarios INT UNSIGNED NOT NULL DEFAULT 0,
  area INT UNSIGNED NOT NULL DEFAULT 0,
  horario VARCHAR(160) NOT NULL DEFAULT '',
  venda_cnpj VARCHAR(20) NOT NULL DEFAULT 'Indiferente',
  marca TINYINT(1) NOT NULL DEFAULT 0,
  preco BIGINT UNSIGNED NOT NULL DEFAULT 0,
  faturamento BIGINT UNSIGNED NOT NULL DEFAULT 0,
  margem DECIMAL(5,2) NOT NULL DEFAULT 0,
  folha BIGINT UNSIGNED NOT NULL DEFAULT 0,
  aluguel BIGINT UNSIGNED NOT NULL DEFAULT 0,
  estoque BIGINT UNSIGNED NOT NULL DEFAULT 0,
  destaque TINYINT(1) NOT NULL DEFAULT 0,
  foto_tipo VARCHAR(20) NOT NULL DEFAULT 'ilustrativas',
  fotos TEXT NULL,
  cadastro_id INT UNSIGNED NULL,
  publicado_em DATETIME NULL,
  criado_em DATETIME NOT NULL,
  atualizado_em DATETIME NOT NULL,
  UNIQUE KEY uq_anuncios_codigo (codigo),
  KEY idx_anuncios_status (status, publicado_em),
  KEY idx_anuncios_cidade (cidade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contatos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  whatsapp VARCHAR(30) NOT NULL,
  assunto VARCHAR(80) NOT NULL,
  mensagem TEXT NOT NULL,
  anuncio VARCHAR(20) NOT NULL DEFAULT '',
  ip VARCHAR(45) NOT NULL DEFAULT '',
  criado_em DATETIME NOT NULL,
  KEY idx_contatos_ip (ip, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_falhas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  criado_em DATETIME NOT NULL,
  KEY idx_login_falhas (ip, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
