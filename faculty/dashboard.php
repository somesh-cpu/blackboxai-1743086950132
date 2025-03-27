<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db_connection.php';

if (!$auth->isFaculty()) {
    $auth->redirect('login.php');
}

$pageTitle = "Faculty Dashboard";

// Get faculty timetable
$timetable = [];
try {
    $stmt = $pdo->prepare("
        SELECT t.day_of_week, t.start_time, t.end_time, 
               s.name as subject_name, s.code as subject_code,
               c.name as classroom, d.name as department_name,
               sem.name as semester_name
        FROM timetable_entries te
        JOIN time_slots t ON te.time_slot_id = t.id
        JOIN subjects s ON te.subject_id = s.id
        JOIN classrooms c ON te.classroom_id = c.id
        JOIN semesters sem ON te.semester_id = sem.id
        JOIN courses co ON sem.course_id = co.id
        JOIN departments d ON co.department_id = d.id
        WHERE te.faculty_id = :faculty_id
        ORDER BY t.day_of_week, t.start_time
    ");
    $stmt->execute([':faculty_id' => $_SESSION['user_id']]);
    $timetable = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching timetable: " . $e->getMessage();
}

// Group timetable by day
$timetableByDay = [
    'Monday' => [],
    'Tuesday' => [],
    'Wednesday' => [],
    'Thursday' => [],
    'Friday' => [],
    'Saturday' => []
];

foreach ($timetable as $entry) {
    $timetableByDay[$entry['day_of_week']][] = $entry;
}

// Include header
include __DIR__ . '/../templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Faculty Sidebar -->
        <div class="col-md-2 px-0 sidebar">
            <div class="d-flex flex-column align-items-center py-3 mb-3">
                <h4 class="text-white">Vignan University</h4>
                <small class="text-muted">Faculty Portal</small>
            </div>
            <ul class="nav flex-column px-2">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-calendar-check"></i> My Timetable
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="../includes/auth.php?logout=1">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 main-content py-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Timetable</h2>
                <div class="text-muted">
                    Welcome, <?php echo $_SESSION['name']; ?>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Classroom</th>
                                    <th>Department & Semester</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timetableByDay as $day => $entries): ?>
                                    <?php if (!empty($entries)): ?>
                                        <?php foreach ($entries as $index => $entry): ?>
                                        <tr>
                                            <?php if ($index === 0): ?>
                                                <td rowspan="<?php echo count($entries); ?>"><?php echo $day; ?></td>
                                            <?php endif; ?>
                                            <td><?php echo date('h:i A', strtotime($entry['start_time'])) . ' - ' . date('h:i A', strtotime($entry['end_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($entry['subject_name']) . ' (' . htmlspecialchars($entry['subject_code']) . ')'; ?></td>
                                            <td><?php echo htmlspecialchars($entry['classroom']); ?></td>
                                            <td><?php echo htmlspecialchars($entry['department_name']) . ' - ' . htmlspecialchars($entry['semester_name']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td><?php echo $day; ?></td>
                                            <td colspan="4" class="text-muted">No classes scheduled</td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Download Timetable</h5>
                </div>
                <div class="card-body">
                    <a href="download_timetable.php" class="btn btn-primary">
                        <i class="bi bi-download"></i> Download as PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include __DIR__ . '/../templates/footer.php';
?>