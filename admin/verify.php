<?php

require_once 'includes/db.php';

$db = getDB();

$token = $_GET['token'] ?? '';

$stmt = $db->prepare("
SELECT admin_id
FROM admin
WHERE verification_token = ?
LIMIT 1
");

$stmt->bind_param("s", $token);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $update = $db->prepare("
    UPDATE admin
    SET
        email_verified = 1,
        verification_token = NULL
    WHERE admin_id = ?
    ");

    $update->bind_param(
        "i",
        $row['admin_id']
    );

    $update->execute();

    echo "
    <h2>Email Verified Successfully</h2>
    <a href='index.php'>Login Now</a>
    ";

} else {

    echo "
    <h2>Invalid Verification Link</h2>
    ";
}