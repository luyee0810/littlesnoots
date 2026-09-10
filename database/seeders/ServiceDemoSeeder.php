<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\ProviderPhoto;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceCategory;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceDemoSeeder extends Seeder
{
    /** Malaysian names, so the demo data reads like the market it's for. */
    private const PROVIDER_NAMES = [
        'Nurul Aisyah', 'Wei Ming Tan', 'Priya Raman', 'Ahmad Faiz', 'Lim Sze Ying',
        'Siti Zulaikha', 'Daniel Wong', 'Kavitha Menon', 'Hafiz Rahman', 'Chong Mei Ling',
        'Farah Iskandar', 'Ravi Subramaniam', 'Tan Jia Hui', 'Zainal Abidin', 'Yap Wen Xin',
        'Aisha Kamal', 'Marcus Lee', 'Devi Krishnan', 'Amirul Hakim', 'Ong Pei Shan',
    ];

    private const OWNER_NAMES = [
        'Sarah Lim', 'Iqbal Hassan', 'Mei Chen', 'Arjun Pillai', 'Nadia Yusof', 'Kenneth Goh',
    ];

    public function run(): void
    {
        $categories = ServiceCategory::ordered()->get();

        if ($categories->isEmpty()) {
            $this->call(ServiceCategorySeeder::class);
            $categories = ServiceCategory::ordered()->get();
        }

        $speciesIds = Species::pluck('id', 'slug');

        // ---- A known provider login -------------------------------------
        $demoSitter = User::factory()->create([
            'name' => 'Nurul Aisyah',
            'email' => 'sitter@twofatcats.test',
            'phone' => '012-345 6789',
        ]);

        $providers = collect([$this->makeProvider($demoSitter, $categories, [
            'slug' => 'nurul-aisyah',
            'headline' => 'Cat-obsessed sitter with a quiet spare room in Bangsar',
            'city' => 'Bangsar',
            'state' => 'Kuala Lumpur',
            'postcode' => '59100',
            'years_experience' => 6,
            'rating_avg' => 4.9,
            'reviews_count' => 47,
            'bookings_count' => 112,
        ])]);

        // ---- The rest of the marketplace --------------------------------
        foreach (array_slice(self::PROVIDER_NAMES, 1) as $name) {
            $user = User::factory()->create([
                'name' => $name,
                'email' => Str::slug($name).'@twofatcats.test',
                'phone' => '01'.fake()->numerify('#-### ####'),
            ]);

            $providers->push($this->makeProvider($user, $categories, [
                'slug' => Str::slug($name),
            ]));
        }

        // ---- Pet owners with bookings in every status --------------------
        $owners = collect(self::OWNER_NAMES)->map(fn (string $name) => User::factory()->create([
            'name' => $name,
            'email' => Str::slug($name).'@example.test',
            'phone' => '01'.fake()->numerify('#-### ####'),
        ]));

        $statuses = ['pending', 'pending', 'accepted', 'accepted', 'completed', 'completed', 'declined', 'cancelled_by_owner', 'expired'];

        foreach ($statuses as $status) {
            $provider = $providers->random();
            $service = $provider->services->random();
            $owner = $owners->random();

            $this->makeBooking($service, $owner, $status, $speciesIds);
        }
    }

    /** @param  array<string, mixed>  $overrides */
    private function makeProvider(User $user, $categories, array $overrides = []): ProviderProfile
    {
        $profile = ProviderProfile::factory()
            ->forUser($user)
            ->create($overrides);

        // Two or three services, each in a distinct category.
        $selectedCategories = $categories->random(fake()->numberBetween(2, 3));

        foreach ($selectedCategories as $category) {
            ProviderService::factory()
                ->forCategory($category)
                ->create([
                    'provider_profile_id' => $profile->id,
                    'max_pets' => $profile->max_pets_per_booking,
                ]);
        }

        // A photo per service the provider offers: people at work with pets,
        // not portraits of pets on their own. Picked deterministically from the
        // bundled library so they survive a reseed.
        foreach ($selectedCategories->values() as $i => $category) {
            ProviderPhoto::create([
                'provider_profile_id' => $profile->id,
                'service_category_id' => $category->id,
                'url' => $this->servicePhotoPath($category->slug),
                'caption' => self::photoCaption($category->slug, $user->name),
                'is_primary' => $i === 0,
                'sort_order' => $i,
            ]);
        }

        // A few blocked-out days in the next month.
        foreach (range(1, 3) as $i) {
            $profile->unavailableDates()->firstOrCreate(
                ['date' => now()->addDays(fake()->numberBetween(3, 40))->toDateString()],
                ['reason' => fake()->randomElement(['Travelling', 'Fully booked', 'Family commitment'])],
            );
        }

        return $profile->load('services');
    }

    /** How many photos each category has handed out so far, so no two sitters
     *  in the same listing show the same picture. */
    private array $photoCursor = [];

    /**
     * A bundled photo showing this service being carried out — the sitter's
     * spare room, the walker on the pavement, the taxi's back seat. Falls back
     * to the pet library if the service set hasn't been downloaded.
     */
    private function servicePhotoPath(string $categorySlug): string
    {
        $files = glob(public_path("images/seed/services/{$categorySlug}/*.jpg"));
        $n = $this->photoCursor[$categorySlug] = ($this->photoCursor[$categorySlug] ?? -1) + 1;

        if (! $files) {
            return DatabaseSeeder::photoPath(self::photoSpecies($categorySlug), $n);
        }

        sort($files);

        return '/images/seed/services/'.$categorySlug.'/'.basename($files[$n % count($files)]);
    }

    /** What the primary photo is showing, in the provider's own terms. */
    private static function photoCaption(string $categorySlug, string $name): string
    {
        return match ($categorySlug) {
            'boarding' => "Where your pet stays with {$name}",
            'house-sitting' => "{$name} settling in at a client's home",
            'dog-walking' => "{$name} out on a walk",
            'daycare' => "A daycare group with {$name}",
            'grooming' => "{$name} at the grooming table",
            'pet-taxi' => "{$name}'s car, set up for pet trips",
            'training' => "{$name} working through a training session",
            default => "{$name} at work",
        };
    }

    /** The species whose photos best illustrate a given service category. */
    private static function photoSpecies(string $categorySlug): string
    {
        return match ($categorySlug) {
            'dog-walking', 'daycare', 'pet-taxi', 'training' => 'Dog',
            default => 'Cat',
        };
    }

    private function makeBooking(ProviderService $service, User $owner, string $status, $speciesIds): void
    {
        $category = $service->category;
        $units = $category->requires_date_range ? fake()->numberBetween(2, 6) : 1;
        $starts = in_array($status, ['completed', 'expired'], true)
            ? fake()->dateTimeBetween('-2 months', '-1 week')
            : fake()->dateTimeBetween('+3 days', '+2 months');

        $booking = Booking::factory()
            ->forService($service, $units)
            ->create([
                'user_id' => $owner->id,
                'starts_at' => $starts,
                'ends_at' => $category->requires_date_range
                    ? (clone $starts)->modify("+{$units} days")
                    : null,
                'pet_species_id' => $speciesIds['cat'] ?? null,
                'owner_name' => $owner->name,
                'owner_email' => $owner->email,
                'owner_phone' => $owner->phone,
                'service_city' => $service->providerProfile->city,
                'message' => 'Hi! Hoping you can look after my cat — she is shy but settles quickly.',
            ]);

        // Set the end state directly; the guarded transitions are for real flows.
        $booking->forceFill(match ($status) {
            'accepted' => ['status' => 'accepted', 'responded_at' => now()->subDays(2)],
            'completed' => ['status' => 'completed', 'responded_at' => now()->subDays(20), 'completed_at' => now()->subDays(5)],
            'declined' => ['status' => 'declined', 'responded_at' => now()->subDay(), 'provider_response' => 'Sorry, I am away that week.'],
            'cancelled_by_owner' => ['status' => 'cancelled_by_owner', 'cancelled_at' => now()->subDays(3)],
            'expired' => ['status' => 'expired', 'expires_at' => now()->subWeek()],
            default => ['status' => 'pending'],
        })->save();
    }
}
