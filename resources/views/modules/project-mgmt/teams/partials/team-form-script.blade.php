@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.needs-validation');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });

    if (window.jQuery && $.fn.select2) {
        $('.searchable-select').select2({
            width: '100%',
            allowClear: true
        });
    }

    const dropdown = document.getElementById('teamMembersDropdown');
    if (!dropdown) return;

    const toggle = dropdown.querySelector('.wmc-checkbox-dropdown-toggle');
    const text = document.getElementById('teamMembersText');
    const search = document.getElementById('teamMembersSearch');
    const options = dropdown.querySelectorAll('.wmc-checkbox-option');
    const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');

    function updateText() {
        const selected = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.dataset.label);

        text.textContent = selected.length ? selected.join(', ') : 'Select Members';
        text.style.color = selected.length ? '#232d42' : '#8a92a6';
    }

    toggle.addEventListener('click', function () {
        dropdown.classList.toggle('open');

        if (dropdown.classList.contains('open')) {
            setTimeout(() => search.focus(), 50);
        }
    });

    search.addEventListener('input', function () {
        const term = this.value.toLowerCase();

        options.forEach(option => {
            option.style.display = option.dataset.name.includes(term) ? 'flex' : 'none';
        });
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateText));

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });

    updateText();
});
</script>
@endpush