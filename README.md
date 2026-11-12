# KMMI

A KMMI weboldal es admin uj rendszere: Symfony 7.4 LTS, PHP 8.5, MySQL 8.0 (EasyAdmin 5 admin, Twig frontend).
A szabalyok es konvenciok az [AGENTS.md](AGENTS.md) fajlban vannak.

Szerkezet:

- `site/` - a Symfony alkalmazas (ez kerul eles szerverre)
- `docker/`, `docker-compose.yml` - csak a dev kornyezet (nginx, php 8.5, MySQL 8.0, phpMyAdmin)
- `scripts/project-init.sh` - dev telepito

## 1. Dev telepites

Elofeltetel: Docker (Compose v2). A gepre PHP-t vagy Composert nem kell telepiteni, minden a kontenerben fut.

```sh
sh scripts/project-init.sh
```

A szkript:

1. letrehozza a `.env` fajlt (Docker Compose valtozok, veletlen jelszavakkal; nem kerul a repoba),
2. letrehozza a `site/.env.local` es `site/.env.test.local` fajlt (APP_SECRET, adatbazis-URL; nem kerulnek a repoba),
3. felepiti es elinditja a kontenereket,
4. lefuttatja a `composer install`-t,
5. letrehozza az adatbazist es lefuttatja a migraciokat.

Ujrafuttathato; a meglevo `.env` fajlokat megtartja. A `--force` ujrageneralja oket (uj jelszavakkal), ezert elotte az
adatbazis-kotetet is torolni kell: `docker compose down -v` (az adatbazis tartalma elvesz).

A regi (legacy) adatbazis betoltese a dev kornyezetbe (a dump nem kerul a repoba, mert jelszo-hasheket tartalmaz):

```sh
sh scripts/project-init.sh --dump=../new_site_docs_and_data/kmmi.sql
```

Ez **eldobja es ujra letrehozza** a dev adatbazist, majd betolti a dumpot. A regi tablak valtozatlanok maradnak:
az entityk ugy vannak megirva, hogy a `doctrine:schema:update --dump-sql` ures eredmenyt ad a betoltott dumpon.
A dumpot az **eles adatbazisbol** kell venni (a merado az eles sema; egy regebbi dumpban pl. a `lead_content` oszlop neve `lead`).
Friss dump a sajat gepre (a `<szerver>` az eles szerver SSH cime; a fajl a repon kivul marad):

```sh
ssh root@<szerver> "docker exec mysql sh -c 'mysqldump -uroot -p\"\$MYSQL_ROOT_PASSWORD\" --single-transaction --no-tablespaces --default-character-set=utf8mb4 kmmi'" > ../new_site_docs_and_data/kmmi.sql
```
Az uj tablak migraciokkal (`migrations/`) kerulnek be; a regi tablakra sosem futtatunk `schema:update`-et.
A kereses indexe (`search_document`) uj tabla, a dumpban nincs benne: a `--dump` kapcsolo a betoltes utan lefuttatja az
`app:search:reindex` parancsot is (kb. 20 mp 5500 oldalra); kesobb, ha a dumpot kezzel toltod be, azt kell kezzel futtatni.

Admin: `http://localhost:8081/admin` (belepes: `/admin/login`). A dump betoltese utan a regi felhasznalok regi
jelszavukkal beleptethetok; a regi (sha512) jelszo-hash az elso sikeres belepeskor modern hash-re cserelodik, es a
regi rendszer az adott felhasznalot ezutan mar nem tudja beleptetni.

Szerkeszto es fajlkezelo (admin):

- A CKEditor 5 szerkeszto a CKEditor CDN-jerol (`cdn.ckeditor.com`) toltodik, ezert internet kell hozza. A licenckulcsot
  a `site/.env.local` fajlba kell irni: `CKEDITOR_LICENSE_KEY=...` (dev kulcs fejlesztesre, production kulcs eles
  szerveren; a kulcs nem kerul a repoba). Kulcs nelkul a szovegmezok sima szovegdobozok maradnak. A kulcsok evente
  lejarnak (a production kulcs 2027-11-02-ig ervenyes), lejarat utan a szerkeszto csak olvashato.
- A feltoltott fajlok a `site/public/uploads/images` es `site/public/uploads/attachments` alatt vannak (nem kerulnek a
  repoba). A fajlkezelo (elFinder) csak bejelentkezett adminnak ervenyes; kepek: jpg/png (max 15 MB); csatolmanyok:
  pdf, doc, docx, xls, xlsx, ppt, pptx, mp3, mp4 (max 60 MB). A tipust a szerver a tartalombol allapitja meg.
- Menu (admin: "Menu"): a menuk elemeit lehet szerkeszteni (a "Fomenu - HU" a publikus fejlec menuje), a legacy szerkesztovel
  azonos tudassal: 3 szintu fa, az elemek huzassal atrendezhetok es mas elem alelemeve tehetok; az "Uj elem" gomb felso szintu
  elemet, az elem "+" gombja alelemet ad, a torles gomb megerosites utan torol. Az "Oldal" mezo a cel (`/` jellel kezdodo
  utvonal, `#` vagy `http(s)://` cim); gepeleskor az oldal-kereso a fo listaoldalakat, cikkcsoportokat, informacios oldalakat,
  galeriakat, telepuleseket es a kereso indexebol a tartalmakat ajanlja. A publikus oldal csak a felso szintu elemeket mutatja
  (mint a legacy); a `#` celu elem (az "Adattar") nyitja a lenyilo menut, az alelemek csak tarolodnak.

Publikus oldal (frontend):

- A stilusok SCSS-bol (`site/assets/styles/frontend.scss`) fordulnak CSS-sze (`symfonycasts/sass-bundle`, Dart Sass a
  kontenerben); a `composer install` is lefuttatja (`sass:build`). SCSS-szerkesztes kozben:
  `docker compose exec php php bin/console sass:build --watch`.
- A feltoltott kepek az eles oldalon vannak, a dev gepen nincsenek: a hianyzo `/uploads/...` fajlt a dev nginx az eles oldalrol
  olvassa (csak dev; `docker/nginx/nginx.conf`, `liip_imagine.yaml` `when@dev`). Kepek a megjelenitesi merethez igazitva, `.webp`-ben
  a `public/media/cache` alatt keletkeznek az elso keresre (torolheto, ujrageneralodik).
- Az `asset-map:compile` csak production-ben kell: dev-ben a `site/public/assets` mappa elavult CSS-t es JS-t szolgalna ki. Ha
  veletlenul lefutott: `rm -rf site/public/assets`, majd `php bin/console cache:clear`.
- `SEO_INDEXING_ENABLED=0` a dev `.env.local`-ban: a `robots.txt` mindent tilt. A sitemap (`/sitemap.xml`), az `/llms.txt`,
  az `/llms-full.txt` es a cikkek/esemenyek/emlekhelyek `{url}.md` valtozata dev-en is mukodik.
- Kereses: a fejlec kereso urlapja a `/search/frontend?search_form[term]=...` oldalra visz (a regi oldal URL-je, oldalankent 12
  talalat, legfeljebb 1000). Az index a `search_document` tabla (MySQL InnoDB FULLTEXT, ekezet- es nagybetu-erzeketlen, minden szo
  szokezdet, minden szonak szerepelnie kell); a cikkek, rendezvenyek, emlekhelyek es szervezetek vannak benne. Az adminban mentett
  tartalom sora magatol frissul; a teljes ujraepites: `docker compose exec php php bin/console app:search:reindex`
  (kotelezo az adatbazis betoltese utan, es ha egy cikktipus vagy rendezvenytipus slugja valtozik). A MySQL legalabb 8.0.19 legyen.
- A sitemap minden URL-jenek ellenorzese a betoltott (valodi) adaton (hosszu futas, tobb ezer URL):
  `docker compose exec php php bin/console app:sitemap:check` (`--section=events`, `--limit=20` a szukitesre).

Cimek (csak a sajat gepedrol erhetok el):

- Oldal: `http://localhost:8081`
- phpMyAdmin: `http://localhost:8082`
- MySQL (kulso kliensnek): `127.0.0.1:3308`

A portok a `.env` fajlban allithatok (`HTTP_PORT`, `PHPMYADMIN_PORT`, `MYSQL_PORT`).

Mindennapi parancsok (a repo gyokerebol):

```sh
docker compose up -d                       # inditas
docker compose exec php php bin/console    # Symfony konzol
docker compose exec php composer qa        # phpcs + phpmd + phpstan + phpunit
docker compose exec php composer regenerate-dev-db
```

Verziok: PHP 8.5 (`php:8.5-fpm-alpine`, patch-verzio nincs rogzitve: az image ujraepitesekor
(`docker compose build`) mindig a legfrissebb 8.5.x toltodik le), MySQL 8.0.46, nginx 1.31.8, phpMyAdmin 5.2.3.
Feltoltesi limitek: nginx `client_max_body_size 50M`, PHP `upload_max_filesize=15M`, `post_max_size=50M`.

## 2. Production elesites

Allapot: a folyamatot a teszt domainen (`uj.kmmi.org.ua`) vegigjatszottuk (2026-11-12): a sitemap mind a 6924 URL-jere 200-at
ad, az admin, a CKEditor es a fajlkezelo mukodik. Az eles atallas lepesei a 2.4 pontban vannak.

A kodban `<...>` jeloli azokat az ertekeket, amelyek szerverenkent masok (oldal mappaja, kontener neve, felhasznalo).
Hostnev, IP es jelszo nem kerul a repoba.

### 2.1 A szerver felepitese

- A szerveren tobb oldal fut ugyanabban a PHP 8.5-os kontenerben; minden oldalnak sajat php-fpm pool es sajat nginx vhost
  kell (egyedi pool nev, felhasznalo es socket). A pool fajlok a kontener image-ebe kerulnek (nem a kontener fajlrendszerebe),
  mert a kontener ujraepitesekor elvesznenek.
- A PHP-s kontenerben kell: PHP 8.5 a `ctype`, `exif`, `gd` (jpeg, webp, freetype), `iconv`, `intl`, `json`, `pdo_mysql` es `zip`
  kiterjesztessel, `git`, `composer` 2, `unzip`, Dart Sass (`sass`; ha nincs, a sass-bundle letolti a sajat verziojat) es kimeno
  HTTPS (GitHub, packagist, jsdelivr). A `docker/php/Dockerfile` a mintaja.
- MySQL legalabb 8.0.19 (a kereso FULLTEXT-je miatt), a PHP-s kontenerbol elerheto.
- Az eles szerveren a repobol csak a `site/` es a `scripts/` kell. Ne maradjon ott a repo gyokerenek tobbi fajlja (pl. a
  dev `docker-compose.yml`): egy veletlen `docker compose` hivas azt venne eszre.
- Az eles adatbazis semaja a merado (lasd AGENTS.md "Adatbazis konvenciok"): a dumpot az elesbol kell venni.

### 2.2 Elso telepites

1. **Pool** a PHP-s kontenerhez (a pool felhasznalo es a socket csoportja az nginx eleresehez tartozo csoport):

```ini
[<oldal>]
user = www-<oldal>
group = www-<oldal>
listen = /var/run/php/php8.5-<oldal>-fpm.sock
listen.owner = www-<oldal>
listen.group = <nginx-csoport>
listen.mode = 0660
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
catch_workers_output = yes
php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 64M
php_admin_value[memory_limit] = 512M
php_admin_value[max_execution_time] = 120
php_admin_value[date.timezone] = UTC
```

   A `www-<oldal>` felhasznalo a kontener image-ben jon letre (egyedi uid; a megosztott oldal-mappa miatt minden image-ben
   egyedi legyen), sajat home mappaval (a Composer es a git ide ir). A pool es a felhasznalo felvetele utan a kontener ujraepitese kell.
2. **Mappa es klon** (a pool felhasznalo neveben; a mappa az o tulajdona legyen). Csak a `site/` es a `scripts/` jon le:

```sh
git clone --filter=blob:none --sparse https://github.com/feca1973/kmmi.git <oldal-mappa>
cd <oldal-mappa> && git sparse-checkout set --no-cone /site/ /scripts/
```

3. **Adatbazis:** kulon adatbazis es felhasznalo (a jelszot a szerver generalja, nem kerul a chatbe vagy a shell elozmenyekbe).
   A tartalom az eles adatbazis masolata: `mysqldump --single-transaction --no-tablespaces <eles-db> | mysql <uj-db>`
   (a regi adatbazis nem valtozik, igy a regi oldal tovabb fut).
4. **`site/.env.local`** (kezzel, 600 jogokkal, nem kerul a repoba):

```sh
APP_ENV=prod
APP_SECRET=<openssl rand -hex 32>
DATABASE_URL="mysql://<felhasznalo>:<jelszo>@<mysql-kontener>:3306/<uj-db>?serverVersion=8.0&charset=utf8mb4"
DEFAULT_URI=https://<domain>
SEO_INDEXING_ENABLED=1
CKEDITOR_LICENSE_KEY=<eles kulcs>
```

   Teszt/staging domainen `SEO_INDEXING_ENABLED=0` (a `robots.txt` mindent tilt), es a CKEditor kulcs olyan legyen, amely az adott
   domainre ervenyes (az eles kulcs az eles domainhez kotott).
5. **Telepites** (a PHP-s kontenerben, a pool felhasznalo neveben, a `site/` mappaban):

```sh
composer install --no-dev -o --no-interaction        # asset-ok, elFinder fajlok, importmap csomagok, CSS (sass:build)
rm -rf public/assets && php bin/console asset-map:compile --env=prod
php bin/console cache:clear --env=prod
php bin/console cache:pool:clear cache.app --env=prod           # az llms-full.txt egy oraig gyorsitotarban van
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console doctrine:schema:update --dump-sql --env=prod    # "Nothing to update" kell: az entityk egyeznek az eles semaval
php bin/console app:search:reindex --env=prod                   # a kereses indexe (adatbazis-betoltes utan kotelezo)
```

6. **nginx vhost** (a gyoker a `site/public`; az elFinder vegpontjan 64 MB, mashol 50 MB; az `/uploads` alatt PHP nem fut):

```nginx
server {
    listen 80;
    server_name <domain>;
    location /.well-known/acme-challenge/ { root /var/www/certbot; }
    location / { return 301 https://$host$request_uri; }
}

server {
    listen 443 ssl;
    server_name <domain>;
    root <oldal-mappa>/site/public;
    index index.php;
    client_max_body_size 50M;

    ssl_certificate /etc/letsencrypt/live/<domain>/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/<domain>/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    location ^~ /uploads/ {
        location ~* \.(php|phtml|phar)$ { return 404; }
        try_files $uri =404;
    }

    location ^~ /admin/efconnect/ {
        client_max_body_size 64M;
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.5-<oldal>-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root/index.php;
        fastcgi_param SCRIPT_NAME /index.php;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_read_timeout 300;
    }

    location / { try_files $uri /index.php$is_args$args; }

    location ~ ^/index\.php(/|$) {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.5-<oldal>-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_read_timeout 300;
        internal;
    }

    location ~ \.php$ { return 404; }

    location ~* \.(?:css|js|jpg|jpeg|gif|png|svg|ico|webp|woff2?)$ {
        expires 7d;
        add_header Cache-Control "public";
        try_files $uri /index.php$is_args$args;
    }
}
```

   Teszt domainen a HTTPS blokkba kerul az `add_header X-Robots-Tag "noindex, nofollow" always;` sor is. A `docker/nginx/nginx.conf`
   a dev valtozat; az `@live_uploads` tartalek csak oda valo, az eles vhostban nem lehet.
7. **Feltoltesek:** a regi oldal `uploads` mappajanak masolasa az `<oldal-mappa>/site/public/uploads` ala (`rsync -a --chown=<uid>:<gid>
   --exclude=.tmb --exclude=.quarantine`); a mappa a pool felhasznalonak irhato kell legyen. A `public/media/cache` a kepek
   gyorsitotara (az elso keresre keletkezik, torolheto).
8. **Ellenorzes:** `php bin/console app:sitemap:check --env=prod` (a sitemap minden URL-jenek 200-at kell adnia), admin belepes,
   szerkesztes, egy kep feltoltese a fajlkezelovel.

### 2.3 Frissites

A `site/` mappaban, a pool felhasznalo neveben: `git pull --ff-only`, majd a 2.2 5. pontja (`composer install --no-dev -o`, `asset-map:compile`,
`cache:clear`, migraciok, semaellenorzes). Feltetelesen:

- ha a `config/packages/liip_imagine.yaml` vagy a kepszurok kodja valtozott: a `public/media/cache` torlese;
- ha a kereses kodja (`src/Search/`), az indexelt tartalom szerkezete vagy egy cikk-/rendezvenytipus slugja valtozott: `app:search:reindex`.

### 2.4 Atallas a regi oldalrol

Az uj oldal sajat adatbazis-masolaton fut, a regi adatbazishoz es a regi mappahoz nem nyulunk, ezert az atallas az nginx vhost cserejet jelenti,
a visszalepes a regi vhost visszamasolasat.

Elokeszites (kieses nelkul): a regi vhost mentese (`<domain>.conf.legacy-backup`, a `.conf` nelkuli fajlt az nginx nem tolti be); az uj vhost
elokeszitese `<domain>.conf.new` neven; biztonsagi mentes az eles adatbazisrol (root-only mappaba, tomoritve); a szerkesztok ertesitese.

Atallas:

1. A szerkesztok nem mentenek a regi adminban (tartalom-befagyasztas, kb. 30 perc).
2. Friss dump betoltese az uj adatbazisba (a dump felulirja a regi tablakat; a `search_document` es a `doctrine_migration_versions` tabla marad).
3. A `uploads` valtozasainak masolasa (`rsync`, csak a kulonbseg).
4. Kod es telepites a 2.3 szerint, `app:search:reindex`.
5. `.env.local`: `DEFAULT_URI=https://<domain>`, `SEO_INDEXING_ENABLED=1`, a CKEditor eles kulcsa; `cache:clear`.
6. Valtas: `mv <domain>.conf.new <domain>.conf`, majd `nginx -t && nginx -s reload`.
7. Ellenorzes az eles neven: foldal, kereso, `/robots.txt`, `/sitemap.xml`, `app:sitemap:check`, admin belepes, kep feltoltese.
8. A sitemap beadasa a Search Console-ban; a naplok (nginx, `var/log/prod.log`) figyelese egy napig.

Visszalepes: `cp <domain>.conf.legacy-backup <domain>.conf && nginx -s reload` (masodpercek). Az uj rendszerben a valtas utan mentett tartalom
ilyenkor nem kerul vissza a regibe.

Az uj rendszer az elso sikeres belepeskor modern hash-re cserel a felhasznalo regi (sha512) jelszavat; ez az uj adatbazis-masolatban tortenik,
a regi adatbazis jelszavai nem valtoznak.

### 2.5 Karbantartas

- A CKEditor eles licenckulcsa evente lejar (**2027-11-02**), az uj kulcsot a CKEditor ugyfelportaljarol kell a `.env.local`-ba tenni.
- A tanusitvany megujitasa a szerveren megszokott modon tortenik (az oldal vhostja a `/.well-known/acme-challenge/` utvonalat kiszolgalja).
- Cron: jelenleg nincs idozitett feladat; ha lesz, a host cronbol `docker exec`-kel kell inditani.
- Az alkalmazas a `site/.env.local` fajlt olvassa, ezert a PHP `variables_order` beallitasa az elesen nem szamit.

## 3. CI (GitHub Actions)

A `.github/workflows/ci.yml` minden nem-`main` agra erkezo push-nal es a `main`-re nyitott pull requestnel lefut. Harom, parhuzamos job:

- `build`: `composer install --no-dev -o`, `composer validate --strict`, `asset-map:compile` es `cache:warmup` production modban (az `asset-map:compile`
  csak ott fut, tesztek nem fedik);
- `static-analysis`: phpcs, phpmd, phpstan;
- `tests`: phpunit, MySQL 8.0.46 service-szel.

A `main`-re a GitHub ruleset csak pull requesten at enged, es csak a harom job zold eredmenye utan lehet mergelni. A CI nem hasznal titkot.
A `tests` jobban a PHP `variables_order=EGPCS` (mint a Docker PHP image-ben), mert a fajlkezelo teszt kulon `php -S` szervert indit, amelynek latnia kell
a kornyezeti valtozokat. A `composer.json` auto-scriptjeiben az `elfinder:install` az `assets:install` elott fut, mert az elFinder fajljait az `assets:install`
csak akkor masolja a `public/bundles` ala, ha az `elfinder:install` mar letoltotte oket.
