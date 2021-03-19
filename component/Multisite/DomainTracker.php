<?php

namespace MOJComponents\Multisite;

class DomainTracker
{
    public function __construct()
    {
        $this->actions();
    }

    public function actions()
    {
        add_action('update_option_home', [$this, 'trackCustomDomain'], 10, 3);
    }

    /**
     * Tracks a new custom domain against the original system domain when changed in
     * the administration panel for a sub-site. Also, updates a custom domain if it
     * changes whilst being tracked.
     *
     * @param $old string the old site domain name
     * @param $new string the new site domain name
     * @param $option string the option name
     * @return bool|null
     */
    public function trackCustomDomain(string $old, string $new, string $option)
    {
        // track only on production
        if ($this->isProduction() !== true) {
            return false;
        }

        // no point if not custom
        if (!$this->isCustomDomain($new)) {
            return false;
        }

        // get the current array of tracked URL's
        $tracker = get_site_option('moj_sub_site_urls', []);

        $old = $this->getKey($old, $tracker);
        // it maybe the case that a custom domain isn't tracked
        // in which case $old maybe null, this is a fatal error

        if (empty($old)) {
            return false;
        }

        // using old as array key, add the custom domain as it's value
        // prevents multiple entries of same path and stores the latest domain
        $tracker[$old] = $new;
        update_site_option('moj_sub_site_urls', $tracker);

        return null;
    }

    /**
     * Generate a sanitised string as the main tracker key for a custom domain
     * If $old is a custom domain it will attempt to extract the key from the
     * current tracker array. If one cannot be found then it will simply bail.
     * @param $old
     * @param $tracker
     * @return false|int|string|null
     */
    protected function getKey($old, $tracker)
    {
        // if old is custom and not empty
        if ($this->isCustomDomain($old)) {
            return array_search($old, $tracker);
        }

        return sanitize_title(parse_url($old, PHP_URL_PATH));
    }

    /**
     * Returns true if on a production stack
     *
     * This method has a consideration for local development and will
     * return true for domain names with a .docker extension
     *
     * @return bool
     * @uses strpos()
     * @uses env()
     */
    public function isProduction()
    {
        $isAllowed = env('WP_ENV') === 'production';

        // emulate production when developing locally
        if (env('WP_ENV') === 'development' && (strpos(env('WP_HOME'), '.docker') > -1)) {
            $isAllowed = true;
        }

        return $isAllowed;
    }

    /**
     * Determine if a given domain name has been customised for a specific sub-site.
     *
     * The str_replace() portion of the method attempts to remove the Multisites' base
     * URL from $domain. All system domains contain this. If the result of str_replace
     * still matches $domain, i.e. nothing has been removed, we can determine $domain
     * is a customised domain name.
     *
     * @param $domain
     * @return bool
     * @uses str_replace()
     * @uses env()
     */
    public function isCustomDomain($domain)
    {
        $domainCheck = str_replace(env('WP_HOME'), '', $domain);
        return $domainCheck === $domain;
    }
}
