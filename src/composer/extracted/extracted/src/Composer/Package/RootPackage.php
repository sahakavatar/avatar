<?php


namespace Composer\Package;


class RootPackage extends CompletePackage implements RootPackageInterface
{
    protected $minimumStability = 'stable';
    protected $preferStable = false;
    protected $stabilityFlags = array();
    protected $config = array();
    protected $references = array();
    protected $aliases = array();

    public function getMinimumStability()
    {
        return $this->minimumStability;
    }

    public function setMinimumStability($minimumStability)
    {
        $this->minimumStability = $minimumStability;
    }

    public function getStabilityFlags()
    {
        return $this->stabilityFlags;
    }

    public function setStabilityFlags(array $stabilityFlags)
    {
        $this->stabilityFlags = $stabilityFlags;
    }

    public function getPreferStable()
    {
        return $this->preferStable;
    }

    public function setPreferStable($preferStable)
    {
        $this->preferStable = $preferStable;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function getReferences()
    {
        return $this->references;
    }

    public function setReferences(array $references)
    {
        $this->references = $references;
    }

    public function getAliases()
    {
        return $this->aliases;
    }

    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }
}
