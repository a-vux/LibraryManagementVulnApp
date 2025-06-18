<?php

$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';

$sql = "SELECT * FROM books WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND title LIKE :search";
    $params[':search'] = '%' . $search . '%';
}
if (!empty($category_id)) {
    $sql .= " AND category_id = :category_id";
    $params[':category_id'] = $category_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

</style>
<div class="container mt-5">
    <h2>All Books</h2>

    <form method="GET" class="row g-3 mb-4">
        <input type="hidden" name="page" value="books.php" />
        <div class="col-md-6">
            <input type="text" name="search" placeholder="Search by title" value="<?= htmlspecialchars($search) ?>" class="form-control" />
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100">Filter</button>
        </div>
    </form>
    <?php if (!empty($search)): ?>
        <div class="alert alert-info">
            You searched for: <strong><?= htmlspecialchars($search) ?></strong>
        </div>
    <?php endif; ?>
    <div class="row">
        <?php foreach ($books as $book): ?>
            <div class="col-md-3 mb-4">
                <a href="index.php?page=book_detail.php&id=<?= $book['id'] ?>" class="text-decoration-none text-dark">
                    <div class="card h-100">
                        <?php if ($book['cover_image']): ?>
                            <img src="<?= htmlspecialchars($book['cover_image']) ?>" class="card-img-top book-cover" alt="Cover">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                            <p class="card-text">By <?= htmlspecialchars($book['author']) ?></p>
                            <p class="card-text"><strong>$<?= htmlspecialchars($book['price']) ?></strong></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>