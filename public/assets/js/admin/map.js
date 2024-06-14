jQuery(function ($) {
	let map = L.map('map', {
		zoomControl: false,
	});
	L.control
		.zoom({
			position: 'topright',
		})
		.addTo(map);

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

	let marker = {};
	let latlng = $('#coordinates').val();
	if (latlng) {
		marker = L.marker(latlng.split(','), { draggable: true }).addTo(map);

		map.setView(latlng.split(','), 7);

		marker.on('dragend', function (e) {
			let newPos = e.target.getLatLng();
			lat = newPos.lat.toFixed(5);
			lng = newPos.lng.toFixed(5);
			$('#coordinates').val(lat + ',' + lng);
		});

		marker.on('click', function (e) {
			map.removeLayer(marker);
			$('#coordinates').val('');
		});
	} else {
		map.setView([48.5112, 2.2055], 4);
	}

	map.on('click', function (e) {
		lat = e.latlng.lat.toFixed(5);
		lng = e.latlng.lng.toFixed(5);

		//Clear existing marker,
		if (marker) {
			map.removeLayer(marker);
		}

		//Add a marker.
		marker = L.marker(e.latlng, { draggable: true }).addTo(map);
		$('#coordinates').val(lat + ', ' + lng);

		marker.on('dragend', function (e) {
			let newPos = e.target.getLatLng();
			lat = newPos.lat.toFixed(5);
			lng = newPos.lng.toFixed(5);
			$('#coordinates').val(lat + ', ' + lng);
		});

		marker.on('click', function (e) {
			map.removeLayer(marker);
			$('#coordinates').val('');
		});
	});

	map.addControl(
		new L.Control.Search({
			url: 'https://nominatim.openstreetmap.org/search?format=json&q={s}',
			jsonpParam: 'json_callback',
			propertyName: 'display_name',
			propertyLoc: ['lat', 'lon'],
			autoCollapse: false,
			collapsed: false,
			autoType: true,
			minLength: 1,
			zoom: 7,
			marker: false,
			firstTipSubmit: true,
			hideMarkerOnCollapse: true,
			position: 'topleft',
		}).on('search:locationfound', function (e) {
			//Clear existing marker,
			if (marker) {
				map.removeLayer(marker);
			}

			lat = e.latlng.lat.toFixed(5);
			lng = e.latlng.lng.toFixed(5);

			//Add a marker.
			marker = L.marker(e.latlng, { draggable: true }).addTo(map);
			$('#coordinates').val(lat + ', ' + lng);

			marker.on('dragend', function (e) {
				let newPos = e.target.getLatLng();
				lat = newPos.lat.toFixed(5);
				lng = newPos.lng.toFixed(5);
				$('#coordinates').val(lat + ', ' + lng);
			});

			marker.on('click', function (e) {
				map.removeLayer(marker);
				$('#coordinates').val('');
			});
		})
	);
});
