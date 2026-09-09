<?php
if(session_status() === PHP_SESSION_NONE) session_start();
if ($_SESSION['role'] !== 'resident') header("Location: ../login.php");

/** @var mysqli $conn */
require_once '../config/db.php'; 

$user_id = $_SESSION['user_id'] ?? 0;

if (isset($_POST['action']) && $_POST['action'] === 'toggle_like' && isset($_POST['id'])) {
    $ann_id = intval($_POST['id']);
    
    $check_like = $conn->query("SELECT * FROM announcement_likes WHERE announcement_id = $ann_id AND user_id = $user_id");
    
    if ($check_like->num_rows > 0) {
        $conn->query("DELETE FROM announcement_likes WHERE announcement_id = $ann_id AND user_id = $user_id");
        $conn->query("UPDATE announcements SET likes = GREATEST(0, likes - 1) WHERE announcement_id = $ann_id");
        $status = 'unliked';
    } else {
        $conn->query("INSERT INTO announcement_likes (announcement_id, user_id) VALUES ($ann_id, $user_id)");
        $conn->query("UPDATE announcements SET likes = likes + 1 WHERE announcement_id = $ann_id");
        $status = 'liked';
    }

    $res = $conn->query("SELECT likes FROM announcements WHERE announcement_id = $ann_id");
    $new_count = $res->fetch_assoc()['likes'];

    echo json_encode(['status' => $status, 'count' => $new_count]);
    exit;
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css">
<style>
    .res-feed-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    .res-feed-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .res-card-img-wrapper {
        overflow: hidden;
        height: 200px;
        background-color: #f8f9fa;
    }
    .res-card-img-wrapper img {
        transition: transform 0.5s ease;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .res-feed-card:hover .card-img-top {
        transform: scale(1.1);
    }
    .like-btn {
        transition: all 0.2s ease;
        border: none;
        background: none;
        color: #adb5bd;
    }
    .like-btn:hover {
        color: #dc3545;
        transform: scale(1.2);
    }
    .like-btn.active {
        color: #dc3545;
    }
</style>
<div class="container py-5">
    <div class="res-feed-header mb-5 text-center">
        <h2 class="fw-bold display-5">Community Bulletin</h2>
        <p class="text-muted mx-auto" style="max-width: 600px;">Stay updated with the latest news, events, and official notices from our Barangay office.</p>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        $res = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC");
        
        $current_user_id = isset($user_id) ? $user_id : 0;

        if ($res && $res->num_rows > 0):
            while($a = $res->fetch_assoc()): 
                $id = $a['announcement_id']; 
                $content = $a['content'];
                $likes = isset($a['likes']) ? $a['likes'] : 0;
                
                $preview = $content;

                $is_liked = false;
                if ($current_user_id > 0) {
                    $liked_query = $conn->query("SELECT 1 FROM announcement_likes WHERE announcement_id = $id AND user_id = $current_user_id");
                    $is_liked = ($liked_query && $liked_query->num_rows > 0);
                }

                $img_name = $a['image'];
                $img_path = "../assets/uploads/" . $img_name;
                $image_exists = (!empty($img_name) && file_exists($img_path));
        ?>
            <div class="col">
                <div class="card h-100 res-feed-card border-0 shadow-sm">
                    <div class="res-card-img-wrapper position-relative" style="height: 200px; overflow: hidden; background: #f8f9fa;">
                        <?php if($image_exists): ?>
                            <img src="<?= $img_path ?>" class="card-img-top h-100 w-100 object-fit-cover" alt="Announcement Image">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                <i class="bi bi-megaphone display-4 opacity-25"></i>
                            </div>
                        <?php endif; ?>
                        <span class="badge bg-primary position-absolute top-0 start-0 m-3 shadow-sm"><?= htmlspecialchars($a['category']) ?></span>
                    </div>

                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 text-muted small">
                            <i class="bi bi-calendar3 me-2"></i>
                            <?= date('M d, Y', strtotime($a['date_posted'])) ?>
                        </div>
                        
                        <h5 class="card-title fw-bold text-dark mb-3"><?= htmlspecialchars($a['title']) ?></h5>
                        
                        <div class="content-wrapper">
                            <div class="collapse show text-secondary" id="preview-<?= $id ?>">
                                <?= nl2br(htmlspecialchars($preview)) ?>
                            </div>

                                <div class="collapse text-secondary" id="content-<?= $id ?>">
                                    <?= nl2br(htmlspecialchars($content)) ?>
                                </div>
                                <br>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent border-top-0 pb-4 px-4">
                        <hr class="mt-0 mb-3 opacity-10">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <button class="like-btn me-2 p-0 border-0 bg-transparent <?= $is_liked ? 'text-danger' : 'text-muted' ?>" 
                                        onclick="handleLike(<?= $id ?>, this)">
                                    <i class="bi <?= $is_liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                </button>
                                <span class="small fw-bold text-muted"><span id="like-count-<?= $id ?>"><?= $likes ?></span> Likes</span>
                            </div>
                            <div class="d-flex align-items-center text-primary">
                                <span class="small fw-semibold me-1">Official</span>
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; 
        else: ?>
            <div class="col-12 text-center py-5">
                <div class="bg-light rounded-4 py-5">
                    <i class="bi bi-mailbox2 text-muted display-1"></i>
                    <p class="mt-3 text-muted">No announcements have been posted yet.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleBtnText(btn) {
    setTimeout(() => {
        const isExpanded = btn.getAttribute('aria-expanded') === 'true';
        btn.innerHTML = isExpanded ? 
            'See Less <i class="bi bi-chevron-up ms-1"></i>' : 
            'Read More <i class="bi bi-chevron-down ms-1"></i>';
    }, 50);
}

function handleLike(id, btn) {
    const formData = new FormData();
    formData.append('action', 'toggle_like');
    formData.append('id', id);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success' || data.count !== undefined) {
            document.getElementById('like-count-' + id).innerText = data.count;
            const icon = btn.querySelector('i');
            if(data.status === 'liked') {
                btn.classList.add('text-danger');
                btn.classList.remove('text-muted');
                icon.classList.replace('bi-heart', 'bi-heart-fill');
            } else {
                btn.classList.remove('text-danger');
                btn.classList.add('text-muted');
                icon.classList.replace('bi-heart-fill', 'bi-heart');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?php include '../includes/footer.php'; ?>