<?php

$d = ['yform_spam_protection', 'yrewrite_scheme', 'mform', 'mblock', 'yrewrite', 'yform',   'adminer', 'theme'];

foreach ($d as $a) {
    $package = rex_package::get($a);
    $am = rex_addon_manager::factory($package);

    $am->uninstall();
}
