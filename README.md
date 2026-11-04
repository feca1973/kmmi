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

Allapot: **vazlat**, a vegleges folyamat az elesites lepesekor (7. lepes) kerul ide, a teszt domainen vegigjatszva.

Kiindulas: a szerveren tobb oldal fut PHP 8.5-os kontenerben; minden oldalnak sajat php-fpm pool es sajat nginx vhost van.
Az eles kornyezetben a `docker/` mappa es a `docker-compose.yml` nem kell, csak a `site/` mappa.

Lepesek (elso elesites):

1. A repo lehuzasa a szerveren az oldal mappajaba (`git clone`, majd a `site/` tartalma a vhost gyokere).
2. A `site/.env.local` letrehozasa kezzel (nem kerul a repoba): `APP_ENV=prod`, `APP_SECRET`, `DATABASE_URL`,
   `DEFAULT_URI=https://kmmi.org.ua` (a sitemap, az llms.txt es a robots.txt abszolut URL-jei ebbol keszulnek),
   `SEO_INDEXING_ENABLED=1` (alapertelmezett; teszt/staging szerveren `0`), `CKEDITOR_LICENSE_KEY` (a CKEditor eles
   licenckulcsa; a kulcs a portalon a production domainhez van kotve).
   A `public/uploads` mappa (images, attachments) a php-fpm felhasznalo szamara irhato legyen, es megmaradjon frissiteskor.
3. Belepes a PHP 8.5-os kontenerbe, majd az oldal mappajaban:

```sh
composer install --no-dev -o            # a bundle asseteket, az elFinder fajlokat, az importmap csomagokat es a CSS-t (sass:build) is
php bin/console asset-map:compile --env=prod
php bin/console cache:clear --env=prod
php bin/console cache:pool:clear cache.app --env=prod   # az llms-full.txt egy oraig gyorsitotarban van
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console app:search:reindex --env=prod            # a kereses indexe; elso elesiteskor es adatbazis-betoltes utan
```

4. Sajat php-fpm pool es nginx vhost az oldalnak (egyedi pool nev es socket), a vhost gyokere a `site/public`.
   A fajlkezelo vegpontjan (`/admin/efconnect/`) a feltoltesi limit 64 MB (nginx `client_max_body_size` es PHP
   `upload_max_filesize`/`post_max_size`), mashol marad a kisebb limit (lasd `docker/nginx/nginx.conf` mintajat).
   A `public/uploads` alatt a PHP-futtatas tiltott (lasd `docker/nginx/nginx.conf` mintajat).
5. Cron (ha lesz ilyen feladat) a host cronbol `docker exec`-kel.

A CSS forditasahoz Dart Sass kell: ha a PATH-ban nincs `sass`, a sass-bundle letolti a sajat verziojat a GitHubrol (a szervernek
elerheto kell legyen, vagy a kontenerbe telepitsd a Dart Sasst, mint a `docker/php/Dockerfile`-ban). A `public/media/cache`
mappa a php-fpm felhasznalonak irhato legyen (a kepek itt keletkeznek).

Elesites elott (a DNS atallitasa elott) a teszt domainen: `php bin/console app:sitemap:check --env=prod` - a sitemap minden URL-jenek
200-at kell adnia (az URL-ek megegyeznek a regi oldaleval, a Google-nek nem kell ujraindexelnie).

Frissites: `git pull`, majd a fenti parancsok (`composer install --no-dev -o`, `asset-map:compile`, `cache:clear`, migraciok;
a kereses indexet csak akkor kell ujraepiteni, ha az oldal URL-jei vagy az indexelt tartalom szerkezete valtozott).

Karbantartando:

- A CKEditor eles licenckulcsa evente lejar (**2027-11-02**), az uj kulcsot a CKEditor ugyfelportaljarol kell beilleszteni.
