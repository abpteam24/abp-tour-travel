<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Documentation' ) ) {
		class ABPTT_Documentation {
			public function __construct() {
				add_action( 'abptt_load_documentation', array( $this, 'load_documentation' ) );
			}
			public function load_documentation(): void {
				$label = ABPTT_Function::label();
				?>
                <div class="abp_panel_max_1200_mar_auto abp_documentation">
                    <div class="_panel_head">
                        <h3 class="abp_gap_xs"><span>📘</span> <?php esc_html_e( 'Documentation — How to Use ABP Tour and Travel', 'abp-tour-travel' ); ?></h3>
                    </div>
                    <div class="_panel_body_fd_column_gap">
                        <div class="dash_card">
                            <div class="dash_card_body">
                                <p class="dash_body_lead"><?php esc_html_e( 'Set up and start selling tickets in about 15 minutes.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'This guide explains the plugin step by step: what every menu does, the order to set things up in, and how ticket types and seat plans work. Full, searchable documentation is also available online.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">
                                    <a class="_btn_light_active_xs" href="https://demo-tt.abp-team.com/documentation/" target="_blank" rel="noopener"><?php esc_html_e( 'Open full documentation', 'abp-tour-travel' ); ?></a>
                                    <a class="_btn_light_active_xs" href="https://demo-tt.abp-team.com" target="_blank" rel="noopener"><?php esc_html_e( 'Open live demo', 'abp-tour-travel' ); ?></a>
                                </p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🚀</span> <?php esc_html_e( 'Quick Start — Follow these steps in order', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <div class="_grid_400">
<?php
					$this->step_card(
											'1',
											__( 'Check Status', 'abp-tour-travel' ),
											__( 'Open the Dashboard and verify everything is ready.', 'abp-tour-travel' ),
											__( 'The System Status card shows your plugin, WordPress, PHP and WooCommerce versions. If WooCommerce is missing, install and activate it first — the plugin uses WooCommerce for checkout and payments.', 'abp-tour-travel' ),
											'dashboard',
											__( 'Open Dashboard', 'abp-tour-travel' )
										);
										$this->step_card(
											'2',
											__( 'Global Configuration', 'abp-tour-travel' ),
											__( 'Set the plugin-wide options.', 'abp-tour-travel' ),
											/* translators: %s: tour label (for example "Tour"). */
											sprintf( __( 'Open Global Configuration → %1$s and set the tour label, slug, icons, taxonomy labels and the Booked Status that reserves inventory. Then open Feature ON/OFF and switch off any feature areas you do not need.', 'abp-tour-travel' ), $label ),
											'configuration',
											__( 'Open Configuration', 'abp-tour-travel' )
										);
										$this->step_card(
											'3',
											__( 'Create Global Lists', 'abp-tour-travel' ),
											__( 'Build the reusable lists every tour shares.', 'abp-tour-travel' ),
											__( 'Global Configuration also holds the reusable data: Locations, Categories, Organizers, Features, Additional services, Traveler Forms, Guides, FAQ, Why Book With Us ? and Term & Conditions. Create them once — they appear as selectable items when you build a tour.', 'abp-tour-travel' ),
											'configuration',
											__( 'Open Global Configuration', 'abp-tour-travel' )
										);
										$this->step_card(
											'4',
											__( 'Ticket Types & Seat Plans', 'abp-tour-travel' ),
											__( 'Create the ticket categories and seat layouts.', 'abp-tour-travel' ),
											__( 'Ticket Types are the reusable categories you sell (Adult, Child, VIP…). Seat Plans are visual layouts for assigned seating (theatre, bus, hall). Both live under Ticket/Seat Plan. Ticket Type vs Seat Plan is explained below.', 'abp-tour-travel' ),
											'sp',
											__( 'Open Ticket/Seat Plan', 'abp-tour-travel' )
										);
										$this->step_card(
											'5',
											__( 'Create a Tour', 'abp-tour-travel' ),
											__( 'Add your first tour and set its tickets or seats.', 'abp-tour-travel' ),
											__( 'Press the + button, add a title, image and description, then choose a detail template and fill in the date/time schedule, location/category, and ticket or seat pricing with quantities. The tour gets its own page automatically.', 'abp-tour-travel' ),
											'posts',
											__( 'Open Tour List', 'abp-tour-travel' )
										);
										$this->step_card(
											'6',
											__( 'Add Shortcodes & Pages', 'abp-tour-travel' ),
											__( 'Publish the booking and tour-list pages.', 'abp-tour-travel' ),
											__( 'The Dashboard\'s System Status card can create a Booking page ([abptt-booking]), a Tour List page ([abptt-post]) and a Gallery page ([abptt-gallery]) for you. Add the list page to your menu so customers can browse every tour, or place the shortcodes into any page yourself.', 'abp-tour-travel' ),
											'dashboard',
											__( 'Open Dashboard', 'abp-tour-travel' )
										);
										$this->step_card(
											'7',
											__( 'Test & Go Live', 'abp-tour-travel' ),
											__( 'Try it with demo data, then watch the orders.', 'abp-tour-travel' ),
											__( 'New to the plugin? Use Dummy Import in the Dashboard\'s System Status card to import sample tours, then remove it again when you are done. When live, every purchase appears under Orders and orders in the statuses you selected as Booked Status reduce availability.', 'abp-tour-travel' ),
											'dashboard',
											__( 'Open Dashboard', 'abp-tour-travel' )
										);
									?>
                                </div>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🗂️</span> <?php esc_html_e( 'Every menu explained', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <b>📊 <?php esc_html_e( 'Dashboard', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'business overview: KPI cards, today\'s sessions, recent orders, quick actions, system status and content breakdown. Demo import and page creation live in System Status.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php echo esc_html( $label ); ?> <?php esc_html_e( 'Lists', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'all your tours: create, edit, clone, restore, trash and permanently delete tours. Each row also has a shortcode helper for that single tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b>🧾 <?php esc_html_e( 'Orders', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'every customer booking with status tabs, filters, booking/seat/traveler details and totals.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b>💺 <?php esc_html_e( 'Ticket/Seat Plan', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'create reusable ticket types, decor (non-seat) items and seat plans for assigned seating.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b>⚙️ <?php esc_html_e( 'Global Configuration', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'plugin-wide settings plus every reusable data list. Full sub-tab list below.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b>📘 <?php esc_html_e( 'Documentation', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'this guide, plus links to the online documentation and live demo.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'The tab bar also has a + button for a new tour, plus quick popup buttons for adding a location and (when enabled) a category without leaving the screen.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🌍</span> <?php esc_html_e( 'Inside Global Configuration', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <b><?php echo esc_html( $label ); ?></b> — <?php esc_html_e( 'main settings: Booked Status, label, slug, icons, taxonomy labels and slugs, Google Maps API key.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Feature ON/OFF', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'master switches for every feature area. Tabs marked (switch) below appear only when their feature is ON.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Dates', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'global date format, time format, sale-close buffer and advance-booking window.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Additional services', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'reusable paid add-ons with their own prices.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Traveler Forms', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'reusable guest fields collected at checkout (name, email, phone…).', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php echo esc_html( ABPTT_Function::location_label() ); ?></b> — <?php esc_html_e( 'reusable meeting points with address and map position.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php echo esc_html( ABPTT_Function::category_label() ); ?></b> (switch) — <?php esc_html_e( 'tour categories.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php echo esc_html( ABPTT_Function::organizer_label() ); ?></b> (switch) — <?php esc_html_e( 'organizers / operators.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php echo esc_html( ABPTT_Function::feature_label() ); ?></b> (switch) — <?php esc_html_e( 'included / excluded feature badges shown on the tour page.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php echo esc_html( ABPTT_Function::guide_label() ); ?></b> (switch) — <?php esc_html_e( 'guide profiles (photo, designation, company, bio, website and social links). Each guide also gets a public profile page.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'FAQ', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'reusable questions and answers.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Why Book With Us ?', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'reusable trust reasons (icon, title, description) shown on tour pages.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Term & Conditions', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'reusable booking policy text.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Slider', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'gallery / carousel presentation, indicators and popup.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Contact Information', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'company name, address, contact number and e-mail.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'CSS Property', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'brand colours, border radius and font sizes.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">📝</span> <?php esc_html_e( 'Tour editor tabs', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <b><?php esc_html_e( 'General', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'title, short description, detail template, SKU, icon, taxonomies, Top Pick and the other basics.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Ticket & Price', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'ticket-only or seat-plan selling, ticket types, prices, quantity, reserved quantity and min/max per order.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Date and Times', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'Particular or Repeated dates, special on/off dates, and General / Day Wise / Date Wise time slots.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Additional services', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'import services from the global library and adjust price or quantity for this tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Traveler Forms', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'use the global form, import it, or build tour-specific fields.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Feature', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'choose which included / excluded features apply to this tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Related Tour', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'pick tours to cross-sell on this tour page.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Guide', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'assign one or more guides to this tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Gallery', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'drag-and-drop gallery images for this tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Places You\'ll See', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'stops or landmarks with image and description.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Tax', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'WooCommerce tax status and tax class for this tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Timeline', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'time-by-time itinerary entries (time, title, description).', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'FAQs', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'use the global FAQ, import it, or write tour-only questions.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Term & Conditions', 'abp-tour-travel' ); ?></b> (switch) — <?php esc_html_e( 'use the global terms, import them, or write tour-specific terms.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🎟️</span> <?php esc_html_e( 'Ticket Type vs Seat Plan — made clear', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text"><?php esc_html_e( 'Every tour sells in one of two ways. You choose this on the tour edit page in the Ticket & Price tab:', 'abp-tour-travel' ); ?></p>
                                <div class="_grid_400">
                                    <div class="dash_card">
                                        <div class="dash_card_body">
                                            <p class="dash_body_lead">🎫 <?php esc_html_e( 'Ticket Type (general admission)', 'abp-tour-travel' ); ?></p>
                                            <p class="dash_body_text"><?php esc_html_e( 'Customers buy a quantity of a ticket type (Adult, Child, VIP) without a fixed seat. Best for festivals, conferences, workshops and guided tours.', 'abp-tour-travel' ); ?></p>
                                        </div>
                                    </div>
                                    <div class="dash_card">
                                        <div class="dash_card_body">
                                            <p class="dash_body_lead">🪑 <?php esc_html_e( 'Seat Plan (assigned seating)', 'abp-tour-travel' ); ?></p>
                                            <p class="dash_body_text"><?php esc_html_e( 'Customers buy a specific seat. You build a visual layout (theatre, bus, hall) once, assign a ticket type to each seat area, and the customer picks their seat on the tour page.', 'abp-tour-travel' ); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <p class="dash_body_lead">💡 <?php esc_html_e( 'What is a "Ticket Type"?', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'A reusable category with a label, a colour, an icon/image and a seat prefix used for automatic numbering (e.g. VIP-01, B-01). Create them in Ticket/Seat Plan → Ticket Type List. Reuse them in many tours and change the price per tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_lead">🛠️ <?php esc_html_e( 'How to create a Seat Plan:', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'Open Ticket/Seat Plan → press Add New Seat Plan. Set the grid size, then click cells to place seats (with a ticket type) or decor items (entrance, aisle, window, stairs). Give it a name and save. The same plan can be reused on any number of tours.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_lead">💰 <?php esc_html_e( 'Where do prices go?', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'Prices, quantities, reserved quantity and min/max per order are set per tour (Tour edit → Ticket & Price). The global Ticket Type only defines the label and style; the price is decided on each tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_lead">⚙️ <?php esc_html_e( 'One or many ticket types?', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'On the tour edit page you can enable multiple ticket types to sell several categories (Adult + Child + VIP) at once — or keep it off to sell a single ticket type per tour.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">📦</span> <?php esc_html_e( 'Booked Status and stock', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text"><?php esc_html_e( 'The plugin does not keep a separate stock counter. Availability is calculated live from the stored orders, so the only thing you configure is which order statuses count as booked.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php
                                    /* translators: %s: tour label (for example "Tour"). */
                                    echo esc_html( sprintf( __( 'Open Global Configuration → %1$s → Booked Status and tick the WooCommerce statuses that should reserve inventory (for example Processing and Completed).', 'abp-tour-travel' ), $label ) );
                                ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'An order that moves into one of those statuses reduces the available quantity or seats; moving it back out releases them again.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'This one setting powers the tour page availability, the seat map colours, the Dashboard session counts and the admin order list.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🧭</span> <?php esc_html_e( 'Optional tour page sections', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Tour Timeline', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'switch Tour Timeline ON in Global Configuration → Feature ON/OFF and Active Timeline ? ON in the tour\'s Timeline tab, then add entries. The design follows the tour\'s detail template (Default, Light or Modern).', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Tour Guide', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'create guide profiles in Global Configuration → Guide, then assign them on each tour. Every guide also gets a public profile page.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Google Location Map', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'shows a map for the tour location. Add a Google Maps API key in Global Configuration if you have one; without a key a key-free embedded map is used instead.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Why Book With Us', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'a reasons block shown on tour pages when the switch is ON. Edit the reasons in Global Configuration → Why Book With Us ?.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'FAQs & Terms', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'each tour has its own FAQs and Term & Conditions tab, which can inherit the global content or use tour-specific content.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🔑</span> <?php esc_html_e( 'Shortcodes you can place anywhere', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <code>[abptt-booking]</code> — <?php esc_html_e( 'search form + tour listing + booking flow. Ideal for your Booking page. Supports style, cat_id, loc_id, organizer_id, guide_id, top_pick, show, column, sort, pagination, pagination-style, form and filter.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <code>[abptt-post]</code> — <?php esc_html_e( 'tour listing without the search form. Ideal for a dedicated Tour List page. Accepts the same listing parameters.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <code>[abptt-gallery]</code> — <?php esc_html_e( 'displays tour gallery images as a gallery grid or a carousel slider (slider_style="gallery" or "slider").', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'Use post_id="…" on any of them to show one specific tour.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">✅</span> <?php esc_html_e( 'Good to know', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <?php esc_html_e( 'Customers pick a date and time from the schedule shown on the tour page before choosing tickets or seats.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'You can configure a global traveler form and additional services once, then import them into any tour and fine-tune them there.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'The tour post type supports Gutenberg and Polylang, so tours can be edited in the block editor and translated for multilingual sites.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'When you uninstall the plugin, your data is preserved by default so you can reinstall later without losing anything. Turn ON "Remove All Data on Uninstall" in Feature ON/OFF only if you really want to delete everything.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'For detailed screenshots, shortcode tables and template-override instructions, use the full online documentation linked at the top of this page.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			private function step_card( string $step, string $title, string $lead, string $text, string $tab = '', string $btn_label = '' ): void {
				$url = $tab ? ABPTT_Function::build_url( $tab ) : '';
				?>
                <div class="dash_card">
                    <div class="dash_card_head">
                        <h4><span class="dash_step"><?php echo esc_html( $step ); ?></span><?php echo esc_html( $title ); ?></h4>
						<?php if ( $url && $btn_label ) { ?>
                            <a class="_btn_light_active_xs" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $btn_label ); ?></a>
						<?php } ?>
                    </div>
                    <div class="dash_card_body">
                        <p class="dash_body_lead"><?php echo esc_html( $lead ); ?></p>
                        <p class="dash_body_text"><?php echo esc_html( $text ); ?></p>
                    </div>
                </div>
				<?php
			}
		}
		new ABPTT_Documentation();
	}
