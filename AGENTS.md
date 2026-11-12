# AGENTS.md

Ez a fajl a KMMI projekt altalanos fejlesztesi szabalyait es konvencioit rogzi (alapja a Powerbox projekt AGENTS.md-je).
Minden uj fejlesztesnel, modositasnal es kodreviewnal ezeket alapertelmezett szabalyoknak kell tekinteni, hacsak az adott feladathoz nincs kulon rogzitett kivetel.

## A projekt

- A `legacy/` mappa a regi, elo rendszer (Symfony 4.1, PHP 7.1); ez szolgaltatja az alapot (adatmodell, tartalom, funkciok).
- A fejlesztes teljes egeszeben ebben a (`php8.5/`) mappaban folyik: egyetlen Symfony alkalmazas (Symfony 7.4 LTS, PHP 8.5),
  EasyAdmin 5 admin, Twig publikus frontend, MySQL 8.0.
- A publikus frontend jelenlegi (legacy) megjelenese az uj, PHP 8.5 alatt futo rendszerben pontosan ugyanaz marad: kinezet,
  elrendezes, felhasznaloi felulet. A frontenden nem lehet eltereses.
- Az adminban a legacy rendszer szinvilaga marad, es ugyanaz marad a listak tartalma, rendezese es lapozasa, valamint az
  urlapokon a mezok elrendezese es sorrendje. Az admin egyeb vizualis reszletei (pl. az EasyAdmin verzio miatti kulonbsegek)
  elterhetnek; ezeket jelezni kell.
- Az admin szerkeszto-funkcioi legalabb a legacy szintjen vannak (pl. a menu 3 szintu, huzhato fa kereso-valasztoval), akkor is,
  ha a publikus oldal nem hasznal egy funkciot; egy szerkeszto egyszerusitese csak jovahagyassal lehetseges.
- Nyelv: csak magyar (hu); a rendszer egynyelvu, nincs nyelvvalto es nincs ukran tartalom. A regi `*_translation` tablak
  valtozatlanok maradnak (locale = hu). A kod, az azonositok es a kodkommentek angolul, a felhasznaloknak szant szovegek magyarul.
- Kod ujrahasznalhato a Powerbox backendbol es a szolyvaiemlakpark fejlesztesekbol; atvetelnel a kodot a KMMI szabalyaihoz kell igazitani.

## Altalanos szabalyok

- A projektben hasznalt altalanos technikai es uzleti konvenciokat itt kell rogziteni.
- Ha egy uj szabaly hosszu tavon relevans, azt ebbe a fajlba kell felvenni.
- Ha egy konkret feladat elter ettol, az elterest a feladathoz vagy a kapcsolodo dokumentacioban kulon rogzitni kell.
- Egy peldany, nincs masolas: ha egy kod egy masik meglevo kodjat hasznalja (osztaly, sablon, komponens), a meglevo peldanyt hivja a
  helyen; masolatot nem keszitunk. Ha elteres kell, az alap peldanyt bovitjuk (parameter, interface, kompozicio vagy oroklodes).
- Nincs duplikacio: ha tobb helyen (pl. tobb controllerben) ugyanaz a logika kell, azt egyetlen metodus vagy service vegezze, a
  hivo helyek azt hivjak.
- Deprecated hivasok: a kodban nem maradhat deprecated hasznalat, ha elkerulheto; ha megsem elkerulheto, annak okat rovid megjegyzes rogzitse.
  Cel: a deprecation-lista ures (vagy a lehetseges minimum), hogy a Symfony kovetkezo fo verziojara egyszeruen lehessen valtani.
- Fuggosegek es verziok: minden csomag, bundle, plugin, Docker image es eszkoz a legfrissebb stabil verzio legyen, ha csak lehetseges
  (uj csomag felvetelekor es lepesenkent a `composer outdated --direct` ellenorzese). Ettol csak dokumentalt ok miatt lehet elterni;
  jelenleg harom kivetel van: Symfony 7.4 LTS (a 8.x helyett), MySQL 8.0 (az eles szerver verzioja) es Font Awesome 4.7
  (a legacy ikonok pontos megjelenesehez; sajat SCSS-be vendorolva, `assets/styles/frontend/_font-awesome.scss`).
- A Sass binaris (Dart Sass) nem Composer csomag: a dev Docker image a legujabbat tolti le (`docker/php/Dockerfile`); ha a PATH-ban
  nincs `sass`, a `symfonycasts/sass-bundle` a sajat, rogzitett (regebbi) verziojat tolti le, ami az elesiteskor is jo.
- Ha a Vue-s megoldas bekerul az adminba, arrol kulon, itt rogzitett dontes szuletik; addig nincs Vue/Nuxt konvencio.

## Frontend konvenciok

- A publikus oldalak URL-jei pontosan megegyeznek az eles (legacy) oldal URL-jeivel (magyar, nyelvi elo tag nelkul, pl.
  `/cikkek/hirek/{slug}`, `/rendezvenyek/{tipus}/{slug}`, `/galeria/{slug}`), hogy a Google-ben ne kelljen ujraindexelni.
  Az URL-ek es a slug-ok nem valtozhatnak; ha egy URL megis megszunik, 301 atiranyitas kell. A rendszer az URL-eket az
  adatbazisbol (cikkek, esemenyek, emlekhelyek, galeriak stb.) allitja elo, es egy teszt ellenorzi, hogy mindegyik 200-at ad.
- A publikus frontend **Bootstrap**-et hasznal CSS keretrendszerkent, mindig a legujabb stabil fo verziot (jelenleg 5.3; a legacy
  4.x). Twig sablonok, SCSS forditas `symfonycasts/sass-bundle`-lel, AssetMapper; Node nem szukseges a szerveren. A Bootstrap 5
  nem hasznal jQuery-t.
- A legacy SCSS (kb. 656 sor) atvetele Bootstrap 5-re: a regi osztalyneveket es valtozokat az uj verzio szerint kell atirni
  (pl. `ml-*` -> `ms-*`, `form-group`, racs es kozok); a megjelenes a legacy-val pontosan egyezik (lasd fent), ezt oldalankent
  screenshot-osszevetes igazolja.
- A legacy 4.x meretei (racs 30 px, kontenerek, lekerekites, navbar/dropdown/lapozo/morzsamenu szinek) a `frontend.scss`-ben a
  Bootstrap valtozoinak `@use ... with (...)` felulirasaval vannak megadva, nem utolagos CSS-feluliras. A sajat SCSS partialok
  sorrendje a legacy build sorrendje (`frontend.scss`), mert az azonos specifikussagu szabalyokat a sorrend donti el.
- Szandekos elteresek a legacy-tol (kerésre, 2026-11-04; a legacy-val valo osszevetes ne "javitsa" vissza): az "Adattar" legordulo
  panelje (desktopon) olyan szeles, hogy az ot oszlop egy sorba kerul (a legacy panel csak a menusor szelessege volt, az oszlopok
  torodtek), es a kereso a fejlec jobb szelen van (a legacy-ban kozvetlenul a menu mellett volt); lasd `_menu.scss`, `_header.scss`.
- A frontend oldalak egyezeset a legacy-val szamszeruen kell igazolni: ugyanarra a (az eles oldalon is meglevo) oldalra a szamitott
  stilusokat (betu, szin, margo, padding, szegely, meret) elemenkent ossze kell vetni a bongeszoben (eles vs. uj).
- Cimsorok (H1-H6): oldalankent pontosan egy H1, a szintek folyamatosan kovetik egymast (nincs kihagyott szint); a szintet a tartalmi
  hierarchia hatarozza meg, a megjelenest a CSS igazitja a designhoz (pl. a legordulo menu oszlopcimei nem cimsorok). Teszt ellenorzi.
- Az eles adatokban fix azonositoju sorok vannak, amelyekre a kod hivatkozik (menu 2; cikktipusok: `ArticleType::NEWS_ID`,
  `FOLK_ART_ID`, `CHURCHES_ID`); ezek egy helyen (konstans) vannak nevezve, es a tesztek ugyanezekkel az azonositokkal mentenek.
- Az oldalsav-blokkok, a fejlec-menu, a lablec stb. Twig komponensek (`src/Twig/Components`, `templates/components`); a
  lablec RSS forrasai a `services.yaml` `app.footer_feeds` parameterben vannak (a tesztkornyezetben ures: nincs halozat).
- Csak dev kornyezetben letezo megoldasok, amelyek eleshez nem kellenek (nem kerulhetnek az eles nginx vhostba / konfigba): az
  `docker/nginx/nginx.conf` `@live_uploads` helyi tartaleka (a hianyzo feltoltott fajlt az eles oldalrol olvassa) es a
  `liip_imagine.yaml` `when@dev` lancolt betoltoje.
- SEO es GEO: `/sitemap.xml` (+ `/sitemap-{szekcio}.xml`), `/robots.txt` (mindenki, az AI botok is bejohetnek; `SEO_INDEXING_ENABLED=0`
  mellett minden zart: dev, staging), `/llms.txt`, `/llms-full.txt` (csak az allando tartalom; a cikkek nincsenek benne) es minden cikk,
  esemeny es emlekhely `{url}.md` alakban. Uj oldaltipus (kulon URL) melle sitemap provider is kell (`src/Seo/Sitemap`), es az
  `app:sitemap:check` parancs (a sitemap minden URL-jet lekeri) a valodi adaton zold kell legyen elesites elott.
- Kereses: `/search/frontend?search_form[term]=...` (a legacy URL). Az index a `search_document` tabla (MySQL InnoDB FULLTEXT, a
  `SearchEngineInterface` moge rejtve): a cikkek (amelyiknek van cikktipusa), rendezvenyek, emlekhelyek es szervezetek vannak benne,
  mint a legacy Elasticsearch-ben. A mentesnel a Doctrine figyelo (`SearchIndexSubscriber`) frissiti az adott sort (a hiba csak
  naplozodik, a tartalom elmentodik); a teljes ujraepites `app:search:reindex` (adatbazis-betoltes utan es tipus-slug valtozasnal
  kotelezo). Uj kereshetove tett oldaltipushoz a `SearchEntryFactory`-t kell bovitni. A reszletoldalak URL-jeit a `PublicPaths` adja
  (sitemap, Markdown oldalak, kereso); a `PublicPathsTest` a controllerek route-jaival egyezteti. MySQL legalabb 8.0.19.
  A FULLTEXT-et hasznalo tesztek nem futhatnak tranzakcioba csomagolva (az InnoDB a nem commitolt sorokat nem keresi), ezert a
  tesztek `ResetDatabase`-t hasznalnak, a DAMA doctrine-test-bundle itt nem alkalmazhato.
- Uj oldalak, sablonok keszitesekor elsosorban Bootstrap osztalyokat kell hasznalni; egyedi CSS csak a legacy megjelenes
  eleresehez vagy ha Bootstrappel nem megoldhato, es egy helyen (SCSS) van.
- A Bootstrap SCSS vendor figyelmeztetesei (ha vannak) elnyomhatok, rovid megjegyzessel; a sajat SCSS-ben nem maradhat deprecated.
- Az admin EasyAdmin 5; az egyedi admin megjelenes (CSS/JS) egy helyen van (`assets/admin/`). Az `admin.css` a legacy admin
  kinezetet adja az egesz adminra (kek oldalsav 230 px, 44 px-es menupontok, szurke oldal fehér panellel, Helvetica 14 px, zold
  gombok, a sorok muveletei szoveges linkek: `showEntityActionsInlined()` a `LegacyCrudController`-ben).
- Menu szerkeszto (`MenuCrudController`): a legacy szerkeszto szintjen, 3 szintu (`MenuItemFormType::MAX_LEVEL`), huzassal
  atrendezheto fa. Urlap: `MenuItemsFormType` (CollectionType) es `MenuItemFormType` (nev, cel, szintenkent `children`).
  Megjelenes: `templates/admin/form/menu_tree.html.twig` urlap-tema (az EasyAdmin `collection_row` elore kirajzolna a
  prototipust, ezert a `kmmi_menu_items_row` blokk felulirja; a gyerek-lista `form['children']`, mert a `form.children` a
  FormView tulajdonsaga) es `assets/admin/menu-tree.js` (SortableJS, egymasba agyazott listak, szintkorlat, torles-megerosites).
  A cel mindig egyedi URL (`MenuItem::setUrl()`, `connected_type = custom_url`); az ures cel validacios hiba. Az elemek
  sorrendje az azonositoik sorrendje (a legacy tabla nem kaphat pozicio oszlopot), ezert a JS a kepernyo sorrendjeben
  ujraszamozza a mezo-neveket (`renumber()`, mentes elott is): az i. entitas az i. sor adatait kapja, az uj sor az urlap
  `data-prototype-N` (N = szint) sablonjabol keszul. A cel-kereso (`assets/admin/page-finder.js`, `/admin/menu-pages`,
  `PageSuggester`) a `src/Menu/Source` forrasaibol ajanl (fo listaoldalak, cikkcsoportok, informacios oldalak, galeriak,
  telepulesek, kereso index); uj oldaltipus, amely menupont lehet, ide is kell: uj `PageSourceInterface` osztaly (magatol
  regisztralodik). A publikus fejlec csak a felso szintet mutatja (mint a legacy), az alelemek csak tarolodnak.
- Dev-ben nem szabad `asset-map:compile`-t futtatni (a `public/assets` elavult CSS-t/JS-t szolgal ki); csak production-ben kell.
- Admin CRUD-ok: minden CRUD a `LegacyCrudController`-bol szarmazik; a cimkek, a lista cime, az "uj" gomb, a lapmeret, az
  alapertelmezett rendezes es a kereso mezoi konstansok (a legacy `easy_admin.yaml` ertekei), a mezok az `indexFields()` es
  `formFields()` metodusokban vannak. A fordithato mezok (`*_translation` tabla) szerkesztese a `TranslationField`-del tortenik.
- Nativ `<input type="date|datetime-local|time">` nem hasznalhato, mert a bongeszo a sajat nyelven rajzolja ki; magyar
  nyelvu datum- es idovalaszto komponens kell (a konkret komponens a megvalositaskor kerul ide).
- Szerkeszto (rich text): CKEditor 5 mindenhol, ahol HTML tartalom szerkesztheto (`RichTextField`), a CKEditor CDN-jerol toltve
  (az ingyenes kulcs csak a CDN-hez ervenyes). A regi HTML-tartalmat nem szabad elveszteni (General HTML Support: minden
  cimke es attributum engedett). A licenckulcs nem kerul a repoba.
  Az ingyenes kulcs csak a pluginok egy reszet engedi (a tiltott plugin a szerkesztot csak olvashatova teszi): uj plugin
  felvetele elott valodi kulccsal ki kell probalni (lista: `assets/admin/ckeditor.js` fejlece).
  Dev es eles kulcs kulon van (`.env.local`); az eles kulcs 2027-11-02-ig ervenyes, evente megujitando.
- A szerkeszto-peldanyok szamat minimalis szinten kell tartani (az ingyenes csomag havi betoltes-kerettel szamol): ha egy
  urlapon tobb szerkeszto van, a nem lathato (fulon vagy osszecsukva levo) szerkeszto csak megnyitaskor indul el (lusta inditas).
- Kepek a publikus frontenden: az eredeti feltoltott kepet sosem kuldjuk. A kep .webp-re alakitva, a megjelenitesi merethez
  igazitva, gyorsitotarazva (generalas az elso keresre, utana az nginx kozvetlenul a gyorsitotarbol szolgalja ki) kerul a
  bongeszohoz. A meretek elore definialt szurokkent (neven) vannak megadva, nem tetszoleges URL parameterbol; a megjelenitett
  meret a legacy megjelenessel egyezik. A legacy `zoomCrop(w, h, '#FFFFFF', 0, 0)` (slider, galeria-boritok) a kep bal felso
  sarkatol vag (a regi PHP 7.1 igy ertelmezi a `0, 0`-t): ehhez a `zoom_crop` szuro (`ZoomCropFilterLoader`) kell, a Liip
  `thumbnail` kozeprol vag, ezert nem hasznalhato. Szuro valtozasa utan a `public/media/cache` megfelelo mappajat torolni kell.

## Fajlmeret konvenciok

- **PHP fajlok** maximalis hossza **400 sor** (entity-k kivetel). Ha tullep, refaktoralni kell (helper class, trait, kulon service).
- **PHP sorok** maximalis hossza **120 karakter**. Uj vagy modositott PHP kodnal a static code analyze miatt ezt be kell tartani.
  Ez a `src/` kodra vonatkozik (a phpcs csak azt nezi); a teszteknel (`tests/`) a szigoru stilusszabalyok (pl. sorhossz) nem kotelezoek.
- Ha Vue komponens kerul a projektbe: maximalis hossza **300-400 sor**; ha tullepi, kisebb komponensekbe kell szervezni.
- Inkabb tobb kisebb, jol elnevezett fajl, mint egy nagy monolitikus fajl.

## Fajlkezeles (feltoltesek)

- Fajlkezelo: elFinder (`helios-ag/fm-elfinder-bundle`) egy felugro ablakban (`callback` mod), a regi rendszerhez hasonloan; a feltoltesek a `public/uploads/` alatt vannak
  (`images`, `attachments`), a regi `/uploads/...` URL-ek valtozatlanul mukodnek.
- A fajlkezelo vegpontja csak bejelentkezett admin felhasznalonak erheto el.
- Engedelyezett tipusok: kepek: jpg, png (legfeljebb 15 MB); csatolmanyok: pdf, doc, docx, xls, xlsx, ppt, pptx, mp3, mp4
  (legfeljebb 60 MB, mint a legacyban; a regi flv fajlok megmaradnak, ujat nem lehet feltolteni). A tipust a szerver a tartalombol hatarozza meg
  (engedelyezolista), nem a kliens altal kuldott nevbol vagy MIME-bol.
- A feltoltott fajl sosem kerul olyan helyre, ahol a PHP vegrehajtana (a `public/uploads` alatt a PHP-futtatas tiltott az nginx-ben).

## Adatbazis konvenciok

- A regi (a legacy rendszerbol atvett) tablak es mezok valtozatlanul maradnak. A merado az ELES adatbazis semaja, nem a
  legacy kod vagy egy regi dump: az eles adatbazisban a `lead` oszlop a `articles_translation`, `cities_translation`,
  `docs_translations` es `organizations_translation` tablaban `lead_content` (a MySQL 8 foglalt szava miatt atnevezve), az
  `events_translation` es a `media` tablaban `lead`. A dev dumpot az eles adatbazisbol kell venni; eltereskor a
  `doctrine:schema:update --dump-sql` nem lehet ures, az entityket kell az elesihez igazitani, az elest nem modositjuk.
- Minden uj, altalunk fejlesztett adatbazis datum mezo `unix timestamp` tipusu `integer` kell legyen.
- Az entityk traiteket hasznalnak a kozos mezokhoz (pl. `HasUnixTimestampsTrait` mintaja a Powerbox projektben): trait csak ott
  hasznalhato, ahol azonos tipusu es tulajdonsagu mezok vannak.
- MySQL 8 foglalt szavak (pl. `lead`) oszlopneveit az entityben vissza-idezojellel kell megadni (`name: '`lead`'`), mert
  a Doctrine DBAL 4 nem idezojelez automatikusan.
- Gedmo-val kitoltott mezok (pl. `#[Gedmo\Slug]`) tulajdonsaga nullable es `null` alapertekes legyen; az ures szoveg
  alapertek "kezzel megadott" ertekkent ertelmezodik, es a Gedmo nem general slugot.
- Ha egy kulso rendszer vagy meglevo orokolt struktura ettol elter, azt kulon dokumentalni kell.

## Minosegi kapu

- Minden lepes utan zold kell legyen: phpunit, statikus elemzes (phpstan), kodstilus (phpcs) es a phpmd; mindez a `composer qa`
  parancsban van (a phpmd benne marad). Mintaja a Powerbox projekt.
  A phpmd (PDepend) gyorsitotara elavulhat es eltakarhat valodi talalatot, ezert a `qa:phpmd` minden futas elott torli
  (`~/.cache/pdepend`); a phpmd-t kezzel is csak igy futtasd.
  Ugyanigy a `qa:phpunit` elobb torli a `var/cache/test` mappat: a nem-debug teszt kontener (a 404-es oldal tesztje hasznalja)
  nem ujraepul magatol, ezert kodvaltozas utan elavult maradna, es hamis hibat adna (a `cache:clear --env=test` ezt nem torli).
  Egy-egy teszt kezi futtatasa elott kodvaltozas utan `rm -rf var/cache/test`.
- A teszt-, statikus elemzesi es stilus konfiguracio a repoban van (`phpunit.dist.xml`, `phpstan.dist.neon`, `phpcs.xml.dist`).

## Fejlesztoi es eles kornyezet

- Dev: egy telepito szkript (a Powerbox `project-init.sh` mintajara) gyorsan felhuzza a projektet a Docker konteneres kornyezettel.
- Production: a teljes Symfony rendszert egy PHP 8.5-os kontenerbe kell lehuzni (git pull), es onnan fut; a szerveren tobb oldal
  fut ugyanilyen kontenerben. A `vendor` mappat a kontenerben kell generalni (`composer install --no-dev -o`), utana cache-urites
  es migraciok.
- Minden oldalnak sajat php-fpm pool es nginx vhost kell (egyedi pool nev es socket).
- A `README.md` ket kulon fejezetben (1. dev telepites, 2. production elesites) pontosan leirja a folyamatot; minden olyan valtozasnal
  frissitendo, amely a telepitest vagy az elesitest erinti.
- Titkok (jelszavak, kulcsok, `.env.local`) nem kerulnek a repoba; az eles szerveren elnek.

## Munkamod

- Chatben: magyarul, tegezve, tomoren; ne kerdezz feleslegesen; konkretan fogalmazz, hasonlatok nelkul, idegen szavak nelkul,
  ahol van magyar megfelelo. Meglevo fajlt keres nelkul ne modosits.
- Kis lepesekben haladj. Lepesenkent: rovid osszefoglalo (mit csinalsz, mit nem), jovahagyas, megvalositas, teszt + statikus
  elemzes + build, az eredmeny megmutatasa, es csak utana a kovetkezo lepes. Kodot, migraciot es meglevo fajl modositasat csak
  jovahagyas utan keszits.
- Jovahagyas kereseskor a valaszlehetosegek leirasa legyen ertheto magyarul: mit valtoztat, mi tortenik utana, visszavonhato-e.
  Shell parancsot ne hasznalj valaszlehetoseg-leirasnak.
- Spec fajlok proazaja ekezet nelkuli magyar (ASCII); a felhasznaloknak szant UI szovegek, JSON mintak es e-mail targyak
  ekezetesek.
- Commitot es pusht csak keresre vegezz.
- Code-review utan ne javits magadtol stiluspontokat tesztekben. A sulyos (adatvesztes, deploy) pontokat jelezd, a kis dolgokat a
  backlogba jegyezd.
- Jelszavakat, kulcsokat es szerver-konfig titkokat ne ird ki, ne mentsd el sehova (memoria, fajl); ha ilyet latsz, szolj, hogy
  cserelje le.
