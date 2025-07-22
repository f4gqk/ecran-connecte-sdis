<?php
$orderFile = 'order.json';
$slidesDir = 'slides/';
if (!file_exists($slidesDir)) mkdir($slidesDir, 0777, true);

$data = ['vitesse' => 5, 'diapos' => []];
if (file_exists($orderFile)) {
    $data = json_decode(file_get_contents($orderFile), true);
}

// Suppression d’un fichier du dossier slides/
if (isset($_POST['delete_file'])) {
    $fileToDelete = basename($_POST['delete_file']);
    $path = $slidesDir . $fileToDelete;
    if (file_exists($path)) {
        unlink($path);
    }
}

// Supprimer toutes les diapos
if (isset($_POST['supprimer_tout'])) {
    foreach ($data['diapos'] as $diapo) {
        $file = $diapo['fichier'];
        if (preg_match('/\\.(jpe?g|png)$/i', $file) && file_exists($file)) {
            unlink($file);
        }
    }
    $data['diapos'] = [];
}

$limitReached = count($data['diapos']) >= 20;

// Upload
if (!$limitReached && isset($_FILES['fichier']) && $_FILES['fichier']['error'] === 0 && !empty($_POST['nom'])) {
    $ext = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'php', 'html', 'pdf'];
    if (in_array($ext, $allowed)) {
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($_FILES['fichier']['name'], PATHINFO_FILENAME));
        $destination = $slidesDir . $filename . '.' . $ext;
        $i = 1;
        while (file_exists($destination)) {
            $destination = $slidesDir . $filename . "_" . $i++ . "." . $ext;
        }
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $destination)) {
            $data['diapos'][] = ['fichier' => $destination, 'nom' => $_POST['nom']];
        }
    }
}

// Ajout depuis fichier déjà présent
if (!$limitReached && isset($_POST['ajouter_file'], $_POST['file_name'], $_POST['nom']) && !empty($_POST['file_name']) && !empty($_POST['nom'])) {
    $filename = basename($_POST['file_name']);
    $fullpath = $slidesDir . $filename;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'php', 'html', 'pdf'];
    if (file_exists($fullpath) && in_array($ext, $allowed)) {
        $data['diapos'][] = ['fichier' => $fullpath, 'nom' => $_POST['nom']];
    }
}

// Renommer
if (isset($_POST['renommer']) && isset($_POST['index']) && isset($data['diapos'][$_POST['index']])) {
    $data['diapos'][$_POST['index']]['nom'] = $_POST['renommer'];
}

// Supprimer une diapo
if (isset($_POST['supprimer']) && isset($data['diapos'][$_POST['supprimer']])) {
    $i = (int)$_POST['supprimer'];
    $file = $data['diapos'][$i]['fichier'];
    if (preg_match('/\\.(jpe?g|png)$/i', $file) && file_exists($file)) unlink($file);
    array_splice($data['diapos'], $i, 1);
}

// Réorganiser
if (isset($_POST['deplacer']) && isset($_POST['index'])) {
    $i = (int)$_POST['index'];
    if ($_POST['deplacer'] === 'haut' && $i > 0) {
        [$data['diapos'][$i - 1], $data['diapos'][$i]] = [$data['diapos'][$i], $data['diapos'][$i - 1]];
    } elseif ($_POST['deplacer'] === 'bas' && $i < count($data['diapos']) - 1) {
        [$data['diapos'][$i + 1], $data['diapos'][$i]] = [$data['diapos'][$i], $data['diapos'][$i + 1]];
    }
}

// Vitesse
if (isset($_POST['vitesse'])) {
    $data['vitesse'] = max(1, (int)$_POST['vitesse']);
}

file_put_contents($orderFile, json_encode($data, JSON_PRETTY_PRINT));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion du Diaporama</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 2rem; background-color: #f8f9fa; }
    .thumb { height: 50px; margin-right: 10px; }
  </style>
</head>
<body>
<div class="container">
  <h1 class="mb-4">Gestion du Diaporama</h1>

  <form method="post" class="mb-4">
    <label class="form-label">Durée d'affichage de chaque diapo (en secondes)</label>
    <div class="input-group w-25">
      <input type="number" name="vitesse" class="form-control" value="<?= $data['vitesse'] ?>" min="1">
      <button class="btn btn-primary" type="submit">Sauvegarder</button>
    </div>
  </form>

  <?php if ($limitReached): ?>
    <div class="alert alert-warning">Le nombre maximum de diapos (20) a été atteint.</div>
  <?php else: ?>
    <div class="card mb-4 p-3">
      <h5>Ajouter un nouveau fichier (.jpg, .jpeg, .png, .php, .html, .pdf)</h5>
      <form method="post" enctype="multipart/form-data">
        <div class="mb-2"><input class="form-control" type="file" name="fichier" accept=".jpg,.jpeg,.png,.php,.html,.pdf" required></div>
        <div class="mb-2"><input class="form-control" type="text" name="nom" placeholder="Nom à afficher pour la diapo" required></div>
        <button class="btn btn-success" type="submit">Téléverser</button>
      </form>
    </div>

    <div class="card mb-4 p-3">
      <h5>Ajouter un fichier existant du dossier <code>slides/</code></h5>
      <form method="post">
        <div class="mb-2"><input class="form-control" type="text" name="file_name" placeholder="Nom du fichier (ex: exemple.pdf)" required></div>
        <div class="mb-2"><input class="form-control" type="text" name="nom" placeholder="Nom à afficher pour la diapo" required></div>
        <input type="hidden" name="ajouter_file" value="1">
        <button class="btn btn-secondary" type="submit">Ajouter</button>
      </form>
    </div>
  <?php endif; ?>

  <h3>Liste des Diapos</h3>
  <form method="post" class="mb-3">
    <button type="submit" name="supprimer_tout" class="btn btn-danger" onclick="return confirm('Supprimer toutes les diapos ?')">Supprimer toutes les diapos</button>
  </form>

  <ol class="list-group list-group-numbered">
    <?php foreach ($data['diapos'] as $i => $d): ?>
      <li class="list-group-item d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <?php if (preg_match('/\\.(jpe?g|png)$/i', $d['fichier'])): ?>
            <img src="<?= htmlspecialchars($d['fichier']) ?>" class="thumb rounded">
          <?php endif; ?>
          <span><strong><?= htmlspecialchars($d['nom']) ?></strong> <span class="badge bg-light text-dark">(<?= basename($d['fichier']) ?>)</span></span>
        </div>
        <div class="d-flex flex-wrap gap-1">
          <form method="post">
            <input type="hidden" name="index" value="<?= $i ?>">
            <button name="deplacer" value="haut" class="btn btn-outline-primary btn-sm">Monter</button>
            <button name="deplacer" value="bas" class="btn btn-outline-primary btn-sm">Descendre</button>
          </form>
          <form method="post">
            <input type="hidden" name="supprimer" value="<?= $i ?>">
            <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
          </form>
          <form method="post" class="d-flex flex-wrap align-items-center">
            <input type="hidden" name="index" value="<?= $i ?>">
            <input type="text" name="renommer" class="form-control form-control-sm me-1" value="<?= htmlspecialchars($d['nom']) ?>">
            <button type="submit" class="btn btn-outline-success btn-sm">Renommer</button>
          </form>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>

  <h3 class="mt-5">Fichiers non utilisés dans <code>slides/</code></h3>
  <ol class="list-group list-group-numbered">
    <?php
    $usedFiles = array_column($data['diapos'], 'fichier');
    $allFiles = array_diff(scandir($slidesDir), ['.', '..']);
    foreach ($allFiles as $fichier) {
        $path = $slidesDir . $fichier;
        $ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
        if (!in_array($path, $usedFiles) && in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<div><strong>' . htmlspecialchars($fichier) . '</strong></div>';
            echo '<div class="d-flex gap-2">';
            echo '<a href="' . htmlspecialchars($path) . '" target="_blank" class="btn btn-outline-secondary btn-sm">Voir</a>';
            echo '<form method="post" onsubmit="return confirm(\'Supprimer ce fichier ?\')">';
            echo '<input type="hidden" name="delete_file" value="' . htmlspecialchars($fichier) . '">';
            echo '<button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>';
            echo '</form></div></li>';
        }
    }
    ?>
  </ol>

  <a href="diapo.php" target="_blank" class="btn btn-primary mt-4">Lancer le diaporama</a>
  <p class="text-muted mt-3">Script réalisé par <strong>Sébastien Roverch</strong></p>
</div>
</body>
</html>
