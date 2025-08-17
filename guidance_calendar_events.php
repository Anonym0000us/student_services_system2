<?php
require 'config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

$counselor_id = $_GET['counselor_id'] ?? '';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
if ($counselor_id === '' || $start === '' || $end === '') { echo json_encode([]); exit; }

$stmt=$conn->prepare("SELECT id, appointment_date, status FROM appointments WHERE user_id = ? AND appointment_date >= ? AND appointment_date < ?");
$stmt->bind_param('sss', $counselor_id, $start, $end);
$stmt->execute();
$res=$stmt->get_result();
$events=[];
while($r=$res->fetch_assoc()){
  $color = strtolower($r['status'])==='approved' ? '#28a745' : (strtolower($r['status'])==='pending' ? '#ffc107' : '#6c757d');
  $startIso = date('c', strtotime($r['appointment_date']));
  $endIso = date('c', strtotime($r['appointment_date'].' +1 hour'));
  $events[] = [ 'id'=>$r['id'], 'title'=>$r['status'], 'start'=>$startIso, 'end'=>$endIso, 'color'=>$color ];
}
echo json_encode($events);