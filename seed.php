<?php
/**
 * Run this ONCE in your browser (e.g. http://localhost/sound-php/seed.php)
 * after importing schema.sql, to create demo accounts and sample content.
 * It is safe to run more than once — it checks before inserting.
 */
require_once "config.php";

function alreadySeeded($pdo){
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM users");
    return $stmt->fetch()['c'] > 0;
}

if (alreadySeeded($pdo)) {
    die("Seed data already exists — nothing to do. Delete rows from 'users' table first if you want to reseed.");
}

// ---- Users ----
$adminHash = password_hash("admin123", PASSWORD_DEFAULT);
$userHash  = password_hash("user123", PASSWORD_DEFAULT);

$pdo->prepare("INSERT INTO users (name,email,phone,address,password,role) VALUES (?,?,?,?,?,?)")
    ->execute(["Site Administrator","admin@sound.com","0300-0000000","Karachi, PK",$adminHash,"admin"]);

$pdo->prepare("INSERT INTO users (name,email,phone,address,password,role) VALUES (?,?,?,?,?,?)")
    ->execute(["Ayesha Khan","ayesha@example.com","0321-1234567","Lahore, PK",$userHash,"user"]);

$adminId = $pdo->lastInsertId() - 1; // admin was inserted first
$userId  = $pdo->lastInsertId();

// ---- Music ----
$music = [
    ["Neon Nights","Zara Ali","Midnight Drive","2026","Pop","English","An upbeat synth-pop anthem about city lights."],
    ["Barsaat Ke Din","Rahat Mehmood","Saawan","2025","Folk","Urdu","A monsoon-themed folk ballad."],
    ["Dil Da Mamla","Sada Rang","Dholna","2025","Rock","Punjabi","High-energy bhangra-rock crossover track."],
    ["Sufi Sama","Qalandar Group","Mehfil","2024","Qawwali","Urdu","Traditional qawwali performance."],
    ["Ocean Drift","Leo Waves","Blue Room","2024","Rock","English","Atmospheric rock with soaring guitars."],
    ["Sindh Diyan Raatan","Zeenat Baloch","Waee","2023","Folk","Sindhi","A soulful Sindhi folk piece."],
];
$mStmt = $pdo->prepare("INSERT INTO music (title,artist,album,year,genre,language,description,is_new) VALUES (?,?,?,?,?,?,?,1)");
$musicIds = [];
foreach ($music as $m) { $mStmt->execute($m); $musicIds[] = $pdo->lastInsertId(); }

// ---- Video ----
$video = [
    ["City Lights","Zara Ali","Midnight Drive","2026","Pop","English","Official music video for Neon Nights."],
    ["Saawan Aaya","Rahat Mehmood","Saawan","2025","Folk","Urdu","Rain-soaked visuals for Barsaat Ke Din."],
    ["Mehfil Live","Qalandar Group","Mehfil","2024","Qawwali","Urdu","Live concert recording."],
    ["Blue Room Sessions","Leo Waves","Blue Room","2024","Rock","English","Studio session video."],
];
$vStmt = $pdo->prepare("INSERT INTO video (title,artist,album,year,genre,language,description,is_new) VALUES (?,?,?,?,?,?,?,1)");
$videoIds = [];
foreach ($video as $v) { $vStmt->execute($v); $videoIds[] = $pdo->lastInsertId(); }

// ---- Reviews ----
$rStmt = $pdo->prepare("INSERT INTO reviews (item_type,item_id,user_id,rating,review_text) VALUES (?,?,?,?,?)");
$rStmt->execute(["music", $musicIds[0], $userId, 5, "Loved the production on this track!"]);
$rStmt->execute(["video", $videoIds[0], $userId, 5, "Visuals match the mood perfectly."]);

echo "Seed data inserted successfully.<br>";
echo "Admin login: admin@sound.com / admin123<br>";
echo "User login: ayesha@example.com / user123<br>";
echo "<a href='index.php'>Go to site →</a>";
