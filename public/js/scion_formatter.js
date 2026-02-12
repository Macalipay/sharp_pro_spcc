function formatCurrency(value) {
    if (value === null || value === undefined) return '0.00';
    return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Formatter for dates (you can customize this according to your needs)
function formatDate(value) {
    if (!value) return '';
    var date = new Date(value);
    var options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString(undefined, options);
}
