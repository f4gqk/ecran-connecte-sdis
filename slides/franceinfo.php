<?php
// Forcer les erreurs visibles
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charger le flux RSS complet
$rss = @simplexml_load_file('https://www.bfmtv.com/rss/news-24-7/');
$titres = [];

if ($rss && isset($rss->channel->item)) {
    foreach ($rss->channel->item as $item) {
        $titre = (string)$item->title;
        // Corriger encodage + entités HTML
        $titre = html_entity_decode($titre, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $titre = mb_convert_encoding($titre, 'UTF-8', 'auto');
        $titres[] = $titre;
    }
} else {
    $titres[] = "⚠️ Impossible de charger les actualités.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bandeau BFM TV - Complet</title>
  <style>
    html, body {
      margin: 0;
      background: black;
    }
    .bandeau {
      position: fixed;
      bottom: 0;
      width: 100%;
      height: 60px;
      background: #001C3E;
      color: white;
      font-family: Arial, sans-serif;
      font-size: 1.4em;
      display: flex;
      align-items: center;
      padding-left: 20px;
      box-sizing: border-box;
    }
    .logo {
      background: #FF9900;
      color: black;
      font-weight: bold;
      padding: 0 15px;
      margin-right: 15px;
      height: 40px;
      display: flex;
      align-items: center;
      border-radius: 4px;
    }
    .actu {
      flex-grow: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
</head>
<body>

<div class="bandeau">
  <div class="logo">BFM TV</div>
  <div class="actu" id="actu"><?= htmlspecialchars($titres[0]) ?></div>
</div>

<script>
  const titres = <?= json_encode($titres, JSON_UNESCAPED_UNICODE) ?>;
  let index = 0;
  const actuDiv = document.getElementById('actu');

  setInterval(() => {
    index = (index + 1) % titres.length;
    actuDiv.textContent = titres[index];
  }, 10000); // toutes les 10 secondes
</script>

</body>
</html>
