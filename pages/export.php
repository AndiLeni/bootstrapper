<?php

$available_packages = rex_package::getAvailablePackages();

$packages = "";

foreach ($available_packages as $key => $value) {
    $packages .= $key . PHP_EOL;
}

$textarea = '<textarea class="form-control" rows="' . count($available_packages) . '">' . $packages . '</textarea>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Installierte Addons');
$fragment->setVar('body', $textarea, false);
echo $fragment->parse('core/page/section.php');
