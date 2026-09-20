<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ApplicationOverviewController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\PetModerationController;
use App\Http\Controllers\Admin\ProviderModerationController;
use App\Http\Controllers\Admin\ReportQueueController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdoptionApplicationController;
use App\Http\Controllers\AdoptionApplicationReviewController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemorialCandleController;
use App\Http\Controllers\MemorialController;
use App\Http\Controllers\MemorialMessageController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\PetListingController;
use App\Http\Controllers\ProviderBookingController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProviderOnboardingController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderServiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewReplyController;
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

// ---- Rehoming: listing your own pet ------------------------------------
// Registered before `/pets/{pet}` so `create` isn't swallowed as a slug.
Route::prefix('rehome')->name('listings.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [PetListingController::class, 'index'])->name('index');
    Route::get('/new', [PetListingController::class, 'create'])->name('create');
    Route::post('/', [PetListingController::class, 'store'])->name('store');
    Route::get('/{pet}/edit', [PetListingController::class, 'edit'])->name('edit');
    Route::put('/{pet}', [PetListingController::class, 'update'])->name('update');
    Route::post('/{pet}/submit', [PetListingController::class, 'submit'])->name('submit');
    Route::delete('/{pet}', [PetListingController::class, 'destroy'])->name('destroy');
    Route::delete('/{pet}/photos/{photo}', [PetListingController::class, 'destroyPhoto'])->name('photos.destroy');
    Route::patch('/{pet}/photos/{photo}/primary', [PetListingController::class, 'makePhotoPrimary'])->name('photos.primary');

    Route::get('/{pet}/applications', [AdoptionApplicationReviewController::class, 'index'])->name('applications');
});

// ---- Adoption applications ----------------------------------------------
Route::middleware('auth')->group(function () {
    Route::patch('/applications/{application}', [AdoptionApplicationReviewController::class, 'update'])
        ->name('applications.update');
    Route::patch('/applications/{application}/withdraw', [AdoptionApplicationReviewController::class, 'withdraw'])
        ->name('applications.withdraw');
});

Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show');
Route::post('/pets/{pet}/apply', [AdoptionApplicationController::class, 'store'])
    ->name('pets.apply');

// ---- Pet services (Phase 2) ---------------------------------------------
Route::get('/services', [ServiceCategoryController::class, 'index'])->name('services.index');
Route::get('/services/{category}', [ServiceCategoryController::class, 'show'])->name('services.show');
Route::get('/providers/{provider}', [ProviderController::class, 'show'])->name('providers.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/providers/{provider}/book', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store'])->name('bookings.review.store');
    Route::post('/bookings/{booking}/messages', [BookingMessageController::class, 'store'])->name('bookings.messages.store');
    Route::post('/reviews/{review}/reply', [ReviewReplyController::class, 'store'])->name('reviews.reply');
});

// ---- Pet memorials ------------------------------------------------------
// `create` is registered before the `{memorial}` slug route so it isn't
// swallowed as a slug.
Route::get('/memorials', [MemorialController::class, 'index'])->name('memorials.index');

Route::middleware('auth')->group(function () {
    Route::get('/memorials/create', [MemorialController::class, 'create'])->name('memorials.create');
    Route::post('/memorials', [MemorialController::class, 'store'])->middleware('verified')->name('memorials.store');
    Route::delete('/memorials/{memorial}', [MemorialController::class, 'destroy'])->name('memorials.destroy');
    Route::post('/memorials/{memorial}/candle', [MemorialCandleController::class, 'store'])->name('memorials.candle');
    Route::post('/memorials/{memorial}/messages', [MemorialMessageController::class, 'store'])
        ->middleware('verified')->name('memorials.messages.store');
    Route::delete('/memorial-messages/{message}', [MemorialMessageController::class, 'destroy'])->name('memorials.messages.destroy');

    // Flagging content for a moderator — never hides anything by itself.
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});

Route::get('/memorials/{memorial}', [MemorialController::class, 'show'])->name('memorials.show');

// ---- Provider dashboard -------------------------------------------------
Route::prefix('provider')->name('provider.')->middleware('auth')->group(function () {
    // Onboarding sits outside the `provider` middleware — it's how you become one.
    Route::get('/onboarding', [ProviderOnboardingController::class, 'create'])->name('onboarding');
    Route::post('/onboarding', [ProviderOnboardingController::class, 'store'])
        ->middleware('verified')->name('onboarding.store');

    Route::middleware('provider')->group(function () {
        Route::get('/profile', [ProviderProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/resubmit', [ProviderProfileController::class, 'resubmit'])->name('resubmit');
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

// ---- Admin (back of house) ----------------------------------------------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'staff'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/pets', [PetModerationController::class, 'index'])->name('pets.index');
    Route::get('/pets/{pet}', [PetModerationController::class, 'show'])->name('pets.show');
    Route::patch('/pets/{pet}/approve', [PetModerationController::class, 'approve'])->name('pets.approve');
    Route::patch('/pets/{pet}/reject', [PetModerationController::class, 'reject'])->name('pets.reject');
    Route::patch('/pets/{pet}/unpublish', [PetModerationController::class, 'unpublish'])->name('pets.unpublish');

    Route::get('/applications', [ApplicationOverviewController::class, 'index'])->name('applications.index');

    Route::resource('organizations', OrganizationController::class)->except(['show']);

    Route::get('/members', [UserController::class, 'index'])->name('users.index');
    Route::get('/members/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/members/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
    Route::patch('/members/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::patch('/members/{user}/reinstate', [UserController::class, 'reinstate'])->name('users.reinstate');

    Route::get('/reports', [ReportQueueController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}/remove', [ReportQueueController::class, 'remove'])->name('reports.remove');
    Route::patch('/reports/{report}/dismiss', [ReportQueueController::class, 'dismiss'])->name('reports.dismiss');

    Route::get('/providers', [ProviderModerationController::class, 'index'])->name('providers.index');
    Route::get('/providers/{provider}', [ProviderModerationController::class, 'show'])->name('providers.show');
    Route::patch('/providers/{provider}/approve', [ProviderModerationController::class, 'approve'])->name('providers.approve');
    Route::patch('/providers/{provider}/suspend', [ProviderModerationController::class, 'suspend'])->name('providers.suspend');
});

/*
|--------------------------------------------------------------------------
| Roadmap — reserved routes for upcoming phases
|--------------------------------------------------------------------------
| Phase 3  Route::resource('products', ProductController::class);
*/

require __DIR__.'/auth.php';
