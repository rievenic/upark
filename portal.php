<?php
$googleMapsConfig = [];
$googleMapsConfigPath = __DIR__ . '/../../config/google_maps.php';
if (is_file($googleMapsConfigPath)) {
    $loadedGoogleMapsConfig = require $googleMapsConfigPath;
    if (is_array($loadedGoogleMapsConfig)) {
        $googleMapsConfig = $loadedGoogleMapsConfig;
    }
}
$googleMapsApiKey = getenv('GOOGLE_MAPS_BROWSER_KEY')
    ?: getenv('GOOGLE_MAPS_API_KEY')
    ?: ($googleMapsConfig['browser_key'] ?? $googleMapsConfig['api_key'] ?? '');
if ($googleMapsApiKey === 'PASTE_YOUR_GOOGLE_MAPS_BROWSER_KEY_HERE' || $googleMapsApiKey === 'PASTE_YOUR_BROWSER_API_KEY_HERE') {
    $googleMapsApiKey = '';
}
$googleMapsConfigured = $googleMapsApiKey !== '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>U-PARK - UNO-R Parking Portal</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- FontAwesome Icons CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/parking-map.css">
    
    <style>
        :root { 
            --unor-blue: #002B49; 
            --unor-gold: #FDB813; 
            --unor-dark-blue: #001f35; 
        }
        body { 
            background-color: #F4F6F9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        .unor-navbar { 
            background: linear-gradient(135deg, var(--unor-dark-blue), var(--unor-blue)); 
            border-bottom: 4px solid var(--unor-gold); 
        }
        .hero-banner { 
            background: linear-gradient(rgba(0, 43, 73, 0.88), rgba(0, 31, 53, 0.92)), url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1200&q=80') center/cover; 
            color: #fff; 
            border-radius: 16px; 
            padding: 35px 25px; 
        }
        .event-card { 
            border: none; 
            border-radius: 12px; 
            transition: transform 0.2s; 
            overflow: hidden; 
        }
        .event-card:hover { 
            transform: translateY(-4px); 
        }
        .slot-badge { 
            width: 48px; 
            height: 48px; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            border-radius: 8px; 
            font-size: 0.88rem;
        }
        .btn-unor-gold { 
            background-color: var(--unor-gold); 
            color: var(--unor-blue); 
            font-weight: 700; 
            border: none; 
        }
        .btn-unor-gold:hover { 
            background-color: #d49b0e; 
            color: #fff; 
        }
        #upark-route-map { width: 100%; height: 340px; border-radius: 10px; background: #e9eef2; }
        #upark-directions { max-height: 220px; overflow-y: auto; }
        .upark-direction-step { padding: .65rem 0; border-bottom: 1px solid #e9ecef; }
        #upark-route-guidance { position: fixed; z-index: 2000; inset: 0; width: auto; max-height: none; overflow: auto; background: #fff; border-radius: 0; box-shadow: 0 0 2rem rgba(0,0,0,.35); }
        #upark-map-stage { position: relative; width: 100%; height: min(62vh, 720px); min-height: 340px; }
        #upark-route-map { width: 100%; height: 100%; border-radius: 10px; background: #e9eef2; }
        #upark-map-fullscreen { position: absolute; z-index: 5; top: .75rem; right: .75rem; }
        #upark-map-stage.upark-map-fullscreen { position: fixed; inset: 0; z-index: 3000; width: 100vw; height: 100vh; height: 100dvh; min-height: 0; background: #fff; }
        #upark-map-stage.upark-map-fullscreen #upark-route-map { border-radius: 0; }
        #upark-guidance-dragbar { cursor: default; touch-action: none; user-select: none; }
        #upark-guidance-maximize { display: none; }
        button[data-bs-target="#upark-route-guidance"] { display: none; }
        @media (max-width: 575.98px) { #upark-map-stage { height: 55vh; min-height: 280px; } }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Header Navigation -->
    <nav class="navbar navbar-dark unor-navbar mb-4 py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2" href="index.php">
                <i class="fa-solid fa-square-p text-warning fs-3"></i>
                <div>
                    <div class="lh-1 fs-5">U-PARK</div>
                    <small class="text-warning opacity-75" style="font-size: 0.72rem;">UNIVERSITY OF NEGROS OCCIDENTAL – RECOLETOS</small>
                </div>
            </a>
            <a href="index.php?action=login" class="btn btn-outline-warning btn-sm fw-bold px-3">
                <i class="fa-solid fa-user-shield me-1"></i> Admin Portal
            </a>
        </div>
    </nav>

    <div class="container mb-5">

        <!-- Hero Banner -->
        <div class="hero-banner shadow-sm mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark fw-bold text-uppercase mb-2">Caritas et Scientia</span>
                    <h2 class="fw-bold mb-2">UNO-R Campus Smart Parking System</h2>
                    <p class="mb-0 opacity-90 small d-none d-md-block">
                        Welcome Recoleto! U-PARK provides real-time parking space allocation for students, faculty, guests, and campus event attendees.
                    </p>
                    <button type="button" id="upark-quick-directions-hero" class="btn btn-unor-gold fw-bold mt-3">
                        <i class="fa-solid fa-route me-2" aria-hidden="true"></i>Guide me to parking
                    </button>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="bg-white text-dark p-3 rounded-3 shadow-sm d-inline-block text-center w-100">
                        <div class="small fw-bold text-muted text-uppercase">Live Open Spots</div>
                        <div class="display-5 fw-bold text-success mb-0"><?= htmlspecialchars($summary['available'] ?? 0) ?></div>
                        <small class="text-secondary">out of <?= htmlspecialchars($summary['total'] ?? 0) ?> total spots</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner Alerts -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'cancelled'): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div>Your reservation has been cancelled and the slot is now open.</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'invalid_key'): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-xmark fs-5"></i>
                <div><strong>Action Failed!</strong> The Security Passcode key you entered was invalid. Please check your passcode and try again.</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div>Reservation details updated successfully!</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <section class="upark-nav-panel mb-4" aria-labelledby="uparkNavTitle">
            <div class="upark-nav-heading">
                <div>
                    <span class="upark-eyebrow"><i class="fa-solid fa-location-dot me-1"></i> CAMPUS NAVIGATION</span>
                    <h3 id="uparkNavTitle" class="fw-bold mb-1">Find your way to U-PARK</h3>
                    <p class="text-muted mb-0 small">Choose a mapped parking area, preview the route, then start navigation in Google Maps.</p>
                </div>
                <span class="upark-live-pill"><span class="upark-live-dot"></span> Parking portal</span>
            </div>
            <div class="row g-0 upark-map-shell">
                <div class="col-lg-8 order-2 order-lg-1">
                    <div id="uparkMap" class="upark-map" role="region" aria-label="Interactive map centered on UNO-R">
                        <div class="upark-map-loading"><i class="fa-solid fa-map-location-dot fs-2 mb-2"></i><strong>UNO-R map</strong><span>Configure your Google Maps browser key to load the interactive map.</span></div>
                    </div>
                </div>
                <div class="col-lg-4 order-1 order-lg-2">
                    <div class="upark-map-controls">
                        <label for="uparkParkingArea" class="form-label fw-bold small">Choose your parking destination</label>
                        <select id="uparkParkingArea" class="form-select form-select-lg mb-2">
                            <option value="ovy">1 — Front of the OVY Building</option>
                            <option value="pool">2 — Near the University Swimming Pool</option>
                            <option value="hm">3 — Side of the HM Building</option>
                            <option value="oratory">4 — Side of the Oratory</option>
                        </select>
                        <input id="uparkDestination" class="form-control form-control-lg mb-2" type="text" autocomplete="off" placeholder="Search place or enter an address" value="University of Negros Occidental–Recoletos, Bacolod City, Philippines">
                        <label for="uparkEventSelect" class="form-label fw-bold small mt-2">Or choose a campus event</label>
                        <select id="uparkEventSelect" class="form-select mb-3">
                            <option value="">UNO-R main campus</option>
                            <?php foreach (($events ?? []) as $mapEvent): ?>
                                <?php if (!empty($mapEvent['location'])): ?>
                                    <option value="<?= htmlspecialchars($mapEvent['location'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($mapEvent['event_name'] ?? 'Campus event', ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($mapEvent['location'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="uparkMyLocation" class="btn btn-outline-primary w-100 mb-2"><i class="fa-solid fa-crosshairs me-2"></i>Show my current location</button>
                        <button type="button" id="uparkRouteButton" class="btn btn-unor-gold w-100 mb-2"><i class="fa-solid fa-route me-2"></i>Preview route</button>
                        <button type="button" id="uparkStartNavigation" class="btn btn-success w-100 mb-3"><i class="fa-solid fa-play me-2"></i>Start navigation</button>
                        <div class="upark-info-tile">
                            <div class="upark-info-icon"><i class="fa-solid fa-square-parking"></i></div>
                            <div><strong>Available parking</strong><div class="small text-muted"><span><?= htmlspecialchars($summary['available'] ?? 0) ?></span> open of <?= htmlspecialchars($summary['total'] ?? 0) ?> spaces</div></div>
                        </div>
                        <a href="#reservationForm" class="btn btn-dark w-100 mt-3"><i class="fa-solid fa-calendar-check me-2"></i>Reserve a parking space</a>
                        <p id="uparkMapStatus" class="small text-muted mt-3 mb-0" aria-live="polite">Directions open in Google Maps. Confirm the exact campus entrance with UNO-R security.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Search & Manage Reservation -->
        <div class="card border-0 shadow-sm p-4 mb-4 bg-white rounded-3 border-start border-4 border-primary">
            <h5 class="fw-bold text-primary mb-1"><i class="fa-solid fa-magnifying-glass me-2"></i>Find or Edit/Cancel My Existing Reservation</h5>
            <p class="text-muted small mb-3">Enter your plate number, slot number, or driver name to search for your booking.</p>
            
            <form method="GET" action="index.php" class="row g-2 mb-3">
                <input type="hidden" name="action" value="user_portal">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" placeholder="Enter Plate Number (e.g. ABC 1234) or Full Name..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-search me-1"></i> Search Booking</button>
                </div>
            </form>

            <?php if (!empty($user_reservations)): ?>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Driver Name</th>
                                <th>Plate Number</th>
                                <th>Event / Purpose</th>
                                <th>Assigned Slot</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_reservations as $ur): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($ur['driver_name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($ur['plate_number']) ?></span></td>
                                    <td><?= htmlspecialchars($ur['event_name']) ?></td>
                                    <td><span class="badge bg-success">Slot <?= htmlspecialchars($ur['slot_number']) ?></span> (<?= htmlspecialchars($ur['zone']) ?>)</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#userEditModal<?= $ur['reservation_id'] ?>">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $ur['reservation_id'] ?>">
                                                <i class="fa-solid fa-trash"></i> Cancel
                                            </button>
                                        </div>

                                        <!-- Cancel Password Modal -->
                                        <div class="modal fade" id="cancelModal<?= $ur['reservation_id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title fs-6"><i class="fa-solid fa-key me-1"></i> Passcode Protection</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="index.php?action=cancel_user_reservation">
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="reservation_id" value="<?= $ur['reservation_id'] ?>">
                                                            <input type="hidden" name="slot_id" value="<?= $ur['slot_id'] ?>">
                                                            <p class="small text-muted mb-2">Enter your secret passcode to confirm cancellation:</p>
                                                            <div class="mb-3">
                                                                <input type="password" name="cancellation_key" class="form-control" placeholder="Enter your custom passcode" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-check me-1"></i> Verify & Cancel Slot</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Edit Password Modal -->
                                        <div class="modal fade" id="userEditModal<?= $ur['reservation_id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title fs-6"><i class="fa-solid fa-pen-to-square me-1"></i> Edit Details (Password Required)</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="index.php?action=edit_user_reservation">
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="reservation_id" value="<?= $ur['reservation_id'] ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Driver Name</label>
                                                                <input type="text" name="driver_name" class="form-control" value="<?= htmlspecialchars($ur['driver_name']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Plate Number</label>
                                                                <input type="text" name="plate_number" class="form-control" value="<?= htmlspecialchars($ur['plate_number']) ?>" required>
                                                            </div>
                                                            <div class="mb-3 border-top pt-2">
                                                                <label class="form-label small fw-bold text-danger"><i class="fa-solid fa-lock me-1"></i> VERIFY PASSCODE</label>
                                                                <input type="password" name="cancellation_key" class="form-control" placeholder="Enter your custom passcode" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($_GET['search'])): ?>
                <div class="alert alert-warning mb-0 mt-2 small"><i class="fa-solid fa-circle-info me-1"></i> No active reservation found for "<strong><?= htmlspecialchars($_GET['search']) ?></strong>".</div>
            <?php endif; ?>
        </div>

        <!-- Campus Events -->
        <h4 class="fw-bold mb-3" style="color: var(--unor-blue);"><i class="fa-solid fa-calendar-days me-2 text-warning"></i>Active Campus Events</h4>
        <div class="row g-4 mb-4">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <?php $img = !empty($event['image_path']) && file_exists("uploads/".$event['image_path']) ? "uploads/".$event['image_path'] : "https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=400&q=80"; ?>
                    <div class="col-md-4">
                        <div class="card event-card shadow-sm h-100 bg-white">
                            <img src="<?= $img ?>" class="card-img-top" style="height: 170px; object-fit: cover;">
                            <div class="card-body p-3">
                                <span class="badge bg-warning text-dark mb-2"><i class="fa-solid fa-clock me-1"></i><?= htmlspecialchars($event['event_date']) ?></span>
                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($event['event_name']) ?></h5>
                                <p class="small text-muted mb-2"><i class="fa-solid fa-location-dot text-danger me-1"></i><?= htmlspecialchars($event['location']) ?></p>
                                <p class="small text-secondary mb-0"><?= htmlspecialchars($event['description'] ?? 'No description available.') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><p class="text-muted">No active campus events listed.</p></div>
            <?php endif; ?>
        </div>

        <!-- Open Slots (Grouped by Campus Zone Proximity) & Booking Form -->
        <div class="row g-4 mb-4">
            <!-- Grouped Open Parking Badges Section -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 bg-white rounded-3 h-100">
                    <h5 class="fw-bold mb-1" style="color: var(--unor-blue);">
                        <i class="fa-solid fa-square-parking me-2 text-success"></i>Open Parking Spaces
                    </h5>
                    <p class="text-muted small mb-3">Browse available spots grouped by nearby campus venues and event locations.</p>

                    <?php if (!empty($available_slots)): ?>
                            // Group available slots dynamically by zone
                            $grouped_slots = [];
                            foreach ($available_slots as $slot) {
                                $zone_name = $slot['zone'];
                                $grouped_slots[$zone_name][] = $slot;
                            }
                        ?>

                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($grouped_slots as $zone_name => $slots_in_zone): ?>
                                <div class="p-3 rounded-3 border bg-light">
                                    <div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-1">
                                        <span class="fw-bold text-dark small">
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i><?= htmlspecialchars($zone_name) ?>
                                        </span>
                                        <span class="badge bg-secondary text-uppercase" style="font-size: 0.68rem;">
                                            <?= count($slots_in_zone) ?> open
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($slots_in_zone as $s): ?>
                                            <div class="slot-badge bg-success text-white shadow-sm" title="Zone: <?= htmlspecialchars($s['zone']) ?> <?= !empty($s['nearest_landmark']) ? '(Near '.$s['nearest_landmark'].')' : '' ?>">
                                                <?= htmlspecialchars($s['slot_number']) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-warning mb-0 small">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> No open parking slots available right now.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 bg-white rounded-3 border-top border-4 border-warning h-100">
                    <h5 class="fw-bold mb-3" style="color: var(--unor-blue);"><i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Book a Parking Space</h5>
                    <form method="POST" action="index.php?action=create">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">DRIVER FULL NAME</label>
                            <input type="text" name="driver_name" class="form-control" required placeholder="e.g. Juan Dela Cruz">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">PLATE NUMBER</label>
                            <input type="text" name="plate_number" class="form-control" required placeholder="e.g. ABC 1234">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">SELECT EVENT / PURPOSE</label>
                            <select id="upark-event-select" name="event_id" class="form-select">
                                <option value="">-- Choose Event (Optional) --</option>
                                <?php foreach ($events as $e): ?>
                                    <option value="<?= htmlspecialchars($e['id'], ENT_QUOTES, 'UTF-8') ?>" data-location="<?= htmlspecialchars($e['location'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($e['event_name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-outline-primary w-100 mb-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#upark-route-guidance"
                                aria-expanded="false" aria-controls="upark-route-guidance">
                            <i class="fa-solid fa-route me-1"></i> Get directions to this event
                        </button>
                        <div class="d-none upark-fullscreen" id="upark-route-guidance">
                        <section aria-labelledby="upark-route-title">
                            <div id="upark-guidance-dragbar" class="d-flex align-items-center justify-content-between gap-2 p-3 border-bottom bg-light rounded-top-3">
                                <span class="fw-bold"><i class="fa-solid fa-route text-primary me-2"></i>Where do you want to go?</span>
                                <div class="d-flex gap-2">
                                    <button id="upark-guidance-maximize" type="button" class="btn btn-sm btn-outline-secondary" aria-label="Maximize guidance window" title="Maximize"><i class="fa-solid fa-expand"></i></button>
                                    <button id="upark-guidance-close" type="button" class="btn btn-sm btn-outline-secondary" aria-label="Close guidance window" title="Close"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                            <div class="p-3">
                            <div class="mb-2">
                                <label for="upark-route-destination" class="form-label small fw-bold">WHERE DO YOU WANT TO GO?</label>
                                <select id="upark-route-destination" class="form-select">
                                    <option value="gym-parking" data-coordinates="10.658011,122.949364">University Parking Lot near the Gymnasium</option>
                                    <option value="motorcycle-parking" data-coordinates="10.657929,122.949550">Motorcycle Parking</option>
                                    <option value="event">Selected event venue</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <label id="upark-route-title" class="form-label small fw-bold mb-0"><i class="fa-solid fa-route text-primary me-1"></i> YOUR CAMPUS ROUTE</label>
                                <button id="upark-locate-button" type="button" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-location-crosshairs me-1"></i> Use my location
                                </button>
                            </div>
                            <p id="upark-route-status" class="small text-muted mb-2" role="status" aria-live="polite">Choose an event and use your location to preview the driving route.</p>
                            <div class="d-flex flex-wrap gap-3 small fw-semibold mb-2">
                                <span><span class="badge rounded-pill bg-success me-1">S</span> Starting point: your location</span>
                                <span><span class="badge rounded-pill bg-danger me-1">F</span> Finish: selected parking or event</span>
                            </div>
                            <div id="upark-map-stage">
                                <div id="upark-route-map" aria-label="Map showing your location and route to the selected event"></div>
                                <button id="upark-map-fullscreen" type="button" class="btn btn-light shadow-sm" aria-pressed="false">
                                    <i class="fa-solid fa-expand me-1" aria-hidden="true"></i><span>Full screen map</span>
                                </button>
                            </div>
                            <div id="upark-route-summary" class="small fw-semibold mt-2" aria-live="polite"></div>
                            <ol id="upark-directions" class="small text-muted mt-2 mb-0 ps-3"></ol>
                            <a id="upark-navigation-link" class="btn btn-sm btn-success mt-2 d-none" target="_blank" rel="noopener noreferrer">
                                <i class="fa-solid fa-turn-up me-1"></i> Open turn-by-turn navigation
                            </a>
                            <div class="d-flex flex-wrap gap-2 mt-3 border-top pt-3" aria-label="Live guidance controls">
                                <button id="upark-guidance-start" type="button" class="btn btn-success"><i class="fa-solid fa-play me-1"></i>Start guidance</button>
                                <button id="upark-guidance-pause" type="button" class="btn btn-warning" disabled><i class="fa-solid fa-pause me-1"></i>Pause</button>
                                <button id="upark-guidance-stop" type="button" class="btn btn-outline-danger" disabled><i class="fa-solid fa-stop me-1"></i>Stop</button>
                            </div>
                            <?php if (!$googleMapsConfigured): ?>
                                <div class="alert alert-warning small mt-3 mb-0">Google Maps is not configured. Set <code>browser_key</code> in <code>config/google_maps.php</code> or provide the <code>GOOGLE_MAPS_API_KEY</code> environment variable.</div>
                            <?php endif; ?>
                            </div>
                        </section>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">SELECT AVAILABLE SLOT (BY PROXIMITY)</label>
                            <select name="slot_id" class="form-select" required>
                                <option value="">-- Choose Slot Near Your Location --</option>
                                <?php foreach ($available_slots as $s): ?>
                                    <option value="<?= $s['id'] ?>">
                                        Slot <?= htmlspecialchars($s['slot_number']) ?> — <?= htmlspecialchars($s['zone']) ?> <?= !empty($s['nearest_landmark']) ? '(Near '.htmlspecialchars($s['nearest_landmark']).')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted d-block mt-1">Pick a slot closest to your event venue to save walking time.</small>
                        </div>
                        <div class="mb-3 border-top pt-2">
                            <label class="form-label small fw-bold text-danger"><i class="fa-solid fa-lock me-1"></i> CREATE YOUR SECRET PASSCODE</label>
                            <input type="password" name="custom_key" class="form-control" required placeholder="Create a secret passcode (e.g. MySecret123)" minlength="4">
                            <small class="text-muted d-block mt-1">This passcode will be stored with your reservation to edit or cancel later.</small>
                        </div>
                        <button type="submit" class="btn btn-unor-gold w-100 py-2 shadow-sm fw-bold">Confirm Parking Reservation</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Passcode Verification Key Modal -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'reserved' && isset($_GET['key'])): ?>
        <?php $actual_key = $_GET['key']; ?>
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.75);" id="keyModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-key me-1"></i> CONFIRM YOUR PASSCODE</h5>
                        <a href="index.php?action=user_portal" class="btn-close"></a>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="fa-solid fa-shield-halved fa-3x text-warning mb-3"></i>
                        <h5 class="fw-bold text-dark">YOUR SECRET PASSCODE HAS BEEN STORED</h5>
                        <div class="bg-dark text-warning p-3 rounded fs-4 fw-bold font-monospace my-3 border border-warning">
                            <?= htmlspecialchars($actual_key) ?>
                        </div>
                        <p class="text-muted small mb-3">Please re-type your passcode below to confirm you remember it:</p>
                        
                        <input type="text" id="passcodeConfirmationInput" class="form-control text-center font-monospace fw-bold mb-3" placeholder="Type your passcode here..." onkeyup="validatePasscodeMatch('<?= htmlspecialchars($actual_key) ?>')">
                        
                        <div id="passcodeError" class="small text-danger d-none mb-2 fw-bold"><i class="fa-solid fa-circle-xmark me-1"></i> Passcode does not match!</div>
                        <div id="passcodeSuccess" class="small text-success d-none mb-2 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Passcode verified!</div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <a href="index.php?action=user_portal" id="screenshotBtn" class="btn btn-secondary fw-bold px-4 disabled" style="pointer-events: none;">
                            I Have Saved/Screenshotted My Key
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function validatePasscodeMatch(expectedKey) {
                const inputVal = document.getElementById('passcodeConfirmationInput').value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                const expectedClean = expectedKey.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                
                const btn = document.getElementById('screenshotBtn');
                const err = document.getElementById('passcodeError');
                const succ = document.getElementById('passcodeSuccess');

                if (inputVal === expectedClean) {
                    btn.classList.remove('btn-secondary', 'disabled');
                    btn.classList.add('btn-primary');
                    btn.style.pointerEvents = 'auto';
                    err.classList.add('d-none');
                    succ.classList.remove('d-none');
                } else {
                    btn.classList.add('btn-secondary', 'disabled');
                    btn.classList.remove('btn-primary');
                    btn.style.pointerEvents = 'none';
                    if (inputVal.length > 0) {
                        err.classList.remove('d-none');
                    } else {
                        err.classList.add('d-none');
                    }
                    succ.classList.add('d-none');
                }
            }
        </script>
    <?php endif; ?>

    <footer class="text-center py-3 text-muted border-top bg-white mt-auto small">
        &copy; <?= date("Y") ?> University of Negros Occidental – Recoletos | U-PARK System
    </footer>

    <button id="upark-quick-directions" type="button" class="btn btn-primary btn-lg shadow position-fixed end-0 bottom-0 m-3" style="z-index: 1030;">
        <i class="fa-solid fa-route me-2" aria-hidden="true"></i>Where do you want to go?
    </button>

    <!-- Bootstrap 5 JavaScript Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <script>
    document.querySelectorAll('#upark-quick-directions, #upark-quick-directions-hero').forEach(button => button.addEventListener('click', function () {
        const panel = document.getElementById('upark-route-guidance');
        if (!panel) return;
        document.body.appendChild(panel);
        panel.classList.remove('d-none');
        panel.dispatchEvent(new Event('upark:open'));
        document.getElementById('upark-event-select')?.focus({ preventScroll: true });
    }));
    (() => {
        const panel = document.getElementById('upark-route-guidance');
        const bar = document.getElementById('upark-guidance-dragbar');
        const maxButton = document.getElementById('upark-guidance-maximize');
        document.getElementById('upark-guidance-close')?.addEventListener('click', () => panel.classList.add('d-none'));
        maxButton?.addEventListener('click', () => {
            const maximized = panel.classList.toggle('upark-maximized');
            maxButton.innerHTML = maximized ? '<i class="fa-solid fa-compress"></i>' : '<i class="fa-solid fa-expand"></i>';
            maxButton.title = maximized ? 'Restore window' : 'Maximize';
            if (window.google?.maps && window.uparkMap) google.maps.event.trigger(window.uparkMap, 'resize');
        });
        let drag = null;
        bar?.addEventListener('pointerdown', event => {
            if (event.target.closest('button') || panel.classList.contains('upark-maximized') || panel.classList.contains('upark-fullscreen')) return;
            const rect = panel.getBoundingClientRect();
            drag = { x: event.clientX, y: event.clientY, left: rect.left, top: rect.top };
            bar.setPointerCapture(event.pointerId);
        });
        bar?.addEventListener('pointermove', event => {
            if (!drag) return;
            panel.style.left = Math.max(0, Math.min(innerWidth - panel.offsetWidth, drag.left + event.clientX - drag.x)) + 'px';
            panel.style.top = Math.max(0, Math.min(innerHeight - 80, drag.top + event.clientY - drag.y)) + 'px';
            panel.style.right = 'auto'; panel.style.bottom = 'auto';
        });
        bar?.addEventListener('pointerup', () => drag = null);
    })();
    </script>

    <!-- Dynamic Event to Slot Proximity Highlight Script -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const eventSelect = document.querySelector('select[name="event_id"]');
        const slotSelect = document.querySelector('select[name="slot_id"]');

        if (eventSelect && slotSelect) {
            eventSelect.addEventListener('change', function () {
                const selectedOptionText = eventSelect.options[eventSelect.selectedIndex].text.toLowerCase();
                
                Array.from(slotSelect.options).forEach(option => {
                    if (option.value === "") return;
                    const optionText = option.text.toLowerCase();
                    
                    if ((selectedOptionText.includes('intramurals') || selectedOptionText.includes('sports') || selectedOptionText.includes('gym')) && optionText.includes('gymnasium')) {
                        option.style.fontWeight = 'bold';
                        option.style.backgroundColor = '#e8f5e9';
                    } else if ((selectedOptionText.includes('tennis') || selectedOptionText.includes('court')) && optionText.includes('tennis')) {
                        option.style.fontWeight = 'bold';
                        option.style.backgroundColor = '#e8f5e9';
                    } else if (selectedOptionText.includes('week') && optionText.includes('main gate')) {
                        option.style.fontWeight = 'bold';
                        option.style.backgroundColor = '#e8f5e9';
                    } else {
                        option.style.fontWeight = 'normal';
                        option.style.backgroundColor = '';
                    }
                });
            });
        }
    });
    </script>
    <?php if (!empty($googleMapsApiKey)): ?>
    <script>
    (() => {
        const eventSelect = document.getElementById('upark-event-select');
        const status = document.getElementById('upark-route-status');
        const summary = document.getElementById('upark-route-summary');
        const directions = document.getElementById('upark-directions');
        const navigationLink = document.getElementById('upark-navigation-link');
        const destinationSelect = document.getElementById('upark-route-destination');
        const mapStage = document.getElementById('upark-map-stage');
        const mapFullscreenButton = document.getElementById('upark-map-fullscreen');
        let map;
        let RouteClass;
        let position = null;
        let marker = null;
        let destinationMarker = null;
        let polylines = [];
        let watchId = null;
        let trackingRequested = false;
        let requestId = 0;
        let mapsScriptRequested = false;
        const mapsApiKey = <?= json_encode($googleMapsApiKey, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const guidancePanel = document.getElementById('upark-route-guidance');
        const locateButton = document.getElementById('upark-locate-button');
        locateButton.disabled = true;

        function refreshMapSize() {
            if (window.google?.maps && map) google.maps.event.trigger(map, 'resize');
            if (map && position) map.setCenter(position);
        }
        function syncMapFullscreenButton() {
            const active = document.fullscreenElement === mapStage || mapStage.classList.contains('upark-map-fullscreen');
            mapFullscreenButton.setAttribute('aria-pressed', active ? 'true' : 'false');
            mapFullscreenButton.innerHTML = active
                ? '<i class="fa-solid fa-compress me-1" aria-hidden="true"></i><span>Exit full screen</span>'
                : '<i class="fa-solid fa-expand me-1" aria-hidden="true"></i><span>Full screen map</span>';
            setTimeout(refreshMapSize, 100);
        }
        mapFullscreenButton.addEventListener('click', async () => {
            if (document.fullscreenElement === mapStage) {
                await document.exitFullscreen?.();
            } else if (mapStage.classList.contains('upark-map-fullscreen')) {
                mapStage.classList.remove('upark-map-fullscreen');
                syncMapFullscreenButton();
            } else if (mapStage.requestFullscreen) {
                try { await mapStage.requestFullscreen(); }
                catch (_) { mapStage.classList.add('upark-map-fullscreen'); syncMapFullscreenButton(); }
            } else {
                mapStage.classList.add('upark-map-fullscreen');
                syncMapFullscreenButton();
            }
        });
        document.addEventListener('fullscreenchange', syncMapFullscreenButton);

        async function initializeMap() {
            if (map) return;
            const { Map } = await google.maps.importLibrary('maps');
            const { Route } = await google.maps.importLibrary('routes');
            RouteClass = Route;
            map = new Map(document.getElementById('upark-route-map'), {
                center: { lat: 10.6765, lng: 122.9509 },
                zoom: 13,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true
            });
            window.uparkMap = map;
            locateButton.disabled = false;
        }

        function routeDestination() {
            const selectedDestination = destinationSelect.options[destinationSelect.selectedIndex];
            if (selectedDestination && selectedDestination.dataset.coordinates) {
                const [lat, lng] = selectedDestination.dataset.coordinates.split(',').map(Number);
                return { routeValue: { lat, lng }, linkValue: lat + ',' + lng };
            }
            if (destinationSelect.value !== 'event') return null;
            const option = eventSelect.options[eventSelect.selectedIndex];
            const venue = option ? option.dataset.location.trim() : '';
            if (!eventSelect.value || !venue) return null;
            const address = venue + ', University of Negros Occidental-Recoletos, Bacolod City, Philippines';
            return { routeValue: address, linkValue: address };
        }

        function clearRoute() {
            polylines.forEach(line => line.setMap(null));
            polylines = [];
            summary.textContent = '';
            directions.replaceChildren();
            navigationLink.classList.add('d-none');
        }

        async function calculateRoute() {
            const destination = routeDestination();
            if (!destination) {
                clearRoute();
                status.textContent = destinationSelect.value === 'event'
                    ? (eventSelect.value ? 'This event does not have a venue yet. Please contact the event organizer.' : 'Choose an event in the booking form, then select “Selected event venue” here.')
                    : 'Choose a destination and use your location to preview the route.';
                return;
            }
            if (!position) {
                status.textContent = 'Choose “Use my location” and allow location access to calculate the route.';
                return;
            }

            const thisRequest = ++requestId;
            clearRoute();
            status.textContent = 'Calculating your driving route…';
            try {
                const result = await RouteClass.computeRoutes({
                    origin: position,
                    destination: destination.routeValue,
                    travelMode: 'DRIVING',
                    regionCode: 'ph',
                    fields: ['path', 'legs']
                });
                if (thisRequest !== requestId) return;
                const route = result.routes && result.routes[0];
                if (!route) throw new Error('No driving route was found for this event venue.');

                polylines = route.createPolylines();
                polylines.forEach(line => line.setMap(map));
                const bounds = new google.maps.LatLngBounds();
                route.path.forEach(point => bounds.extend(point));
                map.fitBounds(bounds, 48);

                const leg = route.legs && route.legs[0];
                const routeEnd = route.path[route.path.length - 1];
                if (routeEnd) {
                    if (!destinationMarker) destinationMarker = new google.maps.Marker({ map, title: 'Finish: selected destination', label: 'F' });
                    destinationMarker.setPosition(routeEnd);
                }
                const values = leg && leg.localizedValues;
                summary.textContent = values
                    ? [values.distance, values.staticDuration].filter(Boolean).join(' · ')
                    : 'Route ready';

                const origin = encodeURIComponent(position.lat + ',' + position.lng);
                navigationLink.href = 'https://www.google.com/maps/dir/?api=1&origin=' + origin
                    + '&destination=' + encodeURIComponent(destination.linkValue) + '&travelmode=driving';
                navigationLink.classList.remove('d-none');

                (leg && leg.steps ? leg.steps : []).forEach(step => {
                    const item = document.createElement('li');
                    item.className = 'upark-direction-step';
                    const instruction = document.createElement('div');
                    instruction.textContent = step.instructions || step.maneuver || 'Continue along the route';
                    item.appendChild(instruction);
                    if (step.localizedValues) {
                        const detail = document.createElement('small');
                        detail.className = 'text-muted';
                        detail.textContent = [step.localizedValues.distance, step.localizedValues.staticDuration].filter(Boolean).join(' · ');
                        item.appendChild(detail);
                    }
                    directions.appendChild(item);
                });
                status.textContent = 'Route shown. Your location will update while this page remains open.';
            } catch (error) {
                if (thisRequest !== requestId) return;
                status.textContent = error.message || 'Could not calculate the route. Check the event venue and try again.';
            }
        }

        function distanceInMeters(a, b) {
            const radians = value => value * Math.PI / 180;
            const dLat = radians(b.lat - a.lat);
            const dLng = radians(b.lng - a.lng);
            const h = Math.sin(dLat / 2) ** 2
                + Math.cos(radians(a.lat)) * Math.cos(radians(b.lat)) * Math.sin(dLng / 2) ** 2;
            return 6371000 * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
        }

        function beginTracking() {
            if (watchId !== null || !navigator.geolocation) return;
            watchId = navigator.geolocation.watchPosition(next => {
                position = { lat: next.coords.latitude, lng: next.coords.longitude };
                if (marker) marker.setPosition(position);
                if (map) map.panTo(position);
                status.textContent = 'Guidance is active. Your position updates on this map while this page stays open.';
            }, error => {
                status.textContent = error.code === 1 ? 'Location access was denied. Allow it in your browser to continue guidance.' : 'Location update was lost. Check device location settings and try again.';
            }, { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 });
            document.getElementById('upark-guidance-start').disabled = true;
            document.getElementById('upark-guidance-pause').disabled = false;
            document.getElementById('upark-guidance-stop').disabled = false;
        }

        function findMyLocation() {
            if (!navigator.geolocation) {
                status.textContent = 'This browser does not support location access.';
                return;
            }
            status.textContent = 'Requesting permission to use your location…';
            navigator.geolocation.getCurrentPosition(found => {
                position = { lat: found.coords.latitude, lng: found.coords.longitude };
                if (!marker) marker = new google.maps.Marker({ map, title: 'Starting point: your location', label: 'S' });
                marker.setPosition(position);
                map.setCenter(position);
                map.setZoom(15);
                calculateRoute();

                if (trackingRequested) beginTracking();
            }, error => {
                const messages = {
                    1: 'Location permission was denied. Allow location access in your browser to see the route.',
                    2: 'Your current location could not be determined. Check your device location settings.',
                    3: 'Location request timed out. Please try again.'
                };
                status.textContent = messages[error.code] || 'Could not access your location.';
            }, { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 });
        }

        locateButton.addEventListener('click', findMyLocation);
        document.getElementById('upark-guidance-start').addEventListener('click', () => {
            trackingRequested = true;
            if (position) beginTracking(); else findMyLocation();
        });
        document.getElementById('upark-guidance-pause').addEventListener('click', () => {
            trackingRequested = false;
            if (watchId !== null) navigator.geolocation.clearWatch(watchId);
            watchId = null;
            document.getElementById('upark-guidance-start').disabled = false;
            document.getElementById('upark-guidance-pause').disabled = true;
            status.textContent = 'Guidance paused. Press Start guidance to resume location updates.';
        });
        document.getElementById('upark-guidance-stop').addEventListener('click', () => {
            trackingRequested = false;
            if (watchId !== null) navigator.geolocation.clearWatch(watchId);
            watchId = null;
            position = null;
            if (marker) marker.setMap(null);
            marker = null;
            clearRoute();
            document.getElementById('upark-guidance-start').disabled = false;
            document.getElementById('upark-guidance-pause').disabled = true;
            document.getElementById('upark-guidance-stop').disabled = true;
            status.textContent = 'Guidance stopped. Choose Use my location or Start guidance when ready.';
        });
        eventSelect.addEventListener('change', calculateRoute);
        destinationSelect.addEventListener('change', calculateRoute);
        window.initUparkPortalMap = initializeMap;

        function loadMapsScript() {
            if (window.google && google.maps) {
                initializeMap();
                return;
            }
            if (mapsScriptRequested) return;
            mapsScriptRequested = true;
            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(mapsApiKey)
                + '&v=weekly&loading=async&callback=initUparkPortalMap';
            script.onerror = () => {
                status.textContent = 'Google Maps could not load. Check the API key and your internet connection.';
            };
            document.head.appendChild(script);
        }

        guidancePanel.addEventListener('upark:open', () => {
            loadMapsScript();
            if (map && window.google && google.maps) {
                google.maps.event.trigger(map, 'resize');
                map.setCenter(position || { lat: 10.6765, lng: 122.9509 });
            }
        });
    })();
    </script>
    <?php endif; ?>
    <script src="assets/js/parking-map.js" defer></script>
    <?php if ($googleMapsConfigured): ?>
        <script defer src="https://maps.googleapis.com/maps/api/js?key=<?= rawurlencode($googleMapsApiKey) ?>&libraries=places&callback=initUParkMap"></script>
    <?php endif; ?>
</body>
</html>
