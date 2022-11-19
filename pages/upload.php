<?php

if (rex_request_method() == 'get') {

    echo rex_view::info('Hinweis: dieser Installer installiert die neueste nicht-beta Version eines Addons. Nutzen Sie den Installer für spezifische Versionen.');

    $form = new rex_fragment();
    $form = $form->parse("file_upload.php");

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', 'Addons installieren');
    $fragment->setVar('body', $form, false);
    echo $fragment->parse('core/page/section.php');
} elseif (rex_request_method() == 'post') {
    $file = rex_files('packages', 'array', []);


    if ($file['name'] == "") {
        echo rex_view::error('Keine Pakete angegeben.');
        return;
    } else {
        $file_content = file_get_contents($file['tmp_name']);

        $packages_to_install = explode("\n", str_replace("\r", "", $file_content));
    }

    include 'global_install.php';
}
