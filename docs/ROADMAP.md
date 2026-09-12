# Little Snoots — Product Roadmap

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

**Goal:** a PetBacker-style marketplace — individual providers (boarding, walking, grooming …)
publish a profile with per-service pricing; owners search by location + dates and book directly.

Full design in **[docs/PHASE2-SERVICES.md](PHASE2-SERVICES.md)**. In brief:

- **Categories:** Boarding, House Sitting, Dog Walking, Daycare, Grooming, Pet Taxi, Training —
  each with its own pricing unit (per night / walk / session / trip).
- **Schema:** `service_categories`; `provider_profiles` (1:1 with `users`, self-serve signup);
  `provider_services` (price per category); `provider_photos`; `provider_unavailable_dates`;
  `bookings` (direct booking, provider accepts/declines); later `reviews`, `messages`.
- **Search:** keywords + location + service category. No payments or commission in Phase 2 —
  owner and provider settle off-platform.
- **UI:** `/services` category grid, `/services/{category}` provider search results,
  `/sitters/{provider}` profile + booking panel, provider booking inbox.
- **Build order:** 2a browse & book (ships standalone) → 2b reviews, verification, uploads →
  2c in-app messaging. Payments deferred beyond Phase 2.

## Phase 3 — Shop / Pet Products (planned)

**Goal:** a multi-merchant shop — registered users open a store and sell pet products (food,
toys, accessories); buyers browse, add to cart and order.

Full design in **[docs/PHASE3-SHOP.md](PHASE3-SHOP.md)**. In brief:

- **Categories:** Food, Treats, Health, Toys, Grooming, Beds & Furniture, Feeding,
  Collars & Apparel — a self-referencing taxonomy so sub-categories need no migration.
- **Schema:** `product_categories`; `merchant_profiles` (1:1 with `users`, self-serve, mirrors
  `provider_profiles`); `products` → `product_variants` (price + stock live here) →
  `product_photos`; `merchant_shipping_rates`; `carts`/`cart_items`; `orders`/`order_items`;
  later `product_reviews`, `payments`, `discount_codes`.
- **Cart:** one cart may span merchants; checkout splits it into one order per merchant under a
  shared `checkout_reference`. Stock is locked and decremented at order placement.
- **Money:** no online payments in 3a — orders are placed as bank transfer / COD and the merchant
  marks them paid. Totals always computed server-side.
- **UI:** `/shop` + `/shop/c/{category}` catalog, `/shop/p/{product}` detail, `/stores/{merchant}`,
  cart & checkout, buyer order history, merchant product & order dashboards.
- **Build order:** 3a catalogue/cart/orders (ships standalone) → 3b reviews, uploads, discounts →
  3c payments (Stripe / Billplz) and payouts.

## Cross-cutting (all phases)
- Shared Blade layout + Tailwind design system (`layouts/app`, warm stone + amber palette).
- Role-based access: adopter / staff / admin.
- Consistent taxonomy + media patterns across pets, services, products.
- Pint for formatting; Pest/PHPUnit for tests; review via the `code-reviewer` agent.
