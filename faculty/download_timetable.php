<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db_connection.php';

if (!$auth->isFaculty()) {
    $auth->redirect('login.php');
}

// Include TCPDF library
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

// Get faculty timetable data
$timetable = $pdo->prepare("
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
$timetable->execute([':faculty_id' => $_SESSION['user_id']]);
$timetable = $timetable->fetchAll(PDO::FETCH_ASSOC);

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

// Create new PDF document
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Vignan University Timetable System');
$pdf->SetAuthor('Vignan University');
$pdf->SetTitle('Faculty Timetable - ' . $_SESSION['name']);
$pdf->SetSubject('Timetable');

// Set margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', 'B', 16);

// University title
$pdf->Cell(0, 10, 'Vignan University', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Faculty Timetable', 0, 1, 'C');
$pdf->Ln(10);

// Faculty information
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Faculty: ' . $_SESSION['name'], 0, 1);
$pdf->Ln(10);

// Create timetable table
$pdf->SetFont('helvetica', '', 10);

// Table header
$header = ['Day', 'Time', 'Subject', 'Department', 'Semester', 'Classroom'];
$w = [25, 25, 60, 40, 30, 30];

// Calculate column widths
$totalWidth = array_sum($w);
$pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$scale = $pageWidth / $totalWidth;
$w = array_map(function($width) use ($scale) {
    return $width * $scale;
}, $w);

// Print table header
$pdf->SetFillColor(211, 211, 211);
$pdf->SetTextColor(0);
$pdf->SetFont('', 'B');
for ($i = 0; $i < count($header); ++$i) {
    $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
}
$pdf->Ln();

// Table data
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0);
$pdf->SetFont('');

foreach ($timetableByDay as $day => $entries) {
    if (!empty($entries)) {
        $firstRow = true;
        foreach ($entries as $entry) {
            if ($firstRow) {
                $pdf->Cell($w[0], 6, $day, 'LR', 0, 'C');
                $firstRow = false;
            } else {
                $pdf->Cell($w[0], 6, '', 'LR');
            }
            
            $pdf->Cell($w[1], 6, date('h:i A', strtotime($entry['start_time'])) . '-' . date('h:i A', strtotime($entry['end_time'])), 'LR');
            $pdf->Cell($w[2], 6, $entry['subject_name'] . ' (' . $entry['subject_code'] . ')', 'LR');
            $pdf->Cell($w[3], 6, $entry['department_name'], 'LR');
            $pdf->Cell($w[4], 6, $entry['semester_name'], 'LR');
            $pdf->Cell($w[5], 6, $entry['classroom'], 'LR');
            $pdf->Ln();
        }
        $pdf->Cell(array_sum($w), 0, '', 'T');
        $pdf->Ln();
    } else {
        $pdf->Cell($w[0], 6, $day, 'LR', 0, 'C');
        $pdf->Cell(array_sum(array_slice($w, 1)), 6, 'No classes scheduled', 'LR');
        $pdf->Ln();
        $pdf->Cell(array_sum($w), 0, '', 'T');
        $pdf->Ln();
    }
}

// Close and output PDF document
$pdf->Output('faculty_timetable_' . $_SESSION['username'] . '.pdf', 'D');
?>