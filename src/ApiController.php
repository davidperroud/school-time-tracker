<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../vendor/autoload.php';

class ApiController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'summary':
                return $this->getSummary();
            case 'categories':
                return $this->getCategories();
            case 'subjects':
                return $this->getSubjects();
            case 'entries':
                return $this->getEntries();
            case 'all_entries':
                return $this->getAllEntries();
            case 'stats':
                return $this->getStats();
            default:
                return $this->error('Action non reconnue');
        }
    }

    public function exportPDF() {
        $this->generatePDF();
    }

    private function getSummary() {
        $period = $_GET['period'] ?? 'day';
        $date = $_GET['date'] ?? date('Y-m-d');
        $month = $_GET['month'] ?? null;
        $year = $_GET['year'] ?? null;

        $dateCondition = $this->getDateCondition($period, $date, $month, $year);

        $sql = "SELECT
                    c.id,
                    c.name as category,
                    c.color,
                    COALESCE(SUM(te.duration_minutes), 0) as total_minutes,
                    COUNT(DISTINCT te.subject_id) as subjects_count,
                    COUNT(te.id) as entries_count
                FROM categories c
                LEFT JOIN subjects s ON c.id = s.category_id
                LEFT JOIN time_entries te ON s.id = te.subject_id AND $dateCondition
                GROUP BY c.id, c.name, c.color
                ORDER BY COALESCE(SUM(te.duration_minutes), 0) DESC";

        $results = $this->db->fetchAll($sql);
        $total = array_sum(array_column($results, 'total_minutes'));

        return $this->success([
            'period' => $period,
            'date' => $date,
            'summary' => $results,
            'total_minutes' => $total
        ]);
    }

    private function getCategories() {
        $sql = "SELECT c.*,
                       COUNT(DISTINCT s.id) as subjects_count,
                       COALESCE(SUM(te.duration_minutes), 0) as total_minutes
                FROM categories c
                LEFT JOIN subjects s ON c.id = s.category_id
                LEFT JOIN time_entries te ON s.id = te.subject_id
                GROUP BY c.id
                ORDER BY c.name";

        return $this->success($this->db->fetchAll($sql));
    }

    private function getSubjects() {
        $categoryId = $_GET['category_id'] ?? null;

        $sql = "SELECT s.*, c.name as category_name, c.color,
                       COALESCE(SUM(te.duration_minutes), 0) as total_minutes,
                       COUNT(te.id) as entries_count
                FROM subjects s
                JOIN categories c ON s.category_id = c.id
                LEFT JOIN time_entries te ON s.id = te.subject_id";

        $params = [];
        if ($categoryId) {
            $sql .= " WHERE s.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " GROUP BY s.id ORDER BY s.name";

        return $this->success($this->db->fetchAll($sql, $params));
    }

    private function getEntries() {
        $date = $_GET['date'] ?? date('Y-m-d');
        $subjectId = $_GET['subject_id'] ?? null;

        $sql = "SELECT te.*, s.name as subject_name, c.name as category_name, c.color
                FROM time_entries te
                JOIN subjects s ON te.subject_id = s.id
                JOIN categories c ON s.category_id = c.id
                WHERE te.entry_date = ?";

        $params = [$date];

        if ($subjectId) {
            $sql .= " AND te.subject_id = ?";
            $params[] = $subjectId;
        }

        $sql .= " ORDER BY te.created_at DESC";

        return $this->success($this->db->fetchAll($sql, $params));
    }

    private function getAllEntries() {
        $filterDate = $_GET['filter_date'] ?? null;

        $sql = "SELECT te.*, s.name as subject_name, c.name as category_name, c.color
                FROM time_entries te
                JOIN subjects s ON te.subject_id = s.id
                JOIN categories c ON s.category_id = c.id";

        $params = [];

        if ($filterDate) {
            $sql .= " WHERE te.entry_date = ?";
            $params[] = $filterDate;
        }

        $sql .= " ORDER BY te.entry_date DESC, te.created_at DESC";

        return $this->success($this->db->fetchAll($sql, $params));
    }

    private function getStats() {
        $subjectId = $_GET['subject_id'] ?? null;
        $days = $_GET['days'] ?? 30;

        if (!$subjectId) {
            return $this->error('subject_id requis');
        }

        $sql = "SELECT
                    entry_date,
                    SUM(duration_minutes) as minutes
                FROM time_entries
                WHERE subject_id = ?
                AND entry_date >= date('now', '-' || ? || ' days')
                GROUP BY entry_date
                ORDER BY entry_date";

        $data = $this->db->fetchAll($sql, [$subjectId, $days]);

        $total = array_sum(array_column($data, 'minutes'));
        $avg = count($data) > 0 ? $total / count($data) : 0;

        return $this->success([
            'subject_id' => $subjectId,
            'days' => $days,
            'daily_data' => $data,
            'total_minutes' => $total,
            'average_per_day' => round($avg, 2)
        ]);
    }

    private function generatePDF() {
        $period = $_GET['period'] ?? 'day';
        $date = $_GET['date'] ?? date('Y-m-d');
        $month = $_GET['month'] ?? null;
        $year = $_GET['year'] ?? null;

        // Récupérer les données du rapport
        $dateCondition = $this->getDateCondition($period, $date, $month, $year);

        $sql = "SELECT
                    c.id,
                    c.name as category,
                    c.color,
                    COALESCE(SUM(te.duration_minutes), 0) as total_minutes,
                    COUNT(DISTINCT te.subject_id) as subjects_count,
                    COUNT(te.id) as entries_count
                FROM categories c
                LEFT JOIN subjects s ON c.id = s.category_id
                LEFT JOIN time_entries te ON s.id = te.subject_id AND $dateCondition
                GROUP BY c.id, c.name, c.color
                ORDER BY COALESCE(SUM(te.duration_minutes), 0) DESC";

        $results = $this->db->fetchAll($sql);
        $total = array_sum(array_column($results, 'total_minutes'));

        // Générer le PDF
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Configuration du PDF
        $pdf->SetCreator('Study Time Tracker');
        $pdf->SetAuthor('Study Time Tracker');
        $pdf->SetTitle('Rapport d\'étude');
        $pdf->SetSubject('Rapport de temps d\'étude');

        // Supprimer les en-têtes et pieds de page par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Marges
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);

        // Ajouter une page
        $pdf->AddPage();

        // Titre
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(0, 15, 'Rapport d\'étude', 0, 1, 'C');
        $pdf->Ln(5);

        // Période
        $pdf->SetFont('helvetica', '', 12);
        $periodLabel = $this->getPeriodLabel($period, $date, $month, $year);
        $pdf->Cell(0, 10, $periodLabel, 0, 1, 'C');
        $pdf->Ln(5);

        // Date de génération
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 8, 'Généré le ' . date('d/m/Y à H:i'), 0, 1, 'R');
        $pdf->Ln(10);

        // Total général
        if ($total > 0) {
            $hours = floor($total / 60);
            $minutes = $total % 60;
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 12, 'Total: ' . $hours . 'h ' . $minutes . 'm', 0, 1, 'C');
            $pdf->Ln(10);
        }

        // Tableau des données
        if ($total > 0 && count($results) > 0) {
            // En-têtes du tableau
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetFillColor(240, 240, 240);

            // Calcul des largeurs de colonnes
            $colWidths = [80, 25, 25, 30, 25]; // Catégorie, Sujets, Sessions, Durée, %

            // En-têtes
            $pdf->Cell($colWidths[0], 10, 'Catégorie', 1, 0, 'L', true);
            $pdf->Cell($colWidths[1], 10, 'Sujets', 1, 0, 'C', true);
            $pdf->Cell($colWidths[2], 10, 'Sessions', 1, 0, 'C', true);
            $pdf->Cell($colWidths[3], 10, 'Durée', 1, 0, 'C', true);
            $pdf->Cell($colWidths[4], 10, '%', 1, 1, 'C', true);

            // Données
            $pdf->SetFont('helvetica', '', 10);
            $fill = false;

            foreach ($results as $item) {
                if ($item['total_minutes'] > 0) {
                    $hours = floor($item['total_minutes'] / 60);
                    $minutes = $item['total_minutes'] % 60;
                    $percentage = $total > 0 ? round(($item['total_minutes'] / $total) * 100, 1) : 0;

                    $pdf->Cell($colWidths[0], 8, $item['category'], 1, 0, 'L', $fill);
                    $pdf->Cell($colWidths[1], 8, $item['subjects_count'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[2], 8, $item['entries_count'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[3], 8, $hours . 'h ' . $minutes . 'm', 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[4], 8, $percentage . '%', 1, 1, 'C', $fill);

                    $fill = !$fill;
                }
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 12);
            $pdf->Cell(0, 15, 'Aucune donnée pour cette période', 0, 1, 'C');
        }

        // Pied de page
        $pdf->Ln(20);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Rapport généré automatiquement par Study Time Tracker', 0, 1, 'C');

        // Sortie du PDF
        $filename = 'rapport_' . $period . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    private function getPeriodLabel($period, $date, $month = null, $year = null) {
        switch ($period) {
            case 'day':
                $dateObj = new DateTime($date);
                return 'Jour du ' . $dateObj->format('d/m/Y');
            case 'week':
                $startDate = new DateTime($date);
                $endDate = new DateTime($date);
                $endDate->modify('+6 days');
                return 'Semaine du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y');
            case 'month':
                if ($month && $year) {
                    $dateObj = new DateTime("$year-$month-01");
                    return 'Mois de ' . $dateObj->format('F Y');
                } else {
                    $dateObj = new DateTime($date);
                    return 'Mois de ' . $dateObj->format('F Y');
                }
            default:
                return $period;
        }
    }

    private function getDateCondition($period, $date, $month = null, $year = null) {
        switch ($period) {
            case 'day':
                return "te.entry_date = '$date'";
            case 'week':
                // Calculer la fin de semaine (6 jours après le début)
                return "te.entry_date >= '$date' AND te.entry_date <= date('$date', '+6 days')";
            case 'month':
                // Si on a month et year fournis, les utiliser directement
                if ($month && $year) {
                    $firstDay = sprintf('%04d-%02d-01', $year, $month);
                    $lastDay = date('Y-m-t', strtotime($firstDay));
                    return "te.entry_date >= '$firstDay' AND te.entry_date <= '$lastDay'";
                } else {
                    // Fallback sur l'ancienne méthode avec date
                    return "te.entry_date >= date('$date', 'start of month') AND te.entry_date <= date('$date', '+1 month', 'start of month', '-1 day')";
                }
            default:
                return "te.entry_date = '$date'";
        }
    }

    private function success($data) {
        return json_encode(['success' => true, 'data' => $data]);
    }

    private function error($message) {
        return json_encode(['success' => false, 'error' => $message]);
    }
}
