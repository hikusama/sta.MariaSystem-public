document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const tableBody = document.querySelector('.table-body-scroll tbody');
    const originalRows = Array.from(tableBody.querySelectorAll('tr'));
    
    const rowData = originalRows.map(row => {
        const cells = row.querySelectorAll('td');
        return {
            element: row,
            name: cells[1].textContent.toLowerCase(),
            role: cells[2].textContent.toLowerCase(),
            status: cells[3].textContent.toLowerCase(),
            date: cells[4].textContent.toLowerCase()
        };
    });

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryValue = categoryFilter.value.toLowerCase();
        
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
                data.element.querySelector('td:first-child').textContent = index + 1;
            } else {
                data.element.style.display = 'none';
            }
        });

        renumberVisibleRows();
    }

    function renumberVisibleRows() {
        let visibleCount = 1;
        rowData.forEach(data => {
            if (data.element.style.display !== 'none') {
                data.element.querySelector('td:first-child').textContent = visibleCount++;
            }
        });
    }

    searchInput.addEventListener('input', filterTable);
    categoryFilter.addEventListener('change', filterTable);

    function clearFilters() {
        searchInput.value = '';
        categoryFilter.value = '';
        filterTable();
    }

    // const clearButton = document.createElement('button');
    // clearButton.textContent = 'Clear Filters';
    clearButton.className = 'btn btn-secondary btn-sm';
    clearButton.addEventListener('click', clearFilters);
    
    categoryFilter.parentNode.appendChild(clearButton);
});