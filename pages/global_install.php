<?php

// show which addons were selected for installation
echo '<h3>Ausgewählte Addons:</h3>';
echo '<ul>';
foreach ($packages_to_install as $package) {
    echo '<li>';
    echo $package;
    echo '</li>';
}
echo '</ul>';


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



echo '<h3>Abhängigkeiten die installiert werden müssen:</h3>';
echo '<ul>';
foreach ($packages_to_install as $package) {
    echo '<li>';
    echo $package;
    echo '</li>';
}
echo '</ul>';


function make_text_graph($nodes)
{
    echo '<ul>';
    foreach ($nodes as $node) {
        if ($node->edges == []) {
            echo '<li>' . $node->name . "</li>";
        } else {
            echo '<li>' . $node->name . "</li>";
            make_text_graph($node->edges);
        }
    }
    echo '</ul>';
}

echo '<h3>Abhängigkeitsgraph:</h3>';
make_text_graph($dependency_manager->nodes);




// solver for dependency resolution
$solver = new Solver();

foreach ($dependency_manager->nodes as $node) {
    $solver->dep_resolve($node);
}



echo '<h3>Installation order:</h3>';

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


// install addons and dependencies
foreach ($solver->resolved as $addon) {
    $package = rex_package::get($addon->name);
    $am = rex_package_manager::factory($package);

    $am->install();
    $activated = $am->activate();

    if (!$activated) {
        echo rex_view::error('Could not activate ' . $addon->name);
    } else {
        echo rex_view::success('Activated ' . $addon->name);
    }
}
