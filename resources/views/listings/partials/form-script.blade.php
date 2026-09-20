{{-- Two small progressive enhancements: the shelter picker, and filtering
     breeds to the chosen species so the list stays manageable. --}}
<script>
    (function () {
        // The shelter block is staff-only, so it may not be on the page at all.
        const orgField = document.getElementById('organization-field');
        if (orgField) {
            document.querySelectorAll('[data-org-toggle]').forEach((radio) => {
                radio.addEventListener('change', () => {
                    const show = radio.dataset.orgToggle === 'show';
                    orgField.hidden = ! show;
                    if (! show) document.getElementById('organization_id').value = '';
                });
            });
        }

        const species = document.getElementById('species_id');
        const filterBreeds = () => {
            const id = species.value;
            document.querySelectorAll('[data-breed-select] option[data-species]').forEach((opt) => {
                const match = ! id || opt.dataset.species === id;
                opt.hidden = ! match;
                if (! match && opt.selected) opt.parentElement.value = '';
            });
        };
        species.addEventListener('change', filterBreeds);
        filterBreeds();
    })();
</script>
