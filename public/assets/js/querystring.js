let form = document.querySelector('#filter-form');
form.addEventListener('formdata', function (event) {
	let formData = event.formData;
	for (let [name, value] of Array.from(formData.entries())) {
		if (value === '') {
			formData.delete(name);
		}
	}
});
