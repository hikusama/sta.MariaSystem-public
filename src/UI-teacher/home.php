<body>
    <?php
      $studentCount = $pdo->query("SELECT COUNT(*) FROM student INNER JOIN enrolment ON student.student_id = enrolment.student_id WHERE enrolment.adviser_id = '$user_id'")->fetchColumn();
      $teacherCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_role = 'TEACHER'")->fetchColumn();
      $parentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_role = 'PARENT'")->fetchColumn();

      $stmt = $pdo->prepare("SELECT section_name, grade_level FROM classes WHERE adviser_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $sectionName = $stmt->fetch(PDO::FETCH_ASSOC);


      $stmt = $pdo->prepare("SELECT school_year_name FROM school_year WHERE school_year_status = 'Active'");
      $stmt->execute();
      $activeSY = $stmt->fetch(PDO::FETCH_ASSOC);
   ?>

    <section>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="mx-2 marginToMedia sideAnimation">
                <h4>Active School Year</h4>
            </div>
        </div>
        <div class="row col-md-5 border shadow m-0 p-3 rounded-3 mb-4">
           <span class="m-0 fs-5">SCHOOL YEAR: <strong><?= $activeSY["school_year_name"] ?? 'No Active School Year' ?></strong></span>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="mx-2 marginToMedia sideAnimation">
                <h4>My Class</h4>
            </div>
        </div>
        <div class="row justify-content-start align-items-center gap-3 d-flex text-center flex-wrap mx-2">
            <a href="index.php?page=contents/student" class="row shadow border rounded-3 p-3 align-items-center justify-content-between col-md-5">
                <div class="col-md-5 d-flex align-items-center justify-content-center flex-column">
                    <h5 class="m-0 p-0 text-start w-100"><?= $sectionName["grade_level"]?></h5>
                    <h5 class="m-0 p-0 text-start w-100"><?= 'Section: ' .  $sectionName["section_name"]?></h5>
                </div>
                <div class="d-flex flex-column align-items-center justify-content-end col-md-7">
                    <h1 class="m-0 p-0 text-end w-100"><?= $studentCount ?></h1>
                    <strong class="m-0 p-0 text-end w-100">Total Students In this section</strong>
                </div>
            </a>
        </div>

    </section>
</body>