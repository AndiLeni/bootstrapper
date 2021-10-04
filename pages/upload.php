<?php

if (rex_request_method() == 'get') {

    echo rex_view::info('Hinweis: dieser Installer installiert die neueste nicht-beta Version eines Addons. Nutzen Sie den Installer für spezifische Versionen.');


    $form = '<p>Hier kann eine Datei mit zeilenweise eingetragenen Addons hochgeladen werden:</p>';
    $form .= '<form method="POST" enctype="multipart/form-data">';
    $form .= '<div class="form-group">';
    $form .= '<input class="form-control" name="packages" type="file">';
    $form .= '</div>';
    $form .= '<button type="submit" class="btn btn-primary">Installieren</button>';
    $form .= '</form>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', 'Addons installieren');
    $fragment->setVar('body', $form, false);
    echo $fragment->parse('core/page/section.php');
} elseif (rex_request_method() == 'post') {
    $file = rex_files('packages', 'array', []);


    if ($file == []) {
        echo rex_view::error('Keine Pakete angegeben.');
        return;
    } else {

        $file_content = file_get_contents($file['tmp_name']);

        $packages_to_install = explode("\n", str_replace("\r", "", $file_content));

        echo '<h2>Ausgewählte Addons:</h2>';
        dump($packages_to_install);
    }

    include 'global_install.php';
}
