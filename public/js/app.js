// Gestion des formulaires AJAX
document.addEventListener('DOMContentLoaded', () => {
    // Formulaire d'ajout d'entrée
    const entryForm = document.querySelector('.entry-form');
    if (entryForm) {
        entryForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(entryForm);
            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                if (response.ok) {
                    entryForm.reset();
                    entryForm.querySelector('input[name="entry_date"]').value = new Date().toISOString().split('T')[0];
                    loadRecentEntries();
                    loadDashboard();
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    }
    
    // Formulaire d'ajout de catégorie
    const categoryForm = document.querySelector('.category-form');
    if (categoryForm) {
        categoryForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(categoryForm);
            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                if (response.ok) {
                    categoryForm.reset();
                    categoryForm.querySelector('input[name="color"]').value = '#3b82f6';
                    loadManagementLists();
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    }
    
    // Formulaire d'ajout de sujet
    const subjectForm = document.querySelector('.subject-form');
    if (subjectForm) {
        subjectForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(subjectForm);
            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                if (response.ok) {
                    subjectForm.reset();
                    loadManagementLists();
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    }
});

// Gestion des onglets
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
        
        // Charger les données selon l'onglet
        if (tab.dataset.tab === 'dashboard') loadDashboard();
        if (tab.dataset.tab === 'entry') loadRecentEntries();
        if (tab.dataset.tab === 'entries') loadAllEntries();
        if (tab.dataset.tab === 'manage') loadManagementLists();
        if (tab.dataset.tab === 'reports') {
            initReportControls();
            loadReport();
        }
    });
});

// Initialiser les contrôles des rapports
function initReportControls() {
    const periodSelect = document.getElementById('reportPeriod');
    const dateInput = document.getElementById('reportDate');
    const dateContainer = document.getElementById('dateInputContainer');
    const monthContainer = document.getElementById('monthInputContainer');
    const monthSelect = document.getElementById('reportMonth');
    const yearSelect = document.getElementById('reportYear');

    // Ajuster les contrôles selon la période actuelle au démarrage
    adjustControlsForPeriod();

    // Gérer le changement de période
    periodSelect.addEventListener('change', () => {
        adjustControlsForPeriod();
        loadReport();
    });

    // Charger le rapport au changement de date
    dateInput.addEventListener('change', () => {
        adjustDateForPeriod();
        loadReport();
    });

    // Charger le rapport au changement de mois/année
    monthSelect.addEventListener('change', loadReport);
    yearSelect.addEventListener('change', loadReport);
}

// Ajuster les contrôles selon la période
function adjustControlsForPeriod() {
    const period = document.getElementById('reportPeriod').value;
    const dateContainer = document.getElementById('dateInputContainer');
    const monthContainer = document.getElementById('monthInputContainer');
    const dateInput = document.getElementById('reportDate');
    const monthSelect = document.getElementById('reportMonth');
    const yearSelect = document.getElementById('reportYear');

    if (period === 'month') {
        // Afficher les contrôles mois/année, masquer la date
        dateContainer.style.display = 'none';
        monthContainer.style.display = 'block';

        // Initialiser les selects mois/année avec la date actuelle
        const currentDate = new Date(dateInput.value || new Date());
        monthSelect.value = currentDate.getMonth() + 1; // Les mois sont 0-indexés
        yearSelect.value = currentDate.getFullYear();
    } else {
        // Afficher le contrôle date, masquer mois/année
        dateContainer.style.display = 'block';
        monthContainer.style.display = 'none';

        // Ajuster la date selon la période
        adjustDateForPeriod();
    }
}

// Ajuster la date selon la période
function adjustDateForPeriod() {
    const period = document.getElementById('reportPeriod').value;
    const dateInput = document.getElementById('reportDate');
    let selectedDate = new Date(dateInput.value);

    if (period === 'week') {
        // Aller au début de la semaine (lundi)
        const day = selectedDate.getDay();
        const diff = selectedDate.getDate() - day + (day === 0 ? -6 : 1);
        const mondayDate = new Date(selectedDate.setDate(diff));
        dateInput.value = mondayDate.toISOString().split('T')[0];
    } else if (period === 'month') {
        // Aller au début du mois
        const firstDay = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
        dateInput.value = firstDay.toISOString().split('T')[0];
    }
}

// Charger le dashboard au démarrage
window.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
    loadRecentEntries();
    loadManagementLists();
    
    // Appliquer le thème sombre si déjà activé
    if(localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
        const themeToggle = document.getElementById('themeToggle');
        if(themeToggle) themeToggle.textContent = '☀️ Thème clair';
    }
    
    // Sauvegarder le choix du thème
    const themeToggle = document.getElementById('themeToggle');
    if(themeToggle) {
        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            if(document.documentElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                themeToggle.textContent = '☀️ Thème clair';
            } else {
                localStorage.setItem('theme', 'light');
                themeToggle.textContent = '🌙 Thème sombre';
            }
        });
    }
});

// Dashboard
async function loadDashboard() {
    try {
        const response = await fetch('api.php?action=summary&period=day');
        const result = await response.json();
        
        if (result.success) {
            displayStats(result.data);
            displayCategoryChart(result.data.summary);
            loadProgressChart();
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayStats(data) {
    const grid = document.getElementById('statsGrid');
    const hours = Math.floor(data.total_minutes / 60);
    const minutes = data.total_minutes % 60;
    
    grid.innerHTML = `
        <div class="stat-card">
            <div class="stat-label">Total aujourd'hui</div>
            <div class="stat-value">${hours}h ${minutes}m</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Catégories actives</div>
            <div class="stat-value">${data.summary.filter(s => s.total_minutes > 0).length}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Sessions</div>
            <div class="stat-value">${data.summary.reduce((sum, s) => sum + s.entries_count, 0)}</div>
        </div>
    `;
}

// Chart.js : graphique par catégorie
function displayCategoryChart(summary) {
    const chartEl = document.getElementById('categoryChart');
    if (!chartEl) return;
    
    const ctx = chartEl.getContext('2d');
    const labels = summary.map(s => s.category);
    const data = summary.map(s => s.total_minutes);
    const colors = summary.map(s => s.color);
    
    if(window.categoryChartInstance) {
        window.categoryChartInstance.destroy();
    }
    
    window.categoryChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: colors
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#222',
                        padding: 15
                    }
                },
                title: {
                    display: true,
                    text: 'Répartition par catégorie (aujourd\'hui)',
                    color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#222',
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const minutes = context.parsed;
                            const hours = Math.floor(minutes / 60);
                            const mins = minutes % 60;
                            return `${context.label}: ${hours}h ${mins}m`;
                        }
                    }
                }
            }
        }
    });
}

let progressChart = null;
async function loadProgressChart() {
    try {
        const response = await fetch('api.php?action=summary&period=week');
        const result = await response.json();
        
        if (result.success) {
            displayProgressChart(result.data.summary);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayProgressChart(summary) {
    const chartEl = document.getElementById('progressChart');
    if (!chartEl) return;
    
    const ctx = chartEl.getContext('2d');
    
    if (progressChart) {
        progressChart.destroy();
    }
    
    progressChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: summary.map(s => s.category),
            datasets: [{
                label: 'Minutes cette semaine',
                data: summary.map(s => s.total_minutes),
                backgroundColor: summary.map(s => s.color),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Temps par catégorie (7 derniers jours)',
                    color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#222',
                    font: { size: 16 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#666' },
                    grid: { color: document.documentElement.classList.contains('dark') ? '#374151' : '#ddd' }
                },
                x: {
                    ticks: { color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#666' },
                    grid: { display: false }
                }
            }
        }
    });
}

// Entrées récentes
async function loadRecentEntries() {
    try {
        const today = new Date().toISOString().split('T')[0];
        const response = await fetch(`api.php?action=entries&date=${today}`);
        const result = await response.json();
        
        if (result.success) {
            displayRecentEntries(result.data);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayRecentEntries(entries) {
    const container = document.getElementById('recentEntries');
    
    if (entries.length === 0) {
        container.innerHTML = '<p class="empty-state">Aucune entrée aujourd\'hui</p>';
        return;
    }
    
    container.innerHTML = entries.map(entry => `
        <div class="entry-item">
            <div class="entry-header">
                <span class="badge" style="background-color: ${entry.color}">${entry.category_name}</span>
                <strong>${entry.subject_name}</strong>
                <span class="entry-duration">${entry.duration_minutes} min</span>
            </div>
            ${entry.notes ? `<div class="entry-notes">${entry.notes}</div>` : ''}
            <div class="entry-actions">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_entry">
                    <input type="hidden" name="id" value="${entry.id}">
                    <button type="submit" class="btn-delete" onclick="return confirm('Supprimer?')">🗑️</button>
                </form>
            </div>
        </div>
    `).join('');
}

// Gestion
async function loadManagementLists() {
    try {
        const [catResponse, subResponse] = await Promise.all([
            fetch('api.php?action=categories'),
            fetch('api.php?action=subjects')
        ]);
        
        const catResult = await catResponse.json();
        const subResult = await subResponse.json();
        
        if (catResult.success) displayCategories(catResult.data);
        if (subResult.success) displaySubjects(subResult.data);
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayCategories(categories) {
    const container = document.getElementById('categoriesList');

    if (categories.length === 0) {
        container.innerHTML = '<p class="empty-state">Aucune catégorie</p>';
        return;
    }

    container.innerHTML = categories.map(cat => `
        <div class="list-item">
            <div class="list-item-content">
                <span class="color-dot" style="background-color: ${cat.color}"></span>
                <strong>${cat.name}</strong>
                <small>${cat.subjects_count} sujet(s)</small>
            </div>
            <div class="list-item-actions">
                <button class="btn-edit" onclick="editCategory(${cat.id}, '${cat.name.replace(/'/g, "\\'")}', '${cat.color}')" title="Modifier">✏️</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="id" value="${cat.id}">
                    <button type="submit" class="btn-delete" onclick="return confirm('Supprimer cette catégorie et tous ses sujets?')" title="Supprimer">🗑️</button>
                </form>
            </div>
        </div>
    `).join('');
}

function displaySubjects(subjects) {
    const container = document.getElementById('subjectsList');

    if (subjects.length === 0) {
        container.innerHTML = '<p class="empty-state">Aucun sujet</p>';
        return;
    }

    container.innerHTML = subjects.map(sub => `
        <div class="list-item">
            <div class="list-item-content">
                <span class="badge" style="background-color: ${sub.color}">${sub.category_name}</span>
                <strong>${sub.name}</strong>
                <small>${sub.total_minutes} min (${sub.entries_count} entrée(s))</small>
            </div>
            <div class="list-item-actions">
                <button class="btn-edit" onclick="editSubject(${sub.id}, ${sub.category_id}, '${sub.name.replace(/'/g, "\\'")}', '${(sub.description || '').replace(/'/g, "\\'")}')" title="Modifier">✏️</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_subject">
                    <input type="hidden" name="id" value="${sub.id}">
                    <button type="submit" class="btn-delete" onclick="return confirm('Supprimer ce sujet?')" title="Supprimer">🗑️</button>
                </form>
            </div>
        </div>
    `).join('');
}

// Rapports
async function loadReport() {
    const period = document.getElementById('reportPeriod').value;
    let date;

    if (period === 'month') {
        // Construire la date à partir des selects mois/année
        const month = document.getElementById('reportMonth').value;
        const year = document.getElementById('reportYear').value;
        date = `${year}-${month.padStart(2, '0')}-01`; // Premier jour du mois
    } else {
        // Utiliser la date directement
        date = document.getElementById('reportDate').value;
    }

    try {
        const response = await fetch(`api.php?action=summary&period=${period}&date=${date}`);
        const result = await response.json();

        if (result.success) {
            displayReport(result.data);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayReport(data) {
    const container = document.getElementById('reportContent');
    const exportBtn = document.getElementById('exportPdfBtn');
    const totalHours = Math.floor(data.total_minutes / 60);
    const totalMins = data.total_minutes % 60;

    let html = `
        <div class="report-header">
            <h2>Rapport - ${getPeriodLabel(data.period, data.date)}</h2>
            <div class="report-total">Total: ${totalHours}h ${totalMins}m</div>
        </div>
    `;

    if (data.summary.length === 0 || data.total_minutes === 0) {
        html += '<p class="empty-state">Aucune donnée pour cette période</p>';
        container.innerHTML = html;
        // Masquer le bouton d'export
        if (exportBtn) exportBtn.style.display = 'none';
        return;
    }

    // Afficher le bouton d'export s'il y a des données
    if (exportBtn) exportBtn.style.display = 'inline-block';
    
    html += `
        <table class="report-table">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Sujets</th>
                    <th>Sessions</th>
                    <th>Durée</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    data.summary.forEach(item => {
        if (item.total_minutes > 0) {
            const hours = Math.floor(item.total_minutes / 60);
            const mins = item.total_minutes % 60;
            const percent = data.total_minutes > 0 ? ((item.total_minutes / data.total_minutes) * 100).toFixed(1) : 0;
            
            html += `
                <tr>
                    <td>
                        <span class="color-dot" style="background-color: ${item.color}"></span>
                        ${item.category}
                    </td>
                    <td>${item.subjects_count}</td>
                    <td>${item.entries_count}</td>
                    <td>${hours}h ${mins}m</td>
                    <td>${percent}%</td>
                </tr>
            `;
        }
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function getPeriodLabel(period, date) {
    const dateObj = new Date(date + 'T00:00:00');
    const dayObj = new Date(date);

    if (period === 'day') {
        return `Jour du ${dateObj.toLocaleDateString('fr-FR')}`;
    } else if (period === 'week') {
        // Calculer la fin de semaine
        const startDate = new Date(dayObj);
        const endDate = new Date(dayObj);
        endDate.setDate(startDate.getDate() + 6);
        return `Semaine du ${startDate.toLocaleDateString('fr-FR')} au ${endDate.toLocaleDateString('fr-FR')}`;
    } else if (period === 'month') {
        return `Mois de ${dateObj.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })}`;
    }
    return period;
}

// Export PDF du rapport
function exportReportPDF() {
    const period = document.getElementById('reportPeriod').value;
    let params = `period=${period}`;

    if (period === 'month') {
        // Construire la date à partir des selects mois/année
        const month = document.getElementById('reportMonth').value;
        const year = document.getElementById('reportYear').value;
        params += `&month=${month}&year=${year}`;
    } else {
        // Utiliser la date directement
        const date = document.getElementById('reportDate').value;
        params += `&date=${date}`;
    }

    // Ouvrir le PDF dans une nouvelle fenêtre/onglet
    window.open(`export_pdf.php?${params}`, '_blank');
}

// Toutes les entrées
let allEntriesData = []; // Stockage des données complètes

async function loadAllEntries() {
    const filterDate = document.getElementById('entriesDateFilter').value;
    let url = 'api.php?action=all_entries';

    if (filterDate) {
        url += `&filter_date=${filterDate}`;
    }

    try {
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            allEntriesData = result.data; // Stocker les données complètes
            filterAndDisplayEntries();
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function filterAndDisplayEntries() {
    const searchTerm = document.getElementById('entriesSearch').value.toLowerCase().trim();
    let filteredEntries = allEntriesData;

    if (searchTerm) {
        filteredEntries = allEntriesData.filter(entry =>
            entry.subject_name.toLowerCase().includes(searchTerm) ||
            entry.category_name.toLowerCase().includes(searchTerm) ||
            (entry.notes && entry.notes.toLowerCase().includes(searchTerm))
        );
    }

    displayAllEntries(filteredEntries);
}

function displayAllEntries(entries) {
    const container = document.getElementById('allEntriesList');

    if (entries.length === 0) {
        container.innerHTML = '<p class="empty-state">Aucune entrée trouvée</p>';
        return;
    }

    container.innerHTML = entries.map(entry => {
        const entryDate = new Date(entry.entry_date).toLocaleDateString('fr-FR');
        const escapedNotes = (entry.notes || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
        return `
            <div class="entry-item">
                <div class="entry-header">
                    <span class="badge" style="background-color: ${entry.color}">${entry.category_name}</span>
                    <strong>${entry.subject_name}</strong>
                    <span class="entry-duration">${entry.duration_minutes} min</span>
                    <small class="entry-date">${entryDate}</small>
                </div>
                ${entry.notes ? `<div class="entry-notes">${entry.notes}</div>` : ''}
                <div class="entry-actions">
                    <button class="btn-edit" onclick="editEntry(${entry.id}, ${entry.subject_id}, ${entry.duration_minutes}, '${entry.entry_date}', '${escapedNotes}')" title="Modifier">Modifier</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_entry">
                        <input type="hidden" name="id" value="${entry.id}">
                        <button type="submit" class="btn-delete" onclick="return confirm('Supprimer cette entrée?')" title="Supprimer">🗑️</button>
                    </form>
                </div>
            </div>
        `;
    }).join('');
}

function clearDateFilter() {
    document.getElementById('entriesDateFilter').value = '';
    loadAllEntries();
}

// Fonctions d'édition des catégories et sujets
function editCategory(id, name, color) {
    document.getElementById('editCategoryId').value = id;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategoryColor').value = color;
    document.getElementById('editCategoryModal').style.display = 'block';
}

function editSubject(id, categoryId, name, description) {
    document.getElementById('editSubjectId').value = id;
    document.getElementById('editSubjectCategoryId').value = categoryId;
    document.getElementById('editSubjectName').value = name;
    document.getElementById('editSubjectDescription').value = description;
    document.getElementById('editSubjectModal').style.display = 'block';
}

function editEntry(id, subjectId, duration, date, notes) {
    console.log('editEntry called with:', {id, subjectId, duration, date, notes});

    // Vérifier que la modale existe
    const modal = document.getElementById('editEntryModal');
    if (!modal) {
        console.error('Modal editEntryModal not found');
        return;
    }

    // Remplir les champs
    const idField = document.getElementById('editEntryId');
    const subjectField = document.getElementById('editEntrySubjectId');
    const durationField = document.getElementById('editEntryDuration');
    const dateField = document.getElementById('editEntryDate');
    const notesField = document.getElementById('editEntryNotes');

    if (idField) idField.value = id;
    if (subjectField) subjectField.value = subjectId;
    if (durationField) durationField.value = duration;
    if (dateField) dateField.value = date;
    if (notesField) notesField.value = notes;

    console.log('Fields filled, showing modal');
    modal.style.display = 'block';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.8)'; // Forcer un arrière-plan visible
    console.log('Modal display set to block, modal element:', modal);
}

// Gestion des modales
document.addEventListener('DOMContentLoaded', () => {
    // Fermer les modales en cliquant sur la croix
    document.querySelectorAll('.modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            closeBtn.closest('.modal').style.display = 'none';
        });
    });

    // Fermer les modales en cliquant sur Annuler
    document.querySelectorAll('.modal-cancel').forEach(cancelBtn => {
        cancelBtn.addEventListener('click', () => {
            cancelBtn.closest('.modal').style.display = 'none';
        });
    });

    // Fermer les modales en cliquant en dehors
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Gestionnaire pour le formulaire d'édition de catégorie
    const editCategoryForm = document.querySelector('.edit-category-form');
    if (editCategoryForm) {
        editCategoryForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(editCategoryForm);
            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                if (response.ok) {
                    document.getElementById('editCategoryModal').style.display = 'none';
                    loadManagementLists();
                    loadDashboard(); // Rafraîchir les graphiques au cas où la couleur change
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    }

    // Gestionnaire pour le formulaire d'édition de sujet
    const editSubjectForm = document.querySelector('.edit-subject-form');
    if (editSubjectForm) {
        editSubjectForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(editSubjectForm);
            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                if (response.ok) {
                    document.getElementById('editSubjectModal').style.display = 'none';
                    loadManagementLists();
                    loadRecentEntries(); // Rafraîchir les entrées récentes au cas où le nom change
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    }

    // Gestionnaire pour le formulaire d'édition d'entrée
    const editEntryForm = document.querySelector('.edit-entry-form');
    if (editEntryForm) {
        editEntryForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(editEntryForm);
            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                if (response.ok) {
                    document.getElementById('editEntryModal').style.display = 'none';
                    loadAllEntries(); // Rafraîchir la liste des entrées
                    loadRecentEntries(); // Rafraîchir les entrées récentes
                    loadDashboard(); // Rafraîchir le dashboard
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    }

    // Gestionnaire pour le filtre de date des entrées
    const entriesDateFilter = document.getElementById('entriesDateFilter');
    if (entriesDateFilter) {
        entriesDateFilter.addEventListener('change', loadAllEntries);
    }

    // Gestionnaire pour le champ de recherche des entrées
    const entriesSearch = document.getElementById('entriesSearch');
    if (entriesSearch) {
        entriesSearch.addEventListener('input', filterAndDisplayEntries);
    }
});
