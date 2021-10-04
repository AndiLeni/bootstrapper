<?php

// show which addons were selected for installation
echo '<h2>Ausgewählte Addons:</h2>';
echo '<ul>';
foreach ($packages_to_install as $package) {
    echo '<li>';
    echo $package;
    echo '</li>';
}
echo '</ul>';


// $packages_to_install = ['forcal', 'watson', 'mform', 'mblock', 'yrewrite', 'yform', 'yform_spam_protection', 'yrewrite_scheme', 'adminer', 'uikit_collection'];

// keep track of installed versions
$versions = [];

// keep track of dependencies
// this variable stores all addons and deoendencies as a node graph
$nodes = [];

// synchronize addons with filestream
rex_package_manager::synchronizeWithFileSystem();

// get available packages from installer
$packagesFromInstaller = rex_install_packages::getAddPackages();

// downloads addons and determines dependencies recursively
$dependency_manager = new Dependency($packagesFromInstaller, $packages_to_install);

foreach ($packages_to_install as $addon) {
    $dependency_manager->get_dependencies($addon);
    $packages_to_install = $dependency_manager->packages_to_install;
}



echo '<h2>Pakete zum installieren:</h2>';
// dump($packages_to_install);
echo '<ul>';
foreach ($packages_to_install as $package) {
    echo '<li>';
    echo $package;
    echo '</li>';
}
echo '</ul>';



echo '<h2>Abhängigkeitsgraph:</h2>';
dump($dependency_manager->nodes);



// echo '<h2>Versionen:</h2>';
// // dump($dependency_manager->versions);
// echo '<ul>';
// foreach ($dependency_manager->versions as $package => $version) {
//     echo '<li>';
//     echo $package . ' --- ' . $version;
//     echo '</li>';
// }
// echo '</ul>';


// solver for dependency resolution
$solver = new Solver();

foreach ($dependency_manager->nodes as $node) {
    $solver->dep_resolve($node);
}



echo '<h2>Installation order:</h2>';

echo '<table class="table table-hover">';
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


if ($dependency_manager->no_downloadable_version_found) {
    echo rex_view::error('Installation aborted because one addon could not be found');
    exit();
}


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
