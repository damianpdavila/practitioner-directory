# practitioner-directory
Displays list of veterinary practitioners on Google map, pulled from members in Joomla Membership Pro component.

Built as a template override for the Membership Pro component's "member list" view.  It leverages the existing model and controller, but overrides the view PHP file.

## Features
* Creates and displays a Google map, initialized to fit entire globe.
* Displays all qualifying members on the map as pins.
* Groups nearby multiple pins into grouping icons with a count of individual pins within.
* Clicking on group expands to show next lower level of grouping (if available), or individual pins.
* Clicking on pin displays basic information about the member in a pop-up card.
* Displays list of members that are visible within current map view.
* Clicking on member in list zooms map to that specific pin and displays the popup.
* Moving or zooming the map dynamically adjusts the members that appear on the map and in the list.
