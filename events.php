<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>U-PARK - Manage Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --unor-blue: #002B49; --unor-gold: #FDB813; }
        body { background-color: #F4F6F9; }
        .unor-navbar { background-color: var(--unor-blue); border-bottom: 4px solid var(--unor-gold); }
        .btn-unor-gold { background-color: var(--unor-gold); color: var(--unor-blue); font-weight: 700; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark unor-navbar mb-4 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.php">
                <i class="fa-solid fa-square-p text-warning"></i> U-PARK | UNO-R Events
            </a>
            <div>
                <a href="index.php?action=index" class="btn btn-outline-light btn-sm me-2">Reservations</a>
                <a href="index.php?action=slots" class="btn btn-warning btn-sm fw-bold">Guard Monitoring</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row g-4">
            <!-- Add Event Form -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-calendar-plus"></i> Add New Event</h5>
                    <form method="POST" action="index.php?action=events">
                        <input type="hidden" name="add_event" value="1">
                        <div class="mb-3">
                            <label class="form-label">Event Name</label>
                            <input type="text" name="event_name" class="form-control" required placeholder="e.g. University Week">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Event Date</label>
                            <input type="date" name="event_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" required placeholder="e.g. Gymnasium">
                        </div>
                        <button type="submit" class="btn btn-unor-gold w-100">Save Event</button>
                    </form>
                </div>
            </div>

            <!-- Events List -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm p-3">
                    <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-calendar-days"></i> Upcoming Campus Events</h5>
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $index => $e): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($e['event_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($e['event_date'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($e['location']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>