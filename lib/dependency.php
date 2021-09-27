<?php

class Dependency
{

    public String $addon;
    public array $packagesFromInstaller;
    public array $packages_to_install;
    public array $versions = [];
    public array $nodes = [];


    public function __construct(array $packagesFromInstaller, array $packages_to_install)
    {

        $this->packagesFromInstaller = $packagesFromInstaller;
        $this->packages_to_install = $packages_to_install;
    }


    public function get_version(String $addon)
    {
        $filekey = $this->get_key($this->packagesFromInstaller, $addon);
        $version = $this->packagesFromInstaller[$addon]['files'][$filekey]['version'];
        return $version;
    }


    function get_dependencies(String $addon)
    {
        if (!rex_addon::exists($addon)) {
            $filekey = $this->get_key($this->packagesFromInstaller, $addon);
            $version = $this->packagesFromInstaller[$addon]['files'][$filekey]['version'];

            echo 'Downloading addon ' . $addon . ' in version ' . $version . '<br>';

            // $this->versions[$addon] = $version;


            $ri = new rex_install();
            $ri->downloadAddon($addon, $version);

            // if (!in_array($addon, $this->packages_to_install)) {
            //     array_push($this->packages_to_install, $addon);
            // }
        }

        $this->versions[$addon] = $this->get_version($addon);

        echo '<h4>Dependencies for ' . $addon . ':</h4>';

        $package = rex_package::get($addon);
        $deps = $package->getProperty('requires');

        if (isset($deps['packages'])) {
            $deps = array_keys($deps['packages']);
            dump($deps);

            foreach ($deps as $dep) {
                $a = rex_addon::get($dep);
                // dump($a);
                // dump($a->isAvailable());

                if (!($a->isAvailable())) {
                    // echo $addon;


                    if (!isset($this->nodes[$addon])) {
                        $this->nodes[$addon] = new Node($addon);
                    }

                    if (!isset($this->nodes[$dep])) {
                        $this->nodes[$dep] = new Node($dep);
                    }

                    $this->nodes[$addon]->addEdge($this->nodes[$dep]);  # addon depends on dependency

                    if (!in_array($dep, $this->packages_to_install)) {
                        array_push($this->packages_to_install, $dep);
                    }

                    $this->get_dependencies($dep);
                }
            }
        }
    }


    function get_key($packagesFromInstaller, $addon)
    {
        $filekeys = array_keys($packagesFromInstaller[$addon]['files']);
        // dump($filekeys);

        foreach ($filekeys as $key) {
            $version = $packagesFromInstaller[$addon]['files'][$key]['version'];
            if (!str_contains($version, 'beta') && !str_contains($version, 'dev') && !str_contains($version, 'alpha') && !str_contains($version, 'rc')) {
                // dump($key);
                return $key;
            }
        }
        echo rex_view::error('No released version for addon ' . $addon);
    }
}
