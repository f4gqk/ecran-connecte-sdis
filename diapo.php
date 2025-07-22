<?php
$data = json_decode(file_get_contents('order.json'), true);
$diapos = $data['diapos'] ?? [];
$vitesse = max(1, $data['vitesse'] ?? 5); // secondes entre les diapos
?>
<!DOCTYPE html>
<html lang="fr" translate="no">
<head>
  <meta charset="UTF-8">
  <meta name="google" content="notranslate">
  <title>Diaporama</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      background: black;
      overflow: hidden;
    }
    .slide {
      display: none;
      width: 100%;
      height: 100%;
    }
    .slide.active {
      display: block;
    }
    iframe, img {
      width: 100%;
      height: 100%;
      border: none;
      object-fit: contain;
      background: white;
    }
    .message {
      color: white;
      text-align: center;
      font-size: 2em;
      padding-top: 20%;
    }
  </style>
</head>
<body>

<?php if (count($diapos) === 0): ?>
  <div class="message">Aucune diapo à afficher</div>
<?php else: ?>
  <?php foreach ($diapos as $index => $diapo):
    $file = $diapo['fichier'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    ?>
    <div class="slide<?= $index === 0 ? ' active' : '' ?>">
      <?php if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
        <img src="<?= htmlspecialchars($file) ?>" alt="">
      <?php elseif ($ext === 'pdf'): ?>
        <iframe src="<?= htmlspecialchars($file) ?>" type="application/pdf"></iframe>
      <?php elseif (in_array($ext, ['php', 'html'])): ?>
        <iframe src="<?= htmlspecialchars($file) ?>"></iframe>
      <?php else: ?>
        <div class="message">Fichier non supporté : <?= htmlspecialchars($file) ?></div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<script>
  const slides = document.querySelectorAll('.slide');
  let index = 0;
  const delay = <?= $vitesse * 1000 ?>;

  function showNextSlide() {
    slides[index].classList.remove('active');
    index = (index + 1) % slides.length;
    slides[index].classList.add('active');
  }

  if (slides.length > 1) {
    setInterval(showNextSlide, delay);
  } else if (slides.length === 1) {
    slides[0].classList.add('active');
  }

  // 🔁 Recharge automatique toutes les 60 secondes
  setTimeout(() => {
    location.reload();
  }, 60000);
</script>

</body>
</html>
