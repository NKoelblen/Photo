const status_selector = document.querySelector('#status');
const parentChildren = document.querySelector('#parent-children');
const selectors = parentChildren.querySelectorAll('.form-check-input');
if (status_selector.value === 'draft') {
	parentChildren.classList.add('d-none');
	selectors.forEach((selector) => {
		selector.checked = false;
	});
}
status_selector.addEventListener('change', function () {
	if (this.value === 'draft') {
		parentChildren.classList.add('d-none');
		selectors.forEach((selector) => {
			selector.checked = false;
		});
	} else {
		parentChildren.classList.remove('d-none');
	}
});
