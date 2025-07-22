<?php
// Lire l'identifiant utilisateur depuis le cookie
$user = isset($_COOKIE['user']) ? strtoupper(preg_replace('/[^A-Z]/', '', $_COOKIE['user'])) : null;
$user = substr($user, 0, 4);

// Fichier JSON spécifique à l'utilisateur
$orderFile = "users/$user/order.json";
$data = ['vitesse' => 5, 'diapos' => []];

if ($user && file_exists($orderFile)) {
    $data = json_decode(file_get_contents($orderFile), true);
}

$vitesse = max(1, (int)($data['vitesse'] ?? 5)) * 1000; // en millisecondes
$diapos = $data['diapos'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Diaporama</title>
    <style>
        html, body {
            margin: 0; padding: 0;
            background: black;
            height: 100%;
            overflow: hidden;
        }
        .diapo {
            position: absolute;
            width: 100%;
            height: 100%;
            display: none;
            justify-content: center;
            align-items: center;
        }
        .diapo img, .diapo iframe {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            background: black;
        }
    </style>
</head>
<body>
<?php foreach ($diapos as $index => $d): 
    $file = htmlspecialchars($d['fichier']);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    echo '<div class="diapo" id="diapo' . $index . '">';
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        echo "<img src=\"$file\">";
    } elseif ($ext === 'pdf') {
        echo "<iframe src=\"$file\" type='application/pdf' width='100%' height='100%'></iframe>";
    } elseif ($ext === 'html' || $ext === 'php') {
        echo "<iframe src=\"$file\" width='100%' height='100%' style='border:none'></iframe>";
    } else {
        echo "<p style='color:white;'>Fichier non supporté</p>";
    }
    echo '</div>';
endforeach; ?>

<script>
let index = 0;
const diapos = document.querySelectorAll('.diapo');
if (diapos.length > 0) {
    diapos[0].style.display = 'flex';
    setInterval(() => {
        diapos[index].style.display = 'none';
        index = (index + 1) % diapos.length;
        diapos[index].style.display = 'flex';
    }, <?= $vitesse ?>);
}
</script>
</body>
</html>
