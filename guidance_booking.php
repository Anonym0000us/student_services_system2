<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Student') { header('Location: login.php'); exit; }
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Book Guidance Appointment</title>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'student_header.php'; ?>
<div class="container py-4">
  <h3>Book an Appointment</h3>
  <div class="row g-3">
    <div class="col-md-3">
      <label class="form-label">Counselor</label>
      <select class="form-select" id="counselor_id">
        <?php
        $res=$conn->query("SELECT user_id AS id, TRIM(CONCAT(first_name,' ',last_name)) AS name FROM users WHERE role IN ('Guidance Admin','Counselor') AND status='Active' ORDER BY name");
        while($c=$res->fetch_assoc()){ echo '<option value="'.htmlspecialchars($c['id']).'">'.htmlspecialchars($c['name']).'</option>'; }
        ?>
      </select>
      <div class="mt-3">
        <label class="form-label">Reason (optional)</label>
        <textarea class="form-control" id="reason" rows="3" placeholder="Briefly describe your concern..."></textarea>
      </div>
      <div class="mt-3 small text-muted">Select a green-free slot on the calendar to request a booking. Standard session length is 60 minutes.</div>
    </div>
    <div class="col-md-9">
      <div id="calendar"></div>
    </div>
  </div>
</div>
<script>
let calendar;
document.addEventListener('DOMContentLoaded', function() {
  const el=document.getElementById('calendar');
  calendar=new FullCalendar.Calendar(el,{
    initialView:'timeGridWeek', nowIndicator:true, selectable:true, height:'auto',
    businessHours: { daysOfWeek:[1,2,3,4,5], startTime:'08:00', endTime:'17:00' },
    events: (info, success) => {
      const cid=document.getElementById('counselor_id').value;
      fetch(`guidance_calendar_events.php?counselor_id=${encodeURIComponent(cid)}&start=${encodeURIComponent(info.startStr)}&end=${encodeURIComponent(info.endStr)}`)
        .then(r=>r.json()).then(success);
    },
    select: (sel)=>{
      const cid=document.getElementById('counselor_id').value;
      const reason=document.getElementById('reason').value.trim();
      if(!cid){ alert('Please select a counselor.'); return; }
      if(sel.start < new Date()){ alert('Please choose a future time.'); return; }
      if(!confirm(`Request ${sel.start.toLocaleString()} with selected counselor?`)) return;
      const body=new URLSearchParams({ counselor_id: cid, start: sel.start.toISOString(), reason, csrf_token: '<?= htmlspecialchars($_SESSION['csrf_token']) ?>' });
      fetch('book_guidance_appointment.php',{ method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })
        .then(r=>r.json()).then(d=>{ alert(d.message||'Done'); if(d.success){ calendar.refetchEvents(); } });
    }
  });
  calendar.render();
  document.getElementById('counselor_id').addEventListener('change', ()=>calendar.refetchEvents());
});
</script>
</body>
</html>