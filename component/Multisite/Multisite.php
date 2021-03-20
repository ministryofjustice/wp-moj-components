<?php

namespace MOJComponents\Multisite;

class Multisite
{
    public function __construct()
    {
        // are we on a multisite install?
        if (!$this->isMultisite()) {
            return null;
        }

        new DomainTracker();
    }

    /**
     * Returns true if the installation is WP Multisite
     * @return bool
     */
    public function isMultisite()
    {
        return is_multisite();
    }
}
