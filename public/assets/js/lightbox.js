const modal = document.getElementById('modal');
if (modal) {
	modal.addEventListener('show.bs.modal', (event) => {
		let button = event.relatedTarget;
		let id = button.getAttribute('data-bs-id');
		let slides = modal.querySelectorAll('.carousel-item');
		slides.forEach((element) => element.classList.remove('active'));
		let slide = modal.querySelector('[data-bs-id="' + id + '"]');
		slide.classList.add('active');
	});
}
