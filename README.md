# LTKL CMS

Content management system and public read API for the LTKL (Lingkar Temu Kabupaten Lestari) website.

Editors manage content through a Filament admin panel; the website reads it over a JSON API. Every piece of editorial content is bilingual — English and Indonesian.

## Stack

| | |
|---|---|
| PHP | ^8.4 |
| Laravel | ^12.0 |
| Admin panel | Filament ^4.0 |
| Frontend tooling | Vite 7, Tailwind CSS 4 |
| Database | MySQL by default (see `.env`) |

Notable packages: `bezhansalleh/filament-shield` (roles and permissions), `dotswan/filament-map-picker` (kabupaten coordinates), `rawilk/filament-quill` (rich text), `tucker-eric/eloquentfilter` (post filtering), `inerba/filament-db-config` (site settings), `pxlrbt/filament-activity-log`.

## Getting started

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan storage:link      # public disk is used for all uploads
php artisan filament:assets   # publish panel assets from packages
```

Create an admin user, then run the app:

```bash
php artisan make:filament-user
composer run dev              # serve + queue worker + vite, all at once
```

The panel lives at **`/administrator`**. Uploaded files are served from the `public` disk, so `storage:link` is required or every image in the panel and the API will 404.

## Testing

```bash
composer run test             # clears config, then runs Pest
php artisan test tests/Feature/PillarApiTest.php
```

Tests use Pest with an in-memory SQLite database. Feature tests opt into `RefreshDatabase` individually — the global `uses()` in `tests/Pest.php` is commented out, so a new test file that touches the database must declare it:

```php
uses(RefreshDatabase::class);
```

## Bilingual content

Every translatable field is a pair of columns: the bare name holds English, the `_id` suffix holds Indonesian.

```
title / title_id      slug / slug_id      description / description_id
components / components_id
```

Forms present these as two tabs, **Indonesian first**. Fields that are not language-dependent — coordinates, numbers, dates, images, relations — are stored once, outside the tabs.

Where a repeatable list is translatable it is stored as one JSON column per language (`commodities` / `commodities_id`), so each language keeps an independent list. The exception is a list anchored to another record: pillar in-practice examples live in their own table with both languages on one row, because two separate lists could point at different kabupatens.

The API returns both languages side by side and lets the frontend choose.

## CMS structure

**Contents** — Pages & Menus, Posts, Contact Us
**Masters** — Collections, Kabupaten, Links, Files, Pillars, Participation Pathways
**Administration** — Users, Roles
**Settings pages** — Website, Seo (both backed by `filament-db-config`)

### Page builder

Pages, Posts and Participation Pathways each carry a `components` JSON column edited through a Filament builder, with a separate list per language.

| Resource | Blocks |
|---|---|
| Pages | `banner`, `collection`, `latest_news`, `lead_text`, `paragraph`, `post_index`, `text_image`, `single_image`, `statistic`, `your_role` |
| Posts | `excerpt`, `paragraph`, `lead_text`, `quote`, `single_image` |
| Participation Pathways | `lead_text`, `text_image`, `stats` |

The `collection` block on pages does not embed data. It stores a choice from `App\Enums\CollectionComponentSource` — Kabupaten Map, Pillars, or Participation Pathways — and the frontend fetches the matching endpoint. `getEndpoint()` on that enum is the single source of truth for the mapping.

## API

All endpoints are public, read-only, and return active records only. Responses are wrapped in `data`.

| Method | Path | Notes |
|---|---|---|
| GET | `/api/pages` | |
| GET | `/api/page/{slug?}` | |
| GET | `/api/navigations` | Menu tree |
| GET | `/api/posts` | Paginated; `meta` holds the pagination |
| GET | `/api/post/{slug}` | |
| GET | `/api/collections` | Filter with `?type=` |
| GET | `/api/collection/{slug}` | |
| GET | `/api/kabupatens` | `?search=` matches either language |
| GET | `/api/kabupatens/map` | Slim map pins; skips records without coordinates |
| GET | `/api/kabupaten/{slug}` | |
| GET | `/api/pillars` | |
| GET | `/api/pillar/{slug}` | Includes in-practice examples |
| GET | `/api/participation-pathways` | |
| GET | `/api/participation-pathway/{slug}` | |
| GET | `/api/settings` | |
| POST | `/api/contact-us` | Only write endpoint |

Detail endpoints match **either** the English or the Indonesian slug.

`/api/posts` supports `featured`, `search`, `sort`, `order`, `type`, `post_tags`, `post_topics`, `post_kabupatens`, `page`, and `per_page` (max 100), handled by `App\ModelFilters\PostFilter`.

### API conventions

Resource classes in `app/Http/Resources` normalise the payload rather than dumping models:

- Upload paths become full URLs; a missing file returns `null`, not a URL pointing at the storage root.
- `decimal` casts return strings from Eloquent, so numeric fields are cast back to real JSON numbers.
- Repeater state is keyed by UUID in the database and is re-indexed so it serialises as a JSON array.
- Optional block keys are read with `?? null`. Blocks saved before a field existed have no key for it, and an unguarded read would 500 the whole page.
- Nested references stay slim. A kabupaten's `pillars` carries titles and slugs only; the full pillar is at `/api/pillar/{slug}`.

OpenAPI annotations live on the controllers. **No generator is installed** — they are documentation only, and nothing renders them yet.

## Seeders

```bash
php artisan db:seed --class=PostSeeder      # 15 posts, 3 per PostType
php artisan db:seed --class=PillarSeeder    # 3 pillars, idempotent on slug
```

`PillarSeeder` uses `updateOrCreate`, so re-running updates in place. It also attaches practices and kabupatens when those records exist, and no-ops on the relations when they don't.

## Deployment notes

- `php artisan filament:assets` must run on deploy, or panel packages lose their CSS and JS.
- `php artisan storage:link` must exist on the target, or all uploads 404.
- `npm run build` is required; the panel theme is compiled through Vite (`resources/css/filament/administrator/theme.css`).
