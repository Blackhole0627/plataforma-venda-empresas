<?php
/*
 * Copy this file to config.php and fill in the values.
 * config.php is ignored by git (it holds the database password).
 */
return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'passaponto',
        'user' => 'root',
        'pass' => '',
    ],

    // Public address, no trailing slash. Used in canonical links, sitemap and e-mails.
    'site_url' => 'http://localhost:8000',
    'timezone' => 'America/Sao_Paulo',

    // false = adds noindex to every page (use while the site is in review)
    'indexar' => false,
    'debug' => false,

    /* ---------- brand ---------- */
    'marca' => 'Empresas à Venda',
    'slogan' => 'Compra e venda de empresas com sigilo',

    /* ---------- contacts ---------- */
    'whatsapp' => '5511999999999',          // only digits, with 55 + DDD
    'whatsapp_label' => '(11) 99999-9999',
    'email' => 'contato@empresasavenda.com.br',
    'instagram' => '',                      // full URL, empty hides the icon
    'horario' => 'Seg a sex, 9h às 18h',

    /* ---------- e-mail notices ---------- */
    'notificar_email' => '',                // team inbox for new submissions/messages, empty = off
    'email_remetente' => 'no-reply@empresasavenda.com.br',

    /* ---------- team shown on the site ----------
     * foto: Unsplash id or a path like /uploads/equipe/maria.jpg
     * ufs:  states this person handles (listing page shows the right consultant)
     * cadastros: true = appears on the seller form as "vai analisar seu cadastro"
     */
    'equipe' => [
        ['nome' => 'Mariana Costa', 'cargo' => 'Consultora · Sul e Sudeste', 'foto' => '1573496359142-b8d87734a5a2',
         'ufs' => ['PR', 'SC', 'RS', 'SP', 'RJ', 'MG', 'ES']],
        ['nome' => 'Rafael Mendes', 'cargo' => 'Consultor · Nordeste', 'foto' => '1507003211169-0a1dd7228f2d',
         'ufs' => ['BA', 'SE', 'AL', 'PE', 'PB', 'RN', 'CE', 'PI', 'MA']],
        ['nome' => 'Juliana Alves', 'cargo' => 'Consultora · Centro-Oeste e Norte', 'foto' => '1494790108377-be9c29b29330',
         'ufs' => ['DF', 'GO', 'MT', 'MS', 'AM', 'PA', 'AC', 'RO', 'RR', 'AP', 'TO']],
        ['nome' => 'Carlos Ribeiro', 'cargo' => 'Análise de cadastros', 'foto' => '1560250097-0b93528c311a',
         'ufs' => [], 'cadastros' => true],
    ],
];
