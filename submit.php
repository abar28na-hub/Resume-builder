<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Collect all fields
$full_name           = trim($_POST['full_name'] ?? '');
$email               = trim($_POST['email'] ?? '');
$phone               = trim($_POST['phone'] ?? '');
$age                 = trim($_POST['age'] ?? '');
$gender              = trim($_POST['gender'] ?? '');
$address             = trim($_POST['address'] ?? '');
$objective           = trim($_POST['objective'] ?? '');
$current_job_title   = trim($_POST['current_job_title'] ?? '');
$years_of_experience = trim($_POST['years_of_experience'] ?? '');
$experience          = trim($_POST['experience'] ?? '');

// ✅ Education fields (No 10th)
$college_name           = trim($_POST['college_name'] ?? '');
$college_course         = trim($_POST['college_course'] ?? '');
$college_cgpa           = trim($_POST['college_cgpa'] ?? '');
$school_12th_name       = trim($_POST['school_12th_name'] ?? '');
$school_12th_course     = trim($_POST['school_12th_course'] ?? '');
$school_12th_percentage = trim($_POST['school_12th_percentage'] ?? '');

$skills               = trim($_POST['skills'] ?? '');
$linkedin_url         = trim($_POST['linkedin_url'] ?? '');
$portfolio_url        = trim($_POST['portfolio_url'] ?? '');

// Dynamic fields
$internship_companies = $_POST['internship_company'] ?? [];
$internship_roles = $_POST['internship_role'] ?? [];
$internship_durations = $_POST['internship_duration'] ?? [];
$internship_locations = $_POST['internship_location'] ?? [];
$internship_descs = $_POST['internship_desc'] ?? [];

$projects = $_POST['projects'] ?? [];
$languages = $_POST['languages'] ?? [];
$language_levels = $_POST['language_level'] ?? [];
$certifications = $_POST['certifications'] ?? [];
$hobbies = $_POST['hobbies'] ?? [];

$resume_id = isset($_POST['resume_id']) ? (int)$_POST['resume_id'] : 0;

// Validation
if ($full_name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $current_job_title === '') {
    header('Location: index.php?status=error');
    exit;
}

$ageValue = ($age !== '' && is_numeric($age)) ? (int)$age : null;
$yoeValue = ($years_of_experience !== '' && is_numeric($years_of_experience)) ? (float)$years_of_experience : null;

// ✅ Build education text (No 10th, No Years)
$education = '';
if ($college_name) {
    $education .= "🎓 $college_name";
    if ($college_course) $education .= " - $college_course";
    if ($college_cgpa) $education .= " - $college_cgpa";
    $education .= "\n";
}
if ($school_12th_name) {
    $education .= "🏫 12th: $school_12th_name";
    if ($school_12th_course) $education .= " - $school_12th_course";
    if ($school_12th_percentage) $education .= " - $school_12th_percentage";
    $education .= "\n";
}

// Build internships string
$internships_str = '';
foreach ($internship_companies as $i => $company) {
    if (trim($company)) {
        $intern_line = trim($company);
        if (!empty($internship_roles[$i])) $intern_line .= "\n" . trim($internship_roles[$i]);
        if (!empty($internship_durations[$i])) $intern_line .= "\n" . trim($internship_durations[$i]);
        if (!empty($internship_locations[$i])) $intern_line .= "\n" . trim($internship_locations[$i]);
        if (!empty($internship_descs[$i])) $intern_line .= "\n" . trim($internship_descs[$i]);
        $internships_str .= $intern_line . "\n\n";
    }
}
$internships_str = trim($internships_str);

// Build projects string
$projects_str = implode("\n", array_filter($projects));

// Build languages string with levels
$languages_str = '';
foreach ($languages as $i => $lang) {
    if (trim($lang)) {
        $lang_text = trim($lang);
        if (!empty($language_levels[$i])) {
            $lang_text .= ' (' . trim($language_levels[$i]) . ')';
        }
        $languages_str .= $lang_text . ', ';
    }
}
$languages_str = rtrim($languages_str, ', ');

// Build certifications string
$certifications_str = implode("\n", array_filter($certifications));

// Build hobbies string
$hobbies_str = implode(', ', array_filter($hobbies));

if ($resume_id > 0) {
    // UPDATE
    $stmt = $mysql->prepare("UPDATE resumes SET 
        full_name=?, email=?, phone=?, age=?, gender=?, address=?, objective=?,
        current_job_title=?, years_of_experience=?, education=?, experience=?,
        skills=?, projects=?, certifications=?, languages=?,
        linkedin_url=?, portfolio_url=?,
        college_name=?, college_course=?, college_cgpa=?,
        school_12th_name=?, school_12th_course=?, school_12th_percentage=?,
        internships=?
        WHERE id=?");

    $stmt->bind_param(
        'sssissssdsssssssssssssss',
        $full_name, $email, $phone, $ageValue, $gender, $address, $objective,
        $current_job_title, $yoeValue, $education, $experience,
        $skills, $projects_str, $certifications_str, $languages_str,
        $linkedin_url, $portfolio_url,
        $college_name, $college_course, $college_cgpa,
        $school_12th_name, $school_12th_course, $school_12th_percentage,
        $internships_str,
        $resume_id
    );
} else {
    // INSERT
    $stmt = $mysql->prepare("INSERT INTO resumes 
        (full_name, email, phone, age, gender, address, objective, current_job_title,
         years_of_experience, education, experience, skills, projects, certifications,
         languages, linkedin_url, portfolio_url,
         college_name, college_course, college_cgpa,
         school_12th_name, school_12th_course, school_12th_percentage,
         internships)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param(
        'sssissssdsssssssssssssss',
        $full_name, $email, $phone, $ageValue, $gender, $address, $objective,
        $current_job_title, $yoeValue, $education, $experience,
        $skills, $projects_str, $certifications_str, $languages_str,
        $linkedin_url, $portfolio_url,
        $college_name, $college_course, $college_cgpa,
        $school_12th_name, $school_12th_course, $school_12th_percentage,
        $internships_str
    );
}

if ($stmt->execute()) {
    $id = ($resume_id > 0) ? $resume_id : $stmt->insert_id;
    header("Location: index.php?status=saved&id=$id");
} else {
    error_log("Database Error: " . $stmt->error);
    header('Location: index.php?status=error');
}

$stmt->close();
$mysql->close();
exit;
?>