<?php

namespace component;

use component\Sitemap\SitemapSettings as Settings;

class Sitemap
{
    /**
     * @var string
     */
    public $parentPath = '';


    /**
     * @var boolean
     */
    public $hasSettings = true;

    /**
     * @var object
     */
    public $settings;

    public function __construct()
    {

        $this->settings = new Settings();

        $this->actions();

        add_shortcode('wp_sitemap_page', [$this, 'shortcodeMainFunc']);
    }

    public function actions()
    {
        add_action('wp_loaded', [$this->settings, 'settings'], 1);
    }

    /**
     * Shortcode function that generate the sitemap
     * Use like this : [wp_sitemap_page]
     *
     * @param $atts
     * @param $content
     * @return str $return
     */
    public function shortcodeMainFunc($atts, $content = null)
    {
        return '<div class="wsp-container">' . $this->buildSitemap($atts, $content) . '</div>';
    }

    /**
     * Main function to call all the various features
     *
     * @param $atts
     * @param $content
     * @return str $return
     */
    public function buildSitemap($atts, $content = null)
    {

        // init
        $return = '';

        // display only some CPT
        // the "only" parameter always is higher than "exclude" options
        $only_cpt = (isset($atts['only']) ? sanitize_text_field($atts['only']) : '');

        // display or not the title
        $get_display_title = (isset($atts['display_title']) ? sanitize_text_field($atts['display_title']) : 'true');
        $is_title_displayed = ($get_display_title == 'false' ? false : true);

        // display or not the category title "category:"
        $get_display_category_title_wording = (isset($atts['display_category_title_wording']) ? sanitize_text_field($atts['display_category_title_wording']) : 'true');
        $is_category_title_wording_displayed = ($get_display_category_title_wording == 'false' ? false : true);

        // get only the private page/post ...
        $only_private = (isset($atts['only_private']) ? sanitize_text_field($atts['only_private']) : 'false');
        $is_get_only_private = ($only_private == 'true' ? true : false);

        // get the kind of sort
        $sort = (isset($atts['sort']) ? sanitize_text_field($atts['sort']) : null);
        $order = (isset($atts['order']) ? sanitize_text_field($atts['order']) : null);

        // Exclude some pages (separated by a coma)

        $wsp_add_nofollow = false;
        $wsp_is_display_post_multiple_time = false;
        $wsp_exclude_pages = '';
        $wsp_is_exclude_password_protected = '';

        $options = get_option('moj_component_settings');


        if (array_key_exists('sitemap_exclude_pages', $options)) {
            $wsp_exclude_pages = trim($options['sitemap_exclude_pages']);
        }
        if (array_key_exists('sitemap_exclude_password_protected', $options)) {
            $wsp_is_exclude_password_protected = $options['sitemap_exclude_password_protected'];
        }

        // Determine if the posts should be displayed multiple time if it is in multiple category
        $display_post_only_once = ($wsp_is_display_post_multiple_time == 1 ? false : true);

        // Determine if the posts should be displayed multiple time if it is in multiple category
        $display_nofollow = ($wsp_add_nofollow == 1 ? true : false);

        // Exclude pages, posts and CTPs protected by password
        if ($wsp_is_exclude_password_protected == 1) {

            global $wpdb;

            // Obtain the password protected content
            $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_status = \'publish\' AND post_password <> \'\' ';
            $password_pages = $wpdb->get_col($sql);

            // add to the other if not empty
            if (!empty($password_pages)) {
                // convert array to string
                $exclude_pages = implode(',', $password_pages);

                // Add the excluded page to the other protected page
                if (!empty($wsp_exclude_pages)) {
                    $wsp_exclude_pages .= ',' . $exclude_pages;
                } else {
                    $wsp_exclude_pages = $exclude_pages;
                }
            }
        }

        // check if the attribute "only" is used
        switch ($only_cpt) {
            // display only PAGE
            case 'page':
                return $this->returnContentTypePage($is_title_displayed, $is_get_only_private, $display_nofollow, $wsp_exclude_pages, $sort);
                break;
            // display only POST
            case 'post':
                return $this->returnContentTypePost($is_title_displayed, $display_nofollow, $display_post_only_once, $is_category_title_wording_displayed,
                    $wsp_exclude_pages, $sort, $sort, $order);
                break;
            // display only ARCHIVE
            case 'archive':
                return $this->returnContentTypeArchive($is_title_displayed, $display_nofollow);
                break;
            // display only AUTHOR
            case 'author':
                return $this->returnContentTypeAuthor($is_title_displayed, $display_nofollow, $sort);
                break;
            // display only CATEGORY
            case 'category':
                return $this->returnContentTypeCategories($is_title_displayed, $display_nofollow, $sort);
                break;
            // display only TAGS
            case 'tag':
                return $this->returnContentTypeTag($is_title_displayed, $display_nofollow);
                break;
            // empty
            case '':
                // nothing but do
                break;
            default:
                // check if it's the name of a CPT

                // extract CPT object
                $cpt = get_post_type_object($only_cpt);

                if (!empty($cpt)) {

                    return $this->returnContentTypeCptItems($is_title_displayed, $display_nofollow, $cpt, $only_cpt, $wsp_exclude_pages, $sort);
                }

                // check if it's a taxonomy
                $taxonomy_obj = get_taxonomy($only_cpt);

                if (!empty($taxonomy_obj)) {
                    return $this->returnContentTypeTaxonomyItems($is_title_displayed, $display_nofollow, $taxonomy_obj, $wsp_exclude_pages);
                }
            // end
        }


        //===============================================
        // Otherwise, display traditionnal sitemap
        //===============================================

        // exclude some custome post type (page, post, archive or author)
        // value : 0=do not exclude ; 1=exclude
        $wsp_exclude_cpt_page = "";
        $wsp_exclude_cpt_post = "";
        $wsp_exclude_cpt_archive = "";
        $wsp_exclude_cpt_author = "";

        if (array_key_exists('sitemap_exclude_cpt_page', $options)) {
            $wsp_exclude_cpt_page = $options['sitemap_exclude_cpt_page'];
        }

        if (array_key_exists('sitemap_exclude_cpt_post', $options)) {
            $wsp_exclude_cpt_post = $options['sitemap_exclude_cpt_post'];
        }

        if (array_key_exists('sitemap_exclude_cpt_archive', $options)) {
            $wsp_exclude_cpt_archive = $options['sitemap_exclude_cpt_archive'];
        }

        if (array_key_exists('sitemap_exclude_cpt_author', $options)) {
            $wsp_exclude_cpt_author = $options['sitemap_exclude_cpt_author'];
        }


        // List the PAGES
        if (empty($wsp_exclude_cpt_page)) {
            $return .= $this->returnContentTypePage($is_title_displayed, $is_get_only_private, $display_nofollow, $wsp_exclude_pages, $sort);
        }

        // List the POSTS by CATEGORY
        if (empty($wsp_exclude_cpt_post)) {
            $return .= $this->returnContentTypePost($is_title_displayed, $display_nofollow, $display_post_only_once, $is_category_title_wording_displayed,
                $wsp_exclude_pages);
        }

        // List the CPT
        $return .= $this->returnContentTypeCptLists($is_title_displayed, $display_nofollow, $wsp_exclude_pages);

        // List the Taxonomies
        $return .= $this->returnContentTypeTaxonomiesLists($is_title_displayed, $display_nofollow, $wsp_exclude_pages);

        // List the ARCHIVES
        if (empty($wsp_exclude_cpt_archive)) {
            $return .= $this->returnContentTypeArchive($is_title_displayed, $display_nofollow);
        }

        // List the AUTHORS
        if (empty($wsp_exclude_cpt_author)) {
            $return .= $this->returnContentTypeAuthor($is_title_displayed, $display_nofollow, $sort);
        }

        // return the content
        return $return;
    }

    /**
     * Return list of posts
     *
     * @param bool $is_title_displayed
     * @param bool $is_get_only_private
     * @param bool $display_nofollow
     * @param array $wsp_exclude_pages
     * @param str $sort
     * @return str $return
     */
    public function returnContentTypePost($is_title_displayed = true, $is_get_only_private = false, $display_nofollow = false, $wsp_exclude_pages = array())
    {

        // init
        $return = '';

        // define the way the pages should be displayed
        $args = array();

        $args['posts_per_page'] = -1;

        // exclude some pages ?
        if (!empty($wsp_exclude_pages)) {
            $args['exclude'] = $wsp_exclude_pages;
        }

        // List of posts for this category
        $the_posts = get_posts($args);

        if (empty($the_posts) == false) {
            // add content
            if ($is_title_displayed == true) {
                $return .= '<h2>' . __('Posts', 'wp-moj-components') . '</h2>' . "\n";
            }

            $return .= '<ul>' . "\n";

            // display a nofollow attribute ?
            $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');

            foreach ($the_posts as $the_post) {
                $return .= '<li><a href="' . get_permalink($the_post->ID) . '" ' . $attr_nofollow . ' >' . $the_post->post_title . '</a> (' . get_the_time('d', $the_post->ID) . '/' . get_the_time('m', $the_post->ID) . '/' . get_the_time('Y', $the_post->ID) . ')</li>';
            }

            $return .= '</ul>' . "\n";
        }

        // return content
        return apply_filters('wsp_posts_return', $return);
    }

    /**
     * Return list of pages
     *
     * @param bool $is_title_displayed
     * @param bool $is_get_only_private
     * @param bool $display_nofollow
     * @param array $wsp_exclude_pages
     * @param str $sort
     * @return str $return
     */
    public function returnContentTypePage($is_title_displayed = true, $is_get_only_private = false, $display_nofollow = false, $wsp_exclude_pages = array(), $sort = null)
    {

        // init
        $return = '';

        if ($display_nofollow == true) {
            add_filter('wp_list_pages', [$this, 'addNoFollow']);
        }

        // define the way the pages should be displayed
        $args = array();
        $args['title_li'] = '';
        $args['echo'] = '0';

        // change the sort
        if ($sort !== null) {
            $args['sort_column'] = $sort;
        }

        // exclude some pages ?
        if (!empty($wsp_exclude_pages)) {
            $args['exclude'] = $wsp_exclude_pages;
        }

        // get only the private content
        if ($is_get_only_private == true) {
            $args['post_status'] = 'private';
        }

        // get data
        $list_pages = wp_list_pages($args);

        // check it's not empty
        if (empty($list_pages)) {
            return '';
        }

        // add content
        if ($is_title_displayed == true) {
            $return .= '<h2 class="wsp-pages-title">' . __('Pages', 'wp-moj-components') . '</h2>' . "\n";
        }
        $return .= '<ul class="wsp-pages-list">' . "\n";
        $return .= $list_pages;
        $return .= '</ul>' . "\n";

        // return content
        return apply_filters('wsp_pages_return', $return);
    }

    /**
     * Return list of posts in the categories
     *
     * @param bool $is_title_displayed
     * @return str $return
     */
    public function returnContentTypeCategories($is_title_displayed = true, $display_nofollow = false, $sort = null)
    {

        // init
        $return = '';

        // args
        $args = array();

        // change the sort order
        if ($sort !== null) {
            $args['orderby'] = $sort;
        }

        // Get the categories
        $cats = get_categories($args);

        // check it's not empty
        if (empty($cats)) {
            return '';
        }

        // display a nofollow attribute ?
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');

        // add content
        if ($is_title_displayed == true) {
            $return .= '<h2 class="wsp-categories-title">' . __('Categories', 'wp-moj-components') . '</h2>' . "\n";
        }
        $return .= '<ul class="wsp-categories-list">' . "\n";
        foreach ($cats as $cat) {
            $return .= "\t" . '<li><a href="' . get_category_link($cat->cat_ID) . '"' . $attr_nofollow . '>' . $cat->name . '</a></li>' . "\n";
        }
        $return .= '</ul>' . "\n";

        // return content
        return apply_filters('wsp_categories_return', $return);
    }


    /**
     * Return list of posts in the categories
     *
     * @param bool $is_title_displayed
     * @return str $return
     */
    public function returnContentTypeTag($is_title_displayed = true, $display_nofollow = false)
    {

        // init
        $return = '';

        // args
        $args = array();

        // Get the categories
        $posttags = get_tags($args);

        // check it's not empty
        if (empty($posttags)) {
            return '';
        }

        // display a nofollow attribute ?
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');

        // add content
        if ($is_title_displayed == true) {
            $return .= '<h2 class="wsp-tags-title">' . __('Tags', 'wp-moj-components') . '</h2>' . "\n";
        }
        $return .= '<ul class="wsp-tags-list">' . "\n";
        foreach ($posttags as $tag) {
            $return .= "\t" . '<li><a href="' . get_tag_link($tag->term_id) . '"' . $attr_nofollow . '>' . $tag->name . '</a></li>' . "\n";
        }
        $return .= '</ul>' . "\n";

        // return content
        return apply_filters('wsp_tags_return', $return);
    }


    /**
     * Return list of archives
     *
     * @param bool $is_title_displayed
     * @return str $return
     */
    public function returnContentTypeArchive($is_title_displayed = true, $display_nofollow = false)
    {

        // init
        $return = '';

        // define the way the pages should be displayed
        $args = array();
        $args['echo'] = 0;

        // get data
        $list_archives = wp_get_archives($args);

        // check it's not empty
        if (empty($list_archives)) {
            return '';
        }

        // display a nofollow attribute ?
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');

        // add content
        if ($is_title_displayed == true) {
            $return .= '<h2 class="wsp-archives-title">' . __('Archives', 'wp-moj-components') . '</h2>' . "\n";
        }
        $return .= '<ul class="wsp-archives-list">' . "\n";
        $return .= $list_archives;
        $return .= '</ul>' . "\n";

        // return content
        return apply_filters('wsp_archives_return', $return);
    }


    /**
     * Return list of authors
     *
     * @param bool $is_title_displayed
     * @param bool $display_nofollow
     * @param text $sort
     * @return str $return
     */
    public function returnContentTypeAuthor($is_title_displayed = true, $display_nofollow = false, $sort = null)
    {

        // init
        $return = '';

        // define the way the pages should be displayed
        $args = array();
        $args['echo'] = 0;

        // change the sort order
        if ($sort !== null) {
            $args['orderby'] = $sort;
        }

        // get data
        $list_authors = wp_list_authors($args);

        // check it's not empty
        if (empty($list_authors)) {
            return '';
        }

        // display a nofollow attribute ?
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');

        // add content
        if ($is_title_displayed == true) {
            $return .= '<h2 class="wsp-authors-title">' . __('Authors', 'wp-moj-components') . '</h2>' . "\n";
        }
        $return .= '<ul class="wsp-authors-list">' . "\n";
        $return .= $list_authors;
        $return .= '</ul>' . "\n";

        // return content
        return apply_filters('wsp_authors_return', $return);
    }


    /**
     * Return list of all other custom post type
     *
     * @param bool $is_title_displayed
     * @param bool $display_nofollow
     * @param str $wsp_exclude_pages
     * @return str $return
     */
    public function returnContentTypeCptLists($is_title_displayed = true, $display_nofollow = false, $wsp_exclude_pages)
    {

        $options = get_option('moj_component_settings');

        // init
        $return = '';

        // define the main arguments
        $args = array(
            'public' => true,
            '_builtin' => false
        );

        // Get the CPT (Custom Post Type)
        $post_types = get_post_types($args, 'names');

        // check it's not empty
        if (empty($post_types)) {
            return '';
        }

        // list all the CPT
        foreach ($post_types as $post_type) {

            // extract CPT object
            $cpt = get_post_type_object($post_type);

            $exclude = '';
            if (array_key_exists('sitemap_exclude_cpt_' . $cpt->name, $options)) {
                // Is this CPT already excluded ?

                $exclude = $options['sitemap_exclude_cpt_' . $cpt->name];

            }

            if (empty($exclude)) {
                $return .= $this->returnContentTypeCptItems($is_title_displayed, $display_nofollow, $cpt, $post_type, $wsp_exclude_pages);
            }
        }

        // return content
        return $return;
    }


    /**
     * Return list of all other custom post type
     *
     * @param bool $is_title_displayed
     * @param bool $display_nofollow
     * @param str $cpt
     * @param str $post_type
     * @param str $wsp_exclude_pages
     * @param str $sort
     * @return str $return
     */
    public function returnContentTypeCptItems($is_title_displayed = true, $display_nofollow = false, $cpt, $post_type, $wsp_exclude_pages, $sort = null)
    {
        // init
        $return = '';

        // List the pages
        $list_pages = '';

        // define the way the pages should be displayed
        $args = array();
        $args['post_type'] = $post_type;
        $args['posts_per_page'] = 999999;
        $args['suppress_filters'] = 0;

        // exclude some pages ?
        if (!empty($wsp_exclude_pages)) {
            $args['exclude'] = $wsp_exclude_pages;
        }

        // change the sort order
        if ($sort !== null) {
            $args['orderby'] = $sort;
        }

        // Query to get the current custom post type
        $posts_cpt = get_posts($args);

        // display a nofollow attribute ?
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');

        // List all the results
        if (!empty($posts_cpt)) {
            foreach ($posts_cpt as $post_cpt) {

                $post_link = apply_filters('wsp_cpt_link', get_permalink($post_cpt->ID), $post_cpt);

                $list_pages .= '<li><a href="' . $post_link . '"' . $attr_nofollow . '>' . $post_cpt->post_title . '</a></li>' . "\n";
            }
        }

        // Return the data (if it exists)
        if (!empty($list_pages)) {
            if ($is_title_displayed == true) {
                $return .= '<h2 class="wsp-' . $post_type . 's-title">' . $cpt->label . '</h2>' . "\n";
            }
            $return .= '<ul class="wsp-' . $post_type . 's-list">' . "\n";
            $return .= $list_pages;
            $return .= '</ul>' . "\n";
        }

        // return content
        return apply_filters('wsp_cpts_return', $return);
    }


    /**
     * Return list of all other custom post type
     *
     * @param bool $is_title_displayed
     * @param bool $display_nofollow
     * @param str $wsp_exclude_pages
     * @return str $return
     */
    public function returnContentTypeTaxonomiesLists($is_title_displayed = true, $display_nofollow = false, $wsp_exclude_pages)
    {

        $options = get_option('moj_component_settings');

        // init
        $return = '';

        $args = array(
            'public' => true,
            '_builtin' => false
        );
        $taxonomies_names = get_taxonomies($args);

        // check it's not empty
        if (empty($taxonomies_names)) {
            return '';
        }

        // list all the taxonomies
        foreach ($taxonomies_names as $taxonomy_name) {

            // Extract
            $taxonomy_obj = get_taxonomy($taxonomy_name);

            $exclude = '';
            if (array_key_exists('sitemap_exclude_taxonomy_' . $taxonomy_name, $options)) {

                // Is this taxonomy already excluded ?
                $exclude = $options['sitemap_exclude_taxonomy_' . $taxonomy_name];
            }

            if (empty($exclude)) {
                $return .= $this->returnContentTypeTaxonomyItems($is_title_displayed, $display_nofollow, $taxonomy_obj, $exclude);
            }

        }

        // return content
        return $return;
    }


    /**
     * Return list of all other taxonomies
     *
     * @param bool $is_title_displayed
     * @param bool $display_nofollow
     * @param object $taxonomy_obj
     * @param str $wsp_exclude_pages
     * @return str $return
     */
    public function returnContentTypeTaxonomyItems($is_title_displayed = true, $display_nofollow = false, $taxonomy_obj, $wsp_exclude_taxonomy)
    {

        // init
        $return = '';

        // List the pages
        $list_pages = '';

        // get some data
        $taxonomy_name = $taxonomy_obj->name;
        $taxonomy_label = $taxonomy_obj->label;

        // init variable to get terms of a taxonomy
        $taxonomies = array($taxonomy_name);
        $args = array();

        // get the terms of this taxonomy
        $terms = get_terms($taxonomies, $args);

        // display a nofollow attribute ?
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');

        // List all the results
        if (!empty($terms)) {
            foreach ($terms as $terms_obj) {
                $list_pages .= '<li><a href="' . get_term_link($terms_obj) . '"' . $attr_nofollow . '>' . $terms_obj->name . '</a></li>' . "\n";
            }
        }

        // Return the data (if it exists)
        if (!empty($list_pages)) {
            if ($is_title_displayed == true) {
                $return .= '<h2 class="wsp-' . $taxonomy_name . 's-title">' . $taxonomy_label . '</h2>' . "\n";
            }
            $return .= '<ul class="wsp-' . $taxonomy_name . 's-list">' . "\n";
            $return .= $list_pages;
            $return .= '</ul>' . "\n";
        }

        // return content
        return apply_filters('wsp_taxonomies_return', $return);
    }

    /**
     * Add nofollow attribute to the links of the wp_list_pages() functions
     *
     * @param str $output content
     * @return str
     */
    public function addNoFollow($output)
    {
        return str_replace('<a href=', '<a rel="nofollow" href=', $output);
    }


}
