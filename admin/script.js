function filterRows(input, table) {
    const filterText = input.value.toLowerCase();
  
    const rows = table.querySelectorAll('tr');
  
    // Apply filter to each row, skipping the header
    rows.forEach(row => {
      // Skip the row if it contains any <th> element (header row)
      if (row.querySelector('th')) {
        return;  // Skip header row
      }
  
      let showRow = false;
  
      // Check each cell in the row
      Array.from(row.querySelectorAll('td')).forEach(cell => {
        if (cell.textContent.toLowerCase().includes(filterText)) {
          showRow = true;
        }
      });
  
      // Show or hide the row based on whether any cell matches the filter
      if (showRow) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }
  
  // Wait for the DOM to load
  document.addEventListener('DOMContentLoaded', () => {
    // Get the search input element
    const input = document.getElementById('search-weddings');
    if (!input) {
      console.error('No search input found');
    }
    
    // Get the table element
    const table = document.getElementById('weddings-table');
    if (!table) {
      console.error('No table found');
    }
    
    // Add an event listener to the search input
    input.addEventListener('input', () => filterRows(input, table));
  });
  