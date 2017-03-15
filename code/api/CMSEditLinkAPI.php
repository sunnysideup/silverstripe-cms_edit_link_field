<?php


class CMSEditLinkAPI extends Object
{

    private static $_cache = array();

    /**
     * common usage ...
     *
     *         public function CMSEditLink($action = null)
     *         {
     *             return CMSEditLinkAPI::find_edit_link_for_object($this, $action);
     *         };
     *
     * @param  DataObject | string $objectOrClassName
     * @param  string              $action
     *
     * @return string
     */
    public static function find_edit_link_for_object($objectOrClassName, $action = null)
    {
        if($objectOrClassName instanceof DataObject && $objectOrClassName->exists()) {
            $modelName = $objectOrClassName->ClassName;
            $id = $objectOrClassName->ID;
        } else {
            $modelName = $objectOrClassName;
            $objectOrClassName = Injector::inst()->get($modelName);
            $id = 0;
        }
        $key = $modelName.'_'.$action;
        if(!isset(self::$_cache[$key])) {
            foreach(ClassInfo::subclassesFor('ModelAdmin') as $i => $class) {
                if($class == 'ModelAdmin') {continue;}
                if(ClassInfo::classImplements($class, 'TestOnly')) {continue;}
                $myAdminClassName = $class;
                $modelAdminclassObject = Injector::inst()->get($myAdminClassName);
                $models = $modelAdminclassObject->getManagedModels();
                foreach($models as $key => $model) {
                    if($key === $modelName || $model === $modelName) {
                        $myManagerClass = $class;
                        $myManagerObject = $modelAdminclassObject;
                        break 2;
                    }
                }
            }
            if(isset($myManagerClass) && isset($myManagerObject)) {
                if(!$action) {
                    $action = $modelName.'/EditForm/field/'.$modelName.'/item/0/';
                    self::$_cache[$key] = Controller::join_links(
                        Director::baseURL(),
                        $myManagerObject->Link($action)
                    );
                } else {
                    return Controller::join_links(
                        Director::baseURL(),
                        $myManagerObject->Link($action)
                    );
                }
            }
        }

        return str_replace('item/0/', 'item/'.$id.'/', self::$_cache[$key]);
    }


}
