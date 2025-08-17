<?php
require 'config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Student') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']); exit; }

$counselor_id = $_POST['counselor_id'] ?? '';
$start = $_POST['start'] ?? '';
$reason = trim($_POST['reason'] ?? '');
if ($counselor_id === '' || $start === '') { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Missing data']); exit; }

try { $dt=new DateTime($start); } catch(Exception $e){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid date']); exit; }
if ($dt < new DateTime()) { echo json_encode(['success'=>false,'message'=>'Time must be in the future']); exit; }
$startStr = $dt->format('Y-m-d H:i:s');
$endStr = $dt->modify('+1 hour')->format('Y-m-d H:i:s');

// Check for conflicts for counselor
$chk=$conn->prepare("SELECT COUNT(*) AS c FROM appointments WHERE user_id=? AND appointment_date BETWEEN ? AND DATE_SUB(?, INTERVAL 1 SECOND)");
$chk->bind_param('sss', $counselor_id, $startStr, $endStr);
$chk->execute(); $c=$chk->get_result()->fetch_assoc()['c'] ?? 0;
if ($c > 0) { echo json_encode(['success'=>false,'message'=>'Slot is already booked.']); exit; }

// Insert
$stmt=$conn->prepare("INSERT INTO appointments (student_id, user_id, appointment_date, reason, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param('isss', $_SESSION['user_id'], $counselor_id, $startStr, $reason);
$ok=$stmt->execute();
echo json_encode(['success'=>$ok, 'message'=>$ok?'Appointment requested.':'Failed to create appointment']);