<?php
include 'config.php';
include 'auth.php';

// Report 1: Total Animals per Species
$report1 = $conn->query("
    SELECT s.common_name, COUNT(a.animal_id) AS total_animals
    FROM species s
    LEFT JOIN animals a ON s.species_id = a.species_id
    GROUP BY s.common_name
");

// Report 2: Threatened Species
$report2 = $conn->query("
    SELECT s.common_name, s.conservation_status
    FROM species s
    WHERE s.conservation_status IN ('Endangered', 'Critically Endangered', 'Vulnerable', 'Endemic', 'Protected')
    ORDER BY s.conservation_status
");

// Report 3: Observation Details
$report3 = $conn->query("
    SELECT 
        o.observation_id,
        s.common_name,
        u.full_name AS ranger_name,
        p.area_name,
        o.observation_date
    FROM observations o
    JOIN animals a ON o.animal_id = a.animal_id
    JOIN species s ON a.species_id = s.species_id
    JOIN rangers r ON o.ranger_id = r.ranger_id
    JOIN users u ON r.user_id = u.user_id
    JOIN protected_areas p ON o.area_id = p.area_id
    ORDER BY o.observation_date DESC
");

// Report 4: Observations per Protected Area
$report4 = $conn->query("
    SELECT p.area_name, COUNT(o.observation_id) AS total_observations
    FROM protected_areas p
    LEFT JOIN observations o ON p.area_id = o.area_id
    GROUP BY p.area_name
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-section {
            margin-bottom: 40px;
            padding: 20px;
            background: rgba(255,255,255,0.95);
            border-radius: 8px;
        }
        h3 {
            color: #2d6a4f;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        th {
            background-color: #2d6a4f;
            color: white;
            padding: 10px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Wildlife Conservation Reports</h2>
    
    <!-- Report 1 -->
    <div class="report-section">
        <h3>📊 Total Animals per Species</h3>
        <table>
            <thead>
                <tr>
                    <th>Species</th>
                    <th>Total Animals</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report1->fetch()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['common_name']); ?></td>
                    <td><?php echo $row['total_animals']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    <!-- Report 2 -->
    <div class="report-section">
        <h3>⚠️ Threatened & Protected Species</h3>
        <table>
            <thead>
                <tr>
                    <th>Species</th>
                    <th>Conservation Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report2->fetch()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['common_name']); ?></td>
                    <td>
                        <span style="
                            <?php 
                            if ($row['conservation_status'] == 'Endangered') echo 'color: red; font-weight: bold;';
                            elseif ($row['conservation_status'] == 'Vulnerable') echo 'color: orange;';
                            elseif ($row['conservation_status'] == 'Endemic') echo 'color: purple;';
                            else echo 'color: green;';
                            ?>
                        ">
                            <?php echo htmlspecialchars($row['conservation_status']); ?>
                        </span>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    <!-- Report 3 -->
    <div class="report-section">
        <h3>👀 Recent Observations</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Species</th>
                    <th>Ranger</th>
                    <th>Area</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report3->fetch()) { ?>
                <tr>
                    <td><?php echo $row['observation_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['common_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['ranger_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['area_name']); ?></td>
                    <td><?php echo $row['observation_date']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    <!-- Report 4 -->
    <div class="report-section">
        <h3>📍 Observations per Protected Area</h3>
        <table>
            <thead>
                <tr>
                    <th>Protected Area</th>
                    <th>Total Observations</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report4->fetch()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['area_name']); ?></td>
                    <td><?php echo $row['total_observations']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    <br>
    <a href="dashboard.php" style="display: inline-block; padding: 10px 20px; background: #2d6a4f; color: white; text-decoration: none; border-radius: 5px;">← Back to Dashboard</a>
</div>

</body>
</html>