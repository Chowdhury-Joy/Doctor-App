# Prompt — Evolve the Existing Codebase

> Use this when continuing work on the current repo. It assumes the code already on disk.
> Read this whole document before writing any code.

---

## 1. Product

A multi-tenant SaaS for private doctor chambers and diagnostic clinics in Bangladesh. Each tenant gets a subdomain with **their own marketing website** plus a booking and serial/queue system. One codebase, one database, tenant-scoped data.

### Two productised tiers

| | `solo` | `clinic` |
|---|---|---|
| Doctors | Exactly one | Many |
| Chambers | One | Many |
| Lab / diagnostic tests | Not available | Available and **bookable** |
| Landing page emphasis | The doctor (person-led) | The facility + services |

The tier is stored on `tenants.plan_tier` and must be **one of exactly `solo` or `clinic`**.

### Third path — bespoke builds

A large clinic or a high-profile doctor may want a custom site. This must be deliverable **from this same repository** via configuration, or at most one added component file. Never a fork, never a branch per client, never a second deployment.

---

## 2. Decisions already locked (do not re-litigate)

1. **Tier vocabulary is `solo` / `clinic`.** The current `basic` / `premium` options are wrong and must be removed.
2. **Lab tests are bookable**, with their own queue/serial — not a display-only catalogue.
3. **Tenant admins author their own landing page content.** The agency does the first-time setup during onboarding; the tenant maintains it afterwards.
4. **Booking lives at its own `/book` route**, not a modal. The landing page links to it.
5. **Multi-test booking is supported.** One booking may contain several lab tests — one visit, one serial number, one payment, price summed. This is the norm at Bangladeshi diagnostic centres, not an edge case.
6. **Bilingual UI, single-language content.** Static interface strings ship professionally translated in Bengali and English. Tenant-authored content is a **single field** in whatever language the clinic chooses — no duplicate fields, no machine translation. See §5.1.

---

## 3. Current state of the code (verified)

### Stack (already installed, do not substitute)
- Laravel **12.64.0**, PHP 8.3
- Filament **v4.12.3** — two panels: `/super-admin`, `/tenant-admin`
- `stancl/tenancy` v3.10 — subdomain identification, **single shared database**
- MySQL 8
- Inertia.js 2.0 + Vue 3, Tailwind
- `vite-plugin-pwa` (service worker only; manifest is generated per tenant by a route)

### What works today
- **Tenant isolation**: `BelongsToTenant` trait + `TenantScope` global scope, applied to every tenant-owned model. Backed by **composite foreign keys** `(tenant_id, id)` on every tenant table, so cross-tenant references are rejected at the database level too.
- **Auth/roles**: `users.role` (`super_admin` / `tenant_admin` / `patient`) + `users.tenant_id`. `User::canAccessPanel()` gates both panels; a tenant admin cannot open another tenant's panel.
- **Booking engine** (`App\Services\BookingService`): pessimistic row lock on the session, weekday validation, slot-block (vacation) checks at both doctor and chamber level, per-session **and** per-day caps, serial number derived from `MAX(serial_number) + 1` so cancellations never cause duplicates. Unique constraint `(tenant_id, schedule_session_id, booking_date, serial_number)` as a DB backstop.
- **Walk-ins** go through the same `BookingService` (Filament `CreateSerial::handleRecordCreation`), so they respect the same lock and caps.
- **Public references** are UUIDs — no sequential IDs in any URL.
- **Live queue**: polled `now_serving` endpoint; patient ticket page updates without refresh.
- **Payments**: webhook-only status changes, fail-closed signature verification, idempotent on `(gateway, transaction_id)`.
- **Billing status**: `read_only` blocks new bookings via middleware and hides the walk-in create action, while the public site stays viewable.
- **Vacation mode**: creating a slot block cancels affected bookings and produces a list of WhatsApp deep links for staff to notify each patient.
- **Custom code**: `custom_code` renders only when `custom_code_approved_at` is set — a real draft/approved gate.
- **Onboarding wizard**: super admin creates a tenant, which auto-provisions a default chamber, doctor, session, and the subdomain record.
- 37 tests passing.

### Known debt you must address
| Issue | Detail |
|---|---|
| Tier vocabulary split | `TenantForm` offers Basic/Premium; the seeder writes solo/clinic. **Nothing reads `plan_tier` at all** — it is currently decorative. |
| `feature_flags` inert | Column exists on `tenants`, never read or written. |
| No lab tests | No model, migration, or UI. Entirely new. |
| Landing = booking form | The 5 "layouts" are header + `<BookingWidget>` + footer. They differ in chrome, not content. There is no website. |
| Thin content model | Tenants have only name, contact phone, theme colour, layout id. No about copy, services, images, address/coords, social links, hours as structured data. |
| Doctor profiles unused | `bio`, `credentials`, `photo` now persist correctly but are never rendered. |
| Webhook schemes are placeholders | Signature logic is structurally correct and fails closed, but the per-gateway algorithms must be replaced with the real bKash / Nagad / SSLCommerz specs, and `config/services.php` has no entries for them. |
| SMS deferred | No SMS gateway. WhatsApp `wa.me` deep links only. |

---

## 4. What to build

### 4.1 Make the tier real

- Replace the `plan_tier` options with exactly `solo` and `clinic`. Migrate any existing rows.
- Introduce a single place that answers tier questions — e.g. a `TenantTier` enum or a small policy/service — rather than scattering `if ($tenant->plan_tier === 'clinic')` across the codebase.
- Tier must gate, at minimum:
  - **Doctors**: `solo` allows one; block creating a second in the tenant admin with a clear message, not a silent failure.
  - **Chambers**: `solo` allows one.
  - **Lab tests**: only available on `clinic`. The whole Filament resource and the landing section should disappear on `solo`.
- **`feature_flags` overrides the tier.** A solo doctor who pays for lab tests gets `feature_flags: {"lab_tests": true}` and the capability turns on without changing their tier. Read capabilities through one accessor that checks flag first, then tier default.
- Changing a tier must never orphan data. Downgrading `clinic` → `solo` with 3 doctors should be refused with an explanation, not silently hide records.

### 4.2 Lab tests (bookable)

New domain objects:

- **`lab_tests`** — name, slug, description, preparation instructions (e.g. "12 hours fasting"), sample type, price, turnaround time, `is_active`, display order. Tenant-scoped.
- **Collection scheduling** — lab tests are *not* doctor sessions. They need collection windows (day of week, start/end time, capacity). Model these separately from `schedule_sessions`; do not overload the doctor session table with nullable doctor columns.

**Booking unification — important architectural call:**

You already have a serial/queue/locking/payment pipeline that is correct and tested. Do **not** write a second one for lab tests. Make the booking record polymorphic over what is being booked:

- A booking points at either a **doctor session occurrence** or a **lab collection slot**.
- Serial numbering, pessimistic locking, cap enforcement, cancellation, payment linkage, and the queue endpoint are written **once** and work for both.
- Keep the existing `serials` table as the base if the migration cost is acceptable; otherwise introduce a `bookings` concept and treat `serials` as a legacy alias. Either way there must be exactly one queue implementation.

**Multi-test booking (locked):**

A patient books one or more tests for a single visit. Note this does **not** complicate the polymorphism — the *bookable* is still the collection slot; the tests are **line items on the booking**.

- **`booking_lab_tests`** line-item table: booking, lab test, and **`price_at_booking`**. Snapshot the price — test prices change, and a booking made last month must still show what the patient was actually quoted.
- **One serial number per booking**, not per test. One patient, one queue position.
- **Capacity counts bookings, not line items.** A patient booking five tests consumes **one** collection slot — it is one person arriving for one sample collection. Getting this wrong silently destroys capacity for everyone else.
- **Total price** is the sum of the line items; a single payment transaction covers the whole booking.
- **Cancellation is whole-booking** in v1. Do not build per-test cancellation.
- **Preparation instructions must aggregate.** Show the union of every selected test's prep instructions on the ticket page and in the confirmation, prominently. A patient booking three tests must see all three sets of requirements.
- **Conflicting prep is a real clinical risk** — one test requiring fasting alongside one requiring a recent meal. v1: display all instructions clearly and let staff catch it. Leave room to mark test pairs as incompatible later; do not build that now.

### 4.3 The landing page (this is the biggest change)

Today's layouts must become real websites. Restructure so a landing page is a **composition of sections**, not a single monolithic template.

**Section library** (each a shared Vue component, each independently toggleable and reorderable per tenant):

| Section | Notes |
|---|---|
| Hero | Clinic/doctor name, tagline, primary CTA → `/book` |
| About | Rich text |
| Doctors | Grid; on `solo` this becomes a single doctor feature block. Cards link to a doctor detail view. |
| Lab tests / Services | `clinic` only (or `feature_flags`). Grid or list with price + prep instructions; each has a "Book this test" CTA. |
| Chambers / Locations | Address, hours, map. |
| Why choose us | Icon + text feature list |
| FAQ | Accordion |
| Contact | Phone, WhatsApp CTA, email, embedded map |
| Footer | Hours, socials, credit line |

**Rules:**
- The **layout** decides structure and chrome. The **sections** carry content. Layouts must not embed content or booking logic — same discipline already applied to `BookingWidget`.
- Section order and visibility live in tenant config (JSON), not in code.
- Every layout must render every section type. A tenant switching layout must never lose content.

**Content model additions** — the tenant needs somewhere to put all this. Extend the tenant's `data` (virtual columns) and/or add a `tenant_content` table for the longer-form pieces. Include at minimum: tagline, about copy, logo, hero image, feature list, FAQ entries, social links, and per-chamber latitude/longitude for maps.

### 4.4 The `/book` route

Move booking off the landing page entirely.

- **`/book`** — the booking flow entry point.
- Step 1 (clinic only): **what are you booking** — see a doctor, or a lab test. Skipped on `solo`.
- Step 2: choose the doctor (or test/tests).
- Step 3: choose date — the date field must remain locked to the session's weekday, as it is now.
- Step 4: choose session/slot, with live remaining-capacity feedback.
- Step 5: patient details (name, phone with BD validation).
- Step 6: confirm → redirect to the existing UUID ticket page.
- Support deep links so landing CTAs can skip steps: `/book?doctor={uuid}`, `/book?test={uuid}`.
- Preserve everything already working: weekday locking, availability polling, disabled submit when full, rate limiting, read-only billing block.

### 4.5 Tenant admin — content authoring

The tenant admin panel currently manages operational data only. Add content management:

- A **Website** section: identity (name, tagline, logo), theme (colours), sections (toggle + reorder), and the content for each enabled section.
- Live preview or at minimum a prominent "View site" link.
- Keep it genuinely simple — the audience is clinic reception staff, not marketers. Prefer a small number of clear fields over a flexible page builder.
- `custom_code` stays super-admin-approved. A tenant may draft it; only a super admin can approve it for rendering.

### 4.6 The bespoke-build seam

Formalise the escape hatch, in this order of preference:

1. **Theme tokens** — colours, fonts, radius, spacing as tenant config. Handles most "make it look like us" requests with zero code.
2. **Section composition** — order, visibility, and per-section variants. Handles most "we want a different structure" requests with zero code.
3. **A bespoke layout component** in this repo, selected via `layout_id`. One file, assigned with a dropdown.
4. **`feature_flags`** for capability toggles.
5. **`custom_code`** for analytics and pixels, approval-gated.

**The rule: customisation is data first, one component file second, never a fork.** Any request that seems to need a branch is a signal that one of the layers above is missing a knob.

---

## 5. UI/UX requirements

- **Mobile-first.** Most patients are on mid-range Android over mobile data.
- **Low bandwidth.** Compress and lazily load images, keep the landing JS payload small, avoid heavy fonts. Assets are built in CI and deployed compiled — never built on the production box.
- **The CTA is the point.** "Book Appointment" must be unmissable on every layout, above the fold, and repeated at the end of the page.
- **No dead ends.** Empty states (no doctors yet, no tests yet, fully booked, clinic on read-only billing) must all say something useful.
- **Fail before submit, not after.** Capacity, weekday, and blocked dates are surfaced while choosing — never as a post-submit error.
- **Accessibility basics**: real labels, visible focus, adequate contrast, keyboard-navigable booking flow.
- **PWA**: per-tenant manifest with the tenant's name, icon, and theme colour; "add to home screen" prompt on the ticket page, where it has the most value.

### 5.1 Language (locked)

The app is currently English-only (`APP_LOCALE=en`). Change this as follows.

**Static UI strings — professionally translated, shipped in the repo.**
- Ship both `en` and `bn`. Roughly 200–300 strings across the patient site, booking flow, and admin panels.
- **Translate manually. Do not use a translation API for these.** Bangladeshi patients use English loanwords for exactly this domain — সিরিয়াল (serial), অ্যাপয়েন্টমেন্ট, ডাক্তার, চেম্বার. Machine translation renders "serial" as ক্রমিক or ধারাবাহিক, which is technically correct and reads like a government form. Get a native speaker to write these; it is a one-time, bounded cost.
- The patient site gets a **language toggle**, defaulting to the tenant's configured default locale (a new tenant setting).

**Tenant-authored content — single field, no translation.**
- Clinic name, tagline, about copy, doctor bios, lab test names, prep instructions, FAQ entries: **one field each**, in whatever language the clinic types. Bengali, English, or the mixed register normal on Bangladeshi clinic pages — all fine.
- **No duplicate `_bn` fields and no machine translation in this path.** Two reasons: forcing tenants to fill every field twice directly attacks the 30-minute onboarding target, and auto-translating **lab test preparation instructions is a safety risk** — a mistranslated fasting requirement means a wasted test or a clinically wrong result.
- If a large client later wants genuinely bilingual content, that is a per-tenant `feature_flags` capability with nullable `_bn` variants falling back to the primary. It fits the existing customisation seam. Do not build it now.

**Do this from day one, regardless of translation timing:** route **every** user-facing string through a translation helper — `__()` server-side and the equivalent in the Vue components. The expensive part of retrofitting i18n is never the translating; it is hunting hardcoded strings out of dozens of Vue templates and Filament schemas afterwards.

**Verify early:** check Filament v4's Bengali (`bn`) coverage for the admin panels. It ships translations for many locales but `bn` completeness is uncertain. If reception staff have limited English, panel translation matters more than the patient site — scope any gap-filling up front rather than discovering it late.

---

## 6. Code structure expectations

- **One booking pipeline.** Locking, serial allocation, cap enforcement, and cancellation exist once and serve both doctor and lab bookings.
- **One tier authority.** Capability checks go through a single accessor. No scattered string comparisons on `plan_tier`.
- **Shared components over duplication.** Layouts compose sections; sections compose UI primitives. Booking logic lives in exactly one place, as it does today.
- **Tenant scoping is non-negotiable.** Every new tenant-owned model gets `BelongsToTenant` *and* the composite `(tenant_id, id)` foreign key pattern, from its first migration.
- **Tests before features.** Every new tenant-scoped model must be added to the two-tenant leakage test before anything is built on top of it.
- Keep the existing conventions: services for domain logic, Filament resources thin, form schemas in their own classes.

---

## 7. Deployment constraints (unchanged)

- Shared cPanel, **1 GB RAM**, no persistent processes.
- Queue workers run via cron (`* * * * * php artisan queue:work --stop-when-empty`), never as a daemon.
- Frontend assets are built locally or in CI and deployed compiled.
- Wildcard subdomain + SSL required; if AutoSSL does not cover wildcards, front with Cloudflare in **Full** mode (never Flexible).

---

## 8. Non-negotiables (verify before any demo)

- Every public booking URL uses a UUID.
- Payment status changes only via a signature-verified server-to-server webhook.
- Booking submission is IP rate-limited at the middleware level.
- Custom code renders only after super-admin approval.
- Two-tenant seeded leakage test passes across **every** tenant-scoped model, including all new lab-test tables.
- Walk-in and online bookings cannot produce a duplicate serial number.

---

## 9. Suggested order

0. **i18n scaffolding** — move existing hardcoded strings into translation files and wire the helpers, before the UI surface grows. Ship `en` first; `bn` strings can land later, but the plumbing must exist now. This is deliberately step zero: every step below adds strings, and each one you add untranslated is one you pay to hunt down later.
1. Tier vocabulary + capability accessor + `feature_flags` wiring. (Everything else depends on this.)
2. Content model + tenant admin authoring UI + tenant default locale setting.
3. Section library + layout refactor. Landing pages become real.
4. `/book` route extraction; landing CTAs point at it.
5. Lab tests: model, collection scheduling, **multi-test line items**, booking-pipeline unification, tenant admin resource, landing section, `/book` integration.
6. Bespoke seam formalisation (theme tokens, section config).
7. Bengali translation pass + patient-site language toggle.
8. Real gateway signature schemes + `config/services.php` entries.

Do not skip ahead. Steps 0–2 are load-bearing for everything after them.
