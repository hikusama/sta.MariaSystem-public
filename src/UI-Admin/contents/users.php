<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-users-gear me-2"></i>Users Management</h4>
    </div>
</div>

<!-- Search and Filters -->

<div class="row g-2  justify-content-between">
    <div class="row mb-3  justify-content-between">
        <div class="col-md-4">
            <input type="text" id="searchInput" name="search" class="form-control"
                placeholder="Search by name, role, status, or date...">
        </div>
        <div class="col-md-4">
            <select id="categoryFilter" name="category" class="form-select">
                <option value="">All User Roles</option>
                <?php
                    $catStmt = $pdo->query("SELECT DISTINCT user_role FROM users ORDER BY user_role ASC");
                    while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)): ?>
                <option value="<?= htmlspecialchars($cat['user_role']) ?>">
                    <?= htmlspecialchars($cat['user_role']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#AddNewAccount"
                id="add_new"><i class="fa fa-plus"></i> New Account</button>
        </div>
    </div>


    <!-- Adding account modal -->
    <div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="AddNewAccountLabel">Create New User Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="Account-form" method="post">
                        <div class="col-md-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="lastName" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="firstName" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middleName">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Suffix</label>
                            <select class="form-select" name="suffix">
                                <option value="" selected>Select suffix (optional)</option>
                                <option value="Jr">Jr</option>
                                <option value="Sr">Sr</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">User Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="user_role" required>
                                <option value="" disabled selected>Select User Role</option>
                                <option value="TEACHER">Teacher</option>
                                <option value="PARENT">Parent</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="" disabled selected>Select</option>
                                <option value="MALE">Male</option>
                                <option value="FEMALE">Female</option>
                            </select>
                        </div>

                        <!-- Contact Information -->
                        <div class="col-md-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="contact" required>
                        </div>

                        <!-- Account Credentials -->
                        <div class="col-md-4">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="cpassword" required>
                        </div>

                        <!-- Form Submission -->
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div> -->
            </div>
        </div>
    </div>
    <!-- Accounts Displays -->
    <div class="table-container-wrapper">
        <?php
            $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_date DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
        ?>

        <!-- Replace your table containers with this: -->
        <div class="table-responsive text-center">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="sticky-top bg-white" style="top: 0;">
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Name</th>
                        <th width="15%">User Role</th>
                        <th width="15%">Status</th>
                        <th width="20%">Created at</th>
                        <th width="25%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
            if($users){
                $count = 1;
                foreach($users as $user) : ?>
                    <tr>
                        <td width="5%"><?= $count++ ?></td>
                        <td width="20%">
                            <?= htmlspecialchars($user["firstname"]) . " " . 
                        (!empty($user["middlename"]) ? htmlspecialchars(substr($user["middlename"], 0, 1)) . ". " : "") . 
                        htmlspecialchars($user["lastname"]) ?>
                        </td>
                        <td width="15%"><?= htmlspecialchars($user["user_role"]) ?></td>
                        <td width="15%">
                            <span class="badge bg-<?= ($user["status"] == 'Active') ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars($user["status"] ?? 'Inactive') ?>
                            </span>
                        </td>
                        <td width="20%"><?= htmlspecialchars($user["created_date"]) ?></td>
                        <td width="25%">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="index.php?page=contents/usersProfile&user_id=<?= $user["user_id"] ?>">
                                    <button type="button" class="btn m-0 btn-info btn-sm">View</button>
                                </a>
                                <form class="status-form">
                                    <select name="status" class="status-select form-select form-select-sm">
                                        <option value="">Select Status</option>
                                        <option value="Active" <?= ($user["status"] === "Active") ? "selected" : "" ?>>
                                            Active</option>
                                        <option value="Inactive"
                                            <?= ($user["status"] === "Inactive") ? "selected" : "" ?>>Inactive</option>
                                    </select>
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;
            } else {
                echo '<tr><td colspan="6" class="text-center py-3">No users found.</td></tr>';
            } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    
    // Get the correct table body - updated selector
    const tableBody = document.querySelector('.table-responsive:last-child tbody');
    
    // Store original rows for filtering
    let originalRows = Array.from(tableBody.querySelectorAll('tr'));

    // Extract row data for filtering
    const rowData = originalRows.map(row => {
        const cells = row.querySelectorAll('td');
        return {
            element: row,
            name: cells[1]?.textContent?.toLowerCase() || '',
            role: cells[2]?.textContent?.toLowerCase() || '',
            status: cells[3]?.textContent?.toLowerCase() || '',
            date: cells[4]?.textContent?.toLowerCase() || ''
        };
    });

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryValue = categoryFilter.value.toLowerCase();

        let visibleCount = 1;
        
        rowData.forEach((data, index) => {
            const matchesSearch = searchTerm === '' ||
                data.name.includes(searchTerm) ||
                data.role.includes(searchTerm) ||
                data.status.includes(searchTerm) ||
                data.date.includes(searchTerm);

            const matchesCategory = categoryValue === '' ||
                data.role.includes(categoryValue);

            if (matchesSearch && matchesCategory) {
                data.element.style.display = '';
                // Update the row number
                data.element.querySelector('td:first-child').textContent = visibleCount++;
            } else {
                data.element.style.display = 'none';
            }
        });

        // Handle empty results
        const visibleRows = rowData.filter(data => data.element.style.display !== 'none');
        if (visibleRows.length === 0) {
            // Add a "no results" row if needed
            let noResultsRow = tableBody.querySelector('.no-results-row');
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = '<td colspan="6" class="text-center py-3">No users found matching your criteria.</td>';
                tableBody.appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else {
            // Remove "no results" row if it exists
            const noResultsRow = tableBody.querySelector('.no-results-row');
            if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        }
    }

    function clearFilters() {
        searchInput.value = '';
        categoryFilter.value = '';
        filterTable();
    }

    // Create clear button if you want one (optional)
    // const clearButton = document.createElement('button');
    // clearButton.textContent = 'Clear';
    // clearButton.className = 'btn btn-secondary btn-sm mt-2';
    // clearButton.addEventListener('click', clearFilters);
    
    // Add clear button near the category filter if needed
    // categoryFilter.parentNode.appendChild(clearButton);

    // Add event listeners
    searchInput.addEventListener('input', filterTable);
    categoryFilter.addEventListener('change', filterTable);

    // Initial filter (in case there are any default values)
    filterTable();
});
</script>