jQuery(function ($) {
	let map = L.map('map');

	let observer = new window.MutationObserver(function (mutations, observer) {
		if (mutations[0].target === document.querySelector('.modal-outer.lieu')) {
			map.invalidateSize();
		}
	});
	observer.observe(document, {
		subtree: true,
		attributes: true,
	});

	L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
	}).addTo(map);

	if (markers) {
		let groupMarkers = L.markerClusterGroup();
		let location = $('h1').text();
		$.each(markers, function (i, marker_data) {
			let coordinates = marker_data['coordinates'].split(',');
			let thumbnail = $.parseJSON(marker_data['thumbnail']);
			let path = thumbnail['path'].startsWith('http') ? thumbnail['path'] : thumbnail['path'] + '-XS.webp';
			let content =
				'<a href="/location/' +
				marker_data['slug'] +
				'" class="link-underline link-underline-opacity-0"><div class="leaflet-popup-background" style="' +
				'background-image: url(' +
				path +
				');' +
				'"><div class="leaflet-popup-overlay"><h3 class="leaflet-popup-title">' +
				marker_data['title'] +
				'</h3></div></div></a>';
			if (~location.indexOf(marker_data['title'])) {
				L.popup({ offset: [1, -27] })
					.setLatLng(coordinates)
					.setContent(content)
					.openOn(map);
			}
			let marker = L.marker(coordinates);
			let popup = L.popup({ className: 'location' + marker_data['id'] }).setContent(content);
			marker.bindPopup(popup).openPopup();
			groupMarkers.addLayer(marker);
		});
		map.addLayer(groupMarkers);
		map.fitBounds(groupMarkers.getBounds());
	} else {
		map.setView([48.5112, 2.2055], 4);
	}
});
