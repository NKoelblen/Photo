/* Disable Post Parent & Children on document load */

let checkedParent = document.querySelector('input[name="parent_id"]:checked');
let childToDisable;
let ascendantToDisable;
if (checkedParent) {
	childToDisable = document.querySelector('input[name="children_ids[]"][value="' + checkedParent.value + '"]');
	childToDisable.setAttribute('disabled', '');
	childToDisable.setAttribute('hidden', '');

	disable_ascendants(childToDisable);
}

let checkedChildren = document.querySelectorAll('input[name="children_ids[]"]:checked');
if (checkedChildren) {
	checkedChildren.forEach((child) => {
		let parentToDisable = document.querySelector('input[name="parent_id"][value="' + child.value + '"]');
		parentToDisable.setAttribute('disabled', '');
		parentToDisable.setAttribute('hidden', '');

		disable_descendants(parentToDisable);
	});
}

/* On Parent change, change disabled Child */

const parents = document.querySelectorAll('input[name="parent_id"]');
parents.forEach((parent) => {
	parent.addEventListener('change', (event) => {
		if (childToDisable) {
			childToDisable.removeAttribute('disabled', '');
			childToDisable.removeAttribute('hidden', '');

			enable_ascendants(childToDisable);
		}

		checkedParent = event.target.value;

		childToDisable = document.querySelector('input[name="children_ids[]"][value="' + checkedParent + '"]');
		childToDisable.setAttribute('disabled', '');
		childToDisable.setAttribute('hidden', '');

		disable_ascendants(childToDisable);
	});
});

/* On Child change, change Parent visibility */

const children = document.querySelectorAll('input[name="children_ids[]"]');
children.forEach((child) => {
	child.addEventListener('change', (event) => {
		let isCheckedChild = event.target.checked;
		let checkedChild = event.target.value;
		let parentToChange = document.querySelector('input[name="parent_id"][value="' + checkedChild + '"]');
		if (isCheckedChild) {
			parentToChange.setAttribute('disabled', '');
			parentToChange.setAttribute('hidden', '');
			disable_descendants(parentToChange);
		} else {
			parentToChange.removeAttribute('disabled', '');
			parentToChange.removeAttribute('hidden', '');
			enable_descendants(parentToChange);
		}
	});
});

/* Functions */

function disable_ascendants(childToDisable) {
	ascendantToDisable = document.querySelector('input[name="children_ids[]"][value="' + childToDisable.dataset.parent + '"]');
	console.log(ascendantToDisable);
	if (ascendantToDisable) {
		ascendantToDisable.setAttribute('disabled', '');
		ascendantToDisable.setAttribute('hidden', '');
		disable_ascendants(ascendantToDisable);
	}
}

function enable_ascendants(childToEnable) {
	ascendantToDisable = document.querySelector('input[name="children_ids[]"][value="' + childToEnable.dataset.parent + '"]');
	if (ascendantToDisable) {
		ascendantToDisable.removeAttribute('disabled', '');
		ascendantToDisable.removeAttribute('hidden', '');
		enable_ascendants(ascendantToDisable);
	}
}

function disable_descendants(parentToDisable) {
	let descendantsToDisable = document.querySelectorAll('input[name="parent_id"][data-parent="' + parentToDisable.value + '"]');
	if (descendantsToDisable) {
		descendantsToDisable.forEach((descendant) => {
			descendant.setAttribute('disabled', '');
			descendant.setAttribute('hidden', '');

			disable_descendants(descendant);
		});
	}
}

function enable_descendants(parentToEnable) {
	let descendantsToDisable = document.querySelectorAll('input[name="parent_id"][data-parent="' + parentToEnable.value + '"]');
	if (descendantsToDisable) {
		descendantsToDisable.forEach((descendant) => {
			descendant.removeAttribute('disabled', '');
			descendant.removeAttribute('hidden', '');

			enable_descendants(descendant);
		});
	}
}
