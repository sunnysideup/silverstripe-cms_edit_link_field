<?php

namespace Sunnysideup\CmsEditLinkField\Api;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;

class CMSEditLinkAPI
{
    private static $_cache = [];

    /**
     * common usage ...
     *
     *         public function CMSEditLink($action = null)
     *         {
     *             return CMSEditLinkAPI::find_edit_link_for_object($this, $action);
     *         };
     * returns an empty string if not found!
     *
     * @param  DataObject|string $objectOrClassName
     * @param  string $action
     * @param  string $modelAdminURLOverwrite - predetermine modeladmin
     *
     * @return string
     */
    public static function find_edit_link_for_object($objectOrClassName, $action = null, $modelAdminURLOverwrite = '') : string
    {
        if (is_string($objectOrClassName)) {
            $modelNameToEdit = $objectOrClassName;
            $objectToEdit = Injector::inst()->get($modelNameToEdit);
            $id = 0;
        } else {
            $modelNameToEdit = $objectOrClassName->ClassName;
            $objectToEdit = $objectOrClassName;
        }
        if ($objectToEdit instanceof DataObject) {
            if ($objectToEdit->exists()) {
                $id = $objectOrClassName->ID;
            } else {
                $id = 0;
            }
        } else {
            user_error('$objectOrClassName is not set correctly.', E_USER_NOTICE);
            return '';
        }
        if ($objectToEdit instanceof Member) {
            return Controller::join_links(
                Director::baseURL(),
                '/admin/security/EditForm/field/Members/item/' . $objectToEdit->ID . '/edit/'
            );
        }
        if ($objectToEdit instanceof Group) {
            return Controller::join_links(
                Director::baseURL(),
                '/admin/security/EditForm/field/Groups/item/' . $objectToEdit->ID . '/edit/'
            );
        }

        $classFound = false;
        if ($modelAdminURLOverwrite) {
            $classFound = true;
        } else {
            $modelAdminResults = self::getModelAdmin($modelNameToEdit);
            if(count($modelAdminResults)) {
                $modelNameToEdit = $modelAdminResults['ModelNameToEdit'];
                $myModelAdminclassObject = $modelAdminResults['MyModelAdminclassObject'];
                $classFound = true;
            }
        }
        if ($classFound) {
            $modelNameToEdit = self::sanitize_class_name($modelNameToEdit);
            if ($id === 0) {
                $id = 'new';
            }
            if (! $action) {
                $action = $modelNameToEdit . '/EditForm/field/' . $modelNameToEdit . '/item/' . $id . '/';
            }
            if ($modelAdminURLOverwrite) {
                $link = '/admin/' . $modelAdminURLOverwrite . '/' . $action;
            } else {
                $link = $myModelAdminclassObject->Link($action);
            }

            return Controller::join_links(
                Director::baseURL(),
                $link
            );
        }

        return '';
    }

    protected static function getModelAdmin($modelNameToEdit) : string
    {
        $originalModelNameToEdit = $modelNameToEdit;
        if(! isset(self::$_cache[$originalModelNameToEdit])) {
            self::$_cache[$originalModelNameToEdit] = [];
            $classFound = false;
            foreach (ClassInfo::subclassesFor(ModelAdmin::class) as $i => $myAdminClassName) {
                for ($includeChildren = 0; $includeChildren < 2; $includeChildren++) {
                    if ($myAdminClassName === ModelAdmin::class) {
                        continue;
                    }
                    if (ClassInfo::classImplements($myAdminClassName, TestOnly::class)) {
                        continue;
                    }
                    $myModelAdminclassObject = Injector::inst()->get($myAdminClassName);
                    $models = $myModelAdminclassObject->getManagedModels();

                    foreach ($models as $model => $modelDetails) {
                        if (is_string($modelDetails)) {
                            $model = $modelDetails;
                        }
                        $childrenForModelBeingManaged = null;
                        if ($includeChildren) {
                            $childrenForModelBeingManaged = ClassInfo::subclassesFor($model);
                            if (is_array($childrenForModelBeingManaged)) {
                                $modelsToSearch = array_reverse($childrenForModelBeingManaged);
                            }
                        } else {
                            $modelsToSearch = [$model];
                        }
                        foreach ($modelsToSearch as $modelToSearch) {
                            if ($modelToSearch === $modelNameToEdit) {
                                if ($modelNameToEdit !== $model) {
                                    $modelNameToEdit = $model;
                                }
                                $classFound = true;
                                break 4;
                            }
                        }
                    }
                }
            }
        }
        if($classFound) {
            self::$_cache[$originalModelNameToEdit] = [
                'ModelNameToEdit' => $modelNameToEdit,
                'MyModelAdminclassObject' => $myModelAdminclassObject,
            ];
        }
        return self::$_cache[$originalModelNameToEdit];
    }

    protected static function sanitize_class_name(string $className) : string
    {
        return str_replace('\\', '-', $className);
    }
}
