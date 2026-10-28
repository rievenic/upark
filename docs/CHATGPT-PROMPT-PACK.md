# U-PARK — Copy-paste prompts for ChatGPT

Each block below is self-contained. Paste one at a time. Prompts 1–3 are the most useful if you only run a few.

---

## 0. Context primer (paste this FIRST, once per chat)

```
I'm designing U-PARK, a campus smart-parking web app for the University of
Negros Occidental – Recoletos (UNO-R) in Bacolod City, Philippines. Remember
these design tokens for everything I ask next.

COLOURS
  Navy (primary):      #002B49
  Navy dark:           #001f35
  Navy light accent:   #004B7A
  Gold (CTA/accent):   #FDB813
  Gold hover:          #D49B0E
  Page background:     #F4F6F9
  Surface:             #FFFFFF
  Available (green):   #198754
  Occupied (red):      #dc3545
  Reserved (amber):    #ffc107
  Route blue:          #1677ff
  User-dot blue:       #1877f2

TYPE
  System sans (Segoe UI stack). Headings bold navy. Eyebrow labels 11px,
  weight 800, uppercase, letter-spacing 0.1em. Form labels 12px, weight 750,
  uppercase. Body small 14px, muted grey.

SHAPE
  Cards radius 12px with soft shadow. Hero and big panels radius 16px.
  Inputs and buttons radius 10px, 48–51px tall. Pills fully rounded.
  Accent edges: 4px gold top border on primary action cards, 4px blue left
  border on search cards, 4px gold bottom rule under the navbar.

TONE
  Clean, institutional, trustworthy. Flat, no gradients except the navy
  navbar/hero. Generous white space. Bootstrap 5 grid.

Reply "ready" and wait for my next prompt.
```

---

## 1. Logo / app icon

```
Design a logo for "U-PARK", a campus smart-parking system for the University
of Negros Occidental – Recoletos.

Mark: a rounded square tile, 13px corner radius, filled gold #FDB813,
containing a bold navy #002B49 letter "P" styled as a parking sign. Flat
vector, no gradients, no shadows, no 3D.

Lockup: the tile sits left of a two-line wordmark —
  line 1: "U-PARK", 19px, weight 800, letter-spacing 0.12em, navy
  line 2: "UNIVERSITY OF NEGROS OCCIDENTAL – RECOLETOS", 9px, weight 700,
          letter-spacing 0.1em, 68% opacity grey

Give me: (a) the mark alone on transparent, (b) the horizontal lockup on
white, (c) the lockup reversed on navy #002B49, (d) a square app-icon /
favicon crop. Flat vector style, clean geometry.
```

---

## 2. Main screen — public parking portal

```
Design the desktop home screen of U-PARK (1440px wide). Stack these sections
top to bottom:

1. NAVBAR — navy gradient (#001f35 → #002B49), 4px gold #FDB813 bottom rule.
   Left: gold [P] tile + "U-PARK" with the university name in small gold
   below. Right: gold outline button "Admin Portal" with a shield-user icon.

2. HERO — 16px radius, padding 35×25px, a parking-lot photo under a navy
   overlay (rgba(0,43,73,.88) → rgba(0,31,53,.92)). Left: small gold badge
   "CARITAS ET SCIENTIA", then "UNO-R Campus Smart Parking System" in bold
   white, a one-line subtitle, and a gold button "Guide me to parking" with
   a route icon. Right: a white card showing "LIVE OPEN SPOTS" and a huge
   green number "42" over "out of 60 total spots".

3. CAMPUS NAVIGATION PANEL — white, 16px radius, soft shadow.
   Header row: gold-grey eyebrow "📍 CAMPUS NAVIGATION", heading "Find your
   way to U-PARK", subtitle, and a green pill "● Parking portal" on the right.
   Body is a 8/4 split: left two-thirds is an interactive campus map with
   four numbered red pins (1–4), a blue dot for the user, and a thick blue
   #1677ff route line. Right third is a control column: a large select
   "1 — Front of the OVY Building", a search input, an event select, then
   three stacked full-width buttons — outline-blue "Show my current location"
   (crosshair icon), gold "Preview route" (route icon), green "Start
   navigation" (play icon). Below them a light info tile with a 42×42 cream
   parking-sign icon and "Available parking / 42 open of 60 spaces", then a
   dark button "Reserve a parking space".

4. SEARCH CARD — white card with a 4px blue LEFT border. Heading
   "🔍 Find or Edit/Cancel My Existing Reservation", a wide input with a blue
   "Search Booking" button at 9/3 widths, and a results table with a dark
   header (Driver Name, Plate Number, Event, Assigned Slot, Actions) and small
   outline-blue Edit + red Cancel buttons.

5. EVENTS — heading "📅 Active Campus Events" in navy with a gold calendar
   icon, then three event cards in a row. Each: 170px cover photo, gold date
   badge with a clock icon, bold event title, red map-pin + location, grey
   description. 12px radius.

6. TWO-COLUMN ROW —
   Left card "Open Parking Spaces" (green parking-sign icon): zones listed as
   light grey blocks, each with a red pin + zone name on the left and a grey
   "8 open" pill on the right, then a wrapped grid of 48×48 green rounded
   squares showing slot numbers in bold white.
   Right card with a 4px GOLD TOP border, "✎ Book a Parking Space": uppercase
   small labels over inputs for Driver Full Name, Plate Number, Select Event,
   an outline-blue "Get directions to this event" button, Select Available
   Slot, then a red-labelled "🔒 CREATE YOUR SECRET PASSCODE" password field,
   and a full-width gold "Confirm Parking Reservation" button.

7. FOOTER — centred grey copyright line on white with a top border.

Plus a floating blue pill button fixed bottom-right: "🛣 Where do you want to go?"

Flat, clean, institutional. Use the tokens from my primer.
```

---

## 3. Icon set (44 glyphs)

```
Design a cohesive custom icon set for U-PARK. Outline style, 24×24px grid,
2px stroke, rounded caps and joins, single colour so it can be recoloured.
Flat vector, consistent optical weight, no fills except where noted.

Draw these 44 icons:

PARKING & VEHICLES
  square-p (rounded square with bold P), square-parking (parking sign),
  car (front 3/4 view), car-side (side profile)

MAP & NAVIGATION
  route (winding dashed path with a pin at each end), location-dot (teardrop
  pin), location-crosshairs (pin inside a reticle), crosshairs (plain
  reticle), map-location-dot (folded map with a pin), turn-up (arrow turning
  upward)

PLAYBACK
  play (triangle), pause (two bars), stop (square), expand (four outward
  corner arrows), compress (four inward corner arrows), xmark (cross)

STATUS
  check, circle-check, circle-xmark, circle-info, circle-exclamation,
  triangle-exclamation, circle (solid dot), clock, bolt (lightning)

CALENDAR
  calendar-days, calendar-plus, calendar-check

USER & SECURITY
  user, user-shield, user-plus, shield-halved, lock, key, eye, eye-slash

ACTIONS
  pen, pen-to-square, trash, magnifying-glass, list-check,
  right-from-bracket, arrow-right

Lay them out on a neutral background, labelled, grouped by the categories
above. They must look like one family.
```

---

## 4. Admin login screen

```
Design a desktop admin login page, 1200×700, as a single card with 22px
radius and a soft shadow (0 24px 70px rgba(8,43,69,.13)), split 50/50.

LEFT PANEL — gradient 145° from #124b70 to #051d30 at 78%, min-height 620px,
46px padding, white text. Top: a 46×46 gold #f5b51b tile with 13px radius
holding a navy parking-sign glyph, beside "U-PARK" (19px, weight 800,
letter-spacing 0.12em) over "UNIVERSITY OF NEGROS OCCIDENTAL – RECOLETOS"
(9px, weight 700, 68% white).
Middle: a gold eyebrow "● CAMPUS PARKING", then a huge headline "Campus
parking, made simple." (48px, weight 750, tight letter-spacing −0.04em), then
a 72%-white paragraph.
Decoration: two large thin outline circles (410px and 300px diameter, 1px
rgba(255,255,255,.1)) bleeding off the right edge. Bottom-right: an
illustration 245×190 — a rounded rectangle outlined in 2px dashed
rgba(255,255,255,.24) rotated −12°, with a large gold side-view car (92px)
sitting inside it, also rotated −12°, with a soft drop shadow.
Bottom-left: a small gold shield icon + "Authorized administrators only" in
58% white, 11px.

RIGHT PANEL — white, 56px padding, form max-width 360px, vertically centred.
A dark-gold eyebrow "ADMIN PORTAL", heading "Welcome back" (30px, weight 750,
navy), subtitle "Enter your admin credentials to continue." in grey.
Then two fields, each 51px tall with 10px radius and a 1px #dce4e9 border and
a grey leading icon inset 15px from the left:
  "Username" — person icon, placeholder "Enter your username"
  "Password" — padlock icon, placeholder "Enter your password", plus a 36×36
  eye toggle button on the right inside the field
Then a full-width navy #082b45 "Sign in" button, 51px, 10px radius, with a
right-arrow icon. Below: a thin top border and two small 12px grey links —
"Create an admin account" and "Back to public view" separated by a dot.

Flat, modern, institutional. No stock photography.
```

---

## 5. Guard dashboard

```
Design a desktop guard/security dashboard for U-PARK on a #F4F6F9 background.

NAVBAR — solid navy #002B49, 4px gold bottom rule. Left: gold shield icon +
"U-PARK | Guard Control & Event Priority Dashboard" in white bold. Right: a
white outline "Events" button and a red "Logout" button.

STATS ROW — four equal cards, 12px radius, white uppercase label over a huge
bold number: "TOTAL CAPACITY 60" on blue, "AVAILABLE SPOTS 42" on green,
"OCCUPIED SPOTS 13" on red, "RESERVED SPOTS 5" on amber with dark text.

CONTROL BAR — white card: on the left "☑ Live Parking Control" in bold with a
gold checklist icon; on the right a select "-- Prioritize Slots by Event --"
and a search input "Search Slot, Driver, Plate..." with a blue magnifier
button.

TABLE — white card, dark header row: Slot | Zone | Status | Occupant / Plate /
Event | Parked Duration | Guard Actions.
Rows show: a big bold slot number; grey zone text; a status pill (green
Available / red Occupied / amber Reserved); the driver name in bold with a
small car icon + plate below and a light event tag; a duration badge with a
clock icon ("1h 24m elapsed") and, when over an hour, a red "⚠ Overdue (>1 hr)"
line; and in the actions column a small status dropdown + blue "Save" button
plus either an amber "⚡ 1-Hr Free" button (for overdue rows) or a red
"🗑 Clear" button.

Show about 6 rows mixing all three statuses, including one overdue.
Flat, dense, readable, institutional.
```

---

## 6. Mobile app — Maxim-style, map first

```
Design a mobile screen for U-PARK, 390×844px, in the style of a modern
ride-hailing app (map fills the screen, content in a bottom sheet).

BACKGROUND — a full-bleed campus map with four numbered gold-and-navy parking
pins, a blue user-location dot with a soft accuracy halo, and a thick blue
#1677ff route line from the dot to pin 1.

TOP — a floating white search bar with 12px radius and a soft shadow: a grey
magnifier icon and the placeholder "Where do you want to park?". A small
circular white button with a crosshair icon floats on the right edge below it.

BOTTOM SHEET — white, 24px top radius, peeking ~340px, with a 36×4px grey grab
handle centred at the top. Inside:
  - A row: "Front of the OVY Building" in bold navy on the left, a green pill
    "12 open" on the right
  - A small grey line: "280 m · 2 min drive · Zone A"
  - A horizontal scroll strip of 48×48 rounded slot chips in green, amber and
    red with slot numbers
  - A light info tile: cream parking-sign icon, "Available parking" in bold,
    "42 open of 60 spaces" in grey
  - A full-width gold #FDB813 button, 56px tall, 12px radius, navy bold text:
    "Reserve this slot"

Flat, high-contrast, generous spacing, thumb-friendly targets. Navy #002B49
and gold #FDB813 only — no other brand colours.
```

---

## 7. Supporting prompts

### Hero background image
```
A wide, clean photograph-style illustration of a university campus parking
lot in the tropics, shot from a slightly elevated angle. Neat white-painted
parking bays, a few parked cars and motorcycles, palm trees and a modern
academic building in the background, bright overcast daylight. Composition
must leave the left half visually calm so dark navy overlay text sits on top
of it. 1200×500, no text, no logos, no people's faces.
```

### Event card placeholder (`default_event.jpg`)
```
A neutral placeholder image for a campus event card, 400×240. Flat vector
illustration: a university building silhouette in navy #002B49 on a light
#F4F6F9 background, with simple gold #FDB813 geometric accents (a banner,
small flags, a calendar shape). Calm, generic, works behind any event title.
No text.
```

### Empty / error states
```
Design three small flat-vector empty-state illustrations for U-PARK, each
about 240×180, navy #002B49 + gold #FDB813 + light grey only, matching line
weights:
1. "No open parking slots" — a parking bay with a closed barrier arm
2. "No reservation found" — a magnifier over an empty clipboard
3. "Map unavailable" — a folded map with a dashed outline and a question mark
No text in the artwork.
```

### Component sheet
```
Produce a UI component sheet for U-PARK on a light #F4F6F9 background,
organised in labelled groups, using my tokens:
BUTTONS — gold primary, navy dark, green success, red danger, outline-blue,
  outline-light (on navy), each in default/hover/disabled
BADGES — green Available, red Occupied, amber Reserved, grey plate tag,
  grey zone count pill, green live pill with a dot
SLOT BADGES — 48×48 rounded squares in green, amber and red with numbers
INPUTS — text input, input with a leading icon, password input with an eye
  toggle, select, all at 48–51px with 10px radius, in rest/focus/error
CARDS — plain card, card with 4px gold top border, card with 4px blue left
  border, stat card in all four colours, event card with image
ALERTS — success, danger, warning, info, each with a leading icon and a close X
MODAL — a centred dialog with a red header, a passcode field and two footer
  buttons
Flat, consistent, Bootstrap-5-like proportions.
```

---

## 8. Quick reference — the numbers to quote

| Thing | Value |
|---|---|
| Navy / gold | `#002B49` / `#FDB813` |
| Page bg | `#F4F6F9` |
| Status green / red / amber | `#198754` / `#dc3545` / `#ffc107` |
| Card radius | 12px (hero & panels 16px, login shell 22px) |
| Input / button radius | 10px, height 48–51px |
| Slot badge | 48×48, radius 8 |
| Logo tile | 46×46, radius 13 |
| Accent border | 4px (gold top / blue left / gold under navbar) |
| Event card image | 170px tall, cover |
| Map panel | min 440px desktop, 330px mobile |
| Mobile frame | 390×844 |
