<?php


class CMSEditLinkField extends ReadonlyField
{

    /**
     * what are we linking to?
     * @var DataObject
     */
    protected $linkedObject = null;

    /**
     * appendix for field name
     * @var String
     */
    protected $nameAppendix = '_CMSEditLink';


    /**
     *
     * @param string $name                 e.g. MyLinkedObjectID
     * @param string $title                e.g. My Fancy Title
     * @param DataObject $linkedObject     e.g. $this->MyLinkedObjectID()
     * @param string $methodOrVariable     (OPTIONAL) - e.g. MyFullTitle
     */
    public function __construct($name, $title, $linkedObject, $methodOrVariable = 'getTitle')
    {
        $name .= $this->nameAppendix;
        if ($linkedObject && $linkedObject->exists() && $linkedObject->hasMethod('CMSEditLink')) {
            $this->linkedObject = $linkedObject;
            if($this->linkedObject->hasMethod($methodOrVariable)) {
                $description = $this->linkedObject->$methodOrVariable();
            } elseif(isset($this->linkedObject->$methodOrVariable)) {
                $description = $this->linkedObject->$methodOrVariable;
            } else {
                $description = 'ERROR!';
                user_error($methodOrVariable.' does not exist on '.$this->linkedObject.' (as method or variable)');
            }
            $content = '<p class="cms-edit-link"><a href="'.$this->linkedObject->CMSEditLink().'">'.Convert::raw2xml($description).'</a></p>';
            $this->dontEscape = true;

            return parent::__construct($name, $title, $content);
        } else {
            return parent::__construct($name, $title);
        }
    }

    /**
     *
     *
     * @param string $s
     */
    function setNameAppendix($s)
    {
        $this->nameAppendix = $s;
    }


}
