<?php
include 'config.php';
include 'auth.php';

// فقط Admin
if (!isAdmin()) {
    echo "Access Denied";
    exit();
}

// ================== LOAD DATA FOR EDIT ==================
$editData = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    
    $stmt = $conn->prepare("SELECT * FROM species WHERE species_id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}

// ================== ADD USING FUNCTION ==================
if (isset($_POST['add'])) {
    $common_name = trim($_POST['common_name']);
    $scientific_name = trim($_POST['scientific_name']);
    $status = trim($_POST['conservation_status']);
    
    // Check if species exists
    $check = $conn->prepare("SELECT species_id FROM species WHERE scientific_name = ?");
    $check->execute([$scientific_name]);
    
    if ($check->fetch()) {
        echo "Species already exists!";
    } else {
        // Call PostgreSQL function
        $stmt = $conn->prepare("SELECT add_species(?, ?, ?)");
        $stmt->execute([$common_name, $scientific_name, $status]);
        echo "Species added successfully!";
    }
}

// ================== UPDATE ==================
if (isset($_POST['update'])) {
    $id = $_POST['species_id'];
    $common_name = $_POST['common_name'];
    $scientific_name = $_POST['scientific_name'];
    $status = $_POST['conservation_status'];
    
    $stmt = $conn->prepare("
        UPDATE species
        SET common_name = ?,
            scientific_name = ?,
            conservation_status = ?
        WHERE species_id = ?
    ");
    $stmt->execute([$common_name, $scientific_name, $status, $id]);
    
    echo "Updated successfully!";
}

// ================== DELETE ==================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM species WHERE species_id = ?");
    $stmt->execute([$id]);
}

// ================== DISPLAY ==================
$result = $conn->query("SELECT * FROM species ORDER BY species_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Species</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Manage Species</h2>
    
    <form method="POST">
        <input type="text"
               name="common_name"
               placeholder="Common Name"
               value="<?php echo $editData['common_name'] ?? ''; ?>"
               required>
        
        <input type="text"
               name="scientific_name"
               placeholder="Scientific Name"
               value="<?php echo $editData['scientific_name'] ?? ''; ?>"
               required>
        
        <input type="text"
               name="conservation_status"
               placeholder="Conservation Status"
               value="<?php echo $editData['conservation_status'] ?? ''; ?>"
               required>
        
        <input type="hidden"
               name="species_id"
               value="<?php echo $editData['species_id'] ?? ''; ?>">
        
        <?php if ($editData) { ?>
            <button type="submit" name="update">Update Species</button>
        <?php } else { ?>
            <button type="submit" name="add">Add Species</button>
        <?php } ?>
    </form>
    
    <br>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Common Name</th>
            <th>Scientific Name</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch()) { ?>
        <tr>
            <td><?php echo $row['species_id']; ?></td>
            <td><?php echo $row['common_name']; ?></td>
            <td><?php echo $row['scientific_name']; ?></td>
            <td><?php echo $row['conservation_status']; ?></td>
            <td>
                <a href="species.php?edit=<?php echo $row['species_id']; ?>">Edit</a> |
                <a href="species.php?delete=<?php echo $row['species_id']; ?>" onclick="return confirm('Delete this species?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>