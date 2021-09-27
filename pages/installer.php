<?php

if (rex_request_method() == 'get') {

    $packagesFromInstaller = rex_install_packages::getAddPackages();

    $names = array_keys($packagesFromInstaller);

    $content = '<p>Addons filtern:</p>';
    $content .= '<input id="filterInput" class="form-control" type="text">';
    $content .= '<hr>';
    $content .= '<form method="POST">';
    $content .= '<ul id="items" style="list-style: none;">';




    foreach ($names as $addon) {
        $content .= '<li>';
        $content .= '<div>';
        $content .= '<input style="margin-right: 10px" type="checkbox" id="' . $addon . '" name="packages[]" value="' . $addon . '">';
        $content .= '<label for="' . $addon . '">' . $addon . '</label>';
        $content .= '</div>';
        $content .= '</li>';
    }

    $content .= '<button type="submit" class="btn btn-primary">Installieren</button>';
    $content .= '</form>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', 'Addons installieren');
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
} elseif (rex_request_method() == 'post') {
    $packages = rex_post('packages', 'array', []);

    if ($packages == []) {
        echo rex_view::error('Keine Pakete angegeben.');
        return;
    } else {
        $packages_to_install = $packages;
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
        $am = rex_addon_manager::factory($package);

        $am->install();
        $activated = $am->activate();

        if (!$activated) {
            echo rex_view::error('Could not activate ' . $addon->name);
        } else {
            echo rex_view::success('Activated ' . $addon->name);
        }
    }
}



?>

</ul>

<script>
    // Get input element
    let filterInput = document.getElementById('filterInput');
    // Add event listener
    filterInput.addEventListener('keyup', filterNames);

    function filterNames() {
        // Get value of input
        let filterValue = document.getElementById('filterInput').value.toUpperCase();

        // Get names ul
        let ul = document.getElementById('items');
        // Get lis from ul
        let li = ul.querySelectorAll('li');
        // console.log(li)

        // Loop through collection-item lis
        for (let i = 0; i < li.length; i++) {
            let a = li[i].getElementsByTagName('label')[0];
            // If matched
            if (a.innerHTML.toUpperCase().indexOf(filterValue) > -1) {
                li[i].style.display = '';
            } else {
                li[i].style.display = 'none';
            }
        }

    }
</script>