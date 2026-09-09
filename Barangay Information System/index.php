<?php 
require_once 'config/db.php';

$total_residents = $conn->query("SELECT COUNT(*) FROM residents")->fetch_row()[0] ?? 0;
$Oresidents = $conn->query("SELECT COUNT(*) FROM resident_occupants")->fetch_row()[0] ?? 0;
$allresidents = $total_residents + $Oresidents;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Syete, Granja | Official Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --brgy-green: #008a45;
            --brgy-dark: #1a1a1a;
            --brgy-light: #f8f9fa;
            --transition: all 0.3s ease;
        }

        body { font-family: 'Inter', sans-serif; background: #fff; color: #333; scroll-behavior: smooth; }

        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid #eee; }
        .nav-link { font-weight: 600; color: #444 !important; font-size: 0.9rem; transition: var(--transition); }
        .nav-link:hover, .nav-link.active { color: var(--brgy-green) !important; }
        
        .home-hero {
            height: 80vh;
            background: url('assets/uploads/brgy3.png');
            background-size: cover; background-position: center;
            display: flex; align-items: center; text-align: center; color: white;
        }

        section { padding: 80px 0; }
        .section-title { font-weight: 800; position: relative; margin-bottom: 50px; }
        .section-title::after { content: ''; width: 60px; height: 5px; background: var(--brgy-green); position: absolute; bottom: -15px; left: 0; }
        .section-title.center::after { left: 50%; transform: translateX(-50%); }

        .ann-card { border: none; border-radius: 15px; overflow: hidden; transition: var(--transition); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .ann-card:hover { transform: translateY(-10px); }
        .ann-date { background: var(--brgy-green); color: white; padding: 10px; width: 70px; text-align: center; border-radius: 10px; position: absolute; top: 20px; left: 20px; }

        .official-card { text-align: center; border: none; background: none; }
        .official-img { width: 180px; height: 180px; object-fit: cover; border-radius: 50%; border: 5px solid var(--brgy-light); margin: 0 auto 20px; transition: var(--transition); }
        .official-card:hover .official-img { border-color: var(--brgy-green); transform: scale(1.05); }

        .issuance-box { background: var(--brgy-light); padding: 40px; border-radius: 20px; transition: var(--transition); border: 1px solid transparent; height: 100%; }
        .issuance-box:hover { background: white; border-color: var(--brgy-green); box-shadow: 0 15px 30px rgba(0,0,0,0.05); }
        .icon-circle { width: 60px; height: 60px; background: var(--brgy-green); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 20px; }

        .event-item { border-left: 4px solid var(--brgy-green); padding: 20px; background: var(--brgy-light); margin-bottom: 15px; border-radius: 0 10px 10px 0; transition: var(--transition); }
        .event-item:hover { background: #eef7f2; transform: translateX(10px); }

        .contact-info { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: 100%; }
        
        .footer { background: var(--brgy-dark); color: white; padding: 40px 0; }

    .calendar-container {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .calendar-header {
        background: #0d6efd;
        color: white;
        padding: 20px;
        text-align: center;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        border-bottom: 1px solid #eee;
        border-right: 1px solid #eee;
    }
    .calendar-day-head {
        background: #f8f9fa;
        font-weight: bold;
        text-align: center;
        padding: 10px;
        border-top: 1px solid #eee;
        border-left: 1px solid #eee;
        text-transform: uppercase;
        font-size: 0.8rem;
        color: #666;
    }
    .calendar-day {
        min-height: 110px;
        padding: 10px;
        background: #fff;
        border-top: 1px solid #eee;
        border-left: 1px solid #eee;
        position: relative;
    }
    .calendar-day.today {
        background-color: #f0f7ff;
    }
    .calendar-day.empty {
        background-color: #fafafa;
    }
    .day-number {
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
        display: block;
    }
    .event-tag {
        font-size: 0.7rem;
        padding: 2px 5px;
        background: #e7f1ff;
        color: #0d6efd;
        border-radius: 3px;
        margin-bottom: 3px;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border-left: 3px solid #0d6efd;
    }
    @media (max-width: 768px) {
        .calendar-day { min-height: 80px; padding: 5px; }
        .event-tag { font-size: 0.6rem; }
    }

    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#about">
            <span style="color:var(--brgy-dark)">BRGY</span><span style="color:var(--brgy-green)">SYETE</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#announcements">Announcements</a></li>
                <li class="nav-item"><a class="nav-link" href="#officials">Officials</a></li>
                <li class="nav-item"><a class="nav-link" href="#calendar">Calendar</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                <li class="nav-item"><a class="nav-link btn btn-success ms-lg-3 px-4 rounded-pill" href="login.php">Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<section id="home" class="home-hero">
    <div class="container">
        <img src="assets/uploads/brgylogo2.png" style="width: 10rem; margin-bottom: 20px;">
        <p class="text-uppercase fw-bold mb-2" style="letter-spacing: 3px;">Service with Integrity</p>
        <h1 class="display-2 fw-800 mb-4" style="font-weight: 800;">Welcome to Barangay SYETE</h1>
        <p class="lead mb-5 opacity-75">Your digital gateway to community services, news, and official records.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="login.php" class="btn btn-success btn-lg px-5 rounded-pill">Log In</a>
            <a href="#about" class="btn btn-outline-light btn-lg px-5 rounded-pill">Learn More</a>
        </div>
    </div>
</section>

<section id="announcements">
    <div class="container">
        <h2 class="section-title center text-center">Latest Announcements</h2>
        <div class="row g-4 mt-2">
            <?php
            $sql = "SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 3";
            $result = $conn->query($sql);
            if($result && $result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
            <div class="col-md-4">
                <div class="card ann-card h-100">
                    <div class="position-relative">
                        <img src="assets/uploads/<?= $row['image'] ?? 'news.jpg' ?>" class="card-img-top" style="height: 220px; object-fit: cover;">
                        <div class="ann-date">
                            <span class="fw-bold d-block"><?= date('d', strtotime($row['date_posted'])) ?></span>
                            <small><?= date('M', strtotime($row['date_posted'])) ?></small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="fw-bold"><?= $row['title'] ?></h5>
                        <p class="text-muted small"><?= substr($row['content'], 0, 100) ?>...</p>
                        <a href="login.php" class="text-success fw-bold text-decoration-none small">READ MORE <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endwhile; else: echo "<p class='text-center'>No announcements posted yet.</p>"; endif; ?>
        </div>
    </div>
</section>

<section id="officials" class="bg-light">
    <div class="container text-center">
        <h2 class="section-title center">Barangay Officials</h2>
        <p class="text-muted mb-5">Meet the dedicated leaders of our community for 2026-2028.</p>
        <div class="row g-4">
            <div class="col-12 mb-4">
                <div class="official-card">
                    <img src="assets/official/2.jpg" class="official-img" alt="Official">
                    <h5 class="fw-bold mb-0">HON. JOSHUA GARCIA</h5>
                    <p class="text-success fw-bold">Punong Barangay</p>
                </div>
            </div>
            <?php for($i=3; $i<=6; $i++): ?>
            <div class="col-md-3">
                <div class="official-card">
                    <img src="assets/official/<?= $i ?>.jpg" class="official-img" style="width:140px; height:140px;">
                    <h6 class="fw-bold mb-0">Barangay Official</h6>
                    <p class="text-muted small">Sangguniang Kabataan</p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section id="calendar" class="bg-light py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">Event Calendar</h2>
        
        <div class="calendar-container">
            <?php
            $month = isset($_GET['m']) ? $_GET['m'] : date('m');
            $year = isset($_GET['y']) ? $_GET['y'] : date('Y');

            $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
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

            <div class="calendar-header">
                <h3 class="mb-0"><?php echo $monthName . " " . $year; ?></h3>
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
                            echo "<div class='event-tag' title='".$event['title']."'>";
                            echo $event['title'];
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
        
        <div class="text-center mt-3">
            <small class="text-muted">* Events are pulled from the announcements table based on date posted.</small>
        </div>
    </div>
</section>

<section id="about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <img src="assets/uploads/brgy.png" class="img-fluid rounded-4 shadow" alt="About">
            </div>
            <div class="col-md-6 ps-md-5 mt-4 mt-md-0">
                <h2 class="section-title">About Our Barangay</h2>
                <p class="lead">Barangay Syete is a community built on the pillars of transparency, safety, and digital innovation.</p>
                <p class="text-muted">Established in 1947, we have grown from a small rural area into a bustling community in Lipa City. Our mission is to provide efficient public service through modernized systems while preserving our rich Batangueño heritage.</p>
                <div class="row mt-4">
                    <div class="col-6">
                        <h4 class="fw-bold text-success"><?= number_format($allresidents) ?></h4>
                        <p class="small text-muted">Registered Residents</p>
                    </div>
                    <div class="col-6">
                        <h4 class="fw-bold text-success">24/7</h4>
                        <p class="small text-muted">Emergency Response</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contact" class="bg-light">
    <div class="container">
        <h2 class="section-title center text-center">Get In Touch</h2>
        <div class="row g-4 mt-4">
            <div class="col-md-4">
                <div class="contact-info text-center">
                    <div class="icon-circle mx-auto"><i class="bi bi-geo-alt"></i></div>
                    <h5>Our Address</h5>
                    <p class="text-muted small">Barangay Syete, Granja, Lipa City, Batangas, 4217</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info text-center">
                    <div class="icon-circle mx-auto"><i class="bi bi-telephone"></i></div>
                    <h5>Phone Number</h5>
                    <p class="text-muted small">+63 (123) 456 7890<br>(043) 123 4567</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info text-center">
                    <div class="icon-circle mx-auto"><i class="bi bi-envelope"></i></div>
                    <h5>Email Address</h5>
                    <p class="text-muted small">info@brgysyete.gov.ph<br>support@brgysyete.gov.ph</p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container text-center">
        <h4 class="fw-bold mb-3">BRGY<span class="text-success">SYETE</span></h4>
        <p class="small opacity-50">© 2026 Barangay Registry System. All Rights Reserved.</p>
        <div class="d-flex justify-content-center gap-3 mt-3">
            <a href="#" class="text-white fs-5"><i class="bi bi-facebook"></i></a>
            <a href="#" class="text-white fs-5"><i class="bi bi-twitter"></i></a>
            <a href="#" class="text-white fs-5"><i class="bi bi-globe"></i></a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>