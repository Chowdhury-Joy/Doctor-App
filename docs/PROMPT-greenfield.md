# Prompt — Build From Scratch

> Use this to start a fresh codebase. Self-contained; assumes no existing code.
> Read this whole document before writing any code.

---

## 1. What you are building

A multi-tenant SaaS platform for **private doctor chambers and diagnostic clinics in Bangladesh**.

Each tenant gets a subdomain (`{tenant}.domain.com`) hosting:
1. **Their own marketing website** — a real landing page with content they control.
2. **A booking flow** at `/book`, reached by a call-to-action from that website.
3. **A live serial/queue system** so patients can see how far along the queue is.

One codebase. One database. Tenant-scoped data. Not separate builds per client.

### The core business insight

Setup time per client is the margin. Onboarding a new clinic — create tenant, assign subdomain, pick layout, populate content, go live — must be achievable in **under 30 minutes**. Design every decision against that target.

---

## 2. Two productised tiers

| | `solo` | `clinic` |
|---|---|---|
| Doctors | Exactly one | Many |
| Chambers | One | Many |
| Lab / diagnostic tests | Not available | Available and **bookable** |
| Landing page emphasis | The doctor (person-led) | The facility + services |

- Stored on `tenants.plan_tier`, constrained to exactly these two values.
- Tier gates capability; a **`feature_flags` JSON column overrides it** per tenant. A solo doctor who buys lab tests gets the flag, not a tier change.
- All capability questions go through **one accessor** that checks the flag first and falls back to the tier default. Never scatter `plan_tier === 'clinic'` comparisons through the codebase.
- Tier downgrades that would orphan data must be refused with a clear explanation, never silently hide records.

### Third path — bespoke builds

A large clinic or high-profile doctor may want a custom site. Deliverable from **this same repository** through configuration, or at most one added component file. Never a fork, never a per-client branch, never a second deployment.

Preference order for any customisation request:
1. **Theme tokens** (colours, fonts, radius, spacing) — zero code.
2. **Section composition** (which sections, what order, which variant) — zero code.
3. **A bespoke layout component** in this repo, selected via `layout_id` — one file, assigned from a dropdown.
4. **`feature_flags`** for capability toggles.
5. **`custom_code`** for analytics/pixels, super-admin approval-gated.

**The rule: customisation is data first, one component file second, never a fork.** A request that seems to need a branch means one of the layers above is missing a knob — add the knob.

---

## 3. Stack (fixed — do not substitute)

- **Backend**: Laravel 12, PHP 8.3+
- **Database**: MySQL 8. Tenant isolation enforced at the **application layer** via Eloquent global scopes — there is no database-level RLS.
- **Multi-tenancy**: `stancl/tenancy`, subdomain identification, **single shared database** (do not enable the database-per-tenant bootstrapper).
- **Admin panels**: Filament v4 — two separate panels: Super Admin (platform owner) and Tenant Admin (clinic staff).
- **Patient frontend**: Inertia.js 2.0 + Vue 3, Tailwind.
- **PWA**: `vite-plugin-pwa`, with a **dynamically generated per-tenant manifest** (name, icon, theme colour from tenant branding). The plugin generates the service worker only; do not let it emit a competing static manifest.
- **Payments**: bKash, Nagad, SSLCommerz — **server-to-server webhook verification only**, never client-side redirect confirmation.
- **SMS**: out of scope for v1. Patient notification is via WhatsApp `wa.me` deep links (no API integration).

---

## 4. Build this first, once, correctly

**Tenant isolation is the single most important thing to get right, before any feature.**

With no RLS backstop, the application layer is the only thing standing between a bug and cross-tenant data leakage.

Before building any tenant-owned model:

1. Build a reusable `BelongsToTenant` trait that adds a global scope filtering on `tenant_id`, and auto-assigns `tenant_id` on create from the current tenant context.
2. Apply it consistently from the **first** migration onward.
3. Add a **composite unique key `(tenant_id, id)`** to every tenant-owned table, and make every cross-table foreign key composite: `(tenant_id, doctor_id)` references `doctors (tenant_id, id)`. This gives you a database-level guarantee that a record can never reference another tenant's row, even if the application layer is bypassed.
4. Write a test that seeds **two tenants** and asserts zero cross-tenant leakage across **every** scoped model. Extend it every time you add a model. This test gates all other work.

Retrofitting this after several models exist is dramatically more expensive than establishing it up front.

**Trap to avoid:** Laravel's `WithoutModelEvents` trait in seeders disables the `creating` hook that assigns `tenant_id`, causing every seeded insert to fail a NOT NULL constraint. Do not use it in seeders that create tenant-scoped records.

---

## 5. Data model

### Central
- **`tenants`** — stancl standard, plus: `template_id`, `layout_id`, `custom_code` (nullable), `custom_code_approved_at` (must be set before custom code renders), `billing_status` (`active` / `read_only`), `plan_tier` (`solo` / `clinic`), `feature_flags` (JSON).
- **`domains`** — stancl standard.
- **`users`** — plus `tenant_id` (nullable) and `role` (`super_admin` / `tenant_admin` / `patient`).

> **stancl `VirtualColumn` warning:** the base Tenant model folds any attribute that is not declared a "custom column" into the `data` JSON blob. If you add real columns in a migration but do not declare them, they will never be written, and SQL filters against them will silently match nothing while PHP attribute reads appear to work. Declare every real column explicitly.

### Tenant-scoped — practice
- **`doctors`** — name, photo, specialty, credentials, bio.
- **`chambers`** — name, address, latitude, longitude, hours, contact.
- **`schedule_sessions`** — per chamber + doctor: day of week, session name (morning/evening), start/end time, slot cap.
- **`slot_blocks`** — date-specific overrides for holidays and vacation mode. Must support **both** doctor-level blocks and chamber-level blocks (chamber-wide closure, with `doctor_id` null).

### Tenant-scoped — diagnostics (`clinic` tier)
- **`lab_tests`** — name, slug, description, preparation instructions (e.g. "12 hours fasting"), sample type, price, turnaround time, `is_active`, display order.
- **Lab collection scheduling** — lab tests are **not** doctor sessions. Model collection windows separately (day of week, start/end time, capacity). Do not overload the doctor session table with nullable doctor columns.

### Bookings — one pipeline, two bookable types

Write the queue/serial/locking/payment pipeline **once**. Make the booking polymorphic over what is being booked (a doctor session occurrence, or a lab collection slot).

- **`bookings`** — **UUID primary key** (never expose a sequential ID in any URL, SMS, or WhatsApp message). Patient name, patient phone, tenant, polymorphic bookable reference, booking date, session/slot reference, `serial_number` (sequential **within the day**, fine for internal display), `status` (`waiting` / `in_chamber` / `completed` / `cancelled`), `payment_status`, `payment_reference`.
- **`booking_lab_tests`** — line items for multi-test bookings: booking, lab test, and **`price_at_booking`**.
- **`payment_transactions`** — gateway, `transaction_id`, webhook payload, `verified_at` (set **only** by the server-side webhook handler, never a frontend callback). Unique on `(gateway, transaction_id)` for idempotency.

### Multi-test booking

A patient books one or more tests for a single visit. This does **not** complicate the polymorphism — the *bookable* is the collection slot; the tests are **line items on the booking**.

- **Snapshot the price** on each line item. Test prices change; a booking made last month must still show what the patient was quoted.
- **One serial number per booking**, not per test. One patient, one queue position.
- **Capacity counts bookings, not line items.** A patient booking five tests consumes **one** collection slot — one person arriving for one sample collection. Getting this wrong silently destroys capacity for everyone else.
- **Total price** is the sum of line items; a single payment transaction covers the booking.
- **Cancellation is whole-booking** in v1. Do not build per-test cancellation.
- **Preparation instructions must aggregate** — show the union of every selected test's prep instructions on the ticket page and in the confirmation, prominently.
- **Conflicting prep is a real clinical risk** (one test requiring fasting alongside one requiring a recent meal). v1: display all instructions clearly and let staff catch it. Leave room to mark test pairs incompatible later; do not build that now.

---

## 6. Booking engine — correctness requirements

These are the parts that break in production if done casually.

### Pessimistic locking
Lock the session/slot row (`lockForUpdate`) inside a transaction during checkout. Online bookings and staff-entered walk-ins race against each other constantly in real clinics.

### Walk-ins use the same path
Staff-entered bookings must go through the **exact same service** as online bookings. A separate admin create path that writes the model directly will bypass the lock and the cap, and will produce duplicate serial numbers.

### Serial numbers
Derive the next serial from **`MAX(serial_number) + 1`** for that session and date, **counting cancelled rows**. Deriving it from `COUNT(active bookings) + 1` looks correct until a booking is cancelled — then the next patient is issued a number a waiting patient already holds. Add a unique constraint on `(tenant_id, session, booking_date, serial_number)` as a database backstop.

### Validation at booking time
- The requested date must fall on the session's configured **day of week**.
- The date must not be covered by a slot block — checking **both** doctor-level and chamber-level blocks.
- Capacity must be enforced, supporting **both** per-session and per-day caps. Which mode applies is chosen per tenant at onboarding.

### Queue status
"Now serving" means the booking currently **`in_chamber`** — not the highest completed number. If #3 is completed and #4 has not been called, the screen must not claim #3 is being seen.

### Vacation mode
Toggling a block for a date with existing bookings must **prompt to cancel them and produce a notification list** (WhatsApp deep links per patient), never silently orphan them.

---

## 7. The landing page

Each tenant gets a real website, not a booking form with a header.

### Architecture
A landing page is a **composition of sections**. The **layout** decides structure and chrome; **sections** carry content. Neither embeds booking logic.

**Ship 5 layout shells**: hero-first, sidebar, card-stack, minimal, clinic-style.

**Section library** (each a shared component, independently toggleable and reorderable per tenant):

| Section | Notes |
|---|---|
| Hero | Name, tagline, primary CTA → `/book` |
| About | Rich text |
| Doctors | Grid; on `solo` becomes a single doctor feature block. Cards open a doctor detail view. |
| Lab tests / Services | `clinic` only. Price + prep instructions; each has "Book this test". |
| Chambers / Locations | Address, hours, map |
| Why choose us | Icon + text feature list |
| FAQ | Accordion |
| Contact | Phone, WhatsApp CTA, email, map |
| Footer | Hours, socials |

**Rules:**
- All five layouts consume the **same** section components. Layout differences are structural and CSS only.
- Section order and visibility live in tenant configuration (JSON), never in code.
- Every layout renders every section type — switching layout must never lose content.

---

## 8. The booking flow (`/book`)

A dedicated route, not a modal. Landing CTAs link to it.

1. **What are you booking** — doctor or lab test. Skipped entirely on `solo`.
2. **Choose** the doctor, or **select one or more lab tests** (multi-select, with a running total and combined preparation instructions shown as tests are added).
3. **Choose date** — the date input must be **locked to the session's weekday**. Auto-fill the next valid occurrence and prevent selecting an invalid day. Do not accept any date and reject it after submission.
4. **Choose session/slot** — with live remaining-capacity feedback ("4 of 10 slots left"), and the submit button disabled when full.
5. **Patient details** — name, phone with Bangladeshi format validation.
6. **Confirm** → redirect to the ticket page.

**Ticket page** (`/bookings/{uuid}`): serial number, live "now serving" via low-bandwidth polling, appointment details, payment status, a **visible copyable link** and/or QR (telling a patient to "save this link" without showing them one is useless), and the PWA install prompt. For lab bookings, list every booked test with the **aggregated preparation instructions** shown prominently — this is the screen the patient will actually re-read the night before.

Support deep links so landing CTAs can skip steps: `/book?doctor={uuid}`, `/book?test={uuid}`.

---

## 9. Admin panels

### Super Admin
- Tenant CRUD via an **onboarding wizard** — this is the highest-value screen in the product; the 30-minute target lives or dies here.
  - Identity: subdomain, clinic name, contact/WhatsApp number, billing status.
  - Configuration: tier (`solo`/`clinic`), layout, slot cap mode, theme colour, custom code + approval.
  - On create: auto-provision the subdomain record and sensible starter data (a chamber, a doctor, a session) so the site is immediately viewable.
- Billing status toggle, with `read_only` enforced in middleware: **block new bookings, allow existing dashboard access, keep the public site viewable.**
- Custom code approval — a real draft/approved gate, not a warning label.

> **Filament v4 trap:** do not combine the page-level `HasWizard` concern with a `Wizard` component inside the form schema. `HasWizard::form()` replaces the entire form with `Wizard::make($this->getSteps())`; if `getSteps()` is not overridden it returns an empty array and the page renders blank. Pick one approach.

### Tenant Admin
- **Daily roster** with one-tap status control (call to chamber → mark completed). Default the view to today, waiting first. Show resolved names, never raw foreign-key IDs.
- **Walk-in entry** — through the shared booking service.
- **Schedule management** — hours, slot blocking, vacation mode with the cancel + notify flow.
- **Lab tests** (clinic tier) — catalogue and collection windows.
- **Website content authoring** — identity, theme, section toggles and ordering, and per-section content. The audience is clinic reception staff: prefer a small number of clear fields over a flexible page builder. Include a prominent "View site" link.
- Tenant admins may **draft** custom code; only a super admin can approve it for rendering.

---

## 10. UI/UX requirements

- **Mobile-first.** Most patients are on mid-range Android over mobile data.
- **Low bandwidth.** Compress and lazy-load images, keep landing JS small, avoid heavy fonts.
- **The CTA is the point.** "Book Appointment" is unmissable, above the fold, and repeated at the end of the page.
- **Fail before submit, not after.** Capacity, weekday validity, and blocked dates are surfaced while the patient is choosing.
- **No dead ends.** Empty states — no doctors configured, no tests, fully booked, read-only billing — must all say something useful. A read-only tenant must show a real message, not a dead button.
- **Accessibility basics.** Real labels, visible focus, adequate contrast, keyboard-navigable booking flow.

### 10.1 Language (decided)

**Static UI strings — professionally translated, shipped in the repo.**
- Ship both `en` and `bn` from the start. Roughly 200–300 strings across the patient site, booking flow, and admin panels.
- **Translate manually. Do not use a translation API for these.** Bangladeshi patients use English loanwords for exactly this domain — সিরিয়াল (serial), অ্যাপয়েন্টমেন্ট, ডাক্তার, চেম্বার. Machine translation renders "serial" as ক্রমিক or ধারাবাহিক: technically correct, reads like a government form. A native speaker gets this right instinctively. It is a one-time, bounded cost.
- The patient site gets a **language toggle**, defaulting to the tenant's configured default locale (a tenant setting).

**Tenant-authored content — single field, no translation.**
- Clinic name, tagline, about copy, doctor bios, lab test names, prep instructions, FAQ entries: **one field each**, in whatever language the clinic types. Bengali, English, or the mixed register normal on Bangladeshi clinic pages.
- **No duplicate `_bn` fields, and no machine translation in this path.** Forcing tenants to fill every field twice directly attacks the 30-minute onboarding target — and auto-translating **lab test preparation instructions is a safety risk**: a mistranslated fasting requirement means a wasted test, a wasted trip, or a clinically wrong result.
- If a large client later wants genuinely bilingual content, that is a per-tenant `feature_flags` capability with nullable `_bn` variants falling back to the primary. It fits the customisation seam in §2. Do not build it in v1.

**Non-negotiable from the first commit:** route **every** user-facing string through a translation helper — `__()` server-side and the equivalent in Vue. Never hardcode a user-facing string, even while only English exists. The expensive part of i18n is never the translating; it is extracting hardcoded strings from dozens of templates afterwards.

**Verify early:** check Filament v4's Bengali (`bn`) coverage for the admin panels. It ships translations for many locales but `bn` completeness is uncertain. If reception staff have limited English, panel translation matters more than the patient site — scope any gap up front.

---

## 11. Security (non-negotiable — verify before any demo)

- Every public booking/serial URL uses a UUID or hash. Never a sequential ID, anywhere.
- Payment status changes **only** via authenticated server-to-server webhook with per-gateway signature verification. Verification must **fail closed** — if a secret is missing, reject. Never ship hardcoded fallback secrets.
- Webhook handling must be **idempotent** — gateways retry as normal behaviour.
- Booking submission is IP rate-limited at the middleware level.
- Custom code requires super-admin approval before rendering.
- **Panel authorisation is explicit.** Implement `canAccessPanel()` so a self-registered patient cannot reach either admin panel, and a tenant admin cannot open another tenant's panel. Without this, any registered user is a de facto super admin.
- **Central routes must not resolve on tenant subdomains**, and tenant routes must not resolve on the central domain. Apply the guard per route group — a global middleware that 404s non-central hosts will kill every tenant route including payment webhooks.
- Two-tenant seeded leakage test passes across every scoped model before onboarding any real client.

---

## 12. Deployment target

Shared **cPanel** hosting, not a VPS. Design around this from day one:

- **No persistent background processes.** Queue workers run via cron: `* * * * * php artisan queue:work --stop-when-empty`. Never `queue:work` as a daemon.
- **1 GB memory ceiling.** Keep PHP-FPM workers and the connection pool conservative.
- SSH available for Composer, artisan, migrations.
- **Wildcard subdomain + SSL** required for `{tenant}.domain.com`. Confirm AutoSSL covers wildcards; if not, front with Cloudflare in **Full** mode (never Flexible).
- **Build frontend assets locally or in CI and deploy compiled output.** Never run `npm run build` on the production box at this memory ceiling.
- Declare every npm package you import. A package that only resolves as a transitive dependency will break a clean CI install.

---

## 13. Seed data

Seed **two tenants** so both tiers are developed against simultaneously:

- **Solo chamber** — one doctor, one session type, low slot cap.
- **Clinic** — multiple doctors, multiple chambers, lab tests, higher slot cap, and **at least two doctors sharing a weekday** so multi-doctor scheduling conflicts surface in development rather than at a client.

Seed a super admin plus one tenant admin per tenant. Give each tenant real content (name, tagline, about copy) so the landing pages are not rendering fallbacks.

---

## 14. Build order

**Phase 1 — Foundation**
1. Laravel + stancl/tenancy, central + tenant connections, single-database configuration. **Set up i18n plumbing now** (`en` + `bn` locale files, translation helpers wired server-side and in Vue) so no string is ever hardcoded.
2. **`BelongsToTenant` + composite foreign keys + the two-tenant leakage test.** Nothing else starts until this passes.
3. Roles, `canAccessPanel`, central/tenant route separation.
4. Super Admin panel: tenant CRUD, onboarding wizard, billing status with read-only enforcement.

**Phase 2 — Booking engine**
5. Scheduling model; per-session and per-day caps.
6. Booking creation with pessimistic locking; `MAX + 1` serial allocation; UUID references.
7. Live queue status endpoint.

**Phase 3 — Staff dashboard**
8. Daily roster, one-tap status control.
9. Walk-in entry through the shared service.
10. Schedule management, slot blocking, vacation mode with cancel + notify.

**Phase 4 — Website & PWA**
11. Section library + 5 layout shells, all consuming shared components.
12. Tenant content authoring in the tenant admin.
13. `/book` route; landing CTAs point at it.
14. Custom code injection, approval-gated.
15. Per-tenant PWA manifest, add-to-home-screen.

**Phase 5 — Diagnostics & payments**
16. Lab tests: catalogue, collection windows, **multi-test line items**, unified into the existing booking pipeline.
17. bKash/Nagad/SSLCommerz webhooks with real signature verification.
18. WhatsApp `wa.me` confirmation deep links.
19. Bengali translation pass + patient-site language toggle.

Do not skip ahead. Phase 1 step 2 is load-bearing for the entire product.

---

## 15. Explicitly out of scope for v1

Do not build: e-commerce, WhatsApp API automation, PDF report generation, doctor commission tracking, custom domain mapping (subdomains only), telemedicine, SMS.

Leave the `feature_flags` JSON column on `tenants` so these can be toggled on later without a schema migration — but do not build the features.
