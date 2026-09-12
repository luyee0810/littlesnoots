<?php

namespace Database\Seeders;

use App\Models\PetMemorial;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MemorialDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $authors = User::whereIn('email', ['admin@littlesnoots.test', 'staff@littlesnoots.test'])->get();

        if ($authors->isEmpty()) {
            $authors = User::factory(2)->create();
        }

        // A handful of visitors to light candles and sign the guestbook.
        $visitors = User::factory(5)->create();

        $tributes = [
            [
                'pet_name' => 'Tiger',
                'species' => 'Kucing Kampung',
                'photo' => '/images/seed/cats/cat-03.jpg',
                'born_on' => '2009-06-14',
                'passed_on' => '2024-02-01',
                'tribute' => "Fifteen years of sunbeams and midnight zoomies. Tiger greeted every guest at the door and slept on my chest through every hard night.\n\nYou were never just a cat. Rest easy, old friend.",
            ],
            [
                'pet_name' => 'Bella',
                'species' => 'Golden Retriever',
                'photo' => '/images/seed/dogs/dog-05.jpg',
                'born_on' => '2012-03-22',
                'passed_on' => '2023-11-18',
                'tribute' => "The gentlest soul I have ever known. Bella loved the beach, stolen socks, and every single person she ever met.\n\nEleven years was not nearly enough. Thank you for all of it.",
            ],
            [
                'pet_name' => 'Coco',
                'species' => 'Holland Lop',
                'photo' => '/images/seed/rabbits/rabbit-02.jpg',
                'born_on' => '2018-01-10',
                'passed_on' => '2024-05-30',
                'tribute' => 'A tiny bundle of mischief who ruled the house with a twitch of her nose. We miss you binkying across the living room, little one.',
            ],
        ];

        $messages = [
            'Sending love to your family. What a beautiful soul. 🕯️',
            'They were so lucky to have you. Rest in peace, sweet one.',
            'This brought tears to my eyes. Thinking of you.',
            'Run free at the rainbow bridge. ❤️',
        ];

        foreach ($tributes as $i => $data) {
            $author = $authors[$i % $authors->count()];

            $memorial = PetMemorial::create([
                'user_id' => $author->id,
                'slug' => Str::slug($data['pet_name']).'-'.Str::lower(Str::random(6)),
                'pet_name' => $data['pet_name'],
                'species' => $data['species'],
                'photo_path' => $data['photo'],
                'born_on' => $data['born_on'],
                'passed_on' => $data['passed_on'],
                'tribute' => $data['tribute'],
            ]);

            // Candles — one per visitor, honouring the unique constraint.
            foreach ($visitors->random(rand(2, 5)) as $visitor) {
                $memorial->candles()->create(['user_id' => $visitor->id]);
            }

            // A couple of guestbook messages.
            foreach ($visitors->random(rand(1, 3)) as $j => $visitor) {
                $memorial->messages()->create([
                    'user_id' => $visitor->id,
                    'body' => $messages[$j % count($messages)],
                ]);
            }
        }
    }
}
