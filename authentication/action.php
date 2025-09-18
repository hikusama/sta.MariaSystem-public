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
if($action === 'admin_register'){
	$registration = $crud->admin_register();
	if($registration){
		echo $registration;
	}
}
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