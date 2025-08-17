<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) { header('Location: login.php'); exit; }
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Guidance Calendar</title>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'guidance_admin_header.php'; ?>
<div class="main-content">
  <div class="container py-4">
    <h3>Appointments Calendar</h3>
    <div id="calendar"></div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const cal=new FullCalendar.Calendar(document.getElementById('calendar'),{
    initialView:'timeGridWeek', editable:true, eventOverlap:false, height:'auto',
    events:'guidance_calendar_admin_events.php',
    eventDrop:(info)=>update(info), eventResize:(info)=>update(info)
  });
  function update(info){
    const p=new URLSearchParams({ id: info.event.id, start: info.event.start.toISOString(), csrf_token: '<?= $_SESSION['csrf_token'] ?>' });
    fetch('admin_update_appointment.php',{ method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: p })
      .then(r=>r.json()).then(d=>{ if(!d.success){ alert(d.message||'Update failed'); info.revert(); } });
  }
  cal.render();
});
</script>
</body>
</html>