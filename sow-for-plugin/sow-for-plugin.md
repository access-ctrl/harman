# FWH Speaking Booking — Complete Build & Delivery Document
## 1. Project summary
Build plan for a WordPress developer to deliver the 1-to-1 speaking booking system for frenchwithharman.com today, with all code written through Claude Code.
All work happens on the staging copy at https://harman.lngc.ltd.
Go live today only if every test passes and Anirudh signs off; otherwise go live first thing tomorrow.
## 2. What we are building
- One package: a 20-minute 1-to-1 French speaking session.
- Price is set in admin.
- Multiple trainers are supported.
- Each trainer sets weekly hours (Monday to Saturday, Canada Eastern Time) and days off from their own login.
- Students never choose a trainer; a free trainer is assigned automatically.
- Students see live slots in their own time zone and pay through Stripe Checkout.
- A booking is confirmed only by a verified Stripe webhook or a server-side Stripe check.
- After payment: a Google Calendar event with a Meet link on the assigned trainer’s calendar.
- One email is sent to the student.
- One email is sent to the trainer with the coordinator in CC.
- Coordinator sees all bookings and can cancel.
- Trainers see only their own schedule and sessions, with join links.
- Out of scope: reminders, rescheduling, refunds, student self-service.
## 3. Architecture decisions (fixed)
| Area | Decision | Why |
|---|---|---|
| Plugin | Custom plugin: `fwh-speaking-booking` | No Composer, no external CDN, no third-party booking plugin |
| Data | Custom tables created with `dbDelta` and a schema version option; data is never dropped on deactivate | Clean queries and safe upgrades |
| Time | Store UTC; convert with explicit `America/Toronto`; student time zone from the browser, validated against PHP timezone list | Handles Canada DST and user-local display |
| Concurrency | MySQL `GET_LOCK` per trainer around availability re-check and insert | Prevents double booking |
| Hold | Slot hold 35 minutes; Stripe Checkout `expires_at` 30 minutes | A slot is never released while a payment can still complete |
| Payment | Stripe Checkout Session, cards only, amount in smallest currency unit, idempotency key per booking. Webhook signature checked on raw body; processed event IDs stored | Avoids duplicate processing |
| Meet host | Each trainer connects their own Google account; event created on trainer calendar; trainer hosts meeting | Student is admitted by the trainer |
| Emails | `wp_mail` via WP Mail SMTP, HTML templates in `/templates` | Deliverability and easy edits |
| Cron | Real server cron calls `wp-cron.php` every 5 minutes; `DISABLE_WP_CRON` set to true | Prevents WP-Cron dependence on traffic |
| Caching | Booking page and plugin REST routes excluded from page and Cloudflare cache | Prevents stale slots and stale nonces |
| Access | Roles: `fwh_sb_trainer`, `fwh_sb_coordinator` with custom capabilities; trainer queries scoped to their trainer ID | Prevents cross-trainer data access |
## 4. Required access and inputs before starting
The developer should not start Block 2 until the first four items are in hand.
Required items:
- Staging wp-admin login for https://harman.lngc.ltd
- cPanel access for the server hosting harman.lngc.ltd (FTP account, cron job, PHP version, `wp-config.php`)
- Cloudflare access for `lngc.ltd`, or a contact who can add rules today
- Stripe: developer added to Harman’s Stripe account with Developer role, or secure test API keys shared
- Google Cloud: project owned by Harman, with infrastructure setup through Claude in Chrome
- Trainer list: name, email, Google account, weekly hours in Toronto time, days off
- Session price and currency, gap between sessions, minimum notice before booking, coordinator email, contact email for students
## 5. Today’s schedule
The build is organized into 13 working hours.
| Block | Time | Work | Done when |
|---|---|---|---|
| 1 | 0:00 to 1:30 | Environment and server setup | Empty plugin active on staging, server cron running, debug log on, WinSCP syncing |
| 2 | 1:30 to 2:30 | Prompt 1: data model, roles, settings, trainers | Tables exist; settings, trainers, hours and blocks save |
| 3 | 2:30 to 4:30 | Prompt 2: availability engine and booking page | Correct slots shown in visitor time zone, including after 1 Nov 2026 |
| 4 | 4:30 to 6:30 | Prompt 3: booking, hold and Stripe | Test payment marks booking paid via webhook; abandoned payment releases the slot |
| 5 | 6:30 to 8:00 | Prompt 4: Google Meet and emails | Paid booking gets a Meet link; exactly two emails sent |
| 6 | 8:00 to 9:30 | Prompt 5: trainer portal, coordinator view, cancel | Each role sees only what it should |
| 7 | 9:30 to 10:00 | Prompt 6: booking page polish | Matches theme; works at 375px and desktop |
| 8 | 10:00 to 10:30 | Prompt 7: security review and package | Review fixes committed; zip built |
| 9 | 10:30 to 12:00 | Full test pass | Every item in the test checklist passes |
| 10 | 12:00 to 13:00 | Go live | Go-live checklist done and one real booking verified |
Cut-off rule:
- If Block 4 (Stripe) is not passing by hour 7, finish building and testing today and go live tomorrow morning.
- Never go live with an untested payment or Meet flow.
## 6. Block-by-block requirements
### Block 1: Environment and server setup
#### 1.1 Tools on the Windows laptop
- Git for Windows (default options). Claude Code uses Git Bash.
- Install Claude Code via PowerShell:
`irm https://claude.ai/install.ps1 | iex`
- Reopen PowerShell and run `claude` once to log in.
- WinSCP for file sync.
- VS Code is optional for reviewing diffs.
#### 1.2 Server (cPanel for harman.lngc.ltd)
- MultiPHP Manager: set the subdomain to PHP 8.1 or newer.
- Backup: export the staging database before activating anything.
- FTP accounts: create `claude` with directory set to the staging site `wp-content/plugins` only.
- Cron jobs: add a job every 5 minutes running:
`wget -q -O - "https://harman.lngc.ltd/wp-cron.php?doing_wp_cron" >/dev/null 2>&1`
#### 1.3 WordPress on staging
- Settings > Reading: tick “Discourage search engines”.
- Deactivate Site Kit by Google.
- Install WP Mail SMTP and connect a real mailbox; send a test email.
- Check for caching/security plugins and exclude booking page URL and `/wp-json/fwh-sb/` from caching.
- Allow `/wp-json/fwh-sb/v1/stripe-webhook` in any firewall.
- Cloudflare: add a cache rule to bypass cache for the booking page and `/wp-json/fwh-sb/`.
- For the webhook path, ensure it is not challenged; on paid plans add a WAF skip rule; on the free plan, switch off Bot Fight Mode for that zone during the build.
#### 1.4 Project folder and `CLAUDE.md`
Create:
- `C:\Projects\fwh-speaking-booking`
- Save `CLAUDE.md` in the folder using Notepad with “Save as type: All files”.
`CLAUDE.md` includes the core project brief:
- Plugin name: `fwh-speaking-booking`
- WordPress plugin for frenchwithharman.com (custom theme `harman`)
- Staging: https://harman.lngc.ltd
- Product: 20-minute 1-to-1 French speaking session; price and currency in settings
- Multiple trainers with auto-assignment
- Students never choose or see a trainer before booking
- Trainer hours are Mon-Sat in America/Toronto, edited by trainers from their own login
- Sunday always closed
- Students see slots in their browser time zone
- Payment via Stripe Checkout, cards only
- A booking becomes paid only through a signature-verified webhook or a server-side Stripe API check
- After payment: Google Meet event created on the assigned trainer’s calendar, with student as guest
- One email to student, one email to trainer with coordinator in CC
- Architecture rules and workflow requirements
#### 1.5 WinSCP sync
- Connect with FTP and TLS/SSL Explicit encryption using the `claude` FTP account.
- Right side: open `fwh-speaking-booking`.
- Left side: open `C:\Projects\fwh-speaking-booking`.
- Commands > Keep Remote Directory up to Date.
- In Transfer settings, set the file mask to ignore `.git/` and `CLAUDE.md`.
- Keep WinSCP open all day.
- Keep a second WinSCP tab on `wp-content/debug.log` to read errors.
#### 1.6 Start Claude Code
- Open PowerShell in `C:\Projects\fwh-speaking-booking`
- Run `claude`
- Paste:
- Read `CLAUDE.md`
- Scaffold the plugin only: main file with header (`Plugin Name: FWH Speaking Booking`, `Requires PHP: 8.0`), autoloader for `/includes`, activation and deactivation hooks, empty “Speaking Booking” admin menu for `fwh_sb_manage`
- Initialise git with a `.gitignore`
- No features yet
- Tell the developer how to verify activation on staging.
- Activate `FWH Speaking Booking` in staging wp-admin, confirm `debug.log` has no notices, then commit.
### Working with Claude Code
- Switch to plan mode (`Shift+Tab`) before pasting each module prompt.
- Review the plan, correct it, then let it implement.
- Read the diff before accepting.
- Push back on anything ignoring `CLAUDE.md`.
- Test in the browser and check `debug.log` after every module.
- Commit only when clean.
- Run `/clear` after each commit so the next module starts from a fresh context.
- If it loops on a bug after two attempts, ask it to stop, explain the root cause, and propose two fixes before changing code.
## 7. Build prompts and module requirements
### Prompt 1: data model, roles, settings, trainers (Block 2)
Required tables:
- `trainers`:
- `id`
- `user_id` nullable
- `name`
- `email`
- `google_email`
- `active`
- `created_utc`
- `trainer_hours`:
- `id`
- `trainer_id`
- `weekday` (`1=Mon..6=Sat`)
- `start_time`
- `end_time` (Toronto local time; multiple ranges per day allowed)
- `blocks`:
- `id`
- `trainer_id` nullable (`NULL` = all trainers)
- `start_utc`
- `end_utc`
- `note`
- `created_by`
- `bookings`:
- `id`
- `ref` unique (example format `FWH-000123`)
- `trainer_id`
- `start_utc`
- `end_utc`
- `status` (`held|paid|expired|cancelled`)
- `hold_expires_utc`
- `student_name`
- `student_email`
- `student_phone`
- `student_tz`
- `amount` int in minor currency units
- `currency`
- `stripe_session_id` unique
- `stripe_payment_intent`
- `google_event_id`
- `google_calendar_owner` (`trainer|fallback`)
- `meet_link`
- `meet_status` (`none|ok|failed`)
- `meet_attempts`
- `created_utc`
- `updated_utc`
- indexes on `(trainer_id, start_utc)` and `(status, hold_expires_utc)`
- `google_tokens`:
- `trainer_id` primary key (`0 = fallback account`)
- `refresh_token` encrypted with OpenSSL using key derived from `AUTH_KEY`
- `google_email`
- `connected_utc`
- `stripe_events`:
- `event_id` primary key
- `type`
- `received_utc`
On activation:
- Add capability `fwh_sb_manage` to administrator.
- Create role `fwh_sb_coordinator` with read and `fwh_sb_view_bookings`
- Create role `fwh_sb_trainer` with read and `fwh_sb_trainer`
Settings page (`fwh_sb_manage`, Settings API):
- session name
- duration (20)
- price
- currency
- gap minutes
- booking window days (30)
- minimum notice hours
- coordinator email
- student contact email
- Stripe mode (`test|live`) with publishable key, secret key, webhook secret for each mode (masked, autoload off)
- Google OAuth client ID and secret
Trainers page:
- list, add, edit, deactivate
- weekly hour ranges Mon-Sat in Toronto time
- “Create login” creates a WordPress user with role `fwh_sb_trainer`, links `user_id`, and sends the standard WordPress set-password email
Blocks page:
- add and remove blocks for one trainer or all trainers
- handle date or date+time range entered in Toronto time
### Prompt 2: availability engine and booking page (Block 3)
Availability service:
- For a Toronto calendar date, generate candidate starts every `(duration + gap)` minutes inside each active trainer’s ranges for that weekday.
- Mon-Sat only.
- A trainer is free for a candidate when:
- inside their hours
- no block overlaps (theirs or global)
- no booking with status `paid`, or held with `hold_expires_utc > now`, overlaps `[start - gap, end + gap]`
- Apply minimum notice and booking window.
- A slot is available if at least one trainer is free.
- Never expose trainer names or IDs publicly.
- All maths in UTC with explicit `America/Toronto`.
- Must be correct across DST changes on 1 Nov 2026 and 14 Mar 2027.
REST route:
- `GET fwh-sb/v1/slots?from=YYYY-MM-DD&to=YYYY-MM-DD`
- Returns available UTC ISO start times grouped by Toronto date
- Max 31 days per call
- Rate limited per IP with transients
- `nocache_headers()`
Shortcode:
- `[fwh_speaking_booking]`
- Session card showing session name, 20 minutes, price
- Month calendar for booking window with only days that have slots clickable
- Slot list rendered in browser time zone (Intl API), plus a time zone selector defaulting to detected zone
- Details form: name, email, phone with country code, hidden honeypot field
- Submit is wired in Module 3
- Vanilla JS; assets enqueued only where shortcode is used
- `DONOTCACHEPAGE` defined on that page
Admin-only diagnostics page:
- Pick a trainer and show their generated slots for 30 Oct 2026, 2 Nov 2026 and 16 Mar 2027 in Toronto, India and Dubai time
### Prompt 3: booking, hold and Stripe (Block 4)
`POST fwh-sb/v1/book`:
- Requires `wp_rest` nonce and honeypot validation
- Validate all fields and validate `student_tz` against `timezone_identifiers_list()`
- Place booking logic:
1. List trainers free at the requested start, ordered by fewest paid + held bookings in that ISO week, then lowest ID.
2. For each candidate: `GET_LOCK('fwh_sb_trainer_{id}', 5)`; re-check availability; if free, insert booking with `status=held`, `hold_expires_utc = now + 35 min`; `RELEASE_LOCK` in `finally`; stop at first success.
3. If no success, return `409` with “That time was just taken, please pick another”.
4. Create Stripe Checkout Session via `wp_remote_post` to `/v1/checkout/sessions` (include Stripe-Version header, `Idempotency-Key = booking ref`)
5. Set `mode=payment`, `payment_method_types[]=card`, one line item with price data, `customer_email`, `client_reference_id = ref`, `metadata[booking_id]`, `expires_at = now + 30 min`, and return the Checkout URL.
6. Redirect JS to the Checkout URL.
`POST fwh-sb/v1/stripe-webhook`:
- `permission_callback` is `__return_true`
- Verify Stripe signature against raw request body using the current mode’s webhook secret with 300s tolerance and `hash_equals`
- Return `400` on failure
- If event ID already exists in `stripe_events`, return `200`
- Insert new event
- Handle `checkout.session.completed` with `payment_status = paid`
- Load booking by `client_reference_id`
- Check amount and currency match
- Set booking status to `paid`
- Save `payment_intent`
- Trigger `do_action('fwh_sb_booking_paid', $booking_id)`
- If booking no longer held (expired/cancelled): mark it paid only if the trainer is still free at that time; otherwise log and email the coordinator to refund in Stripe
- Handle `checkout.session.expired`: if booking is held, set `expired`
- Return `200` for every handled or ignored event
Cancel URL behavior:
- If booking is still held and its session is not paid, set `expired` so the slot frees immediately.
Cron (every 5 minutes):
- For held bookings past `hold_expires_utc`, retrieve the session from Stripe.
- If paid, run the same paid handler; otherwise set `expired`.
`GET fwh-sb/v1/booking-status?ref=&session_id=`:
- Both must match
- Returns status only
Return page:
- “Confirming your payment”
- Poll every 3 seconds up to 2 minutes
- Then show confirmation with date and time in the student’s time zone
Webhook URL and subscription events:
- Webhook URL: `https://www.frenchwithharman.com/wp-json/fwh-sb/v1/stripe-webhook`
- Subscribe to: `checkout.session.completed` and `checkout.session.expired`
Test steps:
- Use card `4242 4242 4242 4242` for success
- Use card `4000 0000 0000 0002` for decline
### Before Prompt 4: Google Cloud setup
- Create a Google project.
- Enable the Google Calendar API.
- OAuth consent screen: External, app name “French with Harman Booking”, scope `https://www.googleapis.com/auth/calendar.events`
- Publish the app to Production.
- In Testing mode, refresh tokens expire after 7 days and Meet links silently stop; trainers will see an “unverified app” warning once when connecting—this is expected for a small internal app.
- Credentials: OAuth Client ID, Web application. Add the redirect URI Prompt 4 prints.
### Prompt 4: Google Calendar, Meet and emails (Block 5)
OAuth 2.0 requirements:
- Scope: `https://www.googleapis.com/auth/calendar.events`
- `access_type=offline`
- `prompt=consent`
- `state = nonce tied to the user`
Trainer connection storage:
- Per trainer in `google_tokens.trainer_id`
- Add Connect/Reconnect button on admin trainer edit screen now; module 5 adds it to the trainer’s own page
- Fallback connection exists with `trainer_id = 0` on settings page
- Print exact redirect URI to add in Google Cloud
- Handle invalid_grant by marking the connection disconnected and emailing the coordinator
On `fwh_sb_booking_paid`:
- Try immediately; on failure retry via single cron events at +1, +5, +15 minutes (`meet_attempts` tracked)
- Use the assigned trainer’s token. If trainer is not connected, use fallback token, add the trainer as attendee too, and email the coordinator that the trainer must connect Google.
Google event creation:
- `POST calendars/primary/events?conferenceDataVersion=1&sendUpdates=none`
- Summary: `French Speaking Session - {student name}`
- Start/end in UTC
- Description includes booking ref and student phone
- Attendees include student email
- `conferenceData.createRequest { requestId: booking ref, conferenceSolutionKey: { type: hangoutsMeet } }`
- If conference status is pending, `GET` the event until a Meet link exists (max 5 tries, 2s apart)
- Save `google_event_id`, `google_calendar_owner`, `meet_link`, `meet_status = ok`
Emails:
- `wp_mail`, HTML templates in `/templates`
- Only after `meet_status = ok`
- Student email:
- date and time in student TZ with zone name
- 20 minutes
- Join button using Meet link
- booking ref
- contact email
- “Join with the Google account using this email to enter directly; otherwise click Ask to join and your trainer will let you in.”
- Trainer and coordinator email in CC:
- student name, email, phone
- Toronto date and time
- Meet link
- booking ref
- Exactly one email each
- Never email the student without a Meet link
After last failed retry:
- `meet_status = failed`
- Email coordinator the booking details
- Show an admin notice plus a “Retry Meet link” action on the booking
### Prompt 5: trainer portal, coordinator view, cancel (Block 6)
Trainer role (`cap fwh_sb_trainer`):
- After login, redirect to My Sessions
- Remove all other admin menus and admin bar items
- Redirect any other wp-admin URL back to My Sessions
- My Schedule: weekly grid Mon-Sat to edit own hour ranges (Toronto time), own blocks list with add/remove (date, optional time range), Google connection status with Connect/Reconnect
- Reject any change that would leave an existing paid booking outside hours or inside a block, and name that booking
- My Sessions: own paid bookings in a week/month calendar and a list, Toronto time, student name, Join Meeting button; Upcoming and Past tabs
- Resolve `trainer_id` from current user on every request
Coordinator role (`cap fwh_sb_view_bookings`) and administrator:
- Bookings page with tabs Upcoming, Past, Held/Expired, Cancelled
- Filters by trainer and date range
- Columns: ref, student, email, phone, trainer, Toronto time, status, Meet link, meet status
- Cancel (nonce + confirm dialog): set `status = cancelled`, delete Google event with `sendUpdates=none` using token that created it, slot frees
- No emails sent on cancel
Calendar UI:
- plain JS/CSS, or small library bundled in `/assets`
- no CDN
### Prompt 6: booking page polish (Block 7)
- Read active theme’s `style.css` and match fonts, colors and buttons
- Mobile-first design
- Date and slot buttons at least 44px
- Clear selected and disabled states
- Loading and error states
- Accessible labels, keyboard navigation
- Show price, “20 minutes” and the time zone near the slot list
- CSS scoped under `.fwh-sb-wrap`
- Check at 375px and 1280px
### Prompt 7: security review and package (Block 8)
Security review checklist:
- Booking status becomes paid only via the verified webhook or server-side Stripe check
- Simulate two concurrent bookings for the last free trainer at one start time
- Every REST route has proper `permission_callback`
- Every admin action checks capability and nonce
- Every query uses `$wpdb->prepare`
- All output escaped
- Trainer data isolation enforced
- Secrets never exposed
- No notices in `debug.log`
- DST correctness around 1 Nov 2026
Fix every issue, list each fix, commit, then build `fwh-speaking-booking.zip` (plugin folder only, no `.git` and no `CLAUDE.md`) and provide a release checklist.
## 8. Block 9: test checklist (staging, Stripe test mode)
### Booking and payment
- Logged out, in an incognito window: slots load and match trainer hours in Toronto time
- Card `4242 4242 4242 4242`: booking becomes paid via webhook within seconds; Stripe dashboard shows webhook delivered with 200
- Card `4000 0000 0000 0002`: declined, booking stays held, then expires; slot comes back
- Cancel on Stripe page: slot free again immediately
- Close the Stripe tab without paying: slot frees after the hold (checks server cron)
- Resend the same webhook from Stripe dashboard: nothing changes, no second email
- Two browsers book the last free trainer at the same time: one succeeds, the other gets “just taken”
- With one trainer busy, the other trainer is assigned
### Time zones
- Browser time zone set to India, then Dubai, then Toronto: slot times convert correctly
- Slots on 2 Nov 2026 show one hour later for India and Dubai than on 30 Oct after Canada’s clock change
- Sundays, blocked dates and times inside the minimum notice never appear
### Meet and emails
- Event appears on assigned trainer’s Google Calendar with Meet link; Google sends no invite email
- Student receives exactly one email in their time zone
- Trainer receives exactly one email with coordinator in CC
- Trainer joins via link; student joins from another Google account, clicks “Ask to join”, trainer admits
- Trainer not connected: fallback account creates event, coordinator alerted
- Meet failure (temporarily break client secret): no student email, coordinator alert; retry works after fixing
### Roles and admin
- Trainer sees only My Schedule and My Sessions; other wp-admin URLs redirect
- Trainer edits hours and adds a day off: booking page updates; conflicting paid booking changes are rejected
- Coordinator sees all bookings and can cancel; cancel removes calendar event and frees slot
- Booking page works on a real phone
- `debug.log` has no new notices
## 9. Block 10: go-live checklist (frenchwithharman.com)
- Developer review of Prompt 7 output signed off; Anirudh approves go-live
- Full backup of live site (files and database)
- Live host on PHP 8.1 or newer; server cron added and `DISABLE_WP_CRON` set in live `wp-config.php`
- `WP_DEBUG` off on live
- WP Mail SMTP working on live
- Live caching plugin and Cloudflare: booking page and `/wp-json/fwh-sb/` excluded from cache; webhook path not blocked
- Upload `fwh-speaking-booking.zip`: Plugins > Add New > Upload Plugin, activate
- Settings entered; Stripe mode set to live with live keys
- Stripe live mode: webhook endpoint `https://www.frenchwithharman.com/wp-json/fwh-sb/v1/stripe-webhook` with `checkout.session.completed` and `checkout.session.expired`; signing secret pasted
- Google Cloud: live redirect URI added; fallback account connected
- Trainers created with “Create login”; each trainer sets a password, sets hours and connects Google
- “1-to-1 Speaking” page with `[fwh_speaking_booking]` added to the main menu; Book Free Trial buttons pointed to it if Harman agrees
- One real booking: temporarily set a low price, book, confirm Meet link and both emails, cancel it, refund in Stripe, restore the real price
- For 48 hours after launch, check Stripe webhook deliveries and the error log daily
- The plugin uses Toronto time internally, so the live site’s WordPress time zone setting does not need to change.
## 10. Troubleshooting matrix
| Symptom | Likely cause | Fix |
|---|---|---|
| Stripe shows webhook 403 or a challenge page | Cloudflare or security plugin blocking the path | Allow `/wp-json/fwh-sb/v1/stripe-webhook`; switch off Bot Fight Mode on the free plan |
| Webhook 400 “signature” | Wrong signing secret, test/live mismatch, or body modified | Copy the secret for that exact endpoint and mode |
| Holds never expire | Server cron not running | Check cPanel cron job and `DISABLE_WP_CRON` |
| “Nonce” errors or old slots on booking page | Page cache serving stale copy | Exclude the page and REST routes from every cache layer |
| Meet links stop after about a week | OAuth app still in Testing mode | Publish to Production and reconnect accounts |
| Student stuck in “Ask to join” | Trainer not in the meeting or not the host | Trainer must connect their own Google account and join from it |
| Slot times off by one hour | Site time zone used instead of America/Toronto | Fix per `CLAUDE.md` and re-run diagnostics |
| Emails missing | WP Mail SMTP not configured | Send a test from WP Mail SMTP and check its email log |
| White screen | PHP fatal error | Read `wp-content/debug.log`; rename plugin folder to disable it, fix, rename back |
## 11. Implementation rules and conventions
- PHP 8.0+ and current WordPress APIs
- WordPress Coding Standards
- No Composer, no external CDNs
- All HTTP via `wp_remote_*`
- Namespace: `FWH\SpeakingBooking`
- Prefix `fwh_sb_` for options, hooks, tables, capabilities and CSS classes
- Layout:
- `fwh-speaking-booking.php`
- `/includes` (one class per concern)
- `/admin`
- `/public`
- `/templates`
- `/assets`
- Tables via `dbDelta` with option `fwh_sb_db_version`; never drop data on deactivate
- Store all times as UTC `DATETIME`; convert with `new DateTimeZone('America/Toronto')` explicitly; never use `wp_timezone()` or the site time zone
- Concurrency: `GET_LOCK('fwh_sb_trainer_{id}')` around re-check + insert; `RELEASE_LOCK` in `finally`
- Hold 35 minutes; Stripe Checkout `expires_at = now + 30 min`
- REST namespace: `fwh-sb/v1`
- Public routes only:
- `slots` (rate limited)
- `book` (nonce + honeypot)
- `booking-status` (`ref + session_id` must match)
- `stripe-webhook` (signature verified on raw body)
- All other routes require a capability
- Capabilities:
- `fwh_sb_manage` (administrator)
- `fwh_sb_view_bookings` (coordinator)
- `fwh_sb_trainer` (trainer)
- Every trainer query is scoped to the current user’s `trainer_id`
- Secrets in options with `autoload off`, masked in UI, never logged, never sent to browser
- Every query must use `$wpdb->prepare`
- Sanitize all input and escape all output
- Log errors with `error_log('[fwh-sb] ...')`
- Never show raw errors to visitors
## 12. Required workflow for each module
- Show a short plan and file list first
- Then implement
- Stop after each module and give:
- files changed
- browser test steps
- any wp-admin, Stripe or Google steps required
- Commit to git only after approval that the module works
- The staging debug log is `wp-content/debug.log`
## 13. Final delivery requirements
- Build the plugin in staging and confirm all tests pass
- Review and fix all security issues
- Build `fwh-speaking-booking.zip` plugin-only release package
- Ensure the plugin is ready for a controlled go-live on the live French with Harman site
- Ensure all calendar, Stripe, email and access-control flows operate according to the requirements above
This document consolidates all project requirements, architecture constraints, build sequence, tests, and go-live steps into one complete reference.