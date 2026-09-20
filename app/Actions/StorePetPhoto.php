<?php

namespace App\Actions;

use App\Models\Pet;
use App\Models\PetPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stores an uploaded pet photo, downscaled.
 *
 * The site runs on shared hosting with a fixed disk quota, and phone cameras
 * produce 4000px, multi-megabyte files that no page ever displays at that size.
 * Every upload is re-encoded to at most MAX_EDGE px of JPEG, which typically
 * turns 5 MB into ~200 KB.
 */
class StorePetPhoto
{
    private const MAX_EDGE = 1600;

    private const QUALITY = 82;

    public function handle(Pet $pet, UploadedFile $file, bool $primary = false): PetPhoto
    {
        $path = $this->storeDownscaled($file);

        // The first photo on a listing is its primary unless told otherwise.
        $primary = $primary || ! $pet->photos()->exists();

        if ($primary) {
            $pet->photos()->update(['is_primary' => false]);
        }

        return PetPhoto::create([
            'pet_id' => $pet->id,
            'path' => $path,
            'alt' => "Photo of {$pet->name}",
            'is_primary' => $primary,
            'sort_order' => (int) $pet->photos()->max('sort_order') + 1,
        ]);
    }

    /** Re-encodes to JPEG within MAX_EDGE, falling back to the original if GD can't read it. */
    private function storeDownscaled(UploadedFile $file): string
    {
        $name = 'pets/'.bin2hex(random_bytes(16)).'.jpg';

        if (! function_exists('imagecreatefromstring')) {
            return $file->store('pets', 'public');
        }

        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            return $file->store('pets', 'public');
        }

        $scaled = imagescale(
            $image,
            ...$this->targetSize(imagesx($image), imagesy($image)),
        );

        if ($scaled !== false) {
            imagedestroy($image);
            $image = $scaled;
        }

        ob_start();
        imagejpeg($image, null, self::QUALITY);
        $jpeg = (string) ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($name, $jpeg);

        return $name;
    }

    /**
     * @return array{int, int} width, height — never upscales a small photo.
     */
    private function targetSize(int $width, int $height): array
    {
        $longest = max($width, $height);

        if ($longest <= self::MAX_EDGE) {
            return [$width, $height];
        }

        $ratio = self::MAX_EDGE / $longest;

        return [(int) round($width * $ratio), (int) round($height * $ratio)];
    }
}
