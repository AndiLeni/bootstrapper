<?php

if (rex_request_method() == 'get') {

    echo rex_view::info('Hinweis: dieser Installer installiert die neueste nicht-beta Version eines Addons. Nutzen Sie den Installer für spezifische Versionen.');

    $packagesFromInstaller = rex_install_packages::getAddPackages();


    $table = new rex_fragment();
    $table->setVar("packagesFromInstaller", $packagesFromInstaller);
    $table = $table->parse('installer.php');


    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', 'Addons installieren');
    $fragment->setVar('body', $table, false);
    echo $fragment->parse('core/page/section.php');
} elseif (rex_request_method() == 'post') {
    $packages = rex_post('packages', 'array', []);

    if ($packages == []) {
        echo rex_view::error('Keine Pakete angegeben.');
        return;
    } else {
        $packages_to_install = $packages;
    }

    include 'global_install.php';
}



?>


<script>
    var clear_filter_btn = document.getElementById('bootstrapper_clear_input_filter')

    clear_filter_btn.addEventListener('click', function(e) {
        filterInput.value = '';
        filterNames()
    })

    // Get input element
    let filterInput = document.getElementById('filterInput');
    // Add event listener
    filterInput.addEventListener('keyup', filterNames);

    function filterNames() {
        // Get value of input
        let filterValue = document.getElementById('filterInput').value.toUpperCase();

        // Get names ul
        let ul = document.getElementById('items');

        // Get tr's
        let tr = ul.querySelectorAll('tr');

        // Loop through collection-item lis
        for (let i = 1; i < tr.length; i++) {
            let a = tr[i].getElementsByTagName('label')[0];

            // If matched
            if (a.innerHTML.toUpperCase().indexOf(filterValue) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }

    }
</script>