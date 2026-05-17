# 3M Barbers — WordPress theme

Custom WordPress theme + heavily customised wp-admin for [3M Barbers](https://3mbarbers.sk), a barber shop in Žilina (SK).

The repo is the **theme directory only** — WordPress core, ACF, and other plugins live outside it.

## What it does

- **Public site** — single-page front (`front-page.php`) with a Vue-driven reservation modal
- **Reservation engine** — `get_available_dates` walks per-barber working hours, lunch breaks, weekend rules, and existing appointments to compute bookable slots for the next 60 days
- **wp-admin as a barber CRM** — barbers see a FullCalendar UI of their appointments at `/wp-admin/edit.php?post_type=appointment` (everything else is stripped from their menu)
- **Customer auto-upsert** — each booking creates/updates a `customer` post (matched by email) with appointment history
- **Transactional emails** — booking confirm, update, cancel; "tomorrow" reminders via cron-hit REST endpoint
- **ICS export** — `/wp-json/api/v1/generate-ics/` produces an `.ics` for a single appointment

## Stack

- **WordPress 6.x** + **PHP 8.x**
- **ACF Pro** — most data flows through `get_field` / `update_field`
- **Vue 3** mounted globally over the page (loaded from CDN)
- **FullCalendar** for the admin calendar view
- **Tailwind-free** — custom Sass under `assets/sass/`
- **Gulp** for asset build (sass / scripts / minify)

## Build

```bash
npm install
npx gulp sass            # compile assets/sass/main.scss → dist/css/main.css
npx gulp admin-sass      # compile assets/sass/admin/**.scss → dist/css/admin/
npx gulp scripts         # uglify assets/js/**/*.js → dist/js/
npx gulp assets:watch    # watch both
npx gulp production      # full production build (minified)
```

Set `WP_VERSIONING = TRUE` in `functions/functions_theme.php` for production — otherwise every request busts the asset cache.

## Required `wp-config.php` constants

```php
define('NOTIFY_CUSTOMERS_TOKEN', '<random>');  // gates the daily reminder REST endpoint
define('ICS_HASH',               '<random>');  // gates the generate-ics REST endpoint
```

(If unset, both endpoints fail closed — safe default.)

## Required directory

`instagram/accessToken.json` — gitignored, generated/refreshed by `instagram/refreshAccessToken.php`. On a fresh deploy, place a current long-lived IG token file there before the next IG fetch runs.

## Custom post types

- **`service`** — offered haircuts (price, duration, description)
- **`appointment`** — booking or "free" block on a barber's calendar
- **`customer`** — auto-upserted, custom admin list (search joins post meta)

## User roles

`administrator` plus two custom roles: `barber` and `together-barber` — cloned from admin then stripped of Plugins/Users/Settings/etc. on `init`. Barbers without `worktime`/`lunchtime` ACF fields don't appear in the booking flow.

## REST endpoints (`api/v1/`)

| Endpoint | Auth | Purpose |
|---|---|---|
| `GET /cancel-reservation/?t=<token>&i=<id>` | per-appointment `cancel_token` (in email) | customer cancels from email link |
| `GET /notify-customers/?t=<NOTIFY_CUSTOMERS_TOKEN>` | static (wp-config) | external cron, sends tomorrow's reminders |
| `GET /generate-ics/?s=...&e=...&n=...&h=<ICS_HASH>` | static (wp-config) | downloadable `.ics` for an appointment |

## License

[MIT](LICENSE) © Michal Čečko
