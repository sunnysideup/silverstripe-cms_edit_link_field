<?php

namespace Sunnysideup\CmsEditLinkField\Api;

use SilverStripe\Control\Controller;

class SubLinkForGridField
{
    public static function edit_link(string $fieldName, int $id): string
    {
        $link = $_SERVER['REQUEST_URI'];
        $link = rtrim($link, '/edit');

        $toAdd = 'ItemEditForm/field/' . $fieldName . '/item/' . $id . '/edit';

        return Controller::join_links($link, $toAdd);
    }
}
