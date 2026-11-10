# Launch guide

Everything except the client's data is ready. Follow this from top to bottom. The team guide and the handoff message are in [guia-da-equipe.md](guia-da-equipe.md).

## Where things stand (Nov 10)

The site runs on Tiago's Hostinger plan (Premium Web Hosting, server in Brazil) at **https://portalempresasavenda.com.br**. Tiago bought the domain himself and Hostinger created a separate site entry for it, so the files live in that site's `public_html`. We use collaborator access, which can't buy anything.

- The old temporary address `whitesmoke-woodcock-645852.hostingersite.com` only holds a `.htaccess` that redirects everything to the domain. Its old copy of the files is still there but never served.
- `www.` redirects to the bare domain (rule in `.htaccess`).
- Database `u744671728_empresas`, user `u744671728_site`. The password is only in `public_html/config.php` on the server.
- PHP 8.3. Hostinger already redirects http to https, so the HTTPS lines in `.htaccess` can stay commented.
- The Nov 10 migration added `cidades.instagram` and the `ajustes` table on the live database. A fresh install gets both from `inc/schema.sql`.
- The 20 sample listings are still loaded so Tiago can review. Delete them before launch.
- The first team login is Tiago's email. `notificar_email` is his Gmail for now.
- To update files, use hPanel, open the site's Arquivos then Gerenciador de arquivos, and upload single files. Its Extract button didn't work for us. Never leave a zip with `config.php` inside `public_html`. The File Manager API needs the `jwt` of the site you opened last, because both sites share one login token in the browser.
- Hostinger's CDN shows a "Checking your browser" page to headless test browsers after many requests. Run the browser tests from a normal browser profile or wait a few minutes.

**Still waiting on Tiago:** the mailbox `contato@portalempresasavenda.com.br` (Hostinger asks for a payment method even for the free email, so only Tiago can activate it in E-mails), the list of new segments, opening hours, team photo, when the commission is paid, and the Pixel or GA ID if he wants tracking. Then delete the sample listings, set `'indexar' => true` and run steps 6 and 7.

## 1. What we need from Tiago

| Item | Where it goes |
|---|---|
| Domain name, and access to where it's registered | Step 2 |
| Hosting access (cPanel or similar) | Step 2 |
| WhatsApp number | `whatsapp` (digits with 55 + DDD) and `whatsapp_label` in `config.php` |
| Public e-mail | `email` |
| Inbox for new submission alerts | `notificar_email` |
| Instagram (optional) | `instagram`, full URL. Empty hides the icon |
| Opening hours | `horario` |
| Team: name, role, states covered, square photo | `equipe`. Photos go in `assets/img/equipe/`. An empty list hides the section |
| Approval or edits for the text in Quem somos, Serviços and Investimento | `index.php`, section `id="quem-somos"` |

Ask him for 3 to 5 real listings too. With none, the home page shows "0 empresas à venda" on every segment and city. Publish some before pointing people to the site.

**Hosting must have:** PHP 8.0+, MySQL 5.7+ or MariaDB 10.3+, `.htaccess` support, GD, SSL and e-mail on the domain. Registro.br only handles domains, it doesn't host sites. Plain nginx hosting won't work because the rules live in `.htaccess`.

## 2. Domain

Do this first, because it's the only step that can make you wait. In Registro.br, set the domain's DNS servers to the ones your host gives you. It's often quick, but it can take several hours and sometimes up to a day or two. If the site must open today, change the DNS the day before.

## 3. Build the release

```bash
git pull
git archive --format=zip -o ../release.zip main
```

The zip leaves out `router.php`, `README.md`, `docs/` and `tools/`. Team photos must be committed before this step or they won't be in the zip.

## 4. Upload

1. In the host's panel, create a MySQL database and a user, and give the user all privileges on it.
2. Upload `release.zip` to the domain's root folder (usually `public_html`) and extract it. The site must be at the root of the domain, not in a subfolder.
3. Copy `config.sample.php` to `config.php` and fill in `db`, `site_url` and the items from step 1. `email_remetente` must use the site's domain.
4. Turn on SSL in the panel, then remove the `#` from the two HTTPS lines at the top of `.htaccess`.

Leave `'indexar' => false` for now.

## 5. Install

Open `https://www.dominio.com.br/install.php`.

- **Red items** block the install and say what to fix. This covers a wrong database password, a missing database, a read-only `uploads` folder and an old PHP version.
- **Yellow items** are warnings, and they catch leftover sample values such as the WhatsApp number and team. Fix them all before launch.

Create the team's first login and leave the sample listings box unticked. If `notificar_email` is set, the installer sends a test e-mail. Check that it arrives, including the spam folder.

**Delete `install.php` afterwards.**

## 6. Check

From your computer:

```bash
python tools/smoke_test.py https://www.dominio.com.br
```

It needs nothing installed besides Python. It checks pages, friendly URLs, private files, the 404 page, the login wall, HTTPS and the sitemap. Fix anything marked `FAIL`.

Then do one real pass by hand, on your phone as well:

1. Send a submission with photos at `/cadastro`.
2. Confirm the alert e-mail arrives.
3. Log in at `/admin/login`, open the submission, create the listing and publish it.
4. Open the listing on the site and confirm the company name isn't shown.
5. Send a message at `/contato` and find it in `/admin/mensagens`.
6. Delete the test listing and the test data.

## 7. Open to Google

Once Tiago approves:

1. Set `'indexar' => true` in `config.php`.
2. Add the site to Google Search Console and submit `/sitemap.xml`.
3. Run the smoke test again. It should end with no failures and no warnings.

## 8. Finish

- Send Tiago the handoff message from [guia-da-equipe.md](guia-da-equipe.md).
- Revoke the GitHub and Netlify tokens that were used during the build.
- Take down the Netlify demo when he no longer needs it.

## If something goes wrong

| What you see | Likely cause |
|---|---|
| 500 on every page | The host forbids a rule in `.htaccess`. Try removing `Options -Indexes` from `.htaccess` and `uploads/.htaccess`, then check the host's error log |
| Home works, other pages give 404 | `mod_rewrite` is off, or `.htaccess` wasn't uploaded. Some FTP programs hide dotfiles |
| "Site em manutenção" | Database settings in `config.php`. Set `'debug' => true` briefly to see the error |
| Alert e-mails don't arrive | `email_remetente` isn't on the site's domain, or the mailbox doesn't exist. Check the host's mail logs and spam |
| Photos won't upload | `uploads` isn't writable (755), or the installer warned about `post_max_size` |
| Everyone gets "Muitos cadastros seguidos" | The host's proxy or CDN hides visitors' real IPs, so they all count as one. Look at `client_ip()` in `inc/bootstrap.php` |
| A replaced photo still looks old | Images are cached for a year. Upload it under a new file name |

## Tested so far

Tested on local Apache 2.4 and PHP 8.2 with GD, using MariaDB 10.4 in strict mode and a fake mail server. This covered the installer's error messages, submissions with photos, upload protection, the login limit, publishing, privacy of company data, an empty site and an empty team.

Not tested, and only possible on the real host: real e-mail delivery, HTTPS, the host's own server software and MySQL itself (as opposed to MariaDB). Steps 5 and 6 cover these.
