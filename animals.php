<?php
include 'config.php';
include 'auth.php';

if (!isAdmin()) {
    echo "Access Denied";
    exit();
}

// ================== LOAD DATA FOR EDIT ==================
$editData = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    
    $stmt = $conn->prepare("SELECT * FROM animals WHERE animal_id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}

// ================== ADD ==================
if (isset($_POST['add'])) {
    $species_id = $_POST['species_id'] ?? '';
    $new_common = trim($_POST['new_common_name'] ?? '');
    $new_scientific = trim($_POST['new_scientific_name'] ?? '');
    
    // validation
    if (!empty($species_id) && !empty($new_common)) {
        die("Choose existing OR new species, not both.");
    }
    
    if (empty($species_id) && empty($new_common)) {
        die("Select or enter a species.");
    }
    
    // new species
    if (!empty($new_common)) {
        if (empty($new_scientific)) {
            die("Enter scientific name.");
        }
        
        // Check if species exists
        $stmt = $conn->prepare("SELECT species_id FROM species WHERE scientific_name = ?");
        $stmt->execute([$new_scientific]);
        $res = $stmt->fetch();
        
        if ($res) {
            $species_id = $res['species_id'];
        } else {
            // Add new species using function
            $stmt = $conn->prepare("SELECT add_species(?, ?, 'Protected')");
            $stmt->execute([$new_common, $new_scientific]);
            $species_id = $conn->lastInsertId();
        }
    }
    
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $health_status = $_POST['health_status'];
    
    // Call add_animal function
    $stmt = $conn->prepare("SELECT add_animal(?, ?, ?, ?)");
    $stmt->execute([$species_id, $gender, $date_of_birth, $health_status]);
    
    echo "Added successfully!";
}

// ================== UPDATE ==================
if (isset($_POST['update'])) {
    $id = $_POST['animal_id'];
    $species_id = $_POST['species_id'];
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $health_status = $_POST['health_status'];
    
    $stmt = $conn->prepare("
        UPDATE animals
        SET species_id = ?, gender = ?, date_of_birth = ?, health_status = ?
        WHERE animal_id = ?
    ");
    $stmt->execute([$species_id, $gender, $date_of_birth, $health_status, $id]);
    
    echo "Updated successfully!";
}

// ================== DELETE ==================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM animals WHERE animal_id = ?");
    $stmt->execute([$id]);
}

// ================== LOAD SPECIES ==================
$speciesList = $conn->query("SELECT * FROM species ORDER BY common_name");

// ================== DISPLAY ==================
$sql = "
    SELECT a.*, s.common_name
    FROM animals a
    JOIN species s ON a.species_id = s.species_id
    ORDER BY a.animal_id
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Animals</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Manage Animals</h2>
    
    <form method="POST">
        <select name="species_id">
            <option value="">Select Existing Species</option>
            <?php
            // Reset the pointer to beginning
            $speciesList = $conn->query("SELECT * FROM species ORDER BY common_name");
            while ($sp = $speciesList->fetch()) { ?>
                <option value="<?php echo $sp['species_id']; ?>"
                <?php if ($editData && $editData['species_id'] == $sp['species_id']) echo "selected"; ?>>
                    <?php echo $sp['common_name']; ?>
                </option>
            <?php } ?>
        </select>
        
        <br><br>
        
        <input type="text" name="new_common_name" placeholder="New Species">
        <input type="text" name="new_scientific_name" placeholder="Scientific Name">
        
        <br><br>
        
        <select name="gender" required>
            <option value="">Gender</option>
            <option value="Male" <?php if ($editData && $editData['gender']=="Male") echo "selected"; ?>>Male</option>
            <option value="Female" <?php if ($editData && $editData['gender']=="Female") echo "selected"; ?>>Female</option>
        </select>
        
        <input type="date" name="date_of_birth" value="<?php echo $editData['date_of_birth'] ?? ''; ?>" required>
        <input type="text" name="health_status" placeholder="Health status" value="<?php echo $editData['health_status'] ?? ''; ?>" required>
        
        <input type="hidden" name="animal_id" value="<?php echo $editData['animal_id'] ?? ''; ?>">
        
        <br><br>
        
        <?php if ($editData) { ?>
            <button type="submit" name="update">Update Animal</button>
        <?php } else { ?>
            <button type="submit" name="add">Add Animal</button>
        <?php } ?>
    </form>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Species</th>
            <th>Gender</th>
            <th>Date of Birth</th>
            <th>Health</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch()) { ?>
        <tr>
            <td><?php echo $row['animal_id']; ?></td>
            <td><?php echo $row['common_name']; ?></td>
            <td><?php echo $row['gender']; ?></td>
            <td><?php echo $row['date_of_birth']; ?></td>
            <td><?php echo $row['health_status']; ?></td>
            <td>
                <a href="animals.php?edit=<?php echo $row['animal_id']; ?>">Edit</a> |
                <a href="animals.php?delete=<?php echo $row['animal_id']; ?>" onclick="return confirm('Delete this animal?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    
    <br>
    <a href="dashboard.php">Back</a>
</div>

</body>
</html>