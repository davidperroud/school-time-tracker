<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Translation.php';
require_once __DIR__ . '/../vendor/autoload.php';

class ApiController {
    private $db;
    private $translation;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->translation = new Translation();
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
            case 'users':
                return $this->getUsers();
            case 'stats':
                return $this->getStats();
            case 'progress':
                return $this->getProgress();
            case 'recent_entries':
                return $this->getRecentEntries();
            default:
                return $this->error($this->translation->t('ui.messages.error'));
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

    private function getUsers() {
        $sql = "SELECT id, username, language_preference, is_admin, created_at, last_login
                FROM users
                ORDER BY username";

        return $this->success($this->db->fetchAll($sql));
    }

    private function getStats() {
        $subjectId = $_GET['subject_id'] ?? null;
        $days = $_GET['days'] ?? 30;

        if (!$subjectId) {
            return $this->error($this->translation->t('ui.messages.error'));
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

    private function getProgress() {
        // Récupérer les 7 derniers jours avec données
        $sql = "SELECT
                    c.id,
                    c.name as category,
                    c.color,
                    COALESCE(SUM(te.duration_minutes), 0) as total_minutes
                FROM categories c
                LEFT JOIN subjects s ON c.id = s.category_id
                LEFT JOIN time_entries te ON s.id = te.subject_id 
                    AND te.entry_date >= date('now', '-7 days')
                GROUP BY c.id, c.name, c.color
                HAVING COALESCE(SUM(te.duration_minutes), 0) > 0
                ORDER BY total_minutes DESC";

        $results = $this->db->fetchAll($sql);

        return $this->success([
            'summary' => $results
        ]);
    }

    private function getRecentEntries() {
        $limit = $_GET['limit'] ?? 10;
        
        $sql = "SELECT te.*, s.name as subject_name, c.name as category_name, c.color
                FROM time_entries te
                JOIN subjects s ON te.subject_id = s.id
                JOIN categories c ON s.category_id = c.id
                ORDER BY te.created_at DESC
                LIMIT ?";

        $results = $this->db->fetchAll($sql, [$limit]);

        return $this->success($results);
    }

    private function generatePDF() {
        $period = $_GET['period'] ?? 'day';
        $date = $_GET['date'] ?? date('Y-m-d');
        $month = $_GET['month'] ?? null;
        $year = $_GET['year'] ?? null;

        // Colors - Slate + Teal
        $primaryColor = [13, 148, 136]; // Teal #0d9488
        $darkColor = [15, 23, 42];      // Slate #0f172a
        $gray100 = [248, 250, 252];     // Light gray
        $gray200 = [226, 232, 240];      // Border gray
        $gray500 = [100, 116, 139];      // Secondary text
        $gray600 = [71, 85, 105];       // Muted text

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

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('School Time Tracker');
        $pdf->SetAuthor('School Time Tracker');
        $pdf->SetTitle($this->translation->t('pdf.title'));
        $pdf->SetSubject('Study Time Report');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins(24, 24, 24);
        $pdf->SetAutoPageBreak(true, 24);

        $pdf->AddPage();

        // Header with clean design
        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Rect(0, 0, 210, 45, 'F');
        
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetY(12);
        $pdf->Cell(0, 12, $this->translation->t('pdf.title'), 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetY(26);
        $periodLabel = $this->getPeriodLabel($period, $date, $month, $year);
        $pdf->Cell(0, 8, $periodLabel, 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetY(36);
        $pdf->Cell(0, 6, $this->translation->t('pdf.generated_on') . ' ' . date('d/m/Y H:i'), 0, 1, 'C');

        // Reset text color
        $pdf->SetTextColor($darkColor[0], $darkColor[1], $darkColor[2]);

        $pdf->SetY(55);

        // Total card
        if ($total > 0) {
            $hours = floor($total / 60);
            $minutes = $total % 60;
            
            $pdf->SetFillColor($gray100[0], $gray100[1], $gray100[2]);
            $pdf->SetDrawColor($gray200[0], $gray200[1], $gray200[2]);
            $pdf->Rect(24, $pdf->GetY(), 162, 18, 'FD');
            
            $pdf->SetFont('helvetica', '', 11);
            $pdf->SetTextColor($gray500[0], $gray500[1], $gray500[2]);
            $pdf->SetXY(30, $pdf->GetY() + 4);
            $pdf->Cell(60, 10, $this->translation->t('pdf.total'), 0, 0, 'L');
            
            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetXY(90, $pdf->GetY() + 1);
            $pdf->Cell(90, 12, $hours . 'h ' . $minutes . 'm', 0, 1, 'R');
            
            $pdf->SetY($pdf->GetY() + 14);
        }

        // Table
        if ($total > 0 && count($results) > 0) {
            $pdf->SetTextColor($darkColor[0], $darkColor[1], $darkColor[2]);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetDrawColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);

            $colWidths = [65, 22, 22, 28, 25];
            $rowHeight = 9;

            $pdf->Cell($colWidths[0], $rowHeight + 2, $this->translation->t('pdf.category'), 1, 0, 'L');
            $pdf->Cell($colWidths[1], $rowHeight + 2, $this->translation->t('pdf.subjects'), 1, 0, 'C');
            $pdf->Cell($colWidths[2], $rowHeight + 2, $this->translation->t('pdf.sessions'), 1, 0, 'C');
            $pdf->Cell($colWidths[3], $rowHeight + 2, $this->translation->t('pdf.duration'), 1, 0, 'C');
            $pdf->Cell($colWidths[4], $rowHeight + 2, $this->translation->t('pdf.percent'), 1, 1, 'C');

            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor($darkColor[0], $darkColor[1], $darkColor[2]);
            $fill = true;
            $fillColor = $gray100;

            foreach ($results as $item) {
                if ($item['total_minutes'] > 0) {
                    $hours = floor($item['total_minutes'] / 60);
                    $minutes = $item['total_minutes'] % 60;
                    $percentage = $total > 0 ? round(($item['total_minutes'] / $total) * 100, 1) : 0;

                    if ($fill) {
                        $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                    }

                    $pdf->Cell($colWidths[0], $rowHeight, $item['category'], 1, 0, 'L', true);
                    $pdf->Cell($colWidths[1], $rowHeight, $item['subjects_count'], 1, 0, 'C', true);
                    $pdf->Cell($colWidths[2], $rowHeight, $item['entries_count'], 1, 0, 'C', true);
                    $pdf->Cell($colWidths[3], $rowHeight, $hours . 'h ' . $minutes . 'm', 1, 0, 'C', true);
                    $pdf->Cell($colWidths[4], $rowHeight, $percentage . '%', 1, 1, 'C', true);

                    $fill = !$fill;
                }
            }
        } else {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetTextColor($gray500[0], $gray500[1], $gray500[2]);
            $pdf->Cell(0, 30, $this->translation->t('pdf.no_data'), 0, 1, 'C');
        }

        // Footer
        $pdf->SetY(-30);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor($gray500[0], $gray500[1], $gray500[2]);
        $pdf->Cell(0, 5, $this->translation->t('pdf.auto_generated'), 0, 1, 'C');
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, 'School Time Tracker — created with love by davidperroud.com', 0, 1, 'C');

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
