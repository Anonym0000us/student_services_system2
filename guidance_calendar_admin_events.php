<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) { http_response_code(403); exit; }
header('Content-Type: application/json; charset=utf-8');

$res=$conn->query("SELECT id, appointment_date, status, student_id FROM appointments WHERE appointment_date IS NOT NULL");
$events=[];
while($r=$res->fetch_assoc()){
  $color = strtolower($r['status'])==='approved' ? '#28a745' : (strtolower($r['status'])==='pending' ? '#ffc107' : (strtolower($r['status'])==='completed' ? '#0d6efd' : '#6c757d'));
  $title = 'Student #'.$r['student_id'].' ('.$r['status'].')';
  $startIso = date('c', strtotime($r['appointment_date']));
  $endIso = date('c', strtotime($r['appointment_date'].' +1 hour'));
  $events[]=['id'=>$r['id'],'title'=>$title,'start'=>$startIso,'end'=>$endIso,'color'=>$color];
}
echo json_encode($events);