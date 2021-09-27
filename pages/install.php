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
    }
    dump($packages_to_install);

}


$packages_to_install = ['forcal', 'watson', 'mform', 'mblock', 'yrewrite', 'yform', 'yform_spam_protection', 'yrewrite_scheme', 'adminer', 'uikit_collection'];

$versions = [];

$nodes = [];

rex_package_manager::synchronizeWithFileSystem();

// get most recent version
$packagesFromInstaller = rex_install_packages::getAddPackages();
// dump($packagesFromInstaller);
// dump($packagesFromInstaller['yform']);
// dump($packagesFromInstaller['yform']['files']);
// $filekey = array_keys($packagesFromInstaller['forcal']['files'])[0];
// dump($packagesFromInstaller['forcal']['files'][$filekey]['version']);
// $version = $packagesFromInstaller['forcal']['files'][$filekey]['version'];

// function get_key($packagesFromInstaller, $addon)
// {
//     $filekeys = array_keys($packagesFromInstaller[$addon]['files']);
//     // dump($filekeys);

//     foreach ($filekeys as $key) {
//         $version = $packagesFromInstaller[$addon]['files'][$key]['version'];
//         if (!str_contains($version, 'beta') && !str_contains($version, 'dev') && !str_contains($version, 'alpha') && !str_contains($version, 'rc')) {
//             // dump($key);
//             return $key;
//         }
//     }
//     echo rex_view::error('No released version for addon ' . $addon);
// }


// function get_dependencies(String $addon, $packagesFromInstaller, $packages_to_install)
// {
//     if (!rex_addon::exists($addon)) {
//         $filekey = get_key($packagesFromInstaller, $addon);
//         $version = $packagesFromInstaller[$addon]['files'][$filekey]['version'];

//         echo 'Downloading addon ' . $addon . ' in version ' . $version . '<br>';

//         $versions[$addon] = $version;


//         $ri = new rex_install();
//         $ri->downloadAddon($addon, $version);

//         if (!in_array($addon, $packages_to_install))
//     }
// }




// foreach ($packages_to_install as $addon) {
//     $nodes[$addon] = new Node($addon);
// }

// dump($nodes);


// foreach ($packages_to_install as $addon) {
//     echo 'dependencies for ' . $addon . '<br>';

//     $package = rex_package::get($addon);
//     $deps = $package->getProperty('requires');

//     if (isset($deps['packages'])) {
//         $deps = array_keys($deps['packages']);
//         dump($deps);

//         foreach ($deps as $dep) {
//             $a = rex_addon::get($dep);
//             // dump($a);
//             // dump($a->isAvailable());

//             if (!($a->isAvailable())) {
//                 // echo $addon;


//                 if (!isset($nodes[$addon])) {
//                     $nodes[$addon] = new Node($addon);
//                 }

//                 if (!isset($nodes[$dep])) {
//                     $nodes[$dep] = new Node($dep);
//                 }

//                 $nodes[$addon]->addEdge($nodes[$dep]);  # addon depends on dependency

//             }
//         }
//     }
// }

// foreach ($packages_to_install as $addon) {

//     // $filekey = array_keys($packagesFromInstaller[$addon]['files'])[0];
//     $filekey = get_key($packagesFromInstaller, $addon);
//     $version = $packagesFromInstaller[$addon]['files'][$filekey]['version'];

//     echo 'Downloading addon ' . $addon . ' in version ' . $version . '<br>';

//     $versions[$addon] = $version;


//     if (rex_addon::exists($addon)) {
//         echo $addon . ' already exists -> skipping <br>';
//     } else {
//         $ri = new rex_install();
//         $ri->downloadAddon($addon, $version);
//     }
// }

// dump($nodes);

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

// // $solver->dep_resolve(reset($nodes));

// // dump($solver->resolved);

// $solver->dep_resolve($nodes['uikit_collection']);

// dump($solver->resolved);

foreach ($dependency_manager->nodes as $node) {
    $solver->dep_resolve($node);
}
// dump($solver->resolved);



?>

<h2>Installation order:</h2>

<table class="table">
    <tr>
        <th>Reihenfolge</th>
        <th>Addon</th>
        <th>Version</th>
    </tr>

    <?php
    foreach ($solver->resolved as $key => $node) {
        $key = $key + 1;
        echo '<tr>';
        echo '<td>' . $key . '</td>';
        echo '<td>' . $node->name . '</td>';
        echo '<td>' . $dependency_manager->versions[$node->name] . '</td>';
        echo '</tr>';
    }

    ?>

</table>

<?php


foreach ($solver->resolved as $addon) {

    echo 'Installing and activating addon ' . $addon->name . '<br>';
    $package = rex_package::get($addon->name);
    $am = rex_addon_manager::factory($package);

    $am->install();
    $activated = $am->activate();

    if (!$activated) {
        echo rex_view::error('Could not activate ' . $addon);
    }
}




// $package = rex_package::get('yform_spam_protection');
// // dump($package);
// dump($package->getConfig());
// dump($package->getProperty('requires'));

// $am = rex_addon_manager::factory($package);


// // dump($am);
// dump($am->checkConflicts());
// dump($am->checkRequirements());
// dump($am->generatePackageOrder());


// dump(rex_package::getRegisteredPackages());
// dump(rex_package::getInstalledPackages());
// dump(rex_package::getAvailablePackages());
// dump(rex_package::getSetupPackages());
// dump(rex_package::getSystemPackages());
