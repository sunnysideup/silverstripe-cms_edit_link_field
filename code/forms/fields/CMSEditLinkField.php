<?php


class CMSEditLinkField extends ReadonlyField
{
    protected $linkedObject = '';

    public function __construct($name, $title, $linkedObject)
    {
        $name .= '_CMSEditLink';
        if ($linkedObject && $linkedObject->exists() && $linkedObject->hasMethod('CMSEditLink')) {
            $this->linkedObject = $linkedObject;
            $content = '<p><a href="'.$linkedObject->CMSEditLink().'">'.$linkedObject->getTitle().'</a></h3>';
            $this->dontEscape = true;

            return parent::__construct($name, $title, $content);
        } else {
            return parent::__construct($name, $title);
        }
    }
}
