<?php


if (rex_request_method() == 'get') {

    echo rex_view::info('Hinweis: dieser Installer installiert die neueste nicht-beta Version eines Addons. Nutzen Sie den Installer für spezifische Versionen.');

    $form = <<<EOD
    <p>Hier bitte zeilenweise ein Addon zur Installation eintragen:</p>
    <form method="POST">
        <div class="form-group">
            <textarea class="form-control" name="packages" rows="20"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Installieren</button>
    </form>
    EOD;

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', 'Addons installieren');
    $fragment->setVar('body', $form, false);
    echo $fragment->parse('core/page/section.php');
} elseif (rex_request_method() == 'post') {

    $packages = rex_post('packages', 'string', '');

    if ($packages == '') {
        echo rex_view::error('Keine Pakete angegeben.');
        return;
    } else {
        $packages_to_install = explode("\n", str_replace("\r", "", $packages));
    }

    include 'global_install.php';
}
