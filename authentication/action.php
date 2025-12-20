<?php
header('Content-Type: application/json');
/* header('x-powered-by : PHP/8.0.30'); */
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';

include 'admin_class.php';

$crud = new Action();

if ($action === 'save_installation_data') {
	$installer = $crud->save_installation_data();

	if ($installer) {
		echo $installer;
	}
}
// if($action === 'admin_register'){
// 	$registration = $crud->admin_register();
// 	if($registration){
// 		echo $registration;
// 	}
// }
if($action === 'Account_form'){
    $registration = $crud->Account_form();
    if($registration){
        echo $registration;
    }
    exit();
}


if($action === 'classroom_form'){
    $registration = $crud->classroom_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'section_form'){
    $registration = $crud->section_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'schoolYear_form'){
    $registration = $crud->schoolYear_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'subjects_form'){
    $registration = $crud->subjects_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'assignTeacher_form'){
    $registration = $crud->assignTeacher_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'studentAcc_form'){
    $registration = $crud->studentAcc_form();
    if($registration){
        echo $registration;
    }
    exit();
} 
if($action === 'enrolment_form'){
    $registration = $crud->enrolment_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'rejectEnrolment_form'){
    $registration = $crud->rejectEnrolment_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'activationSY_form'){
    $registration = $crud->activationSY_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'DeactivationSY_form'){
    $registration = $crud->DeactivationSY_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'stduentEnrolment_form'){
    $registration = $crud->stduentEnrolment_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'deleteClassroom_form'){
    $registration = $crud->deleteClassroom_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'editClassroom_form'){
    $registration = $crud->editClassroom_form();
    if($registration){
        echo $registration;
    }
    exit();
}

if($action === 'deleteSection_form'){
    $registration = $crud->deleteSection_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if ($action === 'getClassroomById') {
    $id = $_POST['classroom_id'] ?? '';
    echo $crud->getClassroomById($id);
    exit();
}
if ($action === 'getSectionById') {
    $id = $_POST['section_id'] ?? '';
    echo $crud->getSectionById($id);
    exit();
}

if($action === 'editSection_form'){
    $registration = $crud->editSection_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'deleteSchoolYear_form'){
    $registration = $crud->deleteSchoolYear_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if ($action === 'getSchoolYearById') {
    $id = $_POST['school_year_id'] ?? '';
    echo $crud->getSchoolYearById($id);
    exit();
}
if($action === 'editSchoolyear_form'){
    $registration = $crud->editSchoolyear_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'deleteSubject_form'){
    $registration = $crud->deleteSubject_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if ($action === 'getSubjectsById') {
    $id = $_POST['subject_id'] ?? '';
    echo $crud->getSubjectsById($id);
    exit();
}
if($action === 'editSubjects_form'){
    $registration = $crud->editSubjects_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'morning_attendanceP'){
    $registration = $crud->morning_attendanceP();
    if($registration){
        echo $registration;
    }
    exit();
} 
if($action === 'morning_attendanceA'){
    $registration = $crud->morning_attendanceA();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'morning_attendanceL'){
    $registration = $crud->morning_attendanceL();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'afternoon_attendanceP'){
    $registration = $crud->afternoon_attendanceP();
    if($registration){
        echo $registration;
    }
    exit();
} 
if($action === 'afternoon_attendanceA'){
    $registration = $crud->afternoon_attendanceA();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'afternoon_attendanceL'){
    $registration = $crud->afternoon_attendanceL();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'status_form'){
    $registration = $crud->status_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'status_enrolment_form'){
    $registration = $crud->status_enrolment_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'student_update_form'){
    $registration = $crud->student_update_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'displayStudentInfo'){
    $registration = $crud->displayStudentInfo();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'medical_update'){
    $registration = $crud->medical_update();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'feedback_form'){
    $registration = $crud->feedback_form();
    if($registration){
        echo $registration;
    }
    exit();
} 
if($action === 'sfFour_form'){
    $registration = $crud->sfFour_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'sfEight_form'){
    $registration = $crud->sfEight_form();
    if($registration){
        echo $registration;
    }
    exit();
}
if($action === 'deleteFeedback_form'){
    $registration = $crud->deleteFeedback_form();
    if($registration){
        echo $registration;
    }
    exit();
}