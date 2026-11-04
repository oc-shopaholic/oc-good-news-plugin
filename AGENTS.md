# AGENTS.md — Code review rules for Shopaholic plugins

Rules for automated review of pull requests in Shopaholic ecosystem repositories: `Lovata.Toolbox`, `Lovata.Shopaholic`, official `Lovata.*Shopaholic` extensions, and plugins built on them. The toolchain in CI owns formatting and complexity metrics; this file covers what tools cannot see. The contribution process and the Code of Conduct are in `CONTRIBUTING.md`.

**This repository:** `Lovata.GoodNews` is classified as **core**. It is a standalone plugin that requires only `Lovata.Toolbox`; it does not depend on Shopaholic, and any integration with Shopaholic is optional (EXT-6). Rules marked *core* apply here.

## 1. Scope and stance

- Review the lines changed by the PR. Read as much surrounding code, base classes and dependent plugins as needed to verify a rule; never judge from the diff alone.
- Report defects introduced by the PR, and pre-existing defects that the PR newly exposes (a changed caller now reaches an old bug) — the latter with the exposure path stated. A pre-existing problem the PR does not touch or expose is not a regression; mention it at most once, as `NOTE`.
- Every finding names a concrete scenario: input or state → wrong behavior → fix. An architectural preference without a failure scenario is not a defect; report it as `NOTE` or not at all.
- Classify the repository first:
  - **core** — `Lovata.Toolbox`, `Lovata.Shopaholic`, official `Lovata.*Shopaholic` extensions;
  - **extension** — any other plugin that depends on Shopaholic.
- Rules marked *core* apply only to core repositories. Rules for orders, cart and payments apply only to plugins that implement them.
- If a rule cannot be verified from the available code (missing dependency, unknown runtime), ask a `QUESTION` instead of asserting a defect.

## 2. Output format

- One finding per comment, anchored to the line:
  `[SEVERITY][RULE-ID] Condition → consequence. Fix: concrete change.`
- Severity:
  - `BLOCKER` — security hole, data loss or leak, broken install or upgrade, fatal error on a main path, BC break outside a MAJOR release.
  - `MAJOR` — stale or wrong data served from cache, wrong price or availability, N queries or N external calls per list, missing permission check, broken rollback.
  - `MINOR` — limited functional regression, i18n, maintainability, undocumented new public contract (BC-3).
- Message types that are not severities: `QUESTION` (intent unclear and the answer decides the severity), `NOTE` (observation, no action required).
- Final summary: counts by severity, rule IDs of blockers, list of checks that could not be performed and why. Give a verdict only when the platform asks for one and every applicable rule was checked; never approve on an incomplete check.
- Severity states whether the PR may merge as is. Platform priority (e.g. Codex P0/P1/P2) is assigned separately from impact, reach and trigger conditions of the specific finding: a `BLOCKER` that affects every storefront request is P0, a `BLOCKER` on a rare admin path may be P1. Never raise a priority to pass a publication threshold.

## 3. Review procedure

1. Classify the repository and list the layers the diff touches: Model, Item, Collection, Store, Handler, Component, backend, migration, lang, theme.
2. Determine the target version from `updates/version.yaml` in the PR head (VER-*).
3. For every new or changed field, relation or write path, trace Model → `$cached`/Item → Store → ModelHandler (CACHE-*).
4. Check request input, ownership, permissions and output escaping (SEC-*).
5. Check queries and external calls per element on frontend paths (PERF-*).
6. Check public contracts: events, Item/Collection members, components, settings, tables (BC-*).
7. Check domain invariants that the diff touches: Collection semantics, prices, catalog, multisite, import, orders (COL-*, PRICE-*, CAT-*, SITE-*, IMP-*, ORD-*).
8. Check migrations and tests (DB-*, TEST-*). Before running tests, follow section 8.

## 4. Architecture reference (verified against Toolbox 2.3.0 / Shopaholic 1.33.0)

| Layer | Base class | Contract |
|---|---|---|
| Model | `October\Rain\Database\Model` + `TraitCached` | Configuration (`$rules`, relations, `$fillable`, `$cached`, `$translatable`), scopes, backend and import logic. |
| Item | `ElementItem` | `const MODEL_CLASS`; serializable snapshot of one model: `$cached` fields + `getElementData()` + `$arExtendResult` methods; relations via `$arRelationList`; eager loads in `static $arQueryWith`. Cached forever per class tag until `clearCache($iID)`. `ItemStorage` keeps a per-process copy and returns a **clone** on `get()`. |
| Collection | `ElementCollection` | `const ITEM_CLASS`; array of IDs, mutable. `arElementIDList === null` means *not set* (`isClear()`); `[]` means *computed empty*. `make([])` leaves the list **not set**; `clear()` / `set([])` set it empty. `intersect()` keeps the order of the current list; `applySorting()` takes the order of the argument and also restricts the set. `count()` counts IDs without SQL. `take()`/`page()`/`all()` return arrays of Items; `first()`/`last()` return an Item, empty Item when the list is empty (`isEmpty()`). |
| Store | `AbstractStoreWithoutParam` / `WithParam` / `WithTwoParam` grouped by `AbstractListStore` | Singleton (`protected static $instance`), `getIDListFromDB(): array`, cached forever until `clear()`. `AbstractListStore::__get()` returns `null` for an unregistered name. `WithParam::clear($v)` returns silently for an empty `$v` other than `0`/`'0'`. |
| Handler | `ModelHandler`, `AbstractBackend{Field,Column,Menu}Handler`, `AbstractExtendRelationConfigHandler`, `AbstractModelRelationHandler` | `ModelHandler::subscribe()` binds `model.afterCreate/afterSave/afterDelete[/afterRestore]`, sets `$this->obElement`, calls `init()` then the hook. Default `afterSave()`/`afterDelete()` clear the Item cache. |
| Component | `ElementPage`, `ElementData`, `SortingElementList`, `ComponentSubmitForm` | Return Item/Collection to Twig. `ElementPage::getElementObject()` resolves the model by slug with ORM; that is the designed place for it. |

Canonical Twig flow: `List.make()` → filters / `sort()` → `count()` → `page()` / `take()`.

## 5. Code Review Rules

### VER — versions and SemVer

- **VER-1** The target version is the latest SemVer key in `updates/version.yaml` of the PR head. PR title, labels and milestones do not override it. Compare versions as SemVer, not as strings. If the file has no new key and the change needs one (VER-3), ask a `QUESTION`; several PRs may share one unreleased version.
- **VER-2** The baseline for compatibility is the last *released* version of the same line (tag or release and its `version.yaml`), not the base branch, which may already hold an unreleased version. If the baseline cannot be confirmed, state it as a limitation.
- **VER-3** For a stable API (≥ 1.0.0): PATCH — compatible fixes; MINOR — compatible new functionality or deprecations; MAJOR — incompatible changes to a public contract (BC-1). A migration is classified by its effect: a schema fix can ship in PATCH, a new feature's table needs MINOR. Insufficient version for the change → `BLOCKER`.
- **VER-4** In a MAJOR release a documented breaking change with a migration path is not a defect. MAJOR does not excuse correctness, security or unintended data-loss defects.
- **VER-5** A new `version.yaml` entry lists its migrations and names newly required dependency versions (`Requires Toolbox plugin version X and later.`); missing either → `MAJOR`. Core convention is a quoted past-tense sentence (`'Added …'`, `'Fixed …'`) — style only, `NOTE`.

### ARCH — layering

- **ARCH-1** Models stay configuration: no frontend data getters. Move them to Item, Collection or Store. → `MINOR`.
- **ARCH-2** Frontend reads go through Item/Collection/Store. Eloquent or `DB::` in theme partials, page PHP sections, or component methods that return models to Twig → `MAJOR` when it bypasses an existing Store/Item for the same data, otherwise `MINOR`. Designed ORM places are exempt: `getIDListFromDB()`, `getElementData()`, `ElementPage::getElementObject()`, backend code, console and import classes.
- **ARCH-3** Model events, Items, Stores and Handlers never read `Input`, `post()`, `request()` or session. Data enters in components and controllers. → `MAJOR` (breaks console, queue and import paths).
- **ARCH-4** `Plugin.php` and `routes.php` only register things; business logic there → `MINOR`.
- **ARCH-5** *core*: no client-specific behavior; put it behind an event, a setting or an extension. → `BLOCKER`.

### EXT — extending other plugins

- **EXT-1** Change another plugin only through its extension points. Patching, overriding or copying another plugin's files, or adding versions or migrations to its `version.yaml` → `BLOCKER` (the next update overwrites or collides).
- **EXT-2** Extend models with `addFillable()`, `addJsonable()`, `addCachedField()`, `$obModel->translatable[] = …`, `array_merge()` into relation arrays. Replacing an array (`$obModel->belongsTo = [...]`, `$obModel->cached = [...]`) → `MAJOR` (drops other plugins' entries).
- **EXT-3** Extend Items with `$arRelationList[...]`, `$arExtendResult[] = 'addX'` + `addDynamicMethod('addX', …)` + `setAttribute()`, or `addDynamicMethod('getXAttribute', …)`. See PERF-1 for what each may do.
- **EXT-4** Extend Collections with `addDynamicMethod('name', fn () => $obList->intersect(XListStore::instance()->name->get()))`. A query inside the method → `MAJOR`.
- **EXT-5** Extend backend with `AbstractBackendFieldHandler`, `AbstractBackendColumnHandler`, `AbstractBackendMenuHandler`, `AbstractExtendRelationConfigHandler`, `Controller::extend` + `mergeConfig()`. Editing another plugin's `fields.yaml`/`columns.yaml` → `BLOCKER`.
- **EXT-6** Every plugin whose classes are used unconditionally is in `public $require`. Optional integrations are guarded with `PluginManager::instance()->hasPlugin()`. Unguarded optional class → `MAJOR` (fatal when absent). A guard for a plugin already in `$require` is noise, not a defect.
- **EXT-7** Each handler is subscribed once in `boot()`. Double subscription → `MAJOR` (double side effects).
- **EXT-8** `$this->obElement` is set by the base handler per event before `init()` and the `after*` hook; it is valid during that event's processing. Assigning it elsewhere (e.g. inside a `Model::extend` closure in `subscribe()`) → `MAJOR` only with a scenario where a later call reads the wrong model or overwrites in-flight state.
- **EXT-9** Before reporting an undefined method or property on a Shopaholic class, check `extend()`/`addDynamicMethod()` registrations in the plugin's dependencies. A `@method`/`@property` annotation alone does not prove availability; an actual registration in a required plugin does.

### EVT — events

- **EVT-1** New events are declared as class constants (`const EVENT_X = 'plugin.entity.action'`) and fired through them; payload and return contract are documented in the docblock and in the version entry.
- **EVT-2** Event name, argument order, call moment and result handling are public API (BC-1). `null`, `false`, `[]`, the first listener result and the array of all results are different contracts; changing `Event::fire(..., $halt)` or the handling of results must be checked against all listeners in core and official extensions. Change without MAJOR → `BLOCKER`.
- **EVT-3** Listeners of collecting events (`shopaholic.sorting.get.list`, `shopaholic.sorting.offer.get.list`, `shopaholic.promo_block.get.product.list`, `*.get.type.list`) return `null` for codes they do not own and an ID array otherwise. The caller defines how results are merged (the sorting stores take the first non-empty array; `promo()` merges all arrays): verify a listener's return against the caller before reporting.
- **EVT-4** A `false` return or `Result::setFalse()` aborts the operation (e.g. `shopaholic.order.before_create`, `EVENT_UPDATE_ORDER_AFTER_CREATE` rolls back the order). Verify the abort is intended and happens before side effects.
- **EVT-5** `shopaholic.order.created` / `after_create` run inside the user request. Synchronous external HTTP, mail or document generation there without a queue → `MAJOR`; with failure that aborts the order → `BLOCKER`.

### CACHE — Item and Store invalidation

- **CACHE-1** Every Store is invalidated on every write path of every field it depends on: create, save, delete, restore, relation attach/detach/sync, import, bulk update. No `clear()` at all → `BLOCKER`; one missing path → `MAJOR`. Dependencies include related models (an Offer price change affects Product price sortings).
- **CACHE-2** In handlers call `->clear()`, not `->get()`. On a FK change clear both the new value and `getOriginal()` (or use `checkFieldChanges()`, `clearCacheNotEmptyValue()`, `checkFieldChangesTwoParam()`). Clearing only the new key → `MAJOR` (old list stays stale).
- **CACHE-3** Overridden `afterSave()`, `afterDelete()`, `subscribe()` keep the base behaviour: `parent::` call or an equivalent explicit `clearItemCache()`/event binding. Neither present → `MAJOR` (Item cache not cleared). Do not demand a second `clearCache()` when the base already does it.
- **CACHE-4** SoftDelete models set `$bWithRestore = true` and clear Stores in `afterRestore()`.
- **CACHE-5** `isFieldChanged()` in `afterDelete()` is always false; clear unconditionally there.
- **CACHE-6** `XListStore::instance()->name` must be registered via `addToStoreList('name', …)` in `init()`; an unregistered name resolves to `null` and the clear is silently skipped → `BLOCKER`.
- **CACHE-7** Writes that bypass model events (`DB::table()->insert/update/delete`, `Model::where()->update()`, raw pivot writes) call `XItem::clearCache($iID)` and the affected `Store->clear()` explicitly. Missing → `MAJOR`. A full cache flush is not a substitute for dependency analysis.
- **CACHE-8** A field read through an Item is in `$cached` (or `addCachedField()`) or produced by `getElementData()`/`$arExtendResult`; otherwise it is `null` on a warm cache → `MAJOR`. `$fillable`, `$cached`, `$translatable`, `$jsonable` serve different purposes; adding to one does not cover the others.
- **CACHE-9** Context-dependent values (user, site, language, currency, price type) never enter the shared Item/Store cache without that context in the key. Personal price in a shared cache → `BLOCKER` (leaks to other customers). Computing from shared raw data at read time is fine.
- **CACHE-10** `Cache::remember()`, `cache()`, `Cache::forever()` outside Toolbox Item/Store need a TTL or an invalidation path and must not store Eloquent models or collections (closures do not serialize). → `MAJOR`.
- **CACHE-11** Custom sortings exposed through `shopaholic.sorting.get.list` are cached in the core `SortingListStore`: clear `XListStore::instance()->sorting->clear($sCode)` when any field they depend on changes.
- **CACHE-12** `ItemStorage` is static per process and `get()` returns a clone: data staleness within a long-running worker is possible, object identity is not. Flag a worker leak only with the actual lifecycle verified; otherwise `QUESTION`.

### STORE — Store implementation

- **STORE-1** Concrete Stores declare `protected static $instance;`. October's `Singleton` trait (3.x/4.x) keeps one `static::$instance` slot per declaring class, so a subclass without its own slot can receive a sibling's instance. Missing → `MAJOR`.
- **STORE-2** `getIDListFromDB(): array` selects IDs only (`->toBase()->pluck('id')->all()`); hydrating models → `MINOR`; a query per row → `MAJOR`.
- **STORE-3** A new filter or sorting = Store + `addToStoreList()` + Collection method + handler invalidation. Missing any part → `MAJOR`.

### COL — Collection semantics

- **COL-1** `make([])` is *not set*: after a zero-result filter, rebuilding with `make([])` and applying `active()` returns the whole catalog. Intentional empty result uses `clear()` or `set([])`. → `MAJOR`.
- **COL-2** `intersect()` keeps the current order; `applySorting()` imposes the argument's order and restricts the set. Swapping one for the other changes ranking or content → `MAJOR` when the output order or set is user-visible.
- **COL-3** Filters mutate `$this`. When two independent results are needed from one source, use `copy()` or `clone`; otherwise the second block shows the first block's filtered set → `MAJOR`.
- **COL-4** `first()`/`last()` return an empty Item, not `null`; `take()`/`page()`/`all()` return arrays. Null checks on an Item or Collection methods on an array → `MAJOR` (fatal or always-true branch).
- **COL-5** Filter and sort on IDs before materializing. `all()` followed by PHP/Twig filtering of Items → `MAJOR` when the list is unbounded.

### PERF — performance

- **PERF-1** Code executed per element of a list — Item accessors (`get*Attribute`), `Item::extend` closures, Collection methods, loops in Twig — is judged by cost per list render. Shared, context-free data belongs in `getElementData()`/`$arExtendResult` (cached) or a Store; context-dependent data (personal price) must not go there (CACHE-9). Severity:
  - N DB queries for N elements where a Store or cached field could serve them → `MAJOR`;
  - N external calls for N elements on an unbounded list, or any external call without timeout and error handling on a storefront path → `MAJOR`;
  - a bounded number of external calls with per-element memoization, timeout and error handling for data that cannot be cached → no finding.
  Memoizing one element does not remove N calls for N elements.
- **PERF-2** When `getElementData()` reads a relation, that relation is in `static $arQueryWith`.
- **PERF-3** Loops over elements do not call `XCollection::make()->…->count()` or Store `->get()` per iteration when a single filtered collection or a map from one Store serves the loop. Use `category($iID, true)` for subcategories. → `MAJOR` when the loop is unbounded. `Item::make()` and `ElementCollection::count()` are cached/array operations and are not defects by themselves.
- **PERF-4** Imports, exports, ERP/1C syncs, mass mail and document generation run in console commands or queue jobs; job payloads carry IDs and reload with `findOrFail()`; large sets use `chunkById()`. `Model::all()` on catalog tables in jobs or seeders → `MAJOR`.
- **PERF-5** Cron expressions in `registerSchedule()` match their intent (`'* 2 * * *'` fires every minute for an hour). Wrong expression → `MAJOR`.

### SEC — security

- **SEC-1** Request arrays (`Input::all()`, `post()`, `input()`) never go whole into `create()`, `fill()`, `update()`, `Auth::register()` or mail data. Map fields explicitly or `Input::only([...])` and validate. `BLOCKER` when a traced path lets an untrusted request value reach a privilege or state field (`is_activated`, `is_superuser`, `permissions`, `active`, `status_id`, `price`, `rating`, `user_id`, `secret_key`); such a field in `$fillable` alone is `NOTE` until the path is shown.
- **SEC-2** When an AJAX handler loads a non-public record (order, address, user data, cart) by an ID from the request, it verifies the access policy: owner (`user_id === AuthHelper::getUser()->id`), secret key, or explicit public visibility. Guests are handled without fatal errors. Missing check → `BLOCKER`. Public catalog records need no owner check.
- **SEC-3** New or changed backend controllers declare `$requiredPermissions`; permissions are registered in `plugin.yaml`; navigation and settings items list `permissions`. Missing → `MAJOR`.
- **SEC-4** Untrusted values never reach SQL (`whereRaw`, `orderByRaw`, `DB::raw`), shell commands or external query strings by interpolation; use the protection that fits the sink — bindings for SQL values, a whitelist for identifiers and sort/filter codes (e.g. `ProductListStore::SORT_*`), `escapeshellarg` or a safe API for shell, encoding for URLs. Untrusted value reaching a sink unprotected → `BLOCKER`; interpolation of constants or validated internal IDs is not a defect.
- **SEC-5** Twig `|raw` only for backend-authored HTML. Request values or user content under `|raw`, or `input()` inside SEO/meta templates → `BLOCKER`.
- **SEC-6** Private files (invoices, imports, exports) use `attachOne`/`attachMany` with `'public' => false` or a protected route. Predictable paths under `storage/app/media` or `uploads/public` → `BLOCKER`.
- **SEC-7** Every route in `routes.php` has authentication and authorization matching its purpose: backend auth for admin or service endpoints, provider protocol verification for payment callbacks (ORD-3), none for storefront pages. Service endpoint without any → `BLOCKER`. Debug helpers (`dd`, `dump`, `die`, `exit`, `var_dump`, Twig `die`/`debug`) in shipped code → `MAJOR`.
- **SEC-8** No credentials, tokens or secret files in the diff; TLS verification stays on (`verify` ≠ `false`). → `BLOCKER`.
- **SEC-9** XML/file intake: external entities disabled, paths validated, no SSRF via user-supplied URLs. Report only with a reachable path.
- **SEC-10** No empty `catch`; log and rethrow or return a `Result` error. → `MINOR`; `MAJOR` when it hides a payment or order failure.

### DB — migrations

- **DB-1** Every migration and seeder is listed in `updates/version.yaml` under the target version. Editing a *released* migration or version entry → `BLOCKER` (installed shops never re-run it). Editing an unreleased one is allowed (VER-2 defines released).
- **DB-2** Migrations extend `October\Rain\Database\Updates\Migration`, use `October\Rain\Database\Schema\Blueprint`, declare `const TABLE_NAME` (and `COLUMN_LIST` for column changes), guard `up()` and `down()` with `Schema::hasTable`/`hasColumns`. `down()` is symmetric; an empty or non-working `down()` → `MAJOR`. Irreversible data migrations say so in a comment.
- **DB-3** Columns added to another plugin's table live in the extending plugin's own guarded migration. A foreign key constraint to another plugin's table → `MAJOR` only when it demonstrably breaks that plugin's existing deletes or migrations (core tables have no FK constraints and delete rows without cascade); otherwise `QUESTION` — project policy, not a verified contract.
- **DB-4** Table names `<author>_<plugin>_<entities>`; file and class names `create_table_<name>`, `update_table_<name>_<change>`, `seeder_<purpose>`.
- **DB-5** Before dropping or retyping a column, data is transferred; soft-deleted rows are preserved where the model uses SoftDelete. Loss without a stated strategy → `BLOCKER`.
- **DB-6** Seeders are idempotent and chunked.

### BC — public API (*core*)

- **BC-1** Public API: Item fields and methods, Collection methods, Store names and `SORT_*` constants, events (name, args, return), component aliases, properties and `on*` handlers, model fields, relations and scopes, settings codes, table and column names, lang keys, and the import extension points (`IMP-4`: documented events plus protected stage methods already overridden by official extensions — not every protected method). Removing, renaming or changing semantics outside MAJOR → `BLOCKER`.
- **BC-2** Removal of a public member outside MAJOR → `BLOCKER` (BC-1). Deprecation first (`@deprecated` with replacement, as core does for `CommonSettings::getValue()`) is the recommended path; its absence before a MAJOR removal is `NOTE`, pending maintainer policy.
- **BC-3** New dynamic members get `@property`/`@method` annotations on the Item, Model or Collection docblock, as core does for Properties, Reviews and Filter.
- **BC-4** Supported PHP, October and Toolbox versions come from `composer.json`, `version.yaml` requirements and CI of the target branch, not from any particular shop. Syntax above the declared minimum without raising it in the same PR → `BLOCKER`.
- **BC-5** Visibility, signature, `static` or `final` changes are checked against subclasses and dynamic extensions in official plugins.

### CAT — catalog and availability

- **CAT-1** User-facing lists apply `active()` (and `site()` in multisite); backend and service queries may intentionally not. Missing `active()` on a storefront path → `MAJOR`.
- **CAT-2** Cart and order positions reference Offer, never Product. Substituting IDs → `BLOCKER`. Changing `Offer.product_id` invalidates both products' Items and lists.
- **CAT-3** Behaviour governed by settings stays governed: `check_offer_active` (hide products without active offers), `allow_negative_offer_quantity`. Hardcoding either → `MAJOR`.
- **CAT-4** Category filters respect main and additional categories and nested-tree order; `category($iID, true)` for children.
- **CAT-5** `quantity`, `quantity_in_unit`, `measure` and dimensions are distinct; unit conversions do not change price or pack count.

### PRICE — prices and sorting

- **PRICE-1** Arithmetic uses `price_value`, `old_price_value` (float); `price`, `old_price` are formatted strings for output. Arithmetic on the formatted string → `BLOCKER`.
- **PRICE-2** The main price is `price_type_id = null`; additional prices are typed. Queries on `lovata_shopaholic_prices` filter `item_type` (polymorphic). Sorting keys are `price|asc`, `price|desc`, `price|asc|<price_type_code>`. Dropping `item_type` or the null/typed distinction → `BLOCKER`.
- **PRICE-3** Price sortings aggregate over active offers: multiple offers per product, inactive and deleted offers excluded, product IDs unique, correct min/max. `groupBy` only if the result is preserved.
- **PRICE-4** Currency conversion, rounding and tax application follow the existing helpers (`CurrencyHelper`, `TaxHelper`, `PriceHelperTrait`); double conversion or a hardcoded rate → `MAJOR`. An existing float price is not a defect.

### SITE — multisite, translations, URLs

- **SITE-1** Site and language are different contexts. An empty `site` relation means visible on all sites (`ListBySiteStore`); treating it as hidden → `MAJOR`.
- **SITE-2** New catalog entities follow the multisite pattern: `MultisiteHelperTrait`, `*_site_relation` table, `ListBySiteStore`, `site()` Collection method, `hasRelationWithSite()` in page components.
- **SITE-3** Slug lookups honour the Toolbox setting `slug_is_translatable`: `transWhere()` plus `checkTransSlug()` when enabled. Replacing with a plain `where('slug')` → `MAJOR`.
- **SITE-4** New translatable content fields go into `$translatable`; RainLab.Translate stays optional.
- **SITE-5** URLs are built with `getPageUrl()`/`PageHelper`; hardcoded paths or page codes → `MINOR`.

### IMP — import and external identifiers

- **IMP-1** `external_id` is a string with leading zeros preserved and is never confused with the local `id`; relations from external data resolve through the importer's mapping. → `BLOCKER` on data corruption.
- **IMP-2** Importers distinguish absent field, `null`, empty string and `0`; an `empty()` check that zeroes price, quantity or a relation on partial update → `MAJOR`.
- **IMP-3** Re-import is idempotent: no duplicate records, images or translations; deactivation of missing items follows the import mode (`setDeactivateFlag()`).
- **IMP-4** Import events (`EVENT_BEFORE_IMPORT`, `EVENT_AFTER_IMPORT`, `extend_xml_import_data`, `extend_xml_import_fields`) with their arguments are public API (BC-1). Protected importer methods count as API only where an official extension or documentation overrides them; ask a `QUESTION` otherwise.

### ORD — orders, cart, payments (only plugins implementing them)

- **ORD-1** Server recomputes totals from current prices, taxes, shipping and promo mechanisms; client-supplied prices, totals, `user_id` or payment flags never become trusted. → `BLOCKER`.
- **ORD-2** Order creation, status transitions and notifications are safe under partial failure and retry; a queue alone is not idempotency. Guaranteed double charge, double order or double mail → `BLOCKER`.
- **ORD-3** Payment callbacks verify the provider's protocol (signature or server-side confirmation), the order, amount, currency, allowed status transition and repeated delivery. A public callback URL is fine; a user return URL never proves payment. → `BLOCKER`.
- **ORD-4** Gateway interface and payment events stay BC; error, cancel, pending and paid remain distinguishable.

### I18N — localization

- **I18N-1** User-facing strings use lang keys `author.plugin::lang.<section>.<key>` in YAML configs, `componentDetails()`, `defineProperties()`, `Flash`, `$attributeNames`, user-visible exceptions. Hardcoded string → `MINOR`.
- **I18N-2** Use the plugin's own namespace; reuse only shared `lovata.toolbox::lang.*` keys. Copying another plugin's keys → `MINOR`.
- **I18N-3** New keys go to `lang/en/lang.php`; when `crowdin.yml` exists, other locales are not hand-edited in feature PRs.
- **I18N-4** Locale directories use ISO 639-1 codes (`cs`, not `cz`; `uk`, not `ua`).

### TEST

- **TEST-1** Where the repository's `CONTRIBUTING.md` requires tests (core does: `tests/unit/{models,item,collection}`), changed behaviour in `classes/`, `components/`, `models/` is covered. Existing tests may already cover it; check before flagging. Uncovered behaviour → `MAJOR` under such a policy, `NOTE` where the repository has no test policy.
- **TEST-2** Cache tests follow warm → change → read: populate the Item or Store, change the model through the normal path, assert the new value (`CACHE_DRIVER=array`).
- **TEST-3** Run PHPUnit only inside an October application with the plugin's `phpunit.xml` and Toolbox present; otherwise state the limitation instead of claiming results.

### PR — hygiene

- **PR-1** Target branch follows the repository's `CONTRIBUTING.md` (core: `develop`) unless the maintainers say otherwise. Wrong base → `MAJOR`.
- **PR-2** The description lists interface changes: events, component properties, settings, fields, required dependency versions.
- **PR-3** No `vendor/`, build artifacts, IDE files, dumps, unrelated reformatting or dev-only packages in `require`.

## 6. Left to CI tools

phpcs (Symfony2/PSR-2), PHPMD (`PHPMD_custom.xml`: complexity, method and class length, naming lengths), `.editorconfig`. Do not report these. Hungarian notation and docblocks on non-public members: `NOTE` at most. Docblock on a new public contract: BC-3.

## 7. Do not flag

- `Item::make($iID)` in loops over ID lists — Items are cached.
- `ElementCollection::count()` — array count, no SQL.
- `DB::table()` joins inside `getIDListFromDB()`; ORM in `ElementPage::getElementObject()`, `getElementData()`, backend, console, import classes.
- `$this->obElement` inside `after*` hooks of a `ModelHandler`.
- `make([])` as the initial not-set collection before standard filters.
- A dynamic method registered by a required plugin, even if the source class lacks it.
- `|raw` on backend-authored rich text (`description`, `preview_text`, CMS content).
- A float price value, a public catalog AJAX handler without owner check, a service query without `site()`.
- A documented breaking change in a MAJOR release.
- Hungarian notation, static singletons, `$obElement` naming.
- Untouched legacy code that the PR neither modifies nor newly reaches.

## 8. Test environment and verification

Run the commands below from the root of the October application where this plugin is installed at `plugins/lovata/goodnews`. If the application runs in a container, run them inside that container. `<test_database>` is a separate database for tests; the local environment defines its name.

### Environment

- Start the local environment the way its own documentation describes.
- Several projects can run on one host. Before running a command, confirm that the application or container belongs to the project you are working on.
- Never run commands in another project's environment, and never stop it without the user's approval.
- The sibling `plugins/lovata/toolbox` must be on the version the branch requires. If that version is unreleased, use its feature branch.

### Test database

- **Tests run only against a separate test database.** `phpunit.xml` does not override the database, so unless the environment sets one for tests, they run against the development database and delete its tables. Set `DB_DATABASE` explicitly on every run.
- Before the first run, confirm the real connection. The command must print the test database name:

  ```bash
  DB_DATABASE=<test_database> php artisan tinker --execute='echo DB::selectOne("select database() as db")->db;'
  ```

- The plugin does not create the test database. If it is missing or access is denied, stop and tell the user. Never fall back to the development database.

### Running tests

```bash
# Whole suite
DB_DATABASE=<test_database> vendor/bin/phpunit -c plugins/lovata/goodnews/phpunit.xml

# One test class
DB_DATABASE=<test_database> vendor/bin/phpunit -c plugins/lovata/goodnews/phpunit.xml --filter ArticleTest
```

If Xdebug slows the run, add `XDEBUG_MODE=off`. The suite also runs the Toolbox tests from `plugins/lovata/toolbox/tests/unit`, so failures can come from Toolbox.

### Verifying a change

1. Take a baseline on the base branch before the change, and record the list of failing tests, not only their number.
2. Run the suite on the branch and compare. A change has no regressions when the list of failures matches the baseline.
3. A test for a bug fix must fail without the fix and pass with it.
4. After a new `updates/version.yaml` entry, run `php artisan october:migrate` and `php artisan cache:clear`.
5. Check the changed behaviour on the development environment against the issue's acceptance criteria: backend forms on create and update screens, with the feature on and off; frontend output; cache updates without a manual clear; delete and restore paths.
6. Check for regressions: pages the change does not target render as on the base branch, and no new errors appear in the logs.

### Reporting in the pull request

- Give the exact test command and a table of tests, errors and failures for the baseline and the branch.
- List remaining failures with their cause, and state that they match the baseline.
- List what was checked on the development environment, and name what was not checked, for example browser-only steps.
