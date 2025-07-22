<?php
$data = json_decode(file_get_contents('order.json'), true);
$diapos = $data['diapos'] ?? [];
$vitesse = max(1, (int)($data['vitesse'] ?? 5));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Diaporama</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      width: 100vw;
      height: 100vh;
      background-color: black;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    #diapo-container {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: black;
    }

    img, iframe, embed {
      max-width: 100%;
      max-height: 100%;
      width: 100%;
      height: 100%;
      object-fit: contain;
      border: none;
    }

    p.erreur {
      color: white;
      font-size: 1.5em;
      text-align: center;
    }
  </style>
</head>
<body>
  <div id="diapo-container"></div>

  <script>
    const diapos = <?= json_encode($diapos); ?>;
    const vitesse = <?= $vitesse * 1000 ?>;
    let index = 0;

    function afficher() {
      const container = document.getElementById('diapo-container');
      const d = diapos[index];
      const fichier = d.fichier;
      const ext = fichier.split('.').pop().toLowerCase();

      container.innerHTML = '';

      if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
        const img = document.createElement('img');
        img.src = fichier + '?t=' + new Date().getTime();
        container.appendChild(img);
      } else if (['php', 'html'].includes(ext)) {
        const iframe = document.createElement('iframe');
        iframe.src = fichier + '?t=' + new Date().getTime();
        container.appendChild(iframe);
      } else if (ext === 'pdf') {
        const embed = document.createElement('embed');
        embed.src = fichier + '#toolbar=0&navpanes=0&scrollbar=0';
        embed.type = 'application/pdf';
        container.appendChild(embed);
      } else {
        const p = document.createElement('p');
        p.className = 'erreur';
        p.textContent = 'Type de fichier non supporté : ' + ext;
        container.appendChild(p);
      }

      index = (index + 1) % diapos.length;
    }

    afficher();
    setInterval(afficher, vitesse);
  </script>
</body>
</html>
