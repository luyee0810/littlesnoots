# Two Fat Cats — Product Roadmap

A pet platform built on Laravel 13 + PostgreSQL. Rolls out in three phases; each phase
reuses a consistent pattern (a `*_categories` taxonomy + a media/photos table where images
are needed) so the codebase stays uniform.

## Phase 1 — Pet Adoption ✅ (in progress / shipped)

**Goal:** browse adoptable pets and submit adoption applications.

- **Schema:** `species` → `breeds` → `pets` → `pet_photos`; `adoption_applications`; `users.role` (adopter/staff/admin).
- **Public UI:** home page, filterable pet listing (`/pets`), pet detail (`/pets/{slug}`) with an application form.
- **Backend:** `PetController`, `AdoptionApplicationController`, `StoreAdoptionApplicationRequest`.
- **Data:** seeded species/breeds + demo pets with photos; admin & staff demo users.

### Remaining Phase 1 polish
- [x] Auth (hand-rolled native Laravel auth) so adopters have accounts and can track applications — register/login/logout, password reset, and a `/dashboard` listing the user's applications.
- [ ] Staff dashboard: manage pet listings, review applications, change status.
- [ ] Authorization policies (`PetPolicy`, `AdoptionApplicationPolicy`).
- [ ] Real image uploads (currently placeholder URLs) via the `public` storage disk.
- [ ] Feature tests for listing, detail, and application flows.
- [ ] Email notifications on application received / status change.

## Phase 2 — Pet Services (planned)

**Goal:** list and discover pet services (grooming, veterinary, boarding, training).

- **Schema:**
  - `service_categories` (Grooming, Vet, Boarding, Training …)
  - `service_providers` (business name, contact, location, verified flag)
  - `services` (→ category, → provider, name, description, price range, duration)
  - optional `service_photos`
- **UI:** `/services` listing with category filter; service detail; provider profile.
- **Backend:** `ServiceController`, `ServiceProviderController`; reuse the filter pattern from `PetController`.
- Later: booking/enquiry requests (mirrors `adoption_applications`).

## Phase 3 — Pet Products (planned)

**Goal:** sell pet-related products (food, toys, accessories).

- **Schema:**
  - `product_categories`
  - `products` (→ category, SKU, price, stock, description)
  - `product_photos`
  - Later: `orders`, `order_items`, cart, payments.
- **UI:** `/products` catalog with category + price filters; product detail; cart & checkout.
- **Backend:** `ProductController`; introduce a cart/order service layer and a payment gateway (e.g. Stripe).

## Cross-cutting (all phases)
- Shared Blade layout + Tailwind design system (`layouts/app`, warm stone + amber palette).
- Role-based access: adopter / staff / admin.
- Consistent taxonomy + media patterns across pets, services, products.
- Pint for formatting; Pest/PHPUnit for tests; review via the `code-reviewer` agent.
