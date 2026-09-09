<?php 
session_start();
if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");
/** @var mysqli $conn */

require_once '../config/db.php';
include '../includes/header.php';

$month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('m');
$year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$prevMonth = date('m', strtotime("-1 month", $firstDayOfMonth));
$prevYear = date('Y', strtotime("-1 month", $firstDayOfMonth));
$nextMonth = date('m', strtotime("+1 month", $firstDayOfMonth));
$nextYear = date('Y', strtotime("+1 month", $firstDayOfMonth));

$numberDays = date('t', $firstDayOfMonth);
$dateComponents = getdate($firstDayOfMonth);
$monthName = $dateComponents['month'];
$dayOfWeek = $dateComponents['wday']; 
$today = date('Y-m-d');

$events = [];
$sql = "SELECT * FROM announcements WHERE MONTH(date_posted) = '$month' AND YEAR(date_posted) = '$year'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $dateKey = date('Y-m-d', strtotime($row['date_posted']));
        $events[$dateKey][] = $row;
    }
}
?>

<style>
    :root {
        --bms-primary: #059669; 
        --bms-bg: #f8fafc;
    }

    .calendar-card {
        border: none;
        border-radius: 15px;
        background: #fff;
    }

    .calendar-header-toolbar {
        background: white;
        padding: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        border-radius: 15px 15px 0 0;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background-color: #e2e8f0; 
        gap: 1px;
        border: 1px solid #e2e8f0;
    }

    .calendar-day-head {
        background: #f8fafc;
        padding: 12px;
        text-align: center;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        color: #64748b;
        letter-spacing: 0.05em;
    }

    .calendar-day {
        min-height: 120px;
        background: white;
        padding: 8px;
        transition: background 0.2s;
    }

    .calendar-day:hover {
        background: #fdfdfd;
    }

    .calendar-day.today {
        background: #f0fdf4; 
    }
    
    .calendar-day.today .day-number {
        background: var(--bms-primary);
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .calendar-day.empty {
        background: #f8fafc;
    }

    .day-number {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
        display: inline-block;
    }

    .event-tag {
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 4px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        text-decoration: none;
        font-weight: 500;
        border-left: 3px solid transparent;
    }

    .tag-general { background: #e0f2fe; color: #0369a1; border-left-color: #0ea5e9; }
    .tag-emergency { background: #fee2e2; color: #b91c1c; border-left-color: #ef4444; }
    .tag-event { background: #fef3c7; color: #92400e; border-left-color: #f59e0b; }
    .tag-holiday { background: #dcfce7; color: #15803d; border-left-color: #22c55e; }

    @media (max-width: 768px) {
        .calendar-day { min-height: 80px; }
        .calendar-day-head { font-size: 0.6rem; padding: 5px; }
    }
</style>

<div class="container-fluid py-4 bg-light">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-0">Barangay Calendar</h3>
                    <p class="text-muted small mb-0">Scheduled announcements and community activities</p>
                </div>
                <a href="announcements.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Add New Event
                </a>
            </div>

            <div class="card calendar-card shadow-sm">
                <div class="calendar-header-toolbar d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0 text-dark"><?php echo $monthName . " " . $year; ?></h4>
                    <div class="btn-group shadow-sm">
                        <a href="?m=<?php echo $prevMonth; ?>&y=<?php echo $prevYear; ?>" class="btn btn-white border"><i class="bi bi-chevron-left"></i></a>
                        <a href="?m=<?php echo date('m'); ?>&y=<?php echo date('Y'); ?>" class="btn btn-white border fw-bold text-uppercase small">Today</a>
                        <a href="?m=<?php echo $nextMonth; ?>&y=<?php echo $nextYear; ?>" class="btn btn-white border"><i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>

                <div class="calendar-grid">
                    <div class="calendar-day-head">Sun</div>
                    <div class="calendar-day-head">Mon</div>
                    <div class="calendar-day-head">Tue</div>
                    <div class="calendar-day-head">Wed</div>
                    <div class="calendar-day-head">Thu</div>
                    <div class="calendar-day-head">Fri</div>
                    <div class="calendar-day-head">Sat</div>

                    <?php
                    for ($i = 0; $i < $dayOfWeek; $i++) {
                        echo '<div class="calendar-day empty"></div>';
                    }

                    $currentDay = 1;
                    while ($currentDay <= $numberDays) {
                        $dateString = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);
                        $isToday = ($dateString == $today) ? 'today' : '';
                        
                        echo "<div class='calendar-day $isToday'>";
                        echo "<span class='day-number'>$currentDay</span>";

                        if (isset($events[$dateString])) {
                            foreach ($events[$dateString] as $event) {
                                $catClass = 'tag-general';
                                if($event['category'] == 'Emergency') $catClass = 'tag-emergency';
                                if($event['category'] == 'Event') $catClass = 'tag-event';
                                if($event['category'] == 'Holiday') $catClass = 'tag-holiday';

                                echo "<div class='event-tag $catClass' title='".$event['content']."'>";
                                echo "<i class='bi bi-dot'></i>" . htmlspecialchars($event['title']);
                                echo "</div>";
                            }
                        }

                        echo "</div>";

                        $currentDay++;
                        $dayOfWeek++;
                    }

                    while ($dayOfWeek % 7 != 0) {
                        echo '<div class="calendar-day empty"></div>';
                        $dayOfWeek++;
                    }
                    ?>
                </div>
            </div>

            <div class="mt-4 d-flex flex-wrap gap-3 justify-content-center">
                <span class="small text-muted"><i class="bi bi-circle-fill text-info me-1" style="font-size: 8px;"></i> General</span>
                <span class="small text-muted"><i class="bi bi-circle-fill text-warning me-1" style="font-size: 8px;"></i> Event</span>
                <span class="small text-muted"><i class="bi bi-circle-fill text-danger me-1" style="font-size: 8px;"></i> Emergency</span>
                <span class="small text-muted"><i class="bi bi-circle-fill text-success me-1" style="font-size: 8px;"></i> Holiday</span>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>