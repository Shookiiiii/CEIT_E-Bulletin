<?php
include "../../db.php";

// Fetch all accreditation records sorted by level (Level IV highest)
$result = $conn->query("
    SELECT * FROM accreditation
    ORDER BY 
        CASE status
            WHEN 'Level IV Re-accredited' THEN 4
            WHEN 'Level III Re-accredited' THEN 3
            WHEN 'Level II Re-accredited' THEN 2
            WHEN 'Level I Re-accredited' THEN 1
        END DESC,
        program ASC
");
$rows = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Accreditation Status</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-1">

<div class="max-w-5xl mx-auto bg-white p-0 rounded-xl space-y-6">

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded">
            <thead class="bg-orange-200">
                <tr>
                    <th class="px-2 py-2 text-left text-[8px]">Program</th>
                    <th class="px-2 py-2 text-left text-[8px]">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($rows) > 0): ?>
                    <?php foreach($rows as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-1 py-1 text-[8px] text-left"><?= htmlspecialchars($row['program']) ?></td>
                            <td class="px-1 py-1 text-[8px] text-left flex items-center gap-1">
                                <?php 
                                    $status = $row['status'];
                                    $badgeColor = '';

                                    switch($status) {
                                        case 'Level IV Re-accredited':
                                            $badgeColor = 'bg-yellow-400';
                                            break;
                                        case 'Level III Re-accredited':
                                            $badgeColor = 'bg-gray-400'; 
                                            break;
                                        case 'Level II Re-accredited':
                                            $badgeColor = 'bg-orange-500';
                                            break;
                                        case 'Level I Re-accredited':
                                            $badgeColor = 'bg-blue-400';
                                            break;
                                    }
                                ?>
                                <span class="inline-block w-2 h-2 rounded-full <?= $badgeColor ?>"></span>
                                <?= htmlspecialchars($status) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="px-4 py-2 text-center text-gray-500">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
