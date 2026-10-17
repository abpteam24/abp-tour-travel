//slick slider for related product
(function ($) {
    "use strict";
    $(document).ready(function () {
        var $relatedGrid = $('div.abptt_area .related_item_area .abptt_grid');
        $relatedGrid.slick({
            dots: false,
            arrows: false,
            infinite: true,
            centerMode: false,
            autoplay: true,
            autoplaySpeed: 4000,
            centerPadding: '0',
            slidesToShow: 3,
            slidesToScroll: 1,
            responsive: [
                {
                    breakpoint: 1000,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: false,
                        centerMode: false
                    }
                },
                {
                    breakpoint: 700,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: false,
                        centerMode: false
                    }
                },
                {
                    breakpoint: 500,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        centerMode: false
                    }
                }
            ]
        });
        $('.abptt_area .related_item_area .related_prev').on('click', function () {
            $relatedGrid.slick('slickPrev');
        });
        $('.abptt_area .related_item_area .related_next').on('click', function () {
            $relatedGrid.slick('slickNext');
        });
    });
}(jQuery));

(function ($) {
    'use strict';
    $(document).on('click', '.abptt_schedule_toggle', function (event) {
        event.preventDefault();
        var $button = $(this);
        var $dropdown = $button.siblings('.abptt_schedule_dropdown');
        var isOpen = $button.attr('aria-expanded') === 'true';
        $('.abptt_schedule_toggle').not($button).attr('aria-expanded', 'false');
        $('.abptt_schedule_dropdown').not($dropdown).prop('hidden', true);
        $button.attr('aria-expanded', isOpen ? 'false' : 'true');
        $dropdown.prop('hidden', isOpen);
    });
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.abptt_schedule_picker').length) {
            $('.abptt_schedule_toggle').attr('aria-expanded', 'false');
            $('.abptt_schedule_dropdown').prop('hidden', true);
        }
    });
    $(document).on('keydown', function (event) {
        if (event.key === 'Escape') {
            $('.abptt_schedule_toggle').attr('aria-expanded', 'false');
            $('.abptt_schedule_dropdown').prop('hidden', true);
        }
    });
}(jQuery));

(function ($) {
    'use strict';
    function initMapCanvases(scope) {
        if (typeof window.google === 'undefined' || typeof window.google.maps === 'undefined') return;
        $(scope).find('.abptt_map_canvas:not([data-gmap-ready])').each(function () {
            var canvas = $(this);
            canvas.attr('data-gmap-ready', '1');
            var lat = parseFloat(canvas.attr('data-map-lat'));
            var lng = parseFloat(canvas.attr('data-map-lng'));
            if (isNaN(lat) || isNaN(lng)) return;
            var label = canvas.attr('data-map-label') || '';
            var address = canvas.attr('data-map-address') || '';
            var map = new google.maps.Map(canvas[0], {
                center: { lat: lat, lng: lng },
                zoom: 15,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false
            });
            var marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: map,
                title: label,
                animation: google.maps.Animation.DROP
            });
            if (address || label) {
                var content = document.createElement('div');
                if (label) {
                    var strong = document.createElement('strong');
                    strong.textContent = label;
                    content.appendChild(strong);
                }
                if (address) {
                    var line = document.createElement('div');
                    line.textContent = address;
                    content.appendChild(line);
                }
                var info = new google.maps.InfoWindow({ content: content });
                google.maps.event.addListener(marker, 'click', function () {
                    info.open(map, marker);
                });
            }
        });
    }
    window.abptt_gmap_frontend_init = function () {
        initMapCanvases(document);
    };
    $(function () {
        if (typeof window.google !== 'undefined' && typeof window.google.maps !== 'undefined') {
            initMapCanvases(document);
        }
    });
}(jQuery));

/**
 * Animated tour route maps (Leaflet + OpenStreetMap, no API key required).
 */
(function ($) {
    'use strict';

    var mapCfg = window.abptt_map_config || {};

    function googleReady() {
        return typeof window.google !== 'undefined' && window.google.maps && typeof window.google.maps.DirectionsService === 'function';
    }

    function parseStops(canvas) {
        var raw = canvas.attr('data-abptt-stops');
        if (!raw) return [];
        try {
            var list = JSON.parse(raw);
            if (!list || typeof list.length === 'undefined') return [];
            var out = [];
            for (var i = 0; i < list.length; i++) {
                var s = list[i];
                var lat = parseFloat(s.lat);
                var lng = parseFloat(s.lng);
                if (isNaN(lat) || isNaN(lng)) continue;
                out.push({ lat: lat, lng: lng, label: s.label || '', address: s.address || '' });
            }
            return out;
        } catch (e) {
            return [];
        }
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function themeColor(el) {
        try {
            var c = window.getComputedStyle(el).getPropertyValue('--tt_color_theme').trim();
            return c || '#0ea5e9';
        } catch (e) {
            return '#0ea5e9';
        }
    }

    function googleEmbedUrl(stops) {
        var parts = [];
        for (var i = 0; i < stops.length; i++) parts.push(stops[i].lat + ',' + stops[i].lng);
        var src = 'https://maps.google.com/maps?output=embed&z=6';
        if (parts.length >= 2) {
            src += '&saddr=' + encodeURIComponent(parts[0]) + '&daddr=' + encodeURIComponent(parts.slice(1).join(' to '));
        } else if (parts.length === 1) {
            src += '&q=' + encodeURIComponent(parts[0]);
        }
        return src;
    }

    function fallbackIframe(canvas) {
        var stops = parseStops(canvas);
        if (!stops.length) return;
        canvas.attr('data-route-ready', '1');
        canvas.html('<iframe class="abptt_map_iframe" style="width:100%;height:100%;border:0;" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" title="Tour route map" src="' + googleEmbedUrl(stops) + '"></iframe>');
    }

    function osrmSegment(a, b) {
        var url = 'https://router.project-osrm.org/route/v1/driving/' +
            a.lng + ',' + a.lat + ';' + b.lng + ',' + b.lat +
            '?overview=full&geometries=geojson';
        return fetch(url).then(function (r) {
            if (!r.ok) throw new Error('osrm');
            return r.json();
        }).then(function (data) {
            if (!data.routes || !data.routes[0] || !data.routes[0].geometry) throw new Error('osrm');
            var coords = data.routes[0].geometry.coordinates;
            var out = [];
            for (var i = 0; i < coords.length; i++) out.push([coords[i][1], coords[i][0]]);
            return out;
        });
    }

    function straightOrRoute(a, b) {
        return osrmSegment(a, b).catch(function () {
            return [[a.lat, a.lng], [b.lat, b.lng]];
        });
    }

    function buildPath(stops) {
        if (stops.length < 2) {
            return Promise.resolve(stops.map(function (s) { return [s.lat, s.lng]; }));
        }
        var tasks = [];
        for (var i = 0; i < stops.length - 1; i++) tasks.push(straightOrRoute(stops[i], stops[i + 1]));
        return Promise.all(tasks).then(function (segments) {
            var path = [];
            for (var k = 0; k < segments.length; k++) {
                var seg = segments[k];
                if (k > 0 && seg.length) seg = seg.slice(1);
                path = path.concat(seg);
            }
            return path;
        });
    }

    function distance(a, b) {
        var dx = a[0] - b[0], dy = a[1] - b[1];
        return Math.sqrt(dx * dx + dy * dy);
    }

    function pointAt(path, dists, d) {
        if (d <= 0) return path[0];
        if (d >= dists[dists.length - 1]) return path[path.length - 1];
        var lo = 0, hi = dists.length - 1;
        while (lo < hi - 1) {
            var mid = (lo + hi) >> 1;
            if (dists[mid] <= d) lo = mid; else hi = mid;
        }
        var segLen = dists[hi] - dists[lo];
        var t = segLen === 0 ? 0 : (d - dists[lo]) / segLen;
        var a = path[lo], b = path[hi];
        return [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t];
    }

    function startMover(map, path) {
        if (path.length < 2) return;
        var icon = L.divIcon({ className: 'abptt_route_mover_wrap', html: '<span class="abptt_route_mover"></span>', iconSize: [16, 16], iconAnchor: [8, 8] });
        var mover = L.marker(path[0], { icon: icon, interactive: false, zIndexOffset: 1000 }).addTo(map);
        var dists = [0];
        for (var i = 1; i < path.length; i++) dists.push(dists[i - 1] + distance(path[i - 1], path[i]));
        var total = dists[dists.length - 1];
        if (total <= 0) return;
        var duration = 13000;
        var elapsed = 0, last = null;
        function frame(ts) {
            if (last === null) last = ts;
            elapsed += (ts - last);
            last = ts;
            var progress = (elapsed % duration) / duration;
            mover.setLatLng(pointAt(path, dists, total * progress));
            window.requestAnimationFrame(frame);
        }
        window.requestAnimationFrame(frame);
    }

    function buildRouteMap(canvas) {
        if (mapCfg.use_gmap && googleReady()) {
            buildGoogleRoute(canvas);
            return;
        }
        if (typeof window.L === 'undefined') {
            fallbackIframe(canvas);
            return;
        }
        var stops = parseStops(canvas);
        if (!stops.length) {
            canvas.find('.abptt_route_map_load').remove();
            return;
        }
        canvas.attr('data-route-ready', '1');
        canvas.find('.abptt_route_map_load').remove();

        var color = themeColor(canvas[0]);
        var map = L.map(canvas[0], { scrollWheelZoom: false, zoomControl: true }).setView([stops[0].lat, stops[0].lng], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
        }).addTo(map);

        for (var i = 0; i < stops.length; i++) {
            var cls = 'abptt_route_pin';
            if (i === 0) cls += ' abptt_route_pin_first';
            if (i === stops.length - 1 && stops.length > 1) cls += ' abptt_route_pin_last';
            var icon = L.divIcon({ className: 'abptt_route_pin_wrap', html: '<span class="' + cls + '">' + (i + 1) + '</span>', iconSize: [28, 28], iconAnchor: [14, 14] });
            var marker = L.marker([stops[i].lat, stops[i].lng], { icon: icon }).addTo(map);
            var html = '';
            if (stops[i].label) html += '<strong>' + escapeHtml(stops[i].label) + '</strong>';
            if (stops[i].address) html += '<div>' + escapeHtml(stops[i].address) + '</div>';
            if (html) marker.bindPopup(html);
        }

        var straight = [];
        for (var j = 0; j < stops.length; j++) straight.push([stops[j].lat, stops[j].lng]);
        var base = L.polyline(straight, { color: '#94a3b8', weight: 4, opacity: 0.5 }).addTo(map);
        map.fitBounds(base.getBounds(), { padding: [30, 30] });

        buildPath(stops).then(function (path) {
            if (path.length < 2) return;
            if (map.hasLayer(base)) map.removeLayer(base);
            L.polyline(path, { color: '#ffffff', weight: 9, opacity: 0.9 }).addTo(map);
            L.polyline(path, { color: color, weight: 5, opacity: 0.95 }).addTo(map);
            L.polyline(path, { color: '#ffffff', weight: 3, opacity: 0.95, className: 'abptt_route_dash' }).addTo(map);
            map.fitBounds(L.latLngBounds(path), { padding: [30, 30] });
            startMover(map, path);
        });

        window.setTimeout(function () { map.invalidateSize(); }, 200);
    }

    function buildGoogleRoute(canvas) {
        var stops = parseStops(canvas);
        if (!stops.length) {
            canvas.find('.abptt_route_map_load').remove();
            return;
        }
        canvas.attr('data-route-ready', '1');
        canvas.find('.abptt_route_map_load').remove();

        var color = themeColor(canvas[0]);
        var map = new google.maps.Map(canvas[0], {
            center: { lat: stops[0].lat, lng: stops[0].lng },
            zoom: 6,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        });

        if (stops.length < 2) {
            new google.maps.Marker({ position: { lat: stops[0].lat, lng: stops[0].lng }, map: map });
            return;
        }

        var directions = new google.maps.DirectionsService();
        var renderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: false,
            polylineOptions: { strokeColor: color, strokeWeight: 5, strokeOpacity: 0.9 }
        });

        var waypoints = [];
        for (var i = 1; i < stops.length - 1; i++) {
            waypoints.push({ location: new google.maps.LatLng(stops[i].lat, stops[i].lng), stopover: true });
        }

        directions.route({
            origin: new google.maps.LatLng(stops[0].lat, stops[0].lng),
            destination: new google.maps.LatLng(stops[stops.length - 1].lat, stops[stops.length - 1].lng),
            waypoints: waypoints,
            travelMode: google.maps.TravelMode.DRIVING
        }, function (result, status) {
            if (status === google.maps.DirectionsStatus.OK && result.routes && result.routes[0]) {
                renderer.setDirections(result);
                var route = result.routes[0];
                if (route.bounds) map.fitBounds(route.bounds);
                if (route.overview_path && route.overview_path.length > 1) {
                    var path = [];
                    for (var p = 0; p < route.overview_path.length; p++) {
                        path.push([route.overview_path[p].lat(), route.overview_path[p].lng()]);
                    }
                    googleMover(map, path);
                }
            } else {
                googleFallback(map, stops, color);
            }
        });
    }

    function googleFallback(map, stops, color) {
        var bounds = new google.maps.LatLngBounds();
        var straight = [];
        for (var i = 0; i < stops.length; i++) {
            var pos = new google.maps.LatLng(stops[i].lat, stops[i].lng);
            bounds.extend(pos);
            straight.push(pos);
            new google.maps.Marker({ position: pos, map: map, label: String(i + 1) });
        }
        if (straight.length > 1) {
            new google.maps.Polyline({ path: straight, map: map, strokeColor: color, strokeWeight: 5, strokeOpacity: 0.85 });
        }
        map.fitBounds(bounds);
    }

    function googleMover(map, path) {
        if (path.length < 2) return;
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(path[0][0], path[0][1]),
            map: map,
            zIndex: 999,
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 7, fillColor: '#0ea5e9', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 3 }
        });
        var dists = [0];
        for (var i = 1; i < path.length; i++) dists.push(dists[i - 1] + distance(path[i - 1], path[i]));
        var total = dists[dists.length - 1];
        if (total <= 0) return;
        var duration = 13000, elapsed = 0, last = null;
        function frame(ts) {
            if (last === null) last = ts;
            elapsed += (ts - last);
            last = ts;
            var pt = pointAt(path, dists, total * ((elapsed % duration) / duration));
            marker.setPosition(new google.maps.LatLng(pt[0], pt[1]));
            window.requestAnimationFrame(frame);
        }
        window.requestAnimationFrame(frame);
    }

    function initRouteMaps(scope) {
        if (mapCfg.use_gmap && !googleReady() && !window.abptt_gmap_forced) {
            return;
        }
        $(scope).find('.abptt_route_map:not([data-route-ready])').each(function () {
            buildRouteMap($(this));
        });
    }

    window.abptt_route_map_init = function () {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { initRouteMaps(document); });
        } else {
            initRouteMaps(document);
        }
    };
    window.abptt_gmap_init = window.abptt_route_map_init;

    $(function () {
        initRouteMaps(document);
        if (mapCfg.use_gmap && !googleReady()) {
            window.setTimeout(function () {
                window.abptt_gmap_forced = true;
                initRouteMaps(document);
            }, 8000);
        }
    });
}(jQuery));