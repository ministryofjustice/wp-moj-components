<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-10-10
 * Time: 14:19
 */

namespace component\Introduce;

use component\Introduce\Popup as Popup;

class PopupSettings extends Popup
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
            'popup_message_title',
            __('Enter a top title', 'wp-moj-components'),
            [$this, 'popupMessageTitleCB'],
            'mojComponentSettings',
            $section
        );

        add_settings_field(
            'popup_message',
            __('One line message', 'wp-moj-components'),
            [$this, 'popupMessageCB'],
            'mojComponentSettings',
            $section
        );
    }

    public function popupMessageTitleCB()
    {
        $options = get_option('moj_component_settings');

        $popupTitle = $options['popup_message_title'] ?? '';

        ?>
        <input type='text' name='moj_component_settings[popup_message_title]'
               value='<?php echo $popupTitle; ?>' class="moj-component-input">
        <?php
    }

    public function popupMessageCB()
    {
        $options = get_option('moj_component_settings');

        $popupMessage = $options['popup_message_body'] ?? '';

        ?>
        <textarea rows='3' name='moj_component_settings[popup_message_body]'
                  class="moj-component-input"><?php echo $popupMessage; ?></textarea>
        <?php
    }

    public function settingsSectionCB()
    {
        echo __(
            'Enter a title and message here that will appear in the notification banner. Once you save your changes the banner will display.',
            'wp-moj-components'
        );
    }
}
