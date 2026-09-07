<?php
require 'config.php';

$status = $_GET['status'] ?? '';
$resume_id = $_GET['id'] ?? 0;
$saved_data = null;

if ($resume_id > 0) {
    $stmt = $mysql->prepare("SELECT * FROM resumes WHERE id = ?");
    $stmt->bind_param("i", $resume_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $saved_data = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resume Builder</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: #ffffff;
        min-height: 100vh;
        padding: 24px;
    }
    
    .container {
        max-width: 1440px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px;
        position: relative;
        z-index: 1;
    }
    
    @media (max-width: 1024px) {
        .container { grid-template-columns: 1fr; gap: 20px; }
        body { padding: 16px; }
    }
    
    .form-section, .preview-section {
        background: #ffffff;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 
            0 4px 24px rgba(0, 0, 0, 0.06),
            0 1px 2px rgba(0, 0, 0, 0.04);
        border: 1px solid #e8edf2;
        max-height: 92vh;
        overflow-y: auto;
        transition: all 0.3s ease;
    }
    
    .form-section:hover, .preview-section:hover {
        box-shadow: 
            0 8px 40px rgba(0, 0, 0, 0.08),
            0 1px 2px rgba(0, 0, 0, 0.04);
    }
    
    .form-section::-webkit-scrollbar,
    .preview-section::-webkit-scrollbar {
        width: 5px;
    }
    .form-section::-webkit-scrollbar-track,
    .preview-section::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .form-section::-webkit-scrollbar-thumb,
    .preview-section::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #10b981, #2563eb);
        border-radius: 10px;
    }
    
    /* Header */
    .app-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 6px;
    }
    .app-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #10b981, #2563eb);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #fff;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
    }
    .form-title {
        font-size: 24px;
        font-weight: 800;
        background: linear-gradient(135deg, #0f1724, #1a3a5c);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.02em;
    }
    .form-subtitle {
        color: #94a3b8;
        font-size: 14px;
        font-weight: 400;
        margin-left: 58px;
        margin-top: -4px;
    }
    
    .section-heading {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
        margin: 28px 0 14px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .section-heading .badge {
        background: linear-gradient(135deg, #10b981, #2563eb);
        color: #fff;
        font-size: 9px;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 600;
        letter-spacing: 0.04em;
    }
    
    .add-btn {
        background: linear-gradient(135deg, #10b981, #2563eb);
        color: #fff;
        border: none;
        padding: 5px 16px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 12px rgba(16, 185, 129, 0.2);
    }
    .add-btn:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 24px rgba(16, 185, 129, 0.3);
    }
    
    .remove-btn {
        background: #f1f5f9;
        color: #94a3b8;
        border: none;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .remove-btn:hover {
        background: #fee2e2;
        color: #ef4444;
    }
    
    label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-top: 14px;
        letter-spacing: 0.01em;
    }
    label .required {
        color: #ef4444;
        margin-left: 2px;
    }
    label .hint {
        font-weight: 400;
        color: #94a3b8;
        font-size: 11px;
    }
    
    input, textarea, select {
        width: 100%;
        padding: 10px 14px;
        margin-top: 4px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        background: #fafbfc;
        color: #1e293b;
        transition: all 0.3s ease;
    }
    input:focus, textarea:focus, select:focus {
        outline: none;
        border-color: #10b981;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.08);
        transform: translateY(-1px);
    }
    input::placeholder, textarea::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }
    select {
        background: #fafbfc;
    }
    select option {
        background: #ffffff;
        color: #1e293b;
    }
    textarea {
        resize: vertical;
        min-height: 56px;
        line-height: 1.5;
    }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .three-col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
    
    .dynamic-item {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px;
        margin-top: 12px;
        position: relative;
        transition: all 0.3s ease;
    }
    .dynamic-item:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }
    .dynamic-item .remove-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 3px 12px;
    }
    .dynamic-item label {
        margin-top: 6px;
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
    }
    .dynamic-item input, .dynamic-item textarea {
        background: #ffffff;
        border-color: #e2e8f0;
    }
    .dynamic-item input:focus, .dynamic-item textarea:focus {
        border-color: #10b981;
    }
    
    .btn-group {
        display: flex;
        gap: 12px;
        margin-top: 28px;
        flex-wrap: wrap;
    }
    .btn {
        padding: 14px 32px;
        border: none;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        flex: 1;
        min-width: 150px;
        font-family: 'Inter', sans-serif;
        letter-spacing: 0.01em;
    }
    .btn-primary {
        background: #1e293b;
        color: #fff;
        box-shadow: 0 2px 12px rgba(30, 41, 59, 0.15);
    }
    .btn-primary:hover {
        background: #0f172a;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(30, 41, 59, 0.25);
    }
    .btn-success {
        background: linear-gradient(135deg, #10b981, #2563eb);
        color: #fff;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.25);
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(16, 185, 129, 0.35);
    }
    
    .msg {
        padding: 14px 20px;
        border-radius: 14px;
        margin-bottom: 18px;
        font-size: 13px;
        font-weight: 500;
        animation: slideDown 0.4s ease;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .msg.success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .msg.success a {
        color: #065f46;
        font-weight: 700;
        text-decoration: underline;
    }
    .msg.error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    /* Preview Styles */
    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f1f5f9;
    }
    .preview-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .preview-status {
        font-size: 11px;
        color: #94a3b8;
        background: #f1f5f9;
        padding: 4px 14px;
        border-radius: 20px;
        font-weight: 500;
    }
    
    /* PDF Download Button */
    .pdf-btn {
        background: linear-gradient(135deg, #dc2626, #ef4444);
        color: #fff;
        border: none;
        padding: 5px 16px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 12px rgba(220, 38, 38, 0.2);
    }
    .pdf-btn:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 24px rgba(220, 38, 38, 0.3);
    }
    
    .resume-preview {
        padding: 8px 0;
    }
    .resume-name {
        font-size: 30px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.02em;
        background: linear-gradient(135deg, #0f1724, #1a3a5c);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .resume-title {
        font-size: 15px;
        color: #475569;
        margin: 4px 0 16px;
        font-weight: 500;
    }
    .resume-contact {
        font-size: 12px;
        color: #475569;
        padding: 12px 0;
        border-top: 1.5px solid #e2e8f0;
        border-bottom: 1.5px solid #e2e8f0;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px 20px;
        background: #f8fafc;
        padding: 10px 14px;
        border-radius: 10px;
    }
    .resume-section {
        margin-bottom: 18px;
    }
    .resume-section h3 {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #2563eb;
        margin: 0 0 6px 0;
        padding-bottom: 4px;
        border-bottom: 2px solid #e2e8f0;
    }
    .resume-section p {
        font-size: 13px;
        color: #334155;
        line-height: 1.7;
        margin: 0;
    }
    .resume-section ul {
        margin: 0;
        padding-left: 18px;
    }
    .resume-section ul li {
        font-size: 13px;
        color: #334155;
        line-height: 1.7;
        margin-bottom: 2px;
    }
    .resume-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .resume-tag {
        background: linear-gradient(135deg, #d1fae5, #dbeafe);
        color: #0f1724;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 14px;
        border-radius: 20px;
        transition: all 0.3s ease;
        border: 1px solid rgba(16, 185, 129, 0.1);
    }
    .resume-tag:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
    }
    .empty-preview {
        color: #94a3b8;
        text-align: center;
        padding: 60px 20px;
    }
    .empty-preview .icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    .empty-preview h3 {
        color: #475569;
        font-size: 18px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .empty-preview p {
        font-size: 14px;
        max-width: 340px;
        margin: 0 auto;
        line-height: 1.6;
        color: #94a3b8;
    }
    
    .load-section {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 2px solid #f1f5f9;
    }
    .load-section label {
        margin-top: 0;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
    }
    .load-row {
        display: flex;
        gap: 10px;
        margin-top: 8px;
    }
    .load-row select {
        flex: 1;
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 13px;
        background: #fafbfc;
        border: 2px solid #e2e8f0;
        color: #1e293b;
    }
    .load-row select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.08);
    }
    .load-row select option {
        background: #ffffff;
    }
    .load-row .btn {
        flex: 0 0 auto;
        padding: 10px 20px;
        min-width: auto;
        font-size: 13px;
        border-radius: 12px;
    }
    .btn-outline {
        background: #f1f5f9;
        color: #1e293b;
        border: 2px solid #e2e8f0;
        box-shadow: none;
    }
    .btn-outline:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
    }
    .btn-danger {
        background: #fee2e2;
        color: #dc2626;
        border: 2px solid #fecaca;
    }
    .btn-danger:hover {
        background: #fecaca;
        transform: translateY(-2px);
    }
    
    @media (max-width: 640px) {
        .form-section, .preview-section {
            padding: 20px;
        }
        .two-col, .three-col {
            grid-template-columns: 1fr;
        }
        .btn {
            min-width: 100%;
        }
        .load-row {
            flex-wrap: wrap;
        }
        .load-row .btn {
            flex: 1;
            min-width: 80px;
        }
        .app-icon {
            width: 36px;
            height: 36px;
            font-size: 18px;
        }
        .form-title {
            font-size: 20px;
        }
        .form-subtitle {
            margin-left: 0;
            font-size: 13px;
        }
        .resume-name {
            font-size: 24px;
        }
        .preview-header {
            flex-wrap: wrap;
            gap: 8px;
        }
    }
</style>
</head>
<body>

<div class="container">
    <!-- FORM (LEFT) -->
    <div class="form-section">
        <div class="app-header">
            <div class="app-icon">📄</div>
            <h1 class="form-title">Resume Builder</h1>
        </div>
        <p class="form-subtitle">Build your professional resume</p>
        
        <?php if ($status === 'saved' && $resume_id): ?>
            <div class="msg success">
                ✅ Your resume has been saved!
                <a href="resume.php?id=<?= $resume_id ?>" target="_blank">View Resume →</a>
            </div>
        <?php endif; ?>
        <?php if ($status === 'error'): ?>
            <div class="msg error">❌ Something went wrong. Please try again.</div>
        <?php endif; ?>
        
        <form id="resumeForm" action="submit.php" method="POST">
            <input type="hidden" name="resume_id" value="<?= $resume_id ?>">
            
            <!-- PERSONAL -->
            <div class="section-heading">
                <span>Personal</span>
                <span class="badge">Required</span>
            </div>
            
            <label>Full Name <span class="required">*</span></label>
            <input type="text" name="full_name" required placeholder="e.g. Olivia Sanchez"
                   value="<?= htmlspecialchars($saved_data['full_name'] ?? '') ?>">
            
            <label>Professional Title <span class="required">*</span></label>
            <input type="text" name="current_job_title" required placeholder="e.g. Administrative Manager"
                   value="<?= htmlspecialchars($saved_data['current_job_title'] ?? '') ?>">
            
            <div class="two-col">
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="+1 234-567-890"
                           value="<?= htmlspecialchars($saved_data['phone'] ?? '') ?>">
                </div>
                <div>
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" required placeholder="you@example.com"
                           value="<?= htmlspecialchars($saved_data['email'] ?? '') ?>">
                </div>
            </div>
            
            <label>Address</label>
            <textarea name="address" rows="2" placeholder="123 Anywhere St, Any City"><?= htmlspecialchars($saved_data['address'] ?? '') ?></textarea>
            
            <div class="two-col">
                <div>
                    <label>Age</label>
                    <input type="number" name="age" min="15" max="80" placeholder="25"
                           value="<?= htmlspecialchars($saved_data['age'] ?? '') ?>">
                </div>
                <div>
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">Select</option>
                        <option value="Female" <?= (isset($saved_data['gender']) && $saved_data['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                        <option value="Male" <?= (isset($saved_data['gender']) && $saved_data['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                        <option value="Other" <?= (isset($saved_data['gender']) && $saved_data['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <!-- SUMMARY -->
            <div class="section-heading">
                <span>Summary</span>
                <span class="badge">Optional</span>
            </div>
            <label>Professional Summary</label>
            <textarea name="objective" rows="3" placeholder="2-3 sentence overview of your experience and strengths..."><?= htmlspecialchars($saved_data['objective'] ?? '') ?></textarea>
            
            <!-- WORK EXPERIENCE -->
            <div class="section-heading">
                <span>Work Experience</span>
                <span class="badge">Optional</span>
            </div>
            <label>Years of Experience</label>
            <input type="number" step="0.5" name="years_of_experience" placeholder="e.g. 3.5"
                   value="<?= htmlspecialchars($saved_data['years_of_experience'] ?? '') ?>">
            <label>Experience Details</label>
            <textarea name="experience" rows="4" placeholder="Company, role, duration, responsibilities..."><?= htmlspecialchars($saved_data['experience'] ?? '') ?></textarea>
            
            <!-- EDUCATION -->
            <div class="section-heading">
                <span>Education</span>
                <span class="badge">Optional</span>
            </div>
            
            <label>College / University Name</label>
            <input type="text" name="college_name" placeholder="e.g. Anna University"
                   value="<?= htmlspecialchars($saved_data['college_name'] ?? '') ?>">
            
            <label>Course / Degree</label>
            <input type="text" name="college_course" placeholder="e.g. B.E Computer Science"
                   value="<?= htmlspecialchars($saved_data['college_course'] ?? '') ?>">
            
            <label>CGPA / Percentage</label>
            <input type="text" name="college_cgpa" placeholder="e.g. 8.5 CGPA"
                   value="<?= htmlspecialchars($saved_data['college_cgpa'] ?? '') ?>">
            
            <label>12th School Name</label>
            <input type="text" name="school_12th_name" placeholder="e.g. XYZ Higher Secondary School"
                   value="<?= htmlspecialchars($saved_data['school_12th_name'] ?? '') ?>">
            
            <label>12th Course / Group</label>
            <input type="text" name="school_12th_course" placeholder="e.g. Computer Science"
                   value="<?= htmlspecialchars($saved_data['school_12th_course'] ?? '') ?>">
            
            <label>12th Percentage</label>
            <input type="text" name="school_12th_percentage" placeholder="78%"
                   value="<?= htmlspecialchars($saved_data['school_12th_percentage'] ?? '') ?>">
            
            <!-- INTERNSHIPS -->
            <div class="section-heading">
                <span>Internships</span>
                <button type="button" class="add-btn" onclick="addInternship()">+ Add Internship</button>
            </div>
            <div id="internshipsContainer">
                <?php
                if ($saved_data && $saved_data['internships']) {
                    $internships = explode("\n\n", $saved_data['internships']);
                    foreach ($internships as $index => $internship) {
                        if (trim($internship)) {
                            $parts = explode("\n", $internship);
                            echo '<div class="dynamic-item">';
                            echo '<button type="button" class="remove-btn" onclick="removeItem(this)">✕</button>';
                            echo '<div class="two-col">';
                            echo '<div><label>Company Name</label><input type="text" name="internship_company[]" value="' . htmlspecialchars($parts[0] ?? '') . '" placeholder="e.g. Google"></div>';
                            echo '<div><label>Role / Position</label><input type="text" name="internship_role[]" value="' . htmlspecialchars($parts[1] ?? '') . '" placeholder="e.g. Data Analyst Intern"></div>';
                            echo '</div>';
                            echo '<div class="two-col">';
                            echo '<div><label>Duration</label><input type="text" name="internship_duration[]" value="' . htmlspecialchars($parts[2] ?? '') . '" placeholder="e.g. June 2023 - Aug 2023"></div>';
                            echo '<div><label>Location</label><input type="text" name="internship_location[]" value="' . htmlspecialchars($parts[3] ?? '') . '" placeholder="e.g. Chennai"></div>';
                            echo '</div>';
                            echo '<label>Description</label>';
                            echo '<textarea name="internship_desc[]" rows="2" placeholder="Key responsibilities...">' . htmlspecialchars($parts[4] ?? '') . '</textarea>';
                            echo '</div>';
                        }
                    }
                }
                ?>
            </div>
            
            <!-- SKILLS -->
            <div class="section-heading">
                <span>Skills</span>
                <span class="badge">Optional</span>
            </div>
            <label>Skills <span class="hint">(comma separated)</span></label>
            <input type="text" name="skills" placeholder="e.g. Python, SQL, Communication"
                   value="<?= htmlspecialchars($saved_data['skills'] ?? '') ?>">
            
            <!-- PROJECTS -->
            <div class="section-heading">
                <span>Projects</span>
                <button type="button" class="add-btn" onclick="addProject()">+ Add Project</button>
            </div>
            <div id="projectsContainer">
                <?php
                if ($saved_data && $saved_data['projects']) {
                    $projects = explode("\n", $saved_data['projects']);
                    foreach ($projects as $index => $project) {
                        if (trim($project)) {
                            echo '<div class="dynamic-item">';
                            echo '<button type="button" class="remove-btn" onclick="removeItem(this)">✕</button>';
                            echo '<label>Project ' . ($index + 1) . '</label>';
                            echo '<input type="text" name="projects[]" value="' . htmlspecialchars(trim($project)) . '" placeholder="Project name - short description">';
                            echo '</div>';
                        }
                    }
                }
                ?>
            </div>
            
            <!-- LANGUAGES -->
            <div class="section-heading">
                <span>Languages Known</span>
                <button type="button" class="add-btn" onclick="addLanguage()">+ Add Language</button>
            </div>
            <div id="languagesContainer">
                <?php
                if ($saved_data && $saved_data['languages']) {
                    $langs = explode(',', $saved_data['languages']);
                    foreach ($langs as $lang) {
                        if (trim($lang)) {
                            $lang_parts = explode('(', $lang);
                            $lang_name = trim($lang_parts[0]);
                            $lang_level = isset($lang_parts[1]) ? str_replace(')', '', trim($lang_parts[1])) : '';
                            echo '<div class="dynamic-item" style="display:flex;gap:10px;align-items:center;">';
                            echo '<input type="text" name="languages[]" value="' . htmlspecialchars($lang_name) . '" placeholder="e.g. Tamil" style="flex:1;">';
                            echo '<select name="language_level[]" style="width:120px;">';
                            echo '<option value="">Level</option>';
                            echo '<option value="Native" ' . ($lang_level === 'Native' ? 'selected' : '') . '>Native</option>';
                            echo '<option value="Fluent" ' . ($lang_level === 'Fluent' ? 'selected' : '') . '>Fluent</option>';
                            echo '<option value="Intermediate" ' . ($lang_level === 'Intermediate' ? 'selected' : '') . '>Intermediate</option>';
                            echo '<option value="Beginner" ' . ($lang_level === 'Beginner' ? 'selected' : '') . '>Beginner</option>';
                            echo '</select>';
                            echo '<button type="button" class="remove-btn" onclick="removeItem(this)" style="margin:0;">✕</button>';
                            echo '</div>';
                        }
                    }
                }
                ?>
            </div>
            
            <!-- CERTIFICATIONS -->
            <div class="section-heading">
                <span>Certifications</span>
                <button type="button" class="add-btn" onclick="addCertification()">+ Add Certification</button>
            </div>
            <div id="certificationsContainer">
                <?php
                if ($saved_data && $saved_data['certifications']) {
                    $certs = explode("\n", $saved_data['certifications']);
                    foreach ($certs as $cert) {
                        if (trim($cert)) {
                            echo '<div class="dynamic-item">';
                            echo '<button type="button" class="remove-btn" onclick="removeItem(this)">✕</button>';
                            echo '<label>Certification</label>';
                            echo '<input type="text" name="certifications[]" value="' . htmlspecialchars(trim($cert)) . '" placeholder="e.g. Google Data Analytics Certificate (2024)">';
                            echo '</div>';
                        }
                    }
                }
                ?>
            </div>
            
            <!-- HOBBIES -->
            <div class="section-heading">
                <span>Hobbies & Interests</span>
                <button type="button" class="add-btn" onclick="addHobby()">+ Add</button>
            </div>
            <div id="hobbiesContainer">
                <?php
                if ($saved_data && $saved_data['achievements']) {
                    $hobbies = explode(',', $saved_data['achievements']);
                    foreach ($hobbies as $hobby) {
                        if (trim($hobby)) {
                            echo '<div class="dynamic-item" style="display:flex;gap:10px;align-items:center;">';
                            echo '<input type="text" name="hobbies[]" value="' . htmlspecialchars(trim($hobby)) . '" placeholder="e.g. Photography" style="flex:1;">';
                            echo '<button type="button" class="remove-btn" onclick="removeItem(this)" style="margin:0;">✕</button>';
                            echo '</div>';
                        }
                    }
                }
                ?>
            </div>
            
            <!-- LINKS -->
            <div class="section-heading">
                <span>Links</span>
                <span class="badge">Optional</span>
            </div>
            <label>LinkedIn</label>
            <input type="url" name="linkedin_url" placeholder="https://linkedin.com/in/yourname"
                   value="<?= htmlspecialchars($saved_data['linkedin_url'] ?? '') ?>">
            <label>Portfolio / GitHub</label>
            <input type="url" name="portfolio_url" placeholder="https://github.com/yourname"
                   value="<?= htmlspecialchars($saved_data['portfolio_url'] ?? '') ?>">
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">💾 Save Resume</button>
                <button type="submit" class="btn btn-success">⚡ Generate & Save</button>
            </div>
        </form>
        
        <!-- LOAD SECTION -->
        <div class="load-section">
            <label>Load Saved Resume</label>
            <div class="load-row">
                <select id="loadSelect">
                    <option value="">-- Select a saved resume --</option>
                    <?php
                    $list = $mysql->query("SELECT id, full_name, email FROM resumes ORDER BY created_at DESC");
                    while ($row = $list->fetch_assoc()):
                    ?>
                    <option value="<?= $row['id'] ?>" <?= ($resume_id == $row['id']) ? 'selected' : '' ?>>
                        #<?= $row['id'] ?> - <?= htmlspecialchars($row['full_name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <button onclick="loadResume()" class="btn btn-primary" style="flex:0;padding:10px 20px;">📂 Load</button>
                <button onclick="newResume()" class="btn btn-outline" style="flex:0;padding:10px 20px;">✨ New</button>
            </div>
        </div>
    </div>
    
    <!-- PREVIEW (RIGHT) -->
    <div class="preview-section">
        <div class="preview-header">
            <span class="preview-title">👁️ Live Preview</span>
            <div style="display:flex;gap:8px;align-items:center;">
                <button onclick="downloadPDF()" class="pdf-btn">📥 PDF</button>
                <span class="preview-status" id="previewStatus">Unsaved</span>
            </div>
        </div>
        <div class="resume-preview" id="resumePreview">
            <div class="empty-preview">
                <div class="icon">📋</div>
                <h3>Your resume will appear here</h3>
                <p>Fill in the form to see a live preview of your professional resume.</p>
            </div>
        </div>
    </div>
</div>

<script>
// ===== DYNAMIC FUNCTIONS =====

function addInternship() {
    const container = document.getElementById('internshipsContainer');
    const div = document.createElement('div');
    div.className = 'dynamic-item';
    div.innerHTML = `
        <button type="button" class="remove-btn" onclick="removeItem(this)">✕</button>
        <div class="two-col">
            <div><label>Company Name</label><input type="text" name="internship_company[]" placeholder="e.g. Google"></div>
            <div><label>Role / Position</label><input type="text" name="internship_role[]" placeholder="e.g. Data Analyst Intern"></div>
        </div>
        <div class="two-col">
            <div><label>Duration</label><input type="text" name="internship_duration[]" placeholder="e.g. June 2023 - Aug 2023"></div>
            <div><label>Location</label><input type="text" name="internship_location[]" placeholder="e.g. Chennai"></div>
        </div>
        <label>Description</label>
        <textarea name="internship_desc[]" rows="2" placeholder="Key responsibilities..."></textarea>
    `;
    container.appendChild(div);
    updatePreview();
}

function addProject() {
    const container = document.getElementById('projectsContainer');
    const count = container.children.length + 1;
    const div = document.createElement('div');
    div.className = 'dynamic-item';
    div.innerHTML = `
        <button type="button" class="remove-btn" onclick="removeItem(this)">✕</button>
        <label>Project ${count}</label>
        <input type="text" name="projects[]" placeholder="Project name - short description">
    `;
    container.appendChild(div);
    updatePreview();
}

function addLanguage() {
    const container = document.getElementById('languagesContainer');
    const div = document.createElement('div');
    div.className = 'dynamic-item';
    div.style.display = 'flex';
    div.style.gap = '10px';
    div.style.alignItems = 'center';
    div.innerHTML = `
        <input type="text" name="languages[]" placeholder="e.g. Tamil" style="flex:1;">
        <select name="language_level[]" style="width:120px;">
            <option value="">Level</option>
            <option value="Native">Native</option>
            <option value="Fluent">Fluent</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Beginner">Beginner</option>
        </select>
        <button type="button" class="remove-btn" onclick="removeItem(this)" style="margin:0;">✕</button>
    `;
    container.appendChild(div);
    updatePreview();
}

function addCertification() {
    const container = document.getElementById('certificationsContainer');
    const div = document.createElement('div');
    div.className = 'dynamic-item';
    div.innerHTML = `
        <button type="button" class="remove-btn" onclick="removeItem(this)">✕</button>
        <label>Certification</label>
        <input type="text" name="certifications[]" placeholder="e.g. Google Data Analytics Certificate (2024)">
    `;
    container.appendChild(div);
    updatePreview();
}

function addHobby() {
    const container = document.getElementById('hobbiesContainer');
    const div = document.createElement('div');
    div.className = 'dynamic-item';
    div.style.display = 'flex';
    div.style.gap = '10px';
    div.style.alignItems = 'center';
    div.innerHTML = `
        <input type="text" name="hobbies[]" placeholder="e.g. Photography" style="flex:1;">
        <button type="button" class="remove-btn" onclick="removeItem(this)" style="margin:0;">✕</button>
    `;
    container.appendChild(div);
    updatePreview();
}

function removeItem(btn) {
    btn.parentElement.remove();
    updatePreview();
}

// ===== LIVE PREVIEW =====
function updatePreview() {
    const form = document.getElementById('resumeForm');
    const formData = new FormData(form);
    
    const internships = formData.getAll('internship_company[]').filter(c => c.trim());
    const internshipRoles = formData.getAll('internship_role[]');
    const internshipDurations = formData.getAll('internship_duration[]');
    const internshipLocations = formData.getAll('internship_location[]');
    const internshipDescs = formData.getAll('internship_desc[]');
    
    const projects = formData.getAll('projects[]').filter(p => p.trim());
    const languages = formData.getAll('languages[]').filter(l => l.trim());
    const languageLevels = formData.getAll('language_level[]');
    const certifications = formData.getAll('certifications[]').filter(c => c.trim());
    const hobbies = formData.getAll('hobbies[]').filter(h => h.trim());
    
    const data = {
        full_name: formData.get('full_name') || '',
        current_job_title: formData.get('current_job_title') || '',
        phone: formData.get('phone') || '',
        email: formData.get('email') || '',
        address: formData.get('address') || '',
        objective: formData.get('objective') || '',
        years_of_experience: formData.get('years_of_experience') || '',
        experience: formData.get('experience') || '',
        college_name: formData.get('college_name') || '',
        college_course: formData.get('college_course') || '',
        college_cgpa: formData.get('college_cgpa') || '',
        school_12th_name: formData.get('school_12th_name') || '',
        school_12th_course: formData.get('school_12th_course') || '',
        school_12th_percentage: formData.get('school_12th_percentage') || '',
        skills: formData.get('skills') || '',
        linkedin_url: formData.get('linkedin_url') || '',
        portfolio_url: formData.get('portfolio_url') || '',
        internships: internships.map((company, i) => ({
            company: company,
            role: internshipRoles[i] || '',
            duration: internshipDurations[i] || '',
            location: internshipLocations[i] || '',
            description: internshipDescs[i] || ''
        })),
        projects: projects,
        languages: languages.map((lang, i) => ({
            name: lang,
            level: languageLevels[i] || ''
        })),
        certifications: certifications,
        hobbies: hobbies
    };
    
    let html = '';
    
    html += `<div class="resume-name">${data.full_name || 'Your Name'}</div>`;
    html += `<div class="resume-title">${data.current_job_title || 'Professional Title'}</div>`;
    
    let contacts = [];
    if (data.address) contacts.push(data.address.replace(/\n/g, ', '));
    if (data.phone) contacts.push(data.phone);
    if (data.email) contacts.push(data.email);
    if (data.linkedin_url) contacts.push(data.linkedin_url);
    if (data.portfolio_url) contacts.push(data.portfolio_url);
    if (contacts.length) {
        html += `<div class="resume-contact">${contacts.join(' | ')}</div>`;
    }
    
    if (data.objective) {
        html += `<div class="resume-section"><h3>Profile</h3><p>${data.objective.replace(/\n/g, '<br>')}</p></div>`;
    }
    
    if (data.experience) {
        html += `<div class="resume-section"><h3>Employment History</h3><p>${data.experience.replace(/\n/g, '<br>')}</p>`;
        if (data.years_of_experience) {
            html += `<p style="font-size:12px;color:#94a3b8;margin-top:6px;">${data.years_of_experience} years total experience</p>`;
        }
        html += `</div>`;
    }
    
    let eduHTML = '';
    if (data.college_name) {
        eduHTML += `🎓 ${data.college_name}`;
        if (data.college_course) eduHTML += ` - ${data.college_course}`;
        if (data.college_cgpa) eduHTML += ` - ${data.college_cgpa}`;
        eduHTML += '\n';
    }
    if (data.school_12th_name) {
        eduHTML += `🏫 12th: ${data.school_12th_name}`;
        if (data.school_12th_course) eduHTML += ` - ${data.school_12th_course}`;
        if (data.school_12th_percentage) eduHTML += ` - ${data.school_12th_percentage}`;
        eduHTML += '\n';
    }
    if (eduHTML) {
        html += `<div class="resume-section"><h3>Education</h3><p>${eduHTML.replace(/\n/g, '<br>')}</p></div>`;
    }
    
    if (data.internships.length) {
        html += `<div class="resume-section"><h3>Internships</h3><ul>`;
        data.internships.forEach(intern => {
            let text = `${intern.company} - ${intern.role}`;
            if (intern.duration) text += ` (${intern.duration})`;
            if (intern.location) text += ` - ${intern.location}`;
            if (intern.description) text += `: ${intern.description}`;
            html += `<li>${text}</li>`;
        });
        html += `</ul></div>`;
    }
    
    if (data.skills) {
        const skills = data.skills.split(',').map(s => s.trim()).filter(s => s);
        if (skills.length) {
            html += `<div class="resume-section"><h3>Skills</h3><div class="resume-tags">`;
            skills.forEach(s => { html += `<span class="resume-tag">${s}</span>`; });
            html += `</div></div>`;
        }
    }
    
    if (data.projects.length) {
        html += `<div class="resume-section"><h3>Projects</h3><ul>`;
        data.projects.forEach(p => { html += `<li>${p}</li>`; });
        html += `</ul></div>`;
    }
    
    if (data.languages.length) {
        html += `<div class="resume-section"><h3>Languages Known</h3><ul>`;
        data.languages.forEach(l => {
            let text = l.name;
            if (l.level) text += ` (${l.level})`;
            html += `<li>${text}</li>`;
        });
        html += `</ul></div>`;
    }
    
    if (data.certifications.length) {
        html += `<div class="resume-section"><h3>Certifications</h3><ul>`;
        data.certifications.forEach(c => { html += `<li>${c}</li>`; });
        html += `</ul></div>`;
    }
    
    if (data.hobbies.length) {
        html += `<div class="resume-section"><h3>Hobbies & Interests</h3><div class="resume-tags">`;
        data.hobbies.forEach(h => { html += `<span class="resume-tag">${h}</span>`; });
        html += `</div></div>`;
    }
    
    if (!html) {
        html = `<div class="empty-preview">
            <div class="icon">📋</div>
            <h3>Your resume will appear here</h3>
            <p>Fill in the form to see a live preview of your professional resume.</p>
        </div>`;
    }
    
    document.getElementById('resumePreview').innerHTML = html;
    document.getElementById('previewStatus').textContent = '🔄 Unsaved';
    document.getElementById('previewStatus').style.color = '#f59e0b';
}

// ===== LOAD FUNCTIONS =====
function loadResume() {
    const select = document.getElementById('loadSelect');
    const id = select.value;
    if (id) {
        window.location.href = `?id=${id}`;
    }
}

function newResume() {
    window.location.href = 'index.php';
}

// ===== DOWNLOAD PDF =====
function downloadPDF() {
    const element = document.getElementById('resumePreview');
    const previewName = document.querySelector('.resume-name')?.innerText || 'Resume';
    
    // Find the button
    const btn = document.querySelector('.pdf-btn');
    const originalText = btn?.innerText || '📥 PDF';
    if (btn) btn.innerText = '⏳ Generating...';
    
    html2canvas(element, {
        scale: 2,
        backgroundColor: '#ffffff',
        allowTaint: true,
        useCORS: true,
        logging: false,
    }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
        
        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        pdf.save(`${previewName}_Resume.pdf`);
        
        if (btn) btn.innerText = originalText;
    }).catch(error => {
        console.error('PDF Error:', error);
        alert('Error generating PDF. Please try again.');
        if (btn) btn.innerText = originalText;
    });
}

// ===== ATTACH EVENTS =====
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#resumeForm input, #resumeForm textarea, #resumeForm select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });
});
</script>
</body>
</html>