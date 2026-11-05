=== ABP Tour & Travel ===
Contributors: abpteam
Tags: tour tickets, ticket booking, seat reservation, tour registration, seat plan
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
WC requires at least: 8.0
WC tested up to: 11.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sell tour tickets online with WooCommerce using general admission, reserved seats, and guest forms.

== Description ==

ABP Tour and Travel turns WooCommerce into a complete tour ticketing and registration system. Create tours, configure ticket types and prices, publish tour details, collect guest information, and sell tickets through the standard WooCommerce checkout for tours, concerts, conferences, festivals, and workshops.

The plugin supports both general admission and reserved seating. Administrators can build reusable seat plans with drag-and-drop controls, automatic numbering, custom labels, ticket-type assignment, and flexible layouts. Seat availability is updated in real time as tickets are booked, keeping seat inventory in sync with each booking.

Create reusable global configuration for ticket types, seat plans, dates, locations, categories, operators, features, additional services, guest forms, FAQs, Terms & Conditions, tour timelines, and guides. Global configuration can be imported into an individual tour and customized when needed. Customers can book from any page with a lightweight shortcode, and a Bookings tab appears automatically in their WooCommerce account.

Read the [Full Documentation](https://demo-tt.abp-team.com/documentation/) for setup guides, or explore the [Live Demo](https://demo-tt.abp-team.com/) to try every feature.

== Key Features ==

* Tour creation and management
* Tour list with pagination and status filters
* Tour edit, clone, view, trash, restore, and permanent delete actions
* General admission ticket sales
* Reserved seat ticket sales
* Reusable global ticket types
* Multiple ticket types and pricing options
* Minimum, maximum, and reserved quantity controls
* Reusable seat plans
* Drag-and-drop seat plan designer
* Automatic seat numbering and custom seat prefixes
* Custom seat labels, cells, dimensions, spacing, and layout
* Ticket-type assignment to seats
* Multiple seat plan instances and seat layers
* Real-time seat availability
* Specific-date and periodic-date tour schedules
* Date-wise and day-wise time configuration
* Advance booking and sale closing buffer controls
* Location, category, and operator management
* Google Location Map with search-and-select admin picker and key-free iframe fallback
* Frontend location map with three display styles (default, light, modern)
* Tour features and related tour display
* Why Book With Us reasons block with per-reason icon, title, and description
* Configurable guest information forms
* Optional additional services with pricing
* Tour FAQs and Terms & Conditions
* Tour Timeline with time, title, and description entries
* Tour Guides with full profiles (bio, photo, designation, company, website, social links)
* Global and tour-specific configuration
* Feature enable and disable controls
* Tour search, filtering, and pagination
* Tour details templates and display themes
* Tour gallery and image slider
* WooCommerce cart, checkout, payment, tax, coupon, and order integration
* Booking and order details in the WordPress dashboard
* Translation-ready
* Gutenberg-compatible tour post type and taxonomies
* Polylang-compatible frontend language filtering when Polylang is active

== Ticket Types and Pricing ==

Create ticket types such as Adult, Child, VIP, or Early Bird and configure their prices and quantities. Ticket types can be created globally and reused across tours. For reserved seating tours, ticket types can also be assigned to specific seats or seat areas.

== Seat Plan Designer ==

The seat plan designer lets you create reusable seating layouts for theaters, conferences, classrooms, meeting points, and other tours.

Seat plans support:

* Drag-and-drop positioning
* Automatic seat numbering
* Custom seat names and prefixes
* Custom cells and text
* Flexible rows and columns
* Adjustable cell size and gaps
* Ticket-type assignment
* Multiple layout configurations
* Visual and background customization
* Clone, edit, view, and delete actions

== Tour Scheduling ==

Configure when tickets are available for each tour:

* Specific tour dates
* Periodic dates
* Date-wise times
* Day-wise times
* Advance booking date limits
* Sale closing buffer time
* Availability and quantity controls

== Google Location Map ==

Show a Google Map for the locations assigned to a tour. With a Google Maps JavaScript API key the interactive map renders via the JavaScript API and Places library; without a key, an embedded iframe renders instead, so the location still displays while the global switch is on.

Setup:

1. Enable Google Location Map under Global Configuration → Feature ON/OFF.
2. Paste your Google Maps JavaScript API key into the Google Maps API Key field in Global Configuration.
3. Open a Location in Global Configuration → Location, search for the address or place, and select it on the map to save its latitude, longitude, address, and place data.
4. Assign that location to a tour.

The map appears on the tour details page when the switch is on, the tour has a location, and the location has saved map data. It includes a search-and-select picker, a draggable marker, per-location data, three frontend styles (default, light, modern), an info window on click, and "Get Directions" links to Google Maps.

== Tour Timeline ==

Display a visual timeline on the tour details page showing activities in chronological order. Each entry has a time, title, and rich-text description.

Setup:

1. Enable Tour Timeline under Global Configuration → Feature ON/OFF.
2. Open a tour and go to the Timeline tab.
3. Turn on the "Active Timeline" switch for that tour.
4. Click "Add New Timeline Item" and fill in the time, title, and description for each entry.
5. Save the tour.

The timeline appears when the tour has at least one titled entry and follows the details template: Default uses a vertical line with dot markers and bordered cards, Light uses circle markers and shadowed cards, and Modern uses a header banner with badge markers and dashed cards. The global switch hides all timelines when OFF; the per-tour switch controls each tour.

== Tour Guides ==

Create and manage reusable guide profiles with rich information including bio, photo, designation, company, website, and social media links (Twitter/X, LinkedIn, Facebook, Instagram). A guide can be linked to any number of tours.

Setup:

1. Enable Tour Guide under Global Configuration → Feature ON/OFF.
2. Open Global Configuration → Guide.
3. Click "Add New Guide" and fill in the name, bio, photo, designation, company, website, and social links.
4. Save the guide.
5. Open a tour and go to the tour edit screen.
6. In the Guide section, search and select one or more guides to associate with the tour.
7. Save the tour.

Guide management includes:

* Add, edit, and delete guides from Global Configuration → Guide
* Rich-text guide bio
* Guide photo upload with image selection and removal
* Designation and company fields
* Website URL
* Social profile URLs for Twitter/X, LinkedIn, Facebook, and Instagram
* Optional per-guide slug, used for the guide profile page URL
* A guide picker in the tour editor with multi-select, so a single guide can be reused across many tours
* A per-tour "Active Guide" switch to show or hide the guide section for that tour
* A global Tour Guide switch that hides all guide features when turned OFF

Guides are stored as terms in the `abptt_guide` taxonomy, which is registered against the Tour post type. Per-guide data such as photo, designation, company, website, and social links is stored in guide meta.

= Frontend Display =

Linked guides appear automatically on the tour details page below the timeline, following the active details template: Default is a vertical list with photo, name, role, bio, and social links; Light is a responsive card grid; Modern is a centred avatar row in a bordered panel. Guides without a photo show their initials. The section is skipped when no guides are linked, the global Tour Guide switch is OFF, or the per-tour "Active Guide" switch is OFF (ON by default).

= Guide Page =

Each guide has a profile page at `/guide/{guide-slug}/` (the `guide` slug is configurable). It shows only the guide's saved data: photo or initials, name, designation and company, website, full bio, and social profiles, omitting empty fields. Override `page/guide.php` by copying it into `wp-content/tt_templates/page/`. To list a guide's tours elsewhere, use `[abptt-post guide_id="TERM_ID"]`; the term ID appears in Global Configuration → Guide.

= Display Hook =

To reposition or restyle the guide section, use `do_action( 'abptt_guide', $post_infos, $post_id, 'default' );` with the tour meta array, tour post ID, and style (`default`, `light`, or `modern`). Each style also fires `abptt_guide_{style}_template` after rendering.

= Template Overrides =

Copy `default.php`, `light.php`, or `modern.php` from the plugin's `tt_templates/guide` folder into `wp-content/tt_templates/guide/` and edit your copy; the plugin uses it automatically.

== Why Book With Us ==

Show a "Why Book With Us" reasons block on every tour page. The block has a heading plus a repeatable list of reasons, and each reason has its own icon, title, and description.

Setup:

1. Enable Why Book With Us under Global Configuration → Feature ON/OFF.
2. Open Global Configuration → Why Book With Us ?.
3. Click "Add New Reason" to add each reason's icon, title, and description.
4. Save.

The block appears on the tour details page in every template style and follows the active design automatically. When the global switch is OFF, or when no reasons are saved, no block is displayed.

== Guest Information and Services ==

Collect the information required for registration through configurable guest forms. Add optional services, such as meals, merchandise, or other tour extras, with separate prices.

Global guest forms and additional services can be imported into a tour and customized for that tour.

== WooCommerce Integration ==

WooCommerce is required for ticket cart and checkout functionality. Customers can select a tour, ticket type, date, seats (when enabled), guest details, and optional services before completing payment through any payment gateway supported by WooCommerce.

WooCommerce manages the cart, checkout, payment, tax, coupon, customer account, and order workflow.

== Shortcodes ==

Add these shortcodes to any WordPress page:

`[abptt-booking]`

Displays the tour listing and booking interface, including search, filters, ticket selection, guest forms, and checkout flow.

`[abptt-post]`

Displays a tour listing without the booking wrapper.

`[abptt-gallery]`

Displays tour images in a gallery or slider.

Common attributes include:

* `post_id` - Display the booking form or detail page for one specific tour. All other listing attributes are ignored.
* `cat_id` - Filter by category ID.
* `loc_id` - Filter by location ID.
* `organizer_id` - Filter by operator / organizer ID.
* `guide_id` - Filter by tour guide ID.
* `top_pick` - Show only Top Pick tours (`on`, `yes`, `true` or `1`).
* `style` - Listing style: `grid`, `list`, `missionary`, or `minimal`. Default `grid`.
* `slider_style` - Gallery style: `gallery` or `slider`. Default `gallery`.
* `show` - Number of tours to display; `0` or empty shows all (capped at 100).
* `column` - Number of columns (1-10). Default `3`.
* `sort` - Tour date order: `ASC` or `DESC`. Default `ASC`.
* `pagination` - Enable or disable pagination: `yes` or `no`. Default `yes`.
* `pagination-style` - Pagination mode: `live` or `number`. Default `live`.
* `form` - Search form layout: `inline` or `column`. Default `inline`. (`[abptt-booking]` only.)
* `filter` - Which taxonomy dropdowns appear in the search form. `all` (default) shows every enabled taxonomy, `none` hides them, a comma list shows only those (`category,location`), and a `-` prefix excludes (`all,-guide`). Valid keys: `category`, `location`, `organizer`, `guide`. (`[abptt-booking]` only.)

Example:

`[abptt-booking style="grid" column="3" pagination="yes" filter="category,location"]`

Minimal tour list example:

`[abptt-post style="minimal" show="8" pagination-style="number"]`

Featured tours example:

`[abptt-post top_pick="on" show="3" style="grid"]`

== My Account Bookings ==

When used with WooCommerce, a "Bookings" tab is added automatically to the WooCommerce My Account area. Logged-in customers can view their tour bookings, tour dates, ticket details, additional services, guest information, and order totals in one place, with pagination for large histories.

== External services ==

This plugin can connect to third-party services to display maps, route maps, and a searchable location picker. A service is only contacted when the related feature is used, and never while the related options are disabled. The plugin itself does not collect, store, or forward this data to the plugin author.

= Google Maps Platform (JavaScript API and Places library) =
Used for the interactive location map, the frontend route map, and the searchable location picker. When the Google Location Map option is enabled and a Google Maps API key has been saved, the site loads the Google Maps JavaScript API and the Places library. The visitor's browser sends map requests, the coordinates of the locations being displayed, and any address or place text typed into the location search directly to Google. This service is provided by Google LLC. Terms of Service: https://cloud.google.com/maps-platform/terms. Privacy Policy: https://policies.google.com/privacy.

= Google Maps embed =
Used to display a key-free map for a tour location when the Google Location Map option is enabled but no Google Maps API key has been saved. The visitor's browser loads a map iframe from maps.google.com and sends the saved coordinates of the tour location to Google. This service is provided by Google LLC. Terms of Service: https://cloud.google.com/maps-platform/terms. Privacy Policy: https://policies.google.com/privacy.

= OpenStreetMap tiles =
Used as the map background for the frontend route map. The visitor's browser requests map tile images for the visible map area from the OpenStreetMap tile servers and sends the requested tile coordinates and the visitor's IP address. This service is provided by the OpenStreetMap Foundation. Tile usage policy: https://operations.osmfoundation.org/policies/tiles/. Privacy Policy: https://wiki.osmfoundation.org/wiki/Privacy_Policy.

= OSRM demo server =
Used to calculate the driving route drawn between the tour stops on the frontend route map. The visitor's browser sends the latitude and longitude of the stops to the OSRM demo server at router.project-osrm.org. No account information or personal details are sent. This service is provided by the OSRM project. Usage policy: https://github.com/Project-OSRM/osrm-backend/wiki/Demo-server. Project website: https://project-osrm.org/.

== Requirements ==

* WordPress 6.2 or later
* PHP 7.4 or later
* MySQL 5.7 or later
* WooCommerce 8.0 or later

== Gutenberg and Multilingual Support ==

The tour post type and all tour taxonomies are registered with REST API support, so tours and taxonomies can be edited with the Gutenberg block editor. The plugin's tour listing and booking shortcodes can also be inserted into Gutenberg Shortcode blocks.

The plugin is translation-ready and supports Polylang's frontend language filtering for tour listings. Create a translated tour and translated taxonomy terms in Polylang for each language you publish. WooCommerce handles translated checkout and customer account pages according to the multilingual plugin configuration.

Plugin-specific global configuration labels, ticket names, and option values are stored as reusable settings and are not automatically duplicated or translated by Polylang. Translate those values through your multilingual workflow or use tour-specific values when each language needs different content.

== Installation ==

= Automatic Installation =

1. Install and activate WooCommerce.
2. In WordPress, go to Plugins > Add New.
3. Search for "ABP Tour and Travel".
4. Install and activate the plugin.
5. Open Tour Ticket from the WordPress admin menu.
6. Configure the global settings.
7. Create ticket types, seat plans, dates, locations, and other reusable data.
8. Create a tour and configure its tickets, prices, schedule, and availability.
9. Add one of the ABP Tour and Travel shortcodes to a page.

= Manual Installation =

1. Download the plugin ZIP file.
2. Go to Plugins > Add New > Upload Plugin.
3. Upload and install the ZIP file.
4. Activate ABP Tour and Travel.
5. Install and activate WooCommerce if it is not already installed.
6. Configure the plugin and create your tours.

== Frequently Asked Questions ==

= Is WooCommerce required? =

Yes. WooCommerce is required for the cart, checkout, payment, and order functionality.

= Can I sell both general admission and reserved seat tickets? =

Yes. Each tour can use general admission tickets or a configurable seat plan for reserved seating.

= Can I create reusable ticket types? =

Yes. Global ticket types can be reused across multiple tours.

= Can I reuse a seat plan? =

Yes. A global seat plan can be assigned to multiple tours and can be used multiple times within a tour.

= Can I create custom seat layouts? =

Yes. The seat plan designer supports drag-and-drop positioning, automatic numbering, custom labels, ticket-type assignment, custom cells, and layout controls.

= Can I collect guest information? =

Yes. Create global guest forms or configure tour-specific guest fields.

= Can I add optional services to a ticket booking? =

Yes. Additional services can be created globally or for a specific tour and can include their own prices.

= Can I configure recurring or specific tour dates? =

Yes. Tours support both specific dates and periodic dates, with day-wise and date-wise time configuration.

= Can I customize the tour listing and gallery? =

Yes. Use the listing and gallery shortcodes with supported attributes to control the displayed tour content and layout.

= Can I show a Google Map for tour locations? =

Yes. Enable Google Location Map in Global Configuration → Feature ON/OFF, add a Google Maps API key, and save map coordinates for each location. The map is displayed on the tour details page when the tour location has saved map data. The map follows the tour's detail template style (default, light, or modern).

= Can I display a tour timeline? =

Yes. Enable Tour Timeline in Global Configuration → Feature ON/OFF, then open a tour, switch Active Timeline ON in the Timeline tab, and add timeline entries with a time, title, and description. The timeline follows the tour's detail template style (default, light, or modern).

= Can I add guides to tours? =

Yes. Enable Tour Guide in Global Configuration → Feature ON/OFF, then create guide profiles in Global Configuration → Guide. Assign one or more guides to a tour from the tour edit screen. A guide can be reused across any number of tours.

Assigned guides appear automatically on the tour details page below the tour timeline, and each guide gets a public profile page at `/guide/{guide-slug}/`.

= Can I add a "Why Book With Us" block? =

Yes. Enable Why Book With Us in Global Configuration → Feature ON/OFF, then edit the reasons in Global Configuration → Why Book With Us ?. Each reason has its own icon, title, and description, and the block is shown on every tour page.

= Is the plugin translation-ready? =

Yes. The plugin uses the WordPress localization system and is translation-ready.

== Screenshots ==

1. Admin Dashboard - overview of tours, today's sessions, recent orders and revenue
2. Tours - admin tour list with create, edit, clone, trash and restore actions
3. Tour Orders - booking and order management with payment status, tickets and traveler details
4. Ticket Types and Seat Plans - reusable ticket types, seat plans and decor items
5. Global Configuration - booked status, menu label, slug and global tour settings
6. Feature ON/OFF - enable or disable SKU, tour icon, date list, location, maps, features and more
7. Global Dates - reusable date configurations and time slots for tours
8. Global Additional Services - reusable add-ons with price, stock and maximum quantity
9. Global Traveler Forms - build one form and import it into any tour
10. Global Locations - reusable locations with address and map details
11. Global Categories - reusable categories for organizing tours
12. Global Operators - reusable tour operators and companies
13. Global Features - reusable tour features and amenities with icons
14. Global Guides - reusable guide profiles with photo and bio
15. Global FAQs - reusable questions and answers for tours
16. Why Book With Us - reasons with icon, title and description shown on tour pages
17. Terms and Conditions - global terms text reused across tours
18. Slider - global slider theme and image settings
19. Contact Information - company name, address, phone and email used in bookings
20. CSS Properties - base color and custom CSS variables for the plugin
21. Tour Editor General - template, sale status, tour icon, SKU and general settings
22. Tour Editor Ticket and Price - ticket type, minimum and maximum quantity, multiple ticket types
23. Tour Editor Seat Plan - assign a seat plan and configure ticket types for the tour
24. Tour Editor Date and Times - specific or recurring dates with multiple time slots
25. Tour Editor Additional Services - enable optional add-ons for this tour
26. Tour Editor Traveler Forms - enable a custom form or import the global form
27. Tour Editor Features - select the included features shown on the tour page
28. Tour Editor Related Tours - choose related tours to display
29. Tour Editor Guides - assign one or more guides to the tour
30. Tour Editor Gallery - upload a gallery for the tour
31. Tour Editor Places You'll See - add locations with descriptions and drag to reorder
32. Tour Editor Timeline - add timeline steps with time, title and description
33. Tour Editor FAQs - add tour-specific questions and answers
34. Tour Editor Terms and Conditions - add tour-specific terms or import global terms
35. Frontend Tour Listing - grid layout with search and category, location, operator and guide filters
36. Frontend Booking List - list layout with tour date, availability and search filters
37. Frontend Booking Grid - grid layout with tour date, availability and search filters
38. Frontend Booking Minimal - minimal layout with tour date, availability and search filters
39. Frontend Tour Detail Default - gallery, timeline, map, seat plan, guides and booking form
40. Frontend Tour Detail Light - highlights, available schedule and booking form
41. Frontend Tour Detail Modern - ticket types, additional services and price totals
42. My Tours Bookings - customers view their bookings, tickets, traveler details and totals



== Need help or have suggestions? ==
If you need any further assistance or support, please contact us through the [🎫 support form](https://abp-team.com/support-desk/). We welcome your suggestions, so feel free to tell us anything we can improve in the plugin.

🌐 [Live Demo](https://demo-tt.abp-team.com/)
📖 [Documentation](https://demo-tt.abp-team.com/documentation/)
💬 [Support Forum](https://wordpress.org/support/plugin/abp-tour-travel/)
🐛 [Bug Reports](https://github.com/abpteam24/abp-tour-travel/issues)
📧 Email: support@abp-team.com


== Changelog ==

= 1.0.1 =

* Fixed duplicate booking rows when order processing runs more than once for the same order.
* Fixed a fatal error on tour lists when a tour has no ticket price configured.
* Fixed multiple particular session times on the same date being ignored.
* Fixed organizer names appearing blank in the admin organizer list.
* Fixed guide bios, timeline notes, FAQ answers and terms text picking up page-level theme output injected by `the_content`.
* Added generic taxonomy filters for category, location, organizer and guide.
* Improved accessibility of the list/grid view toggle and search form headings.

Released: November 5, 2026

= 1.0.0 =

* Initial release.

== Upgrade Notice ==

= 1.0.1 =

Released: November 5, 2026

= 1.0.0 =

Initial release.
