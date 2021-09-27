<?php


if (rex_request_method() == 'get') {

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

    // $packages_to_install = ['forcal', 'watson', 'mform', 'mblock', 'yrewrite', 'yform', 'yform_spam_protection', 'yrewrite_scheme', 'adminer', 'uikit_collection'];

    $versions = [];

    $nodes = [];

    rex_package_manager::synchronizeWithFileSystem();

    // get available pakages from installer
    $packagesFromInstaller = rex_install_packages::getAddPackages();


    $dependency_manager = new Dependency($packagesFromInstaller, $packages_to_install);

    foreach ($packages_to_install as $addon) {
        $dependency_manager->get_dependencies($addon);
        $packages_to_install = $dependency_manager->packages_to_install;
    }

    echo '<h2>Pakate zum installieren:</h2>';
    dump($packages_to_install);

    echo '<h2>Abhängigkeitsgraph:</h2>';
    dump($dependency_manager->nodes);

    echo '<h2>Versionen:</h2>';
    dump($dependency_manager->versions);

    $solver = new Solver();

    foreach ($dependency_manager->nodes as $node) {
        $solver->dep_resolve($node);
    }

    echo '<h2>Installation order:</h2>';

    echo '<table class="table">';
    echo '<tr>';
    echo '<th>Reihenfolge</th>';
    echo '<th>Addon</th>';
    echo '<th>Version</th>';
    echo '</tr>';

    foreach ($solver->resolved as $key => $node) {
        $key = $key + 1;
        echo '<tr>';
        echo '<td>' . $key . '</td>';
        echo '<td>' . $node->name . '</td>';
        echo '<td>' . $dependency_manager->versions[$node->name] . '</td>';
        echo '</tr>';
    }

    echo '</table>';


    foreach ($solver->resolved as $addon) {

        // echo 'Installing and activating addon ' . $addon->name . '<br>';
        $package = rex_package::get($addon->name);
        // $am = rex_addon_manager::factory($package);
        $am = rex_package_manager::factory($package);

        $am->install();
        $activated = $am->activate();

        if (!$activated) {
            echo rex_view::error('Could not activate ' . $addon->name);
        } else {
            echo rex_view::success('Activated ' . $addon->name);
        }
    }
}
