<?php

namespace JewelTheme\AdminBarEditor\Inc\Classes;

class AdminBarEditorOptions extends AdminBarEditorModel
{
    public function __construct()
    {
        // this should be first so the default values get stored
        parent::__construct((array) get_option($this->prefix));
    }
}
