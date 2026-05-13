<?php
include 'config.php';
include 'auth.php';

if (!isAdmin() && !isRanger()) {
    echo "Access Denied";
    exit();
}

// Add Observation
if (isset($_POST['add'])) {
    $animal_id = $_POST['animal_id'];
    $ranger_id = $_POST['ranger_id'];
    $area_id = $_POST['area_id'];
    $observation_date = $_POST['observation_date'];
    $behavior_notes = $_POST['behavior_notes'];
    $health_condition = $_POST['health_condition'];
    
    $stmt = $conn->prepare("
        INSERT INTO observations (animal_id, ranger_id, area_id, observation_date, behavior_notes, health_condition)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$animal_id, $ranger_id, $area_id, $observation_date, $behavior_notes, $health_condition]);
}

// Delete Observation
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM observations WHERE observation_id = ?");
    $stmt->execute([$id]);
}

// Get data for dropdowns
$animals = $conn->query("
    SELECT a.animal_id, s.common_name
    FROM animals a
    JOIN species s ON a.species_id = s.species_id
    ORDER BY s.common_name
");

$rangers = $conn->query("
    SELECT r.ranger_id, u.full_name
    FROM rangers r
    JOIN users u ON r.user_id = u.user_id
");

$areas = $conn->query("SELECT * FROM protected_areas");

// Get all observations
$result = $conn->query("
    SELECT 
        o.observation_id,
        s.common_name,
        u.full_name AS ranger_name,
        p.area_name,
        o.observation_date,
        o.behavior_notes,
        o.health_condition
    FROM observations o
    JOIN animals a ON o.animal_id = a.animal_id
    JOIN species s ON a.species_id = s.species_id
    JOIN rangers r ON o.ranger_id = r.ranger_id
    JOIN users u ON r.user_id = u.user_id
    JOIN protected_areas p ON o.area_id = p.area_id
    ORDER BY o.observation_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Observations</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Manage Observations</h2>
    
    <form method="POST">
        <select name="animal_id" required>
            <option value="">Select Animal</option>
            <?php while ($a = $animals->fetch()) { ?>
                <option value="<?php echo $a['animal_id']; ?>">
                    <?php echo htmlspecialchars($a['common_name']); ?>
                </option>
            <?php } ?>
        </select>
        
        <select name="ranger_id" required>
            <option value="">Select Ranger</option>
            <?php while ($r = $rangers->fetch()) { ?>
                <option value="<?php echo $r['ranger_id']; ?>">
                    <?php echo htmlspecialchars($r['full_name']); ?>
                </option>
            <?php } ?>
        </select>
        
        <select name="area_id" required>
            <option value="">Select Protected Area</option>
            <?php while ($p = $areas->fetch()) { ?>
                <option value="<?php echo $p['area_id']; ?>">
                    <?php echo htmlspecialchars($p['area_name']); ?>
                </option>
            <?php } ?>
        </select>
        
        <input type="date" name="observation_date" required>
        <textarea name="behavior_notes" placeholder="Behavior Notes" required></textarea>
        <input type="text" name="health_condition" placeholder="Health Condition" required>
        
        <button type="submit" name="add">Add Observation</button>
    </form>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Species</th>
            <th>Ranger</th>
            <th>Area</th>
            <th>Date</th>
            <th>Behavior Notes</th>
            <th>Health Condition</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch()) { ?>
        <tr>
            <td><?php echo $row['observation_id']; ?></td>
            <td><?php echo htmlspecialchars($row['common_name']); ?></td>
            <td><?php echo htmlspecialchars($row['ranger_name']); ?></td>
            <td><?php echo htmlspecialchars($row['area_name']); ?></td>
            <td><?php echo $row['observation_date']; ?></td>
            <td><?php echo htmlspecialchars($row['behavior_notes']); ?></td>
            <td><?php echo htmlspecialchars($row['health_condition']); ?></td>
            <td>
                <?php if (isAdmin()) { ?>
                    <a href="observations.php?delete=<?php echo $row['observation_id']; ?>" onclick="return confirm('Delete this observation?')">Delete</a>
                <?php } else { ?>
                    View Only
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>
    
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>