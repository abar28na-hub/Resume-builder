
<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? 'save';
$resume_id = isset($_POST['resume_id']) ? (int)$_POST['resume_id'] : 0;

// Collect all fields
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$age = trim($_POST['age'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$address = trim($_POST['address'] ?? '');
$objective = trim($_POST['objective'] ?? '');
$current_job_title = trim($_POST['current_job_title'] ?? '');
$years_of_experience = trim($_POST['years_of_experience'] ?? '');
$experience = trim($_POST['experience'] ?? '');

// Education fields
$college_name = trim($_POST['college_name'] ?? '');
$college_start_year = trim($_POST['college_start_year'] ?? '');
$college_end_year = trim($_POST['college_end_year'] ?? '');
$college_cgpa = trim($_POST['college_cgpa'] ?? '');
$school_10th_name = trim($_POST['school_10th_name'] ?? '');
$school_10th_year = trim($_POST['school_10th_year'] ?? '');
$school_10th_percentage = trim($_POST['school_10th_percentage'] ?? '');
$school_12th_name = trim($_POST['school_12th_name'] ?? '');
$school_12th_year = trim($_POST['school_12th_year'] ?? '');
$school_12th_percentage = trim($_POST['school_12th_percentage'] ?? '');

$skills = trim($_POST['skills'] ?? '');
$certifications = trim($_POST['certifications'] ?? '');
$projects = trim($_POST['projects'] ?? '');
$achievements = trim($_POST['achievements'] ?? '');
$languages = trim($_POST['languages'] ?? '');
$linkedin_url = trim($_POST['linkedin_url'] ?? '');
$portfolio_url = trim($_POST['portfolio_url'] ?? '');

// Validation
if ($full_name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?status=error');
    exit;
}

$ageValue = ($age !== '' && is_numeric($age)) ? (int)$age : null;
$yoeValue = ($years_of_experience !== '' && is_numeric($years_of_experience)) ? (float)$years_of_experience : null;

// Build education string
$education = '';
if ($college_name) {
    $education .= "🎓 College: $college_name\n";
    if ($college_start_year && $college_end_year) {
        $education .= "   Year: $college_start_year - $college_end_year\n";
    }
    if ($college_cgpa) {
        $education .= "   CGPA/Percentage: $college_cgpa\n";
    }
}
if ($school_10th_name) {
    $education .= "\n🏫 10th: $school_10th_name\n";
    if ($school_10th_year) $education .= "   Year: $school_10th_year\n";
    if ($school_10th_percentage) $education .= "   Percentage: $school_10th_percentage\n";
}
if ($school_12th_name) {
    $education .= "\n🏫 12th: $school_12th_name\n";
    if ($school_12th_year) $education .= "   Year: $school_12th_year\n";
    if ($school_12th_percentage) $education .= "   Percentage: $school_12th_percentage\n";
}

// Check if update or insert
if ($resume_id > 0) {
    // UPDATE
    $stmt = $mysql->prepare("UPDATE resumes SET 
        full_name=?, email=?, phone=?, age=?, gender=?, address=?, objective=?, 
        current_job_title=?, years_of_experience=?, education=?, experience=?, 
        skills=?, projects=?, certifications=?, achievements=?, languages=?, 
        linkedin_url=?, portfolio_url=? 
        WHERE id=?");
    
    $stmt->bind_param(
        'sssissssdsssssssssi',
        $full_name, $email, $phone, $ageValue, $gender, $address, $objective,
        $current_job_title, $yoeValue, $education, $experience,
        $skills, $projects, $certifications, $achievements, $languages,
        $linkedin_url, $portfolio_url, $resume_id
    );
} else {
    // INSERT
    $stmt = $mysql->prepare("INSERT INTO resumes 
        (full_name, email, phone, age, gender, address, objective, current_job_title, 
         years_of_experience, education, experience, skills, projects, certifications, 
         achievements, languages, linkedin_url, portfolio_url) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        'sssissssdsssssssss',
        $full_name, $email, $phone, $ageValue, $gender, $address, $objective,
        $current_job_title, $yoeValue, $education, $experience,
        $skills, $projects, $certifications, $achievements, $languages,
        $linkedin_url, $portfolio_url
    );
}

if ($stmt->execute()) {
    header('Location: index.php?status=saved');
} else {
    error_log("Database Error: " . $stmt->error);
    header('Location: index.php?status=error');
}

$stmt->close();
$mysql->close();
exit;
?>