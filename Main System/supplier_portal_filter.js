function setupTableFilter(options) {
    const searchInput = $(options.searchInput);
    const categoryFilter = $(options.categoryFilter);
    const table = $(options.table);

    function filterTable() {
        const searchText = searchInput.val().toLowerCase();
        const category = categoryFilter.val().toLowerCase();

        table.find('tbody tr').each(function() {
            const batchID = $(this).find('td:nth-child(1)').text().toLowerCase();
            const itemName = $(this).find('td:nth-child(2)').text().toLowerCase();
            const quantity = $(this).find('td:nth-child(4)').text().toLowerCase();
            const itemCategory = $(this).find('td:nth-child(3)').text().toLowerCase().trim();

            const matchesSearch = batchID.includes(searchText) || itemName.includes(searchText) || quantity.includes(searchText);
            const matchesCategory = category === '' || itemCategory === category;

            $(this).toggle(matchesSearch && matchesCategory);
        });
    }

    searchInput.on('input', filterTable);
    categoryFilter.on('change', filterTable);

    if (options.clearButton) {
        $(options.clearButton).on('click', function() {
            searchInput.val('');
            categoryFilter.val('');
            filterTable();
        });
    }
}
