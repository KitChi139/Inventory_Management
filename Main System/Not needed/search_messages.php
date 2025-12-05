<?php
include 'db_connect.php';

$search = $_POST['search'] ?? '';

$sql = "
    SELECT header, supplier, date_created
    FROM messages
    WHERE header LIKE '%$search%' 
       OR supplier LIKE '%$search%'
    ORDER BY date_created DESC
    LIMIT 20
";

$res = $conn->query($sql);

if ($res->num_rows == 0) {
    echo "<p style='padding:10px;'>No messages found</p>";
    exit;
}

while ($m = $res->fetch_assoc()):
?>
<div class='msg-item'>
    <div class='msg-avatar'>
        <?= strtoupper(substr($m['supplier'], 0, 2)) ?>
    </div>

    <div class='msg-details'>
        <strong><?= htmlspecialchars($m['supplier']) ?></strong>
        <span class='msg-date'><?= date("M d, Y", strtotime($m['date_created'])) ?></span>
        <div class='msg-preview'>
            <?= htmlspecialchars($m['header']) ?>
        </div>
    </div>
</div>
<?php endwhile; ?>
