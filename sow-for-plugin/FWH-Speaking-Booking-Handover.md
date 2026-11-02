# FWH Speaking Booking: Plugin Handover Document


## 1. At a glance

| Item | Detail |
|:--|:----------|
| What it is | A custom WordPress plugin that sells and runs one product: a 20-minute one-to-one French speaking session. |
| Website | frenchwithharman.com, theme `harman`, built for WordPress with ACF Pro. |
| Staging | https://harman.lngc.ltd (Stripe and Google in test mode). |
| Plugin folder | `wp-content/plugins/fwh-speaking-booking/` |
| Requirements | PHP 8.0 or newer, WordPress with pretty permalinks, HTTPS, a working outgoing-email setup (SMTP plugin). |
| Payments | Stripe Checkout, cards only. |
| Calendar and video | Google Calendar event with a Google Meet link on the trainer's calendar. |
| Emails | One to the student, one to the trainer (coordinator in CC), sent only after the Meet link exists. |
| Time zone | All booking times are shown and handled in Eastern Time (Toronto). Trainer hours are also entered in Toronto time. |
| Release file | `sow-for-plugin/fwh-speaking-booking.zip` (rebuild it before the final handover so it includes the latest changes). |

## 2. What the plugin does

**The student journey**

1. A purple strip at the top of every page, and any "Enroll now" button linked to `#fwh-book`, opens the booking window.
2. The student picks a day and a time. The calendar shows only days that have a free slot.
3. They enter name, email and phone (with country code). A hidden trap field blocks simple bots.
4. A trainer is assigned automatically. The student never chooses or sees a trainer.
5. The slot is held for 35 minutes and the student is sent to Stripe's secure checkout page.
6. After payment the student returns to the site and sees a confirmation with the Meet link.
7. The student and the trainer each receive one email with the session details and the Meet link.

**Business rules built in**

- One product, one price and currency, set in Settings.
- Several trainers, auto-assigned. The first free trainer for the slot is used.
- Trainer hours are Monday to Saturday in Toronto time. Sunday is always closed.
- A booking becomes paid only through a signature-verified Stripe webhook or a server-side check of the Stripe session. The browser alone can never confirm a booking.
- The coordinator can see every booking and cancel it. Cancelling sends no email.
- Trainers see only their own sessions and schedule.
- Out of scope in this version: reminders, rescheduling, refunds and student accounts.

## 3. Features in detail

**Booking window**

- Opens as a pop-up from the top strip. It loads its code only when first opened, so it adds almost nothing to page speed.
- Three-step progress indicator (Day, Time, Details), animated entrance, loading skeleton and a success tick. Animations switch off for visitors who prefer reduced motion.
- Bottom sheet on phones, centred panel on larger screens, thin custom scrollbar, keyboard and screen-reader support (focus trap, Escape to close).
- Times are shown in Eastern Time with AM/PM. A note under the steps says so; there is no time zone selector.
- Any link or button pointing to `#fwh-book`, or any element with the attribute `data-fwh-book`, opens it. Opening the page with `#fwh-book` at the end of the address also opens it.

**Availability engine**

- Slots come from each trainer's weekly hours, minus days off (blocks), existing bookings and live holds.
- Settings control the session length, the gap after each session, the booking window (how many days ahead) and the minimum notice.
- Daylight-saving changes are handled by converting with the Toronto time zone; all times are stored in UTC.

**Payments**

- Stripe Checkout Session, cards only, with the booking reference as the idempotency key.
- Webhook events used: `checkout.session.completed` and `checkout.session.expired`.
- Amount, currency, reference and session ID must match the booking before it is marked paid.
- If a payment arrives after the hold expired, the booking is confirmed only if the trainer is still free; otherwise the coordinator is emailed to refund it in Stripe.

**Google Meet**

- The event is created on the trainer's own Google Calendar with the student as a guest. Google sends no invitation emails; the plugin's emails are the only ones.
- If the Meet link is not ready, the plugin polls briefly, then retries after 1, 5 and 15 minutes, then alerts the coordinator.
- A fallback Google account is used when a trainer has not connected their own, and the coordinator is told.

**Emails**

- Student email: session time, Meet link and the contact address. Trainer email: student details, time and link, with the coordinator in CC.
- Each email is sent once. If sending fails, a red notice appears in the plugin pages, the failure is logged, and the Bookings page shows a Resend emails button.

## 4. People and permissions

| Role | Capability | What they can do |
|:--|:--|:----------|
| Administrator | `fwh_sb_manage` | Everything: settings, trainers, blocks, bookings, diagnostics. |
| Coordinator | `fwh_sb_view_bookings` | The Bookings page: view all bookings, retry a Meet link, resend emails, cancel. |
| Speaking Trainer | `fwh_sb_trainer` | My Sessions and My Schedule only; the rest of wp-admin is hidden. Every query is limited to the trainer's own ID. |

**Menu pages**

| Page | Purpose |
|:--|:----------|
| Speaking Booking, Settings | Session, top bar, Stripe and Google settings and the fallback Google account. |
| Speaking Booking, Trainers | Add and edit trainers, weekly hours (Monday to Saturday, several ranges per day) and the optional trainer login. |
| Speaking Booking, Blocks | Days off or unavailable ranges for one trainer or for everyone. |
| Speaking Booking, Diagnostics | Shows exactly which slots are offered for a trainer, and why a time is missing. |
| Bookings | Tabs Upcoming, Past, Held / Expired and Cancelled, with filters. Actions: Retry Meet link, Resend emails, Cancel. |
| My Sessions and My Schedule | The trainer's own view; Connect Google lives here. |

## 5. Settings reference

| Field | Meaning |
|:--|:----------|
| Session name | Shown in the pop-up header, checkout and emails. |
| Duration (minutes) | Length of one session. |
| Price and Currency | Charged at checkout. Price in major units (25.00). Currency is a 3-letter Stripe code such as CAD. |
| Gap between sessions (minutes) | Rest time after each session before the next slot starts. |
| Booking window (days) | How far ahead students can book. Default 30. |
| Minimum notice (hours) | The closest a booking may be to now. Default 2. |
| Coordinator email | Receives the CC on every trainer email and all alerts. If empty, alerts go to the WordPress admin email. |
| Student contact email | Shown to students in the confirmation email. |
| Top bar: show, strip text, button label | Controls the purple strip at the top of every page. |
| Stripe mode | Test or Live. Only the keys for the chosen mode are used. |
| Stripe publishable, secret and webhook signing secret (test and live) | Secrets are stored hidden. A blank field keeps the saved value. The plugin rejects keys of the wrong type or mode. |
| Google OAuth client ID and secret | Identify your Google Cloud app. |
| Fallback Google account | Backup calendar owner. Connect, Reconnect or Disconnect. |

**Current staging values:** price 1.00 CAD, 20-minute sessions, 5-minute gap, 30-day window, 2-hour notice, coordinator and contact email `frenchteam54@gmail.com`, Stripe in test mode.

## 6. Theme and site integration

The plugin touches the site in these places. All of them must be kept when the theme or site is moved.

| Where | What was added | Why |
|:--|:----------|:----------|
| `header.php` in the `harman` theme | One line, `<?php wp_body_open(); ?>`, right after the opening `<body>` tag. | Lets the top bar render. If the theme is replaced, add the line again. |
| Courses page, "one to one speaking" card | The Enroll now button link is `#fwh-book`. | Opens the booking window. Other course buttons still go to PayPal. |
| Comparison Table section (ACF layout) | A new dropdown, Section spacing: Default, Remove bottom padding, Remove top padding, Remove top and bottom padding. | Lets editors tighten the gap between stacked sections. The Courses page uses it. |
| Course card images | Six animated SVG illustrations in `assets/images/cards/`, also in the Media Library, used instead of the old videos. About 2 KB each, animation off for reduced motion. | Lighter pages and images that match each course. |
| `functions.php` | The theme stylesheet version was bumped so browsers load the new CSS. | Cache busting. |
| ACF field group "Page Section" | The Section spacing field was added to the database. | When moving to the live site, sync the ACF field group (Tools, Export, Import). |

## 7. Setup guide

**Stripe**

1. Switch the Stripe dashboard to Test mode. Under Developers, API keys, copy the publishable and secret keys into Settings.
2. Under Developers, Webhooks, add the endpoint `https://<site>/wp-json/fwh-sb/v1/stripe-webhook` and select the events `checkout.session.completed` and `checkout.session.expired`.
3. Copy the endpoint's signing secret (starts with `whsec_`) into the matching Settings field and save.
4. Test card: `4242 4242 4242 4242`, any future expiry, any CVC. A declined card: `4000 0000 0000 0002`.
5. For live, repeat with the client's own Stripe account in Live mode.

**Google**

1. In Google Cloud Console create a project and enable the Google Calendar API.
2. Set up the consent screen with the scope `https://www.googleapis.com/auth/calendar.events`. Use Internal if the client has Google Workspace. An External app in Testing needs each connecting account listed as a test user, and its tokens expire after 7 days.
3. Create an OAuth client of type Web application with the redirect URI `https://<site>/wp-admin/admin-post.php?action=fwh_sb_google_callback` (always https).
4. Enter the client ID and secret in Settings, save, then use Connect Google under Fallback Google account. Each trainer connects their own account from My Schedule.

**Email**

1. Install and activate WP Mail SMTP.
2. Use Other SMTP: host `smtp.gmail.com`, TLS, port 587, authentication on, the sending mailbox as username and a Google App Password as the password. A transactional service (Brevo, SendGrid, Mailgun) gives better delivery for live.
3. Set the From Email to the same mailbox with Force From Email on, then send the test from Tools, Email Test.
4. For live, add SPF, DKIM and DMARC records on the domain so the emails do not go to spam.

**Server**

1. Add `define('DISABLE_WP_CRON', true);` to `wp-config.php` and a server cron job every 5 minutes: `*/5 * * * * curl -s https://<site>/wp-cron.php > /dev/null`. It expires old holds and retries failed Meet links.
2. Cloudflare: bypass cache for `/wp-json/fwh-sb/*`, and make sure the webhook URL is not blocked by the firewall. Cloudflare replaces the body of 5xx errors, so the plugin uses 4xx codes for errors a student must read.
3. Keep `DISALLOW_FILE_MODS` true and `WP_DEBUG` off on live. Turn on "Discourage search engines" on staging and switch Site Kit off there.

## 8. Technical reference

**Code layout.** Namespace `FWH\SpeakingBooking`; prefix `fwh_sb_` for options, hooks, tables, capabilities and CSS classes. Classes autoload from `includes/`, `admin/` and `public/`. No Composer, no CDN, no third-party booking plugin; all HTTP uses `wp_remote_*`.

| Folder | Contents |
|:--|:----------|
| `includes/` | Settings, schema, roles, availability, bookings, Stripe and Google clients, Meet service, mailer, REST routes, cron, logging, rate limiting. |
| `admin/` | wp-admin pages: settings, trainers, blocks, bookings, diagnostics, Google connection, trainer portal. |
| `public/` | The `[fwh_speaking_booking]` shortcode and the top bar with its pop-up. |
| `templates/` | The student and trainer HTML emails. |
| `assets/` | Booking widget, top bar and admin CSS and JavaScript. |

**Database tables** (prefix `wp_hf` on this site; created with `dbDelta`; data is never dropped on deactivate)

| Table | Holds |
|:--|:----------|
| `fwh_sb_trainers` | Name, email, Google email, linked WordPress user, active flag. |
| `fwh_sb_trainer_hours` | Weekday and start and end time (Toronto time) per trainer. |
| `fwh_sb_blocks` | Days off, for one trainer or all. |
| `fwh_sb_bookings` | Reference, trainer, start and end (UTC), status, student details, amount, Stripe IDs, Google event ID, Meet link and status, attempts. |
| `fwh_sb_google_tokens` | Encrypted refresh token per trainer; trainer 0 is the fallback account. |
| `fwh_sb_stripe_events` | Processed webhook event IDs, for replay protection. |

**REST API** (namespace `fwh-sb/v1`)

| Route | Access | Purpose |
|:--|:--|:----------|
| `GET /config` | Public, never cached | Price, dates and the security token for the pop-up. Works for logged-in users too. |
| `GET /slots` | Public, token, rate limited | Free slots as UTC times. Returns no trainer information. |
| `POST /book` | Public, token, honeypot, rate limited | Re-checks the slot under a lock, holds it and returns the Stripe Checkout URL. |
| `GET /booking-status` | Public, signed token | Lets the return page poll the result. |
| `POST /stripe-webhook` | Public, signature checked | Confirms or expires bookings. |

**Security and reliability**

- Every query uses `$wpdb->prepare`; input is sanitised and output escaped.
- Secrets are kept in an option with autoload off, masked in the UI, redacted from logs and never sent to the browser. Google refresh tokens are encrypted with AES-256-GCM using a key derived from `AUTH_KEY`.
- The webhook signature is checked on the raw body (HMAC-SHA256, 5-minute tolerance, constant-time compare) and event IDs are stored so replays do nothing.
- A per-trainer database lock around the final re-check and insert stops double booking. A four-process test gave one booking and three clean refusals.
- A one-email-address cap of 3 live holds, honeypot and rate limits protect against slot hogging and bots.
- Only `https://` checkout URLs are followed. Trainer pages resolve the trainer from the logged-in user on every request.

## 9. Operations and troubleshooting

| Symptom | Likely cause | Fix |
|:--|:--|:----------|
| A paid booking has no Meet link | No Google connection, or Google error | Connect Google (Settings or My Schedule), then Bookings, Retry Meet link. |
| Booking has a Meet link but no emails | Mail not set up on the server | Configure WP Mail SMTP, then Bookings, Resend emails. A red notice shows when sending failed. |
| Payment starts but shows an error | Wrong or missing Stripe key, or an unsupported currency | Check the keys and mode in Settings. The page warns about a bad key or currency. |
| Booking stays "held" after paying | Webhook secret missing or webhook not registered | Register the webhook and save its signing secret. The 5-minute cron and the return check also confirm it. |
| Google says redirect_uri_mismatch | The URI in Google Cloud differs from the one in Settings | Copy the URI shown in Settings exactly (https, no trailing slash). |
| Google access_denied | The account is not a listed test user | Add it in the consent screen's test users, or publish or use Internal. |
| Calendar shows no days | No trainer hours, a block, or too little notice | Check Trainers, Blocks and Diagnostics for that trainer. Add hours for more days. |
| Logged-in admin sees an error in the pop-up | Fixed in this version (security token for the wrong user) | Make sure the latest plugin files are installed. |
| Plugin install says missing file permission | `DISALLOW_FILE_MODS` or file ownership | Ask the server owner; keep `DISALLOW_FILE_MODS` true on live. |
| Errors show only as "error code: 5xx" | Cloudflare hides 5xx bodies | Check `wp-content/debug.log` (enable debug temporarily). The plugin logs lines starting with `[fwh-sb]`. |

**Regular tasks**

- After a trainer joins: add them, enter hours, create their login, and ask them to connect Google.
- Watch for the red mail notice and for bookings whose Meet status is not "ok".
- Cancelling a booking does not refund the student; refund in Stripe separately.
- Google test-mode tokens expire after 7 days, so reconnect if Meet links start failing.

## 10. Current status and open items

**Done and verified in tests:** all build modules, the top bar and pop-up, animations, Eastern-only time, trainer and coordinator areas, Stripe Checkout (test mode), Google sign-in and Meet link creation, the Section spacing option and the course card illustrations. 82 automated browser-simulation checks pass.

**Not yet verified:** a full real payment through the webhook, real email delivery, and the layout and animations on real phones and desktops.

| Open item | Owner | Notes |
|:--|:--|:----------|
| SMTP login for the sending mailbox | Client or us | Needs an App Password or an SMTP service. Blocks emails. |
| Stripe test webhook signing secret | Us | Register the endpoint and save the secret. |
| Trainer login and Google connection | Trainer | Trainer "test" has no WordPress login; the fallback account is used. |
| Real trainer hours, including Saturday | Client | Currently one test trainer, Mondays only. |
| Course price check | Client | The Courses card shows 179 CAD, the booking window charges the Settings price (1.00 CAD on staging). |
| Real cron job and Cloudflare rules | Server owner | Needed before launch. |
| Site Kit, search engines, `DISALLOW_FILE_MODS` | Us | Switch Site Kit off on staging, discourage search engines on staging, set `DISALLOW_FILE_MODS` back to true. |
| Commit recent changes and rebuild the release zip | Us | Redirect URI fix, mail failure notice and Resend button, logged-in fix, Eastern-only time, modal trigger, scrollbar. |
| Live accounts | Client | Stripe, Google Cloud, SMTP and DNS in the client's name. |

## 11. Go-live checklist

- [ ] Client has supplied live Stripe keys, a webhook secret and a verified Stripe account.
- [ ] Client's Google Cloud project, OAuth client and consent screen are ready, and every trainer has connected.
- [ ] SMTP works from the live domain and SPF, DKIM and DMARC are in place.
- [ ] Settings are in Live mode, the live webhook endpoint is registered, and a small real payment has worked and been refunded.
- [ ] A test booking confirms: payment, Meet link, calendar event, one student email, one trainer email with the coordinator in CC.
- [ ] Real server cron added and `DISABLE_WP_CRON` set.
- [ ] Cloudflare: cache bypass for `/wp-json/fwh-sb/*`, webhook not blocked.
- [ ] `WP_DEBUG` off, `DISALLOW_FILE_MODS` true, search engines allowed, Site Kit decision made.
- [ ] `wp_body_open()` is present in the live theme's `header.php`; the ACF field group is synced to live.
- [ ] Trainer hours, days off and the price are final, and the Courses buttons are checked.
- [ ] A full backup exists and the client has signed off.
