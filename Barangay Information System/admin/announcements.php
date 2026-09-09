<?php
session_start();
if ($_SESSION['role'] !== 'admin') header("Location: ../index.php");
/** @var mysqli $conn */
include("../config/db.php");

if (isset($_POST['add_announcement'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $admin_id = $_SESSION['user_id'];
    
    $image_name = "";
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/uploads/" . $image_name);
    }

    $conn->query("INSERT INTO announcements (title, content, category, posted_by, image) 
                  VALUES ('$title', '$content', '$category', '$admin_id', '$image_name')");
    header("Location: announcements.php?msg=added");
    exit;
}

if (isset($_POST['edit_announcement'])) {
    $id = $_POST['announcement_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/uploads/" . $image_name);
        $conn->query("UPDATE announcements SET title='$title', content='$content', category='$category', image='$image_name' WHERE announcement_id='$id'");
    } else {
        $conn->query("UPDATE announcements SET title='$title', content='$content', category='$category' WHERE announcement_id='$id'");
    }
    header("Location: announcements.php?msg=updated");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $res = $conn->query("SELECT image FROM announcements WHERE announcement_id = '$id'");
    $row = $res->fetch_assoc();
    if (!empty($row['image']) && file_exists("../assets/uploads/" . $row['image'])) {
        unlink("../assets/uploads/" . $row['image']);
    }
    $conn->query("DELETE FROM announcements WHERE announcement_id = '$id'");
    header("Location: announcements.php?msg=deleted");
    exit;
}

include("../includes/header.php");
?>

<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --bg-light: #f8f9fc;
    }
    body { background-color: var(--bg-light); }
    .card { border: none; border-radius: 12px; transition: transform 0.2s; }
    .shadow-sm { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important; }
    .form-control, .form-select { border-radius: 8px; padding: 10px 15px; border: 1px solid #d1d3e2; }
    .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.1); }
    .btn-primary { background-color: var(--primary-color); border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; }
    .table thead th { background-color: #f8f9fc; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; color: var(--secondary-color); border-top: none; }
    .badge-pill { padding: 0.5em 1em; border-radius: 50px; font-weight: 500; }
    .img-preview { border-radius: 8px; object-fit: cover; border: 1px solid #eee; }
    .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark mb-1">Announcements</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Announcements</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show d-inline-block py-2 mb-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Action completed successfully!
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-primary">Create New Post</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Headline</label>
                            <input type="text" name="title" class="form-control" placeholder="What's happening?" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Category</label>
                            <select name="category" class="form-select">
                                <option>Public Notice</option>
                                <option>Event</option>
                                <option>Emergency</option>
                                <option>Health</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Details</label>
                            <textarea name="content" class="form-control" rows="5" placeholder="Provide more information here..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Schedule</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted">Featured Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <button type="submit" name="add_announcement" class="btn btn-primary w-100 shadow-sm">
                            <i class="bi bi-megaphone me-2"></i> Publish Announcement
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-dark">Published Announcements</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Preview</th>
                                    <th>Content</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC");
                                if($res->num_rows == 0): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No records found.</td></tr>
                                <?php endif; ?>

                                <?php while($row = $res->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <?php if(!empty($row['image'])): ?>
                                                <img src="../assets/uploads/<?= $row['image'] ?>" class="img-preview" width="50" height="50">
                                            <?php else: ?>
                                                <div class="bg-light rounded text-center text-muted" style="width: 50px; height: 50px; line-height: 50px;">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark mb-0"><?= $row['title'] ?></div>
                                            <div class="text-muted small text-truncate" style="max-width: 250px;"><?= $row['content'] ?></div>
                                        </td>
                                        <td>
                                            <?php 
                                                $badgeClass = "bg-primary-subtle text-primary"; 
                                                if($row['category'] == 'Public Notice') $badgeClass = "bg-danger text-white";
                                                if($row['category'] == 'Emergency') $badgeClass = "bg-danger text-white";
                                                if($row['category'] == 'Event') $badgeClass = "bg-warning text-dark";
                                                if($row['category'] == 'Health') $badgeClass = "bg-info text-dark";
                                            ?>
                                            <span class="badge badge-pill <?= $badgeClass ?>"><?= $row['category'] ?></span>
                                        </td>
                                        <td class="text-muted small">
                                            <?= date('M d, Y', strtotime($row['date_posted'])) ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-outline-primary action-btn edit-btn" 
                                                    data-id="<?= $row['announcement_id'] ?>"
                                                    data-title="<?= htmlspecialchars($row['title']) ?>"
                                                    data-content="<?= htmlspecialchars($row['content']) ?>"
                                                    data-category="<?= $row['category'] ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editModal">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="?delete=<?= $row['announcement_id'] ?>" 
                                               class="btn btn-outline-danger action-btn ms-1" 
                                               onclick="return confirm('Permanent delete this announcement?')">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" enctype="multipart/form-data" class="modal-content shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Update Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-0">
                <input type="hidden" name="announcement_id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Title</label>
                    <input type="text" name="title" id="edit_title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Category</label>
                    <select name="category" id="edit_category" class="form-select">
                        <option>Public Notice</option>
                        <option>Event</option>
                        <option>Emergency</option>
                        <option>Health</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Content</label>
                    <textarea name="content" id="edit_content" class="form-control" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Update Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <div class="form-text text-info">Leave blank if you don't want to change the image.</div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="edit_announcement" class="btn btn-primary px-4 shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('click', function (event) {
    if (event.target.closest('.edit-btn')) {
        const btn = event.target.closest('.edit-btn');
        document.getElementById('edit_id').value = btn.dataset.id;
        document.getElementById('edit_title').value = btn.dataset.title;
        document.getElementById('edit_content').value = btn.dataset.content;
        document.getElementById('edit_category').value = btn.dataset.category;
    }
});
</script>

<?php include("../includes/footer.php"); ?>