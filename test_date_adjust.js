// Test de la logique d'ajustement des dates
function adjustDateForPeriod(period, inputDate) {
    let selectedDate = new Date(inputDate + 'T00:00:00');

    if (period === 'week') {
        const day = selectedDate.getDay();
        const diff = selectedDate.getDate() - day + (day === 0 ? -6 : 1);
        const mondayDate = new Date(selectedDate.setDate(diff));
        return mondayDate.toISOString().split('T')[0];
    } else if (period === 'month') {
        const firstDay = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
        return firstDay.toISOString().split('T')[0];
    }
    return inputDate;
}

// Tests
console.log('Test ajustement mois:');
console.log('2026-01-15 ->', adjustDateForPeriod('month', '2026-01-15'));
console.log('2026-02-20 ->', adjustDateForPeriod('month', '2026-02-20'));
console.log('2025-12-25 ->', adjustDateForPeriod('month', '2025-12-25'));

console.log('\nTest ajustement semaine:');
console.log('2026-01-15 (mercredi) ->', adjustDateForPeriod('week', '2026-01-15'));
console.log('2026-01-12 (dimanche) ->', adjustDateForPeriod('week', '2026-01-12'));
