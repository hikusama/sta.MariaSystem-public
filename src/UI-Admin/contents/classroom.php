<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-school me-2"></i>Classrooms Management</h4>
    </div>
</div>
<div class="row col-md-12 col-11 justify-content-between mb-2">
    <div class="col-md-4">
        <input type="text" class="form-control" name="search" placeholder="Search....">
    </div>
    <div class="col-md-4">
        <select name="category" id="categorySelect" class="form-select">
            <option value="">Select Categories</option>
            <option value="Classrooms">Classrooms</option>
            <option value="Sections">Sections</option> 
            <option value="school year">School Year</option>
        </select>
    </div>
    <div class="col-md-4">
        <button class="btn btn-danger m-0" data-bs-toggle="modal" data-bs-target="#createClassrooms" id="createClassroomBtn">Create Classrooms</button>
        <button class="btn btn-danger m-0" data-bs-toggle="modal" data-bs-target="#createSection" id="createSectionBtn">Create Section</button>
        <button class="btn btn-danger m-0" data-bs-toggle="modal" data-bs-target="#createSchoolYear" id="createSchoolYearBtn">Create School Year</button>
</div>
<!-- add Classroom Modal -->
 <div class="modal fade" id="createClassrooms" tabindex="-1" aria-labelledby="createClassroomsLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="createClassroomsLabel">Create New Classrooms</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="classroom-form" method="post">
                    <div class="my-2">
                        <label class="form-label">Classroom Name</label>
                        <input type="text" name="classroom_name" class="form-control" placeholder="ex. DAS 202">
                    </div>
                    <div class="my-2">
                        <label class="form-label">Classroom Type</label>
                        <input type="text" name="classroom_type" class="form-control" placeholder="ex. Lecture Room">
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-5">
                            Create Classroom 
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- add Section Modal -->
<div class="modal fade" id="createSection" tabindex="-1" aria-labelledby="createSectionLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="createSectionLabel">Create New Secton</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="section-form" method="post">
                    <div class="my-2">
                        <label class="form-label">Secton Name</label>
                        <input type="text" name="section_name" class="form-control" placeholder="ex. Jupiter">
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
                        <label class="form-label">Secton Descrption</label>
                        <textarea name="section_desc" id="" class="form-control"></textarea>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-5">
                            Create Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- add School Year Modal -->
<div class="modal fade" id="createSchoolYear" tabindex="-1" aria-labelledby="createSchoolYearLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="createSchoolYearLabel">Create New School Year</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="sy-form" method="post">
                    <div class="my-2">
                        <label class="form-label">School Year Name</label>
                        <input type="text" name="schoolYear_name" class="form-control" placeholder="ex. 2025 - 2026">
                    </div>
                    <div class="my-2">
                        <label class="form-label">School Year Status</label>
                        <select name="status" id="" class="form-select">
                            <option value="">Select Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-5">
                            Create S.Y
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- dsiplays Classrooms -->
<div class="classroomDisplays">
    <h2>Classrooms</h2>
</div>
<div class="sectionsDisplays">
    <h2>Sections</h2>
</div>
<div class="schoolYearDisplays">
    <h2>School Year</h2>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const categorySelect = document.getElementById('categorySelect');
    const createClassroomBtn = document.getElementById('createClassroomBtn');
    const createSectionBtn = document.getElementById('createSectionBtn');
    const createSchoolYearBtn = document.getElementById('createSchoolYearBtn');
    const classroomDisplay = document.querySelector('.classroomDisplays');
    const sectionDisplay = document.querySelector('.sectionsDisplays');
    const schoolYearDisplay = document.querySelector('.schoolYearDisplays');

    // Hide all displays initially except classrooms
    function initializeDisplays() {
        sectionDisplay.style.display = 'none';
        schoolYearDisplay.style.display = 'none';
        classroomDisplay.style.display = 'block';
        
        // Show only create classroom button initially
        createClassroomBtn.style.display = 'inline-block';
        createSectionBtn.style.display = 'none';
        createSchoolYearBtn.style.display = 'none';
    }

    // Handle category selection change
    categorySelect.addEventListener('change', function() {
        const selectedValue = this.value;
        
        // Hide all displays and buttons first
        classroomDisplay.style.display = 'none';
        sectionDisplay.style.display = 'none';
        schoolYearDisplay.style.display = 'none';
        createClassroomBtn.style.display = 'none';
        createSectionBtn.style.display = 'none';
        createSchoolYearBtn.style.display = 'none';

        // Show appropriate display and button based on selection
        switch(selectedValue) {
            case 'Classrooms':
                classroomDisplay.style.display = 'block';
                createClassroomBtn.style.display = 'inline-block';
                break;
            case 'Sections':
                sectionDisplay.style.display = 'block';
                createSectionBtn.style.display = 'inline-block';
                break;
            case 'school year':
                schoolYearDisplay.style.display = 'block';
                createSchoolYearBtn.style.display = 'inline-block';
                break;
            default:
                // If nothing selected or empty, show classrooms as default
                classroomDisplay.style.display = 'block';
                createClassroomBtn.style.display = 'inline-block';
                break;
        }
    });

    // Initialize on page load
    initializeDisplays();
});
</script>