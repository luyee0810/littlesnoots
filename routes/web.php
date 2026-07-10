<?php

use App\Http\Controllers\AdoptionApplicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetController;
use App\Models\Pet;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'featured' => Pet::published()->available()
            ->with(['species', 'photos'])
            ->latest('published_at')
            ->take(4)
            ->get(),
    ]);
})->name('home');

// ---- Pet adoption (Phase 1) --------------------------------------------
Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show');
Route::post('/pets/{pet}/apply', [AdoptionApplicationController::class, 'store'])
    ->name('pets.apply');

// ---- Adopter dashboard --------------------------------------------------
Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Roadmap — reserved routes for upcoming phases
|--------------------------------------------------------------------------
| Phase 2  Route::resource('services', ServiceController::class);
| Phase 3  Route::resource('products', ProductController::class);
*/

require __DIR__.'/auth.php';
