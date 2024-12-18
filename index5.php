<?php

session_start();
require_once 'auth.php';

// DUMB COMMENT
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$host = 'localhost'; 
$dbname = 'movies'; 
$user = 'kasie'; 
$pass = 'kasie';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle movie search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT id, title, director, genre, release_year FROM movies WHERE title LIKE :search OR director LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['title']) && isset($_POST['director']) && isset($_POST['genre']) && isset($_POST['release_year'])) {
        // Insert new entry
        $title = htmlspecialchars($_POST['title']);
        $director = htmlspecialchars($_POST['director']);
        $genre = htmlspecialchars($_POST['genre']);
        $release_year = htmlspecialchars($_POST['release_year']);

        
        $insert_sql = 'INSERT INTO movies (title, director, genre, release_year) VALUES (:title, :director, :genre, :release_year)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['title' => $title, 'director' => $director, 'genre' => $genre, 'release_year' => $release_year]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];
        
        $delete_sql = 'DELETE FROM movies WHERE id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['id' => $delete_id]);
    }
}

// Get all movies for main table
$sql = 'SELECT id, title, director, genre, release_year FROM movies';
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kasie's Movie Banning and Bridge Building</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">Kasie's Movie Banning and Bridge Building</h1>
        <p class="hero-subtitle">"Because nothing brings a community together like collectively deciding what others shouldn't watch!"</p>
        
        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Search for a Movie to Ban</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by Title:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Titles</th>
                                    <th>Director</th>
                                    <th>Genre</th>
                                    <th>Realease Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['director']); ?></td>
                                    <td><?php echo htmlspecialchars($row['genre']); ?></td>
                                    <td><?php echo htmlspecialchars($row['release_year']); ?></td>

                                    <td>
                                        <form action="index5.php" method="post" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <input type="submit" value="Ban!">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No movies found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table section with container -->
    <div class="table-container">
        <h2>All Movies in Database</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titles</th>
                    <th>Director</th>
                    <th>Genre</th>
                    <th>Release Year</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['director']); ?></td>
                    <td><?php echo htmlspecialchars($row['genre']); ?></td>
                    <td><?php echo htmlspecialchars($row['release_year']); ?></td>

                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Ban!">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Form section with container -->
    <div class="form-container">
        <h2>Condemn a Movie Today</h2>
        <form action="index5.php" method="post">
            <label for="title">Movie Title:</label>
            <input type="text" id="title" name="title" required>
            <br><br>
            <label for="director">Director:</label>
            <input type="text" id="director" name="director" required>
            <br><br>
            <label for="genre">Genre:</label>
            <input type="text" id="genre" name="genre" required>
            <br><br>
            <label for="release_year">Release Year:</label>
            <input type="text" id="release_year" name="release_year" required>
            <br><br>
            <input type="submit" value="Condemn Movie">
        </form>
    </div>
</body>
</html>