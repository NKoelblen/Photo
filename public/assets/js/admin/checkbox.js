const checkbox = document.querySelector('thead input[type="checkbox"]');
let checked = false;
const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
checkbox.addEventListener('change', (e) => {
	checked = !checked;
	checkboxes.forEach((item) => {
		item.checked = checked;
	});
});
checkboxes.forEach((item) => {
	item.addEventListener('change', (e) => {
		checkbox.indeterminate = true;
	});
});
