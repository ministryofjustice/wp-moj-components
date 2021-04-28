<?php

namespace MOJComponents\Security;

class FilterRestAPI
{
    /**
     * @var object
     */
    public $helper = '';

    public function __construct()
    {
        global $mojHelper;
        $this->helper = $mojHelper;

        $this->actions();
    }

    public function actions()
    {
        add_filter('rest_authentication_errors', [$this, 'restAuthenticate']);
    }

    public function restAuthenticate($result)
    {
        $currentRoute = $this->getCurrentRoute();

        if (!$this->isRouteAllowed($currentRoute)) {
            return $this->getWPError($result);
        }

        // Our custom authentication check should have no effect
        // on logged-in requests
        return $result;
    }

    /**
     * Current REST route getter.
     *
     * @return string
     */
    private function getCurrentRoute(): string
    {
        $rest_route = $GLOBALS['wp']->query_vars['rest_route'];

        return (empty($rest_route) || '/' == $rest_route) ?
            $rest_route :
            untrailingslashit($rest_route);
    }


    /**
     * Checks a route for whether it belongs to the list of disallowed routes
     *
     * @param $currentRoute
     *
     * @return boolean
     */
    private function isRouteAllowed($currentRoute): bool
    {
        $disallowed = [
            'user'
        ];

        return (str_replace($disallowed, '', $currentRoute) === $currentRoute);
    }

    /**
     * If $access is already a WP_Error object, add our error to the list
     * Otherwise return a new one
     *
     * @param $access
     *
     * @return WP_Error
     */
    private function getWPError($access)
    {
        $error_message = esc_html__('Only authenticated users can access the REST API.');

        if (is_wp_error($access)) {
            $access->add('rest_cannot_access', $error_message, array('status' => rest_authorization_required_code()));

            return $access;
        }

        return new WP_Error('rest_cannot_access', $error_message, array('status' => rest_authorization_required_code()));
    }
}
