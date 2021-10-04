<?php


if (rex_request_method() == 'get') {

    echo rex_view::info('Hinweis: dieser Installer installiert die neueste nicht-beta Version eines Addons. Nutzen Sie den Installer für spezifische Versionen.');

    $form = '<p>Hier bitte zeilenweise ein Addon zur Installation eintragen:</p>';
    $form .= '<form method="POST">';
    $form .= '<div class="form-group">';
    $form .= '<textarea class="form-control" name="packages" rows="20"></textarea>';
    $form .= '</div>';
    $form .= '<button type="submit" class="btn btn-primary">Installieren</button>';
    $form .= '</form>';


    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', 'Addons installieren');
    $fragment->setVar('body', $form, false);
    echo $fragment->parse('core/page/section.php');
} elseif (rex_request_method() == 'post') {

    $packages = rex_post('packages', 'string', '');

    if ($packages == '') {
        echo rex_view::error('Keine Pakete angegeben.');
    } else {
        $packages_to_install = explode("\n", str_replace("\r", "", $packages));
        echo '<h2>Ausgewählte Addons:</h2>';
        dump($packages_to_install);
    }

    include 'global_install.php';
}
