/**
 * @name PracDirMap
 * @version version 1.0
 * @author Damian Davila
 * @fileoverview
 * Displays list of global businesses on a Google Map.  Utilizes MarkerClusterer.js to cluster nearby businesses, based upon zoom level.
 * Developed specifically for veterinary practitioners (hence the name) but generic to any business that fits the schema.
 * Developed to extend Joomla Membership Pro component's Member List page, but doesn't depend on anything from that page other than path to avatars which is set in a constant and can be changed.
 * @param {} practitioner_data Contains the array of practitioner name, address, etc info, plus the count of practitioners.
 */

var pdm_data = {};
var pdm_markers = [];
var pdm_circle = null;

var MAP_EL_ID = 'map';
var MARKER_LIST_EL_ID = '#markerlist';
var MAP_SEARCH_INPUT_EL_ID = 'pac-input';
var MAP_SEARCH_RADIUS_EL_ID = 'pac-radius';

const AVATAR_PATH = '/media/com_osmembership/avatars/';
// Can use custom marker images (e.g. replacements for pin) on a per-marker basis.  
// Just specify the directory path here and the file name in the data field 'profile_marker_picture'.
const MARKER_IMAGE_PATH = '';
const MARKER_DEFAULT_PATH = '/templates/aatcvm/images/';

function showPractitionerMap(practitioner_data, map_el_id = MAP_EL_ID, marker_list_el_id = MARKER_LIST_EL_ID, map_search_input_el_id = MAP_SEARCH_INPUT_EL_ID, map_search_radius_el_id = MAP_SEARCH_RADIUS_EL_ID) {

    pdm_data = practitioner_data;
    MAP_EL_ID = map_el_id;
    MARKER_LIST_EL_ID = marker_list_el_id;
    MAP_SEARCH_INPUT_EL_ID = map_search_input_el_id;
    MAP_SEARCH_RADIUS_EL_ID = map_search_radius_el_id;
    buildMap();
};

function SortByName(x, y) {
    return ((x.name == y.name) ? 0 : ((x.name > y.name) ? 1 : -1));
}

function buildMap(mar) {

    if (mar == 1) {
        pdm_markers = [];
    }

    jQuery(MARKER_LIST_EL_ID).html('');
    var geocoder = new google.maps.Geocoder();
    var bounds = new google.maps.LatLngBounds();
    var center = new google.maps.LatLng(25, 0);
    var mapOptions = {
        mapTypeId: 'roadmap',
        center: center,
        zoom: 1.7,
    };
    map = new google.maps.Map(document.getElementById(MAP_EL_ID), mapOptions);

    // Create the search box and link it to the UI element.
    var input = document.getElementById(MAP_SEARCH_INPUT_EL_ID);
    var searchBox = new google.maps.places.SearchBox(input);
    //         map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    map.setCenter(center);

    // Add multiple markers to map
    var infoWindow = new google.maps.InfoWindow(),
        marker, i;
    var marker_new = [];
    // Call Sort By Name
    pdm_data.data.sort(SortByName);

    for (var i = 0; i < pdm_data.member_count; i++) {

        if (pdm_data.data[i]) {

            if (pdm_data.data[i].profile_marker_picture) {
                var image_icon = MARKER_IMAGE_PATH + pdm_data.data[i].profile_marker_picture;
            } else {
                var image_icon = MARKER_DEFAULT_PATH + 'pin.png';
            }

            var latLng = new google.maps.LatLng(pdm_data.data[i].latitude, pdm_data.data[i].longitude);
            var marker = new google.maps.Marker({
                position: latLng,
                icon: image_icon,
            });

            bounds.extend(marker.getPosition());

            var panel = jQuery(MARKER_LIST_EL_ID);
            panel.innerHTML = '';

            var titleText = pdm_data.data[i].name;
            if (titleText === '') {
                titleText = 'No title';
            }

            var item = document.createElement('DIV');
            var title = document.createElement('A');
            title.href = '#';
            title.className = 'title';
            title.innerHTML = titleText;

            item.appendChild(title);
            jQuery(MARKER_LIST_EL_ID).append(item);

            var fn = markerClickFunction(pdm_data.data[i], latLng, infoWindow);

            google.maps.event.addDomListener(title, 'click', fn);

            // Add info window to marker    
            google.maps.event.addListener(marker, 'click', (function(marker, i) {

                var str = pdm_data.data[i].member_link;
                var info_html =
                    '<div class="info">' +
                    '<h3>' +
                    pdm_data.data[i].name +
                    '</h3>';
                let logo_img = pdm_data.data[i].avatar ? '<img class="map_logo" width="50" height="auto" src="' + AVATAR_PATH + pdm_data.data[i].avatar + '">' : '';
                info_html +=
                    logo_img + '<br/>' +
                    pdm_data.data[i].organization + '<br/>' +
                    // pdm_data.data[i].email + '<br/>' +
                    pdm_data.data[i].address + '<br/><br/>' +
                    '<a href=' + str + '>Read the full profile</a>' +
                    '</div>';

                return function() {
                    infoWindow.setContent(info_html);
                    infoWindow.open(map, marker);
                }
            })(marker, i));

            pdm_markers.push(marker);

        }
    }
    /*     
        map.fitBounds(bounds);*/

    map.setCenter(center);

    var viewport_array = [];

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());

        var viewport_array = [];

        for (var i = 0; i < pdm_markers.length; i++) {

            if (map.getBounds().contains(pdm_markers[i].getPosition())) {
                viewport_array.push(pdm_data.data[i]);
            }
        }

        if (viewport_array.length > 0) {
            jQuery(MARKER_LIST_EL_ID).html('');
            for (var j = 0; j < viewport_array.length; j++) {

                var latLng = new google.maps.LatLng(viewport_array[j].latitude,
                    viewport_array[j].longitude);

                var panel = jQuery(MARKER_LIST_EL_ID);
                panel.innerHTML = '';

                var titleText = viewport_array[j].name;
                if (titleText === '') {
                    titleText = 'No title';
                }

                var item = document.createElement('DIV');
                var title = document.createElement('A');
                title.href = '#';
                title.className = 'title';
                title.innerHTML = titleText;

                item.appendChild(title);
                jQuery(MARKER_LIST_EL_ID).append(item);

                var fn = markerClickFunction(viewport_array[j], latLng, infoWindow);

                google.maps.event.addDomListener(title, 'click', fn);

            }

        }



    });

    var markerCluster = new MarkerClusterer(map, pdm_markers, {
        imagePath: '/images/map/m'
    });
}

markerClickFunction = function(member, latlng, infoWindow) {
    return function(e) {

        if (e.stopPropagation) {
            e.stopPropagation();
            e.preventDefault();
        }
        var title = member.name;
        var address = member.address;
        var email = member.email;
        var fileurl = member.member_link;
        var org = member.organization;

        var infoHtml = '<div class="info">' +
            '<h3>' + title + '</h3>';
        let logo_img = member.avatar ? '<img class="map_logo" width="50" height="auto" src="' + AVATAR_PATH + member.avatar + '"><br/>' : '';
        infoHtml +=
            logo_img +
            '<div class="info-body">' + org + '</div><br/>' +
            // '<div class="info-body">' + email + '</div><br/>' +
            '<div class="info-body">' + address + '</div><br/>' +
            '<div class="info-body"><a href="' + fileurl + '" target="_blank">Read the full profile</a></div></div>';

        if (member.profile_marker_memberture) {
            var image_icon = MARKER_IMAGE_PATH + member.profile_marker_picture;

        } else {
            var image_icon = MARKER_DEFAULT_PATH + 'pin.png';
        }

        var marker = new google.maps.Marker({
            position: latlng,
            icon: image_icon,
        });

        infoWindow.setContent(infoHtml);
        infoWindow.setPosition(latlng);
        infoWindow.open(map);

        marker.setMap(map);
        map.setZoom(15);
        map.setCenter(latlng);
    };
};


function codeAddress() {

    var geocoder = new google.maps.Geocoder();

    if (document.getElementById(MAP_SEARCH_INPUT_EL_ID).value == '') {
        alert("Please enter a location ");
        return false;
    }
    var address = document.getElementById(MAP_SEARCH_INPUT_EL_ID).value;

    if (document.getElementById(MAP_SEARCH_RADIUS_EL_ID).value == '') {
        alert("Please enter radius ");
        return false;
    }
    var radius = parseInt(document.getElementById(MAP_SEARCH_RADIUS_EL_ID).value, 10) * 1000;

    var filter_users = [];

    geocoder.geocode({
        'address': address
    }, function(results, status) {

        if (status == google.maps.GeocoderStatus.OK) {
            side_bar_html = "";
            map.setCenter(results[0].geometry.location);
            var searchCenter = results[0].geometry.location;
            if (pdm_circle) pdm_circle.setMap(null);
            pdm_circle = new google.maps.Circle({
                center: searchCenter,
                radius: radius,
                fillOpacity: 0.35,
                fillColor: "#FF0000",
                map: map
            });
            var bounds = new google.maps.LatLngBounds();
            var foundMarkers = 0;

            for (var i = 0; i < pdm_markers.length; i++) {
                if (google.maps.geometry.spherical.computeDistanceBetween(pdm_markers[i].getPosition(), searchCenter) < radius) {

                    filter_users.push(pdm_data.data[i]);

                    bounds.extend(pdm_markers[i].getPosition())
                    pdm_markers[i].setMap(map);
                    // add a line to the side_bar html

                    foundMarkers++;
                } else {
                    pdm_markers[i].setMap(null);
                }
            }

            map.fitBounds(pdm_circle.getBounds());
            // put the assembled side_bar_html contents into the side_bar div

            var infoWindow = new google.maps.InfoWindow()

            if (filter_users.length > 0) {
                jQuery(MARKER_LIST_EL_ID).html('');
                for (var j = 0; j < filter_users.length; j++) {

                    var latLng = new google.maps.LatLng(filter_users[j].latitude,
                        filter_users[j].longitude);

                    var panel = jQuery(MARKER_LIST_EL_ID);
                    panel.innerHTML = '';

                    var titleText = filter_users[j].name;
                    if (titleText === '') {
                        titleText = 'No title';
                    }

                    var item = document.createElement('DIV');
                    var title = document.createElement('A');
                    title.href = '#';
                    title.className = 'title';
                    title.innerHTML = titleText;

                    item.appendChild(title);
                    jQuery(MARKER_LIST_EL_ID).append(item);

                    var fn = markerClickFunction(filter_users[j], latLng, infoWindow);

                    google.maps.event.addDomListener(title, 'click', fn);

                }

            }

        } else {
            alert('Geocode was not successful for the following reason: ' + status);
        }
    });
}


function reset_state() {
    buildMap(mar = 1);
    jQuery('#' + MAP_SEARCH_INPUT_EL_ID).val('');
    jQuery('#' + MAP_SEARCH_RADIUS_EL_ID).val('');

}