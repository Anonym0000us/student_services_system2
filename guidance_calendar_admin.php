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
<style>
  /* Ensure the calendar uses full width inside main-content */
  .container-fluid { max-width: 100%; }
  #calendar { min-height: calc(100vh - 160px); }
</style>
</head>
<body>
<?php include 'guidance_admin_header.php'; ?>
<div class="main-content">
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Appointments Calendar</h3>
      <button class="btn btn-primary" id="btnNew" data-bs-toggle="modal" data-bs-target="#newApptModal">New Appointment</button>
    </div>
    <div id="calendar"></div>
  </div>
</div>

<!-- New Appointment Modal -->
<div class="modal fade" id="newApptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Appointment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="newApptForm">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label class="form-label">Student</label>
            <select class="form-select" name="student_id" required>
              <?php
              $students = $conn->query("SELECT user_id, TRIM(CONCAT(first_name,' ',last_name)) AS name FROM users WHERE role='Student' AND status='Active' ORDER BY name LIMIT 200");
              while($s = $students->fetch_assoc()){
                echo '<option value="'.htmlspecialchars($s['user_id']).'">'.htmlspecialchars($s['name']).' ('.htmlspecialchars($s['user_id']).')</option>';
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Counselor</label>
            <select class="form-select" name="counselor_id" required>
              <?php
              $counselors = $conn->query("SELECT user_id, TRIM(CONCAT(first_name,' ',last_name)) AS name FROM users WHERE role IN ('Guidance Admin','Counselor') AND status='Active' ORDER BY name");
              while($c = $counselors->fetch_assoc()){
                $sel = ($c['user_id'] === $_SESSION['user_id']) ? ' selected' : '';
                echo '<option value="'.htmlspecialchars($c['user_id']).'"'.$sel.'>'.htmlspecialchars($c['name']).' ('.htmlspecialchars($c['user_id']).')</option>';
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Date & Time</label>
            <input type="datetime-local" class="form-control" name="datetime" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason (optional)</label>
            <textarea class="form-control" name="reason" rows="2" placeholder="Reason or notes..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveAppt">Create</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const calEl = document.getElementById('calendar');
  const cal=new FullCalendar.Calendar(calEl,{
    initialView:'timeGridWeek', editable:true, eventOverlap:false, height:'auto',
    events:'guidance_calendar_admin_events.php',
    eventDrop:(info)=>update(info), eventResize:(info)=>update(info)
  });
  function update(info){
    const p=new URLSearchParams({ id: info.event.id, start: info.event.start.toISOString(), csrf_token: '<?= $_SESSION['csrf_token'] ?>' });
    fetch('admin_update_appointment.php',{ method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: p })
      .then(r=>r.json()).then(d=>{ if(!d.success){ alert(d.message||'Update failed'); info.revert(); } else { cal.refetchEvents(); } });
  }
  cal.render();
  // Resize calendar when window changes size to fit screen
  window.addEventListener('resize', ()=> cal.updateSize());

  // New appointment modal
  // Modal is toggled via data attributes; keep reference if needed
  const modalEl = document.getElementById('newApptModal');
  const modal = new bootstrap.Modal(modalEl);
  document.getElementById('saveAppt').addEventListener('click', ()=>{
    const form = document.getElementById('newApptForm');
    const data = new FormData(form);
    const datetime = form.elements['datetime'].value;
    if(!datetime){ alert('Please select date & time'); return; }
    const body = new URLSearchParams();
    for (const [k,v] of data.entries()) body.append(k, v);
    fetch('admin_create_appointment.php',{ method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })
      .then(r=>r.json()).then(d=>{
        alert(d.message||'Saved');
        if(d.success){ modal.hide(); cal.refetchEvents(); }
      }).catch(()=> alert('Network error'));
  });
});
</script>
</body>
</html>