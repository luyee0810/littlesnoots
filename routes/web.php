<?php

use App\Http\Controllers\AdoptionApplicationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemorialCandleController;
use App\Http\Controllers\MemorialController;
use App\Http\Controllers\MemorialMessageController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProviderBookingController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProviderOnboardingController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderServiceController;
use App\Http\Controllers\ServiceCategoryController;
use App\Models\Pet;
use App\Models\PetMemorial;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'featured' => Pet::published()->available()
            ->with(['species', 'photos'])
            ->latest('published_at')
            ->take(4)
            ->get(),
        'memorials' => PetMemorial::whereNotNull('photo_path')
            ->latest()
            ->take(3)
            ->get(),
    ]);
})->name('home');

// ---- New design preview (scrapbook homepage, WIP) ----------------------
Route::get('/newdesign', function () {
    return view('newdesign', [
        'featured' => Pet::published()->available()
            ->with(['species', 'photos'])
            ->latest('published_at')
            ->take(4)
            ->get(),
    ]);
})->name('newdesign');

// ---- Pet adoption (Phase 1) --------------------------------------------
Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show');
Route::post('/pets/{pet}/apply', [AdoptionApplicationController::class, 'store'])
    ->name('pets.apply');

// ---- Pet services (Phase 2) ---------------------------------------------
Route::get('/services', [ServiceCategoryController::class, 'index'])->name('services.index');
Route::get('/services/{category}', [ServiceCategoryController::class, 'show'])->name('services.show');
Route::get('/sitters/{provider}', [ProviderController::class, 'show'])->name('providers.show');

Route::middleware('auth')->group(function () {
    Route::post('/sitters/{provider}/book', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

// ---- Pet memorials ------------------------------------------------------
// `create` is registered before the `{memorial}` slug route so it isn't
// swallowed as a slug.
Route::get('/memorials', [MemorialController::class, 'index'])->name('memorials.index');

Route::middleware('auth')->group(function () {
    Route::get('/memorials/create', [MemorialController::class, 'create'])->name('memorials.create');
    Route::post('/memorials', [MemorialController::class, 'store'])->name('memorials.store');
    Route::delete('/memorials/{memorial}', [MemorialController::class, 'destroy'])->name('memorials.destroy');
    Route::post('/memorials/{memorial}/candle', [MemorialCandleController::class, 'store'])->name('memorials.candle');
    Route::post('/memorials/{memorial}/messages', [MemorialMessageController::class, 'store'])->name('memorials.messages.store');
});

Route::get('/memorials/{memorial}', [MemorialController::class, 'show'])->name('memorials.show');

// ---- Provider dashboard -------------------------------------------------
Route::prefix('provider')->name('provider.')->middleware('auth')->group(function () {
    // Onboarding sits outside the `provider` middleware — it's how you become one.
    Route::get('/onboarding', [ProviderOnboardingController::class, 'create'])->name('onboarding');
    Route::post('/onboarding', [ProviderOnboardingController::class, 'store'])->name('onboarding.store');

    Route::middleware('provider')->group(function () {
        Route::get('/profile', [ProviderProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProviderProfileController::class, 'update'])->name('profile.update');

        Route::resource('services', ProviderServiceController::class)->except(['show']);

        Route::get('/bookings', [ProviderBookingController::class, 'index'])->name('bookings.index');
        Route::patch('/bookings/{booking}/accept', [ProviderBookingController::class, 'accept'])->name('bookings.accept');
        Route::patch('/bookings/{booking}/decline', [ProviderBookingController::class, 'decline'])->name('bookings.decline');
        Route::patch('/bookings/{booking}/complete', [ProviderBookingController::class, 'complete'])->name('bookings.complete');
    });
});

// ---- Adopter dashboard --------------------------------------------------
Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Roadmap — reserved routes for upcoming phases
|--------------------------------------------------------------------------
| Phase 3  Route::resource('products', ProductController::class);
*/

require __DIR__.'/auth.php';
