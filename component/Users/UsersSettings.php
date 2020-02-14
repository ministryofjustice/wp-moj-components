<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-10-10
 * Time: 14:37
 */

namespace component\Users;

use component\Users as Users;

class UsersSettings extends Users
{
    public $helper;

    public function __construct()
    {
        global $mojHelper;
        $this->helper = $mojHelper;
    }

    public function settings()
    {
        $this->helper->initSettings($this);
    }

    public function settingsFields($section)
    {
        add_settings_field(
            'to_email',
            __('Notification email', 'wp-moj-components'),
            [$this, 'userEmailAddress'],
            'mojComponentSettings',
            $section
        );

        add_settings_field(
            'user_inactive_schedule',
            __('Inactive Check Schedule', 'wp-moj-components'),
            [$this, 'userInactiveSchedule'],
            'mojComponentSettings',
            $section
        );

        add_settings_field(
            'user_active_disable',
            __('Disable User Inactive Check?', 'wp-moj-components'),
            [$this, 'disableUserInactivityCheck'],
            'mojComponentSettings',
            $section
        );
    }

    public function userEmailAddress()
    {
        $options = get_option('moj_component_settings');

        $toEmailAddress = $options['user_active_to_email'] ?? '';

        ?>
        <input type='text' name='moj_component_settings[user_active_to_email]'
               value='<?= $toEmailAddress ?>' placeholder='<?= $toEmailAddress ?>' class="moj-component-input">
        <p>This email will be used to notify in cases where a user exception has been discovered.</p>
        <?php

        return null;
    }

    public function disableUserInactivityCheck()
    {
        $options = get_option('moj_component_settings');

        ?>
        <input type='checkbox' name='moj_component_settings[user_active_disable]'
               value='yes' <?= checked('yes', $options['user_active_disable'] ?? '') ?>
               class="moj-component-input-checkbox">
        <p>User activity checks are useful while testing and on production servers. You can switch off the checking
            process here.</p>
        <?php

        return null;
    }

    public function userInactiveSchedule()
    {
        $options = get_option('moj_component_settings');
        $schedules = wp_get_schedules();
        $items = [];

        foreach ($schedules as $key => $schedule) {
            $items[$key]['name'] = $key;
            $items[$key]['interval'] = $schedule['interval'];
            $items[$key]['display'] = $schedule['display'];
        }

        usort($items, function ($a, $b) {
            return $a['interval'] <=> $b['interval'];
        });

        echo "<select id='user_inactive_schedule' name='moj_component_settings[user_inactive_schedule]'>";
        foreach ($items as $value => $item) {
            $selected = ($options['user_inactive_schedule'] == $value) ? 'selected="selected"' : '';
            echo "<option value='" . $item['name'] . "' $selected>" . $item['display'] . "</option>";
        }
        echo "</select>";
    }

    public function settingsSectionCB()
    {
        echo __(
            '<p>The Users section of the Components plugin seeks to monitor user activity and report unusual occurrences or aged login\'s.</p>',
            'wp-moj-components'
        );
    }
}
