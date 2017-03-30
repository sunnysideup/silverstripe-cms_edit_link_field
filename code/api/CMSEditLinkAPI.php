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
    public static function find_edit_link_for_object($objectOrClassName, $action = null, $modelAdminURLOverwrite = '')
    {
        if(is_string($objectOrClassName)) {
            $modelNameToEdit = $objectOrClassName;
            $objectToEdit = Injector::inst()->get($modelNameToEdit);
            $id = 0;
        } else {
            $modelNameToEdit = $objectOrClassName->ClassName;
            $objectToEdit = $objectOrClassName;
        }
        if($objectToEdit instanceof DataObject) {
            if($objectToEdit->exists()) {
                $id = $objectOrClassName->ID;
            } else {
                $id = 0;
            }
        } else {
            user_error('$objectOrClassName is not set correctly.', E_USER_NOTICE);
            return;
        }
        if($objectToEdit instanceof Member) {
            return Controller::join_links(
                Director::baseURL(),
                '/admin/security/EditForm/field/Members/item/'.$objectToEdit->ID.'/edit/'
            );
        }
        if($objectToEdit instanceof Group) {
            return Controller::join_links(
                Director::baseURL(),
                '/admin/security/EditForm/field/Groups/item/'.$objectToEdit->ID.'/edit/'
            );
        }
        if($modelAdminURLOverwrite) {
            $classFound = true;
        } else {
            $cachekey = $modelNameToEdit.'_'.$action;
            $cache = SS_Cache::factory('cms_edit_link_cache');
            $myAdminClassName = $cache->load($cachekey);
            if ($myAdminClassName) {
                $myModelAdminclassObject = Injector::inst()->get($myAdminClassName);
                if($myModelAdminclassObject instanceof ModelAdmin) {
                    $classFound = true;
                }
            }
            else {
                $classFound = false;
                foreach(ClassInfo::subclassesFor('ModelAdmin') as $i => $myAdminClassName) {
                    if($myAdminClassName == 'ModelAdmin') {continue;}
                    if(ClassInfo::classImplements($myAdminClassName, 'TestOnly')) {continue;}
                    $myModelAdminclassObject = Injector::inst()->get($myAdminClassName);
                    $models = $myModelAdminclassObject->getManagedModels();
                    foreach($models as $key => $model) {
                        if($key === $modelNameToEdit || (is_string($model) && $model === $modelNameToEdit)) {
                            $classFound = true;
                            $cache->save($myAdminClassName, $cachekey);

                            break 2;
                        }
                    }
                }
            }
        }
        if($classFound) {
            $modelNameToEdit = self::sanitize_class_name($modelNameToEdit);
            if($id === 0) {
                $id = 'new';
            }
            if(!$action) {
                $action = $modelNameToEdit.'/EditForm/field/'.$modelNameToEdit.'/item/'.$id.'/';
            }
            if($modelAdminURLOverwrite) {
                $link = '/admin/'.$modelAdminURLOverwrite.'/'.$action;
            } else {
                $link = $myModelAdminclassObject->Link($action);
            }
            return Controller::join_links(
                Director::baseURL(),
                $link
            );
        }
        else {
            return Controller::join_links(
                Director::baseURL(),
                'admin/not-found'
            );
        }

    }


    /**
     * Sanitise a model class' name for inclusion in a link
     * @return string
     */
    protected static function sanitize_class_name($className) {
        return str_replace('\\', '-', $className);
    }

}
