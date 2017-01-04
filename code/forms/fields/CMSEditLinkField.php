<?php


class CMSEditLinkField extends ReadonlyField
{
    protected $linkedObject = '';

    /**
     *
     * @param string $name                 e.g. MyLinkedObjectID
     * @param string $title                e.g. My Fancy Title
     * @param DataObject $linkedObject     e.g. $this->MyLinkedObjectID()
     * @param string $methodOrVariable     (OPTIONAL) - e.g. MyFullTitle
     */
    public function __construct($name, $title, $linkedObject, $methodOrVariable = 'getTitle')
    {
        $name .= '_CMSEditLink';
        if ($linkedObject && $linkedObject->exists() && $linkedObject->hasMethod('CMSEditLink')) {
            $this->linkedObject = $linkedObject;
            if($linkedObject->hasMethod($methodOrVariable)) {
                $description = $linkedObject->$methodOrVariable();
            } elseif(isset($linkedObject->$methodOrVariable)) {
                $description = $linkedObject->$methodOrVariable;
            } else {
                $description = 'ERROR!';
                user_error($methodOrVariable.' does not exist on '.$linkedObject.' (as method or variable)');
            }
            $content = '<p><a href="'.$linkedObject->CMSEditLink().'">'.$description.'</a></h3>';
            $this->dontEscape = true;

            return parent::__construct($name, $title, $content);
        } else {
            return parent::__construct($name, $title);
        }
    }

}
