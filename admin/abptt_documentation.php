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
				?>
                <div class="abp_panel_max_1200_mar_auto abp_documentation">
                    <div class="_panel_head">
                        <h3 class="abp_gap_xs"><span>📘</span> <?php esc_html_e( 'Documentation — How to Use ABP Tour and Travel', 'abp-tour-travel' ); ?></h3>
                    </div>
                    <div class="_panel_body_fd_column_gap">
                        <div class="dash_card">
                            <div class="dash_card_body">
                                <p class="dash_body_lead"><?php esc_html_e( 'Set up and start selling tickets in ~15 minutes.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'This guide explains the plugin step by step: what every menu does, in which order to set things up, and how tickets and seat plans work.', 'abp-tour-travel' ); ?></p>
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
											__( 'The Dashboard shows your WordPress, PHP, and WooCommerce versions. Everything green means you can continue. If WooCommerce is missing, install it from the System Status section — it is required for ticket payments.', 'abp-tour-travel' ),
											'dashboard',
											__( 'Open Dashboard', 'abp-tour-travel' )
										);
										$this->step_card(
											'2',
											__( 'Configuration ON/OFF', 'abp-tour-travel' ),
											__( 'Decide which features you need.', 'abp-tour-travel' ),
											__( 'Configuration → ON/OFF turns features on/off for the whole site (Category, Operator, Seat Plan, Additional Services, FAQ, Terms…). The defaults work out of the box, so you can skip this step.', 'abp-tour-travel' ),
											'configuration',
											__( 'Open Configuration', 'abp-tour-travel' )
										);
										$this->step_card(
											'3',
											__( 'Create Global Lists', 'abp-tour-travel' ),
											__( 'Build the reusable lists every tour shares.', 'abp-tour-travel' ),
											__( 'Global Data holds Dates & Schedules, Locations, and (if enabled) Categories, Operators, Features, Additional Services, Traveler Forms, Guides, Why Book With Us reasons, and Resources. Create them once — they appear as dropdowns when creating a tour.', 'abp-tour-travel' ),
											'global',
											__( 'Open Global Data', 'abp-tour-travel' )
										);
										$this->step_card(
											'4',
											__( 'Ticket Types & Seat Plans', 'abp-tour-travel' ),
											__( 'Create the ticket categories and seat layouts.', 'abp-tour-travel' ),
											__( 'Ticket Types are the reusable categories you sell (Adult, Child, VIP…). Seat Plans are visual layouts for assigned seating (theater, bus, hall). A full explanation of Ticket vs Seat Plan is below.', 'abp-tour-travel' ),
											'sp',
											__( 'Open Ticket/Seat Plan', 'abp-tour-travel' )
										);
										$this->step_card(
											'5',
											__( 'Create a Tour', 'abp-tour-travel' ),
											__( 'Add your first tour and set its tickets or seats.', 'abp-tour-travel' ),
											__( 'Press the + button, then add a title, image, description, schedule (date & time), location/category, and configure tickets or seat plan with prices and quantities. The tour gets its own page automatically.', 'abp-tour-travel' ),
											'posts',
											__( 'Open Tour List', 'abp-tour-travel' )
										);
										$this->step_card(
											'6',
											__( 'Add Shortcodes & Pages', 'abp-tour-travel' ),
											__( 'Publish the booking and tour-list pages.', 'abp-tour-travel' ),
											__( 'The Dashboard creates a Booking page ([abptt-booking]) and a Tour List page ([abptt-post]) for you via the System Status section. Put the list page in your menu so customers can browse every tour; you can also insert the shortcodes into any page.', 'abp-tour-travel' ),
											'dashboard',
											__( 'Open Dashboard', 'abp-tour-travel' )
										);
										$this->step_card(
											'7',
											__( 'Test & Go Live', 'abp-tour-travel' ),
											__( 'Try it with demo data, then watch the orders.', 'abp-tour-travel' ),
											__( 'New to the plugin? Import the built-in demo data from the Dashboard’s System Status section to explore everything safely. When live, every purchase appears under Orders and paid orders reduce the available seats.', 'abp-tour-travel' ),
											'dashboard',
											__( 'Open Dashboard', 'abp-tour-travel' )
										);
									?>
                                </div>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🔛</span> <?php esc_html_e( 'The ON/OFF tab — what it is and why it matters', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text"><?php esc_html_e( 'Configuration → ON/OFF is the master control panel of the plugin. Every feature is listed there with a simple switch. Each switch controls a whole feature area of the site — not just one tour.', 'abp-tour-travel' ); ?></p>
                                <div class="_grid_400">
                                    <div class="dash_card">
                                        <div class="dash_card_body">
                                            <p class="dash_body_lead">🟢 <?php esc_html_e( 'Switch ON (default)', 'abp-tour-travel' ); ?></p>
                                            <p class="dash_body_text"><?php esc_html_e( 'The feature is active everywhere. You see its menus, its global lists, and its fields on the tour edit page.', 'abp-tour-travel' ); ?></p>
                                        </div>
                                    </div>
                                    <div class="dash_card">
                                        <div class="dash_card_body">
                                            <p class="dash_body_lead">⚪ <?php esc_html_e( 'Switch OFF', 'abp-tour-travel' ); ?></p>
                                            <p class="dash_body_text"><?php esc_html_e( 'The feature is completely hidden from the whole site — its menus, forms, and fields disappear. This keeps your admin screen clean and shows only what you use.', 'abp-tour-travel' ); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <p class="dash_body_lead"><?php esc_html_e( 'Popular switches you may want to change:', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Seat Plan', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'turn OFF if you only sell tickets by quantity and never need reserved seating.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Category / Operator', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'turn OFF the ones you do not use to hide their menus and tour fields.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Additional Services', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'turn OFF if you have no extras (meals, merchandise) to sell.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Guest Form (Custom Guest)', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'turn OFF if you do not need to collect guest names/details at checkout.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'FAQ / Terms & Conditions', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'turn OFF if you do not need these blocks on the tour page.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Tour Timeline / Tour Guide / Google Location Map / Why Book With Us', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'turn ON to show these extra sections on the tour page; each one is explained below.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><b><?php esc_html_e( 'Tip:', 'abp-tour-travel' ); ?></b> <?php esc_html_e( 'You can change the switches at any time. Turning a feature back ON restores its menus and tour fields instantly, so feel free to experiment.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🎟️</span> <?php esc_html_e( 'Ticket Type vs Seat Plan — made clear', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text"><?php esc_html_e( 'Every tour sells tickets in one of two ways. You choose this on the tour edit page in the Ticket Type settings:', 'abp-tour-travel' ); ?></p>
                                <div class="_grid_400">
                                    <div class="dash_card">
                                        <div class="dash_card_body">
                                            <p class="dash_body_lead">🪑 <?php esc_html_e( 'Seat Plan (assigned seating)', 'abp-tour-travel' ); ?></p>
                                            <p class="dash_body_text"><?php esc_html_e( 'Customers buy a specific seat. You build a visual layout (theater, bus, hall) once, assign a ticket type to each seat area, and the customer picks their seat on the tour page. Best for concerts, theaters, buses, and stadiums.', 'abp-tour-travel' ); ?></p>
                                        </div>
                                    </div>
                                    <div class="dash_card">
                                        <div class="dash_card_body">
                                            <p class="dash_body_lead">🎫 <?php esc_html_e( 'Ticket Type (general admission)', 'abp-tour-travel' ); ?></p>
                                            <p class="dash_body_text"><?php esc_html_e( 'Customers buy a quantity of a ticket type (Adult, Child, VIP) without a fixed seat. Best for festivals, conferences, workshops, and online tours.', 'abp-tour-travel' ); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <p class="dash_body_lead">💡 <?php esc_html_e( 'What is a "Ticket Type"?', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'A reusable category with a label, a color, an icon, and a seat prefix used for automatic numbering (e.g. VIP-01, B-01). Create them in Ticket/Seat Plan → Ticket Type List. Reuse them in many tours and change the price per tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_lead">🛠️ <?php esc_html_e( 'How to create a Seat Plan:', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'Open Ticket/Seat Plan → press Add New Seat Plan, then drag and drop seats and other cells (entrance, aisle, window, stairs) to build your layout. Give it a name, assign ticket types to seats, and save. Reusable on any number of tours.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_lead">💰 <?php esc_html_e( 'Where do prices go?', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'Prices, quantities, reserved stock, and min/max per order are set per tour (Tour edit → Ticket Configuration). The global Ticket Type only defines the label and style; the price is decided on each tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_lead">⚙️ <?php esc_html_e( 'One or many ticket types?', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text"><?php esc_html_e( 'On the tour edit page you can enable "Multiple Ticket Type" to sell several categories (Adult + Child + VIP) at once — or keep it off to sell a single ticket type per tour.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🗂️</span> <?php esc_html_e( 'Every menu explained', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <b><?php echo esc_html( ABPTT_Function::label() ); ?> List</b> — <?php esc_html_e( 'all your tours: create, edit, clone, trash, restore, and permanently delete tours.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Orders', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'every customer purchase with booking details, status, check-in, and totals.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Ticket/Seat Plan', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'create reusable ticket types, seat plans, and decor (non-seat) cells for assigned seating.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Global Data', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'shared reusable lists used by all tours (see the sub-tabs list below).', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Configuration', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'global settings: general (label, slug, currency…), the ON/OFF switches, contact information, colors, and CSS.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Dashboard', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'overview and charts, plus a System Status health check with quick tools: install WooCommerce, create the booking/list/gallery pages, and import or remove demo data.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Documentation', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'this guide.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🌍</span> <?php esc_html_e( 'Inside Global Data', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Dates', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'global date and time formatting, sale buffer, and advance booking horizon.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Location', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'reusable meeting point list used by every tour.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Category / Operator', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'optional classifications; they only appear here when switched ON in Configuration.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Features', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'highlight badges shown on the tour page (e.g. E-Ticket, VIP Access, Parking).', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Additional Services', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'optional add-ons with their own prices (meals, merchandise, transfers).', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Traveler Forms', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'guest fields collected at checkout (name, email, phone…).', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Resources', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'reusable FAQ and Terms & Conditions blocks.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Guides', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'reusable guide profiles (photo, bio, designation, company, website, and social links) that you link to tours. Shown when the Guide switch is ON.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Why Book With Us', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'a section title plus repeatable reasons (icon, title, description) shown on every tour page. Shown when the Why Book With Us switch is ON.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🧭</span> <?php esc_html_e( 'Optional tour page sections', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Tour Timeline', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'a time-by-time schedule on the tour page. Turn the switch ON in Configuration → ON/OFF, then add entries (time, title, description) on the tour edit → Timeline tab.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Tour Guide', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'create guide profiles in Global Data → Guide, then pick them on each tour. Guides appear below the timeline, and every guide also gets its own public profile page.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Google Location Map', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'shows a map for the tour location. Turn the switch ON in Configuration → ON/OFF and paste a Google Maps API key; without a key a key-free embedded map is used instead.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <b><?php esc_html_e( 'Why Book With Us', 'abp-tour-travel' ); ?></b> — <?php esc_html_e( 'a reasons block shown on every tour page. Turn the switch ON in Configuration → ON/OFF, then edit the title and reasons in Global Data → Why Book With Us.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">🔑</span> <?php esc_html_e( 'Shortcodes you can place anywhere', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <code>[abptt-booking]</code> — <?php esc_html_e( 'full tour listing with search, filters, ticket selection, and booking flow. Ideal for your Booking page.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <code>[abptt-post]</code> — <?php esc_html_e( 'tour listing without the booking wrapper. Ideal for your Tour List page.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <code>[abptt-gallery]</code> — <?php esc_html_e( 'displays tour images in a gallery or slider.', 'abp-tour-travel' ); ?></p>
                            </div>
                        </div>
                        <div class="dash_card">
                            <div class="dash_card_head">
                                <h4><span class="dash_step">✅</span> <?php esc_html_e( 'Good to know', 'abp-tour-travel' ); ?></h4>
                            </div>
                            <div class="dash_card_body">
                                <p class="dash_body_text">• <?php esc_html_e( 'The booked order status (Configuration → General → Booked Status) decides when seats are deducted from inventory. Set it to the statuses you consider "paid".', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'Customers pick a date and time from the schedule shown on the tour page before choosing tickets.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'You can configure a global guest form and additional services once, then import them into any tour and fine-tune them there.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'The tour post type and taxonomies support Gutenberg and Polylang, so tours can be edited in the block editor and translated for multilingual sites.', 'abp-tour-travel' ); ?></p>
                                <p class="dash_body_text">• <?php esc_html_e( 'When you uninstall the plugin, your data is preserved by default so you can reinstall later without losing anything. Turn ON "Remove All Data on Uninstall" in Configuration → ON/OFF only if you really want to delete everything.', 'abp-tour-travel' ); ?></p>
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