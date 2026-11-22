# MoveIt internal UI redesign

The redesign runs inside the existing Laravel application. Blade still renders each role's screens and the existing controllers still own authentication, validation, prices, payments and assignment. The visual system uses the MoveIt navy and red, a neutral surface palette, and the vehicle and room visuals from the project's own asset library.

## Page inventory and completion checklist

Severity describes the issue identified before the redesign. High indicates a problem that could interrupt a task on a supported screen size or with keyboard input. Medium indicates unclear hierarchy or inconsistent interaction. Low indicates presentation or copy cleanup. The checks refer to the browser and route coverage listed below.

### Public interior and account

- [x] Sign-in. Guests enter credentials, remember the session or recover access. Shared account layout with inline errors linked to fields. High.
- [x] Customer registration and driver registration. Guests choose a role and enter the relevant details. Active role selection controls license fields. Inline validation, submitted values and the pending approval flow remain visible. High.
- [x] Driver application pending. Applicants can check the review status and return to sign-in. Retains a single status, explanation and next action. Medium.
- [x] Email verification. Signed-in users can resend the verification email or sign out. Receives the account layout, status and form styling. Medium.
- [x] Password recovery, reset and confirmation. Users request a link, replace their password or confirm their identity. Each screen uses the same focused form layout and linked validation. Medium.
- [x] How MoveIt works. Visitors learn how to plan, pay and confirm a move. The sequence ends with booking and vehicle links. Medium.
- [x] FAQ. Visitors find answers about planning, payment and assignment. Grouped keyboard-operable disclosures and a path to contact support. Medium.
- [x] Contact. Visitors send a message and see support information. Form labels, linked field errors, saved values, success notice and compact support column. High.
- [x] Policies. Visitors read the terms and jump to the topic they need. Jump links and a visible draft-terms notice. Medium.
- [x] Public services catalog and service detail. Visitors compare transport and add-ons, view the service details where a detail link is available, and find support when the catalog is empty. Data-backed prices and pagination. Medium.
- [x] Public vehicle type catalog and type detail. Visitors can browse vehicle categories and view the existing type information where a detail link is available. Vehicle media keeps its aspect ratio and load-safe sizing. Medium.
- [x] Recovery pages. 403, 404, 419, 429 and the server-error fallback provide readable recovery steps and links. HTTP status handling stays in Laravel. High.
- [x] MoveIt assistant. The interior widget inherits the updated focus, surface and button styles. The landing widget keeps its existing assets. Medium.

### Customer

- [x] Dashboard and generic signed-in dashboard. The customer dashboard prioritizes a deposit or saved draft, then recent bookings and notifications. The generic dashboard links to the role workspace. Medium.
- [x] Bookings list. Customers can filter by status, search by booking ID, resume a draft or open a move. The list shows result count, filter reset and a first-booking action. Medium.
- [x] Booking entry. The three current paths remain available. Each card explains when to size a vehicle, build an inventory or scan with a phone. Medium.
- [x] Booking detail. Move details, assignment, confirmed inventory and add-ons stay visible where applicable. Pricing has a distinct review section and the summary precedes in-page links. Existing cancellation and review actions remain in context. High.
- [x] Manual inventory and vehicle size. Inventory controls keep their input types and validation. The vehicle options retain estimates and recommendations with visible keyboard focus. High.
- [x] Locations, date and appointment time. Existing maps, inputs and back links remain in the workflow. The step indicator and form sections clarify position and progress. High.
- [x] Scanner workspace and standalone phone scanner. The workspace prioritizes detected items and secure pairing. The phone view emphasizes camera preview, start and stop controls, and connection feedback. High.
- [x] Deposit checkout. The balance, beneficiary, alias and Amman deadline are grouped above receipt upload. File format, maximum size, validation and prior proof stay visible. High.
- [x] Payment history. Outstanding balances and receipt history have separate headings. Each pending balance links back to its booking. Medium.
- [x] Booking-service list, create, detail and edit. Existing direct routes and role checks stay in place. Browse, review or edit associated service data through consistent listing and editor layouts. Medium.
- [x] Notifications and account settings. Clear unread states and actions. Account removal has its own marked section. Medium.

### Driver

- [x] Dashboard. The current or next move and request queue are the largest actions. Booking indicators and upcoming schedule use real records. Medium.
- [x] Bookings and new requests. Drivers can switch between views and open a booking from the list. New requests has a specific empty state. Medium.
- [x] Schedule. An appointment list shows time in Amman, booking status, detail link and an empty state that leads to requests. High.
- [x] Assigned booking detail. Drivers can find the existing booking actions, trip facts and inventory in the common detail layout. High.
- [x] My vehicles, new vehicle, edit and details. Fleet filters, capacity, status, pricing, pictures and editing stay usable down to mobile. Table actions remain available in the scroll area. High.
- [x] Driver profile and license update. The profile separates identity, contact, license and account access. The editor groups contact, license and password fields. High.
- [x] Services, notification and general account views. Drivers keep access to the routes already available to them and use the updated shared layout. Medium.

### Admin

- [x] Dashboard. Existing metrics, real booking activity, urgent reviews and latest bookings share one operations shell. The chart includes values and an empty state. Medium.
- [x] User list and customer and driver filters. Review account information and use existing account and driver actions. Consolidated title and filter treatment. Medium.
- [x] Driver approvals. Existing status filters and approval actions remain. The destructive-action dialog focuses the reason and closes without recording a decision when cancelled. High.
- [x] Booking list and detail. Filter by ID, status and driver, reset filters and open individual bookings. Price and status remain separate. Medium.
- [x] CliQ queue and proof detail. Search filters and amounts remain visible. Tables scroll inside their region on mobile. Decision fields connect errors with the relevant input. High.
- [x] Services list, create, detail and edit. Manage catalog items in the common shell with one form treatment for descriptions, rates and availability. Medium.
- [x] Vehicle types list, create, detail and edit. Consistent media sizing, status and catalog actions. Medium.
- [x] Fleet list, create, detail and edit. Display driver names and readable capacity. Editors group vehicle information, capacity and price. High.
- [x] Time rules. Managers can open the existing add or edit form from its associated control. Existing time, overlap and multiplier rules still apply. Medium.
- [x] Reviews and per-driver feedback. Managers can start with recent reviews or open a driver rating summary. Consistent page titles and back navigation. Medium.
- [x] Commission report. Managers can review actual totals and records, filter by booking and export the existing CSV. Updated table and filter treatment. Medium.
- [x] Settings and commission. Deposit and commission settings and beneficiary information stay grouped with required-field guidance. High.
- [x] Contact inbox. Sender details and message content are separated, and long messages wrap in their reading area. Medium.
- [x] Notifications and account settings. Reuses role navigation, unread states and account controls. Medium.

## Shared design system

- **Color.** Navy text and navigation at #122f3d, red action at #ac2431, canvas at #f4f6f6, white surfaces and visible control borders. Success, warning, error and informational treatments use readable labels alongside their colors.
- **Typography and space.** Inter with a system fallback. Separate scales for titles, section headings, body, labels and table metadata. Layout spacing uses a four-pixel rhythm with responsive page gutters.
- **Components.** Role-specific workspace shells, common page header and actions, account layout, buttons, field and validation states, data tables, filter rows, status indicators, pagination, disclosures, dialogs, task prompts and error recovery.
- **Interaction.** Visible focus, responsive primary actions, keyboard-accessible overflow regions, Escape in the admin and public navigation, focus returned after closing the admin drawer, reduced-motion support and duplicate-submit feedback.
- **Scope.** The design stylesheet and helper script load only through interior layouts. The standalone scanner explicitly opts into the stylesheet.

## Protected screens and contracts

The marketing landing page at / retains its Blade view, layout, images, video, fonts, navigation script, animation styles, shared wordmark and assistant assets. Before and after browser checks at 390px and 1440px produced the same heading, asset list and measured hero geometry. The source diff also confirms that those files were untouched.

Controller-only responses, API and file endpoints, and Blade placeholders with no rendered route retain their existing contracts. Screens were checked with a separate local SQLite database and temporary test accounts. The application's records and configuration were not seeded for the redesign.

## Verification

- 79 combinations of role, page and filter returned the intended HTML view over local HTTP. The check covered guests, customers, drivers and admins. An additional request confirmed the recovery view returned HTTP 404.
- Browser checks used 320, 375, 390, 768, 1024, 1440 and 1920 pixels on representative public and role screens. The admin table and tablet driver header overflow defects were corrected. Follow-up checks showed no page-level horizontal overflow on the measured screens. Wide tables can scroll inside their regions.
- Browser interactions covered customer sign-in and booking entry, vehicle-size progression, mobile customer navigation, driver workspace actions, mobile admin navigation and focus return, the driver decision dialog and cancellation, opening the time-rule editor and registration role changes.
- 74 Laravel tests passed with 1,098 assertions. All 121 Blade templates compiled and parsed. Changed JavaScript passed syntax checks. Vite built successfully.
- Visual and keyboard checks targeted WCAG 2.2 AA criteria for labels and validation, status text, focus, heading levels, reflow, and navigation. This was a targeted review rather than a full conformance audit.

## Remaining verification

Real CliQ transfers, real camera permissions and sending email through a production provider remain outside the isolated UI run. The Vite build reports existing Sass deprecation warnings from the Bootstrap import path.
