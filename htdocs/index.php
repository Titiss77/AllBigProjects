<?php

// 1. On récupère le chemin absolu du dossier parent (AMFS-refactor)
$dossierParent = dirname(__DIR__);

$chemin = [];

// 2. On scanne le dossier parent pour trouver les projets
if (is_dir($dossierParent)) {
    $elements = scandir($dossierParent);

    foreach ($elements as $element) {
        // On ignore les dossiers système (. et ..) et le htdocs principal où on se trouve
        if ($element === '.' || $element === '..' || $element === 'htdocs') {
            continue;
        }

        $cheminCompletProjet = $dossierParent . '/' . $element;

        // Si c'est un dossier et qu'il contient un sous-dossier 'htdocs'
        if (is_dir($cheminCompletProjet) && is_dir($cheminCompletProjet . '/htdocs')) {
            // On ajoute automatiquement le projet au tableau avec son lien relatif
            $chemin[$element] = [
                'chemin' => '../' . $element . '/htdocs/'
            ];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routes</title>
    <style>
    /* Un petit peu de style pour rendre la liste plus propre */
    body {
        font-family: Arial, sans-serif;
        margin: 40px;
        background: #f9f9f9;
    }

    h1 {
        color: #333;
    }

    ul {
        list-style: none;
        padding: 0;
    }

    li {
        margin: 10px 0;
    }

    a {
        display: inline-block;
        padding: 10px 15px;
        background: #fff;
        color: #007bff;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
    }

    a:hover {
        background: #007bff;
        color: #fff;
        border-color: #007bff;
    }
    </style>
</head>

<body>
    <h1>Liste des chemins</h1>
    <ul>
        <?php foreach ($chemin as $key => $value) { ?>
        <li>
            <a href="<?php echo htmlspecialchars($value['chemin']); ?>">
                📁 <?php echo htmlspecialchars($key); ?>
            </a>
        </li>
        <?php } ?>
    </ul>
</body>

</html>