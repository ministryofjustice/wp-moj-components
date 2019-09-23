<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-09-23
 * Time: 15:31
 */

namespace component;


class Popup
{
    public function __construct()
    {
    }

    public function content()
    {
        echo '<div class="update-nag notice">
                  <p><strong>Did you know?</strong>... You can let us know who you are on our <a href="/this-page">Get To Know Us page</a></p>
              </div>';
    }
}

function Popup()
{
    return new Popup();
}
