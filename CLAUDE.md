# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A custom WordPress theme (`trimbarbers` / "3M Barbers") for a Slovak barbershop in Žilina. The site is essentially a single-page front (`front-page.php`) with a Vue-driven reservation modal (`template_parts/reservation.php`) plus a heavily customized wp-admin used by barbers as a calendar/CRM. All UI copy is Slovak.

The repository is the theme directory only (`wp-content/themes/trimbarbers`); WordPress core, ACF, and other plugins live outside it. ACF is required — most data flows through `get_field` / `update_field`.

## Build / dev commands

`package.json` only declares `scss` and `watch`, but the real tasks are in `gulpfile.js`. Run them via the local gulp install:

```bash
npx gulp sass            # compile assets/sass/main.scss → dist/css/main.css
npx gulp admin-sass      # compile assets/sass/admin/**.scss → dist/css/admin/
npx gulp scripts         # uglify assets/js/**/*.js → dist/js/
npx gulp assets:watch    # watch sass + js
npx gulp production      # sass + admin-sass + minify (main.min.css) + scripts
```

There are no tests, linters, or PHP build steps. `dist/` is gitignored but is what WordPress actually serves — you must run gulp after editing `assets/sass/` or `assets/js/`.

## Cache-busting / versioning gotcha

`functions/functions_theme.php` defines `WP_VERSIONING` (default `FALSE`). While `FALSE`:
- `VERSION` is `time()` (every request busts cache).
- `main.css` (unminified) is enqueued instead of `main.min.css`.

Flip to `TRUE` for production — otherwise the minified CSS produced by `gulp production` is never served.

## Asset enqueue conventions

- Front-end JS components live in `assets/js/components/` and are compiled to `dist/js/components/`. Enqueue them via `enqueue_component($name, $phpVars)` (defined in `functions_theme.php`). Any handle prefixed with `comp-` gets `type="module"` added by a `script_loader_tag` filter, so component files are loaded as ES modules.
- `wp_localize_script` exposes PHP data to JS as the global `PHPVars`.
- Most third-party libs (Vue 3, Axios, Moment + sk locale, Swiper, AOS, lord-icon) are pulled from CDN in `enqueue_custom_scripts_links()` / `admin_enqueue_scripts()` — there is no bundler.
- Admin styles/scripts live under `assets/sass/admin/` and `assets/js/components/admin/`. The `barber` and `together-barber` roles also load `admin/barber_role.css`.

## Domain model (custom post types)

Registered in `functions/functions_posttypes.php`:

- **`service`** — offered haircuts/services. ACF fields: `serv-price`, `serv-description`, `serv-duration` (minutes, drives time-slot math).
- **`appointment`** — bookings or "free" blocks on a barber's calendar. ACF fields: `appointment_datetime` (group with `from`/`to`), `appointment_type` (`appointment` or `free`), `appointment_barber` (user ID), `appointment_service` (post object), `appointment_customer` (group: id/name/email/phone), `appointment_note`. Two registered post-meta keys: `cancel_token` (random string for the email cancel link) and `has_been_reminded` (bool, set by the daily reminder cron).
- **`customer`** — auto-upserted when an appointment is booked (matched by `cust-email`). ACF fields: `cust-name`, `cust-email`, `cust-phone`, `cust-last_appointment`. The admin list table is heavily customized — title/permalink are hidden and a custom JOIN/WHERE filter (`customer_search_join` / `customer_search_where`) makes the search box query post meta instead of post titles.

When editing the appointment list screen, `template_parts/admin/calendar.php` is injected via `all_admin_notices` — that page is the FullCalendar UI barbers actually use.

## User roles

`functions/functions_userroles.php` removes every default role except `administrator` on `init`, then re-adds `barber` and `together-barber` as clones of the admin role with their menus stripped (Plugins, Users, Settings, ACF Fields, etc.). On admin login, barbers are redirected from `index.php` to `edit.php?post_type=appointment`. Don't add capabilities by mutating `barber` directly — the `remove_existing_roles` hook will wipe non-admin roles on every request.

`getBarbers()` in `functions_helper.php` only returns users with `worktime`/`lunchtime` ACF fields populated — a barber without working hours configured won't appear in the booking flow.

## Reservation logic

`functions/functions_reservation.php` — `get_available_dates` (AJAX, public) is the core booking algorithm:

1. Iterates the next 60 days for each barber (or one specific barber if `barberID` is passed).
2. Reads per-user ACF: `worktime` (start/end), `lunchtime` (start/end), `weekend_work` (saturday/sunday booleans).
3. Walks 30-minute slots from `worktime.start - 30min` until `worktime.end - serviceDuration`, skipping any slot that overlaps lunch or an existing appointment for that barber.
4. Multi-day appointments are split into per-day busy ranges (rare but handled).
5. Returns a nested `[year][month][YYYY-MM-DD]` structure. A day's `isAvailable` is `1` if **any** slot is bookable; each slot also carries the list of barbers who can take it (used by the front-end when `barberID = 0` = "any barber").

`make_reservation` (AJAX, public) creates the appointment, generates a 24-char `cancel_token`, picks a random barber from those available for the slot, calls `update_appointment_fields` (which also upserts the `customer` record via `functions_calendar.php`), and emails both the customer and the address in `reservations_email` ACF option.

`functions/functions_calendar.php` mirrors this for admin-side `make_appointment` / `edit_appointment` / `remove_appointment` (calendar drag-drop). `update_appointment_fields` is the single source of truth for writing appointment ACF fields and the customer upsert.

## Email + REST endpoints

- All transactional emails go through `reservation_notification($to, $type, $id, $sendingToBarber)` in `functions/functions_email.php`. `$type` is one of `new`, `update`, `notification`, `cancel-customer`, `cancel-admin` and selects branches inside `template_parts/email_template.php`. Subject line is built from the service title.
- `functions/functions_rest.php` registers three public REST routes under `api/v1/`:
  - `GET /cancel-reservation/?t=<token>&i=<id>` — token-gated cancel link from emails. Redirects to `/?c=1` on success.
  - `GET /notify-customers/?t=<static-token>` — meant to be hit by an external cron (token is hard-coded). Sends 1-day-ahead reminders to appointments whose `has_been_reminded` meta is unset/false, then sets it to true. **Set `max_execution_time` is bumped because this can be slow.**
  - `GET /generate-ics/?s=...&e=...&n=...&h=<static-hash>` — produces an `.ics` file via `classes/ICS.php`.

The static tokens in the REST routes are not secrets in the security sense — treat them as obfuscation only. Don't paste them into commits/PRs/screenshots if exposing the repo.

## Frontend conventions worth knowing

- `header.php` and the reservation modal use Vue template syntax (`:class`, `@click`, `v-if`) directly inline — Vue is mounted globally over the page. Don't escape `:`/`@` attributes when editing PHP templates.
- `svgIcon($path, $attrs)` (in `functions_helper.php`) inlines SVGs; pass file paths via `icon_path(false)` / `image_path(false)` (server paths) — the `_uri` variants return URLs.
- Slovak day/month name helpers (`getDayName`, `getMonthName`, `getShortDayName`) are used in PHP-rendered calendar/email content; on the JS side use moment + the `sk` locale enqueued via CDN.
- `js_json_decode($json)` strips backslashes before `json_decode` — WordPress's `wp_unslash` would normally be the right call, but existing AJAX handlers all use `js_json_decode`, so match that for consistency.

## Author / context

Theme `style.css` author is "Synapps s.r.o. - Michal Čečko" — this is a single-client agency project, not a published/public theme.