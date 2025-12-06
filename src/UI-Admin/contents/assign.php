 <?php
    $stmt = $pdo->prepare("SELECT * FROM classrooms
    LEFT JOIN  classes ON classrooms.room_id = classes.classroom_id
    LEFT JOIN users ON classes.adviser_id = users.user_id
    ORDER BY room_name ASC");
    $stmt->execute();
    $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM sections ORDER BY section_name ASC");
    $stmt->execute();
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_role = 'TEACHER' ORDER BY lastname ASC");
    $stmt->execute();
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM school_year ORDER BY school_year_name ASC");
    $stmt->execute();
    $schoolYears = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
 <style>
.classroomsAvailable {
    background-color: #e4e4e4ff;
}
 </style>
 <div class="d-flex justify-content-between align-items-center mb-2">
     <div class="mx-2">
         <h4><i class="fa-solid fa-building-user me-2"></i>Class Management</h4>
     </div>
 </div>

 <div class="col-md-12 mb-2">
     <input type="text" name="search" placeholder="Search Classrooms...." class="form-control">
 </div>

 <div class="row col-md-12 col-11 m-0 p-0 justify-content-start gap-2">
     <?php foreach($classrooms as $classroom) : ?>
     <div class="classroomsAvailable shadow rounded border col-md-3 m-0 p-3">
         <div class="d-flex">
             <span class="form-span text-dark m-0">Classroom Name: </label>
                 <strong class="text-dark"><?= $classroom["room_name"] ?></strong>
         </div>
         <div class="d-flex mt-2">
             <span class="form-span text-dark m-0">Classroom Type: </label>
                 <strong class="text-dark"><?= $classroom["room_type"] ?></strong>
         </div>
         <div class="d-flex mt-2">
             <span class="form-span text-dark m-0">Classroom Status: </label>
                 <strong class="text-dark"><?= $classroom["room_status"] ?></strong>
         </div>
         <div class="d-flex mt-2">
             <span class="form-span text-dark m-0">Teacher Assigned: </label>
                 <strong class="text-dark"><?= $classroom["firstname"] . " " . $classroom["lastname"] ?></strong>
         </div>
         <div class="w-100 d-flex justify-content-center mt-2">
            <?php
                if($classroom["room_status"] == "Unavailable"){    
                }else{
            ?>
            <button 
                type="button" 
                class="btn btn-danger btn-sm m-0 w-100 text-white assign-teacher-btn" 
                data-id="<?= $classroom["room_id"] ?>">
                Assign Teacher
            </button>
            <?php } ?>
         </div>
     </div>
     <?php endforeach ?>
     <div class="modal fade" id="assgnTeacher" tabindex="-1" aria-labelledby="assgnTeacherLabel" aria-hidden="true">
         <div class="modal-dialog modal-md">
             <div class="modal-content">
                 <div class="modal-header bg-danger text-white">
                     <h5 class="modal-title text-white" id="assgnTeacherLabel">Assign Teacher</h5>
                     <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                         aria-label="Close"></button>
                 </div>
                 <div class="modal-body">
                     <form class="row g-3" id="assign-teacher-form" method="post">

                         <!-- Hidden Input for Classroom ID -->
                         <input type="hidden" name="classroom_id" id="classroomIdInput" value="">

                         <!-- Section Dropdown -->
                         <div class="my-2">
                             <label class="form-label">Section Name</label>
                             <select name="section_id" id="section_id" class="form-select" required>
                                 <option value="">Select Section</option>
                                 <?php foreach($sections as $section): ?>
                                 <option value="<?= $section["section_id"] ?>">
                                     <?= htmlspecialchars($section["section_name"]) ?>
                                 </option>
                                 <?php endforeach; ?>
                             </select>
                         </div>
                         <div class="my-2">
                            <label class="form-label">Grade Level</label>
                            <select name="grade_level" id="" class="form-select">
                                <option value="">Select Grade Level</option>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                            </select>
                        </div>
                        <div class="my-2">
                             <label class="form-label">Teacher Name</label>
                             <select name="teacher_name" id="teacher_id" class="form-select" required>
                                 <option value="">Select Section</option>
                                 <?php foreach($teachers as $teacher): ?>
                                 <option value="<?= $teacher["user_id"] ?>">
                                     <?= htmlspecialchars($teacher["lastname"]) . " " . htmlspecialchars($teacher["firstname"])?>
                                 </option>
                                 <?php endforeach; ?>
                             </select>
                         </div>
                          <div class="my-2">
                             <label class="form-label">School Year</label>
                             <select name="schoolYear_id" id="schoolYear_id" class="form-select" required>
                                 <option value="">Select Section</option>
                                 <?php foreach($schoolYears as $schoolYear): ?>
                                 <option value="<?= $schoolYear["school_year_id"] ?>">
                                     <?= htmlspecialchars($schoolYear["school_year_name"])?>
                                 </option>
                                 <?php endforeach; ?>
                             </select>
                         </div>

                         <!-- Submit Button -->
                         <div class="col-12 text-center mt-3">
                             <button type="submit" class="btn btn-primary px-5">
                                 Assign
                             </button>
                         </div>
                     </form>
                 </div>
             </div>
         </div>
     </div>
 </div>