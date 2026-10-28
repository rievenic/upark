# U-PARK — Design & Vector Inventory

Everything the app currently draws on screen: colours, icons (vectors), shapes, components, and the layout of every page. Extracted from the real code (`portal.php`, `login.php`, `register.php`, `slot.php`, `events.php`, `create.php`, `parking-map.css`, `parking-map.js`).

Use this as the source document. The copy-paste prompts for ChatGPT are in **`docs/CHATGPT-PROMPT-PACK.md`**.

---

## 1. Brand + design tokens

### 1.1 Colours (exact hex from the code)

| Token | Hex | Where it's used |
|---|---|---|
| `--unor-blue` | `#002B49` | Navbar, all headings, logo glyph, gold-button text |
| `--unor-dark-blue` | `#001f35` | Navbar gradient start, hero overlay |
| `--unor-blue-light` | `#004B7A` | Accent (declared, light use) |
| `--unor-gold` | `#FDB813` | Primary CTA, navbar bottom rule, logo tile, highlights |
| `--unor-gold-dark` / hover | `#D49B0E` | Gold button hover (text flips to `#fff`) |
| Page background | `#F4F6F9` | Every page body |
| Card surface | `#FFFFFF` | All cards, panels, modals |

**Login page runs a second, cooler palette:**

| Token | Hex |
|---|---|
| `--navy` | `#082b45` |
| `--navy-deep` | `#051d30` |
| `--blue` | `#124b70` |
| `--gold` | `#f5b51b` |
| `--ink` | `#172b3a` |
| `--muted` | `#71808c` |
| `--line` | `#dce4e9` |

> ⚠️ **Inconsistency worth fixing:** two different navies (`#002B49` vs `#082b45`) and two different golds (`#FDB813` vs `#f5b51b`) exist. Pick one pair for the redesign.

**Status colour system (parking slot states):**

| State | Colour | Bootstrap class | Meaning |
|---|---|---|---|
| Available | Green `#198754` | `bg-success` | Open spot |
| Occupied | Red `#dc3545` | `bg-danger` | Car parked |
| Reserved | Amber `#ffc107` | `bg-warning` | Booked, not arrived |
| Overdue (>60 min) | Red badge + warning | `bg-danger` + `fa-bolt` | Auto-release eligible |

**Map colours:**

| Element | Hex |
|---|---|
| Route polyline | `#1677ff`, weight 6, opacity 0.95 |
| User-location dot | fill `#1877f2`, stroke `#ffffff` 2px, radius 7 |
| Map placeholder gradient | `135deg, #e9f0f3 → #f8fafb` |
| Map container fallback | `#e9eef2` |
| Live pill | bg `#eaf7ef`, text `#167443`, dot `#22a45a` |
| Info tile | bg `#f4f7f9`, border `#e5ebef`, icon tile `#fff5d8` |

### 1.2 Typography

- Family: `'Segoe UI', Tahoma, Geneva, Verdana, sans-serif` (system stack, no webfont loaded)
- Display / hero `h2`: bold, ~2rem
- Login `h1`: `clamp(34px, 4vw, 48px)`, weight 750, letter-spacing `-0.04em`, line-height 1.1
- Section `h4`/`h5`: bold, navy `#002B49`
- Eyebrow / overline: 0.7rem–11px, weight 800, letter-spacing `0.1em–0.15em`, uppercase
- Form labels: 12px, weight 750, letter-spacing `0.04em`, often `UPPERCASE`
- Body small: 0.875rem, muted grey

### 1.3 Shape, radius, elevation

| Element | Radius | Shadow |
|---|---|---|
| Hero banner | 16px | `shadow-sm` |
| Nav/map panel | 16px | `0 8px 26px rgba(0,31,53,.08)` + 1px `#e7edf2` border |
| Cards | 12px (`rounded-3`) | `shadow-sm` |
| Event card | 12px | lifts `translateY(-4px)` on hover |
| Slot badge | 8px | `shadow-sm` |
| Inputs / buttons | 10px | — |
| Logo tile (`brand-mark`) | 13px | — |
| Login shell | 22px (18px mobile) | `0 24px 70px rgba(8,43,69,.13)` |
| Register card | 12px | `0 8px 24px rgba(0,0,0,.12)`, **5px gold top border** |
| Pills / live chips | 999px | — |

**Accent-border convention:** cards are tagged with a 4px coloured edge — *blue left* = search/manage, *gold top* = booking form, *gold bottom* = navbar.

### 1.4 Sizing

| Element | Size |
|---|---|
| Slot badge | 48 × 48 px, font 0.88rem bold |
| Info-tile icon | 42 × 42 px, radius 10, font 1.2rem |
| Logo mark | 46 × 46 px (40 × 40 mobile), glyph 21px |
| Live dot | 7 × 7 px circle |
| Inputs (portal) | min-height 48px |
| Inputs (login) | min-height 51px, padding `12px 14px 12px 43px` (icon inset) |
| Buttons | min-height 46–51px |
| Map panel | min-height 440px (330px mobile) |
| Route map stage | `min(62vh, 720px)`, min 340px (55vh / 280px mobile) |
| Event card image | height 170px, `object-fit: cover` |
| Login brand panel | min-height 620px, padding 46px |

---

## 2. Vector inventory — every icon in the app

All icons are **Font Awesome 6** (`6.4.0` / `6.4.2` via CDN). 44 valid unique glyphs + 1 broken. If you're recreating them as custom SVGs, this is the full set.

### 2.1 Brand / parking

| Icon class | Shape to draw | Used in |
|---|---|---|
| `fa-square-p` | Rounded square with a bold **P** | Navbar logo (portal, events, create) |
| `fa-square-parking` | Square with **P** — parking sign | Login logo, info tile, booking labels |
| `fa-car` | Car, front-3/4 view | Plate-number fields, guard table |
| `fa-car-side` | Car in side profile | Login illustration (92px, gold) |

### 2.2 Map / navigation

| Icon class | Shape to draw | Used in |
|---|---|---|
| `fa-route` | Dashed winding path, pin at each end | "Preview route", "Get directions", floating FAB (**most-used icon, 6×**) |
| `fa-location-dot` | Classic teardrop map pin | Zone headers, event locations |
| `fa-location-crosshairs` | Pin inside crosshair reticle | "Use my location" |
| `fa-crosshairs` | Plain crosshair reticle | "Show my current location" |
| `fa-map-location-dot` | Folded map with a pin | Map loading placeholder (2rem) |
| `fa-turn-up` | Arrow turning upward | "Open turn-by-turn navigation" |

### 2.3 Media / guidance controls

| Icon class | Shape | Used in |
|---|---|---|
| `fa-play` | Solid triangle | "Start navigation", "Start guidance" |
| `fa-pause` | Two vertical bars | "Pause" guidance |
| `fa-stop` | Solid square | "Stop" guidance |
| `fa-expand` | Four outward corner arrows | Fullscreen map / maximize panel |
| `fa-compress` | Four inward corner arrows | Exit fullscreen |
| `fa-xmark` | X cross | Close guidance panel |

### 2.4 Status / feedback

| Icon class | Shape | Used in |
|---|---|---|
| `fa-check` | Checkmark | Confirm buttons |
| `fa-circle-check` | Checkmark in filled circle | Success alerts, passcode verified |
| `fa-circle-xmark` | X in filled circle | Error alerts, passcode mismatch |
| `fa-circle-info` | i in filled circle | Info alerts |
| `fa-circle-exclamation` | ! in filled circle | "No open slots" warning |
| `fa-triangle-exclamation` | ! in triangle | Danger alerts |
| `fa-circle` | Plain filled dot | Eyebrow bullet (login) |
| `fa-clock` | Analog clock face | Event dates, parked duration |
| `fa-bolt` | Lightning bolt | **"1-Hr Free" auto-release** (guard) |

### 2.5 Calendar / events

| Icon class | Shape | Used in |
|---|---|---|
| `fa-calendar-days` | Calendar with grid of day cells | "Active Campus Events" |
| `fa-calendar-plus` | Calendar with + | "Add New Event" |
| `fa-calendar-check` | Calendar with ✓ | "Reserve a parking space" |
| `fa-calendar-event` | ❌ **BROKEN — not a real FA6 name** | `create.php` line 108 → renders blank. Use `fa-calendar-days`. |

### 2.6 User / security

| Icon class | Shape | Used in |
|---|---|---|
| `fa-user` | Head-and-shoulders bust | Driver name field |
| `fa-user-shield` | Bust in front of shield | "Admin Portal", register header (3× size) |
| `fa-user-plus` | Bust with + | "Register Account" |
| `fa-shield-halved` | Shield split vertically | Guard navbar, "Authorized admins only" |
| `fa-lock` | Closed padlock | Passcode fields, guard modal |
| `fa-key` | Key | Admin security key, passcode modals |
| `fa-eye` | Open eye | Show password |
| `fa-eye-slash` | Eye with slash | Hide password |

### 2.7 Actions / utility

| Icon class | Shape | Used in |
|---|---|---|
| `fa-pen` | Pen nib | Edit reservation |
| `fa-pen-to-square` | Pen over a square | "Book a Parking Space", edit modal |
| `fa-trash` | Trash can | Cancel / clear slot |
| `fa-magnifying-glass` | Magnifier | "Find or Edit/Cancel My Reservation" |
| `fa-search` | Same glyph (legacy alias) | Search buttons — **normalise to `fa-magnifying-glass`** |
| `fa-list-check` | List with checkmarks | "Live Parking Control" |
| `fa-right-from-bracket` | Arrow exiting a bracket | Logout |
| `fa-arrow-right` | Right arrow | "Sign in →" |

---

## 3. Non-icon vector elements (shapes drawn in CSS / JS)

These are the custom graphics you'd need to recreate as real SVGs.

### 3.1 Logo lockup
```
┌──────┐
│  [P] │  U-PARK                                    ← 19px, weight 800, ls .12em
└──────┘  UNIVERSITY OF NEGROS OCCIDENTAL – RECOLETOS  ← 9px, weight 700, ls .1em
  46×46
```
- Gold tile `#FDB813`, radius 13px, navy `fa-square-parking` glyph at 21px, centred
- Mobile: tile shrinks to 40×40, glyph 18px, wordmark 16px

### 3.2 Login brand panel illustration
- **Box:** 245 × 190 px, positioned `right: 19px; bottom: 72px`
- **Parking bay lines:** rounded rect, inset `18px 28px`, `2px dashed rgba(255,255,255,.24)`, radius 18px, **rotated −12°**
- **Car:** `fa-car-side` at 92px in gold `#f5b51b`, also rotated −12°, drop-shadow `0 12px 15px rgba(0,0,0,.2)`
- **Background decor:** two concentric outline circles (410px and 300px diameter), `1px solid rgba(255,255,255,.1)`, bleeding off the right edge at `top: 105px` / `top: 160px`
- **Panel fill:** `linear-gradient(145deg, #124b70, #051d30 78%)`

### 3.3 Google Maps vectors (`parking-map.js`)
| Vector | Spec |
|---|---|
| Parking-area pins ×4 | Default red Google pin, white bold numeral label `1`–`4`, BOUNCE animation 700ms on select |
| User location | `SymbolPath.CIRCLE`, scale 7, fill `#1877f2` opacity 1, stroke `#ffffff` 2px |
| Route polyline | `#1677ff`, opacity 0.95, weight 6 |
| Start marker | Label `S` + green pill legend chip |
| Finish marker | Label `F` + red pill legend chip |
| InfoWindow | White card ≤230px: bold area name, lat/lng in small text, hint line |

**The 4 mapped parking areas (real coordinates):**
1. Front of the OVY Building — `10.656332, 122.947260`
2. Near the University Swimming Pool — `10.656025, 122.948658`
3. Side of the HM Building — `10.656863, 122.948430`
4. Side of the Oratory — `10.658461, 122.948377`

Campus centre: `10.6561, 122.94769` · Extra destinations: Gymnasium lot `10.658011, 122.949364`, Motorcycle Parking `10.657929, 122.949550`

### 3.4 Slot badge
48 × 48 rounded square (8px), bold slot number centred, `shadow-sm`, fill = status colour (green/red/amber), white text. Tooltip shows zone + nearest landmark.

### 3.5 Live pill
Rounded-full chip, bg `#eaf7ef`, text `#167443` 0.75rem/700, with a 7px `#22a45a` dot on the left.

### 3.6 Hero banner
Navy gradient overlay `rgba(0,43,73,.88) → rgba(0,31,53,.92)` on a parking-lot photo, `center/cover`, radius 16px, padding `35px 25px`.

> **Missing assets:** the repo has **no** logo file, **no** favicon, and `default_event.jpg` is referenced in `AdminController.php` / `ManageController.php` / `EventAndSlot.php` but doesn't exist. Hero and event-card fallbacks both hot-link the same Unsplash photo. These are the files worth generating first.

---

## 4. Page layouts

### 4.1 Public portal — `portal.php` (the main screen)

```
┌────────────────────────────────────────────────────────────────────┐
│ [P] U-PARK                                      [ 🛡 Admin Portal ]│  navy gradient
│     UNIVERSITY OF NEGROS OCCIDENTAL – RECOLETOS                    │  4px gold rule
├════════════════════════════════════════════════════════════════════┤
│ ╔══════════════════════════════════════════════════════════════╗  │
│ ║  [Caritas et Scientia]                   ┌────────────────┐  ║  │  HERO
│ ║  UNO-R Campus Smart Parking System       │ LIVE OPEN SPOTS│  ║  │  photo + navy
│ ║  Welcome Recoleto! Real-time parking…    │      42        │  ║  │  overlay, r16
│ ║  [ 🛣 Guide me to parking ]              │ out of 60 total│  ║  │
│ ║                                          └────────────────┘  ║  │
│ ╚══════════════════════════════════════════════════════════════╝  │
│                                                                    │
│ [ ✓ alert banner — cancelled / updated / invalid key ]  dismissible│
│                                                                    │
│ ┌──────────────────────────────────────────────────────────────┐  │
│ │ 📍 CAMPUS NAVIGATION               ( ● Parking portal )       │  │  NAV PANEL
│ │ Find your way to U-PARK                                       │  │  r16
│ │ Choose a mapped parking area, preview the route…              │  │
│ ├───────────────────────────────────┬──────────────────────────┤  │
│ │                                   │ Choose your destination  │  │
│ │                                   │ [ 1 — OVY Building   ▾ ] │  │
│ │        GOOGLE MAP                 │ [ Search place…        ] │  │
│ │        4 numbered pins            │ Or choose an event       │  │
│ │        + blue user dot            │ [ UNO-R main campus  ▾ ] │  │
│ │        + blue route line          │ [ ⊕ Show my location   ] │  │  outline blue
│ │                                   │ [ 🛣 Preview route     ] │  │  GOLD
│ │        (8 cols)                   │ [ ▶ Start navigation   ] │  │  green
│ │                                   │ ┌──────────────────────┐ │  │
│ │                                   │ │[P] Available parking │ │  │  info tile
│ │                                   │ │    42 open of 60     │ │  │
│ │                                   │ └──────────────────────┘ │  │
│ │                                   │ [ 📅 Reserve a space   ] │  │  dark
│ │                                   │ status line (aria-live)  │  │
│ └───────────────────────────────────┴──────────────────────────┘  │
│                                                                    │
│ ┃┌─────────────────────────────────────────────────────────────┐  │  4px BLUE
│ ┃│ 🔍 Find or Edit/Cancel My Existing Reservation              │  │  left border
│ ┃│ Enter your plate number, slot number, or driver name…       │  │
│ ┃│ [ Enter Plate Number (e.g. ABC 1234)…    ] [ 🔍 Search    ] │  │  9 / 3 cols
│ ┃│ ┌─────────────────────────────────────────────────────────┐ │  │
│ ┃│ │ DRIVER │ PLATE │ EVENT │ SLOT │ ACTIONS                 │ │  │  dark thead
│ ┃│ │ Juan…  │[ABC…] │ Univ… │[S-12]│ [✎ Edit] [🗑 Cancel]   │ │  │
│ ┃│ └─────────────────────────────────────────────────────────┘ │  │
│ ┃└─────────────────────────────────────────────────────────────┘  │
│                                                                    │
│ 📅 Active Campus Events                                            │
│ ┌────────────┐  ┌────────────┐  ┌────────────┐                    │  3 cols
│ │  [photo]   │  │  [photo]   │  │  [photo]   │  170px cover       │  r12, hover
│ │ 🕐 Mar 14  │  │ 🕐 Mar 20  │  │ 🕐 Apr 02  │  gold badge        │  lift −4px
│ │ Univ Week  │  │ Foundation │  │ Sportsfest │  bold              │
│ │ 📍 Gym     │  │ 📍 Quad    │  │ 📍 Oval    │  red pin           │
│ │ desc…      │  │ desc…      │  │ desc…      │                    │
│ └────────────┘  └────────────┘  └────────────┘                    │
│                                                                    │
│ ┌───────────────────────────┐ ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓   │  4px GOLD top
│ │ [P] Open Parking Spaces   │ ┃ ✎ Book a Parking Space        ┃   │
│ │ Browse spots by venue…    │ ┃ DRIVER FULL NAME              ┃   │
│ │ ┌───────────────────────┐ │ ┃ [ e.g. Juan Dela Cruz       ] ┃   │
│ │ │📍 Gym Zone   [8 open]│ │ ┃ PLATE NUMBER                  ┃   │
│ │ │ [01][02][05][07][11] │ │ ┃ [ e.g. ABC 1234             ] ┃   │  48×48
│ │ │ [12][14][18]         │ │ ┃ SELECT EVENT / PURPOSE        ┃   │  green
│ │ └───────────────────────┘ │ ┃ [ -- Choose Event --      ▾ ] ┃   │  badges
│ │ ┌───────────────────────┐ │ ┃ [ 🛣 Get directions to event] ┃   │
│ │ │📍 Oratory    [3 open]│ │ ┃ SELECT AVAILABLE SLOT         ┃   │
│ │ │ [21][22][25]         │ │ ┃ [ -- Choose Slot Near You --▾]┃   │
│ │ └───────────────────────┘ │ ┃ ─────────────────────────────  ┃   │
│ │                           │ ┃ 🔒 CREATE YOUR SECRET PASSCODE┃   │  red label
│ │                           │ ┃ [ ••••••••                  ] ┃   │
│ │                           │ ┃ [ Confirm Parking Reservation]┃   │  GOLD
│ └───────────────────────────┘ ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛   │
├────────────────────────────────────────────────────────────────────┤
│        © 2026 University of Negros Occidental – Recoletos          │
└────────────────────────────────────────────────────────────────────┘
                                        ┌──────────────────────────┐
                                        │ 🛣 Where do you want to  │  FLOATING FAB
                                        │    go?                   │  fixed bottom-right
                                        └──────────────────────────┘  blue, z-1030
```

### 4.2 Route guidance overlay (fullscreen, `#upark-route-guidance`)

```
┌────────────────────────────────────────────────────────────────┐
│ 🛣 Where do you want to go?              [ ⛶ ]  [ ✕ ]         │  drag bar
├────────────────────────────────────────────────────────────────┤
│ WHERE DO YOU WANT TO GO?                                       │
│ [ University Parking Lot near the Gymnasium               ▾ ]  │
│                                                                │
│ 🛣 YOUR CAMPUS ROUTE              [ ⊕ Use my location ]        │
│ Choose an event and use your location to preview the route.    │
│ (S) Starting point: your location  (F) Finish: parking/event   │  green / red pills
│ ┌────────────────────────────────────────────────────────────┐ │
│ │                                                            │ │
│ │                   MAP  (62vh, max 720px)        [⛶ Full]   │ │
│ │                                                            │ │
│ └────────────────────────────────────────────────────────────┘ │
│ 2.4 km · 7 min                                                 │  summary, bold
│ 1. Head north on Lizares Ave…                                  │  ol, scroll 220px
│ 2. Turn right at the campus gate…                              │
│ [ ↱ Open turn-by-turn navigation ]                             │  green sm
│ ────────────────────────────────────────────────────────────── │
│ [ ▶ Start guidance ]  [ ⏸ Pause ]  [ ⏹ Stop ]                 │  green/amber/outline-red
└────────────────────────────────────────────────────────────────┘
```

### 4.3 Admin login — `login.php` (split screen, 1fr 1fr, 22px radius)

```
┌───────────────────────────────┬───────────────────────────────┐
│  [P] U-PARK                   │                               │
│      UNIVERSITY OF NEGROS…    │   ADMIN PORTAL                │  gold eyebrow
│                               │   Welcome back                │  30px, w750
│  ● CAMPUS PARKING             │   Enter your admin credentials│
│                               │                               │
│  Campus parking,              │   Username                    │  12px, w750
│  made simple.                 │   ┌─────────────────────────┐ │
│                               │   │ 👤 Enter your username  │ │  51px, icon@15px
│  Sign in to manage parking    │   └─────────────────────────┘ │
│  availability, reservations,  │   Password                    │
│  and campus events.           │   ┌─────────────────────────┐ │
│                        ◜◝     │   │ 🔒 ••••••••       [👁]  │ │  toggle 36×36
│              ╭─────────╮  ◞   │   └─────────────────────────┘ │
│              │  🚗     │      │   ┌─────────────────────────┐ │
│              ╰─────────╯      │   │   Sign in           →   │ │  navy, 51px
│          (dashed bay, −12°)   │   └─────────────────────────┘ │
│                               │   ──────────────────────────  │
│  🛡 Authorized administrators │   Create an admin account ·   │  12px links
│     only                      │   Back to public view         │
└───────────────────────────────┴───────────────────────────────┘
  gradient 145° #124b70→#051d30     white, padding 56px
  min-height 620px                  form max-width 360px
```
Mobile ≤760px: brand panel hidden, compact logo row appears above the form, shell caps at 520px.

### 4.4 Admin register — `register.php` (centred card, max 480px)

```
          ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓   5px GOLD top border
          ┃          🛡 (3x, gold)         ┃
          ┃    Create Admin Account        ┃   navy, bold
          ┃  Authorized personnel only…    ┃   muted small
          ┃                                ┃
          ┃  FULL NAME                     ┃
          ┃  [ Full Name                 ] ┃
          ┃  USERNAME                      ┃
          ┃  [ Choose a username         ] ┃
          ┃  PASSWORD                      ┃
          ┃  [ ••••••••                  ] ┃
          ┃  🔑 ADMIN SECURITY KEY         ┃
          ┃  [ administrative auth key   ] ┃
          ┃  [ 👤+ Register Account      ] ┃   GOLD, full width
          ┃  Already registered? Log in    ┃
          ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
```

### 4.5 Guard dashboard — `slot.php`

```
┌────────────────────────────────────────────────────────────────────┐
│ 🛡 U-PARK | Guard Control & Event Priority   [📅 Events] [↪ Logout]│  navy + gold rule
├────────────────────────────────────────────────────────────────────┤
│ [ ℹ alert — slot cleared / invalid PIN / not overdue ]             │
│                                                                    │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐               │  4 stat cards
│ │TOTAL CAP │ │AVAILABLE │ │ OCCUPIED │ │ RESERVED │               │
│ │    60    │ │    42    │ │    13    │ │     5    │               │
│ │  (blue)  │ │ (green)  │ │  (red)   │ │ (amber)  │               │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘               │
│                                                                    │
│ ┌────────────────────────────────────────────────────────────────┐ │
│ │ ☑ Live Parking Control   [Prioritize by Event ▾] [Search…] [🔍]│ │
│ └────────────────────────────────────────────────────────────────┘ │
│                                                                    │
│ ┌────────────────────────────────────────────────────────────────┐ │
│ │ SLOT │ ZONE │ STATUS │ OCCUPANT/PLATE/EVENT │ DURATION │ ACTIONS│ │  dark thead
│ ├──────┼──────┼────────┼──────────────────────┼──────────┼────────┤ │
│ │  12  │ Gym  │[Occupd]│ Juan Dela Cruz       │🕐 1h 24m │[▾][Save│ │
│ │      │      │  red   │ 🚗 ABC 1234          │ ⚠ Overdue│ [⚡1-Hr │ │  bolt = no PIN
│ │      │      │        │ [Univ Week]          │          │  Free] │ │
│ ├──────┼──────┼────────┼──────────────────────┼──────────┼────────┤ │
│ │  13  │ Gym  │[Avail] │ Unoccupied/Open Spot │    --    │[▾][Save│ │
│ │  14  │ Orat │[Reserv]│ Maria Santos         │🕐 0h 18m │[🗑Clear│ │  PIN modal
│ └────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────┘
```

### 4.6 Events manager — `events.php` (4 / 8 split)

```
┌────────────────────────────────────────────────────────────────────┐
│ [P] U-PARK | UNO-R Events        [ Reservations ] [Guard Monitoring]│
├────────────────────────────────────────────────────────────────────┤
│ ┌──────────────────┐ ┌───────────────────────────────────────────┐ │
│ │ 📅+ Add New Event│ │ 📅 Upcoming Campus Events                 │ │
│ │ Event Name       │ │ ┌───────────────────────────────────────┐ │ │
│ │ [ Univ Week    ] │ │ │ # │ EVENT NAME │ DATE     │ LOCATION  │ │ │
│ │ Event Date       │ │ ├───┼────────────┼──────────┼───────────┤ │ │
│ │ [ 2026-03-14   ] │ │ │ 1 │ Univ Week  │Mar 14,26 │[Gymnasium]│ │ │
│ │ Location         │ │ │ 2 │ Foundation │Mar 20,26 │[Quadrangle│ │ │
│ │ [ Gymnasium    ] │ │ └───────────────────────────────────────┘ │ │
│ │ [ Save Event   ] │ │                                           │ │  GOLD
│ └──────────────────┘ └───────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────┘
```

### 4.7 Standalone booking — `create.php` (centred card)

```
          ┌───────────────────────────────────┐
          │       Book Event Parking          │  navy bold
          │  👤 Driver Full Name              │
          │  [ e.g. Juan Dela Cruz          ] │
          │  🚗 Vehicle Plate Number          │
          │  [ e.g. ABC 1234                ] │
          │  📅 Target Event          ⚠broken │  fa-calendar-event
          │  [ -- Select --               ▾ ] │
          │  🛣 Route to event  [⊕ Locate me] │
          │  ┌─────────────────────────────┐  │
          │  │        route map            │  │
          │  └─────────────────────────────┘  │
          │  [P] Available Parking Slot       │
          │  [ -- Choose Slot --          ▾ ] │
          │  [     Confirm Reservation      ] │
          └───────────────────────────────────┘
```

### 4.8 Modals (all `modal-dialog-centered`)

| Modal | Header colour | Icon | Purpose |
|---|---|---|---|
| Cancel reservation | red `bg-danger` | `fa-key` | Passcode → "Verify & Cancel Slot" |
| Edit reservation | blue `bg-primary` | `fa-pen-to-square` | Name + plate + passcode |
| Guard clear slot | red `bg-danger` | `fa-lock` | Guard PIN → "Confirm Removal" |
| Passcode confirmation | gold | `fa-key` | Backdrop `rgba(0,0,0,.75)`, live ✓/✗ validation, button stays disabled until the typed passcode matches |

---

## 5. Component checklist (for a design file / Figma)

**Atoms** — logo mark · wordmark · gold button · navy button · green button · outline-blue button · danger button · text input · input with leading icon · password input with eye toggle · select · status badge (3 states) · slot badge 48×48 · plate badge · zone pill · live pill with dot · count pill · eyebrow label · 4 alert styles · table header row · table body row

**Molecules** — stat card (×4 colours) · info tile · event card · zone group block · map control sidebar · route step list item · S/F legend row · guidance control bar · modal shell (3 header colours) · drag bar

**Organisms** — navbar (public / guard / events variants) · hero banner · campus-navigation panel · search-and-manage card · events grid · open-spaces card · booking form card · guard table · fullscreen guidance overlay · floating FAB · footer

**Screens** — public portal · login · register · guard dashboard · events manager · booking form · route overlay · 4 modals

---

## 6. If you want the "Maxim-style" direction

Maxim's look = map-first, bottom sheet, one big yellow CTA. Your tokens already fit (swap Maxim's yellow for `#FDB813`). The translation:

| Maxim pattern | U-PARK equivalent |
|---|---|
| Fullscreen map as the whole background | Campus map with the 4 numbered parking pins |
| Draggable bottom sheet | Collapse the "Choose destination" sidebar into a sheet |
| "Where to?" search bar floating on top | Reuse the existing `uparkDestination` input |
| Big yellow "Order" button | Gold `Confirm Parking Reservation` |
| Driver/vehicle card after booking | Slot + zone + passcode card |
| Price chips | Slot-status chips (available / reserved / occupied) |
| Live ETA strip | "Parked duration" + overdue warning |

Mobile frame: 390 × 844 px. Map 100%, sheet peeks ~220px, expands to ~70%. Sheet radius 24px top, grab handle 36×4px `#d9e2e9`. Bottom CTA 56px tall, radius 12, gold, navy text.

---

## 7. Known issues to fix while redesigning

1. **`fa-calendar-event` doesn't exist** in FA6 (`create.php:108`) → renders blank. Use `fa-calendar-days`.
2. **Two navies, two golds** across portal vs login — unify.
3. **`fa-search` vs `fa-magnifying-glass`** — same glyph, two names. Pick one.
4. **No logo, favicon, or `default_event.jpg`** in the repo — hero and event fallbacks hot-link Unsplash.
5. **Flat file structure** — `portal.php` links `assets/css/parking-map.css` and `index.php` requires `config/`, `models/`, `controllers/` paths that don't exist in this checkout. Icons/styles will 404 until the folders are restored.
6. **Three different Bootstrap/FA versions** across pages (BS 5.3.0 / 5.3.2, FA 6.4.0 / 6.4.2) — pin one.
