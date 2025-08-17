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
      <div class="mt-3 small text-muted">Click a time slot on the calendar to request a 60-minute session.</div>
    </div>
    <div class="col-md-9">
      <div id="calendar"></div>
    </div>
  </div>
</div>
<script>
let calendar;
function requestBooking(dateObj){
  const cid=document.getElementById('counselor_id').value;
  const reason=document.getElementById('reason').value.trim();
  if(!cid){ alert('Please select a counselor.'); return; }
  if(dateObj < new Date()){ alert('Please choose a future time.'); return; }
  if(!confirm(`Request ${dateObj.toLocaleString()} with selected counselor?`)) return;
  const body=new URLSearchParams({ counselor_id: cid, start: dateObj.toISOString(), reason, csrf_token: '<?= htmlspecialchars($_SESSION['csrf_token']) ?>' });
  fetch('book_guidance_appointment.php',{ method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })
    .then(r=>r.json()).then(d=>{ alert(d.message||'Done'); if(d.success){ calendar.refetchEvents(); } })
    .catch(()=> alert('Network error'));
}

document.addEventListener('DOMContentLoaded', function() {
  const el=document.getElementById('calendar');
  calendar=new FullCalendar.Calendar(el,{
    initialView:'timeGridWeek', nowIndicator:true, selectable:true, height:'auto',
    slotDuration:'00:30:00', snapDuration:'00:30:00',
    events: (info, success) => {
      const cid=document.getElementById('counselor_id').value;
      fetch(`guidance_calendar_events.php?counselor_id=${encodeURIComponent(cid)}&start=${encodeURIComponent(info.startStr)}&end=${encodeURIComponent(info.endStr)}`)
        .then(r=>r.json()).then(success).catch(()=>success([]));
    },
    select: (sel)=>{ requestBooking(sel.start); },
    dateClick: (info)=>{ requestBooking(info.date); }
  });
  calendar.render();
  document.getElementById('counselor_id').addEventListener('change', ()=>calendar.refetchEvents());
});
</script>
</body>
</html>