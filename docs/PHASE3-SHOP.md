# Phase 3 — Shop (merchant marketplace)

A **multi-merchant** shop for pet products: registered users open a merchant store, list
products, and sell to buyers. Little Snoots hosts the catalogue and the order record; it is a
marketplace, not a first-party retailer.

Demo data is Malaysia-based and priced in **MYR (RM)**, matching Phase 2.

**Decisions taken up front**

| Question | Decision |
| --- | --- |
| Who sells | **Merchants — any registered user can open a store**, same self-serve pattern as provider profiles. A store may be a person or a business (`business_name`, `ssm_registration_no` are optional columns). |
| Merchant vs. role | **Merchant is not a `users.role`.** Same reasoning as providers: a user can be an adopter, a sitter *and* a merchant. `$user->isMerchant()` = an approved `merchant_profile` exists. |
| Catalogue shape | `product_categories` taxonomy + `products` + `product_variants` + `product_photos`. **Variants are mandatory** — every product has ≥1 variant, and price/stock/SKU live on the variant, never the product. |
| Cart | **One cart, many merchants.** Checkout splits it into one `order` per merchant, grouped under one `checkout_reference`. Each merchant ships and fulfils independently. |
| Money | **No online payments in 3a.** Orders are placed with `payment_method = manual` (bank transfer / COD) and the merchant marks them paid. Stripe/Billplz lands in 3c. Totals are always computed server-side. |
| Shipping | **Flat rate per merchant**, with a free-shipping threshold and an optional per-item surcharge. No courier integration, no live rates. |
| Stock | Tracked on the variant, decremented **at order placement** (not at cart-add), inside a transaction with a row lock. Oversell is prevented, reservations are not modelled. |
| Scope | Sequenced **3a → 3b → 3c**, and 3a ships standalone. |

---

## 1. Catalogue

Eight top-level categories. `product_categories` is **self-referencing** (`parent_id`) so
sub-categories can be added without a migration; 3a seeds one level.

| Category | Slug | Typical sub-categories |
| --- | --- | --- |
| Food | `food` | Dry, Wet, Raw & Frozen |
| Treats & Chews | `treats` | Biscuits, Dental, Jerky |
| Health & Supplements | `health` | Flea & tick, Vitamins, Wound care |
| Toys | `toys` | Chew, Interactive, Catnip |
| Grooming | `grooming-supplies` | Shampoo, Brushes, Clippers |
| Beds & Furniture | `beds-furniture` | Beds, Crates, Cat trees |
| Bowls & Feeders | `feeding` | Bowls, Fountains, Storage |
| Collars, Leads & Apparel | `collars-apparel` | Collars, Harnesses, Carriers |

Products also carry `species` targeting (json of `species` slugs, reusing the Phase 1 taxonomy)
and `life_stage` (`puppy-kitten` / `adult` / `senior` / `all`) — the two facets buyers actually
filter pet food by.

---

## 2. Schema

Same house pattern as Phases 1 and 2: a `*_categories` taxonomy plus a photos table.

```
users
 └── merchant_profiles (1:1)
       ├── products ──── product_categories
       │     ├── product_variants ──< order_items (snapshotted)
       │     └── product_photos
       ├── merchant_shipping_rates
       └── orders ────────────────── users (the buyer)
             ├── order_items
             └── (3b) product_reviews

carts ──< cart_items ──── product_variants
```

### 3a tables

**`product_categories`**
`id, parent_id (nullable, self FK), name, slug (unique), description, icon, sort_order,
is_active, timestamps`

**`merchant_profiles`** — 1:1 with `users`, route-bound on `slug`
- Identity: `user_id` (unique, cascade), `slug` (unique), `store_name`, `tagline`, `about`,
  `logo_url`, `banner_url`
- Business: `business_name`, `ssm_registration_no`, `support_email`, `support_phone`
- Location: `address1`, `address2`, `city` (index), `state`, `postcode`, `country` — the
  ship-from address, and what "Ships from Penang" on a product card reads off
- Fulfilment: `processing_days`, `returns_policy`, `shipping_policy`
- Lifecycle: `status` enum(`draft`, `pending`, `approved`, `suspended`) default `draft`, indexed;
  `published_at`
- Denormalised counters: `rating_avg decimal(3,2)`, `reviews_count`, `products_count`,
  `orders_count`
- `timestamps`, `softDeletes`

**`products`** — the listing; **no price and no stock here**
`id, merchant_profile_id, product_category_id, name, slug (unique), sku_prefix, short_description,
description, brand, species (json), life_stage, tags (json), status enum(draft/active/archived),
is_featured, weight_grams (nullable — variant may override), published_at, timestamps, softDeletes`
Indexes: `(merchant_profile_id, status)`, `(product_category_id, status)`, `slug` unique.

**`product_variants`** — where price and stock live
`id, product_id, name ("2kg", "Large / Blue"), sku (unique), price decimal(8,2),
compare_at_price (nullable, for strike-through), currency, stock_quantity, low_stock_threshold,
weight_grams, is_default, is_active, sort_order, timestamps`
A single-variant product gets one row named "Default" — this keeps every downstream query
(`order_items`, `cart_items`, stock) variant-shaped with no special case.

**`product_photos`**
`id, product_id, product_variant_id (nullable), url, caption, is_primary, sort_order, timestamps`
— mirrors `pet_photos` / `provider_photos`.

**`merchant_shipping_rates`**
`id, merchant_profile_id, name, states (json, null = rest of Malaysia), flat_rate decimal(8,2),
per_item_surcharge, free_over (nullable), is_active, timestamps`
Cheapest matching rate for the buyer's state wins; a merchant with no rate row ships free.

**`carts` / `cart_items`**
- `carts`: `id, user_id (nullable), session_id (nullable, index), timestamps` — guests get a
  session cart that is merged into their user cart on login.
- `cart_items`: `id, cart_id, product_variant_id, quantity, timestamps`, unique on
  `(cart_id, product_variant_id)`. **No price column** — the cart always reads live prices, so a
  price change is visible before checkout, not after.

**`orders`** — one per merchant per checkout
- Keys: `reference` (short public code, unique), `checkout_reference` (groups a multi-merchant
  checkout, index), `merchant_profile_id`, `user_id` (buyer)
- Buyer snapshot: `buyer_name`, `buyer_email`, `buyer_phone`
- Shipping snapshot: `ship_to_name`, `ship_to_phone`, `address1`, `address2`, `city`, `state`,
  `postcode`, `country`, `delivery_notes`
- **Money snapshot** (all server-computed, never posted from the form): `subtotal`,
  `shipping_total`, `discount_total`, `total`, `currency`
- Payment: `payment_method` enum(`manual`, `cod`) in 3a, `payment_status`
  enum(`unpaid`, `paid`, `refunded`), `paid_at`, `payment_reference`
- Fulfilment: `status` enum(`pending`, `confirmed`, `packing`, `shipped`, `delivered`,
  `cancelled_by_buyer`, `cancelled_by_merchant`, `refunded`) default `pending`, indexed;
  `tracking_carrier`, `tracking_number`, `shipped_at`, `delivered_at`, `cancelled_at`,
  `cancellation_reason`, `merchant_note`
- Composite indexes: `(merchant_profile_id, status)`, `(user_id, status)`, `(created_at)`

**`order_items`** — fully snapshotted, so editing or deleting a product never rewrites history
`id, order_id, product_id (nullable on delete), product_variant_id (nullable on delete),
product_name, variant_name, sku, unit_price, quantity, line_total, weight_grams, photo_url,
timestamps`

### 3b/3c tables

**`product_reviews`** — `order_item_id` (unique), `product_id`, `merchant_profile_id`, `user_id`,
`rating` (1–5), `title`, `body`, `merchant_reply`, `replied_at`, `published_at`. Only creatable
against a `delivered` order item, mirroring the Phase 2 review rule.

**`payments`** (3c) — gateway id, amount, status, raw payload; plus `payouts` for merchant
settlement.

**`discount_codes`** (3b) — merchant-scoped percentage/fixed codes with usage limits.

### Notes on things that could go wrong

- **Price on the item, not the product.** Putting `price` on `products` and adding variants later
  is the migration everyone regrets. Every product has ≥1 variant from day one.
- **Stock races.** `POST /checkout` runs in a transaction: `lockForUpdate()` the affected variant
  rows, re-check `stock_quantity >= quantity`, decrement, then write the orders. A shortfall
  redirects back to the cart naming the item — not a 500.
- **Totals from the cart, never the form.** Same rule as `ProviderService::totalFor()`; the
  checkout request accepts an address and nothing financial.
- **Multi-merchant checkout is the whole design.** Cancelling one order must not touch its
  siblings; `checkout_reference` exists only to render "your 2 orders" on the confirmation page.
- **Guest carts.** `session_id` carts are real rows and need a prune command
  (`shop:prune-carts`, carts untouched for 30 days) or the table grows forever.
- **`products` is not `pets`.** Unrelated tables; the only thing shared is the `species`
  vocabulary, referenced by slug in json rather than by FK.

---

## 3. Routes

```php
// Public shop
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/c/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/shop/p/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/stores/{merchant}', [MerchantController::class, 'show'])->name('merchants.show');

// Cart (guest or auth)
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

// Checkout & orders (auth)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

// Sell on Little Snoots (auth)
Route::prefix('merchant')->name('merchant.')->middleware('auth')->group(function () {
    Route::get('/onboarding', [MerchantOnboardingController::class, 'create'])->name('onboarding');
    Route::post('/onboarding', [MerchantOnboardingController::class, 'store']);
    Route::get('/store',  [MerchantProfileController::class, 'edit'])->name('store.edit');
    Route::put('/store',  [MerchantProfileController::class, 'update'])->name('store.update');
    Route::resource('products', MerchantProductController::class);
    Route::resource('products.variants', MerchantVariantController::class)->shallow()->except(['show']);
    Route::resource('shipping-rates', MerchantShippingRateController::class)->except(['show']);
    Route::get('/orders', [MerchantOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}/confirm', [MerchantOrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('/orders/{order}/ship',    [MerchantOrderController::class, 'ship'])->name('orders.ship');
    Route::patch('/orders/{order}/deliver', [MerchantOrderController::class, 'deliver'])->name('orders.deliver');
    Route::patch('/orders/{order}/paid',    [MerchantOrderController::class, 'markPaid'])->name('orders.paid');
    Route::patch('/orders/{order}/cancel',  [MerchantOrderController::class, 'cancel'])->name('orders.cancel');
});
```

`MerchantProfile` and `Product` both return `slug` from `getRouteKeyName()`, same as `Pet` and
`ProviderProfile`. `/shop/p/{slug}` is prefixed to keep product slugs from colliding with the
category namespace.

## 4. Order state machine

```
pending ──> confirmed ──> packing ──> shipped ──> delivered ──> review window opens (3b)
   │            │             │
   │            └─────────────┴──> cancelled_by_merchant   (restocks the variants)
   └──> cancelled_by_buyer   (allowed while pending/confirmed only; restocks)

payment_status: unpaid ──> paid ──> refunded   (independent of fulfilment in 3a)
```

Transitions live in guarded `Order` model methods (`markConfirmed()`, `markShipped()`, …) that
throw on an illegal move — the `Booking` pattern, not controller `if` chains. Cancellation and
refund **restock** the variants in the same transaction that moves the status.

## 5. Controllers, requests, policies

| Layer | Classes |
| --- | --- |
| Controllers | `ShopController`, `ProductController`, `MerchantController`, `CartController`, `CheckoutController`, `OrderController`, `MerchantOnboardingController`, `MerchantProfileController`, `MerchantProductController`, `MerchantVariantController`, `MerchantShippingRateController`, `MerchantOrderController` |
| Form requests | `StoreMerchantProfileRequest`, `UpdateMerchantProfileRequest`, `StoreProductRequest`, `UpdateProductRequest`, `StoreVariantRequest`, `StoreCartItemRequest`, `StoreCheckoutRequest`, `ShipOrderRequest`, `StoreProductReviewRequest` (3b) |
| Policies | `MerchantProfilePolicy` (owner or admin), `ProductPolicy` (merchant owns it, or admin), `OrderPolicy` (`view`: buyer or merchant; `fulfil`: merchant; `cancel`: either, status-dependent) |
| Services | `CartService` (resolve/merge session ↔ user cart, add/update/remove), `CheckoutService` (lock stock → compute totals → split by merchant → create orders → clear cart), `ShippingCalculator` (cheapest matching rate per merchant) |
| Scopes | `Product::active()`, `published()`, `inCategory($slug)`, `forSpecies($slug)`, `priceBetween()`, `inStock()`, `search($keywords)`; `MerchantProfile::approved()`, `published()` |

`CheckoutService` is the one piece that earns a service class rather than a scope — it spans
several tables inside a transaction, and both the controller and the (3c) payment webhook will
need it.

## 6. Browsing & search

| Input | Param | Behaviour |
| --- | --- | --- |
| Keywords | `q` | `ilike '%term%'` across `products.name`, `.brand`, `.short_description` |
| Category | `category` | exact `product_categories.slug`, including descendants |
| Species | `species` | json containment against `products.species` |
| Price | `min` / `max` | range over `product_variants.price` via `whereHas` |
| In stock | `in_stock` | `whereHas('variants', fn ($q) => $q->where('stock_quantity', '>', 0))` |
| Sort | `sort` | `newest` (default), `price_asc`, `price_desc`, `rating` |

Case-insensitive matching uses Postgres `ilike`, per the house convention. A trigram index on
`products.name` only if `ilike` measurably slows down.

Deliberately **not** in 3a: brand facet, multi-select categories, faceted counts, autocomplete.

## 7. Views

```
resources/views/
  shop/index.blade.php              hero, category grid, featured products
  shop/category.blade.php           filter bar + product grid
  products/show.blade.php           gallery, variant picker, stock, add-to-cart, merchant strip
  merchants/show.blade.php          store banner, about, that merchant's products
  cart/show.blade.php               grouped by merchant, per-merchant shipping, totals
  checkout/create.blade.php         shipping address, per-merchant summary, place order
  orders/index.blade.php            buyer order history
  orders/show.blade.php             status timeline, items, tracking, cancel
  merchant/onboarding.blade.php     store → address → shipping rates
  merchant/products/*.blade.php     product list + create/edit with variant rows
  merchant/orders.blade.php         order inbox: new first, confirm/ship inline
  partials/product-card.blade.php   sibling of pet-card / provider-card
  partials/shop-filters.blade.php   keywords + category + species + price
  partials/cart-badge.blade.php     item count in the nav
  partials/order-status.blade.php
```

Same warm stone + amber palette. Nav: replace the disabled `Shop` span in `layouts/app`
([layouts/app.blade.php:20](../resources/views/layouts/app.blade.php#L20)) with a live link plus
a cart badge, and add "Sell on Little Snoots" to the dashboard next to "Become a sitter".

## 8. Seeders & factories

- `ProductCategorySeeder` — the eight categories above, idempotent `updateOrCreate` on slug.
- `MerchantProfileFactory`, `ProductFactory`, `ProductVariantFactory`, `OrderFactory`.
- `ShopDemoSeeder` — ~8 merchants across Malaysian cities, ~60 products with photos and 1–4
  variants each, a few out-of-stock and on-sale, and orders spread across every status.
- Demo login: `merchant@littlesnoots.test` (password = factory default `password`).
- Photos stay placeholder URLs (`picsum.photos`) in 3a, same as pets and providers; real uploads
  are a 3b item.

## 9. Build sequence

### 3a — Catalogue, cart, manual-payment orders (ships standalone)

1. `product_categories`, `merchant_profiles`, `products`, `product_variants`, `product_photos`,
   `merchant_shipping_rates` migrations + models + factories.
2. `ProductCategorySeeder` + `ShopDemoSeeder`; `User::isMerchant()` / `merchantProfile()`.
3. Merchant onboarding, store profile, product & variant CRUD, shipping rates.
4. `/shop`, `/shop/c/{category}`, `/shop/p/{product}`, `/stores/{merchant}` with the filter bar.
5. `carts` / `cart_items` + `CartService`, including guest→user merge on login.
6. `CheckoutService`: stock lock, server-side totals, split into per-merchant orders.
7. Merchant order inbox (confirm / ship / deliver / mark paid / cancel); buyer order list + cancel.
8. Policies, nav link + cart badge, dashboard sections for both sides.
9. Feature tests: filters, variant selection, cart merge, oversell prevention, multi-merchant
   split, every state transition, authorization.

### 3b — Trust, media, merchandising
Product reviews on delivered items (with merchant replies) → recompute `rating_avg`; admin
approval queue for `pending` merchants; real image uploads on the `public` disk (shared with the
pet/provider photo migration); discount codes; low-stock alerts; order emails on every
transition; wishlist.

### 3c — Payments
Stripe (or Billplz for MYR/FPX) checkout, `payments` + `payouts` tables, platform commission,
refunds driving `payment_status`, and merchant payout onboarding. The money columns on `orders`
are already shaped for it; it adds gateway columns and a `commission_total`.

## 10. Open questions for later

- **Commission model** — flat percentage per order, or listing fees? Affects the payout schema.
- **Who ships** — merchant-shipped only, or a consolidated warehouse option later?
- **Digital/subscription products** (repeat food deliveries) — a different fulfilment shape;
  deliberately excluded from 3a.
- **Multi-currency**, or MYR only as in Phase 2.
- **Tax/SST** — no tax columns in 3a; adding them post-launch means backfilling `orders`.
