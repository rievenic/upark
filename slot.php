<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>U-PARK - Guard Live Control & Spot Check</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --unor-blue: #002B49; --unor-gold: #FDB813; }
        body { background-color: #F4F6F9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .unor-navbar { background-color: var(--unor-blue); border-bottom: 4px solid var(--unor-gold); }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark unor-navbar mb-4 py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2" href="index.php">
                <i class="fa-solid fa-shield-halved text-warning fs-4"></i>
                <span>U-PARK</span> | Guard Control & Event Priority Dashboard
            </a>
            <div>
                <a href="index.php?action=admin_events" class="btn btn-outline-light btn-sm me-2">
                    <i class="fa-solid fa-calendar-days me-1"></i> Events
                </a>
                <a href="index.php?action=logout" class="btn btn-danger btn-sm fw-bold">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-dismissible fade show d-flex align-items-center gap-2 alert-<?= ($_GET['msg'] == 'invalid_pin' || $_GET['msg'] == 'not_overdue') ? 'danger' : 'success' ?>" role="alert">
                <i class="fa-solid fa-circle-info fs-5"></i>
                <div>
                    <?php 
                        if ($_GET['msg'] == 'slot_cleared') echo "Parking slot cleared successfully! Reset to available.";
                        elseif ($_GET['msg'] == 'invalid_pin') echo "<strong>Access Denied!</strong> Incorrect Guard Password/PIN.";
                        elseif ($_GET['msg'] == 'not_overdue') echo "Slot cannot be auto-released yet (under 1 hour).";
                        elseif ($_GET['msg'] == 'details_updated') echo "Driver details updated!";
                        else echo "Slot status updated!";
                    ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Summary Statistics -->
        <div class="row g-3 mb-4 text-center">
            <div class="col-md-3">
                <div class="card border-0 bg-primary text-white p-3 shadow-sm rounded-3">
                    <h6 class="text-uppercase small mb-1 opacity-75">Total Capacity</h6>
                    <h2 class="fw-bold mb-0"><?= htmlspecialchars($summary['total'] ?? 0) ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-success text-white p-3 shadow-sm rounded-3">
                    <h6 class="text-uppercase small mb-1 opacity-75">Available Spots</h6>
                    <h2 class="fw-bold mb-0"><?= htmlspecialchars($summary['available'] ?? 0) ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-danger text-white p-3 shadow-sm rounded-3">
                    <h6 class="text-uppercase small mb-1 opacity-75">Occupied Spots</h6>
                    <h2 class="fw-bold mb-0"><?= htmlspecialchars($summary['occupied'] ?? 0) ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-warning text-dark p-3 shadow-sm rounded-3">
                    <h6 class="text-uppercase small mb-1 opacity-75">Reserved Spots</h6>
                    <h2 class="fw-bold mb-0"><?= htmlspecialchars($summary['reserved'] ?? 0) ?></h2>
                </div>
            </div>
        </div>

        <!-- Guard Control Header & Priority Filters -->
        <div class="card border-0 shadow-sm p-4 bg-white rounded-3 mb-4">
            <div class="row align-items-center g-3">
                <div class="col-md-5">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-warning me-2"></i>Live Parking Control</h5>
                </div>
                <div class="col-md-7">
                    <form method="GET" action="index.php" class="row g-2">
                        <input type="hidden" name="action" value="slots">
                        
                        <!-- Event Priority Filter -->
                        <div class="col-md-6">
                            <select name="event_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Prioritize Slots by Event --</option>
                                <?php foreach ($events as $ev): ?>
                                    <option value="<?= $ev['id'] ?>" <?= (isset($_GET['event_id']) && $_GET['event_id'] == $ev['id']) ? 'selected' : '' ?>>
                                        Priority: <?= htmlspecialchars($ev['event_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Driver/Plate Search -->
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Search Slot, Driver, Plate..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Guard Table -->
        <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Slot</th>
                            <th>Zone</th>
                            <th>Status</th>
                            <th>Occupant / Plate / Event</th>
                            <th>Parked Duration</th>
                            <th>Guard Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($slots)): ?>
                            <?php foreach ($slots as $slot): ?>
                                <tr>
                                    <td class="fw-bold fs-5 text-dark"><?= htmlspecialchars($slot['slot_number']) ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($slot['zone']) ?></td>
                                    <td>
                                        <span class="badge px-3 py-2 bg-<?= $slot['status'] == 'available' ? 'success' : ($slot['status'] == 'occupied' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst(htmlspecialchars($slot['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($slot['driver_name'])): ?>
                                            <div><strong><?= htmlspecialchars($slot['driver_name']) ?></strong></div>
                                            <small class="text-muted"><i class="fa-solid fa-car me-1"></i><?= htmlspecialchars($slot['plate_number']) ?></small>
                                            <div><span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($slot['event_name']) ?></span></div>
                                        <?php else: ?>
                                            <span class="text-muted small"><em>Unoccupied / Open Spot</em></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($slot['elapsed_minutes'])): ?>
                                            <?php 
                                                $mins = $slot['elapsed_minutes'];
                                                $is_overdue = ($mins >= 60);
                                            ?>
                                            <span class="badge bg-<?= $is_overdue ? 'danger' : 'info' ?> text-dark p-2">
                                                <i class="fa-solid fa-clock me-1"></i><?= floor($mins / 60) ?>h <?= ($mins % 60) ?>m elapsed
                                            </span>
                                            <?php if ($is_overdue): ?>
                                                <div><small class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>Overdue (>1 hr)</small></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <!-- Manual Status Toggle -->
                                            <form method="POST" action="index.php?action=slots" class="d-flex gap-1">
                                                <input type="hidden" name="update_status" value="1">
                                                <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
                                                <select name="status" class="form-select form-select-sm w-auto">
                                                    <option value="available" <?= $slot['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                                                    <option value="occupied" <?= $slot['status'] == 'occupied' ? 'selected' : '' ?>>Occupied</option>
                                                    <option value="reserved" <?= $slot['status'] == 'reserved' ? 'selected' : '' ?>>Reserved</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                            </form>

                                            <?php if (!empty($slot['reservation_id']) || $slot['status'] != 'available'): ?>
                                                <!-- Overdue Auto-Release (No Password Needed if >= 60 mins) -->
                                                <?php if (isset($slot['elapsed_minutes']) && $slot['elapsed_minutes'] >= 60): ?>
                                                    <form method="POST" action="index.php?action=slots" onsubmit="return confirm('Customer absent for over 1 hour. Remove and free slot immediately?');">
                                                        <input type="hidden" name="clear_overdue_slot" value="1">
                                                        <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold" title="Auto Release (No Password Required)">
                                                            <i class="fa-solid fa-bolt me-1"></i> 1-Hr Free
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <!-- Standard Guard Removal Button -->
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#guardRemoveModal<?= $slot['slot_id'] ?>">
                                                        <i class="fa-solid fa-trash"></i> Clear
                                                    </button>

                                                    <!-- Guard Password Verification Modal -->
                                                    <div class="modal fade" id="guardRemoveModal<?= $slot['slot_id'] ?>" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h5 class="modal-title fs-6"><i class="fa-solid fa-lock me-1"></i> Guard Passcode Verification Required</h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST" action="index.php?action=slots">
                                                                    <div class="modal-body text-start">
                                                                        <input type="hidden" name="clear_slot_protected" value="1">
                                                                        <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
                                                                        
                                                                        <p class="small text-muted mb-2">Enter Guard PIN to clear driver <strong><?= htmlspecialchars($slot['driver_name'] ?? 'Occupant') ?></strong> from Slot <?= htmlspecialchars($slot['slot_number']) ?>.</p>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label class="form-label small fw-bold">GUARD PASSWORD / PIN</label>
                                                                            <input type="password" name="guard_pin" class="form-control" placeholder="Enter Guard PIN" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-check me-1"></i> Confirm Removal</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No parking slots found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
