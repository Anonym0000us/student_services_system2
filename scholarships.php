<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";

// Connect to the database  
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarships</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: 'Arial', sans-serif; background: #f8f9fa; }
        .container { margin-top: 40px; }
        .scholarship-card {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .scholarship-card:hover { transform: scale(1.02); }
        .btn-apply { width: 100%; }
    </style>
</head>
<body>
    
<?php include('student_header.php'); ?> <!-- Include the Student Header -->

<div class="container">
    <h2 class="text-center mb-4">📢 Available Scholarships</h2>
    
    <!-- Search Bar -->
    <input type="text" id="searchScholarship" class="form-control mb-3" placeholder="🔍 Search for a scholarship...">

    <div class="row">
        <?php
        $sql = "SELECT * FROM scholarships ORDER BY deadline ASC";
        $result = $conn->query($sql);

        // Check if the query was successful
        if (!$result) {
            die("Query failed: " . $conn->error);
        }

        while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="scholarship-card">
                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                    <p><strong>📅 Deadline:</strong> <?= date("F j, Y", strtotime($row['deadline'])) ?></p>

                    <!-- Apply Button (Triggers Modal) -->
                    <button class="btn btn-primary btn-apply mt-2 apply-btn" data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>">📩 Apply Now</button>

                    <!-- View Details Button -->
                    <button class="btn btn-info btn-sm mt-2 view-details" data-id="<?= $row['id'] ?>">📜 View Details</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Scholarship Details Modal -->
<div class="modal fade" id="scholarshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scholarshipTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scholarshipDetails"></div>
        </div>
    </div>
</div>

<!-- Apply Confirmation Modal -->
<div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to apply for <strong id="applyScholarshipName"></strong>?</p>
                <input type="hidden" id="applyScholarshipId">
                <div id="applyMessage" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApplyBtn">Yes, Apply</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Search functionality
    $("#searchScholarship").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".scholarship-card").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // View Details - AJAX Request
    $(".view-details").click(function() {
        var id = $(this).data("id");
        $.ajax({
            url: "get_scholarship.php",
            type: "GET",
            data: { id: id },
            success: function(response) {
                var data = JSON.parse(response);
                $("#scholarshipTitle").text(data.name);
                $("#scholarshipDetails").html(`
                    <p><strong>📄 Description:</strong> ${data.description}</p>
                    <p><strong>🎓 Eligibility:</strong> ${data.eligibility}</p>
                `);
                $("#scholarshipModal").modal("show");
            }
        });
    });

    // Open Apply Confirmation Modal
    $(".apply-btn").click(function() {
        var scholarshipId = $(this).data("id");
        var scholarshipName = $(this).data("name");

        $("#applyScholarshipId").val(scholarshipId);
        $("#applyScholarshipName").text(scholarshipName);
        $("#applyMessage").html("");
        $("#applyModal").modal("show");
    });

    // Confirm Application (AJAX Request)
    $("#confirmApplyBtn").click(function() {
        var scholarshipId = $("#applyScholarshipId").val();
        
        $.ajax({
            url: "apply_scholarship.php",
            type: "POST",
            data: { scholarship_id: scholarshipId },
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    $("#applyMessage").html('<div class="alert alert-success">✅ ' + response.message + '</div>');
                } else {
                    $("#applyMessage").html('<div class="alert alert-danger">❌ ' + response.message + '</div>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $("#applyMessage").html('<div class="alert alert-danger">❌ An unexpected error occurred. Please try again. (' + textStatus + ': ' + errorThrown + ')</div>');
            }
        });
    });

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>