<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id']) && isset($_POST['answers'])) {
    $course_id = (int)$_POST['course_id'];
    $answers = json_decode($_POST['answers'], true);
    
    // Simple quiz logic - in a real app, you'd have questions stored in DB
    $correct_answers = [
        'q1' => 'a',
        'q2' => 'b',
        'q3' => 'c'
    ];
    
    $score = 0;
    $total = count($correct_answers);
    
    foreach ($answers as $question => $answer) {
        if (isset($correct_answers[$question]) && $correct_answers[$question] == $answer) {
            $score++;
        }
    }
    
    $percentage = round(($score / $total) * 100);
    
    echo json_encode([
        'success' => true, 
        'score' => $score, 
        'total' => $total, 
        'percentage' => $percentage,
        'message' => "You scored $score out of $total ($percentage%)"
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
