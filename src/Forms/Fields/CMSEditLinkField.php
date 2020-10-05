<?php

namespace Sunnysideup\CmsEditLinkField\Forms\Fields;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\HTMLReadonlyField;
use SilverStripe\ORM\DataObject;
use Sunnysideup\CmsEditLinkField\Api\CMSEditLinkAPI;

/**
 * typical usage:
 * CMSEditLinkField::create(
 *      $name = 'MyParent',
 *      $title = 'My Parent',
 *      $linkedObject = $this->MyParent()
 * );
 */
class CMSEditLinkField extends HTMLReadonlyField
{
    /**
     * what are we linking to?
     * @var DataObject
     */
    protected $linkedObject = null;

    /**
     * appendix for field name
     * @var string
     */
    protected $nameAppendix = '_CMSEditLink';

    /**
     * @param string $name                 e.g. MyLinkedObjectID
     * @param string $title                e.g. My Fancy Title
     * @param DataObject|null $linkedObject     e.g. MyLinkedObject
     * @param string $methodOrVariable     (OPTIONAL) - e.g. MyFullTitle
     */
    public function __construct(string $name, string $title, $linkedObject, ?string $methodOrVariable = 'getTitle')
    {
        $name .= $this->nameAppendix;
        if ($linkedObject && $linkedObject->exists()) {
            $link = $this->findLink($linkedObject);
            $description = $this->findLabelDescription($linkedObject, $methodOrVariable);
            $content = '<p class="cms-edit-link"><a href="' . $link . '" target="_blank">' . Convert::raw2xml($description) . '</a></p>';

            parent::__construct($name, $title, $content);
        } else {
            $content = _t('CMSEditLinkField.NONE', '(none)');

            parent::__construct($name, $title, $content);
        }
    }

    /**
     * @param string $s
     */
    public function setNameAppendix(string $s)
    {
        $this->nameAppendix = $s;

        return $this;
    }

    protected function findLink($linkedObject): string
    {
        if ($linkedObject->hasMethod('CMSEditLink')) {
            $link = $linkedObject->CMSEditLink();
        } elseif ($linkedObject->hasMethod('getCMSEditLink')) {
            $link = $linkedObject->getCMSEditLink();
        } else {
            $link = CMSEditLinkAPI::find_edit_link_for_object($linkedObject);
        }

        return $link;
    }

    protected function findLabelDescription($linkedObject, string $methodOrVariable): string
    {
        if ($linkedObject->hasMethod($methodOrVariable)) {
            $description = $linkedObject->{$methodOrVariable}();
        } elseif (isset($linkedObject->{$methodOrVariable})) {
            $description = $linkedObject->{$methodOrVariable};
        } else {
            $description = 'ERROR!';
            user_error($methodOrVariable . ' does not exist on ' . $linkedObject . ' (as method or variable)');
        }

        return $description;
    }
}
