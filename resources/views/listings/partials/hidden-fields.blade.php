{{-- The photo form posts to the same validated endpoint as the main form, so it
     has to carry the required fields unchanged. --}}
@foreach (['name', 'species_id', 'age_group', 'gender', 'size', 'status', 'description'] as $field)
    <input type="hidden" name="{{ $field }}" value="{{ $pet->{$field} }}">
@endforeach
