<?php

namespace Sunnysideup\CmsEditLinkField\Forms\Fields;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\HTMLReadonlyField;
use SilverStripe\ORM\DataObject;

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
     * @param DataObject $linkedObject     e.g. MyLinkedObject
     * @param string $methodOrVariable     (OPTIONAL) - e.g. MyFullTitle
     */
    public function __construct($name, $title, $linkedObject, $methodOrVariable = 'getTitle')
    {
        $name .= $this->nameAppendix;
        if ($linkedObject && $linkedObject->exists() && $linkedObject->hasMethod('CMSEditLink')) {
            $this->linkedObject = $linkedObject;
            if ($this->linkedObject->hasMethod($methodOrVariable)) {
                $description = $this->linkedObject->{$methodOrVariable}();
            } elseif (isset($this->linkedObject->{$methodOrVariable})) {
                $description = $this->linkedObject->{$methodOrVariable};
            } else {
                $description = 'ERROR!';
                user_error($methodOrVariable . ' does not exist on ' . $this->linkedObject . ' (as method or variable)');
            }
            $content = '<p class="cms-edit-link"><a href="' . $this->linkedObject->CMSEditLink() . '">' . Convert::raw2xml($description) . '</a></p>';

            return parent::__construct($name, $title, $content);
        }
        parent::__construct($name, $title, $content = _t('CMSEditLinkField.NONE', '(none)'));
    }

    /**
     * @param string $s
     */
    public function setNameAppendix($s)
    {
        $this->nameAppendix = $s;
    }
}
