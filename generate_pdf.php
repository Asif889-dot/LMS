<?php
// 1. Load config and start buffering
require_once 'config.php'; 
ob_start();

require_once 'vendor/autoload.php';
require_once 'User.php';
require_once 'Enrollment.php';

$user = new User();
$enrollment = new Enrollment();

// --- DATA FETCHING ---
if (!$user->isLoggedIn() || !isset($_GET['course_id'])) {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'];
$user_name = $_SESSION['username'] ?? 'Student Name';

$query = "SELECT title FROM courses WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$course_title = $course['title'] ?? 'Professional Training';

// Generate Verification ID
$cert_id = "SCA-" . strtoupper(substr(md5($course_title . $user_id), 0, 10));
$verify_url = "https://portal.smartchoiceacademy.softzila.com/verify.php?id=" . $cert_id;

// --- PDF GENERATION ---
$pdf = new \FPDF('L', 'mm', 'A4');
$pdf->SetAutoPageBreak(false); 
$pdf->AddPage();

// 1. THE LUXURY FRAME
$pdf->SetFillColor(15, 23, 42); 
$pdf->Rect(0, 0, 297, 210, 'F');
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(5, 5, 287, 200, 'F');

// Left Decorative Stripe
$pdf->SetFillColor(30, 64, 175); 
$pdf->Rect(5, 5, 12, 200, 'F');
$pdf->SetFillColor(180, 83, 9); 
$pdf->Rect(17, 5, 1.5, 200, 'F');

// 2. LOGO & BRANDING
// --- ADD LOGO HERE ---
if (file_exists('logo.png')) {
    $pdf->Image('logo.png', 25, 12, 20, 20); // Adjust x, y, width, height as needed
}

$pdf->SetXY(48, 15); // Shifted right to make room for logo
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(30, 64, 175);
$pdf->Cell(100, 10, 'SMART CHOICE ACADEMY', 0, 0, 'L');
$pdf->SetXY(48, 22);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 116, 139);
$pdf->Cell(100, 5, 'OFFICIAL CERTIFICATE OF COMPLETION', 0, 0, 'L');

// 3. MAIN TITLES
$pdf->SetY(45);
$pdf->SetFont('Times', 'B', 45);
$pdf->SetTextColor(15, 23, 42);
$pdf->Cell(0, 25, 'Diploma of Achievement', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 15);
$pdf->SetTextColor(107, 114, 128);
$pdf->Cell(0, 10, 'THIS IS TO CERTIFY THAT', 0, 1, 'C');

// 4. STUDENT NAME
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 38);
$pdf->SetTextColor(180, 83, 9);
$pdf->Cell(0, 25, strtoupper($user_name), 0, 1, 'C');
$pdf->SetDrawColor(180, 83, 9);
$pdf->SetLineWidth(0.8);
$pdf->Line(80, 118, 217, 118);

// 5. COURSE DETAILS
$pdf->SetY(130);
$pdf->SetFont('Arial', '', 14);
$pdf->SetTextColor(31, 41, 55);
$pdf->Cell(0, 10, 'has successfully satisfied all requirements for the professional course in', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 24);
$pdf->SetTextColor(30, 64, 175);
$pdf->Cell(0, 15, $course_title, 0, 1, 'C');

// 6. SIGNATURE, SEAL & QR SECTION
// SIGNATURE IMAGE (Director)
if (file_exists('signature.png')) {
    // Corrected to place your blue signature on the line
    $pdf->Image('signature.png', 60, 165, 40, 25); 
}

// QR CODE (QuickChart)
$qr_api = "https://quickchart.io/qr?text=" . urlencode($verify_url) . "&size=200";
$pdf->Image($qr_api, 136, 158, 25, 25, 'PNG');

// Lines for Sign/Date
$pdf->SetDrawColor(180, 83, 9);
$pdf->SetLineWidth(0.4);
$pdf->Line(50, 185, 110, 185); 
$pdf->Line(190, 185, 250, 185);

// Labels
$pdf->SetXY(50, 187);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(15, 23, 42);
$pdf->Cell(60, 5, 'ACADEMY DIRECTOR', 0, 0, 'C');

$pdf->SetXY(190, 187);
$pdf->Cell(60, 10, 'DATE: ' . date('M d, Y'), 0, 0, 'C');

// 7. VERIFICATION FOOTER
$pdf->SetY(198);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(30, 64, 175);
$pdf->Cell(0, 5, "Verification ID: $cert_id | Verify validity at portal.smartchoiceacademy.com", 0, 0, 'C');

// --- FINAL OUTPUT ---
if (ob_get_length()) ob_end_clean();
$pdf->Output('D', 'SCA_Diploma_' . $user_id . '.pdf'); 
exit;