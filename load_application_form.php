<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['scholarship_id'])) {
    echo '<div class="alert alert-danger">Access denied or missing scholarship information.</div>';
    exit();
}

$user_id = $_SESSION['user_id'];
$scholarship_id = intval($_GET['scholarship_id']);

// Get scholarship details
$scholarship_sql = "SELECT * FROM scholarships WHERE id = ? AND status = 'active'";
$scholarship_stmt = $conn->prepare($scholarship_sql);
$scholarship_stmt->bind_param("i", $scholarship_id);
$scholarship_stmt->execute();
$scholarship_result = $scholarship_stmt->get_result();
$scholarship = $scholarship_result->fetch_assoc();
$scholarship_stmt->close();

if (!$scholarship) {
    echo '<div class="alert alert-danger">Scholarship not found or inactive.</div>';
    exit();
}

// Get user details
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Check if already applied
$check_sql = "SELECT * FROM scholarship_applications WHERE scholarship_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("is", $scholarship_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $existing_application = $check_result->fetch_assoc();
    echo '<div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Application Status</h5>
            <p>You have already applied for this scholarship.</p>
            <p><strong>Status:</strong> <span class="badge bg-' . 
            ($existing_application['status'] == 'approved' ? 'success' : 
             ($existing_application['status'] == 'rejected' ? 'danger' : 
              ($existing_application['status'] == 'under_review' ? 'warning' : 'secondary'))) . 
            '">' . ucfirst($existing_application['status']) . '</span></p>
            <p><strong>Application Date:</strong> ' . date("F j, Y", strtotime($existing_application['application_date'])) . '</p>';
    
    if ($existing_application['status'] == 'rejected' && !empty($existing_application['rejection_reason'])) {
        echo '<p><strong>Rejection Reason:</strong> ' . htmlspecialchars($existing_application['rejection_reason']) . '</p>';
    }
    
    echo '</div>';
    exit();
}
$check_stmt->close();

// Parse required documents
$required_documents = [];
if (!empty($scholarship['documents_required'])) {
    $documents = json_decode($scholarship['documents_required'], true);
    if (is_array($documents)) {
        $required_documents = $documents;
    } else {
        $required_documents = [$scholarship['documents_required']];
    }
}
?>

<style>
:root {
    --neust-blue: #003366;
    --neust-light-blue: #00509E;
    --neust-gold: #FFD700;
    --neust-white: #FFFFFF;
    --neust-gray: #F8F9FA;
}

.stepper {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}

.stepper::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    background: var(--neust-white);
    border: 2px solid #e9ecef;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: #6c757d;
    transition: all 0.3s ease;
}

.step.active {
    border-color: var(--neust-blue);
    background: var(--neust-blue);
    color: var(--neust-white);
}

.step.completed {
    border-color: var(--neust-gold);
    background: var(--neust-gold);
    color: var(--neust-blue);
}

.step-label {
    text-align: center;
    margin-top: 10px;
    font-size: 0.9rem;
    color: #6c757d;
}

.step.active .step-label {
    color: var(--neust-blue);
    font-weight: 600;
}

.step-content {
    display: none;
}

.step-content.active {
    display: block;
}

.form-section {
    background: var(--neust-white);
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.form-section h5 {
    color: var(--neust-blue);
    border-bottom: 2px solid var(--neust-gold);
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.form-control:focus {
    border-color: var(--neust-blue);
    box-shadow: 0 0 0 0.2rem rgba(0, 83, 158, 0.25);
}

.btn-primary {
    background: var(--neust-blue);
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: var(--neust-light-blue);
    transform: scale(1.02);
}

.btn-success {
    background: #28a745;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background: #218838;
    transform: scale(1.02);
}

.document-upload {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.document-upload:hover {
    border-color: var(--neust-blue);
    background: rgba(0, 83, 158, 0.05);
}

.document-upload.dragover {
    border-color: var(--neust-gold);
    background: rgba(255, 215, 0, 0.1);
}

.upload-icon {
    font-size: 3rem;
    color: #6c757d;
    margin-bottom: 15px;
}

.file-preview {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
    border-left: 4px solid var(--neust-blue);
}

.file-preview .file-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.file-preview .file-name {
    font-weight: 600;
    color: var(--neust-blue);
}

.file-preview .file-size {
    color: #6c757d;
    font-size: 0.9rem;
}

.remove-file {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-file:hover {
    background: #c82333;
    transform: scale(1.1);
}

.progress-bar {
    background: var(--neust-gold);
}

.scholarship-summary {
    background: linear-gradient(135deg, var(--neust-blue), var(--neust-light-blue));
    color: var(--neust-white);
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
}

.scholarship-summary h6 {
    color: var(--neust-gold);
    margin-bottom: 15px;
}

.eligibility-check {
    background: #e8f5e8;
    border: 1px solid #c3e6c3;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
}

.eligibility-check.eligible {
    background: #d4edda;
    border-color: #c3e6c3;
}

.eligibility-check.not-eligible {
    background: #f8d7da;
    border-color: #f5c6cb;
}
</style>

<!-- Application Form Stepper -->
<div class="stepper">
    <div class="step active" data-step="1">
        <span>1</span>
        <div class="step-label">Personal Info</div>
    </div>
    <div class="step" data-step="2">
        <span>2</span>
        <div class="step-label">Documents</div>
    </div>
    <div class="step" data-step="3">
        <span>3</span>
        <div class="step-label">Review</div>
    </div>
    <div class="step" data-step="4">
        <span>4</span>
        <div class="step-label">Submit</div>
    </div>
</div>

<!-- Progress Bar -->
<div class="progress mb-4" style="height: 8px;">
    <div class="progress-bar" role="progressbar" style="width: 25%;" id="progressBar"></div>
</div>

<!-- Step 1: Personal Information -->
<div class="step-content active" id="step1">
    <div class="form-section">
        <h5><i class="fas fa-user"></i> Personal Information</h5>
        
        <div class="scholarship-summary">
            <h6><i class="fas fa-graduation-cap"></i> Scholarship Details</h6>
            <p><strong><?= htmlspecialchars($scholarship['name']) ?></strong></p>
            <p><strong>Type:</strong> <?= htmlspecialchars($scholarship['type']) ?></p>
            <p><strong>Amount:</strong> ₱<?= number_format($scholarship['amount'], 2) ?></p>
            <p><strong>Deadline:</strong> <?= date("F j, Y", strtotime($scholarship['deadline'])) ?></p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Student ID</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['user_id']) ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']) ?>" readonly>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Course</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['course'] ?? 'Not specified') ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Year Level</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['year_level'] ?? 'Not specified') ?>" readonly>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? 'Not specified') ?>" readonly>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">GPA (Grade Point Average)</label>
                    <input type="number" class="form-control" id="gpa" name="gpa" step="0.01" min="1.0" max="4.0" 
                           value="<?= htmlspecialchars($user['gpa'] ?? '') ?>" required>
                    <div class="form-text">Enter your current GPA (1.0 - 4.0)</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Family Income (Monthly)</label>
                    <input type="number" class="form-control" id="family_income" name="family_income" 
                           value="<?= htmlspecialchars($user['family_income'] ?? '') ?>" required>
                    <div class="form-text">Enter your family's monthly income in pesos</div>
                </div>
            </div>
        </div>
        
        <div class="eligibility-check" id="eligibilityCheck">
            <h6><i class="fas fa-check-circle"></i> Eligibility Check</h6>
            <div id="eligibilityStatus">Checking eligibility...</div>
        </div>
    </div>
    
    <div class="text-end">
        <button type="button" class="btn btn-primary" onclick="nextStep()">
            Next <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

<!-- Step 2: Document Upload -->
<div class="step-content" id="step2">
    <div class="form-section">
        <h5><i class="fas fa-file-upload"></i> Required Documents</h5>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Important:</strong> Please upload all required documents in PDF, JPG, or PNG format. 
            Maximum file size: 5MB per file.
        </div>
        
        <div id="documentUploads">
            <?php foreach ($required_documents as $index => $document): ?>
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-file-alt"></i> <?= htmlspecialchars($document) ?>
                    <span class="text-danger">*</span>
                </label>
                <div class="document-upload" data-document="<?= htmlspecialchars($document) ?>" data-index="<?= $index ?>">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <p class="mb-2">Click to upload or drag and drop</p>
                    <p class="text-muted small">PDF, JPG, PNG up to 5MB</p>
                    <input type="file" class="d-none" accept=".pdf,.jpg,.jpeg,.png" 
                           data-document="<?= htmlspecialchars($document) ?>" data-index="<?= $index ?>">
                </div>
                <div class="file-preview d-none" id="preview-<?= $index ?>">
                    <div class="file-info">
                        <div>
                            <div class="file-name" id="fileName-<?= $index ?>"></div>
                            <div class="file-size" id="fileSize-<?= $index ?>"></div>
                        </div>
                        <button type="button" class="remove-file" onclick="removeFile(<?= $index ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Note:</strong> All documents must be clear, legible, and up-to-date. 
            Incomplete applications will not be processed.
        </div>
    </div>
    
    <div class="text-end">
        <button type="button" class="btn btn-secondary me-2" onclick="prevStep()">
            <i class="fas fa-arrow-left"></i> Previous
        </button>
        <button type="button" class="btn btn-primary" onclick="nextStep()">
            Next <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

<!-- Step 3: Review -->
<div class="step-content" id="step3">
    <div class="form-section">
        <h5><i class="fas fa-eye"></i> Review Application</h5>
        
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">Personal Information</h6>
                <table class="table table-borderless">
                    <tr><td><strong>Student ID:</strong></td><td id="reviewStudentId"></td></tr>
                    <tr><td><strong>Name:</strong></td><td id="reviewName"></td></tr>
                    <tr><td><strong>Course:</strong></td><td id="reviewCourse"></td></tr>
                    <tr><td><strong>Year Level:</strong></td><td id="reviewYearLevel"></td></tr>
                    <tr><td><strong>GPA:</strong></td><td id="reviewGPA"></td></tr>
                    <tr><td><strong>Family Income:</strong></td><td id="reviewFamilyIncome"></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Documents</h6>
                <div id="reviewDocuments"></div>
            </div>
        </div>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Please review all information carefully before submitting. You cannot edit this application after submission.
        </div>
    </div>
    
    <div class="text-end">
        <button type="button" class="btn btn-secondary me-2" onclick="prevStep()">
            <i class="fas fa-arrow-left"></i> Previous
        </button>
        <button type="button" class="btn btn-primary" onclick="nextStep()">
            Next <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

<!-- Step 4: Submit -->
<div class="step-content" id="step4">
    <div class="form-section">
        <h5><i class="fas fa-paper-plane"></i> Submit Application</h5>
        
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <strong>Ready to Submit!</strong> Your application has been reviewed and is ready for submission.
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <h6 class="text-primary">Final Application Summary</h6>
                <div id="finalSummary"></div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                        <h6>Scholarship Application</h6>
                        <p class="text-muted"><?= htmlspecialchars($scholarship['name']) ?></p>
                        <div class="d-grid">
                            <button type="button" class="btn btn-success" onclick="submitApplication()">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-end">
        <button type="button" class="btn btn-secondary me-2" onclick="prevStep()">
            <i class="fas fa-arrow-left"></i> Previous
        </button>
    </div>
</div>

<script>
let currentStep = 1;
let uploadedFiles = {};

// Initialize document uploads
$(document).ready(function() {
    initializeDocumentUploads();
    checkEligibility();
});

function initializeDocumentUploads() {
    $('.document-upload').click(function() {
        $(this).find('input[type="file"]').click();
    });
    
    $('.document-upload').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    $('.document-upload').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    $('.document-upload').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFileUpload(files[0], $(this));
        }
    });
    
    $('input[type="file"]').change(function(e) {
        if (e.target.files.length > 0) {
            handleFileUpload(e.target.files[0], $(this).closest('.document-upload'));
        }
    });
}

function handleFileUpload(file, uploadElement) {
    const documentType = uploadElement.data('document');
    const index = uploadElement.data('index');
    
    // Validate file type
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        alert('Please upload only PDF, JPG, or PNG files.');
        return;
    }
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB.');
        return;
    }
    
    // Store file information
    uploadedFiles[index] = {
        file: file,
        documentType: documentType,
        name: file.name,
        size: file.size,
        type: file.type
    };
    
    // Show preview
    showFilePreview(index, file.name, file.size);
    
    // Update upload area
    uploadElement.find('.upload-icon i').removeClass('fa-cloud-upload-alt').addClass('fa-check-circle text-success');
    uploadElement.find('p').first().text('File uploaded successfully!');
}

function showFilePreview(index, fileName, fileSize) {
    const preview = $(`#preview-${index}`);
    $(`#fileName-${index}`).text(fileName);
    $(`#fileSize-${index}`).text(formatFileSize(fileSize));
    preview.removeClass('d-none');
}

function removeFile(index) {
    delete uploadedFiles[index];
    const preview = $(`#preview-${index}`);
    const uploadElement = preview.siblings('.document-upload');
    
    preview.addClass('d-none');
    uploadElement.find('.upload-icon i').removeClass('fa-check-circle text-success').addClass('fa-cloud-upload-alt');
    uploadElement.find('p').first().text('Click to upload or drag and drop');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function checkEligibility() {
    const gpa = parseFloat($('#gpa').val()) || 0;
    const familyIncome = parseFloat($('#family_income').val()) || 0;
    
    let eligible = true;
    let reasons = [];
    
    // Check GPA requirement
    if (gpa < 1.75) {
        eligible = false;
        reasons.push('GPA must be 1.75 or higher');
    }
    
    // Check if deadline has passed
    const deadline = new Date('<?= $scholarship['deadline'] ?>');
    const now = new Date();
    if (deadline < now) {
        eligible = false;
        reasons.push('Application deadline has passed');
    }
    
    const eligibilityDiv = $('#eligibilityStatus');
    const eligibilityCheck = $('#eligibilityCheck');
    
    if (eligible) {
        eligibilityCheck.removeClass('not-eligible').addClass('eligible');
        eligibilityDiv.html('<span class="text-success"><i class="fas fa-check-circle"></i> You are eligible for this scholarship!</span>');
    } else {
        eligibilityCheck.removeClass('eligible').addClass('not-eligible');
        eligibilityDiv.html('<span class="text-danger"><i class="fas fa-times-circle"></i> You are not eligible for the following reasons:</span><ul class="mt-2 mb-0"><li>' + reasons.join('</li><li>') + '</li></ul>');
    }
}

function nextStep() {
    if (currentStep < 4) {
        if (validateCurrentStep()) {
            currentStep++;
            updateStepper();
            updateProgress();
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepper();
        updateProgress();
    }
}

function validateCurrentStep() {
    switch(currentStep) {
        case 1:
            const gpa = $('#gpa').val();
            const familyIncome = $('#family_income').val();
            
            if (!gpa || !familyIncome) {
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (parseFloat(gpa) < 1.0 || parseFloat(gpa) > 4.0) {
                alert('Please enter a valid GPA between 1.0 and 4.0.');
                return false;
            }
            
            checkEligibility();
            return true;
            
        case 2:
            const requiredDocs = <?= json_encode($required_documents) ?>;
            const uploadedCount = Object.keys(uploadedFiles).length;
            
            if (uploadedCount < requiredDocs.length) {
                alert('Please upload all required documents.');
                return false;
            }
            
            // Prepare review data
            prepareReviewData();
            return true;
            
        case 3:
            return true;
            
        default:
            return true;
    }
}

function prepareReviewData() {
    // Personal info
    $('#reviewStudentId').text('<?= htmlspecialchars($user['user_id']) ?>');
    $('#reviewName').text('<?= htmlspecialchars($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']) ?>');
    $('#reviewCourse').text('<?= htmlspecialchars($user['course'] ?? 'Not specified') ?>');
    $('#reviewYearLevel').text('<?= htmlspecialchars($user['year_level'] ?? 'Not specified') ?>');
    $('#reviewGPA').text($('#gpa').val());
    $('#reviewFamilyIncome').text('₱' + $('#family_income').val());
    
    // Documents
    let documentsHtml = '';
    Object.keys(uploadedFiles).forEach(index => {
        const file = uploadedFiles[index];
        documentsHtml += `<div class="mb-2">
            <i class="fas fa-file-alt text-primary"></i> 
            <strong>${file.documentType}:</strong> ${file.name}
        </div>`;
    });
    $('#reviewDocuments').html(documentsHtml);
    
    // Final summary
    $('#finalSummary').html(`
        <div class="alert alert-success">
            <h6><i class="fas fa-check-circle"></i> Application Complete</h6>
            <p>All required information and documents have been provided.</p>
        </div>
        <div class="row">
            <div class="col-md-6">
                <strong>Scholarship:</strong> <?= htmlspecialchars($scholarship['name']) ?><br>
                <strong>Type:</strong> <?= htmlspecialchars($scholarship['type']) ?><br>
                <strong>Amount:</strong> ₱<?= number_format($scholarship['amount'], 2) ?>
            </div>
            <div class="col-md-6">
                <strong>Documents:</strong> ${Object.keys(uploadedFiles).length} uploaded<br>
                <strong>Application Date:</strong> ${new Date().toLocaleDateString()}<br>
                <strong>Status:</strong> Ready to submit
            </div>
        </div>
    `);
}

function updateStepper() {
    $('.step').removeClass('active completed');
    
    for (let i = 1; i <= 4; i++) {
        if (i < currentStep) {
            $(`.step[data-step="${i}"]`).addClass('completed');
        } else if (i === currentStep) {
            $(`.step[data-step="${i}"]`).addClass('active');
        }
    }
    
    $('.step-content').removeClass('active');
    $(`#step${currentStep}`).addClass('active');
}

function updateProgress() {
    const progress = (currentStep / 4) * 100;
    $('#progressBar').css('width', progress + '%');
}

function submitApplication() {
    if (Object.keys(uploadedFiles).length === 0) {
        alert('Please upload all required documents before submitting.');
        return;
    }
    
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('scholarship_id', <?= $scholarship_id ?>);
    formData.append('gpa', $('#gpa').val());
    formData.append('family_income', $('#family_income').val());
    
    // Append files
    Object.keys(uploadedFiles).forEach(index => {
        const file = uploadedFiles[index];
        formData.append(`documents[${index}]`, file.file);
        formData.append(`document_types[${index}]`, file.documentType);
    });
    
    // Show loading state
    const submitBtn = $('button[onclick="submitApplication()"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
    submitBtn.prop('disabled', true);
    
    // Submit application
    $.ajax({
        url: 'submit_enhanced_application.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    showSuccessMessage(result.message);
                } else {
                    showErrorMessage(result.message);
                }
            } catch (e) {
                showErrorMessage('An unexpected error occurred. Please try again.');
            }
        },
        error: function() {
            showErrorMessage('Network error. Please check your connection and try again.');
        },
        complete: function() {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    });
}

function showSuccessMessage(message) {
    $('#applicationForm').html(`
        <div class="text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            <h3 class="text-success mt-3">Application Submitted Successfully!</h3>
            <p class="lead">${message}</p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>What happens next?</strong><br>
                Your application will be reviewed by the scholarship committee. 
                You will receive notifications about the status of your application.
            </div>
            <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                <i class="fas fa-home"></i> Return to Scholarships
            </button>
        </div>
    `);
}

function showErrorMessage(message) {
    $('#applicationForm').html(`
        <div class="text-center py-5">
            <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
            <h3 class="text-danger mt-3">Application Submission Failed</h3>
            <p class="lead">${message}</p>
            <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                <i class="fas fa-redo"></i> Try Again
            </button>
        </div>
    `);
}

// Real-time validation
$('#gpa, #family_income').on('input', function() {
    if (currentStep === 1) {
        checkEligibility();
    }
});
</script>